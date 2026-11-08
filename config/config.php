<?php
/**
 * Archivo de configuración general de la aplicación.
 * Define constantes, configura la sesión, la zona horaria, el reporte de errores y el autoloading de clases.
 */

// --- Entorno (config/app.php) ---

/**
 * Configuración por entorno: URL, modo (local/production), HTTPS y claves. Está en config/app.php, que NO se versiona
 * (ver config/app.example.php). Es lo único que se ajusta al mover el sistema a otro servidor.
 *
 * Tolerante a fallos: si el archivo falta o está roto, el sistema arranca en modo SEGURO (producción: errores ocultos,
 * cookies seguras) en lugar de caer o enseñar errores. El problema queda en el log del servidor.
 */
$__appDefaults = [
    'env'       => 'production',
    'base_url'  => 'http://localhost/project/',
    'base_path' => '/',
    'https'     => false,
    'hmac_key'  => '',
    'hmac_previous_keys' => [],
    'daily_send_limit_email'    => 150,
    'daily_send_limit_whatsapp' => 200,
];
$__appFile = __DIR__ . DIRECTORY_SEPARATOR . 'app.php';
$__app = $__appDefaults;
if (is_file($__appFile)) {
    try {
        $__loaded = require $__appFile;
        if (is_array($__loaded)) { $__app = array_merge($__appDefaults, $__loaded); }
        else { error_log('[config] config/app.php no devuelve un arreglo: se usan valores por defecto seguros.'); }
    } catch (\Throwable $e) {
        error_log('[config] config/app.php es invalido (' . $e->getMessage() . '): se usan valores por defecto seguros.');
    }
} else {
    error_log('[config] No existe config/app.php: el sistema arranca en modo produccion con valores por defecto. Copia config/app.example.php.');
}
$GLOBALS['__app_config'] = $__app;

/** Lee un valor del entorno (config/app.php). */
function app_config($key, $default = null) {
    return $GLOBALS['__app_config'][$key] ?? $default;
}

// --- Configuración General del Sistema ---

/**
 * @var string URL base de la aplicación. Sale de config/app.php ('base_url'). Debe terminar con "/".
 * Aparece en los QR de verificación de los PDF y en los enlaces de los correos.
 */
define('BASE_URL', rtrim((string)app_config('base_url'), '/') . '/');

/** @var bool true si el sistema corre en modo local (errores visibles). Cualquier otro valor = producción. */
define('APP_DEBUG', app_config('env') === 'local');

/** @var bool true si el sitio se sirve por HTTPS (activa cookies seguras). */
define('APP_HTTPS', (bool)app_config('https'));

/**
 * @var string Ruta absoluta al directorio raíz del proyecto en el servidor.
 * Se usa para cargar recursos (imágenes, archivos) de forma confiable sin depender del cwd.
 */
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

/**
 * @var string Nombre del sitio web que se muestra en los títulos de las páginas.
 */
define('SITE_NAME', 'Sistema de Gestión - Escuela de Béisbol Menor Maporal');

/**
 * @var string Versión actual de la aplicación.
 */
define('VERSION', '1.0.0');

// --- Interruptores de eliminación ---

/**
 * @var bool Permite eliminar atletas. Está deshabilitado.
 * Importante: al borrar un atleta se eliminan también todos sus pagos
 * (la clave foránea payments.athlete_id tiene ON DELETE CASCADE).
 * Para reactivar la eliminación, cambiar a true.
 */
define('ALLOW_DELETE_ATHLETES', false);

/**
 * @var bool Permite eliminar pagos. Está deshabilitado.
 * Para reactivar la eliminación, cambiar a true.
 */
define('ALLOW_DELETE_PAYMENTS', false);

// --- Configuración de Sesión ---

// Aumenta la seguridad de las cookies de sesión.
ini_set('session.cookie_httponly', 1); // La cookie solo es accesible a través del protocolo HTTP.
ini_set('session.use_only_cookies', 1); // El módulo de sesión solo usará cookies para almacenar el ID de sesión.
ini_set('session.cookie_secure', APP_HTTPS ? 1 : 0); // Segun config/app.php ('https'): con HTTPS la cookie solo viaja por conexiones seguras.
ini_set('session.cookie_lifetime', 0); // La cookie de sesión expira cuando se cierra el navegador.
ini_set('session.gc_maxlifetime', 1800); // Tiempo máximo de vida de la sesión en el servidor (30 minutos)
ini_set('session.cookie_samesite', 'Lax'); // Protección contra CSRF

// Configurar parámetros de la cookie de sesión antes de iniciar
session_set_cookie_params([
    'lifetime' => 0, // Expira al cerrar el navegador
    'path' => (string)app_config('base_path', '/'), // la cookie no viaja a otras aplicaciones del mismo dominio
    'domain' => '',
    'secure' => APP_HTTPS, // Segun config/app.php
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Inicia la sesión. Debe llamarse antes de cualquier salida al navegador.
session_start();

// Sistema de detección de cierre de navegador
// Genera un token único por sesión de navegador
if (!isset($_COOKIE['browser_token'])) {
    // Si no hay token de navegador, es una nueva sesión de navegador
    $browser_token = bin2hex(random_bytes(32));
    setcookie('browser_token', $browser_token, 0, (string)app_config('base_path', '/'), '', APP_HTTPS, true); // Cookie de sesión
    $_SESSION['browser_token'] = $browser_token;
} else {
    // Verificar que el token del navegador coincida con el de la sesión
    if (isset($_SESSION['browser_token']) && $_SESSION['browser_token'] !== $_COOKIE['browser_token']) {
        // El token no coincide, destruir la sesión
        session_unset();
        session_destroy();
        session_start();
    } elseif (!isset($_SESSION['browser_token'])) {
        // No hay token en la sesión pero sí en la cookie, es una sesión antigua
        // Destruir la sesión y crear una nueva
        session_unset();
        session_destroy();
        session_start();
        $browser_token = bin2hex(random_bytes(32));
        setcookie('browser_token', $browser_token, 0, (string)app_config('base_path', '/'), '', APP_HTTPS, true);
        $_SESSION['browser_token'] = $browser_token;
    }
}

// Regenerar el ID de sesión periódicamente para mayor seguridad
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    // Regenerar ID de sesión cada 30 minutos
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// --- Zona Horaria ---

// Establece la zona horaria por defecto para todas las funciones de fecha y hora.
date_default_timezone_set('America/Caracas');

// --- Configuración de Errores ---

// Los errores se REGISTRAN siempre (log del servidor) pero solo se MUESTRAN en pantalla en modo local: en producción un
// error mostrado revelaría rutas, consultas y datos internos a cualquier visitante.
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? 1 : 0);
ini_set('display_startup_errors', APP_DEBUG ? 1 : 0);
ini_set('log_errors', 1);

// --- Autoload de Clases ---

/**
 * Registra una función de autoloading.
 * Esta función se ejecuta automáticamente cuando se intenta usar una clase que no ha sido definida.
 * Busca la clase en los directorios especificados y la incluye si la encuentra.
 *
 * @param string $class_name El nombre de la clase a cargar.
 */
spl_autoload_register(function ($class_name) {
    // Directorios donde se buscarán las clases.
    $directories = [
        'models/',
        'controllers/',
        'services/',
        'config/'
    ];
    
    // Itera sobre los directorios para encontrar el archivo de la clase.
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return; // Termina la función una vez que se encuentra y carga la clase.
        }
    }
});