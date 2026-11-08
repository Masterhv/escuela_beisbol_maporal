<?php
/**
 * Vista de Generación de Cobros (Pagos Pendientes).
 * 
 * Permite a administradores y secretarios generar un cobro individual para un atleta:
 * - Búsqueda asistida de atleta por cédula o nombre con autocompletado en tiempo real.
 * - Selección de concepto de cobro (Inscripción, Mensualidad, Uniformes, etc.).
 * - Selección de mes y año aplicable, fecha límite de vencimiento y monto en bolívares.
 * - El cobro se registra con estatus "Pendiente", listo para que el representante reporte su transferencia.
 * 
 * Variables recibidas desde PaymentsController::create():
 * @var array $concepts Conceptos de cobro activos disponibles en el catálogo.
 */
$errors = [
    'concept' => 'Selecciona un concepto de cobro válido.', 'athlete' => 'Selecciona un atleta de la lista.',
    'month' => 'El mes no es válido.', 'year' => 'El año no es válido.', 'due_date' => 'La fecha de vencimiento no es válida.',
    'amount' => 'El monto debe ser mayor que cero.', 'failed' => 'No se pudo generar el cobro. Inténtalo de nuevo.',
];
$err = $_GET['error'] ?? '';
$months = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
$currentMonth = $months[(int)date('n') - 1];
?>
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-plus-circle text-red-700"></i>
            Generar Cobro
        </h2>
        <p class="text-gray-600 mt-1">Crea un cobro para un atleta. Nace como <strong>Pendiente</strong>; el representante lo reporta y el secretario lo aprueba.</p>
    </div>

    <?php if ($err !== ''): ?>
    <div class="pay-notice pay-notice--rejected"><p class="pay-notice__text"><?php echo htmlspecialchars($errors[$err] ?? 'No se pudo completar la operación.'); ?></p></div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="athlete_search" class="block text-sm font-medium text-gray-700 mb-2">Búsqueda por cédula</label>
                    <div class="relative">
                        <input type="text" id="athlete_search" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-red-700" placeholder="C.I. del atleta o representante..." autocomplete="off">
                        <input type="hidden" id="athlete_id" name="athlete_id" required>
                        <input type="hidden" id="athlete_name" name="athlete_name">
                        <div id="autocomplete-results-payment" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 hidden shadow-lg max-h-60 overflow-y-auto"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Busque por cédula del atleta o cédula del representante</p>
                </div>
                <div>
                    <label for="concept_id" class="block text-sm font-medium text-gray-700 mb-2">Concepto</label>
                    <select id="concept_id" name="concept_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <?php foreach ($concepts as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" data-amount="<?php echo htmlspecialchars($c['default_amount']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Monto (Bs.)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" placeholder="Ej: 10000.00">
                    <p class="text-xs text-gray-500 mt-1">Se sugiere la tarifa del concepto; puedes ajustarla para este cobro.</p>
                </div>
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Mes</label>
                    <select id="month" name="month" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <?php foreach ($months as $m): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m === $currentMonth ? 'selected' : ''; ?>><?php echo $m; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Año</label>
                    <input type="number" id="year" name="year" required min="2000" max="2100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" value="<?php echo date('Y'); ?>">
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento</label>
                    <input type="date" id="due_date" name="due_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                </div>
            </div>
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas (Opcional)</label>
                <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" placeholder="Añade notas adicionales sobre el cobro..."></textarea>
            </div>
            <div class="flex justify-end gap-4">
                <a href="<?php echo BASE_URL; ?>payments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">Cancelar</a>
                <button type="submit" class="btn-primary text-white font-medium py-2 px-4 rounded-lg"><i class="fas fa-save mr-2"></i>Generar cobro</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const concept = document.getElementById('concept_id');
    const amount = document.getElementById('amount');
    function suggest() {
        const opt = concept.options[concept.selectedIndex];
        const v = opt ? parseFloat(opt.getAttribute('data-amount')) : 0;
        if (v > 0) { amount.value = v.toFixed(2); }
    }
    concept.addEventListener('change', suggest);
    suggest();

    initAutocomplete(
        'athlete_search', 'autocomplete-results-payment',
        '<?php echo BASE_URL; ?>athletes/autocompleteUnified',
        function (result, term) { return athleteResultFormatter(result, '<?php echo BASE_URL; ?>', term); },
        'athlete_id',
        function (result) { document.getElementById('athlete_name').value = result.first_name + ' ' + result.last_name; }
    );
});
</script>
