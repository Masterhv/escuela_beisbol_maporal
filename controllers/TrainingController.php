<?php
require_once 'controllers/Controller.php';

/**
 * Controlador para la gestión de cronogramas de entrenamiento.
 * Entrenadores pueden crear y gestionar sus propios cronogramas.
 * Administradores y secretarios tienen acceso completo de lectura.
 */
class TrainingController extends Controller {

    /** @var TrainingSchedule */
    private $scheduleModel;

    public function __construct() {
        parent::__construct();
        require_once 'models/TrainingSchedule.php';
        $this->scheduleModel = new TrainingSchedule();
    }

    // -------------------------------------------------------------------------
    // INDEX — lista de cronogramas
    // -------------------------------------------------------------------------
    public function index() {
        $this->requireAuth();

        $isRepresentante = ($_SESSION['admin_role'] === 'representante');

        $filters = [
            'q'        => trim($_GET['q'] ?? ''),
            'category' => $_GET['category'] ?? '',
            // Representantes solo ven cronogramas activos
            'status'   => $isRepresentante ? 'activo' : ($_GET['status'] ?? ''),
            'coach_id' => '',
        ];

        // Entrenadores solo ven sus propios cronogramas
        if ($_SESSION['admin_role'] === 'entrenador') {
            $filters['coach_id'] = $_SESSION['admin_id'];
        }

        $schedules = $this->scheduleModel->getSchedulesWithFilters($filters);
        $stats     = $this->scheduleModel->getStats();
        $coaches   = $this->scheduleModel->getCoaches();

        $this->view->render('training/index', [
            'title'          => 'Cronogramas de Entrenamiento',
            'schedules'      => $schedules,
            'stats'          => $stats,
            'coaches'        => $coaches,
            'filters'        => $filters,
            'isRepresentante' => $isRepresentante,
            'success'        => $_GET['success'] ?? null,
            'error'          => $_GET['error'] ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // CREATE — nuevo cronograma
    // -------------------------------------------------------------------------
    public function create() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        $coaches = $this->scheduleModel->getCoaches();
        $error   = null;
        $data    = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectSchedulePost();
            $errors = $this->validateSchedule($data);

            if (empty($errors)) {
                // Entrenadores solo pueden crear cronogramas a su nombre
                if ($_SESSION['admin_role'] === 'entrenador') {
                    $data['coach_id']   = $_SESSION['admin_id'];
                    $data['coach_name'] = $_SESSION['admin_name'];
                }

                $id = $this->scheduleModel->createSchedule($data);
                if ($id) {
                    // Guardar sesiones de entrenamiento si se enviaron
                    $this->saveSessions($id, $_POST);
                    $this->redirect('training?success=created');
                } else {
                    $error = 'Error al crear el cronograma. Intente de nuevo.';
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $this->view->render('training/create', [
            'title'   => 'Nuevo Cronograma',
            'coaches' => $coaches,
            'data'    => $data,
            'error'   => $error,
        ]);
    }

    // -------------------------------------------------------------------------
    // EDIT — editar cronograma
    // -------------------------------------------------------------------------
    public function edit() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        $id       = (int)($_GET['id'] ?? 0);
        $schedule = $this->scheduleModel->getScheduleWithSessions($id);

        if (!$schedule) {
            $this->redirect('training?error=not_found');
        }

        // Entrenadores solo pueden editar sus propios cronogramas
        if ($_SESSION['admin_role'] === 'entrenador' && $schedule['coach_id'] != $_SESSION['admin_id']) {
            $this->redirect('training?error=no_permission');
        }

        $coaches = $this->scheduleModel->getCoaches();
        $error   = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectSchedulePost();
            $errors = $this->validateSchedule($data);

            if (empty($errors)) {
                if ($_SESSION['admin_role'] === 'entrenador') {
                    $data['coach_id']   = $_SESSION['admin_id'];
                    $data['coach_name'] = $_SESSION['admin_name'];
                }
                $data['updated_at'] = date('Y-m-d H:i:s');

                if ($this->scheduleModel->updateSchedule($id, $data)) {
                    // Reemplazar todas las sesiones
                    $this->scheduleModel->deleteSessionsBySchedule($id);
                    $this->saveSessions($id, $_POST);
                    $this->redirect('training/view?id=' . $id . '&success=updated');
                } else {
                    $error = 'Error al actualizar el cronograma.';
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $this->view->render('training/edit', [
            'title'    => 'Editar Cronograma',
            'schedule' => $schedule,
            'coaches'  => $coaches,
            'error'    => $error,
        ]);
    }

    // -------------------------------------------------------------------------
    // VIEW — detalle de un cronograma
    // -------------------------------------------------------------------------
    public function view() {
        $this->requireAuth();

        $id       = (int)($_GET['id'] ?? 0);
        $schedule = $this->scheduleModel->getScheduleWithSessions($id);

        if (!$schedule) {
            $this->redirect('training?error=not_found');
        }

        // Entrenadores solo pueden ver sus propios cronogramas
        if ($_SESSION['admin_role'] === 'entrenador' && $schedule['coach_id'] != $_SESSION['admin_id']) {
            $this->redirect('training?error=no_permission');
        }

        $this->view->render('training/view', [
            'title'    => 'Detalle del Cronograma',
            'schedule' => $schedule,
            'success'  => $_GET['success'] ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE — eliminar cronograma (AJAX POST)
    // -------------------------------------------------------------------------
    public function delete() {
        $this->requireRole(['administrador', 'entrenador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        $id       = (int)($_POST['id'] ?? 0);
        $schedule = $this->scheduleModel->findById($id);

        if (!$schedule) {
            $this->jsonResponse(['success' => false, 'message' => 'Cronograma no encontrado.'], 404);
            return;
        }

        // Entrenadores solo pueden borrar los suyos
        if ($_SESSION['admin_role'] === 'entrenador' && $schedule['coach_id'] != $_SESSION['admin_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'No tiene permiso para eliminar este cronograma.'], 403);
            return;
        }

        if ($this->scheduleModel->delete($id)) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el cronograma.'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // ADD SESSION — agregar sesión a un cronograma (AJAX POST)
    // -------------------------------------------------------------------------
    public function addSession() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        $scheduleId = (int)($_POST['schedule_id'] ?? 0);
        $schedule   = $this->scheduleModel->findById($scheduleId);

        if (!$schedule) {
            $this->jsonResponse(['success' => false, 'message' => 'Cronograma no encontrado.'], 404);
            return;
        }

        if ($_SESSION['admin_role'] === 'entrenador' && $schedule['coach_id'] != $_SESSION['admin_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'No tiene permiso.'], 403);
            return;
        }

        $sessionData = [
            'schedule_id' => $scheduleId,
            'day_of_week' => $_POST['day_of_week'] ?? '',
            'start_time'  => $_POST['start_time'] ?? '',
            'end_time'    => $_POST['end_time'] ?? '',
            'activity'    => trim($_POST['activity'] ?? ''),
            'intensity'   => $_POST['intensity'] ?? 'media',
            'materials'   => trim($_POST['materials'] ?? ''),
        ];

        $errors = $this->validateSession($sessionData, $scheduleId);
        if (!empty($errors)) {
            $this->jsonResponse(['success' => false, 'message' => implode(' ', $errors)], 422);
            return;
        }

        $newId = $this->scheduleModel->createSession($sessionData);
        if ($newId) {
            $session = $this->scheduleModel->getSessionById($newId);
            $this->jsonResponse(['success' => true, 'session' => $session]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al agregar la sesión.'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // DELETE SESSION — eliminar sesión (AJAX POST)
    // -------------------------------------------------------------------------
    public function deleteSession() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        $sessionId = (int)($_POST['session_id'] ?? 0);
        $session   = $this->scheduleModel->getSessionById($sessionId);

        if (!$session) {
            $this->jsonResponse(['success' => false, 'message' => 'Sesión no encontrada.'], 404);
            return;
        }

        $schedule = $this->scheduleModel->findById($session['schedule_id']);
        if ($_SESSION['admin_role'] === 'entrenador' && $schedule['coach_id'] != $_SESSION['admin_id']) {
            $this->jsonResponse(['success' => false, 'message' => 'No tiene permiso.'], 403);
            return;
        }

        if ($this->scheduleModel->deleteSession($sessionId)) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar la sesión.'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function collectSchedulePost() {
        return [
            'title'      => trim($_POST['title'] ?? ''),
            'category'   => trim($_POST['category'] ?? ''),
            'coach_id'   => (int)($_POST['coach_id'] ?? 0),
            'coach_name' => trim($_POST['coach_name'] ?? ''),
            'location'   => trim($_POST['location'] ?? ''),
            'objective'  => trim($_POST['objective'] ?? ''),
            'notes'      => trim($_POST['notes'] ?? ''),
            'status'     => $_POST['status'] ?? 'activo',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date'   => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        ];
    }

    private function validateSchedule($data) {
        $errors = [];
        if (empty($data['title']))      $errors[] = 'El título del cronograma es obligatorio.';
        if (empty($data['category']))   $errors[] = 'La categoría es obligatoria.';
        if (empty($data['location']))   $errors[] = 'El lugar de entrenamiento es obligatorio.';
        if (empty($data['start_date'])) $errors[] = 'La fecha de inicio es obligatoria.';

        if ($_SESSION['admin_role'] !== 'entrenador') {
            if (empty($data['coach_id'])) $errors[] = 'Debe seleccionar un entrenador.';
        }

        if (!empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
        }

        $validStatuses = ['activo', 'pausado', 'finalizado'];
        if (!in_array($data['status'], $validStatuses)) {
            $errors[] = 'Estado no válido.';
        }

        return $errors;
    }

    private function validateSession($data, $scheduleId, $excludeId = null) {
        $errors = [];
        $validDays = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
        $validIntensities = ['baja','media','alta'];

        if (!in_array($data['day_of_week'], $validDays))   $errors[] = 'Día no válido.';
        if (empty($data['start_time']))                    $errors[] = 'La hora de inicio es obligatoria.';
        if (empty($data['end_time']))                      $errors[] = 'La hora de fin es obligatoria.';
        if (empty($data['activity']))                      $errors[] = 'La actividad es obligatoria.';
        if (!in_array($data['intensity'], $validIntensities)) $errors[] = 'Intensidad no válida.';

        if (!empty($data['start_time']) && !empty($data['end_time'])) {
            if ($data['end_time'] <= $data['start_time']) {
                $errors[] = 'La hora de fin debe ser posterior a la hora de inicio.';
            } elseif (empty($errors)) {
                if ($this->scheduleModel->hasTimeConflict($scheduleId, $data['day_of_week'], $data['start_time'], $data['end_time'], $excludeId)) {
                    $errors[] = 'El horario se solapa con otra sesión en ese mismo día.';
                }
            }
        }

        return $errors;
    }

    /**
     * Procesa el array de sesiones enviadas en el POST del formulario y las guarda.
     */
    private function saveSessions($scheduleId, $post) {
        $days       = $post['session_day'] ?? [];
        $starts     = $post['session_start'] ?? [];
        $ends       = $post['session_end'] ?? [];
        $activities = $post['session_activity'] ?? [];
        $intensities = $post['session_intensity'] ?? [];
        $materials  = $post['session_materials'] ?? [];

        $count = count($days);
        for ($i = 0; $i < $count; $i++) {
            if (empty(trim($activities[$i] ?? ''))) continue;
            $sessionData = [
                'schedule_id' => $scheduleId,
                'day_of_week' => $days[$i] ?? 'lunes',
                'start_time'  => $starts[$i] ?? '08:00',
                'end_time'    => $ends[$i] ?? '09:00',
                'activity'    => trim($activities[$i]),
                'intensity'   => $intensities[$i] ?? 'media',
                'materials'   => trim($materials[$i] ?? ''),
            ];
            // Skip rows with invalid times or conflicts
            $errs = $this->validateSession($sessionData, $scheduleId);
            if (empty($errs)) {
                $this->scheduleModel->createSession($sessionData);
            }
        }
    }
}
