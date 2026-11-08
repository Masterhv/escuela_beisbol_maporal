<?php
require_once 'controllers/Controller.php';

/**
 * Controlador para el Dashboard principal de la aplicación.
 * Muestra un resumen general de la información del sistema.
 */
class DashboardController extends Controller {
    
    /**
     * @var Athlete Instancia del modelo Athlete.
     */
    protected $athleteModel;

    /**
     * @var Payment Instancia del modelo Payment.
     */
    protected $paymentModel;

    /**
     * @var Admin Instancia del modelo Admin.
     */
    protected $adminModel;

    /**
     * Constructor. Carga los modelos necesarios para el dashboard.
     */
    public function __construct() {
        parent::__construct();
        require_once 'models/Athlete.php';
        require_once 'models/Payment.php';
require_once 'models/PaymentWorkflow.php';
        require_once 'models/Admin.php';
        $this->athleteModel = new Athlete();
        $this->paymentModel = new Payment();
        $this->adminModel = new Admin();
    }
    
    /**
     * Muestra la página principal del dashboard.
     * Requiere que el usuario esté autenticado.
     * Recopila y muestra estadísticas clave y listas de actividad reciente.
     */
    public function index() {
        $this->requireAuth();

        
        $role = $_SESSION['admin_role'];
        $scope = $this->scopeAthleteIds(); // null = sin restricción (personal); array = ids del representante

        // Lo que ve cada rol:
        //  - administrador / secretario: resumen completo (incluye ingresos y pagos por revisar).
        //  - entrenador: atletas; las cifras de dinero no son de su responsabilidad (ve Solvencia).
        //  - representante: solo SUS atletas y SUS pagos; nunca cifras globales de la escuela.
        $showMoney = in_array($role, ['administrador', 'secretario'], true);

        $athleteStats = $this->athleteModel->getAthleteStats();
        $stats = [
            'totalAthletes' => $scope === null ? ($athleteStats['total'] ?? 0) : count($scope),
            'activeAthletes' => $scope === null ? ($athleteStats['active'] ?? 0) : null,
            'pendingPayments' => null,
            'inReviewPayments' => null,
            'totalRevenue' => null,
        ];
        if ($showMoney) {
            $paymentStats = $this->paymentModel->getPaymentStats();
            $stats['pendingPayments'] = (int)($paymentStats['pending_payments'] ?? 0) + (int)($paymentStats['rejected_payments'] ?? 0);
            $stats['inReviewPayments'] = (int)($paymentStats['in_review_payments'] ?? 0);
            $stats['totalRevenue'] = $paymentStats['total_revenue'] ?? 0.00;
        }

        // Actividad reciente: filtrada por alcance (en SQL) y sin dinero para el entrenador.
        $recentAthletes = $scope === null
            ? $this->athleteModel->getrecentAthletes(5)
            : array_slice(array_values(array_filter($this->athleteModel->getrecentAthletes(1000), fn($a) => in_array((int)$a['id'], $scope, true))), 0, 5);
        $recentPayments = ($showMoney || $role === 'representante')
            ? $this->paymentModel->getRecentPayments(5, $scope)
            : [];

        // Renderiza la vista del dashboard y le pasa los datos.
        $this->view->render('dashboard/index', [
            'stats' => $stats,
            'recentAthletes' => $recentAthletes,
            'recentPayments' => $recentPayments,
            'showMoney' => $showMoney,
            'isRepresentante' => $role === 'representante',
            'title' => 'Dashboard'
        ]);
    }
}
