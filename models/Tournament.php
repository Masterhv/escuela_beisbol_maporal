<?php
require_once 'models/Model.php';

/**
 * Modelo Tournament para la gestión de torneos.
 */
class Tournament extends Model {

    protected $table = 'tournaments';

    /**
     * Obtiene torneos con filtros opcionales.
     */
    public function getTournamentsWithFilters($filters = []) {
        $sql = "SELECT t.*, a.full_name AS creator_name
                FROM {$this->table} t
                LEFT JOIN admins a ON t.created_by = a.id";

        $conditions = [];
        $params     = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(t.name LIKE :q OR t.location LIKE :q2 OR t.category LIKE :q3)";
            $params[':q']  = "%{$filters['q']}%";
            $params[':q2'] = "%{$filters['q']}%";
            $params[':q3'] = "%{$filters['q']}%";
        }

        if (!empty($filters['status'])) {
            $conditions[] = "t.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $conditions[] = "t.category = :category";
            $params[':category'] = $filters['category'];
        }

        if (!empty($filters['year'])) {
            $conditions[] = "YEAR(t.start_date) = :year";
            $params[':year'] = (int)$filters['year'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY t.start_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un torneo por ID con sus participantes.
     */
    public function getTournamentWithParticipants($id) {
        $sql = "SELECT t.*, a.full_name AS creator_name
                FROM {$this->table} t
                LEFT JOIN admins a ON t.created_by = a.id
                WHERE t.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tournament) {
            $tournament['participants'] = $this->getParticipants($id);
        }

        return $tournament;
    }

    /**
     * Obtiene los participantes de un torneo.
     */
    public function getParticipants($tournamentId) {
        $sql = "SELECT tp.*, at.first_name, at.last_name, at.category, at.age, at.photo
                FROM tournament_participants tp
                JOIN athletes at ON tp.athlete_id = at.id
                WHERE tp.tournament_id = :tid
                ORDER BY at.last_name, at.first_name";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tid', $tournamentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Agrega un atleta como participante.
     */
    public function addParticipant($tournamentId, $athleteId, $notes = '') {
        $sql = "INSERT IGNORE INTO tournament_participants (tournament_id, athlete_id, notes)
                VALUES (:tid, :aid, :notes)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tid',   $tournamentId, PDO::PARAM_INT);
        $stmt->bindParam(':aid',   $athleteId,    PDO::PARAM_INT);
        $stmt->bindParam(':notes', $notes);
        return $stmt->execute();
    }

    /**
     * Elimina un participante del torneo.
     */
    public function removeParticipant($tournamentId, $athleteId) {
        $sql = "DELETE FROM tournament_participants
                WHERE tournament_id = :tid AND athlete_id = :aid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tid', $tournamentId, PDO::PARAM_INT);
        $stmt->bindParam(':aid', $athleteId,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Actualiza posición/resultado de un participante.
     */
    public function updateParticipant($tournamentId, $athleteId, $data) {
        $sql = "UPDATE tournament_participants
                SET position = :position, result = :result, notes = :notes
                WHERE tournament_id = :tid AND athlete_id = :aid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':result',   $data['result']);
        $stmt->bindParam(':notes',    $data['notes']);
        $stmt->bindParam(':tid',      $tournamentId, PDO::PARAM_INT);
        $stmt->bindParam(':aid',      $athleteId,    PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Estadísticas de torneos.
     */
    public function getStats() {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'programado'  THEN 1 ELSE 0 END) AS programados,
                    SUM(CASE WHEN status = 'en_curso'    THEN 1 ELSE 0 END) AS en_curso,
                    SUM(CASE WHEN status = 'finalizado'  THEN 1 ELSE 0 END) AS finalizados,
                    SUM(CASE WHEN status = 'cancelado'   THEN 1 ELSE 0 END) AS cancelados
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza automáticamente el estatus según fechas.
     */
    public function updateStatusByDate() {
        // Pasar a "en_curso" si ya empezó y no ha terminado
        $sql1 = "UPDATE {$this->table}
                 SET status = 'en_curso'
                 WHERE status = 'programado'
                   AND start_date <= CURDATE()
                   AND (end_date IS NULL OR end_date >= CURDATE())";
        // Pasar a "finalizado" si ya venció la fecha de fin
        $sql2 = "UPDATE {$this->table}
                 SET status = 'finalizado'
                 WHERE status IN ('programado','en_curso')
                   AND end_date IS NOT NULL
                   AND end_date < CURDATE()";
        $this->db->prepare($sql1)->execute();
        $this->db->prepare($sql2)->execute();
    }

    /**
     * Verifica si un atleta ya está inscrito.
     */
    public function isParticipant($tournamentId, $athleteId) {
        $sql = "SELECT id FROM tournament_participants
                WHERE tournament_id = :tid AND athlete_id = :aid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tid', $tournamentId, PDO::PARAM_INT);
        $stmt->bindParam(':aid', $athleteId,    PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetch();
    }

    /**
     * Cuenta los participantes de un torneo.
     */
    public function countParticipants($tournamentId) {
        $sql  = "SELECT COUNT(*) FROM tournament_participants WHERE tournament_id = :tid";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tid', $tournamentId, PDO::PARAM_INT);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}
