<?php
require_once 'services/Mailer.php';
require_once 'models/PaymentWorkflow.php';

/**
 * Comunicaciones automaticas del flujo de verificacion de pagos.
 *
 *  - notifyReported():  avisa por correo a quienes pueden conciliar (secretarios y administradores) que hay un pago en revision.
 *  - notifyRejected():  avisa por correo al representante que su pago fue rechazado, con el motivo.
 *  - (El recibo al aprobar lo envian EmailReceiptService / WhatsAppReceiptService, que ya existen.)
 *
 * REGLA: una notificacion NUNCA bloquea ni revierte el cambio de estado. Todos los metodos capturan
 * cualquier error, lo registran con error_log() y devuelven un resultado; no lanzan excepciones.
 * El cambio de estado ya esta confirmado en la base de datos cuando se llega aqui.
 *
 * No se envia WhatsApp de rechazo: Meta exige una plantilla aprobada y no existe una para este caso.
 * Ademas del correo, quien concilia ve los pagos por revisar dentro del sistema, en el contador
 * "Por revisar" del menu (PaymentWorkflow::countReviewableBy). El correo es un complemento, no la unica via.
 */
class PaymentNotificationService {

    /**
     * Avisa a quienes pueden conciliar (los roles que PaymentWorkflow autoriza a aprobar: secretario y administrador)
     * de que un pago espera revision. El correo NO incluye la referencia bancaria ni el comprobante: quien concilia
     * debe verlos dentro del sistema. Quien registro el pago no recibe el aviso (no podra conciliarlo).
     *
     * @return array ['success' => bool, 'sent' => int, 'error' => ?string]
     */
    public function notifyReported(array $payment, array $reporter) {
        try {
            // Los roles se toman del modelo: si cambia quien puede aprobar, el aviso lo sigue sin tocar este archivo.
            $recipients = $this->activeEmails(PaymentWorkflow::rolesFor('approve'));
            // Quien reporto no se avisa a si mismo (separacion de funciones: no podra aprobarlo).
            $recipients = array_values(array_filter($recipients, fn($r) => (int)$r['id'] !== (int)$reporter['id']));
            if (empty($recipients)) {
                return ['success' => false, 'sent' => 0, 'error' => 'No hay secretarios ni administradores activos a quienes avisar.'];
            }

            $athlete = htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')));
            $concept = htmlspecialchars($payment['concept_name'] ?? 'Pago');
            $period  = htmlspecialchars(($payment['month'] ?? '') . ' ' . ($payment['year'] ?? ''));
            $amount  = number_format((float)$payment['amount'], 2);
            $by      = htmlspecialchars($reporter['name']);
            $link    = htmlspecialchars(BASE_URL . 'payments/view?id=' . (int)$payment['id']);

            $subject = 'Pago por revisar - ' . trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? ''));
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #8B1538;'>Pago en revisión</h2>
                    <p>Se reportó un pago que espera su verificación:</p>
                    <ul>
                        <li>Atleta: <strong>{$athlete}</strong></li>
                        <li>Concepto: <strong>{$concept}</strong> ({$period})</li>
                        <li>Monto: <strong>Bs. {$amount}</strong></li>
                        <li>Reportado por: <strong>{$by}</strong></li>
                    </ul>
                    <p>Concilie la referencia bancaria en el sistema y apruebe o rechace el pago.</p>
                    <p><a href='{$link}' style='background:#8B1538;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;'>Abrir el pago</a></p>
                    <p style='color: #666; font-size: 12px; margin-top: 30px;'>Mensaje automático del Sistema de Gestión de la Escuela de Béisbol Menor Maporal.</p>
                </div>";

            $mailer = new Mailer();
            $sent = 0;
            foreach ($recipients as $r) {
                if ($mailer->send($r['email'], $subject, $body)) { $sent++; }
                else { error_log("[PaymentNotification] No se pudo avisar al secretario #{$r['id']} del pago #{$payment['id']}."); }
            }
            return ['success' => $sent > 0, 'sent' => $sent, 'error' => $sent > 0 ? null : 'No se pudo enviar ningún aviso.'];
        } catch (\Throwable $e) {
            error_log('[PaymentNotification::notifyReported] ' . $e->getMessage());
            return ['success' => false, 'sent' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Avisa al representante del atleta de que su pago fue rechazado, con el motivo escrito por
     * quien lo reviso. Va a athletes.parent_email, el mismo destino que el recibo.
     *
     * @return array ['success' => bool, 'error' => ?string]
     */
    public function notifyRejected(array $payment, $reason) {
        try {
            $to = trim($payment['parent_email'] ?? '');
            if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'El correo del representante está vacío o no es válido.'];
            }

            $parent  = htmlspecialchars($payment['parent_name'] ?? 'Representante');
            $athlete = htmlspecialchars(trim(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')));
            $period  = htmlspecialchars(($payment['month'] ?? '') . ' ' . ($payment['year'] ?? ''));
            $amount  = number_format((float)$payment['amount'], 2);
            $reasonSafe = nl2br(htmlspecialchars((string)$reason));

            $subject = 'Su pago no pudo ser verificado - Escuela de Béisbol Menor Maporal';
            $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #8B1538;'>Pago no verificado</h2>
                    <p>Hola <strong>{$parent}</strong>,</p>
                    <p>No pudimos verificar el pago de <strong>{$athlete}</strong> correspondiente a <strong>{$period}</strong> por <strong>Bs. {$amount}</strong>.</p>
                    <div style='background-color: #fff8f8; border-left: 4px solid #8B1538; padding: 12px 16px; margin: 20px 0;'>
                        <p style='margin: 0; font-size: 12px; color: #8B1538; text-transform: uppercase;'>Motivo</p>
                        <p style='margin: 6px 0 0;'>{$reasonSafe}</p>
                    </div>
                    <p>Puede volver a reportar el pago desde el sistema con los datos correctos, o comunicarse con la escuela.</p>
                    <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este es un correo automático del Sistema de Gestión de la Escuela de Béisbol Menor Maporal.</p>
                </div>";

            $ok = (new Mailer())->send($to, $subject, $body);
            return ['success' => (bool)$ok, 'error' => $ok ? null : 'PHPMailer no pudo enviar el correo (ver log).'];
        } catch (\Throwable $e) {
            error_log('[PaymentNotification::notifyRejected] ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /** Cuentas activas de los roles indicados con correo valido: [['id'=>..,'email'=>..], ...]. */
    private function activeEmails(array $roles) {
        if (empty($roles)) {
            return [];
        }
        $db = Database::getInstance()->getConnection();
        $in = implode(',', array_fill(0, count($roles), '?'));
        $stmt = $db->prepare("SELECT id, email FROM admins WHERE role IN ({$in}) AND status = 'active' AND email <> ''");
        $stmt->execute(array_values($roles));
        return array_values(array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), fn($r) => filter_var($r['email'], FILTER_VALIDATE_EMAIL)));
    }
}
