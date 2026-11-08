<?php
/**
 * Modal simple para sumar o restar unidades de un ítem.
 * Uso: openMovementModal(id, nombre, stock, 'entrada' | 'salida')
 */
?>
<div id="movement-modal" class="modal-overlay">
    <div class="modal-box modal-box--wide">
        <button id="close-movement-modal" class="modal-close">
            <i class="fas fa-times"></i>
        </button>

        <h3 id="movement-item-name" class="modal-title"></h3>
        <p id="movement-stock-info" class="modal-subtitle modal-subtitle--spaced"></p>

        <div class="grid grid-cols-2 gap-2 mb-4">
            <button type="button" id="type-btn-entrada" onclick="selectMovType('entrada')"
                    class="mov-type-btn py-2 rounded-lg border-2 text-sm font-semibold transition-colors">
                <i class="fas fa-plus mr-1"></i> Agregar
            </button>
            <button type="button" id="type-btn-salida" onclick="selectMovType('salida')"
                    class="mov-type-btn py-2 rounded-lg border-2 text-sm font-semibold transition-colors">
                <i class="fas fa-minus mr-1"></i> Quitar
            </button>
        </div>

        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
        <input type="number" id="movement-quantity" min="1" value="1"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 mb-3">

        <label class="block text-sm font-medium text-gray-700 mb-1">Nota <span class="text-gray-400 font-normal">(opcional)</span></label>
        <input type="text" id="movement-reason" maxlength="255" placeholder="Ej: compra, préstamo..."
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">

        <p id="movement-error" class="text-red-600 text-sm mt-2 hidden"></p>

        <div class="modal-actions pt-4">
            <button id="confirm-movement" class="btn-danger">Guardar</button>
            <button id="cancel-movement" class="btn-cancel">Cancelar</button>
        </div>
    </div>
</div>

<script>
let _movItemId = null, _movType = 'entrada';
const movModal = document.getElementById('movement-modal');

window.selectMovType = function(type) {
    _movType = type;
    ['entrada', 'salida'].forEach(t => {
        const b = document.getElementById('type-btn-' + t);
        b.classList.remove('is-entrada', 'is-salida');
        if (t === type) b.classList.add('is-' + t);
    });
};

window.openMovementModal = function(id, name, stock, type) {
    _movItemId = id;
    document.getElementById('movement-item-name').textContent = name;
    document.getElementById('movement-stock-info').textContent = 'Hay ' + stock + ' en inventario';
    document.getElementById('movement-quantity').value = 1;
    document.getElementById('movement-reason').value = '';
    document.getElementById('movement-error').classList.add('hidden');
    selectMovType(type || 'entrada');
    movModal.classList.add('is-open');
    setTimeout(() => document.getElementById('movement-quantity').select(), 100);
};

function closeMovementModal() { movModal.classList.remove('is-open'); _movItemId = null; }
movModal.addEventListener('click', e => { if (e.target === movModal) closeMovementModal(); });
document.getElementById('close-movement-modal').addEventListener('click', closeMovementModal);
document.getElementById('cancel-movement').addEventListener('click', closeMovementModal);

document.getElementById('confirm-movement').addEventListener('click', function() {
    const qty   = parseInt(document.getElementById('movement-quantity').value);
    const errEl = document.getElementById('movement-error');
    if (!qty || qty <= 0) {
        errEl.textContent = 'Ingrese una cantidad mayor a 0.';
        errEl.classList.remove('hidden');
        return;
    }
    errEl.classList.add('hidden');

    const btn = this;
    btn.disabled = true;
    ajaxRequest('<?php echo BASE_URL; ?>inventory/movement', {
        item_id: _movItemId,
        type: _movType,
        quantity: qty,
        reason: document.getElementById('movement-reason').value.trim()
    }, function(r) {
        btn.disabled = false;
        if (r.success) { closeMovementModal(); location.reload(); }
        else { errEl.textContent = r.message || 'Error al guardar.'; errEl.classList.remove('hidden'); }
    });
});
</script>
