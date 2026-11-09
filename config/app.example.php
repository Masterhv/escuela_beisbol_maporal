<?php
/**
 * CONFIGURACION POR ENTORNO (plantilla).
 *
 * Copia este archivo como config/app.php y ajusta los valores. config/app.php NO se versiona (esta en .gitignore)
 * porque contiene secretos. Es el UNICO archivo que hay que tocar al mover el sistema a otro servidor.
 *
 * Si config/app.php no existe, el sistema arranca en modo SEGURO por defecto (produccion: errores ocultos).
 * Para desarrollo local, crea config/app.php con 'env' => 'local'.
 */
return [
    // 'production' (por defecto) = errores ocultos, cookies seguras si hay HTTPS. 'local' = errores visibles.
    'env' => 'production',

    // URL completa donde esta instalado el sistema, TERMINADA EN "/". Aparece en los QR de verificacion de los PDF y
    // en los enlaces de los correos. Un QR ya impreso apunta a la URL que habia al generarlo: cambiarla despues
    // deja de funcionar esos QR, asi que defínela BIEN antes de emitir documentos reales.
    'base_url' => 'https://escueladebeisbolmenormaporal.com/',

    // Ruta de la carpeta del sistema dentro del dominio (para .htaccess y recursos): '/' si esta en la raiz, '/sistema/' si
    // esta en una subcarpeta. Debe coincidir con la ruta de base_url.
    'base_path' => '/',

    // true si el sitio se sirve por HTTPS (OBLIGATORIO en produccion): activa la cookie de sesion "secure".
    'https' => true,

    // Clave con la que se firman los QR de verificacion de los documentos NUEVOS. Larga y aleatoria. Generala con:
    //   php -r "echo bin2hex(random_bytes(32));"
    // NO la cambies una vez emitidos documentos: los QR anteriores dejarian de validarse (ver 'hmac_previous_keys').
    'hmac_key' => 'GENERAR-UNA-CLAVE-ALEATORIA-DE-64-CARACTERES',

    // Claves ANTERIORES aceptadas SOLO para verificar documentos ya emitidos (nunca para firmar).
    'hmac_previous_keys' => [],

    // Limite de seguridad de envios automaticos por dia (correo y WhatsApp, cada uno). Evita que un error o un bucle
    // mande cientos de mensajes. Con el negocio de Meta sin verificar, WhatsApp admite 250 conversaciones/dia.
    'daily_send_limit_email'    => 150,
    'daily_send_limit_whatsapp' => 200,
];
