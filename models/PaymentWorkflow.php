<?php
require_once 'models/Model.php';

/**
 * PaymentWorkflow: UNICA puerta por la que cambia el estado de un pago.
 *
 * Ciclo de vida:
 *
 *   pending ──report──> in_review ──approve──> approved ──reopen──> in_review
 *      ^                    │
 *      │                    └──reject──> rejected ──report──> in_review
 *      └─────────(rejected tambien puede volver a reportarse)
 *
 * Reglas financieras que este modelo garantiza (no la interfaz):
 *  - Un pago solo cambia de estado mediante una transicion valida de la tabla TRANSITIONS.
 *  - Cada transicion exige un rol concreto (ROLE_FOR). Un rol no listado NO puede ejecutarla.
 *  - Separacion de funciones: quien reporto/registro un pago no puede aprobarlo ni rechazarlo, sea cual sea su rol
 *    (secretario o administrador). Siempre hace falta una SEGUNDA persona para conciliar.
 *  - Rechazar y reabrir exigen un comentario (queda en la bitacora).
 *  - Aprobar y rechazar: secretario y administrador. Un pago aprobado es inmutable: solo el administrador
 *    puede reabrirlo, con motivo.
 *  - Ningun rol puede BORRAR un pago ni su historial: no existe ninguna transicion ni metodo que lo haga.
 *  - Cada cambio de estado y su registro en payment_history ocurren en UNA transaccion, con el
 *    pago bloqueado (FOR UPDATE): dos aprobaciones simultaneas no pueden producir dos recibos.
 *  - payment_history es solo-escritura: este modelo nunca actualiza ni borra sus filas.
 */
class PaymentWorkflow extends Model {

    protected $table = 'payments';

    public const PENDING   = 'pending';
    public const IN_REVIEW = 'in_review';
    public const APPROVED  = 'approved';
    public const REJECTED  = 'rejected';

    public const STATUSES = [self::PENDING, self::IN_REVIEW, self::APPROVED, self::REJECTED];

    /** Etiquetas visibles. Unica fuente de verdad para vistas, correos y reportes. */
    public const LABELS = [
        self::PENDING   => 'Pendiente',
        self::IN_REVIEW => 'En Revisión',
        self::APPROVED  => 'Aprobado',
        self::REJECTED  => 'Rechazado',
    ];

    /** Clases CSS (definidas en style.css) por estado. */
    public const BADGES = [
        self::PENDING   => 'bg-yellow-100 text-yellow-800',
        self::IN_REVIEW => 'bg-blue-100 text-blue-800',
        self::APPROVED  => 'bg-green-100 text-green-800',
        self::REJECTED  => 'bg-red-100 text-red-800',
    ];

    /** accion => [estados de origen permitidos, estado destino] */
    private const TRANSITIONS = [
        'report'  => [[self::PENDING, self::REJECTED], self::IN_REVIEW],
        'approve' => [[self::IN_REVIEW],               self::APPROVED],
        'reject'  => [[self::IN_REVIEW],               self::REJECTED],
        'reopen'  => [[self::APPROVED],                self::IN_REVIEW],
    ];

    /** accion => roles autorizados. */
    private const ROLE_FOR = [
        'report'  => ['representante', 'secretario', 'administrador'],
        'approve' => ['secretario', 'administrador'],
        'reject'  => ['secretario', 'administrador'],
        'reopen'  => ['administrador'],
    ];

    /** Acciones que obligan a escribir un comentario. */
    private const REQUIRES_COMMENT = ['reject', 'reopen'];

    /** Longitud de una referencia bancaria: solo digitos, de REFERENCE_MIN a REFERENCE_MAX. */
    public const REFERENCE_MIN = 4;
    public const REFERENCE_MAX = 20;

    /**
     * Devuelve la referencia normalizada si es valida, o null. Solo se quitan los espacios de los extremos: NO se
     * "corrige" nada mas (quitar letras o guiones en silencio podria cambiar el numero sin que la familia lo note, y entonces
     * la referencia no coincidiria con la del banco). Solo digitos, entre REFERENCE_MIN y REFERENCE_MAX.
     */
    public static function normalizeReference($raw) {
        $ref = trim((string)$raw);
        return preg_match('/^[0-9]{' . self::REFERENCE_MIN . ',' . self::REFERENCE_MAX . '}$/D', $ref) ? $ref : null;
    }

    /** Estados que cuentan como DEUDA (todo lo que no esta aprobado). */
    public const DEBT_STATUSES = [self::PENDING, self::IN_REVIEW, self::REJECTED];

    /**
     * Los roles autorizados para una accion. Aprobar y rechazar: secretario y administrador (nunca sobre un pago
     * que ellos mismos registraron). Reabrir un pago aprobado: solo el administrador.
     */
    public static function rolesFor($action) {
        return self::ROLE_FOR[$action] ?? [];
    }

    public static function label($status) {
        return self::LABELS[$status] ?? ucfirst((string)$status);
    }

    public static function badge($status) {
        return self::BADGES[$status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Acciones que un usuario puede ejecutar AHORA sobre un pago. La interfaz las usa para decidir
     * que botones mostrar; el modelo las vuelve a validar al ejecutar (la UI nunca es la defensa).
     *
     * @return string[] subconjunto de report|approve|reject|reopen
     */
    public static function allowedActions(array $payment, $role, $adminId) {
        $out = [];
        $status = $payment['status'] ?? '';
        foreach (self::TRANSITIONS as $action => [$from, $to]) {
            if (!in_array($status, $from, true)) continue;
            if (!in_array($role, self::ROLE_FOR[$action], true)) continue;
            if (in_array($action, ['approve', 'reject'], true)
                && !empty($payment['reported_by']) && (int)$payment['reported_by'] === (int)$adminId) {
                continue; // separacion de funciones
            }
            $out[] = $action;
        }
        return $out;
    }

    /**
     * Ejecuta una transicion de forma atomica.
     *
     * @param int    $paymentId
     * @param string $action  report|approve|reject|reopen
     * @param array  $actor   ['id'=>int,'name'=>string,'role'=>string]
     * @param string $comment Obligatorio para reject y reopen
     * @param array  $extra   Solo para 'report': reference_number, payment_method, payment_date, receipt_image
     * @return array ['ok'=>bool, 'error'=>?string, 'code'=>?string, 'payment'=>?array, 'from'=>?string, 'to'=>?string]
     */
    public function transition($paymentId, $action, array $actor, $comment = '', array $extra = []) {
        $fail = function ($code, $msg) {
            return ['ok' => false, 'error' => $msg, 'code' => $code, 'payment' => null, 'from' => null, 'to' => null];
        };

        if (!isset(self::TRANSITIONS[$action])) {
            return $fail('invalid_action', 'Acción no reconocida.');
        }
        $role = (string)($actor['role'] ?? '');
        if (!in_array($role, self::ROLE_FOR[$action], true)) {
            return $fail('forbidden_role', 'Tu rol no tiene permiso para esta acción.');
        }
        $comment = trim((string)$comment);
        if (in_array($action, self::REQUIRES_COMMENT, true) && mb_strlen($comment) < 5) {
            return $fail('comment_required', 'Debes escribir un motivo (mínimo 5 caracteres).');
        }
        if (mb_strlen($comment) > 500) {
            return $fail('comment_too_long', 'El comentario no puede superar 500 caracteres.');
        }

        [$allowedFrom, $to] = self::TRANSITIONS[$action];

        try {
            $this->db->beginTransaction();

            // Bloqueo de fila: serializa aprobaciones simultaneas del mismo pago.
            $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = :id FOR UPDATE");
            $stmt->bindValue(':id', (int)$paymentId, PDO::PARAM_INT);
            $stmt->execute();
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $this->db->rollBack();
                return $fail('not_found', 'El pago no existe.');
            }
            $from = $payment['status'];

            if (!in_array($from, $allowedFrom, true)) {
                $this->db->rollBack();
                return $fail('invalid_transition',
                    'No se puede ' . $this->verb($action) . ' un pago en estado "' . self::label($from) . '".');
            }

            // Separacion de funciones: quien lo reporto no lo concilia.
            if (in_array($action, ['approve', 'reject'], true)
                && !empty($payment['reported_by']) && (int)$payment['reported_by'] === (int)$actor['id']) {
                $this->db->rollBack();
                return $fail('self_review', 'No puedes aprobar ni rechazar un pago que tú mismo registraste.');
            }

            $now = date('Y-m-d H:i:s');
            $set = ['status = :status'];
            $bind = [':status' => $to];

            switch ($action) {
                case 'report':
                    $rawRef = trim((string)($extra['reference_number'] ?? ''));
                    $method = trim((string)($extra['payment_method'] ?? ''));
                    if ($rawRef === '' || $method === '') {
                        $this->db->rollBack();
                        return $fail('report_incomplete', 'El número de referencia y el método de pago son obligatorios.');
                    }
                    // La defensa real: aunque el navegador ya filtre, el servidor exige SOLO DIGITOS (4 a 20).
                    $ref = self::normalizeReference($rawRef);
                    if ($ref === null) {
                        $this->db->rollBack();
                        return $fail('invalid_reference', 'La referencia debe tener solo números, entre ' . self::REFERENCE_MIN . ' y ' . self::REFERENCE_MAX . ' dígitos.');
                    }
                    // Una referencia bancaria identifica UNA operacion del banco: no puede respaldar dos pagos activos, sea cual
                    // sea el metodo que se haya escrito (antes se comparaba tambien el metodo, y el mismo deposito podia
                    // justificar dos cobros declarando "transferencia" en uno y "pago movil" en el otro).
                    $dup = $this->db->prepare("SELECT id FROM payments WHERE reference_number = :r AND id <> :id AND status IN ('in_review','approved') LIMIT 1");
                    $dup->execute([':r' => $ref, ':id' => (int)$paymentId]);
                    if ($dup->fetchColumn()) {
                        $this->db->rollBack();
                        return $fail('duplicate_reference', 'Esa referencia bancaria ya está asociada a otro pago.');
                    }
                    $set[] = 'reference_number = :ref';      $bind[':ref'] = $ref;
                    $set[] = 'payment_method = :method';     $bind[':method'] = $method;
                    $set[] = 'payment_date = :pdate';        $bind[':pdate'] = $extra['payment_date'] ?? date('Y-m-d');
                    $set[] = 'reported_by = :rb';            $bind[':rb'] = (int)$actor['id'];
                    $set[] = 'reported_at = :ra';            $bind[':ra'] = $now;
                    $set[] = 'reviewed_by = NULL';
                    $set[] = 'reviewed_at = NULL';
                    $set[] = 'review_comment = NULL';
                    if (!empty($extra['receipt_image'])) {
                        $set[] = 'receipt_image = :img';     $bind[':img'] = $extra['receipt_image'];
                    }
                    break;

                case 'approve':
                    $set[] = 'reviewed_by = :rv';            $bind[':rv'] = (int)$actor['id'];
                    $set[] = 'reviewed_at = :rt';            $bind[':rt'] = $now;
                    $set[] = 'review_comment = :rc';         $bind[':rc'] = ($comment !== '' ? $comment : null);
                    break;

                case 'reject':
                    $set[] = 'reviewed_by = :rv';            $bind[':rv'] = (int)$actor['id'];
                    $set[] = 'reviewed_at = :rt';            $bind[':rt'] = $now;
                    $set[] = 'review_comment = :rc';         $bind[':rc'] = $comment;
                    break;

                case 'reopen':
                    // Se conserva reported_by/reviewed_by originales: la bitacora guarda quien reabrio.
                    $set[] = 'review_comment = :rc';         $bind[':rc'] = $comment;
                    // Un recibo emitido deja de ser valido: se reinicia para que el nuevo recibo se envie.
                    $set[] = "email_status = 'not_sent'";
                    $set[] = 'email_sent_at = NULL';
                    $set[] = 'email_error = NULL';
                    $set[] = "whatsapp_status = 'not_sent'";
                    $set[] = 'whatsapp_sent_at = NULL';
                    $set[] = 'whatsapp_error = NULL';
                    break;
            }

            $upd = $this->db->prepare('UPDATE payments SET ' . implode(', ', $set) . ' WHERE id = :pid AND status = :expected');
            foreach ($bind as $k => $v) { $upd->bindValue($k, $v); }
            $upd->bindValue(':pid', (int)$paymentId, PDO::PARAM_INT);
            $upd->bindValue(':expected', $from);
            $upd->execute();
            if ($upd->rowCount() !== 1) {
                $this->db->rollBack();
                return $fail('conflict', 'El pago cambió mientras lo procesabas. Recarga e inténtalo de nuevo.');
            }

            $hist = $this->db->prepare(
                "INSERT INTO payment_history (payment_id, from_status, to_status, action, actor_id, actor_name, actor_role, comment, amount)
                 VALUES (:pid, :f, :t, :a, :aid, :an, :ar, :c, :amt)"
            );
            $hist->execute([
                ':pid' => (int)$paymentId, ':f' => $from, ':t' => $to, ':a' => $action,
                ':aid' => (int)$actor['id'], ':an' => (string)$actor['name'], ':ar' => $role,
                ':c' => ($comment !== '' ? $comment : null), ':amt' => $payment['amount'],
            ]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('[PaymentWorkflow] ' . $e->getMessage());
            return $fail('server_error', 'No se pudo completar la operación. Inténtalo de nuevo.');
        }

        return [
            'ok' => true, 'error' => null, 'code' => null,
            'payment' => $this->loadPayment($paymentId), 'from' => $from, 'to' => $to,
        ];
    }

    /**
     * Registra un pago NUEVO por parte del personal (secretario/administrador). Nace siempre en
     * 'pending': nadie puede crear un pago ya aprobado. Para cobrarlo debe pasar por revision.
     *
     * @return int|false id del pago creado
     */
    public function createPending(array $data, array $actor) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "INSERT INTO payments (athlete_id, athlete_name, concept_id, amount, due_date, status, month, year, notes)
                 VALUES (:aid, '', :cid, :amt, :due, 'pending', :month, :year, :notes)"
            );
            $stmt->execute([
                ':aid' => (int)$data['athlete_id'], ':cid' => (int)$data['concept_id'],
                ':amt' => $data['amount'], ':due' => $data['due_date'],
                ':month' => $data['month'], ':year' => (int)$data['year'], ':notes' => $data['notes'] ?? null,
            ]);
            $id = (int)$this->db->lastInsertId();

            $hist = $this->db->prepare(
                "INSERT INTO payment_history (payment_id, from_status, to_status, action, actor_id, actor_name, actor_role, comment, amount)
                 VALUES (:pid, NULL, 'pending', 'create', :aid, :an, :ar, :c, :amt)"
            );
            $hist->execute([
                ':pid' => $id, ':aid' => (int)$actor['id'], ':an' => (string)$actor['name'],
                ':ar' => (string)$actor['role'], ':c' => 'Cobro generado.', ':amt' => $data['amount'],
            ]);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('[PaymentWorkflow::createPending] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Edicion de datos de un cobro. SOLO mientras el pago esta 'pending' o 'rejected': en revision
     * o aprobado, monto/fecha/mes no se tocan (para cambiarlos hay que rechazar o reabrir antes).
     * Esta funcion nunca modifica el estado.
     */
    public function updateDetails($paymentId, array $data, array $actor) {
        $p = $this->loadPayment($paymentId);
        if (!$p) { return ['ok' => false, 'code' => 'not_found', 'error' => 'El pago no existe.']; }
        if (!in_array($p['status'], [self::PENDING, self::REJECTED], true)) {
            return ['ok' => false, 'code' => 'locked',
                'error' => 'Un pago ' . mb_strtolower(self::label($p['status'])) . ' no se puede modificar.'];
        }
        if (!in_array($actor['role'], ['secretario', 'administrador'], true)) {
            return ['ok' => false, 'code' => 'forbidden_role', 'error' => 'Tu rol no puede editar pagos.'];
        }
        $stmt = $this->db->prepare(
            "UPDATE payments SET concept_id = :cid, amount = :amt, due_date = :due, month = :month, year = :year, notes = :notes
             WHERE id = :id AND status IN ('pending','rejected')"
        );
        $stmt->execute([
            ':cid' => (int)$data['concept_id'], ':amt' => $data['amount'], ':due' => $data['due_date'],
            ':month' => $data['month'], ':year' => (int)$data['year'], ':notes' => $data['notes'] ?? null,
            ':id' => (int)$paymentId,
        ]);
        return ['ok' => true, 'code' => null, 'error' => null];
    }

    /**
     * Cuantos pagos En Revision muestra el contador del menu: los que ESTE usuario puede conciliar. Excluye los
     * que el mismo registro (no podria aprobarlos: separacion de funciones), de modo que el numero coincide con
     * lo que realmente puede resolver. Aplica igual al secretario y al administrador, que concilian por igual.
     */
    public function countReviewableBy($adminId) {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM payments WHERE status = 'in_review' AND (reported_by IS NULL OR reported_by <> :me)"
        );
        $stmt->bindValue(':me', (int)$adminId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /** Bitacora de un pago, de la mas antigua a la mas reciente. */
    public function history($paymentId) {
        $stmt = $this->db->prepare("SELECT * FROM payment_history WHERE payment_id = :id ORDER BY id ASC");
        $stmt->bindValue(':id', (int)$paymentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Pago con datos de atleta, concepto y nombres de quien reporto/reviso. */
    public function loadPayment($paymentId) {
        $stmt = $this->db->prepare(
            "SELECT p.*, s.first_name, s.last_name, s.category, s.age, s.parent_name, s.parent_phone,
                    s.parent_email, s.parent_id_card, c.name AS concept_name,
                    ar.full_name AS reported_by_name, av.full_name AS reviewed_by_name
             FROM payments p
             JOIN athletes s ON s.id = p.athlete_id
             LEFT JOIN payment_concepts c ON c.id = p.concept_id
             LEFT JOIN admins ar ON ar.id = p.reported_by
             LEFT JOIN admins av ON av.id = p.reviewed_by
             WHERE p.id = :id"
        );
        $stmt->bindValue(':id', (int)$paymentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function verb($action) {
        return ['report' => 'reportar', 'approve' => 'aprobar', 'reject' => 'rechazar', 'reopen' => 'reabrir'][$action] ?? $action;
    }
}
