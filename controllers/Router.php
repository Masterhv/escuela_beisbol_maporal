<?php
/**
 * Clase Router para manejar el enrutamiento de la aplicación.
 * Dirige las peticiones HTTP al controlador y acción correspondientes de forma limpia y segura.
 */
class Router {
    /**
     * @var array Almacena las rutas registradas manualmente si se requiere.
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
     * Obtiene y normaliza la ruta relativa solicitada limpia de prefijos base e index.php.
     *
     * @return string Ruta relativa normalizada (ej. '/', '/athletes', '/athletes/view')
     */
    public static function getNormalizedPath() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $path = urldecode($path);

        // Obtener la ruta base configurada
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
        $baseUrlPath = parse_url($baseUrl, PHP_URL_PATH) ?? '/';
        $baseUrlPath = rtrim($baseUrlPath, '/');

        // Si la aplicación corre en un subdirectorio y la URL comienza con esa ruta, removerla
        if ($baseUrlPath !== '' && strpos($path, $baseUrlPath) === 0) {
            $path = substr($path, strlen($baseUrlPath));
        }

        // Remover /index.php si viene en la URL
        if (strpos($path, '/index.php') === 0) {
            $path = substr($path, strlen('/index.php'));
        }

        // Normalizar barras repetidas o vacías
        $trimmed = trim($path, '/');
        return $trimmed === '' ? '/' : '/' . $trimmed;
    }
    
    /**
     * Ejecuta el enrutador.
     * Analiza la URL solicitada y llama al controlador y acción apropiados.
     */
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $path = self::getNormalizedPath();
        
        // Enrutamiento modular basado en la estructura: /controlador/accion/parametro
        $segments = array_values(array_filter(explode('/', trim($path, '/')), function($seg) {
            return $seg !== '';
        }));

        $controllerName = !empty($segments[0]) ? strtolower($segments[0]) : 'dashboard';
        $action = !empty($segments[1]) ? $segments[1] : 'index';
        $param = !empty($segments[2]) ? $segments[2] : null;

        // Soporte de nombres de controlador con guiones (ej. password-reset -> PasswordResetController)
        $controllerClass = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $controllerName))) . 'Controller';

        // Rutas para restablecimiento de contraseña (/password/*)
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

        // Ruta pública de verificación de documentos (/verify) — no requiere autenticación
        if ($controllerName === 'verify') {
            $controllerFile = 'controllers/VerifyController.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                // Permitir token como segundo segmento si no viene por GET (?token=...)
                if ($action !== 'index' && empty($_GET['token'])) {
                    $_GET['token'] = $action;
                } elseif (!empty($param) && empty($_GET['token'])) {
                    $_GET['token'] = $param;
                }
                $verifyInstance = new VerifyController();
                $verifyInstance->index();
                return;
            }
        }

        // Si se intenta acceder al dashboard o la raíz sin sesión activa, redirige al login
        if ($controllerName === 'dashboard' && !isset($_SESSION['admin_id'])) {
            $controllerName = 'auth';
            $controllerClass = 'AuthController';
            $action = 'login';
        }
        
        $controllerFile = 'controllers/' . $controllerClass . '.php';
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
                
                // Normalizar kebab-case y snake_case a camelCase (ej. update-status -> updateStatus)
                $actionCamel = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $action))));
                
                $targetAction = null;
                if (method_exists($controllerInstance, $action)) {
                    $targetAction = $action;
                } elseif (method_exists($controllerInstance, $actionCamel)) {
                    $targetAction = $actionCamel;
                }

                if ($targetAction !== null) {
                    try {
                        $reflection = new ReflectionMethod($controllerInstance, $targetAction);
                        
                        // Solo invocar métodos públicos no mágicos
                        if ($reflection->isPublic() && !str_starts_with($targetAction, '__')) {
                            
                            // Si hay un parámetro numérico en la URL y $_GET['id'] no está definido, poblarlo
                            if ($param !== null && !isset($_GET['id']) && is_numeric($param)) {
                                $_GET['id'] = (int)$param;
                            }

                            // Si el método espera parámetros, pasarlos; si no, invocar normalmente
                            if ($reflection->getNumberOfParameters() > 0) {
                                $callArgs = array_slice($segments, 2);
                                $reflection->invokeArgs($controllerInstance, $callArgs);
                            } else {
                                $controllerInstance->$targetAction();
                            }
                            return;
                        }
                    } catch (ReflectionException $e) {
                        // Error de reflexión, tratar como 404
                    }
                }
            }
        }
        
        // Muestra error 404 si la ruta no existe
        $this->show404();
    }
    
    /**
     * Muestra una página de error 404 (No Encontrado) estilizada e institucional.
     */
    private function show404() {
        http_response_code(404);
        
        $logoUrl = defined('BASE_URL') ? BASE_URL . 'img/logo-maporal.png' : '/img/logo-maporal.png';
        $homeUrl = defined('BASE_URL') ? BASE_URL : '/';
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'Escuela de Béisbol Menor Maporal';
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - Página no encontrada | <?php echo htmlspecialchars($siteName); ?></title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>
        <body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 text-gray-800">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center border border-gray-100">
                <div class="mb-6 flex justify-center">
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo Maporal" class="h-20 w-auto object-contain" onerror="this.style.display='none'">
                </div>
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                    <i class="fas fa-compass fa-2x"></i>
                </div>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-2">404</h1>
                <h2 class="text-xl font-semibold text-gray-800 mb-3">Página no encontrada</h2>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    La dirección a la que intentas acceder no existe, fue movida o no está disponible en este momento.
                </p>
                <a href="<?php echo htmlspecialchars($homeUrl); ?>" 
                   class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-red-700 hover:bg-red-800 text-white font-medium shadow-md transition-colors duration-200">
                    <i class="fas fa-home"></i>
                    <span>Volver al inicio</span>
                </a>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}
