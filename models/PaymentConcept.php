<?php
require_once 'models/Model.php';

/**
 * Catalogo de conceptos de cobro (Mensualidad, Inscripcion, Uniforme...) con su tarifa vigente.
 *
 * Cambiar la tarifa de un concepto NO altera pagos existentes: cada pago guarda su propio monto
 * en payments.amount al crearse. La tarifa del catalogo solo sugiere el monto de los cobros nuevos.
 * Los conceptos no se borran (los pagos los referencian): se desactivan.
 */
class PaymentConcept extends Model {

    protected $table = 'payment_concepts';

    /** Conceptos activos, para los formularios de cobro. */
    public function activeList() {
        return $this->db->query("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY is_monthly DESC, name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Todos los conceptos con cuantos pagos los usan, para la pantalla del administrador. */
    public function listWithUsage() {
        return $this->db->query(
            "SELECT c.*, (SELECT COUNT(*) FROM payments p WHERE p.concept_id = c.id) AS payments_count
             FROM {$this->table} c ORDER BY c.is_active DESC, c.is_monthly DESC, c.name ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function nameTaken($name, $excludeId = null) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE name = :n" . ($excludeId ? ' AND id <> :id' : ''));
        $stmt->bindValue(':n', $name);
        if ($excludeId) { $stmt->bindValue(':id', (int)$excludeId, PDO::PARAM_INT); }
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }
}
