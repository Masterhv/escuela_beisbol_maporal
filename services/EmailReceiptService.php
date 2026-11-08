<?php
require_once 'services/Mailer.php';
require_once 'services/PdfGenerator.php';
require_once 'models/Payment.php';
require_once 'models/PaymentWorkflow.php';

use App\Core\Traits\PdfGenerator;

/**
 * Servicio que orquesta el envío del recibo de pago por correo electrónico:
 * 1) Genera el PDF del recibo individual (reutilizando el trait PdfGenerator,
 *    la misma vista usada por el canal de WhatsApp).
 * 2) Envía el correo con el PDF adjunto al representante (parent_email).
 * 3) Registra el resultado (enviado/fallido) en la tabla payments.
 *
 * El envío nunca debe bloquear ni revertir el guardado del pago: cualquier
 * error se captura, se registra en el pago y se loguea con error_log().
 */
class EmailReceiptService {
    use PdfGenerator;

    /**
     * @var Payment
     */
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new Payment();
    }

    /**
     * Genera el PDF del recibo de un pago en memoria. Lo usan tanto el envío por
     * correo como la descarga manual, para que ambos entreguen el mismo documento.
     *
     * @param int        $paymentId ID del pago.
     * @param array|null $payment   Datos del pago con atleta (opcional; si es null se consultan).
     * @return array ['content' => string (bytes del PDF), 'filename' => string]
     * @throws \RuntimeException Si el pago no existe.
     */
    public function buildReceiptPdf($paymentId, $payment = null) {
        $payment = $payment ?: $this->paymentModel->findPaymentWithAthleteById($paymentId);

        if (!$payment) {
            throw new \RuntimeException('El pago no existe.');
        }

        $athleteName = trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''));
        $filenameBase = 'Recibo_Pago_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $athleteName) . '_' . $payment['id'];

        $verificationPayload = [
            'payment_id'   => $payment['id'],
            'athlete_name' => $athleteName,
            'parent_name'  => $payment['parent_name'] ?? null,
            'amount'       => number_format($payment['amount'], 2),
            'month'        => $payment['month'],
            'year'         => $payment['year'],
            'payment_date' => $payment['payment_date'],
        ];

        $html = $this->renderPdfView('payment_receipt', [
            'title'   => 'Recibo de Pago',
            'payment' => $payment,
        ], $verificationPayload, 'payment_receipt', $filenameBase);

        return [
            'content'  => $this->generatePdfString($html),
            'filename' => $filenameBase . '.pdf',
        ];
    }

    /**
     * Genera el recibo en PDF y lo envía por correo al representante del atleta.
     *
     * @param int $paymentId ID del pago (debe existir y estar en estado 'approved').
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function sendReceipt($paymentId) {
        try {
            $payment = $this->paymentModel->findPaymentWithAthleteById($paymentId);

            if (!$payment) {
                return $this->fail($paymentId, 'El pago no existe.');
            }

            if ($payment['status'] !== 'approved') {
                return $this->fail($paymentId, 'El pago no está aprobado; no se envía el recibo.');
            }

            $parentEmail = trim($payment['parent_email'] ?? '');
            if (empty($parentEmail) || !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                return $this->fail($paymentId, 'El correo del representante está vacío o no es válido: "' . $parentEmail . '".');
            }

            $athleteName = trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''));

            // 1. Generar el PDF del recibo (en memoria, sin enviarlo al navegador).
            $pdf = $this->buildReceiptPdf($paymentId, $payment);
            $pdfContent = $pdf['content'];
            $filename = $pdf['filename'];

            // 2. Construir y enviar el correo con el PDF adjunto.
            $paymentDateLabel = !empty($payment['payment_date'])
                ? date('d/m/Y', strtotime($payment['payment_date']))
                : date('d/m/Y');

            $subject = 'Recibo de Pago - ' . $payment['month'] . ' ' . $payment['year'] . ' - Escuela de Béisbol Menor Maporal';
            $body = $this->buildEmailBody($payment, $athleteName, $paymentDateLabel);

            $mailer = new Mailer();
            $sent = $mailer->sendWithAttachment($parentEmail, $subject, $body, $pdfContent, $filename);

            if (!$sent) {
                return $this->fail($paymentId, 'PHPMailer no pudo enviar el correo (ver log de errores para el detalle SMTP).');
            }

            // 3. Registrar éxito.
            $this->paymentModel->update($paymentId, [
                'email_status'  => 'sent',
                'email_sent_at' => date('Y-m-d H:i:s'),
                'email_error'   => null,
            ]);

            return ['success' => true, 'error' => null];
        } catch (\Throwable $e) {
            return $this->fail($paymentId, $e->getMessage());
        }
    }

    /**
     * Construye el cuerpo HTML del correo de recibo de pago.
     *
     * @param array  $payment
     * @param string $athleteName
     * @param string $paymentDateLabel
     * @return string
     */
    private function buildEmailBody($payment, $athleteName, $paymentDateLabel) {
        $parentName = htmlspecialchars($payment['parent_name'] ?? 'Representante');
        $athleteNameSafe = htmlspecialchars($athleteName);
        $monthYear = htmlspecialchars($payment['month'] . ' ' . $payment['year']);
        $amount = number_format($payment['amount'], 2);

        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #8B1538;'>Recibo de Pago</h2>
                <p>Hola <strong>{$parentName}</strong>,</p>
                <p>Confirmamos el pago correspondiente a <strong>{$athleteNameSafe}</strong> por el mes de <strong>{$monthYear}</strong>.</p>
                <div style='background-color: #fff8f8; border: 2px solid #8B1538; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 12px; color: #8B1538; text-transform: uppercase; letter-spacing: 0.5px;'>Monto Pagado</p>
                    <p style='margin: 4px 0 0; font-size: 24px; font-weight: bold; color: #111;'>Bs. {$amount}</p>
                </div>
                <p>Fecha de pago: <strong>{$paymentDateLabel}</strong></p>
                <p>Adjuntamos el recibo en formato PDF con el detalle completo de este pago.</p>
                <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este es un correo automático del Sistema de Gestión de la Escuela de Béisbol Menor Maporal.</p>
            </div>
        ";
    }

    /**
     * Registra un fallo en el pago (sin propagar excepciones) y lo loguea.
     *
     * @param int    $paymentId
     * @param string $errorMessage
     * @return array ['success' => false, 'error' => string]
     */
    private function fail($paymentId, $errorMessage) {
        error_log("[EmailReceiptService] Error enviando recibo del pago #{$paymentId}: {$errorMessage}");

        try {
            $this->paymentModel->update($paymentId, [
                'email_status' => 'failed',
                'email_error'  => $errorMessage,
            ]);
        } catch (\Throwable $inner) {
            error_log("[EmailReceiptService] Además, no se pudo registrar el estado de fallo: " . $inner->getMessage());
        }

        return ['success' => false, 'error' => $errorMessage];
    }
}
