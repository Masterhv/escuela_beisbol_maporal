<?php

// PLANTILLA de configuración de correo (SMTP). NO contiene credenciales reales.
//
// Cómo usarla:
//   1. Copia este archivo como config/mail.php  (ese archivo está en .gitignore y no se versiona).
//   2. Completa 'username' y 'password'.
//
// Configuración de PHPMailer (Gmail SMTP)
//
// 'from_address' debe coincidir con 'username' (la cuenta autenticada),
// o Gmail puede rechazar/marcar como spam el correo enviado.
//
// 'password' NO es la contraseña normal de la cuenta: es una "contraseña de aplicación" de 16 caracteres.
// Se genera en https://myaccount.google.com/apppasswords (requiere verificación en dos pasos activa).
return [
    'host' => 'smtp.gmail.com', // Servidor SMTP de Gmail
    'port' => 587, // Puerto SMTP (STARTTLS)
    'username' => '', // Cuenta Gmail remitente, ej. cuenta@gmail.com
    'password' => '', // Contraseña de aplicación (16 caracteres, sin espacios)
    'from_address' => '', // Debe coincidir con 'username'
    'from_name' => 'Escuela de Béisbol Menor Maporal', // Nombre del remitente

    // OPCIONAL - Lista blanca de destinatarios para entornos de prueba. Si se define, NO se envia correo a ninguna
    // otra direccion (incluida la recuperacion de contrasena). Dejar vacia o quitar en produccion.
    // 'allowed_recipients' => ['pruebas@ejemplo.com']
];
