<?php

/**
 * Cliente delgado para la WhatsApp Cloud API (Meta) usando cURL nativo de PHP.
 *
 * No depende de librerías externas: sube archivos (media) y envía mensajes de
 * tipo plantilla (template) con un documento adjunto en el header.
 *
 * Todas las peticiones son salientes (HTTPS hacia graph.facebook.com), por lo
 * que funcionan correctamente incluso desde un servidor local (XAMPP/localhost);
 * Meta nunca necesita alcanzar este servidor para enviar mensajes.
 */
require_once __DIR__ . '/SendLimiter.php';

class WhatsAppClient {
    /**
     * @var array Configuración cargada desde config/whatsapp.php.
     */
    private $config;

    /**
     * @var string URL base de la Graph API, ej. https://graph.facebook.com/v23.0
     */
    private $baseUrl;

    /**
     * Timeout de conexión/ejecución para las peticiones a Meta, en segundos.
     */
    private const REQUEST_TIMEOUT = 15;

    public function __construct() {
        // Excepcion capturable en lugar del error fatal de un require fallido (ver Mailer::__construct).
        if (!is_file('config/whatsapp.php')) {
            throw new \RuntimeException('Falta config/whatsapp.php: no se puede enviar por WhatsApp.');
        }
        $this->config = require 'config/whatsapp.php';
        $this->baseUrl = 'https://graph.facebook.com/' . $this->config['api_version'];
    }

    /**
     * Normaliza un teléfono local venezolano (ej. '0412-1234567') al formato
     * E.164 sin el símbolo '+' que exige la Cloud API (ej. '584121234567').
     *
     * @param string $rawPhone Teléfono tal como está almacenado en la BD.
     * @return string|null El teléfono normalizado, o null si no se puede normalizar.
     */
    public function normalizePhone($rawPhone) {
        if (empty($rawPhone)) {
            return null;
        }

        // Deja solo dígitos.
        $digits = preg_replace('/\D+/', '', $rawPhone);

        if (empty($digits)) {
            return null;
        }

        $countryCode = $this->config['country_code'] ?? '58';

        // Ya viene con código de país (ej. 584121234567, 12 dígitos empezando por 58).
        if (strpos($digits, $countryCode) === 0 && strlen($digits) === (strlen($countryCode) + 10)) {
            return $digits;
        }

        // Formato local venezolano: 0 + 10 dígitos (ej. 04121234567 -> 11 dígitos).
        if (strlen($digits) === 11 && $digits[0] === '0') {
            return $countryCode . substr($digits, 1);
        }

        // Formato local sin el 0 inicial (10 dígitos, ej. 4121234567).
        if (strlen($digits) === 10) {
            return $countryCode . $digits;
        }

        // No se reconoce un formato válido.
        return null;
    }

    /**
     * Sube un archivo binario al endpoint de medios de WhatsApp Cloud API.
     *
     * @param string $fileContent Contenido binario del archivo (ej. bytes del PDF).
     * @param string $mimeType    Tipo MIME del archivo (ej. 'application/pdf').
     * @param string $filename    Nombre de archivo a usar en la subida.
     * @return string El media_id devuelto por Meta.
     * @throws WhatsAppApiException Si la subida falla o Meta responde con error.
     */
    public function uploadMedia($fileContent, $mimeType, $filename) {
        $phoneNumberId = $this->config['phone_number_id'];
        $url = "{$this->baseUrl}/{$phoneNumberId}/media";

        // Escribe el contenido a un archivo temporal para poder adjuntarlo como CURLFile.
        $tmpFile = tempnam(sys_get_temp_dir(), 'wa_media_');
        file_put_contents($tmpFile, $fileContent);

        try {
            $cfile = new CURLFile($tmpFile, $mimeType, $filename);

            $postFields = [
                'messaging_product' => 'whatsapp',
                'type' => $mimeType,
                'file' => $cfile,
            ];

            $response = $this->request('POST', $url, $postFields, true);

            if (empty($response['id'])) {
                throw new WhatsAppApiException(
                    'Meta no devolvió un media_id. Respuesta: ' . json_encode($response)
                );
            }

            return $response['id'];
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * Envía un mensaje de tipo plantilla (template) cuyo header es texto fijo
     * (sin variables) y que solo lleva parámetros de texto en el cuerpo.
     * Es el caso de la plantilla aprobada 'recibo_pago'. El botón URL de esa
     * plantilla es estático (sin variable), por lo que no se envía componente button.
     *
     * @param string $toE164     Número de destino en formato E.164 sin '+'.
     * @param array  $bodyParams Lista ordenada de valores de texto para las variables {{1}}, {{2}}, ... del template.
     * @return string El message_id (wamid) devuelto por Meta.
     * @throws WhatsAppApiException Si el envío falla, Meta responde con error o se alcanzo el limite diario de envios.
     */
    public function sendTextTemplate($toE164, array $bodyParams) {
        $this->assertRecipientAllowed($toE164);
        $slot = $this->reserveSlot($toE164);
        try {
            $id = $this->doSendTextTemplate($toE164, $bodyParams);
            SendLimiter::finish($slot, true);
            return $id;
        } catch (\Throwable $e) {
            SendLimiter::finish($slot, false, $e->getMessage());
            throw $e;
        }
    }

    private function doSendTextTemplate($toE164, array $bodyParams) {
        $phoneNumberId = $this->config['phone_number_id'];
        $url = "{$this->baseUrl}/{$phoneNumberId}/messages";

        $bodyParameters = array_map(function ($value) {
            return ['type' => 'text', 'text' => (string)$value];
        }, $bodyParams);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $toE164,
            'type' => 'template',
            'template' => [
                'name' => $this->config['template_name'],
                'language' => ['code' => $this->config['template_lang']],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => $bodyParameters,
                    ],
                ],
            ],
        ];

        $response = $this->request('POST', $url, json_encode($payload), false);

        if (empty($response['messages'][0]['id'])) {
            throw new WhatsAppApiException(
                'Meta no devolvió un message_id. Respuesta: ' . json_encode($response)
            );
        }

        return $response['messages'][0]['id'];
    }

    /**
     * Envía un mensaje de tipo plantilla (template) con un documento adjunto
     * en el header y parámetros de texto en el cuerpo.
     *
     * @param string $toE164     Número de destino en formato E.164 sin '+'.
     * @param string $mediaId    media_id obtenido previamente con uploadMedia().
     * @param string $filename   Nombre de archivo mostrado al destinatario.
     * @param array  $bodyParams Lista ordenada de valores de texto para las variables {{1}}, {{2}}, ... del template.
     * @param string|null $templateName Nombre de la plantilla. Si es null usa 'template_name' de la config.
     * @param string|null $templateLang Idioma de la plantilla. Si es null usa 'template_lang' de la config.
     * @return string El message_id (wamid) devuelto por Meta.
     * @throws WhatsAppApiException Si el envío falla, Meta responde con error o se alcanzo el limite diario de envios.
     */
    public function sendDocumentTemplate($toE164, $mediaId, $filename, array $bodyParams, $templateName = null, $templateLang = null) {
        $this->assertRecipientAllowed($toE164);
        $slot = $this->reserveSlot($toE164);
        try {
            $id = $this->doSendDocumentTemplate($toE164, $mediaId, $filename, $bodyParams, $templateName, $templateLang);
            SendLimiter::finish($slot, true);
            return $id;
        } catch (\Throwable $e) {
            SendLimiter::finish($slot, false, $e->getMessage());
            throw $e;
        }
    }

    private function doSendDocumentTemplate($toE164, $mediaId, $filename, array $bodyParams, $templateName = null, $templateLang = null) {
        $phoneNumberId = $this->config['phone_number_id'];
        $url = "{$this->baseUrl}/{$phoneNumberId}/messages";

        $bodyParameters = array_map(function ($value) {
            return ['type' => 'text', 'text' => (string)$value];
        }, $bodyParams);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $toE164,
            'type' => 'template',
            'template' => [
                'name' => $templateName ?: $this->config['template_name'],
                'language' => ['code' => $templateLang ?: $this->config['template_lang']],
                'components' => [
                    [
                        'type' => 'header',
                        'parameters' => [
                            [
                                'type' => 'document',
                                'document' => [
                                    'id' => $mediaId,
                                    'filename' => $filename,
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'body',
                        'parameters' => $bodyParameters,
                    ],
                ],
            ],
        ];

        $response = $this->request('POST', $url, json_encode($payload), false);

        if (empty($response['messages'][0]['id'])) {
            throw new WhatsAppApiException(
                'Meta no devolvió un message_id. Respuesta: ' . json_encode($response)
            );
        }

        return $response['messages'][0]['id'];
    }

    /**
     * Reserva un cupo del limite diario de envios (SendLimiter) justo antes de llamar a Meta. Un envio que luego falla
     * tambien consume cupo: protege contra bucles de reintentos.
     *
     * @return int id del registro de envio (0 si el contador no estuvo disponible: se permite igualmente).
     * @throws WhatsAppApiException Si se alcanzo el limite diario; no se llama a Meta.
     */
    private function reserveSlot($toE164) {
        $slot = SendLimiter::reserve('whatsapp', 'whatsapp', $toE164);
        if ($slot === false) {
            throw new WhatsAppApiException('Se alcanzo el limite diario de envios de WhatsApp (' . SendLimiter::limit('whatsapp') . '). No se envio el mensaje; se puede reintentar manana o ajustar el limite en config/app.php.');
        }
        return $slot;
    }

    /**
     * Salvaguarda de entornos de prueba: si 'allowed_recipients' esta definida en config/whatsapp.php
     * (lista de numeros en formato E.164 sin '+', p. ej. '584245606018'), se NIEGA a enviar a cualquier
     * otro numero. Se evalua antes de cualquier llamada de red y cubre todos los tipos de envio.
     * Si la clave no existe o esta vacia, no se restringe nada (comportamiento de produccion).
     *
     * @throws WhatsAppApiException Si el destinatario no esta en la lista permitida.
     */
    private function assertRecipientAllowed($toE164) {
        $allowed = $this->config['allowed_recipients'] ?? [];
        if (empty($allowed) || !is_array($allowed)) {
            return;
        }
        if (!in_array((string)$toE164, array_map('strval', $allowed), true)) {
            error_log('[WhatsAppClient] Envio BLOQUEADO: el destinatario no esta en allowed_recipients (config/whatsapp.php).');
            throw new WhatsAppApiException('Destinatario no autorizado en este entorno (allowed_recipients). No se envio el mensaje.');
        }
    }

    /**
     * Ejecuta una petición HTTP hacia la Graph API de Meta y decodifica la respuesta JSON.
     *
     * @param string $method       Método HTTP ('POST', 'GET', etc.).
     * @param string $url          URL completa del endpoint.
     * @param mixed  $body         Cuerpo de la petición: array (multipart) o string JSON.
     * @param bool   $isMultipart  True si $body es un array para multipart/form-data.
     * @return array La respuesta decodificada de Meta.
     * @throws WhatsAppApiException Si cURL falla o Meta responde con un código de error.
     */
    private function request($method, $url, $body, $isMultipart) {
        $accessToken = $this->config['access_token'];

        $headers = ["Authorization: Bearer {$accessToken}"];
        if (!$isMultipart) {
            $headers[] = 'Content-Type: application/json';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::REQUEST_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $rawResponse = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrno !== 0) {
            throw new WhatsAppApiException("Error de conexión con Meta (cURL #{$curlErrno}): {$curlError}");
        }

        $decoded = json_decode($rawResponse, true);

        if ($httpCode >= 400) {
            $errorMessage = $decoded['error']['message'] ?? $rawResponse;
            $errorCode = $decoded['error']['code'] ?? $httpCode;
            throw new WhatsAppApiException("Error HTTP {$httpCode} de Meta (code {$errorCode}): {$errorMessage}");
        }

        if (!is_array($decoded)) {
            throw new WhatsAppApiException('Respuesta inválida de Meta (no es JSON válido): ' . substr((string)$rawResponse, 0, 500));
        }

        return $decoded;
    }
}
