<?php

// PLANTILLA de configuración de WhatsApp Cloud API (Meta). NO contiene credenciales reales.
//
// Cómo usarla:
//   1. Copia este archivo como config/whatsapp.php  (ese archivo está en .gitignore y no se versiona).
//   2. Completa 'phone_number_id', 'waba_id' y 'access_token'.
//
// Cómo obtener estos valores:
// 1. Crea una app tipo "Business" en https://developers.facebook.com/apps
// 2. Agrega el producto "WhatsApp" a la app.
// 3. En WhatsApp > Configuración de la API obtendrás 'phone_number_id' y 'waba_id',
//    y un token temporal de prueba (dura 24h, solo para probar).
// 4. Para producción: crea un "System User" en Meta Business Suite y genera un
//    Access Token permanente con los permisos 'whatsapp_business_messaging' y
//    'whatsapp_business_management'. Reemplaza 'access_token' con ese valor.
// 5. Crea y solicita aprobación de las plantillas (tipo Utility, idioma 'es') con header de tipo
//    Documento (para adjuntar el PDF):
//      - Recibo de pago ('template_name'), 4 variables:
//          {{1}} representante, {{2}} mes y año, {{3}} N.° de recibo, {{4}} monto (con "Bs.")
//      - Planilla de inscripción ('enrollment_template_name'):
//          {{1}} representante, {{2}} atleta, {{3}} fecha de inscripción (d/m/Y)
//    El nombre exacto y el idioma de cada plantilla van abajo.
// 6. Mientras la app esté en modo desarrollo, solo puedes enviar mensajes a
//    números agregados manualmente como "destinatarios de prueba" en el panel
//    de Meta (máximo 5). Para enviar sin esa restricción hay que verificar el
//    negocio en Meta Business Manager.

return [
    'api_version'     => 'v26.0',              // Versión de la Graph API de Meta
    'phone_number_id' => '',                   // Phone Number ID del número de WhatsApp Business
    'waba_id'         => '',                   // WhatsApp Business Account ID
    'access_token'    => '',                   // Access Token permanente del System User

    // Plantilla del recibo de pago (header Documento, body con 4 variables):
    //   {{1}} representante, {{2}} mes y año, {{3}} N.° de recibo, {{4}} monto (con "Bs.").
    'template_name'   => 'recibo_pago_mes_pdf',    // Nombre exacto de la plantilla aprobada en Meta
    'template_lang'   => 'es',                 // Código de idioma de la plantilla (ej. 'es', 'es_ES', 'es_MX')
    // OPCIONAL - Lista blanca de destinatarios (E.164 sin '+'), para entornos de prueba. Si se define, el sistema
    // NO envia a ningun numero fuera de la lista. Dejar vacia o quitar en produccion.
    // 'allowed_recipients' => ['584120000000'],

    'country_code'    => '58',                 // Código de país usado para normalizar teléfonos locales (Venezuela)

    // Plantilla para la planilla de inscripción (header Documento, body con 3 variables).
    // Debe existir y estar APROBADA en Meta; mientras no lo esté, el envío por WhatsApp
    // de la inscripción quedará como "Fallido" (el atleta se registra igual).
    'enrollment_template_name' => 'inscripcion_atleta_pdf',
    'enrollment_template_lang' => 'es',
];
