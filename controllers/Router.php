<?php
/**
 * Clase Router para manejar las rutas de la aplicación.
 * Se encarga de dirigir las peticiones HTTP al controlador y acción correspondientes.
 */
class Router {
    /**
     * @var array Almacena las rutas registradas.
     */
    private $routes = [];
    
    /**
     * Registra una ruta para el método HTTP GET.
     *
     * @param string $path La ruta a registrar.
     * @param mixed $callback La función o método a ejecutar cuando la ruta coincide.
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * Registra una ruta para el método HTTP POST.
     *
     * @param string $path La ruta a registrar.
     * @param mixed $callback La función o método a ejecutar cuando la ruta coincide.
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * Ejecuta el enrutador.
     * Analiza la URL solicitada y llama al controlador y acción apropiados.
     */
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH);

        // Elimina la parte de la URL base del path para obtener la ruta relativa
        if (strpos($path, $baseUrlPath) === 0) {
            $path = substr($path, strlen($baseUrlPath) -1);
        }

        $path = $path === '' ? '/' : $path;
        
        // Enrutamiento simple basado en la estructura de la URL: /controlador/accion
        $segments = explode('/', trim($path, '/'));
        $controllerName = !empty($segments[0]) ? $segments[0] : 'dashboard';
        $action = !empty($segments[1]) ? $segments[1] : 'index';
        $param = !empty($segments[2]) ? $segments[2] : null;

        // Rutas para restablecimiento de contraseña
        if ($controllerName === 'password') {
            $controllerClass = 'PasswordResetController';
            $controllerFile = 'controllers/' . $controllerClass . '.php';

            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerInstance = new $controllerClass();

                if ($method === 'GET' && $action === 'request') {
                    $controllerInstance->request();
                    return;
                }
                if ($method === 'POST' && $action === 'send') {
                    $controllerInstance->sendLink();
                    return;
                }
                if ($method === 'GET' && $action === 'reset' && $param) {
                    $controllerInstance->reset($param);
                    return;
                }
                if ($method === 'POST' && $action === 'update') {
                    $controllerInstance->update();
                    return;
                }
            }
        }

        // Ruta pública de verificación de documentos — no requiere autenticación
        if ($controllerName === 'verify') {
            $controllerFile = 'controllers/VerifyController.php';
            require_once $controllerFile;
            $verifyInstance = new VerifyController();
            $verifyInstance->index();
            return;
        }

        // Si se intenta acceder al dashboard sin sesión, redirige al login
        if ($controllerName === 'dashboard' && !isset($_SESSION['admin_id'])) {
            $controllerName = 'auth';
            $action = 'login';
        }
        
        $controllerClass = ucfirst($controllerName) . 'Controller';
        
        $controllerFile = 'controllers/' . $controllerClass . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerInstance = new $controllerClass();
            
            if (method_exists($controllerInstance, $action)) {
                // Llama a la acción del controlador
                $controllerInstance->$action();
            } else {
                // Muestra error 404 si la acción no existe
                $this->show404();
            }
        } else {
            // Muestra error 404 si el controlador no existe
            $this->show404();
        }
    }
    
    /**
     * Muestra una página de error 404 (No Encontrado).
     */
    private function show404() {
        http_response_code(404);
        echo "Página no encontrada";
    }
}