<?php
require_once 'models/Model.php';
require_once __DIR__ . '/PaymentWorkflow.php';

/**
 * Modelo Payment para gestionar los pagos de los atletas.
 * Hereda de la clase Model y define la lógica de negocio para los pagos.
 */
class Payment extends Model {
    /**
     * @var string La tabla de la base de datos para los pagos.
     */
    protected $table = 'payments';
    
    /**
     * Obtiene estadísticas de los pagos.
     *
     * @param array $filters Filtros para acotar las estadísticas (ej. por fecha).
     * @return array Un array con estadísticas como total de pagos, pagados, pendientes e ingresos totales.
     */
    public function getPaymentStats($filters = []) {
        $sql = "SELECT
                    COUNT(*) as total_payments,
                    COUNT(DISTINCT p.athlete_id) as total_athletes,
                    SUM(CASE WHEN p.status = 'approved' THEN 1 ELSE 0 END) as approved_payments,
                    SUM(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) as pending_payments,
                    SUM(CASE WHEN p.status = 'in_review' THEN 1 ELSE 0 END) as in_review_payments,
                    SUM(CASE WHEN p.status = 'rejected' THEN 1 ELSE 0 END) as rejected_payments,
                    SUM(CASE WHEN p.status = 'approved' THEN p.amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN p.status <> 'approved' THEN p.amount ELSE 0 END) as total_outstanding
                FROM {$this->table} p
                JOIN athletes s ON p.athlete_id = s.id";
        
        $conditions = [];
        $params = [];

        // Aplicar exactamente los mismos filtros que getPaymentsWithAthletes()
        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['athlete_name'])) {
            $conditions[] = "CONCAT(s.first_name, ' ', s.last_name) LIKE :athlete_name";
            $params[':athlete_name'] = "%{$filters['athlete_name']}%";
        }

        if (!empty($filters['athlete_search'])) {
            $conditions[] = "(s.id_card LIKE :athlete_search OR s.parent_id_card LIKE :athlete_search)";
            $params[':athlete_search'] = "%{$filters['athlete_search']}%";
        }
        
        if (!empty($filters['month'])) {
            $conditions[] = "p.month = :month";
            $params[':month'] = $filters['month'];
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "p.due_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = "p.due_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['year'])) {
            $conditions[] = "p.year = :year";
            $params[':year'] = $filters['year'];
        }

        if (!empty($filters['category'])) {
            $conditions[] = "s.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['age'])) {
            $conditions[] = "s.age = :age";
            $params[':age'] = $filters['age'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene una lista de pagos junto con la información del atleta asociado.
     *
     * Este método NO maneja la búsqueda por cédula; solo usa filtros normales
     * como estado, mes, año, categoría, etc.
     *
     * @param array $filters Filtros para la búsqueda (por estado, mes, año, categoría, edad, etc.).
     * @return array Una lista de pagos.
     */
    public function getPaymentsWithAthletes($filters = []) {
        $sql = "SELECT p.*, s.first_name, s.last_name, s.category, s.age
                FROM {$this->table} p
                JOIN athletes s ON p.athlete_id = s.id";
        
        $conditions = [];
        $params = [];

        if (!empty($filters['status'])) {
            $conditions[] = "p.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['athlete_name'])) {
            $conditions[] = "CONCAT(s.first_name, ' ', s.last_name) LIKE :athlete_name";
            $params[':athlete_name'] = "%{$filters['athlete_name']}%";
        }

        // Compatibilidad con el filtro previo por cédula explícita (athlete_search)
        if (!empty($filters['athlete_search'])) {
            $conditions[] = "(s.id_card LIKE :athlete_search OR s.parent_id_card LIKE :athlete_search)";
            $params[':athlete_search'] = "%{$filters['athlete_search']}%";
        }
        
        if (!empty($filters['month'])) {
            $conditions[] = "p.month = :month";
            $params[':month'] = $filters['month'];
        }

        if (!empty($filters['start_date'])) {
            $conditions[] = "p.due_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $conditions[] = "p.due_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['year'])) {
            $conditions[] = "p.year = :year";
            $params[':year'] = $filters['year'];
        }

        if (!empty($filters['category'])) {
            $conditions[] = "s.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['age'])) {
            $conditions[] = "s.age = :age";
            $params[':age'] = $filters['age'];
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY p.due_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Búsqueda de pagos por cédula del atleta o del representante.
     * Este método se usa cuando el usuario escribe en el campo "Búsqueda por cédula"
     * en la gestión de pagos, y debe comportarse igual que en Gestión de Atletas.
     *
     * @param string $term Número de cédula (atleta o representante).
     * @return array Lista de pagos filtrados por esa cédula.
     */
    public function getPaymentsByIdCardTerm($term) {
        $sql = "SELECT p.*, s.first_name, s.last_name, s.category, s.age
                FROM {$this->table} p
                JOIN athletes s ON p.athlete_id = s.id
                WHERE s.id_card LIKE :q1 OR s.parent_id_card LIKE :q2
                ORDER BY p.due_date DESC";

        $stmt = $this->db->prepare($sql);
        $search = "%{$term}%";
        $stmt->bindValue(':q1', $search);
        $stmt->bindValue(':q2', $search);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busqueda unificada del cuadro de Pagos: el termino se busca como CEDULA (atleta o representante) y, si son solo
     * digitos y al menos PaymentWorkflow::REFERENCE_MIN, tambien como REFERENCIA bancaria. Se combina con el resto de filtros
     * (estado, mes, año, categoria...), que antes se ignoraban cuando habia un termino.
     *
     * Cada fila trae 'matched_by' = 'reference' | 'id_card' | 'both' para que quien busca vea POR QUE salio (un numero puede
     * ser una cedula y una referencia a la vez), y las coincidencias exactas de referencia van primero.
     *
     * La busqueda por referencia es solo para quien concilia (administrador y secretario): $searchReferences = false la desactiva
     * y entonces SOLO se busca por cedula. El alcance por atletas se aplica en SQL ($onlyAthleteIds), no despues.
     *
     * @param string $term              Lo que escribio el usuario.
     * @param array  $filters           Resto de filtros de getPaymentsWithAthletes (status, month, year, category, age...).
     * @param bool   $searchReferences  true solo para administrador/secretario.
     * @param int[]|null $onlyAthleteIds Restringe a esos atletas (representante).
     */
    public function searchPayments($term, array $filters = [], $searchReferences = false, ?array $onlyAthleteIds = null) {
        if ($onlyAthleteIds !== null && empty($onlyAthleteIds)) {
            return [];
        }
        $term = trim((string)$term);
        $isDigits = $term !== '' && ctype_digit($term);
        $byRef = $searchReferences && $isDigits && strlen($term) >= PaymentWorkflow::REFERENCE_MIN;

        $where = []; $params = [];
        // Mismos filtros que la lista normal
        foreach (['status' => 'p.status = :status', 'month' => 'p.month = :month', 'year' => 'p.year = :year', 'category' => 's.category = :category', 'age' => 's.age = :age'] as $k => $cond) {
            if (!empty($filters[$k])) { $where[] = $cond; $params[":$k"] = $filters[$k]; }
        }
        if (!empty($filters['start_date'])) { $where[] = 'p.due_date >= :start_date'; $params[':start_date'] = $filters['start_date']; }
        if (!empty($filters['end_date']))   { $where[] = 'p.due_date <= :end_date';   $params[':end_date'] = $filters['end_date']; }
        if ($onlyAthleteIds !== null) { $where[] = 'p.athlete_id IN (' . implode(',', array_map('intval', $onlyAthleteIds)) . ')'; }

        // PDO (sin modo emulado) NO admite reutilizar un mismo :parametro varias veces en una sentencia: cada aparicion lleva el suyo.
        $idCardSel = "(s.id_card LIKE :idc1 ESCAPE '!' OR s.parent_id_card LIKE :idc2 ESCAPE '!')";   // en el SELECT (etiqueta "por que coincidio")
        $idCardWhere = "(s.id_card LIKE :idw1 ESCAPE '!' OR s.parent_id_card LIKE :idw2 ESCAPE '!')"; // en el WHERE (filtro)
        // El termino se busca como TEXTO literal: % y _ son comodines de LIKE y, sin escapar, un solo '%' en el cuadro devolveria todas
        // las filas. Se escapan (! % _) con '!' (un escape que NO es especial en cadenas SQL: con la barra invertida la consulta falla en MariaDB) y se declara ESCAPE '!' en cada LIKE.
        $like = '%' . preg_replace('/([!%_])/', '!$1', $term) . '%';
        if ($term === '') {
            $matchSelect = "0 AS ref_exact, 0 AS ref_part, 0 AS card_hit";
        } else {
            $params[':idc1'] = $params[':idc2'] = $like;
            if ($byRef) {
                $matchSelect = "CASE WHEN p.reference_number = :refx THEN 1 ELSE 0 END AS ref_exact, CASE WHEN p.reference_number LIKE :refp ESCAPE '!' THEN 1 ELSE 0 END AS ref_part, CASE WHEN $idCardSel THEN 1 ELSE 0 END AS card_hit";
                $params[':refx'] = $term; $params[':refp'] = $like;
                $where[] = "(p.reference_number LIKE :refw ESCAPE '!' OR $idCardWhere)";
                $params[':refw'] = $like; $params[':idw1'] = $params[':idw2'] = $like;
            } else {
                $matchSelect = "0 AS ref_exact, 0 AS ref_part, CASE WHEN $idCardSel THEN 1 ELSE 0 END AS card_hit";
                $where[] = $idCardWhere;
                $params[':idw1'] = $params[':idw2'] = $like;
            }
        }

        $sql = "SELECT p.*, s.first_name, s.last_name, s.category, s.age, c.name AS concept_name, $matchSelect
                FROM {$this->table} p
                JOIN athletes s ON p.athlete_id = s.id
                LEFT JOIN payment_concepts c ON c.id = p.concept_id"
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . " ORDER BY ref_exact DESC, ref_part DESC, p.due_date DESC, p.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $ref = (int)$r['ref_part'] === 1; $card = (int)$r['card_hit'] === 1;
            $r['matched_by'] = ($ref && $card) ? 'both' : ($ref ? 'reference' : ($card ? 'id_card' : null));
            $r['matched_exact'] = (int)$r['ref_exact'] === 1;
            unset($r['ref_exact'], $r['ref_part'], $r['card_hit']);
        }
        unset($r);
        return $rows;
    }

    /**
     * Obtiene todos los pagos de un atleta específico.
     *
     * @param int $athleteId El ID del atleta.
     * @return array Una lista de los pagos del atleta.
     */
    public function getPaymentsByAthleteId($athleteId) {
        $sql = "SELECT * FROM {$this->table} WHERE athlete_id = :athlete_id ORDER BY year DESC, month DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':athlete_id', $athleteId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtiene los pagos más recientes.
     *
     * @param int $limit El número de pagos a obtener.
     * @param int[]|null $onlyAthleteIds Si no es null, solo pagos de esos atletas (en SQL, antes del LIMIT).
     * @return array Una lista de los pagos más recientes.
     */
    public function getRecentPayments($limit = 5, ?array $onlyAthleteIds = null) {
        if ($onlyAthleteIds !== null && empty($onlyAthleteIds)) {
            return [];
        }
        $scopeSql = $onlyAthleteIds !== null ? 'WHERE p.athlete_id IN (' . implode(',', array_map('intval', $onlyAthleteIds)) . ')' : '';
        $sql = "SELECT p.*, s.first_name, s.last_name 
                FROM {$this->table} p 
                JOIN athletes s ON p.athlete_id = s.id 
                {$scopeSql}
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Indica si un pago está atrasado: sigue pendiente y su fecha de vencimiento ya pasó.
     *
     * "Atrasado" NO es un estado guardado (los estados son pending, in_review, approved y rejected): se calcula
     * al vuelo comparando la fecha de vencimiento con hoy, para que nunca dependa de que alguien
     * haya abierto una pantalla que lo actualice.
     *
     * @param array $payment Fila de pago con las claves 'status' y 'due_date'.
     * @return bool
     */
    public static function isPastDue(array $payment) {
        return in_array($payment['status'] ?? '', ['pending', 'rejected'], true)
            && !empty($payment['due_date'])
            && $payment['due_date'] < date('Y-m-d');
    }

    /**
     * Obtiene la deuda de un atleta (todo cobro NO aprobado: pendiente, en revisión o rechazado),
     * del más antiguo al más reciente, haya vencido su fecha o no.
     *
     * @param int $athleteId El ID del atleta.
     * @return array Lista de pagos pendientes (mes, año, monto, fecha de vencimiento...).
     */
    public function getPendingPaymentsByAthleteId($athleteId) {
        $sql = "SELECT id, month, year, amount, due_date
                FROM {$this->table}
                WHERE athlete_id = :athlete_id AND status <> 'approved'
                ORDER BY year ASC, due_date ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':athlete_id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta cuántos pagos (de cualquier estado) tiene registrados un atleta.
     *
     * @param int $athleteId El ID del atleta.
     * @return int
     */
    public function countPaymentsByAthleteId($athleteId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE athlete_id = :athlete_id");
        $stmt->bindValue(':athlete_id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Obtiene pagos por rango de fechas.
     * @deprecated Usar getPaymentsWithAthletes con filtros de fecha en su lugar.
     *
     * @param string $startDate La fecha de inicio.
     * @param string $endDate La fecha de fin.
     * @return array Una lista de pagos.
     */
    public function getPaymentsByDateRange($startDate, $endDate) {
        return $this->getPaymentsWithAthletes([
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Busca un pago por su ID y devuelve la información del atleta asociado.
     *
     * @param int $id El ID del pago.
     * @return array|false Los datos del pago y del atleta, o false si no se encuentra.
     */
    public function findPaymentWithAthleteById($id) {
        $sql = "SELECT
                    p.*,
                    s.first_name,
                    s.last_name,
                    s.category,
                    s.age,
                    s.parent_name,
                    s.parent_phone,
                    s.parent_email
                FROM {$this->table} p
                JOIN athletes s ON p.athlete_id = s.id
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los ingresos mensuales dentro de un rango de fechas.
     *
     * @param string $startDate La fecha de inicio.
     * @param string $endDate La fecha de fin.
     * @return array Un array con los ingresos agrupados por año y mes.
     */
    public function getMonthlyRevenue($startDate, $endDate) {
        $sql = "SELECT YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as revenue
                FROM {$this->table}
                WHERE status = 'approved' AND payment_date BETWEEN :start_date AND :end_date
                GROUP BY YEAR(payment_date), MONTH(payment_date)
                ORDER BY year, month";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}