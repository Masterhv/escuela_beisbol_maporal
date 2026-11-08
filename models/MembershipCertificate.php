<?php
require_once 'models/Model.php';

/**
 * Modelo de las constancias de pertenencia a la escuela.
 *
 * Una constancia certifica que un atleta forma parte de la escuela. Por eso:
 *  - Solo se emite a atletas ACTIVOS (y no retirados). La regla vive aquí, dentro de la transacción de emisión,
 *    no solo en la vista o el controlador: ocultar un botón nunca basta.
 *  - Cada constancia lleva un número correlativo (CON-AAAA-NNNN) y una vigencia de 30 días.
 *  - Queda registrada (quién, cuándo, a qué correo), lo que permite verificarla y saber cuántas se entregaron.
 */
class MembershipCertificate extends Model {
    /** @var string */
    protected $table = 'membership_certificates';

    /** Días de vigencia de una constancia desde su emisión. */
    public const VALIDITY_DAYS = 30;

    /**
     * Comprueba si a un atleta se le puede emitir una constancia.
     *
     * Elegible solo si existe, está 'active' y no tiene una solvencia de retiro emitida (withdrawn_at).
     *
     * @param int  $athleteId
     * @param bool $lockRow   Si es true usa SELECT ... FOR UPDATE (solo válido dentro de una transacción).
     * @return array ['eligible' => bool, 'reason' => 'ok'|'not_found'|'inactive'|'suspended'|'withdrawn', 'athlete' => array|null]
     */
    public function checkEligibility($athleteId, $lockRow = false) {
        $stmt = $this->db->prepare("SELECT * FROM athletes WHERE id = :id" . ($lockRow ? ' FOR UPDATE' : ''));
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        $athlete = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$athlete) {
            return ['eligible' => false, 'reason' => 'not_found', 'athlete' => null];
        }

        if (!empty($athlete['withdrawn_at'])) {
            return ['eligible' => false, 'reason' => 'withdrawn', 'athlete' => $athlete];
        }
        if ($athlete['status'] === 'inactive') {
            return ['eligible' => false, 'reason' => 'inactive', 'athlete' => $athlete];
        }
        if ($athlete['status'] === 'suspended') {
            return ['eligible' => false, 'reason' => 'suspended', 'athlete' => $athlete];
        }
        if ($athlete['status'] !== 'active') {
            // Cualquier otro valor (no debería existir según el ENUM) se trata como no elegible.
            return ['eligible' => false, 'reason' => 'inactive', 'athlete' => $athlete];
        }

        return ['eligible' => true, 'reason' => 'ok', 'athlete' => $athlete];
    }

    /**
     * Devuelve la constancia VIGENTE más reciente del atleta (valid_until >= hoy), o null.
     *
     * @param int $athleteId
     * @return array|null
     */
    public function findLatestValid($athleteId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table}
             WHERE athlete_id = :id AND valid_until >= CURDATE()
             ORDER BY issued_at DESC, id DESC LIMIT 1"
        );
        $stmt->bindValue(':id', (int)$athleteId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Obtiene o emite la constancia de un atleta, de forma ATÓMICA.
     *
     * Dentro de una transacción: bloquea la fila del atleta, VUELVE a comprobar la elegibilidad (el atleta
     * pudo desactivarse entre que el usuario abrió la pantalla y confirmó) y, si ya existe una constancia
     * vigente, la reutiliza en lugar de emitir otra. Si no, genera el número correlativo e inserta la fila.
     *
     * @param int    $athleteId
     * @param string $issuedBy Nombre de quien la emite (se guarda como texto).
     * @return array ['success' => bool, 'reason' => string, 'reused' => bool, 'certificate' => array|null,
     *                'eligibility' => array]
     */
    public function issue($athleteId, $issuedBy) {
        $this->db->beginTransaction();
        try {
            $eligibility = $this->checkEligibility($athleteId, true);
            if (!$eligibility['eligible']) {
                $this->db->rollBack();
                return ['success' => false, 'reason' => $eligibility['reason'], 'reused' => false,
                        'certificate' => null, 'eligibility' => $eligibility];
            }

            // Con la fila del atleta bloqueada, dos emisiones simultáneas del MISMO atleta se serializan:
            // la segunda encontrará aquí la que acaba de crear la primera y la reutilizará.
            $existing = $this->findLatestValid($athleteId);
            if ($existing) {
                $this->db->commit();
                return ['success' => true, 'reason' => 'ok', 'reused' => true,
                        'certificate' => $existing, 'eligibility' => $eligibility];
            }

            $now = new DateTime();
            $validUntil = (clone $now)->modify('+' . self::VALIDITY_DAYS . ' days');
            $number = $this->nextCertificateNumber((int)$now->format('Y'));

            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (certificate_number, athlete_id, issued_at, valid_until, issued_by)
                 VALUES (:number, :athlete_id, :issued_at, :valid_until, :issued_by)"
            );
            $stmt->bindValue(':number', $number);
            $stmt->bindValue(':athlete_id', (int)$athleteId, PDO::PARAM_INT);
            $stmt->bindValue(':issued_at', $now->format('Y-m-d H:i:s'));
            $stmt->bindValue(':valid_until', $validUntil->format('Y-m-d'));
            $stmt->bindValue(':issued_by', mb_substr((string)$issuedBy, 0, 100));
            $stmt->execute();
            $id = (int)$this->db->lastInsertId();

            $certificate = $this->findById($id);
            $this->db->commit();

            return ['success' => true, 'reason' => 'ok', 'reused' => false,
                    'certificate' => $certificate, 'eligibility' => $eligibility];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Siguiente número de constancia del año: CON-AAAA-0001, CON-AAAA-0002, ...
     *
     * El contador vive en certificate_counters y NUNCA disminuye (no se calcula como "máximo + 1" sobre las
     * constancias existentes). El incremento es atómico: INSERT ... ON DUPLICATE KEY UPDATE bloquea la fila del
     * año hasta el final de la transacción, de modo que dos emisiones simultáneas no obtienen el mismo número.
     * Debe llamarse dentro de la transacción de issue().
     *
     * @param int $year
     * @return string
     */
    private function nextCertificateNumber($year) {
        $stmt = $this->db->prepare(
            "INSERT INTO certificate_counters (year, last_number) VALUES (:year, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)"
        );
        $stmt->bindValue(':year', (int)$year, PDO::PARAM_INT);
        $stmt->execute();
        $number = (int)$this->db->lastInsertId();
        return sprintf('CON-%04d-', $year) . str_pad((string)$number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Registra el resultado del envío por correo de una constancia.
     *
     * @param int         $id
     * @param string      $status 'sent' o 'failed'
     * @param string|null $to     Correo al que se envió
     * @param string|null $error  Detalle del error si falló
     * @return bool
     */
    public function markEmailResult($id, $status, $to = null, $error = null) {
        $status = ($status === 'sent') ? 'sent' : 'failed';
        $stmt = $this->db->prepare(
            "UPDATE {$this->table}
             SET email_status = :status, email_sent_at = :sent_at, email_sent_to = :to, email_error = :error
             WHERE id = :id"
        );
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':sent_at', $status === 'sent' ? date('Y-m-d H:i:s') : null);
        $stmt->bindValue(':to', $to !== null ? mb_substr($to, 0, 100) : null);
        $stmt->bindValue(':error', $status === 'sent' ? null : $error);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Busca una constancia por su número (ej. CON-2026-0001).
     *
     * @param string $number
     * @return array|null
     */
    public function findByNumber($number) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE certificate_number = :n LIMIT 1");
        $stmt->bindValue(':n', (string)$number);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
