<?php
/**
 * Punto de entrada principal de la aplicación (Front Controller).
 * Todas las peticiones se redirigen a este archivo a través de la configuración del servidor (ej. .htaccess).
 * Se encarga de inicializar la configuración y el enrutador.
 */

// Buffer de salida global para evitar cualquier output accidental antes del HTML.
ob_start();

try {
    // Carga el archivo de configuración principal.
    require_once __DIR__ . '/config/config.php';

    // Carga la clase del enrutador.
    require_once __DIR__ . '/controllers/Router.php';

    // Crea una nueva instancia del enrutador y lo ejecuta.
    $router = new Router();
    $router->run();
    
    // Vaciar el buffer al navegador
    if (ob_get_level()) {
        ob_end_flush();
    }
} catch (\Throwable $e) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    error_log('[App Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);

    $isProduction = defined('APP_DEBUG') ? !APP_DEBUG : true;
    
    if (!$isProduction) {
        echo "<div style='font-family:sans-serif;padding:2rem;max-width:800px;margin:auto;'>";
        echo "<h1 style='color:#b91c1c;'>Error en la aplicación</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . " (línea " . $e->getLine() . ")</p>";
        echo "<pre style='background:#f3f4f6;padding:1rem;border-radius:0.5rem;overflow-x:auto;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        echo "</div>";
    } else {
        $logo = defined('BASE_URL') ? BASE_URL . 'img/logo-maporal.png' : '/img/logo-maporal.png';
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error del Servidor | Escuela de Béisbol Menor Maporal</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 text-gray-800">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center border border-gray-100">
                <div class="mb-4 flex justify-center">
                    <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo Maporal" class="h-16 w-auto object-contain" onerror="this.style.display='none'">
                </div>
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 text-red-600 mb-3 text-xl font-bold">!</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Error del Servidor</h1>
                <p class="text-sm text-gray-600 mb-6">
                    Ha ocurrido un inconveniente inesperado al procesar su solicitud. El equipo técnico ha sido notificado en el registro del sistema.
                </p>
                <a href="/" class="inline-block px-5 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-medium text-sm transition-colors">
                    Reintentar / Volver al inicio
                </a>
            </div>
        </body>
        </html>
        <?php
    }
}
