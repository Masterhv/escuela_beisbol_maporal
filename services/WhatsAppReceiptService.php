<?php
require_once 'services/WhatsAppClient.php';
require_once 'services/EmailReceiptService.php';
require_once 'models/Payment.php';

/**
 * Servicio que orquesta el envío del recibo de pago por WhatsApp usando la
 * plantilla aprobada 'recibo_pago_mes_pdf' (header Documento, cuerpo con 4 variables,
 * footer "Mensaje automático, no responder"):
 *
 *   "Hola {{1}}, hemos registrado tu pago correspondiente al mes de {{2}} con éxito en
 *    la Escuela de Beisbol Menor Maporal. Recibo N°: {{3}}. Monto: {{4}}. Adjuntamos tu
 *    recibo en PDF. Gracias por tu confianza."
 *
 * {{1}} representante, {{2}} mes y año ("Noviembre 2025"), {{3}} N.° de recibo, {{4}} monto.
 *
 * Pasos: genera el PDF del recibo (el mismo que se envía por correo), lo sube a
 * Meta para obtener un media_id y envía la plantilla con el PDF adjunto.
 *
 * El envío nunca debe bloquear ni revertir el guardado del pago: cualquier
 * error se captura, se registra en el pago y se loguea con error_log().
 */
class WhatsAppReceiptService {
    /**
     * @var Payment
     */
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new Payment();
    }

    /**
     * Envía la confirmación de pago por WhatsApp al representante del atleta.
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

            $client = new WhatsAppClient();

            $toE164 = $client->normalizePhone($payment['parent_phone'] ?? '');
            if (!$toE164) {
                return $this->fail($paymentId, 'El teléfono del representante está vacío o no tiene un formato reconocible: "' . ($payment['parent_phone'] ?? '') . '".');
            }

            // 1. Generar el PDF del recibo (el mismo documento que se envía por correo).
            $pdf = (new EmailReceiptService())->buildReceiptPdf($paymentId, $payment);

            // 2. Subir el PDF a Meta para obtener un media_id.
            $mediaId = $client->uploadMedia($pdf['content'], 'application/pdf', $pdf['filename']);

            // 3. Enviar la plantilla con el PDF adjunto en el header.
            // Variables del cuerpo: {{1}} representante, {{2}} mes y año, {{3}} N.° de recibo, {{4}} monto.
            $bodyParams = $this->buildBodyParams($payment);

            $client->sendDocumentTemplate($toE164, $mediaId, $pdf['filename'], $bodyParams);

            $this->paymentModel->update($paymentId, [
                'whatsapp_status'  => 'sent',
                'whatsapp_sent_at' => date('Y-m-d H:i:s'),
                'whatsapp_error'   => null,
            ]);

            return ['success' => true, 'error' => null];
        } catch (\Throwable $e) {
            return $this->fail($paymentId, $e->getMessage());
        }
    }

    /**
     * Construye las 4 variables del cuerpo de la plantilla 'recibo_pago_mes_pdf', en su orden.
     *
     * Meta rechaza una variable vacía o con saltos de línea, por eso se limpian los textos y se
     * usa un valor de respaldo cuando falta un dato.
     *
     * @param array $payment Pago con los datos del atleta (Payment::findPaymentWithAthleteById).
     * @return array [representante, "Mes Año", N.° de recibo, "Bs. monto"]
     */
    public function buildBodyParams(array $payment) {
        $clean = fn($v) => trim(preg_replace('/\s+/', ' ', (string)$v));

        $parent = $clean($payment['parent_name'] ?? '') ?: 'Representante';
        $period = $clean(($payment['month'] ?? '') . ' ' . ($payment['year'] ?? '')) ?: 'el período indicado';

        return [
            $parent,
            $period,
            (string)$payment['id'],
            'Bs. ' . number_format((float)$payment['amount'], 2),
        ];
    }

    /**
     * Registra un fallo en el pago (sin propagar excepciones) y lo loguea.
     *
     * @param int    $paymentId
     * @param string $errorMessage
     * @return array ['success' => false, 'error' => string]
     */
    private function fail($paymentId, $errorMessage) {
        error_log("[WhatsAppReceiptService] Error enviando recibo del pago #{$paymentId}: {$errorMessage}");

        try {
            $this->paymentModel->update($paymentId, [
                'whatsapp_status' => 'failed',
                'whatsapp_error'  => $errorMessage,
            ]);
        } catch (\Throwable $inner) {
            // Si ni siquiera se puede registrar el error, solo lo logueamos.
            error_log("[WhatsAppReceiptService] Además, no se pudo registrar el estado de fallo: " . $inner->getMessage());
        }

        return ['success' => false, 'error' => $errorMessage];
    }
}
