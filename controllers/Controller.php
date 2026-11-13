<?php
/**
 * Clase controladora base.
 * Todos los controladores de la aplicación deben heredar de esta clase.
 * Proporciona funcionalidades comunes como cargar modelos, vistas, redireccionar y manejar la autenticación.
 */
class Controller {
    /**
     * @var Model El objeto del modelo asociado al controlador.
     */
    protected $model;

    /**
     * @var View El objeto de la vista para renderizar las respuestas.
     */
    protected $view;
    
    /**
     * Constructor de la clase Controller.
     * Inicializa el objeto View.
     */
    public function __construct() {
        $this->view = new View();
    }
    
    /**
     * Carga un modelo específico.
     * Incluye el archivo del modelo y crea una instancia del mismo.
     *
     * @param string $model El nombre del modelo a cargar (sin la extensión .php).
     */
    protected function loadModel($model) {
        $base = defined('BASE_PATH') ? BASE_PATH : (dirname(__DIR__) . DIRECTORY_SEPARATOR);
        $file = $base . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
        if (is_file($file)) {
            require_once $file;
        }
        if (class_exists($model)) {
            $this->model = new $model();
        } else {
            error_log("[Controller] No se pudo cargar el modelo: {$model}");
            $this->model = null;
        }
    }
    
    /**
     * Redirecciona a una URL específica dentro de la aplicación.
     *
     * @param string $url La URL o ruta a la que se va a redireccionar.
     */
    protected function redirect($url) {
        if (preg_match('#^https?://#i', $url)) {
            header('Location: ' . $url);
        } else {
            header('Location: ' . BASE_URL . ltrim($url, '/'));
        }
        exit();
    }
    
    /**
     * Requiere que el usuario esté autenticado para acceder a una página.
     * Si el usuario no ha iniciado sesión, lo redirige a la página de login.
     * También maneja el tiempo de espera por inactividad.
     */
    protected function requireAuth() {
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('auth/login');
        }

        // Timeout por inactividad (10 minutos)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
            session_unset();
            session_destroy();
            $this->redirect('auth/login?reason=inactive');
        }
        $_SESSION['last_activity'] = time();
        
        // Evita que la página se guarde en caché
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
    
    /**
     * Requiere que el usuario tenga un rol específico para acceder a una página.
     * Primero verifica si el usuario está autenticado.
     * Si se especifican roles y el usuario no tiene el rol requerido, lo redirige al dashboard.
     *
     * @param array $roles Un array de roles permitidos. Si está vacío, solo se requiere autenticación.
     */
    protected function requireRole($roles = []) {
        $this->requireAuth();
        
        if (!empty($roles) && !in_array($_SESSION['admin_role'], $roles)) {
            $this->redirect('dashboard');
        }
    }
    
    /**
     * Exige que la petición sea un POST originado en este mismo sitio. Segunda capa contra CSRF para
     * acciones financieras (aprobar, rechazar, reabrir), además de session.cookie_samesite=Lax:
     * si el navegador envía Origin (o Referer), su host debe coincidir con el del servidor.
     * Una petición sin ninguno de los dos (curl, scripts internos) se acepta: no la origina un navegador.
     *
     * @param bool $asJson true = responde JSON 405/403; false = redirige al dashboard.
     */
    protected function requireSameOriginPost($asJson = true) {
        $fail = function ($status, $msg) use ($asJson) {
            if ($asJson) { $this->jsonResponse(['success' => false, 'message' => $msg], $status); }
            http_response_code($status);
            $this->redirect('dashboard');
        };

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            $fail(405, 'Método no permitido.');
        }
        $source = $_SERVER['HTTP_ORIGIN'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if ($source !== '') {
            $sourceHost = parse_url($source, PHP_URL_HOST);
            $sourcePort = parse_url($source, PHP_URL_PORT);
            $ownHost = parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
            $ownPort = parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_PORT);
            if (!$sourceHost || strcasecmp($sourceHost, (string)$ownHost) !== 0 || (int)$sourcePort !== (int)$ownPort) {
                $fail(403, 'Origen de la petición no permitido.');
            }
        }
    }

    /**
     * Devuelve los ids de atletas que el usuario actual PUEDE ver, o null si no tiene restricción.
     *
     *  - administrador / secretario / entrenador: null (ven todos los atletas).
     *  - representante: solo los atletas cuya cédula de representante (athletes.parent_id_card)
     *    coincide con la cédula de su cuenta (admins.id_card). La cédula se lee de la BASE DE DATOS
     *    en cada petición, no de la sesión, para que desactivar la cuenta o cambiar su cédula
     *    surta efecto de inmediato. Una cuenta sin cédula, o inactiva, no ve ningún atleta.
     *
     * Es el ÚNICO lugar donde se define a qué atletas accede un representante: listados, detalles,
     * descargas, comprobantes y autocompletados lo usan, de modo que no haya una ruta que lo olvide.
     *
     * @return int[]|null
     */
    protected function scopeAthleteIds() {
        if (($_SESSION['admin_role'] ?? '') !== 'representante') {
            return null;
        }
        static $cache = [];
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        if (isset($cache[$adminId])) {
            return $cache[$adminId];
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            "SELECT s.id
             FROM athletes s
             JOIN admins a ON a.id_card = s.parent_id_card
             WHERE a.id = :id AND a.status = 'active' AND a.role = 'representante'
               AND a.id_card IS NOT NULL AND a.id_card <> ''"
        );
        $stmt->bindValue(':id', $adminId, PDO::PARAM_INT);
        $stmt->execute();
        return $cache[$adminId] = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * ¿Puede el usuario actual acceder a este atleta? true para el personal; para el representante,
     * solo si es suyo.
     */
    protected function canAccessAthlete($athleteId) {
        $scope = $this->scopeAthleteIds();
        return $scope === null || in_array((int)$athleteId, $scope, true);
    }

    /**
     * Corta la petición con 403 si el usuario no puede acceder a ese atleta. Responde igual
     * ("no encontrado") exista o no el atleta, para no revelar qué ids existen.
     */
    protected function requireAthleteAccess($athleteId, $asJson = false) {
        if ($this->canAccessAthlete($athleteId)) {
            return;
        }
        if ($asJson) {
            $this->jsonResponse(['success' => false, 'message' => 'No tienes acceso a este recurso.'], 403);
        }
        http_response_code(403);
        $this->redirect('dashboard');
    }

    /**
     * Envía una respuesta en formato JSON.
     * Útil para APIs o peticiones AJAX.
     *
     * @param mixed $data Los datos a ser codificados en JSON.
     * @param int $status El código de estado HTTP a enviar.
     */
    protected function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}
