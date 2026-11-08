<?php
require_once 'models/Model.php';

/**
 * Modelo TrainingSchedule para gestionar cronogramas de entrenamiento.
 */
class TrainingSchedule extends Model {

    protected $table = 'training_schedules';

    // -------------------------------------------------------------------------
    // Cronogramas
    // -------------------------------------------------------------------------

    /**
     * Obtiene todos los cronogramas con filtros opcionales.
     */
    public function getSchedulesWithFilters($filters = []) {
        $sql = "SELECT ts.*, a.full_name AS coach_full_name
                FROM {$this->table} ts
                LEFT JOIN admins a ON ts.coach_id = a.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND ts.category = :category";
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND ts.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['coach_id'])) {
            $sql .= " AND ts.coach_id = :coach_id";
            $params[':coach_id'] = $filters['coach_id'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (ts.title LIKE :q OR ts.location LIKE :q OR a.full_name LIKE :q)";
            $params[':q'] = "%{$filters['q']}%";
        }

        $sql .= " ORDER BY ts.start_date DESC, ts.title ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un cronograma con sus sesiones.
     */
    public function getScheduleWithSessions($id) {
        $sql = "SELECT ts.*, a.full_name AS coach_full_name
                FROM {$this->table} ts
                LEFT JOIN admins a ON ts.coach_id = a.id
                WHERE ts.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $schedule = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($schedule) {
            $schedule['sessions'] = $this->getSessionsBySchedule($id);
        }
        return $schedule;
    }

    /**
     * Crea un cronograma.
     */
    public function createSchedule($data) {
        return $this->create($data);
    }

    /**
     * Actualiza un cronograma.
     */
    public function updateSchedule($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * Estadísticas generales de cronogramas.
     */
    public function getStats() {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'activo'    THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN status = 'pausado'   THEN 1 ELSE 0 END) AS pausados,
                    SUM(CASE WHEN status = 'finalizado' THEN 1 ELSE 0 END) AS finalizados
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // -------------------------------------------------------------------------
    // Sesiones de entrenamiento
    // -------------------------------------------------------------------------

    /**
     * Obtiene todas las sesiones de un cronograma ordenadas por día y hora.
     */
    public function getSessionsBySchedule($scheduleId) {
        $dayOrder = "FIELD(day_of_week,'lunes','martes','miercoles','jueves','viernes','sabado','domingo')";
        $sql = "SELECT * FROM training_sessions
                WHERE schedule_id = :schedule_id
                ORDER BY {$dayOrder}, start_time ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una sesión por ID.
     */
    public function getSessionById($id) {
        $sql = "SELECT * FROM training_sessions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea una sesión de entrenamiento.
     */
    public function createSession($data) {
        $sql = "INSERT INTO training_sessions
                    (schedule_id, day_of_week, start_time, end_time, activity, intensity, materials)
                VALUES
                    (:schedule_id, :day_of_week, :start_time, :end_time, :activity, :intensity, :materials)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':schedule_id', $data['schedule_id'], PDO::PARAM_INT);
        $stmt->bindValue(':day_of_week', $data['day_of_week']);
        $stmt->bindValue(':start_time',  $data['start_time']);
        $stmt->bindValue(':end_time',    $data['end_time']);
        $stmt->bindValue(':activity',    $data['activity']);
        $stmt->bindValue(':intensity',   $data['intensity']);
        $stmt->bindValue(':materials',   $data['materials'] ?? null);
        $stmt->execute();
        return $this->db->lastInsertId();
    }

    /**
     * Actualiza una sesión de entrenamiento.
     */
    public function updateSession($id, $data) {
        $sql = "UPDATE training_sessions
                SET day_of_week = :day_of_week,
                    start_time  = :start_time,
                    end_time    = :end_time,
                    activity    = :activity,
                    intensity   = :intensity,
                    materials   = :materials
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':day_of_week', $data['day_of_week']);
        $stmt->bindValue(':start_time',  $data['start_time']);
        $stmt->bindValue(':end_time',    $data['end_time']);
        $stmt->bindValue(':activity',    $data['activity']);
        $stmt->bindValue(':intensity',   $data['intensity']);
        $stmt->bindValue(':materials',   $data['materials'] ?? null);
        $stmt->bindValue(':id',          $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Elimina una sesión.
     */
    public function deleteSession($id) {
        $sql = "DELETE FROM training_sessions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Elimina todas las sesiones de un cronograma.
     */
    public function deleteSessionsBySchedule($scheduleId) {
        $sql = "DELETE FROM training_sessions WHERE schedule_id = :schedule_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':schedule_id', $scheduleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Obtiene los entrenadores disponibles (rol = entrenador, status = active).
     */
    public function getCoaches() {
        $sql = "SELECT id, full_name FROM admins
                WHERE role = 'entrenador' AND status = 'active'
                ORDER BY full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si hay solapamiento de horario para el mismo día en el mismo cronograma.
     */
    public function hasTimeConflict($scheduleId, $dayOfWeek, $startTime, $endTime, $excludeSessionId = null) {
        $sql = "SELECT COUNT(*) FROM training_sessions
                WHERE schedule_id = :schedule_id
                  AND day_of_week = :day
                  AND (
                        (start_time < :end_time AND end_time > :start_time)
                      )";
        if ($excludeSessionId) {
            $sql .= " AND id != :exclude_id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':schedule_id', $scheduleId, PDO::PARAM_INT);
        $stmt->bindValue(':day',         $dayOfWeek);
        $stmt->bindValue(':start_time',  $startTime);
        $stmt->bindValue(':end_time',    $endTime);
        if ($excludeSessionId) {
            $stmt->bindValue(':exclude_id', $excludeSessionId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }
}
