<?php
require_once 'models/Model.php';

/**
 * Modelo Inventory para gestionar el inventario de equipamiento
 * y materiales deportivos de la escuela.
 */
class Inventory extends Model {

    protected $table = 'inventory_items';

    // -----------------------------------------------------------------------
    // Ítems
    // -----------------------------------------------------------------------

    /**
     * Obtiene ítems con búsqueda por nombre y filtro opcional de stock bajo.
     */
    public function getItemsWithFilters($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['q'])) {
            $sql .= " AND name LIKE :q";
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (isset($filters['low_stock']) && $filters['low_stock'] === '1') {
            $sql .= " AND quantity <= min_quantity AND min_quantity > 0";
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Estadísticas globales del inventario.
     */
    public function getStats() {
        $sql = "SELECT
                    COUNT(*) AS total_items,
                    SUM(quantity) AS total_units,
                    SUM(CASE WHEN quantity <= min_quantity AND min_quantity > 0 THEN 1 ELSE 0 END) AS low_stock_items
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // -----------------------------------------------------------------------
    // Movimientos
    // -----------------------------------------------------------------------

    /**
     * Obtiene todos los movimientos de un ítem, ordenados del más reciente al más antiguo.
     */
    public function getMovements($itemId) {
        $sql = "SELECT * FROM inventory_movements
                WHERE item_id = :item_id
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registra un movimiento de inventario y actualiza la cantidad del ítem.
     *
     * @param int    $itemId    ID del ítem
     * @param string $type      'entrada' | 'salida' | 'ajuste'
     * @param int    $quantity  Cantidad del movimiento (siempre positivo)
     * @param string $reason    Motivo del movimiento
     * @param int    $adminId   ID del usuario que registra
     * @param string $adminName Nombre del usuario que registra
     * @return array ['success' => bool, 'message' => string, 'new_quantity' => int]
     */
    public function registerMovement($itemId, $type, $quantity, $reason, $adminId, $adminName) {
        $item = $this->findById($itemId);
        if (!$item) {
            return ['success' => false, 'message' => 'Ítem no encontrado.'];
        }

        $before = (int)$item['quantity'];

        if ($type === 'entrada') {
            $after = $before + $quantity;
        } elseif ($type === 'salida') {
            if ($quantity > $before) {
                return ['success' => false, 'message' => 'Stock insuficiente. Disponible: ' . $before . '.'];
            }
            $after = $before - $quantity;
        } elseif ($type === 'ajuste') {
            // Para ajuste, quantity es el nuevo total
            $after = $quantity;
        } else {
            return ['success' => false, 'message' => 'Tipo de movimiento inválido.'];
        }

        // Registrar movimiento
        $sql = "INSERT INTO inventory_movements
                    (item_id, type, quantity, quantity_before, quantity_after, reason, registered_by, registered_by_name)
                VALUES
                    (:item_id, :type, :qty, :before, :after, :reason, :by, :by_name)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':item_id',  $itemId, PDO::PARAM_INT);
        $stmt->bindValue(':type',     $type);
        $stmt->bindValue(':qty',      $type === 'ajuste' ? abs($after - $before) : $quantity, PDO::PARAM_INT);
        $stmt->bindValue(':before',   $before, PDO::PARAM_INT);
        $stmt->bindValue(':after',    $after,  PDO::PARAM_INT);
        $stmt->bindValue(':reason',   $reason);
        $stmt->bindValue(':by',       $adminId, PDO::PARAM_INT);
        $stmt->bindValue(':by_name',  $adminName);
        $stmt->execute();

        // Actualizar cantidad en el ítem
        $this->update($itemId, ['quantity' => $after, 'updated_at' => date('Y-m-d H:i:s')]);

        return ['success' => true, 'new_quantity' => $after];
    }
}
