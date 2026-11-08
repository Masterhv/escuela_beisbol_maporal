<?php
/**
 * SendLimiter: limite diario de envios automaticos (correo y WhatsApp) y registro de cada intento.
 *
 * POR QUE EXISTE: al pasar a produccion se quitan las listas blancas de destinatarios, que eran lo unico que impedia un
 * envio masivo accidental. Un mensaje enviado a una familia NO se puede retirar. Este limite es la red de seguridad:
 * si un error o un bucle intenta mandar cientos de mensajes, se corta al llegar al tope del dia.
 *
 * COMO FUNCIONA
 *  - Cada canal (email / whatsapp) tiene su tope diario en config/app.php (daily_send_limit_email / _whatsapp).
 *  - reserve() consume un cupo con UNA sola sentencia atomica sobre send_counters (INSERT ... ON DUPLICATE KEY UPDATE
 *    used = used + 1): bloquea solo la fila (canal, dia), sin interbloqueos, y dos peticiones simultaneas nunca pueden
 *    pasarse del tope. Si el valor resultante supera el tope, se DEVUELVE el cupo y el envio se bloquea.
 *  - Un envio que luego falla TAMBIEN consume cupo: protege contra bucles de reintentos contra un servidor caido.
 *  - FALLA CERRADO: si el contador no se puede consultar (base de datos caida o con un error transitorio), el envio se
 *    BLOQUEA tras reintentar, no se permite. Un limite que se salta cuando algo falla no protege de nada.
 *    Unica excepcion: la recuperacion de contrasena (ver abajo), que siempre se permite.
 *  - La recuperacion de contrasena esta EXENTA (purpose = 'password_reset'): si una averia agotara el cupo, nadie
 *    podria recuperar su acceso y el sistema quedaria inutilizable. No consume cupo, pero se registra.
 *  - Configuracion: un valor > 0 es el tope; -1 desactiva el limite A PROPOSITO; 0, vacio o no numerico NO desactivan la
 *    proteccion (se usa el valor por defecto): un descuido en la configuracion no debe dejar el sistema sin freno.
 *  - El destinatario NO se guarda: solo una huella parcial (para detectar repeticiones), sin correo ni telefono.
 */
class SendLimiter {

    public const PURPOSE_PASSWORD_RESET = 'password_reset';
    private const DEFAULTS = ['email' => 150, 'whatsapp' => 200];
    private const RETRIES = 3;

    /** Tope diario del canal, de config/app.php. Devuelve -1 si el limite esta desactivado a proposito. */
    public static function limit($channel) {
        $key = $channel === 'whatsapp' ? 'daily_send_limit_whatsapp' : 'daily_send_limit_email';
        $default = self::DEFAULTS[$channel] ?? 150;
        $v = function_exists('app_config') ? app_config($key, $default) : $default;
        if (!is_numeric($v)) { return $default; }
        $v = (int)$v;
        if ($v === -1) { return -1; }          // desactivado a proposito
        return $v > 0 ? $v : $default;         // 0 o negativo distinto de -1: no se desactiva por descuido
    }

    /** Envios del canal HOY que consumieron cupo (lo que dice el contador atomico). */
    public static function usedToday(PDO $db, $channel) {
        $stmt = $db->prepare("SELECT used FROM send_counters WHERE channel = :c AND day = CURDATE()");
        $stmt->execute([':c' => $channel]);
        $v = $stmt->fetchColumn();
        return $v === false ? 0 : (int)$v;
    }

    /**
     * Reserva un cupo para un envio. Debe llamarse justo ANTES de enviar.
     *
     * @return int|false id del registro (para marcarlo despues con finish()), o false si NO se debe enviar (limite diario
     *                   alcanzado, o el contador no esta disponible y el envio no es exento).
     */
    public static function reserve($channel, $purpose, $recipient = '') {
        $exempt = $purpose === self::PURPOSE_PASSWORD_RESET;
        $hash = $recipient !== '' ? substr(hash('sha256', strtolower(trim((string)$recipient))), 0, 16) : null;
        $limit = self::limit($channel);
        $lastError = null;

        for ($attempt = 1; $attempt <= self::RETRIES; $attempt++) {
            try {
                $db = (new Database())->getConnection();

                if ($exempt || $limit === -1) {
                    // Exento o sin limite: se registra, pero no consume ni comprueba cupo.
                    $db->prepare("INSERT INTO send_log (channel, purpose, status, recipient_hash) VALUES (?, ?, 'reserved', ?)")->execute([$channel, $purpose, $hash]);
                    return (int)$db->lastInsertId();
                }

                // UNA sentencia atomica: crea la fila del dia o suma 1, y devuelve el valor ya incrementado.
                $stmt = $db->prepare(
                    "INSERT INTO send_counters (channel, day, used) VALUES (:c, CURDATE(), LAST_INSERT_ID(1))
                     ON DUPLICATE KEY UPDATE used = LAST_INSERT_ID(used + 1)"
                );
                $stmt->execute([':c' => $channel]);
                $used = (int)$db->lastInsertId();

                if ($used > $limit) {
                    // Se pasaria del tope: se devuelve el cupo (resta atomica) y se bloquea.
                    $db->prepare("UPDATE send_counters SET used = GREATEST(used - 1, 0) WHERE channel = :c AND day = CURDATE()")->execute([':c' => $channel]);
                    $db->prepare("INSERT INTO send_log (channel, purpose, status, recipient_hash, detail) VALUES (?, ?, 'blocked', ?, ?)")
                       ->execute([$channel, $purpose, $hash, "Limite diario de $limit envios alcanzado"]);
                    error_log("[SendLimiter] ENVIO BLOQUEADO ($channel/$purpose): se alcanzo el limite diario de $limit. Revisa si hay un error o un bucle; el limite se ajusta en config/app.php.");
                    return false;
                }
                $db->prepare("INSERT INTO send_log (channel, purpose, status, recipient_hash) VALUES (?, ?, 'reserved', ?)")->execute([$channel, $purpose, $hash]);
                return (int)$db->lastInsertId();
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
                usleep(random_int(20000, 80000) * $attempt);   // pausa breve y reintento (fallos transitorios, p. ej. un interbloqueo)
            }
        }

        // No se pudo consultar el contador tras varios intentos.
        error_log("[SendLimiter] No se pudo consultar el contador ($channel/$purpose): $lastError");
        if ($exempt) {
            return 0;   // la recuperacion de contrasena se permite siempre
        }
        error_log("[SendLimiter] ENVIO BLOQUEADO por seguridad ($channel/$purpose): sin contador disponible no se puede garantizar el limite diario.");
        return false;
    }

    /** Marca el resultado de un envio reservado. No lanza excepciones. */
    public static function finish($id, $ok, $detail = '') {
        if (!$id) { return; }
        try {
            $db = (new Database())->getConnection();
            $db->prepare("UPDATE send_log SET status = ?, detail = ? WHERE id = ?")
               ->execute([$ok ? 'sent' : 'failed', $detail !== '' ? mb_substr($detail, 0, 200) : null, (int)$id]);
        } catch (\Throwable $e) {
            error_log('[SendLimiter] No se pudo actualizar el registro de envio: ' . $e->getMessage());
        }
    }
}
