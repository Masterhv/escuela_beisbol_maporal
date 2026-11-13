<?php
require_once 'controllers/Controller.php';

/**
 * Controlador para la gestión de administradores.
 * Maneja las operaciones CRUD para los usuarios administradores del sistema.
 */
class AdminsController extends Controller {
    
    /**
     * Constructor. Carga el modelo Admin.
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Admin');
    }
    
    /**
     * Muestra la lista de administradores.
     * Permite buscar administradores por nombre, usuario o email.
     * Requiere rol de 'administrador'.
     */
    public function index() {
        $this->requireRole(['administrador', 'entrenador']);
        
        $search = isset($_GET['q']) ? trim($_GET['q']) : '';
        $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
        
        if (!empty($search)) {
            // Búsqueda por cédula
            $conditions = [
                'id_card LIKE :search' => "%{$search}%"
            ];
            $admins = $this->model->findAll($conditions);
        } else {
            $admins = $this->model->findAll();
        }

        // Calcula la edad de cada administrador
        foreach ($admins as &$admin) {
            if (!empty($admin['birth_date'])) {
                $birthDate = new DateTime($admin['birth_date']);
                $today = new DateTime('today');
                $admin['age'] = $birthDate->diff($today)->y;
            } else {
                $admin['age'] = null;
            }
        }
        
        $this->view->render('admins/index', [
            'admins' => $admins,
            'search' => $search,
            'title' => 'Gestión de Administradores',
            'error' => $error
        ]);
    }
    
    /**
     * Muestra el formulario para crear un nuevo administrador y procesa su envío.
     * Requiere rol de 'administrador'.
     */
    public function create() {
        $this->requireRole(['administrador']); // Solo administrador puede crear otros administradores
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => trim($_POST['full_name']),
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'id_card' => trim($_POST['id_card']),
                'birth_date' => trim($_POST['birth_date']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'emergency_contact_name' => trim($_POST['emergency_contact_name']),
                'emergency_contact_relationship' => trim($_POST['emergency_contact_relationship']),
                'emergency_contact_phone' => trim($_POST['emergency_contact_phone']),
                'role' => $_POST['role'],
                'status' => $_POST['status'],
                'password' => $_POST['password']
            ];

            // Las cuentas de representante ya no se crean aqui: se crean al inscribir un atleta,
            // que reutiliza los datos del formulario de inscripcion. Se rechaza con mensaje claro
            // (aunque el formulario ya no ofrece la opcion, el POST podria venir manipulado).
            if ($data['role'] === 'representante') {
                $this->view->render('admins/create', [
                    'error' => 'Las cuentas de representante se crean desde la inscripción de un atleta: use Atletas → Nuevo Atleta y marque "Crear cuenta de acceso".',
                    'data' => $data,
                    'title' => 'Gestión de Administradores'
                ]);
                return;
            }

            $errors = $this->validateAdminData($data);
            $passwordErrors = self::validatePasswordStrength($data['password']);
            $errors = array_merge($errors, $passwordErrors);

            // Validar clave de operaciones si el rol es administrador
            if ($data['role'] === 'administrador') {
                $opKey = trim($_POST['operation_key'] ?? '');
                $opKeyConfirm = trim($_POST['operation_key_confirm'] ?? '');
                $opKeyErrors = $this->validateOperationKey($opKey, $opKeyConfirm);
                $errors = array_merge($errors, $opKeyErrors);
            }

            if (!empty($errors)) {
                $error = implode('<br>', $errors);
            } else {
                // Hashea la contraseña y crea el administrador
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                if ($data['role'] === 'administrador') {
                    $data['operation_key'] = password_hash(trim($_POST['operation_key']), PASSWORD_DEFAULT);
                }
                if ($this->model->createAdmin($data)) {
                    $this->redirect('admins?success=created');
                } else {
                    // El mensaje nombra el rol de la cuenta que se intento crear (representante,
                    // secretario...): antes siempre decia "el administrador" y confundia al crear representantes.
                    $roleNames = ['administrador' => 'administrador', 'secretario' => 'secretario', 'entrenador' => 'entrenador', 'representante' => 'representante'];
                    $error = "Error al crear la cuenta de " . ($roleNames[$data['role']] ?? 'usuario') . ". Intente de nuevo.";
                }
            }
        }
        
        $this->view->render('admins/create', [
            'error' => isset($error) ? $error : null,
            'data' => isset($data) ? $data : [],
            'title' => 'Nuevo Administrador'
        ]);
    }
    
    /**
     * Muestra el formulario para editar un administrador y procesa su envío.
     * Requiere rol de 'administrador'.
     * Un administrador no puede editar a otro administrador.
     */
    public function edit() {
        $this->requireRole(['administrador']); // Solo administrador puede editar administradores
        
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $admin = $this->model->findById($id);
        
        if (!$admin) {
            $this->redirect('admins?error=not_found');
        }

        // Restricción: Un administrador no puede editar a otro administrador (excepto a sí mismo)
        if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'administrador' && $admin['role'] === 'administrador' && $admin['id'] != $_SESSION['admin_id']) {
            $this->redirect('admins?error=Un administrador no puede editar a otro.');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => trim($_POST['full_name']),
                'username' => trim($_POST['username']),
                'email' => trim($_POST['email']),
                'id_card' => trim($_POST['id_card']),
                'birth_date' => trim($_POST['birth_date']),
                'phone' => trim($_POST['phone']),
                'address' => trim($_POST['address']),
                'emergency_contact_name' => trim($_POST['emergency_contact_name']),
                'emergency_contact_relationship' => trim($_POST['emergency_contact_relationship']),
                'emergency_contact_phone' => trim($_POST['emergency_contact_phone']),
                'role' => $_POST['role'],
                'status' => $_POST['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Editar cualquier cuenta exige la CLAVE DE OPERACIONES ESPECIALES del administrador en sesion.
            $errors = [];
            if (!(new Admin())->verifyOperationKey($_SESSION['admin_id'], trim($_POST['edit_operation_key'] ?? ''))) {
                $errors[] = 'La clave de operaciones especiales es incorrecta o falta.';
            }
            $errors = array_merge($errors, $this->validateAdminData($data, $id));

            // Valida y hashea la nueva contraseña si se proporcionó
            if (!empty($_POST['password'])) {
                $passwordErrors = self::validatePasswordStrength($_POST['password']);
                if (!empty($passwordErrors)) {
                    $errors = array_merge($errors, $passwordErrors);
                } else {
                    $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
            }

            // Validar clave de operaciones si el rol es administrador y se proporcionó una nueva
            if ($data['role'] === 'administrador' && !empty(trim($_POST['operation_key'] ?? ''))) {
                $opKey = trim($_POST['operation_key']);
                $opKeyConfirm = trim($_POST['operation_key_confirm'] ?? '');
                $opKeyErrors = $this->validateOperationKey($opKey, $opKeyConfirm);
                if (!empty($opKeyErrors)) {
                    $errors = array_merge($errors, $opKeyErrors);
                } else {
                    $data['operation_key'] = password_hash($opKey, PASSWORD_DEFAULT);
                }
            }

            if (!empty($errors)) {
                $error = implode('<br>', $errors);
            } else {
                if ($this->model->update($id, $data)) {
                    error_log('[AdminsController] cuenta #' . $id . ' (' . $data['username'] . ') actualizada por '
                        . ($_SESSION['admin_name'] ?? 'desconocido') . ' [rol ' . ($_SESSION['admin_role'] ?? '') . ']');
                    $this->redirect('admins?success=updated');
                } else {
                    $error = "Error al actualizar el administrador";
                }
            }
        }
        
        // Calcula la edad para mostrarla en la vista
        if (!empty($admin['birth_date'])) {
            $birthDate = new DateTime($admin['birth_date']);
            $today = new DateTime('today');
            $admin['age'] = $birthDate->diff($today)->y;
        } else {
            $admin['age'] = null;
        }

        $this->view->render('admins/edit', [
            'admin' => $admin,
            'error' => isset($error) ? $error : null,
            'title' => 'Editar Administrador'
        ]);
    }
    
    /**
     * Elimina un administrador.
     * Se realiza mediante una petición POST (generalmente AJAX).
     * Requiere rol de 'administrador'.
     * Impide la auto-eliminación y la eliminación de un administrador por otro.
     */
    public function delete() {
        $this->requireRole(['administrador']);

        // Eliminacion de cuentas DESHABILITADA (ver ALLOW_DELETE_ADMINS en config/config.php):
        // ninguna cuenta, de ningun rol, se borra del sistema.
        if (!ALLOW_DELETE_ADMINS) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'La eliminaci&oacute;n de cuentas est&aacute; deshabilitada.'
            ], 403);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            
            // No permitir eliminar la propia cuenta
            if ($id == $_SESSION['admin_id']) {
                $this->jsonResponse(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.'], 400);
                return;
            }

            $admin_to_delete = $this->model->findById($id);

            if (!$admin_to_delete) {
                $this->jsonResponse(['success' => false, 'message' => 'Administrador no encontrado.'], 404);
                return;
            }

            // Un administrador no puede eliminar a otro administrador
            if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'administrador' && $admin_to_delete['role'] === 'administrador') {
                $this->jsonResponse(['success' => false, 'message' => 'Un administrador no puede eliminar a otro.'], 403);
                return;
            }
            
            if ($this->model->delete($id)) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Error al eliminar el administrador.'], 500);
            }
        }
    }

    /**
     * Muestra los detalles de un administrador específico.
     * Requiere rol de 'administrador'.
     */
    public function view() {
        $this->requireRole(['administrador', 'entrenador']); // Administrador y entrenador pueden ver detalles
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $admin = $this->model->findById($id);
        
        if (!$admin) {
            $this->redirect('admins?error=not_found');
        }

        $this->view->render('admins/view', [
            'admin' => $admin,
            'title' => 'Detalles del Administrador'
        ]);
    }

    /**
     * Valida los datos de un administrador.
     *
     * @param array $data Los datos a validar.
     * @param int|null $excludeId ID a excluir en validaciones de unicidad (para edición).
     * @return array Un array de mensajes de error.
     */
    private function validateAdminData($data, $excludeId = null) {
        $errors = [];

        // Validaciones de campos obligatorios
        if (empty($data['full_name'])) $errors[] = 'El nombre completo es obligatorio.';
        if (empty($data['username'])) $errors[] = 'El nombre de usuario es obligatorio.';
        if (empty($data['email'])) $errors[] = 'El correo electrónico es obligatorio.';
        if (empty($data['role'])) $errors[] = 'El rol es obligatorio.';
        if (empty($data['id_card'])) $errors[] = 'La cédula es obligatoria.';
        if (empty($data['phone'])) $errors[] = 'El teléfono es obligatorio.';
        if (empty($data['address'])) $errors[] = 'La dirección es obligatoria.';
        if (empty($data['emergency_contact_name'])) $errors[] = 'El nombre del contacto de emergencia es obligatorio.';
        if (empty($data['emergency_contact_relationship'])) $errors[] = 'El parentesco del contacto de emergencia es obligatorio.';
        if (empty($data['emergency_contact_phone'])) $errors[] = 'El teléfono del contacto de emergencia es obligatorio.';

        // Validación de rol
        $allowed_roles = ['secretario', 'administrador', 'entrenador', 'representante'];
        if (!in_array($data['role'], $allowed_roles)) {
            $errors[] = 'El rol seleccionado no es válido.';
        }
 
        // Validaciones de formato
        if (!empty($data['full_name']) && !preg_match('/^[\p{L}\s\(\)Ññ-]+$/u', $data['full_name'])) $errors[] = 'El nombre completo solo debe contener letras y espacios.';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'El formato del correo electrónico no es válido.';
        if (!empty($data['id_card']) && !preg_match('/^[0-9]{7,8}$/', $data['id_card'])) $errors[] = 'La cédula debe contener entre 7 y 8 dígitos numéricos.';
        if (!empty($data['phone']) && !preg_match('/^\+?[0-9\s-]{7,20}$/', $data['phone'])) $errors[] = 'El teléfono debe tener un formato válido (ej: +58 424-1234567).';
        if (!empty($data['emergency_contact_phone']) && !preg_match('/^\+?[0-9\s-]{7,20}$/', $data['emergency_contact_phone'])) $errors[] = 'El teléfono de emergencia debe tener un formato válido (ej: +58 424-1234567).';

        // Validación de duplicados
        if (!empty($data['username']) && $this->model->isUsernameTaken($data['username'], $excludeId)) {
            $errors[] = 'El nombre de usuario ya está registrado en el sistema.';
        }
        if (!empty($data['id_card']) && $this->model->isIdCardTaken($data['id_card'], $excludeId)) {
            $errors[] = 'La cédula ya está registrada en el sistema.';
        }
        if (!empty($data['email']) && $this->model->isEmailTaken($data['email'], $excludeId)) {
            $errors[] = 'El correo electrónico ya está registrado en el sistema.';
        }

        // Validación de edad
        if (!empty($data['birth_date'])) {
            try {
                $birthDate = new DateTime($data['birth_date']);
                $today = new DateTime('today');
                $age = $birthDate->diff($today)->y;
                if ($age < 18) {
                    $errors[] = 'El administrador debe ser mayor de 18 años.';
                }
            } catch (Exception $e) {
                $errors[] = 'El formato de la fecha de nacimiento no es válido.';
            }
        } else {
            $errors[] = 'La fecha de nacimiento es obligatoria.';
        }

        return $errors;
    }

    /**
     * Valida una clave de operaciones especiales (4 dígitos numéricos).
     *
     * @param string $key La clave ingresada.
     * @param string $confirm La confirmación de la clave.
     * @return array Un array de mensajes de error.
     */
    private function validateOperationKey($key, $confirm) {
        $errors = [];
        if (empty($key)) {
            $errors[] = 'La clave de operaciones especiales es obligatoria para el rol Administrador.';
        } elseif (!preg_match('/^\d{4}$/', $key)) {
            $errors[] = 'La clave de operaciones debe ser exactamente 4 dígitos numéricos.';
        } elseif ($key !== $confirm) {
            $errors[] = 'Las claves de operaciones especiales no coinciden.';
        }
        return $errors;
    }

    /**
     * Verifica la clave de operaciones especiales del administrador en sesión.
     * Responde con JSON. Usado por AJAX antes de ejecutar eliminaciones.
     */
    public function verifyOperationKey() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'administrador') {
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $key = trim($_POST['operation_key'] ?? '');

        if (!preg_match('/^\d{4}$/', $key)) {
            echo json_encode(['success' => false, 'message' => 'La clave debe ser de 4 dígitos numéricos.']);
            exit;
        }

        if ($this->model->verifyOperationKey($_SESSION['admin_id'], $key)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Clave de operaciones incorrecta.']);
        }
        exit;
    }

    /**
     * Valida la fortaleza de una contraseÃ±a. Estatico y publico: el alta de la cuenta del representante
     * desde la inscripcion del atleta (AthletesController) aplica exactamente las mismas reglas.
     *
     * @param string $password La contraseÃ±a a validar.
     * @return array Un array de mensajes de error.
     */
    public static function validatePasswordStrength($password) {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres.";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula.";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número.";
        }
        if (!preg_match('/[!@#$%^&*]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un símbolo (!@#$%^&*).";
        }
        return $errors;
    }

    /**
     * Proporciona resultados de autocompletado para la búsqueda de administradores.
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

        $admins = $this->model->searchAdmins($term);
        echo json_encode($admins);
        exit;
    }

    /**
     * Proporciona resultados de autocompletado para la búsqueda de administradores por cédula.
     * Responde con JSON.
     */
    public function autocompleteByIdCard() {
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

        $admins = $this->model->searchByIdCard($term);
        echo json_encode($admins);
        exit;
    }
}
