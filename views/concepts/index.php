<?php
/** @var array $concepts Conceptos con payments_count. */
$errors = [
    'name' => 'El nombre debe tener entre 3 y 80 caracteres.', 'description' => 'La descripción no puede superar 255 caracteres.',
    'amount' => 'La tarifa no es válida.', 'duplicate' => 'Ya existe un concepto con ese nombre.',
    'not_found' => 'El concepto no existe.', 'failed' => 'No se pudo guardar. Inténtalo de nuevo.',
];
$success = ['created' => 'Concepto creado.', 'updated' => 'Concepto actualizado.', 'bank_saved' => 'Datos bancarios guardados. Ya los ven los representantes al reportar un pago.', 'bank_same' => 'No hubo cambios en los datos bancarios.'];
$bankLabels = SchoolBankAccount::FIELDS;
// Si la validacion fallo se muestra lo que el usuario escribio; si no, lo guardado.
$b = ($bankOld ?? null) ?: $bank;
$bankFieldErr = !empty($bankErrors);
$err = $_GET['error'] ?? '';
$ok  = $_GET['success'] ?? '';
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-tags text-red-700"></i>
                Conceptos de Cobro
            </h2>
            <p class="text-black mt-1">Define qué se cobra y su tarifa. Cambiar una tarifa no modifica los cobros ya generados.</p>
        </div>
        <button type="button" onclick="openConceptModal()" class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>Nuevo concepto
        </button>
    </div>

    <?php if ($err !== '' && $err !== 'bank'): ?>
    <div class="pay-notice pay-notice--rejected"><p class="pay-notice__text"><?php echo htmlspecialchars($errors[$err] ?? 'No se pudo completar la operación.'); ?></p></div>
    <?php endif; ?>
    <?php if ($ok !== '' && isset($success[$ok])): ?>
    <div class="pay-notice pay-notice--approved"><p class="pay-notice__text"><?php echo htmlspecialchars($success[$ok]); ?></p></div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Concepto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Tarifa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Cobros</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($concepts as $c): $active = (int)$c['is_active'] === 1; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($c['name']); ?></p>
                            <?php if (!empty($c['description'])): ?><p class="text-sm text-black"><?php echo htmlspecialchars($c['description']); ?></p><?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">Bs. <?php echo number_format($c['default_amount'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo (int)$c['is_monthly'] ? 'Mensual' : 'Único'; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo (int)$c['payments_count']; ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo $active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'; ?>"><?php echo $active ? 'Activo' : 'Inactivo'; ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <button type="button" class="text-red-700 hover:text-red-800 p-1" title="Editar"
                                        data-id="<?php echo (int)$c['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($c['name'], ENT_QUOTES); ?>"
                                        data-description="<?php echo htmlspecialchars($c['description'] ?? '', ENT_QUOTES); ?>"
                                        data-amount="<?php echo htmlspecialchars($c['default_amount']); ?>"
                                        data-monthly="<?php echo (int)$c['is_monthly']; ?>"
                                        onclick="openConceptModal(this)"><i class="fas fa-edit"></i></button>
                                <form method="POST" action="<?php echo BASE_URL; ?>concepts/toggle" class="inline">
                                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                                    <input type="hidden" name="active" value="<?php echo $active ? '0' : '1'; ?>">
                                    <button type="submit" class="text-sm text-gray-700 hover:text-gray-900 underline"><?php echo $active ? 'Desactivar' : 'Activar'; ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Datos bancarios de la escuela: UN solo bloque para todos los conceptos. Los ve el representante al reportar un pago. -->
    <div id="datos-bancarios" class="bg-white rounded-xl shadow-sm border p-6">
        <div class="flex items-start justify-between gap-4 mb-1">
            <h3 class="text-lg font-semibold text-gray-900"><i class="fas fa-university text-red-700 mr-2"></i>Datos bancarios de la escuela</h3>
            <?php if (SchoolBankAccount::isConfigured($bank)): ?>
            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Cargados</span>
            <?php else: ?>
            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Sin cargar</span>
            <?php endif; ?>
        </div>
        <p class="text-sm text-gray-600 mb-4">Son los mismos para todos los conceptos. El representante los ve justo encima del formulario donde registra su pago, para depositar en la cuenta correcta. Puedes cargar solo transferencia, solo pago móvil, o ambos.</p>

        <?php if ($bankFieldErr): ?>
        <div class="pay-notice pay-notice--rejected mb-4">
            <p class="pay-notice__title">No se guardaron los datos</p>
            <ul class="pay-notice__list">
                <?php foreach ($bankErrors as $be): ?><li><?php echo htmlspecialchars($be); ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>concepts/bank" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="bank-holder-name" class="block text-sm font-medium text-gray-700 mb-1">Titular de la cuenta</label>
                    <input type="text" id="bank-holder-name" name="holder_name" maxlength="120" value="<?php echo htmlspecialchars($b['holder_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ej: Escuela de Béisbol Menor Maporal">
                </div>
                <div>
                    <label for="bank-holder-id" class="block text-sm font-medium text-gray-700 mb-1">Cédula / RIF del titular</label>
                    <input type="text" id="bank-holder-id" name="holder_id" maxlength="20" value="<?php echo htmlspecialchars($b['holder_id'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ej: J-12345678-9 o V-12345678">
                </div>
            </div>

            <div class="bank-form__group">
                <p class="bank-form__title">Transferencia bancaria</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="bank-name" class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                        <input type="text" id="bank-name" name="bank_name" maxlength="80" value="<?php echo htmlspecialchars($b['bank_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ej: Banco de Venezuela">
                    </div>
                    <div>
                        <label for="bank-type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de cuenta</label>
                        <select id="bank-type" name="account_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">— Seleccionar —</option>
                            <option value="corriente" <?php echo ($b['account_type'] ?? '') === 'corriente' ? 'selected' : ''; ?>>Corriente</option>
                            <option value="ahorro" <?php echo ($b['account_type'] ?? '') === 'ahorro' ? 'selected' : ''; ?>>Ahorro</option>
                        </select>
                    </div>
                    <div>
                        <label for="bank-number" class="block text-sm font-medium text-gray-700 mb-1">N.° de cuenta (20 dígitos)</label>
                        <input type="text" id="bank-number" name="account_number" maxlength="30" inputmode="numeric" value="<?php echo htmlspecialchars($b['account_number'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="01020123456789012345">
                    </div>
                </div>
            </div>

            <div class="bank-form__group">
                <p class="bank-form__title">Pago móvil</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="bank-mobile-bank" class="block text-sm font-medium text-gray-700 mb-1">Banco</label>
                        <input type="text" id="bank-mobile-bank" name="mobile_bank" maxlength="80" value="<?php echo htmlspecialchars($b['mobile_bank'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ej: Banco de Venezuela">
                    </div>
                    <div>
                        <label for="bank-mobile-phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" id="bank-mobile-phone" name="mobile_phone" maxlength="15" inputmode="tel" value="<?php echo htmlspecialchars($b['mobile_phone'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="0414-1234567">
                    </div>
                    <div>
                        <label for="bank-mobile-id" class="block text-sm font-medium text-gray-700 mb-1">Cédula / RIF</label>
                        <input type="text" id="bank-mobile-id" name="mobile_id" maxlength="20" value="<?php echo htmlspecialchars($b['mobile_id'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ej: J-12345678-9">
                    </div>
                </div>
            </div>

            <div class="pay-notice pay-notice--pending">
                <p class="pay-notice__text"><i class="fas fa-shield-alt mr-1"></i><strong>Revisa bien antes de guardar.</strong> Un número de cuenta equivocado hace que las familias depositen en otra cuenta. Cada cambio queda registrado con tu nombre, la fecha y los valores anteriores.</p>
            </div>

            <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-gray-500">
                    <?php if (!empty($bank['updated_at'])): ?>Última actualización: <?php echo date('d/m/Y H:i', strtotime($bank['updated_at'])); ?><?php echo !empty($bank['updated_by_name']) ? ' por ' . htmlspecialchars($bank['updated_by_name']) : ''; ?>.<?php else: ?>Aún no se han cargado datos.<?php endif; ?>
                </p>
                <button type="submit" class="btn-primary text-white font-medium py-2 px-5 rounded-lg"><i class="fas fa-save mr-2"></i>Guardar datos bancarios</button>
            </div>
        </form>

        <?php if (!empty($bankHistory)): ?>
        <div class="mt-6 pt-5 border-t">
            <p class="text-sm font-semibold text-gray-800 mb-2"><i class="fas fa-history text-gray-500 mr-1"></i>Últimos cambios</p>
            <ul class="bank-history">
                <?php foreach ($bankHistory as $h): ?>
                <li><span class="bank-history__when"><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></span> · <strong><?php echo htmlspecialchars($h['changed_by_name']); ?></strong> cambió: <?php echo htmlspecialchars($h['changed_fields']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>

<div id="concept-modal" class="modal-overlay">
    <div class="modal-box modal-box--wide">
        <form id="concept-form" method="POST" action="<?php echo BASE_URL; ?>concepts/create">
            <input type="hidden" name="id" id="concept-id" value="">
            <div class="modal-header"><h3 id="concept-title" class="modal-title">Nuevo concepto</h3></div>

            <label for="concept-name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input type="text" id="concept-name" name="name" required minlength="3" maxlength="80" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3">

            <label for="concept-description" class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
            <input type="text" id="concept-description" name="description" maxlength="255" class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3">

            <label for="concept-amount" class="block text-sm font-medium text-gray-700 mb-1">Tarifa (Bs.)</label>
            <input type="number" id="concept-amount" name="default_amount" step="0.01" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3">

            <label class="flex items-center gap-2 text-sm text-gray-700 mb-4">
                <input type="checkbox" id="concept-monthly" name="is_monthly" value="1"> Se cobra cada mes (mensualidad)
            </label>

            <div class="modal-actions">
                <button type="submit" class="btn-primary text-white font-medium py-2 px-4 rounded-lg">Guardar</button>
                <button type="button" onclick="closeConceptModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
const BASE_CONCEPTS = '<?php echo BASE_URL; ?>concepts/';
function openConceptModal(btn) {
    const form = document.getElementById('concept-form');
    const editing = !!btn;
    form.action = BASE_CONCEPTS + (editing ? 'update' : 'create');
    document.getElementById('concept-title').textContent = editing ? 'Editar concepto' : 'Nuevo concepto';
    document.getElementById('concept-id').value = editing ? btn.dataset.id : '';
    document.getElementById('concept-name').value = editing ? btn.dataset.name : '';
    document.getElementById('concept-description').value = editing ? btn.dataset.description : '';
    document.getElementById('concept-amount').value = editing ? btn.dataset.amount : '';
    document.getElementById('concept-monthly').checked = editing ? btn.dataset.monthly === '1' : false;
    document.getElementById('concept-modal').classList.add('is-open');
    setTimeout(function () { document.getElementById('concept-name').focus(); }, 80);
}
function closeConceptModal() { document.getElementById('concept-modal').classList.remove('is-open'); }
document.getElementById('concept-modal').addEventListener('click', function (e) { if (e.target === this) closeConceptModal(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeConceptModal(); });
</script>
