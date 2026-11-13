<?php
require_once 'controllers/Controller.php';
require_once 'models/Payment.php';
require_once 'models/PaymentWorkflow.php';
require_once 'models/Athlete.php';
require_once 'models/SchoolBankAccount.php';
require_once 'services/WhatsAppReceiptService.php';
require_once 'services/EmailReceiptService.php';
require_once 'services/PaymentNotificationService.php';
require_once 'vendor/autoload.php'; // Carga Dompdf
require_once 'services/PdfGenerator.php';

use App\Core\Traits\PdfGenerator;

/**
 * Controlador del modulo de pagos.
 *
 * Todo cambio de estado pasa por PaymentWorkflow (que valida transicion, rol, separacion de funciones
 * y deja bitacora). Este controlador solo: autentica, aplica el alcance del usuario (un representante
 * solo ve a sus atletas), valida la entrada, delega y comunica el resultado.
 *
 * Roles:
 *  - representante: ve el estado de cuenta de SUS atletas y reporta pagos (referencia, fecha, metodo, comprobante).
 *  - secretario:    crea cobros, concilia (aprueba/rechaza con comentario) pagos en revision.
 *  - entrenador:    sin acceso al modulo de pagos (no ve montos, referencias ni comprobantes); ve a los atletas activos e inactivos en Atletas.
 *  - administrador: todo lo del secretario (incluida la conciliacion) mas conceptos y tarifas, reportes y reabrir
 *                   pagos aprobados con motivo. Nadie puede borrar pagos. Ninguno concilia lo que el mismo registro.
 */
class PaymentsController extends Controller {

    use PdfGenerator;

    private const METHODS = [
        'transferencia' => 'Transferencia bancaria',
        'pago_movil'    => 'Pago móvil',
        'efectivo'      => 'Efectivo',
        'otro'          => 'Otro',
    ];

    private $paymentModel;
    private $workflow;
    private $athleteModel;

    public function __construct() {
        parent::__construct();
        $this->paymentModel = new Payment();
        $this->workflow     = new PaymentWorkflow();
        $this->athleteModel = new Athlete();
    }

    // ------------------------------------------------------------------ utilidades

    /** Datos del usuario actual, en el formato que espera PaymentWorkflow. */
    private function actor() {
        return [
            'id'   => (int)$_SESSION['admin_id'],
            'name' => (string)($_SESSION['admin_name'] ?? ''),
            'role' => (string)$_SESSION['admin_role'],
        ];
    }

    private function isStaff() {
        return in_array($_SESSION['admin_role'], ['administrador', 'secretario'], true);
    }

    /**
     * Carga un pago y verifica que el usuario pueda verlo. Un representante solo accede a pagos de sus
     * atletas; ante un pago ajeno o inexistente responde igual (no revela que ids existen).
     */
    private function loadAccessiblePayment($id, $asJson = false) {
        $payment = $id ? $this->workflow->loadPayment((int)$id) : false;
        if (!$payment || !$this->canAccessAthlete($payment['athlete_id'])) {
            if ($asJson) {
                $this->jsonResponse(['success' => false, 'message' => 'El pago no existe.'], 404);
            }
            $this->redirect('payments?error=not_found');
        }
        return $payment;
    }

    /** Responde al fallo de una transicion con el codigo HTTP adecuado. */
    private function failureStatus($code) {
        return [
            'forbidden_role' => 403, 'self_review' => 403, 'not_found' => 404,
            'invalid_transition' => 409, 'conflict' => 409, 'duplicate_reference' => 409,
        ][$code] ?? 400;
    }

    private function validateMonth($month) {
        return in_array($month, ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'], true);
    }

    // ------------------------------------------------------------------ lectura

    /**
     * Lista de pagos. El representante ve solo los de sus atletas; el personal ve todo. El entrenador no tiene
     * acceso al modulo de pagos: se le devuelve al inicio.
     */
    public function index() {
        $this->requireAuth();
        if ($_SESSION['admin_role'] === 'entrenador') {
            $this->redirect('dashboard');
        }

        $filters = $_GET;
        $scope = $this->scopeAthleteIds();   // null = sin restriccion (personal); array = atletas del representante

        // El cuadro de busqueda encuentra por CEDULA (atleta o representante) y, para quien concilia (administrador y
        // secretario), tambien por REFERENCIA bancaria (solo digitos, desde 4). Se combina con el resto de filtros.
        // El representante NO busca por referencia: solo ve los pagos de sus atletas y el alcance se aplica en SQL.
        $canSearchReferences = in_array($_SESSION['admin_role'], ['administrador', 'secretario'], true);
        $payments = $this->paymentModel->searchPayments(trim((string)($filters['q'] ?? '')), $filters, $canSearchReferences, $scope);
        $this->view->render('payments/index', [
            'payments' => $payments,
            'filters'  => $filters,
            'canSearchReferences' => $canSearchReferences,
            'title'    => 'Gestión de Pagos',
        ]);
    }

    /** Bandeja de pagos En Revision, para el secretario (y el administrador, solo lectura). */
    public function review() {
        $this->requireRole(['secretario', 'administrador']);
        $payments = $this->paymentModel->getPaymentsWithAthletes(['status' => PaymentWorkflow::IN_REVIEW]);
        usort($payments, fn($a, $b) => strcmp((string)$a['reported_at'], (string)$b['reported_at'])); // el mas antiguo primero

        $this->view->render('payments/review', [
            'payments' => $payments,
            'title'    => 'Pagos en Revisión',
        ]);
    }

    /** Detalle de un pago con su bitacora y las acciones que el usuario puede ejecutar. */
    public function view() {
        $this->requireAuth();
        if ($_SESSION['admin_role'] === 'entrenador') {
            $this->redirect('dashboard'); // el entrenador no ve montos ni comprobantes
        }
        $payment = $this->loadAccessiblePayment($_GET['id'] ?? null);
        $actions = PaymentWorkflow::allowedActions($payment, $_SESSION['admin_role'], $_SESSION['admin_id']);

        // Los datos bancarios de la escuela SOLO se cargan cuando el usuario puede reportar este pago (es cuando los necesita
        // para depositar). No viajan al HTML de un pago aprobado, en revision, ni de quien no puede reportar.
        $bank = in_array('report', $actions, true) ? (new SchoolBankAccount())->get() : null;

        $this->view->render('payments/view', [
            'payment' => $payment,
            'history' => $this->workflow->history($payment['id']),
            'actions' => $actions,
            'bank'    => $bank,
            'methods' => self::METHODS,
            'title'   => 'Detalles del Pago',
        ]);
    }

    // ------------------------------------------------------------------ cobros (secretario / administrador)

    /** Genera un cobro (nace siempre Pendiente). Nadie puede crear un pago ya aprobado. */
    public function create() {
        $this->requireRole(['administrador', 'secretario']);
        $concepts = (new PaymentConcept())->activeList();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conceptId = (int)($_POST['concept_id'] ?? 0);
            $concept = (new PaymentConcept())->findById($conceptId);
            $athleteId = (int)($_POST['athlete_id'] ?? 0);
            $month = trim($_POST['month'] ?? '');
            $year  = (int)($_POST['year'] ?? 0);
            $due   = $_POST['due_date'] ?? '';
            $amount = round((float)($_POST['amount'] ?? 0), 2);

            $err = null;
            if (!$concept || !(int)$concept['is_active'])      $err = 'concept';
            elseif (!$this->athleteModel->findById($athleteId)) $err = 'athlete';
            elseif (!$this->validateMonth($month))              $err = 'month';
            elseif ($year < 2000 || $year > 2100)               $err = 'year';
            elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) $err = 'due_date';
            elseif ($amount <= 0 || $amount > 99999999)         $err = 'amount';

            if ($err) {
                $this->redirect('payments/create?error=' . $err);
            }
            $id = $this->workflow->createPending([
                'athlete_id' => $athleteId, 'concept_id' => $conceptId, 'amount' => $amount, 'due_date' => $due,
                'month' => $month, 'year' => $year, 'notes' => trim($_POST['notes'] ?? ''),
            ], $this->actor());

            $this->redirect($id ? 'payments?success=created' : 'payments/create?error=failed');
        }

        $this->view->render('payments/create', ['concepts' => $concepts, 'title' => 'Generar Cobro']);
    }

    /** Edita los datos de un cobro. Solo mientras esta Pendiente o Rechazado; nunca cambia el estado. */
    public function edit() {
        $this->requireRole(['administrador']); // Editar cobros: solo administrador
        $payment = $this->loadAccessiblePayment($_GET['id'] ?? null);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conceptId = (int)($_POST['concept_id'] ?? 0);
            $concept = (new PaymentConcept())->findById($conceptId);
            $month = trim($_POST['month'] ?? '');
            $year  = (int)($_POST['year'] ?? 0);
            $due   = $_POST['due_date'] ?? '';
            $amount = round((float)($_POST['amount'] ?? 0), 2);

            if (!$concept || !$this->validateMonth($month) || $year < 2000 || $year > 2100
                || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $due) || $amount <= 0 || $amount > 99999999) {
                $this->redirect('payments/edit?id=' . (int)$payment['id'] . '&error=invalid');
            }
            $r = $this->workflow->updateDetails($payment['id'], [
                'concept_id' => $conceptId, 'amount' => $amount, 'due_date' => $due,
                'month' => $month, 'year' => $year, 'notes' => trim($_POST['notes'] ?? ''),
            ], $this->actor());

            $this->redirect($r['ok'] ? 'payments?success=updated'
                                     : 'payments/view?id=' . (int)$payment['id'] . '&error=' . urlencode($r['code']));
        }

        if (!in_array($payment['status'], [PaymentWorkflow::PENDING, PaymentWorkflow::REJECTED], true)) {
            $this->redirect('payments/view?id=' . (int)$payment['id'] . '&error=locked');
        }
        $this->view->render('payments/edit', [
            'payment' => $payment, 'concepts' => (new PaymentConcept())->activeList(), 'title' => 'Editar Cobro',
        ]);
    }

    // ------------------------------------------------------------------ flujo de verificacion

    /**
     * Reportar un pago: referencia, fecha, metodo y comprobante. Lo ejecuta el representante (sobre
     * sus atletas) o el personal en nombre de un representante. Pasa a En Revision, avisa por correo a los secretarios y aparece en el contador "Por revisar" del menu.
     */
    public function report() {
        $this->requireRole(['representante', 'secretario', 'administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('payments');
        }
        $this->requireSameOriginPost(false);
        $payment = $this->loadAccessiblePayment($_POST['id'] ?? null);
        $back = 'payments/view?id=' . (int)$payment['id'];

        $reference = PaymentWorkflow::normalizeReference($_POST['reference_number'] ?? '');   // solo digitos, 4 a 20; null si no es valida
        $method    = trim($_POST['payment_method'] ?? '');
        $date      = trim($_POST['payment_date'] ?? '');
        if ($reference === null) {
            // Se valida ANTES de subir el comprobante: asi un rechazo no deja un archivo huerfano en el disco.
            $this->redirect($back . '&error=reference');
        }
        if (!isset(self::METHODS[$method])) {
            $this->redirect($back . '&error=method');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date > date('Y-m-d') || strtotime($date) < strtotime('-1 year')) {
            $this->redirect($back . '&error=date');
        }

        // Comprobante: obligatorio salvo efectivo. Se valida ANTES de tocar el estado.
        $uploaded = null;
        if (!empty($_FILES['receipt_image']['name'])) {
            $uploaded = $this->handleReceiptUpload($_FILES['receipt_image']);
            if ($uploaded === false) {
                $this->redirect($back . '&error=receipt_upload');
            }
        } elseif ($method !== 'efectivo' && empty($payment['receipt_image'])) {
            $this->redirect($back . '&error=receipt_required');
        }

        $result = $this->workflow->transition($payment['id'], 'report', $this->actor(), '', [
            'reference_number' => $reference, 'payment_method' => $method,
            'payment_date' => $date, 'receipt_image' => $uploaded,
        ]);

        if (!$result['ok']) {
            if ($uploaded && file_exists($uploaded)) { unlink($uploaded); } // no dejar archivos huerfanos
            $this->redirect($back . '&error=' . urlencode($result['code']));
        }
        // Si habia un comprobante anterior (pago rechazado y vuelto a reportar) y se subio uno nuevo, se borra el viejo.
        if ($uploaded && !empty($payment['receipt_image']) && file_exists($payment['receipt_image'])) {
            unlink($payment['receipt_image']);
        }

        // Aviso por correo a los secretarios. Es un complemento: nunca bloquea ni revierte el reporte (el estado ya
        // esta confirmado) y un fallo de correo solo queda en el log. El secretario tambien ve el contador del menu.
        (new PaymentNotificationService())->notifyReported($result['payment'], $this->actor());
        $this->redirect($back . '&success=reported');
    }

    /** Aprueba un pago en revision (secretario o administrador, y nunca el que el mismo registro). Dispara el recibo. */
    public function approve() {
        $this->requireRole(['secretario', 'administrador']);
        $this->mustPost();
        $payment = $this->loadAccessiblePayment($_POST['id'] ?? null, true);

        $r = $this->workflow->transition($payment['id'], 'approve', $this->actor(), trim($_POST['comment'] ?? ''));
        if (!$r['ok']) {
            $this->jsonResponse(['success' => false, 'message' => $r['error'], 'code' => $r['code']], $this->failureStatus($r['code']));
        }
        // La aprobacion ya esta confirmada: el recibo es un intento aparte que nunca la revierte.
        $this->sendReceiptsSafely($payment['id']);
        $this->jsonResponse(['success' => true]);
    }

    /** Rechaza un pago en revision con motivo obligatorio y avisa al representante por correo. */
    public function reject() {
        $this->requireRole(['secretario', 'administrador']);
        $this->mustPost();
        $payment = $this->loadAccessiblePayment($_POST['id'] ?? null, true);
        $comment = trim($_POST['comment'] ?? '');

        $r = $this->workflow->transition($payment['id'], 'reject', $this->actor(), $comment);
        if (!$r['ok']) {
            $this->jsonResponse(['success' => false, 'message' => $r['error'], 'code' => $r['code']], $this->failureStatus($r['code']));
        }
        (new PaymentNotificationService())->notifyRejected($r['payment'], $comment);
        $this->jsonResponse(['success' => true]);
    }

    /** Reabre un pago aprobado (solo administrador, con motivo). El recibo anterior queda sin efecto. */
    public function reopen() {
        $this->requireRole(['administrador']);
        $this->mustPost();
        $payment = $this->loadAccessiblePayment($_POST['id'] ?? null, true);

        $r = $this->workflow->transition($payment['id'], 'reopen', $this->actor(), trim($_POST['comment'] ?? ''));
        if (!$r['ok']) {
            $this->jsonResponse(['success' => false, 'message' => $r['error'], 'code' => $r['code']], $this->failureStatus($r['code']));
        }
        $this->jsonResponse(['success' => true]);
    }

    /** Acciones que cambian dinero o estado: POST y del mismo sitio (ver Controller::requireSameOriginPost). */
    private function mustPost() {
        $this->requireSameOriginPost(true);
    }

    // ------------------------------------------------------------------ recibos

    /** Descarga el recibo. Solo de pagos aprobados y solo de atletas accesibles. */
    public function downloadReceipt() {
        $this->requireAuth();
        if ($_SESSION['admin_role'] === 'entrenador') {
            $this->redirect('dashboard');
        }
        $payment = $this->loadAccessiblePayment($_GET['id'] ?? null);
        if ($payment['status'] !== PaymentWorkflow::APPROVED) {
            $this->redirect('payments/view?id=' . (int)$payment['id'] . '&error=not_approved');
        }

        try {
            $pdf = (new EmailReceiptService())->buildReceiptPdf($payment['id']);
        } catch (\Throwable $e) {
            error_log('[PaymentsController] Error generando recibo del pago #' . $payment['id'] . ': ' . $e->getMessage());
            $this->redirect('payments/view?id=' . (int)$payment['id'] . '&error=pdf_failed');
        }
        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /** Reenvia manualmente el recibo por WhatsApp. Solo personal, solo pagos aprobados. */
    public function resendWhatsApp() {
        $this->requireRole(['administrador', 'secretario']);
        $this->mustPost();
        $payment = $this->loadAccessiblePayment($_POST['id'] ?? null, true);
        if ($payment['status'] !== PaymentWorkflow::APPROVED) {
            $this->jsonResponse(['success' => false, 'message' => 'Solo se puede enviar el recibo de un pago aprobado.'], 400);
        }
        $result = (new WhatsAppReceiptService())->sendReceipt($payment['id']);
        $this->jsonResponse($result['success'] ? ['success' => true] : ['success' => false, 'message' => $result['error']], $result['success'] ? 200 : 500);
    }

    /** Reenvia manualmente el recibo por correo. Solo personal, solo pagos aprobados. */
    public function resendEmail() {
        $this->requireRole(['administrador', 'secretario']);
        $this->mustPost();
        $payment = $this->loadAccessiblePayment($_POST['id'] ?? null, true);
        if ($payment['status'] !== PaymentWorkflow::APPROVED) {
            $this->jsonResponse(['success' => false, 'message' => 'Solo se puede enviar el recibo de un pago aprobado.'], 400);
        }
        $result = (new EmailReceiptService())->sendReceipt($payment['id']);
        $this->jsonResponse($result['success'] ? ['success' => true] : ['success' => false, 'message' => $result['error']], $result['success'] ? 200 : 500);
    }

    /** Envia recibo por WhatsApp y correo sin propagar errores: nunca revierte la aprobacion. */
    private function sendReceiptsSafely($paymentId) {
        try { (new WhatsAppReceiptService())->sendReceipt($paymentId); }
        catch (\Throwable $e) { error_log("[PaymentsController] WhatsApp del pago #{$paymentId}: " . $e->getMessage()); }
        try { (new EmailReceiptService())->sendReceipt($paymentId); }
        catch (\Throwable $e) { error_log("[PaymentsController] Correo del pago #{$paymentId}: " . $e->getMessage()); }
    }

    // ------------------------------------------------------------------ comprobantes

    /**
     * Sirve el comprobante de un pago SOLO a quien puede ver ese pago. Los archivos de
     * img/comprobantes/ no son accesibles por URL directa (ver img/comprobantes/.htaccess).
     */
    public function receiptImage() {
        $this->requireAuth();
        if ($_SESSION['admin_role'] === 'entrenador') {
            http_response_code(403);
            exit;
        }
        $payment = $this->loadAccessiblePayment($_GET['id'] ?? null);
        $path = (string)($payment['receipt_image'] ?? '');

        // Defensa en profundidad: la ruta guardada debe estar dentro de img/comprobantes/.
        $base = realpath('img/comprobantes');
        $real = $path !== '' ? realpath($path) : false;
        if (!$base || !$real || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($real)) {
            http_response_code(404);
            exit;
        }
        $mime = mime_content_type($real);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($real));
        header('Content-Disposition: inline; filename="comprobante_' . (int)$payment['id'] . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($real);
        exit;
    }

    /** Guarda un comprobante subido. Devuelve la ruta relativa o false. */
    private function handleReceiptUpload($file) {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        $mimeExtensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($mimeExtensions[$mime]) || $file['size'] > 5 * 1024 * 1024) {
            return false;
        }
        // Debe ser una imagen real, no solo llevar el MIME correcto.
        if (@getimagesize($file['tmp_name']) === false) {
            return false;
        }
        if (!is_dir('img/comprobantes')) {
            mkdir('img/comprobantes', 0755, true);
        }
        $destination = 'img/comprobantes/comprobante_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $mimeExtensions[$mime];
        return move_uploaded_file($file['tmp_name'], $destination) ? $destination : false;
    }
}
?>
