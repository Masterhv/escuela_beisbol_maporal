<?php
require_once 'controllers/Controller.php';

/**
 * Controlador para la autenticación de usuarios.
 * Maneja el inicio de sesión, cierre de sesión y la gestión del perfil del usuario.
 */
class AuthController extends Controller {
    
    /**
     * Constructor. Carga el modelo Admin.
     */
    public function __construct() {
        parent::__construct();
        $this->loadModel('Admin');
    }
    
    /**
     * Muestra el formulario de inicio de sesión y procesa la autenticación.
     * Si el usuario ya está logueado, lo redirige al dashboard.
     */
    public function login() {
        // Si ya hay una sesión activa, redirigir al dashboard
        if (isset($_SESSION['admin_id'])) {
            $this->redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            
            if (empty($username) || empty($password)) {
                $error = "Por favor, complete todos los campos";
            } else {
                // Autenticar al usuario a través del modelo
                $admin = $this->model->authenticate($username, $password);
                
                if ($admin) {
                    // Si la autenticación es exitosa, se crea la sesión
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['last_activity'] = time(); // Para el control de inactividad
                    
                    $this->redirect('dashboard');
                } else {
                    $error = "Credenciales incorrectas o usuario inactivo";
                }
            }
        }
        
        $this->view->render('auth/login', [
            'error' => isset($error) ? $error : null,
            'title' => 'Iniciar Sesión'
        ]);
    }
    
    /**
     * Cierra la sesión del usuario.
     * Limpia y destruye la sesión y las cookies de sesión.
     */
    public function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = [];
        
        // Borrar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Borrar la cookie del token de navegador
        if (isset($_COOKIE['browser_token'])) {
            setcookie('browser_token', '', time() - 42000, '/', '', false, true);
        }
        
        // Destruir la sesión
        session_destroy();
        $this->redirect('auth/login?logged_out=true');
    }
    
    /**
     * Muestra y procesa el formulario de perfil del usuario.
     * Permite al usuario actualizar su nombre, email y contraseña.
     */
    public function profile() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => trim($_POST['full_name']),
                'email' => trim($_POST['email'])
            ];
            
            // Si se proporciona una nueva contraseña, se valida y actualiza
            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    $error = "Las contraseñas no coinciden";
                } else {
                    // Es importante hashear la contraseña antes de guardarla
                    $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $this->model->updatePassword($_SESSION['admin_id'], $hashedPassword);
                    $success = "Perfil actualizado correctamente";
                }
            }
            
            // Si no hay errores, se actualizan los demás datos
            if (!isset($error)) {
                $this->model->update($_SESSION['admin_id'], $data);
                // Actualizar los datos de la sesión también
                $_SESSION['admin_name'] = $data['full_name'];
                $_SESSION['admin_email'] = $data['email'];
                $success = "Perfil actualizado correctamente";
            }
        }
        
        $admin = $this->model->findById($_SESSION['admin_id']);
        
        $this->view->render('auth/profile', [
            'admin' => $admin,
            'error' => isset($error) ? $error : null,
            'success' => isset($success) ? $success : null,
            'title' => 'Mi Perfil'
        ]);
    }

    /**
     * Endpoint para mantener la sesión activa (keep-alive).
     * Se llama periódicamente desde el frontend para actualizar el timestamp de la última actividad.
     */
    public function keepAlive() {
        if (isset($_SESSION['admin_id'])) {
            $_SESSION['last_activity'] = time();
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }
    }
}
