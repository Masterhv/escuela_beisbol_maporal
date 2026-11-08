<?php
require_once 'services/Mailer.php';
require_once 'services/WhatsAppClient.php';
require_once 'services/PdfGenerator.php';
require_once 'models/Athlete.php';

use App\Core\Traits\PdfGenerator;

/**
 * Servicio de la inscripción de atletas.
 *
 * - Genera los PDFs (planilla de inscripción con datos, planilla en blanco y ficha técnica).
 * - Envía la planilla al representante por correo y por WhatsApp, y guarda el estado de cada canal.
 *
 * Los envíos nunca deben bloquear ni revertir el registro del atleta: cualquier error se captura,
 * se guarda en el propio atleta y se registra con error_log(). Cada canal es independiente.
 */
class AthleteEnrollmentService {
    use PdfGenerator;

    /**
     * @var Athlete
     */
    private $athleteModel;

    public function __construct() {
        $this->athleteModel = new Athlete();
    }

    // ------------------------------------------------------------------
    // Generación de PDFs
    // ------------------------------------------------------------------

    /**
     * Genera la planilla de inscripción con los datos de un atleta.
     *
     * @param int        $athleteId
     * @param array|null $athlete   Datos del atleta (opcional; si es null se consultan).
     * @return array ['content' => string, 'filename' => string]
     * @throws \RuntimeException Si el atleta no existe.
     */
    public function buildEnrollmentPdf($athleteId, $athlete = null) {
        $athlete = $athlete ?: $this->athleteModel->findById($athleteId);
        if (!$athlete) {
            throw new \RuntimeException('El atleta no existe.');
        }

        $filenameBase = 'Planilla_Inscripcion_' . $this->safeName($athlete) . '_' . $athlete['id'];

        $verificationPayload = [
            'athlete_name'      => $athlete['first_name'] . ' ' . $athlete['last_name'],
            'athlete_id_card'   => $athlete['id_card'] ?? 'N/A',
            'athlete_category'  => $athlete['category'],
            'parent_name'       => $athlete['parent_name'],
            'registration_date' => $athlete['registration_date'],
        ];

        $html = $this->renderPdfView('enrollment_form', [
            'title'   => 'Planilla de Inscripción',
            'Athlete' => $athlete,
        ], $verificationPayload, 'enrollment_form', $filenameBase);

        return [
            'content'  => $this->generatePdfString($html),
            'filename' => $filenameBase . '.pdf',
        ];
    }

    /**
     * Genera la planilla de inscripción en blanco (sin datos), para imprimir y llenar a mano.
     * No registra verificación porque no hay datos que verificar.
     *
     * @return array ['content' => string, 'filename' => string]
     */
    public function buildBlankEnrollmentPdf() {
        $filenameBase = 'Planilla_Inscripcion_en_blanco';

        $html = $this->renderPdfView('enrollment_form', [
            'title' => 'Planilla de Inscripción',
            'blank' => true,
        ], [], 'enrollment_form', $filenameBase, false);

        return [
            'content'  => $this->generatePdfString($html),
            'filename' => $filenameBase . '.pdf',
        ];
    }

    /**
     * Genera la ficha técnica del atleta (descarga interna; no se envía al representante).
     * Usa los mismos datos de verificación que ReportsController::generateAthleteCard().
     *
     * @param int        $athleteId
     * @param array|null $athlete
     * @return array ['content' => string, 'filename' => string]
     * @throws \RuntimeException Si el atleta no existe.
     */
    public function buildAthleteCardPdf($athleteId, $athlete = null) {
        $athlete = $athlete ?: $this->athleteModel->findById($athleteId);
        if (!$athlete) {
            throw new \RuntimeException('El atleta no existe.');
        }

        $filenameBase = 'Ficha_Atleta_' . $this->safeName($athlete);

        $verificationPayload = [
            'athlete_name'      => $athlete['first_name'] . ' ' . $athlete['last_name'],
            'athlete_id_card'   => $athlete['id_card'] ?? 'N/A',
            'athlete_category'  => $athlete['category'],
            'athlete_dob'       => $athlete['date_of_birth'],
            'parent_name'       => $athlete['parent_name'],
            'parent_phone'      => $athlete['parent_phone'],
            'registration_date' => $athlete['registration_date'],
        ];

        $html = $this->renderPdfView('athlete_card', [
            'title'   => 'Ficha del Atleta',
            'Athlete' => $athlete,
        ], $verificationPayload, 'athlete_card', $filenameBase);

        return [
            'content'  => $this->generatePdfString($html),
            'filename' => $filenameBase . '.pdf',
        ];
    }

    // ------------------------------------------------------------------
    // Envíos
    // ------------------------------------------------------------------

    /**
     * Envía la planilla por correo y por WhatsApp. Genera el PDF una sola vez
     * y lo reutiliza en ambos canales; el fallo de uno no afecta al otro.
     *
     * @param int $athleteId
     * @return array ['email' => array, 'whatsapp' => array]
     */
    public function sendAll($athleteId) {
        try {
            $athlete = $this->athleteModel->findById($athleteId);
            if (!$athlete) {
                return [
                    'email'    => ['success' => false, 'error' => 'El atleta no existe.'],
                    'whatsapp' => ['success' => false, 'error' => 'El atleta no existe.'],
                ];
            }

            $pdf = $this->buildEnrollmentPdf($athleteId, $athlete);
        } catch (\Throwable $e) {
            $emailResult = $this->fail($athleteId, 'email', 'No se pudo generar la planilla: ' . $e->getMessage());
            $waResult = $this->fail($athleteId, 'whatsapp', 'No se pudo generar la planilla: ' . $e->getMessage());
            return ['email' => $emailResult, 'whatsapp' => $waResult];
        }

        return [
            'email'    => $this->deliverEmail($athlete, $pdf),
            'whatsapp' => $this->deliverWhatsApp($athlete, $pdf),
        ];
    }

    /**
     * Envía la planilla solo por correo (reenvío manual).
     *
     * @param int $athleteId
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function sendEmail($athleteId) {
        try {
            $athlete = $this->athleteModel->findById($athleteId);
            if (!$athlete) {
                return ['success' => false, 'error' => 'El atleta no existe.'];
            }
            return $this->deliverEmail($athlete, $this->buildEnrollmentPdf($athleteId, $athlete));
        } catch (\Throwable $e) {
            return $this->fail($athleteId, 'email', $e->getMessage());
        }
    }

    /**
     * Envía la planilla solo por WhatsApp (reenvío manual).
     *
     * @param int $athleteId
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function sendWhatsApp($athleteId) {
        try {
            $athlete = $this->athleteModel->findById($athleteId);
            if (!$athlete) {
                return ['success' => false, 'error' => 'El atleta no existe.'];
            }
            return $this->deliverWhatsApp($athlete, $this->buildEnrollmentPdf($athleteId, $athlete));
        } catch (\Throwable $e) {
            return $this->fail($athleteId, 'whatsapp', $e->getMessage());
        }
    }

    /**
     * Entrega la planilla por correo y registra el resultado.
     */
    private function deliverEmail(array $athlete, array $pdf) {
        $athleteId = $athlete['id'];

        try {
            $parentEmail = trim($athlete['parent_email'] ?? '');
            if (empty($parentEmail) || !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                return $this->fail($athleteId, 'email', 'El correo del representante está vacío o no es válido: "' . $parentEmail . '".');
            }

            $athleteName = trim($athlete['first_name'] . ' ' . $athlete['last_name']);
            $subject = 'Planilla de Inscripción - ' . $athleteName . ' - Escuela de Béisbol Menor Maporal';
            $body = $this->buildEmailBody($athlete, $athleteName);

            $mailer = new Mailer();
            $sent = $mailer->sendWithAttachment($parentEmail, $subject, $body, $pdf['content'], $pdf['filename']);

            if (!$sent) {
                return $this->fail($athleteId, 'email', 'PHPMailer no pudo enviar el correo (ver log de errores para el detalle SMTP).');
            }

            $this->athleteModel->update($athleteId, [
                'enrollment_email_status'  => 'sent',
                'enrollment_email_sent_at' => date('Y-m-d H:i:s'),
                'enrollment_email_error'   => null,
            ]);

            return ['success' => true, 'error' => null];
        } catch (\Throwable $e) {
            return $this->fail($athleteId, 'email', $e->getMessage());
        }
    }

    /**
     * Entrega la planilla por WhatsApp y registra el resultado.
     * Usa la plantilla 'enrollment_template_name' (header Documento, 3 variables).
     */
    private function deliverWhatsApp(array $athlete, array $pdf) {
        $athleteId = $athlete['id'];

        try {
            $client = new WhatsAppClient();

            $toE164 = $client->normalizePhone($athlete['parent_phone'] ?? '');
            if (!$toE164) {
                return $this->fail($athleteId, 'whatsapp', 'El teléfono del representante está vacío o no tiene un formato reconocible: "' . ($athlete['parent_phone'] ?? '') . '".');
            }

            $config = require 'config/whatsapp.php';
            $templateName = $config['enrollment_template_name'] ?? null;
            $templateLang = $config['enrollment_template_lang'] ?? null;
            if (empty($templateName)) {
                return $this->fail($athleteId, 'whatsapp', 'Falta configurar "enrollment_template_name" en config/whatsapp.php.');
            }

            $mediaId = $client->uploadMedia($pdf['content'], 'application/pdf', $pdf['filename']);

            // Variables del body: {{1}} representante, {{2}} atleta, {{3}} fecha de inscripción.
            $bodyParams = [
                trim($athlete['parent_name'] ?? '') ?: 'Representante',
                trim($athlete['first_name'] . ' ' . $athlete['last_name']),
                !empty($athlete['registration_date']) ? date('d/m/Y', strtotime($athlete['registration_date'])) : date('d/m/Y'),
            ];

            $client->sendDocumentTemplate($toE164, $mediaId, $pdf['filename'], $bodyParams, $templateName, $templateLang);

            $this->athleteModel->update($athleteId, [
                'enrollment_whatsapp_status'  => 'sent',
                'enrollment_whatsapp_sent_at' => date('Y-m-d H:i:s'),
                'enrollment_whatsapp_error'   => null,
            ]);

            return ['success' => true, 'error' => null];
        } catch (\Throwable $e) {
            return $this->fail($athleteId, 'whatsapp', $e->getMessage());
        }
    }

    // ------------------------------------------------------------------
    // Utilidades
    // ------------------------------------------------------------------

    /**
     * Cuerpo HTML del correo de inscripción, con el mismo estilo que los demás correos del sistema.
     */
    private function buildEmailBody(array $athlete, $athleteName) {
        $parentName = htmlspecialchars($athlete['parent_name'] ?? 'Representante');
        $athleteSafe = htmlspecialchars($athleteName);
        $category = htmlspecialchars($athlete['category'] ?? '');
        $date = !empty($athlete['registration_date'])
            ? date('d/m/Y', strtotime($athlete['registration_date']))
            : date('d/m/Y');

        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #8B1538;'>Bienvenido a la Escuela de Béisbol Menor Maporal</h2>
                <p>Hola <strong>{$parentName}</strong>,</p>
                <p>Confirmamos la inscripción de <strong>{$athleteSafe}</strong> en la categoría <strong>{$category}</strong>.</p>
                <div style='background-color: #fff8f8; border: 2px solid #8B1538; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 12px; color: #8B1538; text-transform: uppercase; letter-spacing: 0.5px;'>Fecha de inscripción</p>
                    <p style='margin: 4px 0 0; font-size: 24px; font-weight: bold; color: #111;'>{$date}</p>
                </div>
                <p>Adjuntamos la planilla de inscripción en formato PDF. Consérvala para futuras consultas.</p>
                <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este es un correo automático del Sistema de Gestión de la Escuela de Béisbol Menor Maporal.</p>
            </div>
        ";
    }

    /**
     * Nombre de atleta apto para nombres de archivo.
     */
    private function safeName(array $athlete) {
        $name = trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''));
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        return trim(preg_replace('/_+/', '_', $name), '_') ?: 'Atleta';
    }

    /**
     * Registra el fallo de un canal en el atleta (sin propagar excepciones) y lo loguea.
     *
     * @param int    $athleteId
     * @param string $channel 'email' o 'whatsapp'
     * @param string $errorMessage
     * @return array ['success' => false, 'error' => string]
     */
    private function fail($athleteId, $channel, $errorMessage) {
        error_log("[AthleteEnrollmentService] Error enviando inscripción del atleta #{$athleteId} por {$channel}: {$errorMessage}");

        try {
            $this->athleteModel->update($athleteId, [
                "enrollment_{$channel}_status" => 'failed',
                "enrollment_{$channel}_error"  => $errorMessage,
            ]);
        } catch (\Throwable $inner) {
            error_log('[AthleteEnrollmentService] Además, no se pudo registrar el estado de fallo: ' . $inner->getMessage());
        }

        return ['success' => false, 'error' => $errorMessage];
    }
}
