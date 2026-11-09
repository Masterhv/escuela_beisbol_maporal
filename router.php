<?php
/**
 * Router para el servidor embebido de PHP (php -S).
 * Permite servir archivos estáticos directamente y reenviar las rutas a index.php.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . $uri;

// Si el archivo físico existe y no es un archivo PHP, el servidor integrado lo entrega directamente
if ($uri !== '/' && is_file($filePath)) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($extension !== 'php') {
        return false;
    }
}

// Para todas las demás rutas MVC, cargar index.php
require_once __DIR__ . '/index.php';
