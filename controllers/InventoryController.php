<?php
require_once 'controllers/Controller.php';

/**
 * Controlador para la gestión del inventario de equipamiento deportivo.
 * Accesible para todos los roles excepto representante.
 */
class InventoryController extends Controller {

    /** @var Inventory */
    private $inventoryModel;

    public function __construct() {
        parent::__construct();
        require_once 'models/Inventory.php';
        $this->inventoryModel = new Inventory();
    }

    // -----------------------------------------------------------------------
    // INDEX — listado principal
    // -----------------------------------------------------------------------
    public function index() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        $filters = [
            'q'         => trim($_GET['q'] ?? ''),
            'low_stock' => $_GET['low_stock'] ?? '',
        ];

        $items = $this->inventoryModel->getItemsWithFilters($filters);
        $stats = $this->inventoryModel->getStats();

        $this->view->render('inventory/index', [
            'title'   => 'Inventario',
            'items'   => $items,
            'stats'   => $stats,
            'filters' => $filters,
            'success' => $_GET['success'] ?? null,
            'error'   => $_GET['error']   ?? null,
        ]);
    }

    // -----------------------------------------------------------------------
    // CREATE
    // -----------------------------------------------------------------------
    public function create() {
        $this->requireRole(['administrador', 'secretario']);

        $error = null;
        $data  = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data   = $this->collectPost();
            $errors = $this->validateItem($data);

            if (empty($errors)) {
                $data['created_by']      = $_SESSION['admin_id'];
                $data['created_by_name'] = $_SESSION['admin_name'];

                if ($this->inventoryModel->create($data)) {
                    $this->redirect('inventory?success=created');
                } else {
                    $error = 'Error al registrar el ítem. Intente de nuevo.';
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $this->view->render('inventory/create', [
            'title' => 'Nuevo Ítem de Inventario',
            'data'  => $data,
            'error' => $error,
        ]);
    }

    // -----------------------------------------------------------------------
    // EDIT
    // -----------------------------------------------------------------------
    public function edit() {
        $this->requireRole(['administrador', 'secretario']);

        $id   = (int)($_GET['id'] ?? 0);
        $item = $this->inventoryModel->findById($id);

        if (!$item) $this->redirect('inventory?error=not_found');

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data   = $this->collectPost(false);
            $errors = $this->validateItem($data);

            if (empty($errors)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                if ($this->inventoryModel->update($id, $data)) {
                    $this->redirect('inventory/view?id=' . $id . '&success=updated');
                } else {
                    $error = 'Error al actualizar el ítem.';
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $this->view->render('inventory/edit', [
            'title' => 'Editar Ítem',
            'item'  => $item,
            'error' => $error,
        ]);
    }

    // -----------------------------------------------------------------------
    // VIEW — detalle + historial de movimientos
    // -----------------------------------------------------------------------
    public function view() {
        $this->requireRole(['administrador', 'secretario', 'entrenador']);

        $id   = (int)($_GET['id'] ?? 0);
        $item = $this->inventoryModel->findById($id);

        if (!$item) $this->redirect('inventory?error=not_found');

        $movements = $this->inventoryModel->getMovements($id);

        $this->view->render('inventory/view', [
            'title'     => 'Detalle de Ítem',
            'item'      => $item,
            'movements' => $movements,
            'success'   => $_GET['success'] ?? null,
        ]);
    }

    // -----------------------------------------------------------------------
    // DELETE (AJAX POST)
    // -----------------------------------------------------------------------
    public function delete() {
        $this->requireRole(['administrador']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        $id   = (int)($_POST['id'] ?? 0);
        $item = $this->inventoryModel->findById($id);

        if (!$item) {
            $this->jsonResponse(['success' => false, 'message' => 'Ítem no encontrado.'], 404);
            return;
        }

        if ($this->inventoryModel->delete($id)) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el ítem.'], 500);
        }
    }

    // -----------------------------------------------------------------------
    // MOVEMENT — registrar entrada / salida / ajuste (AJAX POST)
    // -----------------------------------------------------------------------
    public function movement() {
        $this->requireRole(['administrador', 'secretario']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Método no permitido.'], 405);
            return;
        }

        $itemId   = (int)($_POST['item_id']  ?? 0);
        $type     = trim($_POST['type']      ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $reason   = trim($_POST['reason']    ?? '');

        if (!$itemId || !in_array($type, ['entrada', 'salida', 'ajuste'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Datos inválidos.'], 422);
            return;
        }
        if ($quantity <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'La cantidad debe ser mayor a 0.'], 422);
            return;
        }
        if ($reason === '') {
            $reason = ['entrada' => 'Entrada', 'salida' => 'Salida', 'ajuste' => 'Ajuste'][$type];
        }

        $result = $this->inventoryModel->registerMovement(
            $itemId, $type, $quantity, $reason,
            $_SESSION['admin_id'], $_SESSION['admin_name']
        );

        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    // -----------------------------------------------------------------------
    // Helpers privados
    // -----------------------------------------------------------------------

    /**
     * Solo recoge los campos que muestran los formularios simplificados.
     * Si $withQuantity es false (edición), la cantidad no se modifica:
     * se cambia únicamente mediante movimientos.
     */
    private function collectPost($withQuantity = true) {
        $data = [
            'name'         => trim($_POST['name'] ?? ''),
            'min_quantity' => (int)($_POST['min_quantity'] ?? 0),
        ];
        if ($withQuantity) {
            $data['quantity'] = (int)($_POST['quantity'] ?? 0);
        }
        return $data;
    }

    private function validateItem($data) {
        $errors = [];

        if (empty($data['name']))        $errors[] = 'El nombre del ítem es obligatorio.';
        if (strlen($data['name']) > 150) $errors[] = 'El nombre no puede superar 150 caracteres.';

        if (isset($data['quantity']) && $data['quantity'] < 0) $errors[] = 'La cantidad no puede ser negativa.';
        if ($data['min_quantity'] < 0) $errors[] = 'La cantidad mínima no puede ser negativa.';

        return $errors;
    }
}
