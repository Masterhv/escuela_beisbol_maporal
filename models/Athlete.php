<?php
require_once 'models/Model.php';

/**
 * Modelo Athlete para gestionar los atletas de la escuela.
 * Hereda de la clase Model y define la lógica de negocio para los atletas.
 */
class Athlete extends Model {
    /**
     * @var string La tabla de la base de datos para los atletas.
     */
    protected $table = 'athletes';
    
    /**
     * Obtiene el número de atletas activos por categoría.
     *
     * @return array Un array con el conteo de atletas por cada categoría.
     */
    public function getAthletesByCategory() {
        $sql = "SELECT category, COUNT(*) as count FROM {$this->table} WHERE status = 'active' GROUP BY category";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene estadísticas sobre los atletas.
     *
     * @return array Un array con el total de atletas y los conteos por estado (activo, inactivo, suspendido).
     */
    public function getAthleteStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca atletas EXCLUSIVAMENTE por cédula del atleta o cédula del representante.
     *
     * @param string $term El término de búsqueda (solo números de cédula).
     * @param int[]|null $onlyIds Si no es null, restringe el resultado a esos ids (se aplica en SQL, ANTES
     *                            del LIMIT, para que un representante reciba sus atletas y no 10 ajenos).
     * @return array Una lista de atletas que coinciden con el término.
     */
    public function searchAthletes($term, ?array $onlyIds = null) {
        if ($onlyIds !== null && empty($onlyIds)) {
            return [];
        }
        $scopeSql = $onlyIds !== null ? ' AND id IN (' . implode(',', array_map('intval', $onlyIds)) . ')' : '';
        $sql = "SELECT id, first_name, last_name, id_card, parent_id_card, photo FROM {$this->table}
                WHERE (id_card LIKE :term1
                OR parent_id_card LIKE :term2){$scopeSql}
                ORDER BY first_name, last_name
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$term}%";
        $stmt->bindParam(':term1', $searchTerm);
        $stmt->bindParam(':term2', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca atletas por su número de cédula.
     *
     * @param string $term El número de cédula a buscar.
     * @param int[]|null $onlyIds Restricción opcional por ids (ver searchAthletes).
     * @return array Una lista de atletas que coinciden con el término.
     */
    public function searchByIdCard($term, ?array $onlyIds = null) {
        if ($onlyIds !== null && empty($onlyIds)) {
            return [];
        }
        $scopeSql = $onlyIds !== null ? ' AND id IN (' . implode(',', array_map('intval', $onlyIds)) . ')' : '';
        $sql = "SELECT id, first_name, last_name, id_card FROM {$this->table}
                WHERE id_card LIKE :term{$scopeSql}
                ORDER BY id_card
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "{$term}%";
        $stmt->bindParam(':term', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los atletas registrados más recientemente.
     *
     * @param int $limit El número de atletas a obtener.
     * @return array Una lista de los atletas más recientes.
     */
    public function getrecentAthletes($limit = 5) {
        return $this->findAll([], 'registration_date DESC', $limit);
    }
    
    /**
     * Verifica si una cédula de atleta ya está registrada.
     *
     * @param string $idCard La cédula a verificar.
     * @param int|null $excludeId Un ID de atleta a excluir de la búsqueda (útil al actualizar).
     * @return bool True si la cédula ya existe, false si no.
     */
    public function isAthleteIdCardTaken($idCard, $excludeId = null) {
        if (empty($idCard)) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id_card = :id_card";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_card', $idCard);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica si una cédula de representante ya está registrada.
     *
     * @param string $parentIdCard La cédula del representante a verificar.
     * @param int|null $excludeId Un ID de atleta a excluir de la búsqueda (útil al actualizar).
     * @return bool True si la cédula ya existe, false si no.
     */
    public function isParentIdCardTaken($parentIdCard, $excludeId = null) {
        if (empty($parentIdCard)) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE parent_id_card = :parent_id_card";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':parent_id_card', $parentIdCard);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica si un email de representante ya está registrado.
     *
     * @param string $email El email a verificar.
     * @param int|null $excludeId Un ID de atleta a excluir de la búsqueda (útil al actualizar).
     * @return bool True si el email ya existe, false si no.
     */
    public function isParentEmailTaken($email, $excludeId = null) {
        if (empty($email)) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE parent_email = :parent_email";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':parent_email', $email);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica si un teléfono de representante ya está registrado.
     *
     * @param string $phone El teléfono a verificar.
     * @param int|null $excludeId Un ID de atleta a excluir de la búsqueda (útil al actualizar).
     * @return bool True si el teléfono ya existe, false si no.
     */
    public function isParentPhoneTaken($phone, $excludeId = null) {
        if (empty($phone)) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE parent_phone = :parent_phone";
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':parent_phone', $phone);
        if ($excludeId) {
            $stmt->bindParam(':exclude_id', $excludeId);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Crea un nuevo atleta.
     *
     * @param array $data Los datos del atleta a crear.
     * @return array Un array indicando si la operación fue exitosa y el ID del nuevo atleta.
     */
    public function createAthlete($data) {
        $id = $this->create($data);
        // id real solo si la tabla lo genero (AUTO_INCREMENT); null si la fila se inserto sin id,
        // para que el controlador nunca escriba (estatus inicial, planilla) contra un id falso.
        return ['success' => $id !== false, 'id' => (is_int($id) && $id > 0) ? $id : null];
    }

    /**
     * Actualiza los datos de un atleta.
     *
     * @param int $id El ID del atleta a actualizar.
     * @param array $data Los nuevos datos del atleta.
     * @return array Un array indicando si la operación fue exitosa.
     */
    /**
     * Registra una edicion de datos del atleta en su bitacora (athlete_history): quien, cuando y que cambio.
     * Los cambios de ESTATUS llevan su propio registro (status_detail) y no se duplican aqui.
     *
     * @param int    $athleteId ID del atleta editado.
     * @param string $changes   Resumen "campo: antes -> despues" en una linea.
     * @param array  $actor     ['id', 'name', 'role'] de quien edito.
     * @return bool
     */
    public function logAthleteEdit($athleteId, $changes, $actor) {
        $stmt = $this->db->prepare(
            "INSERT INTO athlete_history (athlete_id, action, changes, actor_id, actor_name, actor_role)
             VALUES (:aid, 'update', :ch, :act_id, :act_name, :act_role)"
        );
        return $stmt->execute([
            ':aid' => (int)$athleteId, ':ch' => $changes,
            ':act_id' => ($actor['id'] ?? null) !== null ? (int)$actor['id'] : null,
            ':act_name' => (string)($actor['name'] ?? 'desconocido'),
            ':act_role' => (string)($actor['role'] ?? ''),
        ]);
    }

    /**
     * Devuelve la bitacora de ediciones del atleta, la mas reciente primero.
     *
     * @param int $athleteId ID del atleta.
     * @param int $limit     Cuantas entradas traer.
     * @return array
     */
    public function getAthleteHistory($athleteId, $limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT action, changes, actor_name, actor_role, created_at
             FROM athlete_history WHERE athlete_id = :aid ORDER BY id DESC LIMIT :lim"
        );
        $stmt->bindValue(':aid', (int)$athleteId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateAthlete($id, $data) {
        $result = $this->update($id, $data);
        return ['success' => $result];
    }

    /**
     * Obtiene una lista de atletas aplicando varios filtros.
     *
     * @param array $filters Filtros para la búsqueda (nombre, estado, fecha, edad, categoría).
     * @return array Una lista de atletas que cumplen con los filtros.
     */
    public function getAthletesWithFilters($filters = []) {
        $sql = "SELECT * FROM {$this->table}";
        $conditions = [];
        $params = [];

        // Búsqueda EXCLUSIVAMENTE por cédula del atleta o cédula del representante
        if (!empty($filters['q'])) {
            $conditions[] = "(id_card LIKE :q1 OR parent_id_card LIKE :q2)";
            $params[':q1'] = "%{$filters['q']}%";
            $params[':q2'] = "%{$filters['q']}%";
        }

        if (!empty($filters['athlete_name'])) {
            $conditions[] = "CONCAT(first_name, ' ', last_name) LIKE :athlete_name";
            $params[':athlete_name'] = "%{$filters['athlete_name']}%";
        }

        if (!empty($filters['status'])) {
            $conditions[] = "status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['filter_by'])) {
            switch ($filters['filter_by']) {
                case 'day':
                    $conditions[] = "DATE(registration_date) = CURDATE()";
                    break;
                case 'week':
                    $conditions[] = "WEEK(registration_date) = WEEK(CURDATE()) AND YEAR(registration_date) = YEAR(CURDATE())";
                    break;
                case 'month':
                    $conditions[] = "MONTH(registration_date) = MONTH(CURDATE()) AND YEAR(registration_date) = YEAR(CURDATE())";
                    break;
                case 'year':
                    $conditions[] = "YEAR(registration_date) = YEAR(CURDATE())";
                    break;
            }
        }
        
        if (!empty($filters['athlete_search'])) {
            $conditions[] = "(id_card LIKE :athlete_search OR parent_id_card LIKE :athlete_search)";
            $params[':athlete_search'] = "%{$filters['athlete_search']}%";
        }
        
        if (!empty($filters['age'])) {
            $conditions[] = "age = :age";
            $params[':age'] = $filters['age'];
        }

        if (!empty($filters['category'])) {
            $conditions[] = "category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "registration_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = "registration_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['athlete_month'])) {
            $conditions[] = "MONTH(registration_date) = :athlete_month";
            $params[':athlete_month'] = ltrim($filters['athlete_month'], '0');
        }

        if (!empty($filters['athlete_year'])) {
            $conditions[] = "YEAR(registration_date) = :athlete_year";
            $params[':athlete_year'] = $filters['athlete_year'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY first_name, last_name";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // Solvencia de retiro
    // ------------------------------------------------------------------

    /**
     * Comprueba si a un atleta se le puede emitir la solvencia de retiro.
     *
     * Reglas: el atleta existe, no tiene deuda alguna (todo cobro no aprobado: pendiente, en revisión o rechazado, venza o no su fecha) y no
     * tiene ya una solvencia emitida. Un atleta 'inactive' por otro motivo SÍ puede recibirla;
     * lo que la bloquea es que ya exista una (withdrawn_at).
     *
     * @param int  $athleteId
     * @param bool $lockRow   Si es true usa SELECT ... FOR UPDATE (solo válido dentro de una transacción).
     * @return array ['eligible' => bool, 'reason' => 'ok'|'not_found'|'already_withdrawn'|'pending_payments',
     *                'athlete' => array|null, 'pending' => array, 'pending_total' => float, 'payments_count' => int]
     */
    public function checkClearanceEligibility($athleteId, $lockRow = false) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id" . ($lockRow ? ' FOR UPDATE' : ''));
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        $athlete = $stmt->fetch(PDO::FETCH_ASSOC);

        $result = [
            'eligible' => false, 'reason' => 'not_found', 'athlete' => null,
            'pending' => [], 'pending_total' => 0.0, 'payments_count' => 0,
        ];
        if (!$athlete) {
            return $result;
        }
        $result['athlete'] = $athlete;

        // Los pagos se leen con la misma conexión: dentro de una transacción ven el mismo estado consistente.
        $stmt = $this->db->prepare(
            "SELECT id, month, year, amount, due_date FROM payments
             WHERE athlete_id = :id AND status <> 'approved'
             ORDER BY year ASC, due_date ASC, id ASC"
        );
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        $result['pending'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result['pending'] as $p) {
            $result['pending_total'] += (float)$p['amount'];
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM payments WHERE athlete_id = :id");
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        $result['payments_count'] = (int)$stmt->fetchColumn();

        if (!empty($athlete['withdrawn_at'])) {
            $result['reason'] = 'already_withdrawn';
        } elseif (!empty($result['pending'])) {
            $result['reason'] = 'pending_payments';
        } else {
            $result['eligible'] = true;
            $result['reason'] = 'ok';
        }
        return $result;
    }

    /**
     * Emite la solvencia de retiro de forma ATÓMICA: o se hace todo o no se hace nada.
     *
     * Dentro de una transacción: bloquea la fila del atleta, VUELVE a comprobar la elegibilidad (un pago
     * pendiente registrado entre que el usuario abrió la pantalla y confirmó debe impedir la emisión),
     * genera el número correlativo del año y marca al atleta como inactivo con los datos del retiro.
     *
     * @param int    $athleteId
     * @param string $issuedBy Nombre de quien la emite (se guarda como texto).
     * @return array ['success' => bool, 'reason' => string, 'clearance_number' => string|null,
     *                'withdrawn_at' => string|null, 'eligibility' => array]
     */
    public function issueClearance($athleteId, $issuedBy) {
        $this->db->beginTransaction();
        try {
            $eligibility = $this->checkClearanceEligibility($athleteId, true);
            if (!$eligibility['eligible']) {
                $this->db->rollBack();
                return ['success' => false, 'reason' => $eligibility['reason'], 'clearance_number' => null,
                        'withdrawn_at' => null, 'eligibility' => $eligibility];
            }

            $now = date('Y-m-d H:i:s');
            $number = $this->nextClearanceNumber((int)date('Y'));

            $stmt = $this->db->prepare(
                "UPDATE {$this->table}
                 SET status = 'inactive', withdrawn_at = :withdrawn_at,
                     clearance_number = :number, clearance_issued_by = :issued_by,
                     status_reason = :reason, status_changed_at = :withdrawn_at2,
                     status_changed_by = NULL, status_changed_name = :issued_by2, status_changed_role = 'retiro'
                 WHERE id = :id AND withdrawn_at IS NULL"
            );
            // El motivo de esta inactividad ES la solvencia de retiro: queda registrado sin pedirselo al usuario.
            $stmt->bindValue(':reason', 'Retiro formal de la escuela (solvencia ' . $number . ').');
            $stmt->bindValue(':withdrawn_at2', $now);
            $stmt->bindValue(':issued_by2', mb_substr((string)$issuedBy, 0, 100));
            $stmt->bindValue(':withdrawn_at', $now);
            $stmt->bindValue(':number', $number);
            $stmt->bindValue(':issued_by', mb_substr((string)$issuedBy, 0, 100));
            $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return ['success' => false, 'reason' => 'already_withdrawn', 'clearance_number' => null,
                        'withdrawn_at' => null, 'eligibility' => $eligibility];
            }

            $this->db->commit();
            return ['success' => true, 'reason' => 'ok', 'clearance_number' => $number,
                    'withdrawn_at' => $now, 'eligibility' => $eligibility];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    // ------------------------------------------------------------------
    // Detalle del estatus (por que un atleta esta inactivo o suspendido)
    // ------------------------------------------------------------------

    public const STATUS_LABELS = ['active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido'];
    public const STATUS_REASON_MIN = 5;
    public const STATUS_REASON_MAX = 500;

    /**
     * Cambia el estatus de un atleta y deja registrado el detalle EN LA MISMA SENTENCIA, de modo que no puede existir
     * un atleta inactivo/suspendido recien cambiado sin su motivo, ni al reves.
     *
     *  - Pasar a 'inactive' o 'suspended' EXIGE un motivo de STATUS_REASON_MIN a STATUS_REASON_MAX caracteres; se guarda
     *    junto con cuando y quien (nombre y rol copiados, para que sigan legibles aunque la cuenta cambie).
     *  - Pasar a 'active' limpia el detalle: un atleta activo no tiene motivo de inactividad.
     *  - Si el estatus NO cambia no se toca nada (no se pisa un detalle anterior ni se pide un motivo).
     *
     * Los demas datos del atleta se actualizan con updateAthlete(); esto solo toca los campos de estatus.
     *
     * @param int    $athleteId
     * @param string $newStatus  active|inactive|suspended
     * @param string $reason     Obligatorio para inactive/suspended
     * @param array  $actor      ['id'=>int|null,'name'=>string,'role'=>string]
     * @return array ['ok'=>bool, 'code'=>?string, 'error'=>?string, 'changed'=>bool]
     */
    public function changeStatus($athleteId, $newStatus, $reason, array $actor) {
        if (!isset(self::STATUS_LABELS[$newStatus])) {
            return ['ok' => false, 'code' => 'invalid_status', 'error' => 'Estatus no valido.', 'changed' => false];
        }
        $reason = trim((string)$reason);
        $needsReason = $newStatus !== 'active';

        // OJO al orden: el motivo solo se exige si el estatus REALMENTE cambia. Se decide mas abajo, con el estatus que hay en la
        // base de datos (fila bloqueada), no antes: editar a un atleta que ya esta inactivo, sin cambiar su estatus, no debe
        // obligar a inventar un motivo.

        // PDO no admite transacciones anidadas: si quien nos llama ya abrio una, se usa esa y NO se confirma ni revierte aqui
        // (confirmar o revertir una transaccion ajena podria dejar datos a medias o cerrar la del llamador).
        $ownTx = !$this->db->inTransaction();
        if ($ownTx) {
            $this->db->beginTransaction();
        }
        try {
            $stmt = $this->db->prepare("SELECT status FROM {$this->table} WHERE id = :id FOR UPDATE");
            $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
            $stmt->execute();
            $current = $stmt->fetchColumn();
            if ($current === false) {
                if ($ownTx) { $this->db->rollBack(); }
                return ['ok' => false, 'code' => 'not_found', 'error' => 'El atleta no existe.', 'changed' => false];
            }
            if ($current === $newStatus) {
                if ($ownTx) { $this->db->rollBack(); }   // sin cambio: no se pide motivo ni se pisa el detalle existente
                return ['ok' => true, 'code' => null, 'error' => null, 'changed' => false];
            }

            // Hay cambio real: ahora si, el motivo es obligatorio para inactive/suspended.
            if ($needsReason) {
                $len = mb_strlen($reason);
                if ($len < self::STATUS_REASON_MIN) {
                    if ($ownTx) { $this->db->rollBack(); }
                    return ['ok' => false, 'code' => 'reason_required', 'changed' => false,
                            'error' => 'Debes escribir el motivo (minimo ' . self::STATUS_REASON_MIN . ' caracteres) para dejar al atleta como ' . mb_strtolower(self::STATUS_LABELS[$newStatus]) . '.'];
                }
                if ($len > self::STATUS_REASON_MAX) {
                    if ($ownTx) { $this->db->rollBack(); }
                    return ['ok' => false, 'code' => 'reason_too_long', 'changed' => false,
                            'error' => 'El motivo no puede superar ' . self::STATUS_REASON_MAX . ' caracteres.'];
                }
            }

            if ($needsReason) {
                $upd = $this->db->prepare(
                    "UPDATE {$this->table} SET status = :s, status_reason = :r, status_changed_at = :at,
                            status_changed_by = :by, status_changed_name = :nm, status_changed_role = :rl
                     WHERE id = :id"
                );
                $upd->bindValue(':r', $reason);
                $upd->bindValue(':at', date('Y-m-d H:i:s'));
                $upd->bindValue(':by', isset($actor['id']) ? (int)$actor['id'] : null, isset($actor['id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $upd->bindValue(':nm', mb_substr((string)($actor['name'] ?? ''), 0, 100));
                $upd->bindValue(':rl', mb_substr((string)($actor['role'] ?? ''), 0, 20));
            } else {
                $upd = $this->db->prepare(
                    "UPDATE {$this->table} SET status = :s, status_reason = NULL, status_changed_at = NULL,
                            status_changed_by = NULL, status_changed_name = NULL, status_changed_role = NULL
                     WHERE id = :id"
                );
            }
            $upd->bindValue(':s', $newStatus);
            $upd->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
            $upd->execute();
            if ($ownTx) { $this->db->commit(); }
            return ['ok' => true, 'code' => null, 'error' => null, 'changed' => true];
        } catch (\Throwable $e) {
            if ($ownTx && $this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('[Athlete::changeStatus] ' . $e->getMessage());
            return ['ok' => false, 'code' => 'server_error', 'error' => 'No se pudo cambiar el estatus. Intentalo de nuevo.', 'changed' => false];
        }
    }

    /**
     * Arma lo que un ROL puede ver sobre el estatus de un atleta, y nada mas. Es la UNICA fuente de esta regla: el
     * controlador la usa para decidir que viaja a la vista, de modo que el motivo (sensible: puede ser disciplinario o
     * de deuda) no llega al HTML de quien no debe verlo, en vez de ocultarlo con un `if` en la plantilla.
     *
     *   administrador / secretario : estatus, desde cuando, motivo y quien lo registro.
     *   entrenador / representante : estatus y desde cuando. SIN motivo y SIN autor.
     *
     * Un atleta activo devuelve null (no hay nada que detallar). Si el estatus es inactivo/suspendido pero nunca se
     * registro el detalle (casos anteriores a este registro), 'recorded' es false: se muestra "no registrado", sin inventar.
     *
     * @return array|null
     */
    public static function statusDetailFor(array $athlete, $role) {
        $status = $athlete['status'] ?? 'active';
        if ($status === 'active') {
            return null;
        }
        $full = in_array($role, ['administrador', 'secretario'], true);
        $since = $athlete['status_changed_at'] ?? null;
        $detail = [
            'status'   => $status,
            'label'    => self::STATUS_LABELS[$status] ?? ucfirst($status),
            'since'    => $since,                                  // null = fecha no registrada
            'recorded' => !empty($athlete['status_reason']) || !empty($since),
            'full'     => $full,
            'reason'   => null,
            'by_name'  => null,
            'by_role'  => null,
            'withdrawn_at' => $athlete['withdrawn_at'] ?? null,
        ];
        if ($full) {
            $detail['reason']  = $athlete['status_reason'] ?? null;
            $detail['by_name'] = $athlete['status_changed_name'] ?? null;
            $detail['by_role'] = $athlete['status_changed_role'] ?? null;
        }
        return $detail;
    }

    /**
     * Quita del arreglo de un atleta los campos de detalle que el rol no debe ver. Se aplica ANTES de pasarlo a la vista.
     */
    public static function stripStatusDetailFor(array $athlete, $role) {
        if (in_array($role, ['administrador', 'secretario'], true)) {
            return $athlete;
        }
        foreach (['status_reason', 'status_changed_by', 'status_changed_name', 'status_changed_role'] as $k) {
            unset($athlete[$k]);
        }
        return $athlete;
    }

    /**
     * Actualiza los datos de un atleta Y su estatus en UNA sola transaccion: o se aplican los dos o ninguno. Sin esto, un
     * fallo entre ambos pasos dejaria al atleta con datos nuevos y estatus viejo (o al reves).
     *
     * @return array ['success' => bool, 'message' => ?string]
     */
    public function updateAthleteWithStatus($id, array $data, $newStatus, $reason, array $actor) {
        $ownTx = !$this->db->inTransaction();
        if ($ownTx) {
            $this->db->beginTransaction();
        }
        try {
            $u = $this->updateAthlete($id, $data);
            if (empty($u['success'])) {
                if ($ownTx) { $this->db->rollBack(); }
                return ['success' => false, 'message' => 'No se pudo actualizar el atleta.'];
            }
            $st = $this->changeStatus($id, $newStatus, $reason, $actor);
            if (!$st['ok']) {
                if ($ownTx) { $this->db->rollBack(); }
                return ['success' => false, 'message' => $st['error']];
            }
            if ($ownTx) { $this->db->commit(); }
            return ['success' => true, 'message' => null];
        } catch (\Throwable $e) {
            if ($ownTx && $this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('[Athlete::updateAthleteWithStatus] ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo guardar el atleta. Intentalo de nuevo.'];
        }
    }

    /**
     * Registra el detalle de un atleta que se CREA ya inactivo o suspendido. changeStatus() no sirve aqui porque el
     * atleta ya nace con ese estatus (no hay cambio). Misma regla: motivo obligatorio.
     */
    public function recordInitialStatus($athleteId, $status, $reason, array $actor) {
        if ($status === 'active' || !isset(self::STATUS_LABELS[$status])) {
            return ['ok' => true];
        }
        $reason = trim((string)$reason);
        if (mb_strlen($reason) < self::STATUS_REASON_MIN || mb_strlen($reason) > self::STATUS_REASON_MAX) {
            return ['ok' => false, 'error' => 'Motivo invalido.'];
        }
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET status_reason = :r, status_changed_at = :at, status_changed_by = :by,
                    status_changed_name = :nm, status_changed_role = :rl WHERE id = :id AND status = :s"
        );
        $stmt->bindValue(':r', $reason);
        $stmt->bindValue(':at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':by', isset($actor['id']) ? (int)$actor['id'] : null, isset($actor['id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':nm', mb_substr((string)($actor['name'] ?? ''), 0, 100));
        $stmt->bindValue(':rl', mb_substr((string)($actor['role'] ?? ''), 0, 20));
        $stmt->bindValue(':s', $status);
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        return ['ok' => true];
    }

    /**
     * Calcula el siguiente número de solvencia del año: SOL-AAAA-0001, SOL-AAAA-0002, ...
     *
     * El contador vive en la tabla clearance_counters y NUNCA disminuye. No se calcula como
     * "máximo + 1" sobre los números vigentes de athletes porque, al reactivar a un atleta, su
     * número se borra y se volvería a asignar a otro: habría dos documentos con el mismo número
     * (el papel ya impreso y el nuevo).
     *
     * El incremento es atómico: INSERT ... ON DUPLICATE KEY UPDATE bloquea la fila del año hasta el
     * final de la transacción de issueClearance(), de modo que dos emisiones simultáneas nunca
     * obtienen el mismo número. LAST_INSERT_ID(expr) devuelve el valor ya incrementado.
     *
     * Debe llamarse dentro de la transacción de issueClearance().
     *
     * @param int $year
     * @return string
     */
    private function nextClearanceNumber($year) {
        $stmt = $this->db->prepare(
            "INSERT INTO clearance_counters (year, last_number) VALUES (:year, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)"
        );
        $stmt->bindValue(':year', (int)$year, PDO::PARAM_INT);
        $stmt->execute();
        $number = (int)$this->db->lastInsertId();
        return sprintf('SOL-%04d-', $year) . str_pad((string)$number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Borra los datos de retiro de un atleta (se usa al reactivarlo, para no dejar un atleta activo
     * con una solvencia vigente).
     *
     * @param int $athleteId
     * @return bool
     */
    public function clearWithdrawal($athleteId) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET withdrawn_at = NULL, clearance_number = NULL, clearance_issued_by = NULL
             WHERE id = :id"
        );
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}