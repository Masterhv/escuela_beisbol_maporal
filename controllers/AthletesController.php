<?php
require_once 'controllers/Controller.php';
require_once 'models/Admin.php';
require_once 'vendor/autoload.php'; // Carga Dompdf
require_once 'services/PdfGenerator.php';
require_once 'services/AthleteEnrollmentService.php';
require_once 'services/AthleteClearanceService.php';
require_once 'services/AthleteCertificateService.php';

use App\Core\Traits\PdfGenerator;

/**
 * Controlador para la gestión de atletas.
 * Maneja las operaciones CRUD para los atletas de la escuela.
 */
class AthletesController extends Controller {

    // Da acceso a downloadPdfString() para las descargas de planilla y ficha técnica.
    use PdfGenerator;

    /**
     * Estados válidos de un atleta (coinciden con el ENUM de la columna athletes.status).
     */
    private const VALID_STATUSES = ['active', 'inactive', 'suspended'];

    /**
     * Normaliza el estado recibido del formulario. Un valor que no sea válido (por ejemplo uno
     * manipulado) se descarta para no enviar a la base de datos algo que su ENUM rechazaría.
     *
     * @param mixed       $status
     * @param string|null $fallback Estado a usar si el recibido no es válido.
     * @return string
     */
    private function sanitizeStatus($status, $fallback = 'active') {
        $status = is_string($status) ? trim($status) : '';
        return in_array($status, self::VALID_STATUSES, true) ? $status : $fallback;
    }

    /**
     * Prepara (y valida) la cuenta de representante que se crea junto con el atleta, reutilizando los
     * datos que el formulario ya pide (nombre, cedula, telefono, correo, direccion y contacto de
     * emergencia del atleta). Solo se piden aparte: fecha de nacimiento, usuario, contrasena y el
     * parentesco del contacto de emergencia.
     *
     * Si la cedula del representante ya tiene cuenta en el sistema, NO se valida ni se crea nada:
     * se devuelve accountExists para que el acceso quede vinculado por cedula, como siempre.
     *
     * @return array [errores[], datosParaCreateAdmin|null, yaTeniaCuenta(bool)]
     */
    private function prepareRepresentativeAccount(array $data, Admin $adminModel) {
        $parentIdCard = trim($data['parent_id_card'] ?? '');

        // El representante ya tiene cuenta (p. ej. otro atleta inscrito antes): no se valida ni se crea nada.
        // Se consulta SIEMPRE en la base de datos (el flag del navegador solo mejora la UX, no se confia).
        if ($adminModel->isIdCardTaken($parentIdCard)) {
            return [[], null, true];
        }

        $errors = [];
        $birthDate = trim($data['account_birth_date'] ?? '');
        $username = trim($data['account_username'] ?? '');
        $password = (string)($data['account_password'] ?? '');
        $confirm = (string)($data['account_password_confirm'] ?? '');
        $relationship = trim($data['account_emergency_relationship'] ?? '');

        if ($birthDate === '') {
            $errors[] = 'La fecha de nacimiento del representante es obligatoria para crear su cuenta.';
        } else {
            try {
                $age = (new DateTime($birthDate))->diff(new DateTime('today'))->y;
                if ($age < 18) {
                    $errors[] = 'El representante debe ser mayor de 18 anos para tener una cuenta.';
                }
            } catch (Exception $e) {
                $errors[] = 'El formato de la fecha de nacimiento del representante no es valido.';
            }
        }

        if ($username === '') {
            $errors[] = 'El nombre de usuario del representante es obligatorio.';
        } elseif (!preg_match('/^[A-Za-z0-9._-]{3,30}$/', $username)) {
            $errors[] = 'El nombre de usuario solo puede tener letras, numeros, punto, guion y guion bajo (3 a 30 caracteres).';
        } elseif ($adminModel->isUsernameTaken($username)) {
            $errors[] = 'El nombre de usuario ya esta registrado en el sistema.';
        }

        $email = trim($data['parent_email'] ?? '');
        if ($email !== '' && $adminModel->isEmailTaken($email)) {
            $errors[] = 'El correo electronico ya esta registrado con otra cuenta del sistema.';
        }

        if ($password !== $confirm) {
            $errors[] = 'Las contrasenas no coinciden.';
        } else {
            $errors = array_merge($errors, AdminsController::validatePasswordStrength($password));
        }

        if ($relationship === '') {
            $errors[] = 'Indica el parentesco del contacto de emergencia (se guarda en la cuenta).';
        }

        if (!empty($errors)) {
            return [$errors, null, false];
        }

        $accountData = [
            'full_name' => trim($data['parent_name'] ?? ''),
            'id_card' => $parentIdCard,
            'birth_date' => $birthDate,
            'phone' => trim($data['parent_phone'] ?? ''),
            'email' => $email,
            'address' => trim($data['address'] ?? ''),
            'emergency_contact_name' => trim($data['emergency_contact'] ?? ''),
            'emergency_contact_relationship' => $relationship,
            'emergency_contact_phone' => trim($data['emergency_phone'] ?? ''),
            'role' => 'representante',
            'status' => 'active',
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ];
        return [[], $accountData, false];
    }

    
    /** Datos de quien actua, en el formato que espera Athlete::changeStatus(). */
    private function statusActor() {
        return [
            'id'   => isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null,
            'name' => (string)($_SESSION['admin_name'] ?? ''),
            'role' => (string)($_SESSION['admin_role'] ?? ''),
        ];
    }

    /**
     * Error de validacion del motivo de estatus, o null si esta bien. Se pide motivo SOLO cuando el estatus resultante
     * es inactivo/suspendido Y es distinto del actual: no se obliga a justificar de nuevo un estado ya registrado, ni
     * al reactivar. Se valida ANTES de subir fotos o tocar la base de datos para no dejar archivos huerfanos.
     *
     * @param string|null $currentStatus null al crear un atleta nuevo.
     */
    private function statusReasonError(array $data, $currentStatus) {
        $new = $this->sanitizeStatus($data['status'] ?? ($currentStatus ?? 'active'), $currentStatus ?? 'active');
        if ($new === 'active' || $new === $currentStatus) {
            return null;
        }
        $len = mb_strlen(trim((string)($data['status_reason'] ?? '')));
        if ($len < Athlete::STATUS_REASON_MIN) {
            return 'Escribe el motivo (minimo ' . Athlete::STATUS_REASON_MIN . ' caracteres) para dejar al atleta como ' . mb_strtolower(Athlete::STATUS_LABELS[$new]) . '.';
        }
        if ($len > Athlete::STATUS_REASON_MAX) {
            return 'El motivo no puede superar ' . Athlete::STATUS_REASON_MAX . ' caracteres.';
        }
        return null;
    }

    /**
     * Constructor. Carga el modelo Athlete.
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Athlete');
    }
    
    /**
     * Muestra la lista de atletas.
     * Permite filtrar los atletas por diferentes criterios.
     */
    public function index() {
        $this->requireAuth(); // Todos los roles autenticados pueden ver la lista
         
        $filters = $_GET;
        $athletes = $this->model->getAthletesWithFilters($filters);

        // Un representante solo ve a los atletas que le pertenecen (por cédula de representante).
        $scope = $this->scopeAthleteIds();
        if ($scope !== null) {
            $athletes = array_values(array_filter($athletes, fn($a) => in_array((int)$a['id'], $scope, true)));
        }

        // El motivo del estatus es sensible: la consulta trae todas las columnas, asi que se QUITA de cada fila para
        // los roles que no deben verlo, antes de que llegue a la vista.
        $viewerRole = $_SESSION['admin_role'];
        $athletes = array_map(fn($a) => Athlete::stripStatusDetailFor($a, $viewerRole), $athletes);

        // Estadísticas globales de atletas (sin importar filtros de la tabla)
        $athleteStats = $this->model->getAthleteStats();
        $totalAthletes = isset($athleteStats['total']) ? (int)$athleteStats['total'] : 0;

        // Conteo de atletas por categoría (usando solo el filtro de categoría)
        $categoryCount = null;
        if (!empty($filters['category'])) {
            $categoryAthletes = $this->model->getAthletesWithFilters([
                'category' => $filters['category']
            ]);
            $categoryCount = count($categoryAthletes);
        }

        // Conteo de atletas por edad (usando solo el filtro de edad)
        $ageCount = null;
        if (!empty($filters['age'])) {
            $ageAthletes = $this->model->getAthletesWithFilters([
                'age' => $filters['age']
            ]);
            $ageCount = count($ageAthletes);
        }
        
        $this->view->render('athletes/index', [
            'athletes' => $athletes,
            'filters' => $filters,
            'title' => 'Gestión de Atletas',
            'stats' => [
                'total_athletes' => $totalAthletes,
                'category_count' => $categoryCount,
                'age_count' => $ageCount
            ]
        ]);
    }
    
    /**
     * Muestra el formulario para crear un nuevo atleta y procesa su envío.
     * Requiere rol de 'administrador' o 'secretario'.
     */
    public function create() {
        $this->requireRole(['administrador', 'secretario']); // Administrador y secretario pueden crear
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validateAthleteData($data);
            // Un atleta que nace inactivo/suspendido tambien necesita su motivo.
            if ($reasonError = $this->statusReasonError($data, null)) { $errors[] = $reasonError; }

            // Cuenta del representante (OBLIGATORIA): se valida ANTES de crear nada, para que un
            // usuario/correo repetido no deje un atleta sin cuenta ni una cuenta sin atleta.
            // Si la cedula ya tiene cuenta, prepareRepresentativeAccount la reutiliza (sin crear otra).
            $adminModel = new Admin();
            [$accountErrors, $accountData, $accountExists] = $this->prepareRepresentativeAccount($data, $adminModel);
            $errors = array_merge($errors, $accountErrors);

            if (!empty($errors)) {
                $this->view->render('athletes/create', [
                    'title' => 'Nuevo Atleta',
                    'error' => implode('<br>', $errors),
                    'Athlete' => $data
                ]);
                return;
            }
            
            // Procesar foto si se subió
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->handlePhotoUpload($_FILES['photo']);
                if ($photoPath === false) {
                    $this->view->render('athletes/create', [
                        'title' => 'Nuevo Atleta',
                        'error' => 'Error al subir la fotografía. Verifique el formato y tamaño.',
                        'Athlete' => $data
                    ]);
                    return;
                }
            }
            
            // Calcula la edad a partir de la fecha de nacimiento
            $birthDate = new DateTime($data['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;

            $athleteData = [
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'date_of_birth' => $data['date_of_birth'],
                'age' => $age,
                'parent_name' => trim($data['parent_name']),
                'parent_phone' => trim($data['parent_phone']),
                'parent_email' => trim($data['parent_email']),
                'id_card' => !empty($data['id_card']) ? trim($data['id_card']) : null,
                'parent_id_card' => trim($data['parent_id_card']),
                'address' => trim($data['address']),
                'emergency_contact' => trim($data['emergency_contact']),
                'emergency_phone' => trim($data['emergency_phone']),
                'medical_conditions' => trim($data['medical_conditions']),
                'category' => $data['category'],
                'status' => $this->sanitizeStatus($data['status'] ?? 'active'),
                'registration_date' => date('Y-m-d'),
                'jersey_number' => !empty($data['jersey_number']) ? (int)$data['jersey_number'] : null,
                'photo' => $photoPath
            ];
            
            // Atleta + cuenta del representante + estatus inicial, TODO en una transaccion compartida
            // (Database es singleton): o se registran los dos, o no queda ninguno.
            $result = ['success' => false, 'id' => null];
            $accountCreated = false;
            $db = $this->model->getDb();
            $ownTx = !$db->inTransaction();
            try {
                if ($ownTx) { $db->beginTransaction(); }

                $result = $this->model->createAthlete($athleteData);
                if (!$result['success'] || !$result['id']) {
                    throw new RuntimeException($result['message'] ?? 'Error al crear el atleta.');
                }

                if ($accountData !== null) {
                    if (!$adminModel->createAdmin($accountData)) {
                        throw new RuntimeException('No se pudo crear la cuenta del representante.');
                    }
                    $accountCreated = true;
                }

                // Si nace inactivo/suspendido, queda registrado el detalle (motivo, quien y cuando).
                if ($athleteData['status'] !== 'active') {
                    $st = $this->model->recordInitialStatus($result['id'], $athleteData['status'], $data['status_reason'] ?? '', $this->statusActor());
                    if (!$st['ok']) { throw new RuntimeException('Motivo del estatus invalido.'); }
                }

                if ($ownTx) { $db->commit(); }
            } catch (\Throwable $e) {
                if ($ownTx && $db->inTransaction()) { $db->rollBack(); }
                error_log('[AthletesController::create] ' . $e->getMessage());
                // Si falla la creacion, eliminar la foto subida
                if (!empty($photoPath) && file_exists($photoPath)) {
                    unlink($photoPath);
                }
                $this->view->render('athletes/create', [
                    'title' => 'Nuevo Atleta',
                    'error' => 'No se pudo completar la inscripción: ' . $e->getMessage(),
                    'Athlete' => $data,
                    'planConcepts' => (new PaymentWorkflow())->planConcepts(),
                ]);
                return;
            }

            // Plan de cobros (inscripcion + mensualidades con los montos de Conceptos). Igual que la planilla,
            // un fallo aqui nunca revierte el registro del atleta: se informa y los cobros se generan a mano.
            $planQuery = '';
            if (!empty($data['generate_plan'])) {
                $plan = (new PaymentWorkflow())->createEnrollmentPlan($result['id'], (int)($data['plan_months'] ?? 0), $this->statusActor());
                $planQuery = $plan['ok'] ? '&plan=' . $plan['created'] . (empty($plan['missing']) ? '' : '&plan_missing=' . urlencode(implode(',', $plan['missing'])))
                                         : '&plan_error=1';
            }

            // Envia la planilla de inscripcion al representante por correo y WhatsApp.
            // Un fallo en cualquier canal nunca revierte el registro del atleta.
            $this->sendEnrollmentSafely($result['id']);
            $accountQuery = $accountData !== null ? ($accountCreated ? '&account=created' : '&account=exists') : '';
            $this->redirect('athletes?success=created' . $planQuery . $accountQuery);
        } else {
            $this->view->render('athletes/create', [
                'title' => 'Nuevo Atleta',
                'planConcepts' => (new PaymentWorkflow())->planConcepts(),
            ]);
        }
    }
    
    /**
     * Muestra el formulario para editar un atleta y procesa su envío.
     * Requiere rol de 'administrador' o 'secretario'.
     */
    public function edit() {
        $this->requireRole(['administrador', 'secretario']); // Administrador y secretario pueden editar
        
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('athletes?error=not_found');

        $Athlete = $this->model->findById($id);
        if (!$Athlete) $this->redirect('athletes?error=not_found');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $errors = $this->validateAthleteData($data, $id);
            // Cambiar a inactivo/suspendido exige motivo (se valida antes de subir la foto).
            if ($reasonError = $this->statusReasonError($data, $Athlete['status'])) { $errors[] = $reasonError; }

            if (!empty($errors)) {
                $athleteData = array_merge($Athlete, $data);
                $this->view->render('athletes/edit', [
                    'title' => 'Editar Atleta',
                    'error' => implode('<br>', $errors),
                    'Athlete' => $athleteData
                ]);
                return;
            }

            // Procesar foto si se subió una nueva
            $photoPath = $Athlete['photo']; // Mantener la foto actual por defecto
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $newPhotoPath = $this->handlePhotoUpload($_FILES['photo']);
                if ($newPhotoPath !== false) {
                    // Eliminar foto anterior si existe
                    if (!empty($Athlete['photo']) && file_exists($Athlete['photo'])) {
                        unlink($Athlete['photo']);
                    }
                    $photoPath = $newPhotoPath;
                }
            }

            // Recalcula la edad
            $birthDate = new DateTime($data['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;

            $athleteData = [
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'date_of_birth' => $data['date_of_birth'],
                'age' => $age,
                'parent_name' => trim($data['parent_name']),
                'parent_phone' => trim($data['parent_phone']),
                'parent_email' => trim($data['parent_email']),
                'id_card' => !empty($data['id_card']) ? trim($data['id_card']) : null,
                'parent_id_card' => trim($data['parent_id_card']),
                'address' => trim($data['address']),
                'emergency_contact' => trim($data['emergency_contact']),
                'emergency_phone' => trim($data['emergency_phone']),
                'medical_conditions' => trim($data['medical_conditions']),
                'category' => $data['category'],
                'jersey_number' => !empty($data['jersey_number']) ? (int)$data['jersey_number'] : null,
                'photo' => $photoPath
            ];
            $newStatus = $this->sanitizeStatus($data['status'] ?? $Athlete['status'], $Athlete['status']);

            // Datos y estatus se guardan JUNTOS (una sola transaccion en el modelo): o se aplican los dos o ninguno.
            $result = $this->model->updateAthleteWithStatus($id, $athleteData, $newStatus, $data['status_reason'] ?? '', $this->statusActor());

            if ($result['success']) {
                // Reactivar a un atleta con solvencia de retiro: se borran los datos del retiro para no
                // dejar a un atleta activo con una solvencia vigente. Queda registrado en el log.
                if ($newStatus === 'active' && !empty($Athlete['withdrawn_at'])) {
                    $this->model->clearWithdrawal($id);
                    error_log("[AthletesController] Atleta #{$id} reactivado por " . ($_SESSION['admin_name'] ?? 'desconocido')
                        . "; se anuló la solvencia " . ($Athlete['clearance_number'] ?? '') . " emitida el " . $Athlete['withdrawn_at']);
                }
                $this->redirect('athletes?success=updated');
            } else {
                // Si falla la actualización y se subió una nueva foto, eliminarla
                if ($photoPath !== $Athlete['photo'] && !empty($photoPath) && file_exists($photoPath)) {
                    unlink($photoPath);
                }
                $athleteData = array_merge($Athlete, $data);
                $this->view->render('athletes/edit', [
                    'title' => 'Editar Atleta',
                    'error' => $result['message'] ?? 'Error al actualizar el atleta.',
                    'Athlete' => $athleteData
                ]);
            }
        } else {
            $this->view->render('athletes/edit', [
                'Athlete' => $Athlete,
                'title' => 'Editar Atleta'
            ]);
        }
    }
    
    /**
     * Sirve la foto de un atleta SOLO a quien puede ver a ese atleta (mismo alcance que el resto: el representante solo ve
     * a sus atletas). Las fotos son de menores de edad: el acceso directo a img/atleta_* esta bloqueado (.htaccess) y
     * esta es la unica via. Ruta: athletes/photo?id=N
     *
     * La ruta del archivo sale SIEMPRE de la base de datos, nunca del cliente; se exige que viva dentro de img/ y se llame
     * atleta_*, y que sea una imagen real. Ante cualquier fallo responde 404 igual, sin revelar si el atleta existe.
     */
    public function photo() {
        $this->requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        $athlete = $id > 0 ? $this->model->findById($id) : false;
        $path = (string)($athlete['photo'] ?? '');

        $base = realpath('img');
        $real = $path !== '' ? realpath($path) : false;
        $ok = $athlete
            && $this->canAccessAthlete($id)
            && $base && $real
            && strpos($real, $base . DIRECTORY_SEPARATOR) === 0
            && strpos(basename($real), 'atleta_') === 0
            && is_file($real);
        $mime = $ok ? mime_content_type($real) : '';
        if (!$ok || !in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            http_response_code(404);
            exit;
        }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($real));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');   // el navegador la reutiliza, pero no se comparte en caches publicas
        readfile($real);
        exit;
    }

    /**
     * Muestra los detalles de un atleta específico.
     */
    public function view() {
        $this->requireAuth(); // Todos los roles autenticados pueden ver detalles
        
        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('athletes?error=not_found');
        $this->requireAthleteAccess($id);

        $Athlete = $this->model->findById($id);
        if (!$Athlete) $this->redirect('athletes?error=not_found');

        // El motivo del estatus es sensible: solo administrador y secretario lo reciben. Para el resto se QUITA aqui,
        // antes de la vista, en lugar de ocultarlo con un `if` en la plantilla.
        $role = $_SESSION['admin_role'];
        $statusDetail = Athlete::statusDetailFor($Athlete, $role);
        $Athlete = Athlete::stripStatusDetailFor($Athlete, $role);

        $this->view->render('athletes/view', [
            'Athlete' => $Athlete,
            'statusDetail' => $statusDetail,
            'title' => 'Detalles del Atleta'
        ]);
    }

    /**
     * Pantalla de confirmación de la solvencia de retiro. Muestra si el atleta puede recibirla y,
     * si no, qué pagos pendientes lo impiden. La emisión real ocurre solo por POST (issueClearance).
     * Ruta: athletes/clearance?id=N. Requiere rol de 'administrador' o 'secretario'.
     */
    public function clearance() {
        $this->requireRole(['administrador', 'secretario']);

        $id = $_GET['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        $service = new AthleteClearanceService();
        $eligibility = $service->checkEligibility($id);

        // Si ya tiene solvencia, se manda al detalle (allí está la insignia y el botón de reimpresión).
        if ($eligibility['reason'] === 'already_withdrawn') {
            $this->redirect('athletes/view?id=' . urlencode($id) . '&info=already_withdrawn');
        }

        $this->view->render('athletes/clearance', [
            'Athlete'     => $eligibility['athlete'],
            'eligibility' => $eligibility,
            'title'       => 'Solvencia de Retiro',
        ]);
    }

    /**
     * Emite la solvencia de retiro (acción con consecuencias: el atleta pasa a inactivo) y entrega el PDF.
     * Solo por POST. Requiere rol de 'administrador' o 'secretario'. La comprobación de pagos se repite
     * dentro de una transacción, por lo que un pago registrado en el último momento impide la emisión.
     * Ruta: POST athletes/issueClearance (id)
     */
    public function issueClearance() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('athletes?error=invalid_request');
        }

        $id = $_POST['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        $service = new AthleteClearanceService();

        try {
            $result = $service->issue($id, $_SESSION['admin_name'] ?? 'Sistema');
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error emitiendo solvencia del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/clearance?id=' . urlencode($id) . '&error=failed');
        }

        if (!$result['success']) {
            // No elegible (pagos pendientes) o ya retirado: se vuelve a la pantalla de confirmación.
            $this->redirect('athletes/clearance?id=' . urlencode($id) . '&error=' . urlencode($result['reason']));
        }

        // Emitida. Si el PDF fallara, el retiro ya está registrado y se puede reimprimir desde el detalle.
        try {
            $pdf = $service->buildClearancePdf($id);
        } catch (\Throwable $e) {
            error_log('[AthletesController] Solvencia emitida pero falló el PDF del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/view?id=' . urlencode($id) . '&info=clearance_issued_pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Reimprime la solvencia ya emitida (mismo número y fecha que la original).
     * Ruta: athletes/downloadClearance?id=N. Cualquier usuario con sesión, igual que las demás descargas.
     */
    public function downloadClearance() {
        $this->requireAuth();

        $id = $_GET['id'] ?? null;
        if ($id) { $this->requireAthleteAccess($id); }
        $athlete = $id ? $this->model->findById($id) : null;
        if (!$athlete || empty($athlete['withdrawn_at'])) {
            $this->redirect('athletes?error=not_found');
        }

        try {
            $service = new AthleteClearanceService();
            $pdf = $service->buildClearancePdf($id, $athlete);
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error generando solvencia del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/view?id=' . urlencode($id) . '&error=pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Pantalla de la constancia de pertenencia. Muestra si el atleta puede recibirla (solo activos) y, si ya
     * existe una vigente, sus datos. La emisión real ocurre solo por POST (issueCertificate / sendCertificateEmail).
     * Ruta: athletes/certificate?id=N. Requiere rol de 'administrador' o 'secretario'.
     */
    public function certificate() {
        $this->requireRole(['administrador', 'secretario']);

        $id = $_GET['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        $service = new AthleteCertificateService();
        $status = $service->checkStatus($id);

        $this->view->render('athletes/certificate', [
            'Athlete'          => $status['eligibility']['athlete'],
            'eligibility'      => $status['eligibility'],
            'validCertificate' => $status['valid_certificate'],
            'title'            => 'Constancia de Pertenencia',
        ]);
    }

    /**
     * Emite la constancia (o reutiliza la vigente) y entrega el PDF para descargar.
     * Solo por POST. Requiere rol de 'administrador' o 'secretario'. La comprobación de que el atleta está
     * activo se repite dentro de una transacción en el modelo, así que forzar la petición no la evita.
     * Ruta: POST athletes/issueCertificate (id)
     */
    public function issueCertificate() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('athletes?error=invalid_request');
        }

        $id = $_POST['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        $service = new AthleteCertificateService();

        try {
            $result = $service->issue($id, $_SESSION['admin_name'] ?? 'Sistema');
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error emitiendo la constancia del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/certificate?id=' . urlencode($id) . '&error=failed');
        }

        if (!$result['success']) {
            $this->redirect('athletes/certificate?id=' . urlencode($id) . '&error=' . urlencode($result['reason']));
        }

        try {
            $pdf = $service->buildCertificatePdf($result['certificate']);
        } catch (\Throwable $e) {
            error_log('[AthletesController] Constancia emitida pero falló el PDF del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/certificate?id=' . urlencode($id) . '&error=pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Emite la constancia (o reutiliza la vigente) y la envía por correo al representante.
     * Solo por POST (AJAX, responde JSON). Requiere rol de 'administrador' o 'secretario'.
     * Ruta: POST athletes/sendCertificateEmail (id)
     */
    public function sendCertificateEmail() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        $id = $_POST['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->jsonResponse(['success' => false, 'message' => 'El atleta no existe.'], 404);
            return;
        }

        $service = new AthleteCertificateService();

        try {
            $result = $service->issue($id, $_SESSION['admin_name'] ?? 'Sistema');
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error emitiendo la constancia del atleta #' . $id . ': ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'message' => 'No se pudo emitir la constancia.'], 500);
            return;
        }

        if (!$result['success']) {
            $messages = [
                'inactive'  => 'El atleta está inactivo: solo se emite constancia a atletas activos.',
                'suspended' => 'El atleta está suspendido: solo se emite constancia a atletas activos.',
                'withdrawn' => 'El atleta fue retirado: ya no se le puede emitir una constancia.',
                'not_found' => 'El atleta no existe.',
            ];
            $this->jsonResponse(['success' => false, 'message' => $messages[$result['reason']] ?? 'No se pudo emitir la constancia.'], 409);
            return;
        }

        $send = $service->sendByEmail($result['certificate']);

        if ($send['success']) {
            $this->jsonResponse([
                'success'            => true,
                'certificate_number' => $result['certificate']['certificate_number'],
                'reused'             => $result['reused'],
                'sent_to'            => $send['sent_to'],
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => $send['error'], 'certificate_number' => $result['certificate']['certificate_number']], 500);
        }
    }

    /**
     * Descarga la constancia VIGENTE del atleta (mismo número y fecha que la emitida). Si no hay una vigente,
     * lleva a la pantalla de la constancia para emitir una nueva. No emite nada por sí misma.
     * Ruta: athletes/downloadCertificate?id=N. Requiere rol de 'administrador' o 'secretario': es un documento oficial.
     */
    public function downloadCertificate() {
        $this->requireRole(['administrador', 'secretario']);

        $id = $_GET['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        $service = new AthleteCertificateService();
        $status = $service->checkStatus($id);

        // Solo activos; y solo si ya hay una vigente. Si no, se pasa por la pantalla (que explica el motivo o emite).
        if (!$status['eligibility']['eligible'] || !$status['valid_certificate']) {
            $this->redirect('athletes/certificate?id=' . urlencode($id));
        }

        try {
            $pdf = $service->buildCertificatePdf($status['valid_certificate']);
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error generando la constancia del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/certificate?id=' . urlencode($id) . '&error=pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Descarga la planilla de inscripción de un atleta (PDF con sus datos).
     * Ruta: athletes/downloadEnrollmentForm?id=N
     */
    public function downloadEnrollmentForm() {
        $this->requireAuth();

        $id = $_GET['id'] ?? null;
        if ($id) { $this->requireAthleteAccess($id); }
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        try {
            $service = new AthleteEnrollmentService();
            $pdf = $service->buildEnrollmentPdf($id);
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error generando planilla del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/view?id=' . urlencode($id) . '&error=pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Descarga la ficha técnica de un atleta (uso interno; no se envía al representante).
     * Ruta: athletes/downloadAthleteCard?id=N
     */
    public function downloadAthleteCard() {
        // La ficha técnica incluye datos médicos y es de uso interno de la escuela: no se entrega
        // a representantes (ni siquiera de su propio atleta; ellos reciben la planilla por correo).
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        $id = $_GET['id'] ?? null;
        if (!$id || !$this->model->findById($id)) {
            $this->redirect('athletes?error=not_found');
        }

        try {
            $service = new AthleteEnrollmentService();
            $pdf = $service->buildAthleteCardPdf($id);
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error generando ficha del atleta #' . $id . ': ' . $e->getMessage());
            $this->redirect('athletes/view?id=' . urlencode($id) . '&error=pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Descarga la planilla de inscripción en blanco, para imprimir y llenar a mano.
     * No requiere un atleta. Ruta: athletes/downloadBlankEnrollmentForm
     */
    public function downloadBlankEnrollmentForm() {
        $this->requireAuth();

        try {
            $service = new AthleteEnrollmentService();
            $pdf = $service->buildBlankEnrollmentPdf();
        } catch (\Throwable $e) {
            error_log('[AthletesController] Error generando planilla en blanco: ' . $e->getMessage());
            $this->redirect('athletes?error=pdf_failed');
        }

        $this->downloadPdfString($pdf['content'], $pdf['filename']);
    }

    /**
     * Reenvía manualmente la planilla de inscripción por correo.
     * POST (AJAX). Requiere rol de 'administrador' o 'secretario'.
     */
    public function resendEnrollmentEmail() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            if (!$id || !$this->model->findById($id)) {
                $this->jsonResponse(['success' => false, 'message' => 'El atleta no existe.'], 404);
                return;
            }

            $service = new AthleteEnrollmentService();
            $result = $service->sendEmail($id);

            if ($result['success']) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['error']], 500);
            }
        }
    }

    /**
     * Reenvía manualmente la planilla de inscripción por WhatsApp.
     * POST (AJAX). Requiere rol de 'administrador' o 'secretario'.
     */
    public function resendEnrollmentWhatsApp() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            if (!$id || !$this->model->findById($id)) {
                $this->jsonResponse(['success' => false, 'message' => 'El atleta no existe.'], 404);
                return;
            }

            $service = new AthleteEnrollmentService();
            $result = $service->sendWhatsApp($id);

            if ($result['success']) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['error']], 500);
            }
        }
    }

    /**
     * Envía la planilla de inscripción por correo y WhatsApp sin propagar errores
     * al flujo principal. Cada fallo queda registrado en el propio atleta y en error_log.
     *
     * @param int $athleteId
     */
    private function sendEnrollmentSafely($athleteId) {
        try {
            $service = new AthleteEnrollmentService();
            $service->sendAll($athleteId);
        } catch (\Throwable $e) {
            error_log("[AthletesController] Error inesperado al enviar la inscripción del atleta #{$athleteId}: " . $e->getMessage());
        }
    }

    /**
     * Elimina un atleta.
     * Se realiza mediante una petición POST (generalmente AJAX).
     * Requiere rol de 'administrador'. El secretario NO puede borrar atletas.
     */
    public function delete() {
        $this->requireRole(['administrador']); // Solo administrador puede borrar atletas

        // Eliminación deshabilitada (ver ALLOW_DELETE_ATHLETES en config/config.php).
        // Se rechaza antes de verificar la clave o tocar la base de datos.
        if (!ALLOW_DELETE_ATHLETES) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'La eliminación de atletas está deshabilitada.'
            ], 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            // Verificar la clave de operaciones especiales
            $operationKey = trim($_POST['operation_key'] ?? '');
            require_once 'models/Admin.php';
            $adminModel = new Admin();
            if (!$adminModel->verifyOperationKey($_SESSION['admin_id'], $operationKey)) {
                $this->jsonResponse(['success' => false, 'message' => 'Clave de operaciones incorrecta.'], 403);
                return;
            }
            
            // Obtener datos del atleta antes de eliminarlo para borrar su foto
            $Athlete = $this->model->findById($id);
            
            if ($this->model->delete($id)) {
                // Eliminar la foto si existe
                if ($Athlete && !empty($Athlete['photo']) && file_exists($Athlete['photo'])) {
                    unlink($Athlete['photo']);
                }
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el atleta'], 500);
            }
        }
    }

    /**
     * Valida los datos de un atleta.
     *
     * @param array $data Los datos a validar.
     * @param int|null $excludeId ID a excluir en validaciones de unicidad (para edición).
     * @return array Un array de mensajes de error.
     */
    private function validateAthleteData($data, $excludeId = null) {
        $errors = [];

        // Validación de campos obligatorios
        $requiredFields = [
            'first_name' => 'El nombre del atleta es obligatorio.',
            'last_name' => 'El apellido del atleta es obligatorio.',
            'date_of_birth' => 'La fecha de nacimiento es obligatoria.',
            'parent_name' => 'El nombre del padre/madre es obligatorio.',
            'parent_phone' => 'El teléfono del padre/madre es obligatorio.',
            'parent_email' => 'El email del padre/madre es obligatorio.',
            'address' => 'La dirección es obligatoria.',
            'emergency_contact' => 'El contacto de emergencia es obligatorio.',
            'emergency_phone' => 'El teléfono de emergencia es obligatorio.',
            'medical_conditions' => 'Las condiciones médicas son obligatorias.',
            'category' => 'La categoría es obligatoria.',
            'status' => 'El estado es obligatorio.',
            'jersey_number' => 'El número de camiseta es obligatorio.',
            'parent_id_card' => 'La cédula del padre/madre es obligatoria.'
        ];

        foreach ($requiredFields as $field => $message) {
            if (empty($data[$field])) {
                $errors[] = $message;
            }
        }

        // Validaciones de formato de nombre
        if (!empty($data['first_name']) && !preg_match('/^[\p{L}\s\(\)Ññ-]+$/u', $data['first_name'])) $errors[] = 'El nombre del atleta solo debe contener letras, espacios, guiones y paréntesis.';
        if (!empty($data['last_name']) && !preg_match('/^[\p{L}\s\(\)Ññ-]+$/u', $data['last_name'])) $errors[] = 'El apellido del atleta solo debe contener letras, espacios, guiones y paréntesis.';
        if (!empty($data['parent_name']) && !preg_match('/^[\p{L}\s\(\)Ññ-]+$/u', $data['parent_name'])) $errors[] = 'El nombre del padre/madre solo debe contener letras, espacios, guiones y paréntesis.';
        if (!empty($data['emergency_contact']) && !preg_match('/^[\p{L}\s\(\)Ññ-]+$/u', $data['emergency_contact'])) $errors[] = 'El nombre del contacto de emergencia solo debe contener letras, espacios, guiones y paréntesis.';
 
         // Validación de email
         if (!empty($data['parent_email']) && !filter_var($data['parent_email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'El formato del correo electrónico no es válido.';
 
        // Validación de cédula
        if (!empty($data['id_card']) && !preg_match('/^[0-9]{8}$/', $data['id_card'])) {
            $errors[] = 'La cédula debe contener 8 dígitos.';
        }
        if (!empty($data['parent_id_card']) && !preg_match('/^[0-9]{7,8}$/', $data['parent_id_card'])) {
            $errors[] = 'La cédula del padre/madre debe contener entre 7 y 8 dígitos.';
        }

        // Validación de cédulas duplicadas
        if (!empty($data['id_card']) && $this->model->isAthleteIdCardTaken($data['id_card'], $excludeId)) {
            $errors[] = 'La cédula del atleta ya está registrada en el sistema.';
        }
        if (!empty($data['parent_id_card']) && $this->model->isParentIdCardTaken($data['parent_id_card'], $excludeId)) {
            $errors[] = 'La cédula del representante ya está registrada en el sistema.';
        }

        // Validación de email duplicado
        if (!empty($data['parent_email']) && $this->model->isParentEmailTaken($data['parent_email'], $excludeId)) {
            $errors[] = 'El correo electrónico del representante ya está registrado en el sistema.';
        }

        // Validación de teléfono duplicado
        if (!empty($data['parent_phone']) && $this->model->isParentPhoneTaken($data['parent_phone'], $excludeId)) {
            $errors[] = 'El número de teléfono del representante ya está registrado en el sistema.';
        }

         // Validación de teléfono (formato: 0414-1234567)
        if (!empty($data['parent_phone']) && !preg_match('/^04(12|14|16|24|26|22)-[0-9]{7}$/', $data['parent_phone'])) {
            $errors[] = 'El teléfono del padre/madre debe tener un formato válido (ej: 0414-1234567).';
        }
        if (!empty($data['emergency_phone']) && !preg_match('/^04(12|14|16|24|26|22)-[0-9]{7}$/', $data['emergency_phone'])) {
            $errors[] = 'El teléfono de emergencia debe tener un formato válido (ej: 0414-1234567).';
        }

        // Validación de edad y categoría
        $categoryAgeRanges = [
            'Infantil' => ['min' => 8, 'max' => 10],
            'Pre-juvenil' => ['min' => 11, 'max' => 12],
            'Juvenil' => ['min' => 13, 'max' => 15],
            'Junior' => ['min' => 16, 'max' => 18]
        ];

        if (empty($data['date_of_birth'])) {
            $errors[] = 'La fecha de nacimiento es obligatoria.';
        } else {
            $birthDate = new DateTime($data['date_of_birth']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            
            $category = $data['category'];
            if (isset($categoryAgeRanges[$category])) {
                $minAge = $categoryAgeRanges[$category]['min'];
                $maxAge = $categoryAgeRanges[$category]['max'];
                if ($age < $minAge || $age > $maxAge) {
                    $errors[] = "La edad del atleta ({$age} años) no corresponde a la categoría seleccionada ({$category}: {$minAge}-{$maxAge} años).";
                }
            } else {
                $errors[] = "La categoría seleccionada no es válida.";
            }
        }

        return $errors;
    }

    /**
     * Proporciona resultados de autocompletado para la búsqueda de atletas por cédula.
     * Responde con JSON.
     */
    public function autocomplete() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id'])) {
            echo json_encode([]);
            exit;
        }

        $term = isset($_GET['term']) ? trim($_GET['term']) : '';

        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $athletes = $this->model->searchByIdCard($term, $this->scopeAthleteIds());
        echo json_encode($athletes);
        exit;
    }

    /**
     * Consulta AJAX del formulario de inscripcion: devuelve true si la cedula del representante
     * ya tiene cuenta de acceso. Con esto el formulario oculta los campos de usuario/contrasena
     * (cuenta reutilizada, flujo de hermanos) y solo los exige para un representante nuevo.
     */
    public function checkRepresentativeAccount() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id'])) {
            echo json_encode(['exists' => false]);
            exit;
        }

        $idCard = trim($_GET['id_card'] ?? '');
        if (!preg_match('/^[0-9]{7,8}$/', $idCard)) {
            echo json_encode(['exists' => false]);
            exit;
        }

        echo json_encode(['exists' => (new Admin())->isIdCardTaken($idCard)]);
        exit;
    }

    /**
     * Proporciona resultados de autocompletado para la búsqueda de atletas por nombre.
     * Responde con JSON.
     */
    public function autocompleteByName() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode([]);
            exit;
        }
        
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        
        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        $athletes = $this->model->searchAthletes($term, $this->scopeAthleteIds());
        echo json_encode($athletes);
        exit;
    }
    
    /**
     * Autocompletado unificado por nombre, cédula del atleta o del representante.
     * Responde con JSON.
     */
    public function autocompleteUnified() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode([]);
            exit;
        }
        
        $term = isset($_GET['term']) ? trim($_GET['term']) : '';
        
        if (empty($term)) {
            echo json_encode([]);
            exit;
        }

        // Usa la búsqueda inteligente unificada del modelo (con el scope aplicado en SQL)
        $athletes = $this->model->searchAthletes($term, $this->scopeAthleteIds());
        echo json_encode($athletes);
        exit;
    }

    /**
     * Maneja la subida de fotografía del atleta.
     *
     * @param array $file El archivo subido desde $_FILES
     * @return string|false La ruta del archivo guardado o false en caso de error
     */
    private function handlePhotoUpload($file) {
        // Validar que sea una imagen
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            return false;
        }
        
        // Validar tamaño (2MB máximo)
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }
        
        // Crear directorio img si no existe
        $uploadDir = 'img/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generar nombre único para el archivo
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'atleta_' . uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Mover archivo
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }
}
