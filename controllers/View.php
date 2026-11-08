<?php
/**
 * Clase View para manejar la renderización de las vistas.
 * Se encarga de cargar los archivos de la vista, pasarles datos y mostrarlos.
 */
class View {
    /**
     * Renderiza una vista completa, incluyendo el layout principal.
     *
     * @param string $view La ruta de la vista a renderizar (sin la extensión .php).
     * @param array $data Datos para pasar a la vista.
     * @param bool $isPdf Indica si la vista es para un PDF, en cuyo caso solo devuelve el contenido.
     * @return string|void Si es un PDF, devuelve el contenido HTML. Si no, no devuelve nada.
     */
    public function render($view, $data = [], $isPdf = false) {
        // Extrae los datos del array para que estén disponibles como variables en la vista
        extract($data);
        
        // Inicia el buffer de salida para capturar el contenido de la vista
        ob_start();
        require_once "views/{$view}.php";
        $content = ob_get_clean();
        
        // Si es para un PDF, solo se retorna el contenido crudo
        if ($isPdf) {
            return $content;
        }
        
        // Carga el layout principal que envolverá el contenido de la vista
        require_once 'views/layouts/main.php';
    }
    
    /**
     * Renderiza una vista parcial sin el layout principal.
     * Útil para componentes reutilizables o vistas para AJAX.
     *
     * @param string $view La ruta de la vista a renderizar.
     * @param array $data Datos para pasar a la vista.
     */
    public function renderPartial($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
    
    /**
     * Renderiza datos en formato JSON.
     *
     * @param mixed $data Los datos a codificar en JSON.
     */
    public function renderJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Obtiene la sección activa de la URL.
     * Útil para resaltar el enlace activo en el menú de navegación.
     *
     * @return string La primera parte de la URL, que representa la sección activa.
     */
    public function getActiveSection() {
        // Replicamos la lógica del Router para obtener el controlador actual
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $baseUrlPath = parse_url(BASE_URL, PHP_URL_PATH);

        // Elimina la parte de la URL base del path para obtener la ruta relativa
        if ($baseUrlPath && strpos($path, $baseUrlPath) === 0) {
            $path = substr($path, strlen($baseUrlPath) - 1);
        }

        $path = $path === '' ? '/' : $path;

        // La primera parte del path es el "controlador" (dashboard, athletes, payments, reports, admins, etc.)
        $segments = explode('/', trim($path, '/'));
        $controllerName = !empty($segments[0]) ? $segments[0] : 'dashboard';

        return $controllerName;
    }
}
