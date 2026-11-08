<?php
/**
 * Vista de Detalle y Trazabilidad de un Ítem de Inventario.
 * 
 * Presenta:
 * - Indicador visual grande de la cantidad actual en almacén.
 * - Advertencia destacada de stock bajo si la cantidad es menor o igual al mínimo.
 * - Botones directos para sumar (Entrada) o restar (Salida) unidades mediante modal AJAX.
 * - Historial cronológico de todos los movimientos registrados con usuario responsable, fecha y motivo.
 * 
 * Variables recibidas desde InventoryController::view():
 * @var array       $item       Datos detallados del ítem (id, name, quantity, min_quantity, etc.).
 * @var array       $movements  Historial de movimientos asociados (tipo, cantidad, antes, después, razón).
 * @var string|null $success    Mensaje de confirmación de operación exitosa.
 */

// Etiquetas y estilos por tipo de movimiento
$movLabel = ['entrada' => 'Agregó', 'salida' => 'Quitó', 'ajuste' => 'Ajustó'];
$movColor = ['entrada' => 'text-green-700', 'salida' => 'text-red-700', 'ajuste' => 'text-blue-700'];
$isLow    = $item['min_quantity'] > 0 && $item['quantity'] <= $item['min_quantity'];
$canEdit  = in_array($_SESSION['admin_role'], ['administrador', 'secretario']);
?>

<div class="max-w-2xl mx-auto space-y-5">

    <!-- Encabezado -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="<?php echo BASE_URL; ?>inventory" class="text-gray-400 hover:text-red-700">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h2>
        </div>
        <?php if ($canEdit): ?>
        <a href="<?php echo BASE_URL; ?>inventory/edit?id=<?php echo $item['id']; ?>"
           class="text-sm text-gray-600 hover:text-red-700"><i class="fas fa-edit mr-1"></i>Editar</a>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        <i class="fas fa-check-circle mr-1"></i> Cambios guardados.
    </div>
    <?php endif; ?>

    <!-- Cantidad -->
    <div class="bg-white rounded-xl shadow-sm border p-6 text-center">
        <p class="text-sm text-gray-500">En inventario</p>
        <p class="text-6xl font-bold my-1 <?php echo $isLow ? 'text-yellow-600' : 'text-red-700'; ?>">
            <?php echo number_format($item['quantity']); ?>
        </p>
        <?php if ($isLow): ?>
        <p class="text-sm text-yellow-700"><i class="fas fa-exclamation-triangle mr-1"></i>Poco stock (aviso en <?php echo (int)$item['min_quantity']; ?>)</p>
        <?php endif; ?>

        <?php if ($canEdit): ?>
        <div class="flex justify-center gap-3 mt-4">
            <button onclick="openMovementModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name']), ENT_QUOTES); ?>', <?php echo (int)$item['quantity']; ?>, 'salida')"
                    class="px-5 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 font-medium">
                <i class="fas fa-minus mr-1"></i> Quitar
            </button>
            <button onclick="openMovementModal(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars(addslashes($item['name']), ENT_QUOTES); ?>', <?php echo (int)$item['quantity']; ?>, 'entrada')"
                    class="px-5 py-2 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 font-medium">
                <i class="fas fa-plus mr-1"></i> Agregar
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Historial -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 font-semibold text-gray-900">Historial</div>
        <?php if (empty($movements)): ?>
        <p class="p-6 text-center text-sm text-gray-400">Aún no hay movimientos.</p>
        <?php else: ?>
        <ul class="divide-y divide-gray-100 text-sm">
            <?php foreach ($movements as $mov): ?>
            <li class="px-5 py-3 flex items-center justify-between gap-3">
                <div>
                    <span class="font-semibold <?php echo $movColor[$mov['type']] ?? 'text-gray-700'; ?>">
                        <?php echo $movLabel[$mov['type']] ?? ucfirst($mov['type']); ?>
                        <?php echo $mov['type'] === 'ajuste' ? '(→ ' . number_format($mov['quantity_after']) . ')' : number_format($mov['quantity']); ?>
                    </span>
                    <?php if (!empty($mov['reason']) && !in_array($mov['reason'], ['Entrada', 'Salida'], true)): ?>
                    <span class="text-gray-600"> — <?php echo htmlspecialchars($mov['reason']); ?></span>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400">
                        <?php echo htmlspecialchars($mov['registered_by_name'] ?? ''); ?>
                        · <?php echo date('d/m/Y H:i', strtotime($mov['created_at'])); ?>
                    </p>
                </div>
                <span class="text-gray-500 font-mono whitespace-nowrap">
                    <?php echo number_format($mov['quantity_before']); ?> → <?php echo number_format($mov['quantity_after']); ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<?php if ($canEdit) include __DIR__ . '/_movement_modal.php'; ?>
