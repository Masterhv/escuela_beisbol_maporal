<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (is_file('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
}

/**
 * Clase Mailer para manejar el envío de correos electrónicos.
 * Utiliza la librería PHPMailer para el envío de correos a través de SMTP.
 */
require_once __DIR__ . '/SendLimiter.php';

class Mailer {
    /**
     * @var PHPMailer La instancia de PHPMailer.
     */
    private $mailer;

    /**
     * @var array La configuración para el envío de correos.
     */
    private $config;

    /**
     * Constructor de la clase Mailer.
     * Carga la configuración y configura la instancia de PHPMailer.
     */
    public function __construct() {
        // Un require fallido es un error fatal que ningun try/catch atrapa: dejaria en blanco la pagina que intento
        // enviar el correo (p. ej. el reporte de un pago, ya guardado). Se convierte en excepcion para que quien
        // llama la capture, registre el fallo y siga.
        if (!is_file('config/mail.php')) {
            throw new \RuntimeException('Falta config/mail.php: no se puede enviar correo.');
        }
        if (!class_exists(PHPMailer::class)) {
            throw new \RuntimeException('Falta vendor/ (PHPMailer): ejecute composer install.');
        }
        $this->config = require 'config/mail.php';
        
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['port'];
        
        // Configurar timeouts para evitar bloqueos
        $this->mailer->Timeout = 10; // Timeout de conexión en segundos
        $this->mailer->SMTPKeepAlive = false;
        
        // Configuración adicional para debugging (desactivar en producción)
        $this->mailer->SMTPDebug = 0; // 0 = sin debug, 2 = debug completo
        
        $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
    }

    /**
     * Salvaguarda de entornos de prueba: si 'allowed_recipients' esta definida en config/mail.php (lista de
     * correos), NO se envia a ninguna otra direccion; el intento se registra y el envio devuelve false.
     * Si la clave no existe o esta vacia, no se restringe nada (comportamiento de produccion).
     * Todos los correos del sistema pasan por send() o sendWithAttachment(), asi que la regla no se puede esquivar.
     *
     * OJO: tambien afecta a la recuperacion de contrasena. Con la lista activa, un usuario real no recibira su enlace.
     */
    private function isRecipientAllowed($to) {
        $allowed = $this->config['allowed_recipients'] ?? [];
        if (empty($allowed) || !is_array($allowed)) {
            return true;
        }
        $ok = in_array(strtolower(trim((string)$to)), array_map(fn($e) => strtolower(trim((string)$e)), $allowed), true);
        if (!$ok) {
            error_log('[Mailer] Envio BLOQUEADO: el destinatario no esta en allowed_recipients (config/mail.php).');
        }
        return $ok;
    }

    /**
     * Envía un correo electrónico.
     *
     * @param string $to La dirección de correo del destinatario.
     * @param string $subject El asunto del correo.
     * @param string $body El cuerpo del correo (puede ser HTML).
     * @param string $purpose Para que es el correo (receipt, enrollment, notification, password_reset...). Solo afecta al
     *                        registro y al limite diario de envios (SendLimiter); 'password_reset' esta exento del limite.
     * @return bool True si el correo se envió correctamente, false en caso contrario (tambien si se alcanzo el limite diario).
     */
    public function send($to, $subject, $body, $purpose = 'notification') {
        if (!$this->isRecipientAllowed($to)) {
            return false;
        }
        $slot = SendLimiter::reserve('email', $purpose, $to);
        if ($slot === false) {
            return false;   // limite diario alcanzado: no se envia
        }
        $ok = $this->doSend($to, $subject, $body);
        SendLimiter::finish($slot, $ok);
        return $ok;
    }

    /** Envio real (PHPMailer). Se llama solo desde send(), despues de la lista blanca y del limite diario. */
    private function doSend($to, $subject, $body) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->CharSet = 'UTF-8';

            $result = $this->mailer->send();
            return $result;
        } catch (Exception $e) {
            // Registrar el error con más detalles
            error_log("Error al enviar correo a {$to}: " . $e->getMessage());
            error_log("PHPMailer Error Info: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    /**
     * Envía un correo electrónico con un archivo adjunto (ej. un PDF generado en memoria).
     *
     * @param string $to                  La dirección de correo del destinatario.
     * @param string $subject             El asunto del correo.
     * @param string $body                El cuerpo del correo (HTML).
     * @param string $attachmentContent   Contenido binario del archivo adjunto.
     * @param string $attachmentFilename  Nombre de archivo mostrado al destinatario.
     * @param string $attachmentMime      Tipo MIME del adjunto (por defecto 'application/pdf').
     * @param string $purpose             Para que es el correo (ver send()); afecta al registro y al limite diario.
     * @return bool True si el correo se envió correctamente, false en caso contrario (tambien si se alcanzo el limite diario).
     */
    public function sendWithAttachment($to, $subject, $body, $attachmentContent, $attachmentFilename, $attachmentMime = 'application/pdf', $purpose = 'document') {
        if (!$this->isRecipientAllowed($to)) {
            return false;
        }
        $slot = SendLimiter::reserve('email', $purpose, $to);
        if ($slot === false) {
            return false;   // limite diario alcanzado: no se envia
        }
        $ok = $this->doSendWithAttachment($to, $subject, $body, $attachmentContent, $attachmentFilename, $attachmentMime);
        SendLimiter::finish($slot, $ok);
        return $ok;
    }

    /** Envio real con adjunto (PHPMailer). Se llama solo desde sendWithAttachment(). */
    private function doSendWithAttachment($to, $subject, $body, $attachmentContent, $attachmentFilename, $attachmentMime) {
        $tmpFile = tempnam(sys_get_temp_dir(), 'mail_att_');

        try {
            file_put_contents($tmpFile, $attachmentContent);

            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->addAddress($to);
            $this->mailer->addAttachment($tmpFile, $attachmentFilename, 'base64', $attachmentMime);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->CharSet = 'UTF-8';

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Error al enviar correo con adjunto a {$to}: " . $e->getMessage());
            error_log("PHPMailer Error Info: {$this->mailer->ErrorInfo}");
            return false;
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}