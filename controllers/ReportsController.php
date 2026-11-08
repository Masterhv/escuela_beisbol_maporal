<?php
require_once 'controllers/Controller.php';
require_once 'vendor/autoload.php'; // Carga Dompdf
require_once 'services/PdfGenerator.php';

use App\Core\Traits\PdfGenerator;

/**
 * Controlador para la generación de reportes.
 * Utiliza el trait PdfGenerator para crear los archivos PDF.
 */
class ReportsController extends Controller {
    
    // Usa el trait para tener acceso a los métodos de generación de PDF
    use PdfGenerator;

    /**
     * @var Athlete Instancia del modelo Athlete.
     */
    protected $athleteModel;

    /**
     * @var Payment Instancia del modelo Payment.
     */
    protected $paymentModel;

    /**
     * Constructor. Carga los modelos necesarios.
     */
    public function __construct() {
        parent::__construct();
        require_once 'models/Athlete.php';
        require_once 'models/Payment.php';
        require_once 'models/PaymentWorkflow.php';
        $this->athleteModel = new Athlete();
        $this->paymentModel = new Payment();
    }
    
    /**
     * Muestra la página principal de reportes.
     * Permite al usuario seleccionar el tipo de reporte y aplicar filtros.
     * Muestra una vista previa de los datos del reporte.
     */
    public function index() {
        // Los reportes cubren a TODA la escuela (atletas, finanzas): son del personal. Un representante
        // consulta el estado de cuenta de sus atletas en Pagos.
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        $reportType = $_GET['report_type'] ?? 'athlete_list';
        $filters = $_GET;
        $data = [];

        // Si se ha aplicado un filtro, se obtienen los datos correspondientes
        if (isset($_GET['filter'])) {
            $filters = $this->normalizeDateFilters($filters);
            if ($reportType === 'financial') {
                $filters = $this->normalizeFinancialFilters($filters);
            }
            switch ($reportType) {
                case 'athlete_list':
                    $data['athletes'] = $this->athleteModel->getAthletesWithFilters($filters);
                    $data['stats'] = $this->athleteModel->getAthleteStats();
                    break;
                case 'financial':
                    $data['payments'] = $this->paymentModel->getPaymentsWithAthletes($filters);
                    $data['stats'] = $this->paymentModel->getPaymentStats($filters);
                    $startDate = $filters['start_date'] ?? date('Y-01-01');
                    $endDate = $filters['end_date'] ?? date('Y-m-d');
                    $data['monthlyRevenue'] = $this->paymentModel->getMonthlyRevenue($startDate, $endDate);
                    break;
                case 'athlete_card':
                    if (!empty($filters['athlete_id'])) {
                        $data['Athlete'] = $this->athleteModel->findById($filters['athlete_id']);
                    }
                    break;
                case 'payment_history':
                    if (!empty($filters['athlete_id'])) {
                        $data['Athlete'] = $this->athleteModel->findById($filters['athlete_id']);
                        $data['payments'] = $this->paymentModel->getPaymentsByAthleteId($filters['athlete_id']);
                    }
                    break;
            }
        }

        $this->view->render('reports/index', [
            'title' => 'Generar Reportes',
            'reportType' => $reportType,
            'filters' => $filters,
            'data' => $data
        ]);
    }

    /**
     * Procesa la solicitud de generación de un reporte en PDF.
     * Llama al método de generación correspondiente según el tipo de reporte.
     */
    public function generate() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);
        
        $reportType = $_POST['report_type'] ?? 'athlete_list';
        $filters = $_POST;
        $filters = $this->normalizeDateFilters($filters);

        switch ($reportType) {
            case 'athlete_card':
                $this->generateAthleteCard($filters['athlete_id'] ?? null);
                break;
            case 'athlete_list':
                $this->generateAthleteList($filters);
                break;
            case 'payment_history':
                $this->generatePaymentHistory($filters['athlete_id'] ?? null);
                break;
            case 'financial':
                $filters = $this->normalizeFinancialFilters($filters);
                $this->generateFinancialReport($filters);
                break;
        }
    }

    /**
     * Genera el PDF de la ficha de un atleta.
     * @param int|null $athleteId El ID del atleta.
     */
    private function generateAthleteCard($athleteId) {
        if (!$athleteId) return;
        $Athlete = $this->athleteModel->findById($athleteId);
        
        $athleteName = $Athlete['first_name'] . '_' . $Athlete['last_name'];
        $athleteName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $athleteName);
        $athleteName = preg_replace('/_+/', '_', $athleteName);
        $filename = 'Ficha_Atleta_' . $athleteName;

        // Payload de verificación con los datos clave del documento
        $verificationPayload = [
            'athlete_name'      => $Athlete['first_name'] . ' ' . $Athlete['last_name'],
            'athlete_id_card'   => $Athlete['id_card'] ?? 'N/A',
            'athlete_category'  => $Athlete['category'],
            'athlete_dob'       => $Athlete['date_of_birth'],
            'parent_name'       => $Athlete['parent_name'],
            'parent_phone'      => $Athlete['parent_phone'],
            'registration_date' => $Athlete['registration_date'],
        ];

        $html = $this->renderPdfView('athlete_card', [
            'title'  => 'Ficha del Atleta',
            'Athlete' => $Athlete
        ], $verificationPayload, 'athlete_card', $filename);
        $this->generatePdf($html, $filename);
    }

    /**
     * Genera el PDF de la lista de atletas.
     * @param array $filters Filtros aplicados a la lista.
     */
    private function generateAthleteList($filters) {
        $athletes = $this->athleteModel->getAthletesWithFilters($filters);

        // Ordenar alfabéticamente por nombre
        usort($athletes, function($a, $b) {
            return strcasecmp($a['first_name'], $b['first_name']);
        });

        // Traduce el estado de los atletas para el reporte
        foreach ($athletes as &$Athlete) {
            switch ($Athlete['status']) {
                case 'active': $Athlete['status'] = 'Activo'; break;
                case 'inactive': $Athlete['status'] = 'Inactivo'; break;
                case 'retired': $Athlete['status'] = 'Retirado'; break;
                default: $Athlete['status'] = ucfirst($Athlete['status']); break;
            }
        }

        $filename = 'Lista_Atletas';

        $verificationPayload = [
            'total_athletes'  => count($athletes),
            'filters_applied' => implode(', ', array_filter([
                !empty($filters['category']) ? 'Categoría: ' . $filters['category'] : '',
                !empty($filters['status'])   ? 'Estado: '    . $filters['status']   : '',
            ])) ?: 'Ninguno',
        ];

        $html = $this->renderPdfView('athlete_list', [
            'title'    => 'Lista de Atletas',
            'athletes' => $athletes,
            'filters'  => $filters
        ], $verificationPayload, 'athlete_list', $filename);
        $this->generatePdf($html, $filename, 'A4', 'landscape');
    }

    /**
     * Genera el PDF del historial de pagos de un atleta.
     * @param int|null $athleteId El ID del atleta.
     */
    private function generatePaymentHistory($athleteId) {
        if (!$athleteId) return;
        $Athlete = $this->athleteModel->findById($athleteId);
        $payments = $this->paymentModel->getPaymentsByAthleteId($athleteId);

        // Traduce el estado de los pagos para el reporte
        foreach ($payments as &$payment) {
            switch ($payment['status']) {
                default: $payment['status'] = PaymentWorkflow::label($payment['status']); break;
            }
        }

        // Crear nombre de archivo con el nombre del atleta
        $athleteName = $Athlete['first_name'] . '_' . $Athlete['last_name'];
        // Sanitizar el nombre para que sea válido como nombre de archivo
        $athleteName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $athleteName);
        $athleteName = preg_replace('/_+/', '_', $athleteName); // Eliminar guiones bajos múltiples

        $filename = 'Historial_de_Pago_' . $athleteName;

        $totalAmount = array_sum(array_column(
            array_filter($payments, fn($p) => in_array($p['status'], [PaymentWorkflow::label(PaymentWorkflow::APPROVED), PaymentWorkflow::APPROVED], true)),
            'amount'
        ));
        $verificationPayload = [
            'athlete_name'     => $Athlete['first_name'] . ' ' . $Athlete['last_name'],
            'athlete_id_card'  => $Athlete['id_card'] ?? 'N/A',
            'athlete_category' => $Athlete['category'],
            'parent_name'      => $Athlete['parent_name'],
            'total_payments'   => count($payments),
            'total_amount'     => number_format($totalAmount, 2),
        ];

        $html = $this->renderPdfView('payment_history', [
            'title'    => 'Historial de Pagos',
            'Athlete'  => $Athlete,
            'payments' => $payments
        ], $verificationPayload, 'payment_history', $filename);
        $this->generatePdf($html, $filename);
    }

    /**
     * Genera el PDF del reporte financiero.
     * @param array $filters Filtros aplicados al reporte.
     */
    private function generateFinancialReport($filters) {
        $payments = $this->paymentModel->getPaymentsWithAthletes($filters);
        $stats = $this->paymentModel->getPaymentStats($filters);

        // Ordenar alfabéticamente por nombre del atleta
        usort($payments, function($a, $b) {
            return strcasecmp($a['first_name'], $b['first_name']);
        });

        // Descripción legible de los filtros aplicados (si los hay)
        $filterDescription = $this->buildFinancialFilterDescription($filters);

        $filename = 'reporte_financiero';

        $verificationPayload = [
            'total_revenue'   => number_format($stats['total_revenue'] ?? 0, 2),
            'paid_count'      => $stats['approved_payments'] ?? 0,
            'pending_count'   => $stats['pending_payments'] ?? 0,
            'total_athletes'  => $stats['total_athletes']   ?? 0,
            'filters_applied' => $filterDescription ?: 'Ninguno',
        ];

        $html = $this->renderPdfView('financial_report', [
            'title'             => 'Reporte Financiero',
            'payments'          => $payments,
            'stats'             => $stats,
            'filters'           => $filters,
            'filterDescription' => $filterDescription
        ], $verificationPayload, 'financial', $filename);
        $this->generatePdf($html, $filename);
    }
    
    /**
     * Busca atletas para el autocompletado en los filtros de reportes.
     */
    public function searchAthletes() {
        // Antes no exigia ni sesion: devolvia nombres de menores y cedulas de representantes a cualquiera.
        $this->requireRole(['administrador', 'secretario', 'entrenador']);
        $term = trim($_GET['term'] ?? '');
        $athletes = $term === '' ? [] : $this->athleteModel->searchAthletes($term, $this->scopeAthleteIds());
        $this->view->renderJson($athletes);
    }

    /**
     * Normaliza los nombres de los filtros de fecha.
     * Como diferentes formularios de reporte pueden tener campos de fecha con nombres distintos,
     * este método los unifica a 'start_date' y 'end_date'.
     *
     * @param array $filters El array de filtros original.
     * @return array El array de filtros con los nombres de fecha normalizados.
     */
    private function normalizeDateFilters($filters) {
        if (isset($filters['athlete_start_date'])) {
            $filters['start_date'] = $filters['athlete_start_date'];
        }
        if (isset($filters['athlete_end_date'])) {
            $filters['end_date'] = $filters['athlete_end_date'];
        }
        if (isset($filters['financial_start_date'])) {
            $filters['start_date'] = $filters['financial_start_date'];
        }
        if (isset($filters['financial_end_date'])) {
            $filters['end_date'] = $filters['financial_end_date'];
        }
        return $filters;
    }

    /**
     * Construye una descripción legible de los filtros financieros aplicados,
     * para mostrarla en el PDF del reporte financiero.
     *
     * Si no hay filtros relevantes, devuelve una cadena vacía.
     *
     * @param array $filters
     * @return string
     */
    private function buildFinancialFilterDescription(array $filters): string {
        $parts = [];

        // Rango de fechas real de pago
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $start = !empty($filters['start_date']) ? date('d/m/Y', strtotime($filters['start_date'])) : 'Sin inicio';
            $end = !empty($filters['end_date']) ? date('d/m/Y', strtotime($filters['end_date'])) : 'Sin fin';
            $parts[] = "Rango de fechas: {$start} - {$end}";
        }

        // Mes / Año (campos del registro de pago)
        if (!empty($filters['month']) || !empty($filters['year'])) {
            $months = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre',
            ];
            $monthLabel = !empty($filters['month']) && isset($months[(int)$filters['month']])
                ? $months[(int)$filters['month']]
                : null;

            if ($monthLabel && !empty($filters['year'])) {
                $parts[] = "Mes/Año: {$monthLabel} {$filters['year']}";
            } elseif ($monthLabel) {
                $parts[] = "Mes: {$monthLabel}";
            } elseif (!empty($filters['year'])) {
                $parts[] = "Año: {$filters['year']}";
            }
        }

                // Categoría del atleta (con rango de edad como en Gestión de Pagos)
                if (!empty($filters['category'])) {
                    $categoryLabels = [
                        'Infantil'    => 'Infantil (8-10 años)',
                        'Pre-juvenil' => 'Pre-juvenil (11-12 años)',
                        'Juvenil'     => 'Juvenil (13-15 años)',
                        'Junior'      => 'Junior (16-18 años)',
                    ];
        
                    $category = $filters['category'];
                    $label = $categoryLabels[$category] ?? $category;
                    $parts[] = "Categoría: {$label}";
                }

        // Estado del pago
        if (!empty($filters['status'])) {
            $label = PaymentWorkflow::label($filters['status']);
            $parts[] = "Estado: {$label}";
        }

        return implode(' | ', $parts);
    }

    /**
     * Normaliza los filtros específicos del reporte financiero (mes, año, estado, categoría)
     * a los nombres usados internamente por el modelo Payment.
     *
     * @param array $filters
     * @return array
     */
    private function normalizeFinancialFilters($filters) {
        // Limpiar siempre los filtros internos usados por el reporte financiero
        // para evitar que valores provenientes de otros formularios (por ejemplo,
        // "category" o "status" de la lista de atletas) afecten los resultados
        // cuando en los selects del reporte financiero se elige la opción "Todos".
        unset($filters['month'], $filters['year'], $filters['status'], $filters['category']);

        if (isset($filters['financial_month']) && $filters['financial_month'] !== '') {
            // El campo month en la BD almacena el nombre del mes (ej: "Noviembre", "Diciembre")
            // por lo que se mantiene el valor tal cual viene del formulario
            $filters['month'] = $filters['financial_month'];
        }

        if (!empty($filters['financial_year'])) {
            $filters['year'] = $filters['financial_year'];
        }

        if (!empty($filters['financial_status'])) {
            $filters['status'] = $filters['financial_status'];
        }

        if (!empty($filters['financial_category'])) {
            $filters['category'] = $filters['financial_category'];
        }

        return $filters;
    }
}
