<?php
require_once 'controllers/Controller.php';
require_once 'models/Tournament.php';
require_once 'models/Athlete.php';

/**
 * Controlador para la gestión de torneos.
 */
class TournamentsController extends Controller {

    private $tournamentModel;
    private $athleteModel;

    public function __construct() {
        parent::__construct();
        $this->tournamentModel = new Tournament();
        $this->athleteModel    = new Athlete();
    }

    /* ------------------------------------------------------------------ */
    /*  INDEX                                                               */
    /* ------------------------------------------------------------------ */
    public function index() {
        $this->requireAuth();

        // Actualizar estatus por fechas automáticamente
        $this->tournamentModel->updateStatusByDate();

        $filters     = $_GET;
        $tournaments = $this->tournamentModel->getTournamentsWithFilters($filters);
        $stats       = $this->tournamentModel->getStats();

        $this->view->render('tournaments/index', [
            'title'       => 'Cronograma de Torneos',
            'tournaments' => $tournaments,
            'stats'       => $stats,
            'filters'     => $filters,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  CREATE                                                              */
    /* ------------------------------------------------------------------ */
    public function create() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->collectPost();
            $errors = $this->validate($data);

            if (!empty($errors)) {
                $this->view->render('tournaments/create', [
                    'title'  => 'Nuevo Torneo',
                    'errors' => $errors,
                    'old'    => $data,
                ]);
                return;
            }

            $data['created_by'] = $_SESSION['admin_id'];

            if ($this->tournamentModel->create($data)) {
                $this->redirect('tournaments?success=created');
            } else {
                $this->redirect('tournaments/create?error=failed');
            }
        }

        $this->view->render('tournaments/create', [
            'title' => 'Nuevo Torneo',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  EDIT                                                                */
    /* ------------------------------------------------------------------ */
    public function edit() {
        $this->requireRole(['administrador', 'secretario']);

        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('tournaments?error=not_found');

        $tournament = $this->tournamentModel->findById($id);
        if (!$tournament) $this->redirect('tournaments?error=not_found');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data   = $this->collectPost();
            $errors = $this->validate($data);

            if (!empty($errors)) {
                $this->view->render('tournaments/edit', [
                    'title'      => 'Editar Torneo',
                    'tournament' => array_merge($tournament, $data),
                    'errors'     => $errors,
                ]);
                return;
            }

            if ($this->tournamentModel->update($id, $data)) {
                $this->redirect('tournaments?success=updated');
            } else {
                $this->redirect('tournaments/edit?id=' . $id . '&error=failed');
            }
        }

        $this->view->render('tournaments/edit', [
            'title'      => 'Editar Torneo',
            'tournament' => $tournament,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  VIEW                                                                */
    /* ------------------------------------------------------------------ */
    public function view() {
        $this->requireAuth();

        $id = $_GET['id'] ?? null;
        if (!$id) $this->redirect('tournaments?error=not_found');

        $tournament = $this->tournamentModel->getTournamentWithParticipants($id);
        if (!$tournament) $this->redirect('tournaments?error=not_found');

        // Atletas disponibles para añadir (excluyendo los ya inscritos)
        $allAthletes = $this->athleteModel->findAll(['status = :status' => 'active'], 'last_name ASC');
        $participantIds = array_column($tournament['participants'], 'athlete_id');
        $availableAthletes = array_filter($allAthletes, fn($a) => !in_array($a['id'], $participantIds));

        $this->view->render('tournaments/view', [
            'title'             => 'Detalle del Torneo',
            'tournament'        => $tournament,
            'availableAthletes' => array_values($availableAthletes),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  DELETE (AJAX)                                                       */
    /* ------------------------------------------------------------------ */
    public function delete() {
        $this->requireRole(['administrador']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            $operationKey = trim($_POST['operation_key'] ?? '');
            require_once 'models/Admin.php';
            $adminModel = new Admin();
            if (!$adminModel->verifyOperationKey($_SESSION['admin_id'], $operationKey)) {
                $this->jsonResponse(['success' => false, 'message' => 'Clave de operaciones incorrecta.'], 403);
                return;
            }

            if ($this->tournamentModel->delete($id)) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el torneo.'], 500);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  ADD PARTICIPANT (AJAX)                                              */
    /* ------------------------------------------------------------------ */
    public function addParticipant() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tournamentId = (int)($_POST['tournament_id'] ?? 0);
            $athleteId    = (int)($_POST['athlete_id']    ?? 0);
            $notes        = trim($_POST['notes'] ?? '');

            if (!$tournamentId || !$athleteId) {
                $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos.'], 400);
                return;
            }

            if ($this->tournamentModel->isParticipant($tournamentId, $athleteId)) {
                $this->jsonResponse(['success' => false, 'message' => 'El atleta ya está inscrito.'], 409);
                return;
            }

            if ($this->tournamentModel->addParticipant($tournamentId, $athleteId, $notes)) {
                // Devolver datos del atleta para actualizar la UI sin recargar
                $athlete = $this->athleteModel->findById($athleteId);
                $this->jsonResponse([
                    'success' => true,
                    'athlete' => [
                        'id'         => $athlete['id'],
                        'first_name' => $athlete['first_name'],
                        'last_name'  => $athlete['last_name'],
                        'category'   => $athlete['category'],
                        'age'        => $athlete['age'],
                        'photo'      => $athlete['photo'],
                        'notes'      => $notes,
                    ],
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al inscribir al atleta.'], 500);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  REMOVE PARTICIPANT (AJAX)                                           */
    /* ------------------------------------------------------------------ */
    public function removeParticipant() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tournamentId = (int)($_POST['tournament_id'] ?? 0);
            $athleteId    = (int)($_POST['athlete_id']    ?? 0);

            if (!$tournamentId || !$athleteId) {
                $this->jsonResponse(['success' => false, 'message' => 'Datos incompletos.'], 400);
                return;
            }

            if ($this->tournamentModel->removeParticipant($tournamentId, $athleteId)) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al retirar al atleta.'], 500);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */
    private function collectPost() {
        return [
            'name'        => trim($_POST['name']        ?? ''),
            'category'    => trim($_POST['category']    ?? ''),
            'location'    => trim($_POST['location']    ?? ''),
            'start_date'  => trim($_POST['start_date']  ?? ''),
            'end_date'    => !empty(trim($_POST['end_date'] ?? '')) ? trim($_POST['end_date']) : null,
            'status'      => trim($_POST['status']      ?? 'programado'),
            'description' => trim($_POST['description'] ?? ''),
            'notes'       => trim($_POST['notes']       ?? ''),
        ];
    }

    private function validate($data) {
        $errors = [];
        if (empty($data['name']))       $errors[] = 'El nombre del torneo es obligatorio.';
        if (empty($data['category']))   $errors[] = 'La categoría es obligatoria.';
        if (empty($data['location']))   $errors[] = 'El lugar es obligatorio.';
        if (empty($data['start_date'])) $errors[] = 'La fecha de inicio es obligatoria.';
        if (!empty($data['end_date']) && $data['end_date'] < $data['start_date']) {
            $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
        }
        return $errors;
    }
}
