<?php
/**
 * Configuración de producción para https://escueladebeisbolmenormaporal.com/
 * 
 * Copiar como config/app.php en el servidor de producción.
 */
return [
    // En producción los errores se ocultan al usuario y se registran en logs
    'env' => 'production',

    // Dominio canónico oficial de producción
    'base_url' => 'https://escueladebeisbolmenormaporal.com/',

    // Raíz del dominio
    'base_path' => '/',

    // Conexión segura obligatoria para cookies de sesión seguras
    'https' => true,

    // Clave para firma de códigos QR de verificación en constancias y carnets
    // Generada con: bin2hex(random_bytes(32))
    'hmac_key' => 'b7f309a4716766d997d91e8ebca52b7c46fbe4cbbd3aa152763f03b29074094a',

    // Claves HMAC anteriores (para documentos emitidos previamente si se rota la clave)
    'hmac_previous_keys' => [],

    // Límites de seguridad diarios para envíos
    'daily_send_limit_email'    => 150,
    'daily_send_limit_whatsapp' => 200,
];

