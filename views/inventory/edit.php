<?php
/**
 * Vista de Edición de Ítem del Inventario.
 * 
 * Permite modificar los datos básicos de un material deportivo:
 * - Nombre del ítem.
 * - Cantidad mínima para la alerta de stock.
 * 
 * Nota de negocio: La cantidad en existencia no se edita directamente desde este formulario;
 * se ajusta mediante el registro de movimientos (Entrada / Salida) para preservar la trazabilidad.
 * 
 * Variables recibidas desde InventoryController::edit():
 * @var array       $item   Registro actual del ítem obtenido de la base de datos.
 * @var string|null $error  Mensaje de error en caso de fallo de validación.
 */
?>
<div class="max-w-md mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="<?php echo BASE_URL; ?>inventory" class="text-gray-400 hover:text-red-700">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Editar ítem</h2>
    </div>

    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-600 text-sm">
        <i class="fas fa-exclamation-circle mr-1"></i><?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>inventory/edit?id=<?php echo $item['id']; ?>"
          class="bg-white rounded-xl shadow-sm border p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
            <input type="text" name="name" required maxlength="150"
                   value="<?php echo htmlspecialchars($item['name']); ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Avisarme cuando queden <span class="text-gray-400 font-normal">(opcional)</span>
            </label>
            <input type="number" name="min_quantity" min="0"
                   value="<?php echo (int)$item['min_quantity']; ?>"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Deje 0 si no desea aviso de poco stock.</p>
        </div>

        <p class="text-sm text-gray-500 bg-gray-50 rounded-lg px-3 py-2">
            Hay <strong><?php echo number_format($item['quantity']); ?></strong> en inventario.
            Para cambiar la cantidad use los botones <strong>+</strong> / <strong>−</strong> de la lista.
        </p>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 btn-primary text-white font-medium py-2.5 rounded-lg">
                <i class="fas fa-save mr-1"></i> Guardar
            </button>
            <a href="<?php echo BASE_URL; ?>inventory"
               class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 rounded-lg text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>
