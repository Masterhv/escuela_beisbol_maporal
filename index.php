<?php
/**
 * Punto de entrada principal de la aplicación (Front Controller).
 * Todas las peticiones se redirigen a este archivo a través de la configuración del servidor (ej. .htaccess).
 * Se encarga de inicializar la configuración y el enrutador.
 */

// Buffer de salida global para evitar cualquier output accidental antes del HTML.
ob_start();

// Carga el archivo de configuración principal.
// Este archivo define constantes, inicia la sesión y configura el autoloading.
require_once 'config/config.php';

// Carga la clase del enrutador.
require_once 'controllers/Router.php';

// Crea una nueva instancia del enrutador.
$router = new Router();

// Ejecuta el enrutador, que se encargará de analizar la URL
// y llamar al controlador y método correspondientes.
$router->run();