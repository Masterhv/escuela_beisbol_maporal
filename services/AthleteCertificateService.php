<?php
require_once 'services/PdfGenerator.php';
require_once 'services/Mailer.php';
require_once 'models/MembershipCertificate.php';
require_once 'models/Athlete.php';

use App\Core\Traits\PdfGenerator;

/**
 * Servicio de la constancia de pertenencia a la escuela.
 *
 * - Emite (u obtiene la vigente) la constancia de un atleta ACTIVO. La regla de negocio y la transacción viven
 *   en el modelo MembershipCertificate; este servicio solo orquesta y construye el documento.
 * - Genera el PDF siempre a partir de la fila GUARDADA (número, fecha de emisión y vigencia), nunca con la
 *   fecha actual, de modo que cada reimpresión sea idéntica a la original.
 * - Envía la constancia por correo al representante.
 *
 * WhatsApp no está incluido: requiere una plantilla propia aprobada por Meta (ver DOCUMENTACION_COMPLETA.md).
 */
class AthleteCertificateService {
    use PdfGenerator;

    /** Tipo de documento registrado en pdf_verifications. */
    public const REPORT_TYPE = 'membership_certificate';

    /** @var MembershipCertificate */
    private $certificateModel;

    /** @var Athlete */
    private $athleteModel;

    public function __construct() {
        $this->certificateModel = new MembershipCertificate();
        $this->athleteModel = new Athlete();
    }

    /**
     * Elegibilidad del atleta (solo activo y no retirado) y, si lo es, la constancia vigente si ya existe.
     *
     * @param int $athleteId
     * @return array ['eligibility' => array, 'valid_certificate' => array|null]
     */
    public function checkStatus($athleteId) {
        $eligibility = $this->certificateModel->checkEligibility($athleteId);
        return [
            'eligibility'       => $eligibility,
            'valid_certificate' => $eligibility['eligible'] ? $this->certificateModel->findLatestValid($athleteId) : null,
        ];
    }

    /**
     * Emite la constancia (o reutiliza la vigente). Atómico: ver MembershipCertificate::issue().
     *
     * @param int    $athleteId
     * @param string $issuedBy
     * @return array Resultado de MembershipCertificate::issue()
     */
    public function issue($athleteId, $issuedBy) {
        return $this->certificateModel->issue($athleteId, $issuedBy);
    }

    /**
     * Genera el PDF de una constancia ya emitida.
     *
     * @param int|array $certificate ID de la constancia o su fila completa.
     * @return array ['content' => string, 'filename' => string, 'certificate' => array, 'athlete' => array]
     * @throws \RuntimeException Si la constancia o el atleta no existen.
     */
    public function buildCertificatePdf($certificate) {
        if (!is_array($certificate)) {
            $certificate = $this->certificateModel->findById($certificate);
        }
        if (!$certificate) {
            throw new \RuntimeException('La constancia no existe.');
        }

        $athlete = $this->athleteModel->findById($certificate['athlete_id']);
        if (!$athlete) {
            throw new \RuntimeException('El atleta de la constancia no existe.');
        }

        $athleteName = trim($athlete['first_name'] . ' ' . $athlete['last_name']);
        $filenameBase = 'Constancia_' . $certificate['certificate_number'] . '_' . $this->safeName($athleteName);

        // Los datos que identifican el documento. 'athlete_id' permite a la página pública de verificación
        // avisar si el atleta ya no figura como activo.
        $verificationPayload = [
            'certificate_number' => $certificate['certificate_number'],
            'athlete_id'         => (int)$athlete['id'],
            'athlete_name'       => $athleteName,
            'athlete_id_card'    => $athlete['id_card'] ?? 'N/A',
            'athlete_category'   => $athlete['category'],
            'registration_date'  => $athlete['registration_date'],
            'parent_name'        => $athlete['parent_name'],
            'issued_at'          => $certificate['issued_at'],
            'valid_until'        => $certificate['valid_until'],
            'issued_by'          => $certificate['issued_by'],
        ];

        $html = $this->renderPdfView('membership_certificate', [
            'title'       => 'Constancia de Pertenencia',
            'Athlete'     => $athlete,
            'Certificate' => $certificate,
        ], $verificationPayload, self::REPORT_TYPE, $filenameBase);

        return [
            'content'     => $this->generatePdfString($html),
            'filename'    => $filenameBase . '.pdf',
            'certificate' => $certificate,
            'athlete'     => $athlete,
        ];
    }

    /**
     * Envía una constancia ya emitida por correo al representante del atleta. NUNCA lanza: cualquier error
     * se registra en la propia constancia (email_status/email_error) y se devuelve.
     *
     * @param int|array $certificate ID de la constancia o su fila completa.
     * @return array ['success' => bool, 'error' => string|null, 'sent_to' => string|null]
     */
    public function sendByEmail($certificate) {
        $certificateId = is_array($certificate) ? ($certificate['id'] ?? null) : $certificate;
        $to = null;

        try {
            $pdf = $this->buildCertificatePdf($certificate);
            $cert = $pdf['certificate'];
            $athlete = $pdf['athlete'];
            $certificateId = $cert['id'];

            $to = trim($athlete['parent_email'] ?? '');
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return $this->fail($certificateId, $to, 'El correo del representante está vacío o no es válido: "' . $to . '".');
            }

            $athleteName = trim($athlete['first_name'] . ' ' . $athlete['last_name']);
            $subject = 'Constancia de Pertenencia - ' . $athleteName . ' - Escuela de Béisbol Menor Maporal';
            $body = $this->buildEmailBody($athlete, $athleteName, $cert);

            $mailer = new Mailer();
            if (!$mailer->sendWithAttachment($to, $subject, $body, $pdf['content'], $pdf['filename'])) {
                return $this->fail($certificateId, $to, 'PHPMailer no pudo enviar el correo (ver log de errores para el detalle SMTP).');
            }

            $this->certificateModel->markEmailResult($certificateId, 'sent', $to);
            return ['success' => true, 'error' => null, 'sent_to' => $to];
        } catch (\Throwable $e) {
            return $this->fail($certificateId, $to, $e->getMessage());
        }
    }

    /**
     * Cuerpo HTML del correo, con el mismo estilo que los demás correos del sistema.
     */
    private function buildEmailBody(array $athlete, $athleteName, array $certificate) {
        $parent = htmlspecialchars($athlete['parent_name'] ?? 'Representante');
        $name = htmlspecialchars($athleteName);
        $number = htmlspecialchars($certificate['certificate_number']);
        $issued = date('d/m/Y', strtotime($certificate['issued_at']));
        $until = date('d/m/Y', strtotime($certificate['valid_until']));
        $days = (int)MembershipCertificate::VALIDITY_DAYS;

        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #8B1538;'>Constancia de Pertenencia</h2>
                <p>Hola <strong>{$parent}</strong>,</p>
                <p>Adjuntamos la constancia que acredita que <strong>{$name}</strong> forma parte de la Escuela de Béisbol Menor Maporal.</p>
                <div style='background-color: #fff8f8; border: 2px solid #8B1538; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0;'>
                    <p style='margin: 0; font-size: 12px; color: #8B1538; text-transform: uppercase; letter-spacing: 0.5px;'>N.° de constancia</p>
                    <p style='margin: 4px 0 0; font-size: 22px; font-weight: bold; color: #111;'>{$number}</p>
                </div>
                <p>Fecha de emisión: <strong>{$issued}</strong></p>
                <p>Válida por {$days} días, hasta el <strong>{$until}</strong>. Pasada esa fecha, solicita una nueva en la escuela.</p>
                <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este es un correo automático del Sistema de Gestión de la Escuela de Béisbol Menor Maporal.</p>
            </div>
        ";
    }

    /**
     * Registra el fallo en la constancia (sin propagar excepciones) y lo loguea.
     *
     * @return array ['success' => false, 'error' => string, 'sent_to' => string|null]
     */
    private function fail($certificateId, $to, $message) {
        error_log("[AthleteCertificateService] Error enviando la constancia #{$certificateId}: {$message}");
        try {
            if ($certificateId) {
                $this->certificateModel->markEmailResult($certificateId, 'failed', $to, $message);
            }
        } catch (\Throwable $inner) {
            error_log('[AthleteCertificateService] Además, no se pudo registrar el estado de fallo: ' . $inner->getMessage());
        }
        return ['success' => false, 'error' => $message, 'sent_to' => $to];
    }

    /**
     * Texto apto para nombres de archivo.
     */
    private function safeName($name) {
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$name);
        return trim(preg_replace('/_+/', '_', $name), '_') ?: 'Atleta';
    }
}
