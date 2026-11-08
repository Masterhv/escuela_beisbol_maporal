<?php
/**
 * Vista principal del Inventario Deportivo (Listado general).
 * 
 * Muestra el catálogo simplificado de materiales deportivos con:
 * - Buscador en tiempo real por nombre de ítem.
 * - Alerta visual unificada si existen ítems con stock bajo el mínimo.
 * - Tabla con nombre, cantidad disponible y botones de acción rápida (+ / -).
 * - Modal de confirmación para eliminar ítems (exclusivo para administradores).
 * 
 * Variables recibidas desde InventoryController::index():
 * @var array  $items    Lista de ítems de inventario con sus cantidades y alertas.
 * @var array  $stats    Totales de ítems, unidades y conteo de stock bajo.
 * @var array  $filters  Filtros aplicados (búsqueda 'q', 'low_stock').
 * @var string $success  Mensaje de éxito tras crear o actualizar un ítem.
 * @var string $error    Mensaje de error en caso de fallo o ítem no encontrado.
 */

// Permisos según el rol del usuario autenticado
$canEdit  = in_array($_SESSION['admin_role'], ['administrador', 'secretario']);
$isAdmin  = $_SESSION['admin_role'] === 'administrador';
$lowCount = (int)($stats['low_stock_items'] ?? 0);
?>

<div class="space-y-5 max-w-4xl mx-auto">

    <!-- Encabezado -->
    <div class="flex items-center justify-between gap-4">
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-boxes text-red-700"></i> Inventario
        </h2>
        <?php if ($canEdit): ?>
        <a href="<?php echo BASE_URL; ?>inventory/create"
           class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Agregar
        </a>
        <?php endif; ?>
    </div>

    <!-- Mensajes -->
    <?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <i class="fas fa-check-circle mr-1"></i>
        <?php echo $success === 'created' ? 'Ítem agregado.' : 'Cambios guardados.'; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
        <i class="fas fa-exclamation-circle mr-1"></i>
        <?php echo htmlspecialchars($error === 'not_found' ? 'Ítem no encontrado.' : $error); ?>
    </div>
    <?php endif; ?>

    <!-- Aviso de poco stock -->
    <?php if ($lowCount > 0 && $filters['low_stock'] !== '1'): ?>
    <div class="bg-yellow-50 border border-yellow-300 rounded-lg px-4 py-3 text-sm text-yellow-800 flex items-center gap-2">
        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
        <span><strong><?php echo $lowCount; ?></strong> ítem<?php echo $lowCount != 1 ? 's' : ''; ?> con poco stock.
            <a href="?low_stock=1" class="underline font-medium">Ver</a></span>
    </div>
    <?php endif; ?>

    <!-- Búsqueda -->
    <form method="GET" action="<?php echo BASE_URL; ?>inventory" class="flex gap-2">
        <?php if ($filters['low_stock'] === '1'): ?><input type="hidden" name="low_stock" value="1"><?php endif; ?>
        <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q']); ?>"
               placeholder="Buscar ítem..."
               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-red-700">
        <button type="submit" class="btn-primary text-white px-4 rounded-lg" title="Buscar">
            <i class="fas fa-search"></i>
        </button>
        <?php if ($filters['q'] !== '' || $filters['low_stock'] === '1'): ?>
        <a href="<?php echo BASE_URL; ?>inventory"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 rounded-lg flex items-center">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Lista -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <?php if (empty($items)): ?>
        <div class="p-12 text-center text-gray-400">
            <i class="fas fa-box-open text-5xl mb-3 block"></i>
            <p>No hay ítems<?php echo ($filters['q'] !== '' || $filters['low_stock'] === '1') ? ' con esa búsqueda' : ' registrados'; ?>.</p>
        </div>
        <?php else: ?>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Ítem</th>
                    <th class="px-4 py-3 text-center">Cantidad</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($items as $item):
                    $isLow   = $item['min_quantity'] > 0 && $item['quantity'] <= $item['min_quantity'];
                    $jsName  = htmlspecialchars(addslashes($item['name']), ENT_QUOTES);
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="<?php echo BASE_URL; ?>inventory/view?id=<?php echo $item['id']; ?>"
                           class="font-medium text-gray-900 hover:text-red-700">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-lg font-bold <?php echo $isLow ? 'text-yellow-600' : 'text-gray-900'; ?>">
                            <?php echo number_format($item['quantity']); ?>
                        </span>
                        <?php if ($isLow): ?>
                        <span class="block text-xs text-yellow-700">Poco stock</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-1">
                            <?php if ($canEdit): ?>
                            <button onclick="openMovementModal(<?php echo $item['id']; ?>, '<?php echo $jsName; ?>', <?php echo (int)$item['quantity']; ?>, 'salida')"
                                    class="w-8 h-8 rounded-full bg-red-50 text-red-700 hover:bg-red-100" title="Quitar">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button onclick="openMovementModal(<?php echo $item['id']; ?>, '<?php echo $jsName; ?>', <?php echo (int)$item['quantity']; ?>, 'entrada')"
                                    class="w-8 h-8 rounded-full bg-green-50 text-green-700 hover:bg-green-100" title="Agregar">
                                <i class="fas fa-plus"></i>
                            </button>
                            <a href="<?php echo BASE_URL; ?>inventory/edit?id=<?php echo $item['id']; ?>"
                               class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-gray-800" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($isAdmin): ?>
                            <button onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo $jsName; ?>')"
                                    class="w-8 h-8 text-gray-400 hover:text-red-600" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php if ($canEdit) include __DIR__ . '/_movement_modal.php'; ?>

<?php if ($isAdmin): ?>
<!-- Confirmar eliminación -->
<div id="delete-item-modal" class="modal-overlay">
    <div class="modal-box">
        <h3 class="modal-title">¿Eliminar ítem?</h3>
        <p id="delete-item-name" class="modal-subtitle"></p>
        <p class="modal-text">Se borrará también su historial. No se puede deshacer.</p>
        <div class="modal-actions">
            <button id="confirm-delete-item" class="btn-danger">Eliminar</button>
            <button id="cancel-delete-item" class="btn-cancel">Cancelar</button>
        </div>
    </div>
</div>

<script>
let _delItemId = null;
const delModal = document.getElementById('delete-item-modal');

window.deleteItem = function(id, name) {
    _delItemId = id;
    document.getElementById('delete-item-name').textContent = name;
    delModal.classList.add('is-open');
};
function closeDeleteModal() { delModal.classList.remove('is-open'); _delItemId = null; }
delModal.addEventListener('click', e => { if (e.target === delModal) closeDeleteModal(); });
document.getElementById('cancel-delete-item').addEventListener('click', closeDeleteModal);

document.getElementById('confirm-delete-item').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    ajaxRequest('<?php echo BASE_URL; ?>inventory/delete', {id: _delItemId}, function(r) {
        btn.disabled = false;
        if (r.success) { closeDeleteModal(); location.reload(); }
        else { alert(r.message || 'Error al eliminar.'); }
    });
});
</script>
<?php endif; ?>
