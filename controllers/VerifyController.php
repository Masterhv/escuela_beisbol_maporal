<?php
require_once 'models/PdfVerification.php';

/**
 * Controlador público para verificar la autenticidad de PDFs generados.
 * No requiere autenticación — cualquier persona con el QR puede acceder.
 * No extiende Controller para evitar la inicialización de la sesión de auth.
 */
class VerifyController {

    /** @var PdfVerification */
    private $verifyModel;

    public function __construct() {
        // No llamar parent::__construct() — esta página es pública (sin auth)
        require_once 'models/PdfVerification.php';
        $this->verifyModel = new PdfVerification();
    }

    /**
     * Página pública de verificación.
     * URL: verify  o  verify?token={token}
     */
    public function index() {
        $token = trim($_GET['token'] ?? '');
        $result = null;

        $certificateStatus = null;

        if (!empty($token)) {
            $result = $this->verifyModel->verify($token);
            $certificateStatus = $this->certificateStatus($result);
        }

        // Renderizar directamente sin el layout autenticado
        extract([
            'title'  => 'Verificar Documento',
            'result' => $result,
            'certificateStatus' => $certificateStatus,
            'token'  => htmlspecialchars($token),
        ]);
        require_once 'views/verify/index.php';
    }

    /**
     * Para una constancia de pertenencia AUTÉNTICA, indica si sigue vigente y si el atleta aún figura como activo.
     *
     * Esta página es pública, así que revela lo mínimo: nunca el motivo por el que un atleta ya no está activo
     * (retirado, suspendido...) ni ningún dato suyo. El athlete_id se toma del payload firmado con HMAC (el
     * documento válido), no de la URL, por lo que nadie puede hacer que la página consulte a otro atleta.
     *
     * @param array|null $result Resultado de PdfVerification::verify()
     * @return array|null ['valid_until' => 'Y-m-d', 'is_current' => bool, 'athlete_active' => bool|null] o null si no aplica
     */
    private function certificateStatus($result) {
        if (!$result || empty($result['valid']) || empty($result['record'])) {
            return null;
        }
        if (($result['record']['report_type'] ?? '') !== 'membership_certificate') {
            return null;
        }

        $payload = $result['payload'] ?? [];
        $validUntil = $payload['valid_until'] ?? null;
        if (!$validUntil || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $validUntil)) {
            return null;
        }

        // El último día de vigencia (valid_until = hoy) aún es vigente.
        $status = [
            'valid_until'    => $validUntil,
            'is_current'     => $validUntil >= date('Y-m-d'),
            'athlete_active' => null,
        ];

        $athleteId = (int)($payload['athlete_id'] ?? 0);
        if ($athleteId > 0) {
            try {
                require_once 'models/Athlete.php';
                $athlete = (new Athlete())->findById($athleteId);
                $status['athlete_active'] = $athlete ? ($athlete['status'] === 'active' && empty($athlete['withdrawn_at'])) : false;
            } catch (\Throwable $e) {
                error_log('[VerifyController] No se pudo consultar el estado del atleta: ' . $e->getMessage());
            }
        }

        return $status;
    }
}
