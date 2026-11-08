<?php
require_once 'services/PdfGenerator.php';
require_once 'models/Athlete.php';
require_once 'models/Payment.php';

use App\Core\Traits\PdfGenerator;

/**
 * Servicio de la solvencia de retiro de un atleta.
 *
 * - Comprueba si el atleta puede recibirla (no tiene pagos pendientes).
 * - La emite de forma atómica (el atleta pasa a 'inactive' y se guardan los datos del retiro).
 * - Genera el PDF, tanto al emitirla como al reimprimirla.
 *
 * La regla de negocio y la transacción viven en el modelo Athlete (checkClearanceEligibility,
 * issueClearance); este servicio solo orquesta y construye el documento.
 */
class AthleteClearanceService {
    use PdfGenerator;

    /** Tipo de documento registrado en pdf_verifications. */
    public const REPORT_TYPE = 'withdrawal_clearance';

    /** Meses en español -> número, para ordenar los pagos (el mes se guarda como texto). */
    private const MONTHS = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
        'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

    /** @var Athlete */
    private $athleteModel;

    /** @var Payment */
    private $paymentModel;

    public function __construct() {
        $this->athleteModel = new Athlete();
        $this->paymentModel = new Payment();
    }

    /**
     * Elegibilidad del atleta para la solvencia (ver Athlete::checkClearanceEligibility).
     *
     * @param int $athleteId
     * @return array
     */
    public function checkEligibility($athleteId) {
        return $this->athleteModel->checkClearanceEligibility($athleteId);
    }

    /**
     * Emite la solvencia. Es atómico: si algo falla, el atleta no cambia.
     *
     * @param int    $athleteId
     * @param string $issuedBy Nombre de quien la emite.
     * @return array Resultado de Athlete::issueClearance().
     */
    public function issue($athleteId, $issuedBy) {
        return $this->athleteModel->issueClearance($athleteId, $issuedBy);
    }

    /**
     * Genera el PDF de la solvencia de un atleta que YA tiene una emitida.
     * Usa siempre el número y la fecha GUARDADOS (nunca la fecha actual), por lo que cada
     * reimpresión es idéntica a la original.
     *
     * @param int        $athleteId
     * @param array|null $athlete   Datos del atleta (opcional; si es null se consultan).
     * @return array ['content' => string, 'filename' => string]
     * @throws \RuntimeException Si el atleta no existe o no tiene solvencia emitida.
     */
    public function buildClearancePdf($athleteId, $athlete = null) {
        $athlete = $athlete ?: $this->athleteModel->findById($athleteId);
        if (!$athlete) {
            throw new \RuntimeException('El atleta no existe.');
        }
        if (empty($athlete['withdrawn_at']) || empty($athlete['clearance_number'])) {
            throw new \RuntimeException('El atleta no tiene una solvencia de retiro emitida.');
        }

        $summary = $this->paymentSummary($athleteId);
        $athleteName = trim($athlete['first_name'] . ' ' . $athlete['last_name']);
        $filenameBase = 'Solvencia_Retiro_' . $athlete['clearance_number'] . '_' . $this->safeName($athlete);

        $verificationPayload = [
            'clearance_number' => $athlete['clearance_number'],
            'athlete_name'     => $athleteName,
            'athlete_id_card'  => $athlete['id_card'] ?? 'N/A',
            'athlete_category' => $athlete['category'],
            'parent_name'      => $athlete['parent_name'],
            'withdrawn_at'     => $athlete['withdrawn_at'],
            'issued_by'        => $athlete['clearance_issued_by'] ?? '',
            'paid_payments'    => $summary['paid_count'],
        ];

        $html = $this->renderPdfView('clearance', [
            'title'   => 'Solvencia de Retiro',
            'Athlete' => $athlete,
            'summary' => $summary,
        ], $verificationPayload, self::REPORT_TYPE, $filenameBase);

        return [
            'content'  => $this->generatePdfString($html),
            'filename' => $filenameBase . '.pdf',
        ];
    }

    /**
     * Resumen de los pagos del atleta para el documento: cuántos pagos aprobados tiene, el total
     * y el último mes pagado (ordenado por año y mes real, no alfabéticamente).
     *
     * @param int $athleteId
     * @return array ['paid_count' => int, 'paid_total' => float, 'last_period' => string|null, 'total_count' => int]
     */
    public function paymentSummary($athleteId) {
        $payments = $this->paymentModel->getPaymentsByAthleteId($athleteId);
        $paid = array_values(array_filter($payments, fn($p) => ($p['status'] ?? '') === 'approved'));

        $total = 0.0;
        $last = null; $lastKey = -1;
        foreach ($paid as $p) {
            $total += (float)$p['amount'];
            $monthNum = self::MONTHS[mb_strtolower(trim($p['month'] ?? ''))] ?? 0;
            $key = ((int)$p['year']) * 100 + $monthNum;
            if ($key > $lastKey) {
                $lastKey = $key;
                $last = trim(($p['month'] ?? '') . ' ' . ($p['year'] ?? ''));
            }
        }

        return [
            'paid_count'  => count($paid),
            'paid_total'  => $total,
            'last_period' => $last,
            'total_count' => count($payments),
        ];
    }

    /**
     * Nombre de atleta apto para nombres de archivo.
     */
    private function safeName(array $athlete) {
        $name = trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''));
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        return trim(preg_replace('/_+/', '_', $name), '_') ?: 'Atleta';
    }
}
