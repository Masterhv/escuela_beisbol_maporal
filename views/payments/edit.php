<?php
/** @var array $payment @var array $concepts */
$err = $_GET['error'] ?? '';
$months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-edit text-yellow-600"></i>
            Editar Cobro
        </h2>
        <p class="text-gray-600 mt-1">
            Actualiza el cobro de <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>.
            Estado actual:
            <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full <?php echo PaymentWorkflow::badge($payment['status']); ?>"><?php echo PaymentWorkflow::label($payment['status']); ?></span>
        </p>
    </div>

    <?php if (isset($error)): ?>
    <div class="pay-notice pay-notice--rejected"><p class="pay-notice__text"><?php echo htmlspecialchars($error); ?></p></div>
    <?php endif; ?>
    <?php if ($err !== ''): ?>
    <div class="pay-notice pay-notice--rejected"><p class="pay-notice__text">Revisa los datos: concepto, mes, año, fecha y un monto mayor que cero.</p></div>
    <?php endif; ?>

    <div class="pay-notice pay-notice--pending">
        <p class="pay-notice__text">Solo se pueden editar cobros Pendientes o Rechazados. Esta pantalla no cambia el estado del pago: para eso el representante lo reporta y el secretario lo concilia.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Atleta</label>
                    <input type="text" readonly value="<?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100">
                </div>
                <div>
                    <label for="concept_id" class="block text-sm font-medium text-gray-700 mb-2">Concepto</label>
                    <select id="concept_id" name="concept_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <?php foreach ($concepts as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)$payment['concept_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Monto (Bs.)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" value="<?php echo htmlspecialchars($payment['amount']); ?>">
                </div>
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Mes</label>
                    <select id="month" name="month" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <?php foreach ($months as $m): ?>
                        <option value="<?php echo $m; ?>" <?php echo $payment['month'] === $m ? 'selected' : ''; ?>><?php echo $m; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Año</label>
                    <input type="number" id="year" name="year" required min="2000" max="2100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" value="<?php echo (int)$payment['year']; ?>">
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento</label>
                    <input type="date" id="due_date" name="due_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" value="<?php echo htmlspecialchars($payment['due_date']); ?>">
                </div>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas (Opcional)</label>
                <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700"><?php echo htmlspecialchars($payment['notes'] ?? ''); ?></textarea>
            </div>
            <div class="border border-red-200 bg-red-50 rounded-lg p-4">
                <label for="operation_key" class="block text-sm font-semibold text-gray-900 mb-1"><i class="fas fa-shield-alt text-red-700 mr-2"></i>Clave de Operaciones Especiales *</label>
                <p class="text-xs text-gray-600 mb-2">Editar un cobro creado requiere tu clave de 4 dígitos de administrador.</p>
                <input type="password" id="operation_key" name="operation_key" required maxlength="4" pattern="\d{4}" inputmode="numeric" placeholder="••••"
                       class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 tracking-widest text-center text-xl">
            </div>
            <div class="flex justify-end gap-4">
                <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo (int)$payment['id']; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">Cancelar</a>
                <button type="submit" class="btn-primary text-white font-medium py-2 px-4 rounded-lg"><i class="fas fa-save mr-2"></i>Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
