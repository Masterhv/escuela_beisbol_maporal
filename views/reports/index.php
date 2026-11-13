<?php
/**
 * Centro de Generación de Reportes y Documentos Oficiales.
 * 
 * Permite seleccionar el tipo de reporte, aplicar filtros y exportar a PDF con verificación QR:
 * 1. Lista de Atletas (filtrada por categoría, estatus o edad).
 * 2. Ficha de Atleta (carnet y datos deportivos del jugador seleccionado).
 * 3. Historial de Pagos de Atleta (todos los cobros y abonos del alumno).
 * 4. Reporte Financiero (ingresos por rango de fechas, estatus de cobro y totales recaudados).
 * 
 * Variables recibidas desde ReportsController::index():
 * @var string $title        Título de la sección.
 * @var array  $data         Resultados filtrados para previsualización.
 * @var array  $filters      Parámetros aplicados en la consulta.
 * @var string $selectedType Tipo de reporte activo.
 */
?>
<div class="space-y-8" x-data="reports()">
    <!-- Header -->
    <div>
        <h2 class="text-3xl font-bold text-gray-900"><?php echo $title; ?></h2>
        <p class="text-black mt-1">Seleccione un tipo de reporte, configure los filtros y genere su documento.</p>
    </div>

    <!-- Report Type Selector -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <label for="report_type" class="block text-lg font-semibold text-black">1. Seleccione el Tipo de Reporte</label>
        <select id="report_type" name="report_type" x-model="selectedReport" class="mt-2 block w-full md:w-1/2 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
            <option value="athlete_list">Lista de Atletas</option>
            <option value="athlete_card">Ficha de Atleta</option>
            <option value="payment_history">Historial de Pagos de Atleta</option>
            <option value="financial">Reporte Financiero</option>
        </select>
    </div>

    <!-- Filters Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="text-lg font-semibold text-black mb-4">2. Configure los Filtros</h3>
        <form id="report-form" method="GET" action="<?php echo BASE_URL; ?>reports">
            <input type="hidden" name="filter" value="1">
            <input type="hidden" name="report_type" x-bind:value="selectedReport">

            <!-- Athlete List Filters -->
            <div x-show="selectedReport === 'athlete_list'" class="space-y-4">
                <p class="text-sm text-black">Filtros para la lista de atletas. Deje en blanco para incluir todos.</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-black">Categoría</label>
                        <select name="category" id="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                            <option value="">Todas</option>
                            <option value="Infantil" <?php echo (isset($filters['category']) && $filters['category'] === 'Infantil') ? 'selected' : ''; ?>>Infantil (8-10 años)</option>
                            <option value="Pre-juvenil" <?php echo (isset($filters['category']) && $filters['category'] === 'Pre-juvenil') ? 'selected' : ''; ?>>Pre-juvenil (11-12 años)</option>
                            <option value="Juvenil" <?php echo (isset($filters['category']) && $filters['category'] === 'Juvenil') ? 'selected' : ''; ?>>Juvenil (13-15 años)</option>
                            <option value="Junior" <?php echo (isset($filters['category']) && $filters['category'] === 'Junior') ? 'selected' : ''; ?>>Junior (16-18 años)</option>
                        </select>
                    </div>
                    <div>
                        <label for="age" class="block text-sm font-medium text-black">Edad</label>
                        <input type="number" name="age" id="age" value="<?php echo $filters['age'] ?? ''; ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" placeholder="Ej: 10">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-black">Estatus</label>
                        <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <option value="active" <?php echo (isset($filters['status']) && $filters['status'] === 'active') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactive" <?php echo (isset($filters['status']) && $filters['status'] === 'inactive') ? 'selected' : ''; ?>>Inactivo</option>
                            <option value="suspended" <?php echo (isset($filters['status']) && $filters['status'] === 'suspended') ? 'selected' : ''; ?>>Suspendido</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="athlete_month" class="block text-sm font-medium text-black">Mes</label>
                        <select name="athlete_month" id="athlete_month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <?php
                            $months = [
                                '01' => 'Enero',
                                '02' => 'Febrero',
                                '03' => 'Marzo',
                                '04' => 'Abril',
                                '05' => 'Mayo',
                                '06' => 'Junio',
                                '07' => 'Julio',
                                '08' => 'Agosto',
                                '09' => 'Septiembre',
                                '10' => 'Octubre',
                                '11' => 'Noviembre',
                                '12' => 'Diciembre',
                            ];
                            $selectedMonth = $filters['athlete_month'] ?? '';
                            foreach ($months as $value => $label) {
                                $selected = ($selectedMonth === $value) ? 'selected' : '';
                                echo "<option value=\"{$value}\" {$selected}>{$label}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="athlete_year" class="block text-sm font-medium text-black">Año</label>
                        <input type="number" name="athlete_year" id="athlete_year" value="<?php echo $filters['athlete_year'] ?? ''; ?>" placeholder="Ej: <?php echo date('Y'); ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                    </div>
                </div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date_athlete" class="block text-sm font-medium text-black">Fecha de Inicio</label>
                            <input type="date" name="athlete_start_date" id="start_date_athlete" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" value="<?php echo $filters['athlete_start_date'] ?? ''; ?>">
                        </div>
                        <div>
                            <label for="end_date_athlete" class="block text-sm font-medium text-black">Fecha de Fin</label>
                            <input type="date" name="athlete_end_date" id="end_date_athlete" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" value="<?php echo $filters['athlete_end_date'] ?? ''; ?>">
                        </div>
                    </div>
            </div>

            <!-- Athlete Search Filter -->
            <div x-show="selectedReport === 'athlete_card' || selectedReport === 'payment_history'" class="space-y-4">
                <p class="text-sm text-black">Busque y seleccione un atleta por cédula (atleta o representante).</p>
                <div class="relative">
                    <label for="athlete_search" class="block text-sm font-medium text-black mb-1">Búsqueda por cédula</label>
                    <input type="text" id="athlete_search" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 rounded-md" placeholder="C.I. del atleta o representante..." autocomplete="off" value="<?php echo isset($filters['athlete_name']) ? htmlspecialchars($filters['athlete_name']) : ''; ?>">
                    <input type="hidden" id="athlete_id" name="athlete_id" value="<?php echo $filters['athlete_id'] ?? ''; ?>">
                    <input type="hidden" id="athlete_name" name="athlete_name" value="<?php echo $filters['athlete_name'] ?? ''; ?>">
                    <div id="autocomplete-results-reports" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 hidden shadow-lg"></div>
                </div>
            </div>

            <!-- Financial Filters -->
            <div x-show="selectedReport === 'financial'" class="space-y-4">
                <p class="text-sm text-black">Filtre los resultados financieros por rango de fechas, mes, año y estado.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="financial_start_date" class="block text-sm font-medium text-black">Fecha de Inicio</label>
                        <input type="date" name="financial_start_date" id="financial_start_date" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" value="<?php echo $filters['financial_start_date'] ?? ''; ?>">
                    </div>
                    <div>
                        <label for="financial_end_date" class="block text-sm font-medium text-black">Fecha de Fin</label>
                        <input type="date" name="financial_end_date" id="financial_end_date" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" value="<?php echo $filters['financial_end_date'] ?? ''; ?>">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label for="financial_month" class="block text-sm font-medium text-black">Mes</label>
                        <select name="financial_month" id="financial_month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <?php
                            // Los valores deben coincidir con los nombres almacenados en la BD
                            $months = [
                                'Enero' => 'Enero',
                                'Febrero' => 'Febrero',
                                'Marzo' => 'Marzo',
                                'Abril' => 'Abril',
                                'Mayo' => 'Mayo',
                                'Junio' => 'Junio',
                                'Julio' => 'Julio',
                                'Agosto' => 'Agosto',
                                'Septiembre' => 'Septiembre',
                                'Octubre' => 'Octubre',
                                'Noviembre' => 'Noviembre',
                                'Diciembre' => 'Diciembre',
                            ];
                            $selectedMonth = $filters['financial_month'] ?? '';
                            foreach ($months as $value => $label) {
                                $selected = ($selectedMonth === $value) ? 'selected' : '';
                                echo "<option value=\"{$value}\" {$selected}>{$label}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="financial_year" class="block text-sm font-medium text-black">Año</label>
                        <input type="number" name="financial_year" id="financial_year" value="<?php echo $filters['financial_year'] ?? ''; ?>" placeholder="Ej: <?php echo date('Y'); ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="financial_category" class="block text-sm font-medium text-black">Categoría</label>
                        <select name="financial_category" id="financial_category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" @change="onCategoryChange()">
                            <option value="">Todas</option>
                            <option value="Infantil" <?php echo (isset($filters['financial_category']) && $filters['financial_category'] === 'Infantil') ? 'selected' : ''; ?>>Infantil (8-10 años)</option>
                            <option value="Pre-juvenil" <?php echo (isset($filters['financial_category']) && $filters['financial_category'] === 'Pre-juvenil') ? 'selected' : ''; ?>>Pre-juvenil (11-12 años)</option>
                            <option value="Juvenil" <?php echo (isset($filters['financial_category']) && $filters['financial_category'] === 'Juvenil') ? 'selected' : ''; ?>>Juvenil (13-15 años)</option>
                            <option value="Junior" <?php echo (isset($filters['financial_category']) && $filters['financial_category'] === 'Junior') ? 'selected' : ''; ?>>Junior (16-18 años)</option>
                        </select>
                    </div>
                    <div>
                        <label for="financial_status" class="block text-sm font-medium text-black">Estatus</label>
                        <select name="financial_status" id="financial_status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <?php foreach (PaymentWorkflow::LABELS as $statusKey => $statusLabel): ?>
                            <option value="<?php echo $statusKey; ?>" <?php echo (isset($filters['financial_status']) && $filters['financial_status'] === $statusKey) ? 'selected' : ''; ?>><?php echo $statusLabel; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end pt-5 mt-5 border-t">
                <button type="button" @click="filterOnScreen" class="bg-gray-800 text-white font-bold py-2 px-4 rounded-lg hover:bg-black transition">
                    <i class="fas fa-filter mr-2"></i>Filtrar en Pantalla
                </button>
                <button type="button" @click="generatePdf" class="btn-primary ml-3 text-white font-bold py-2 px-4 rounded-lg transition shadow-md hover:shadow-lg">
                    <i class="fas fa-file-pdf mr-2"></i>Generar PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Report Preview -->
    <?php if (isset($data) && !empty($data) && isset($_GET['filter'])): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h3 class="text-xl font-bold text-black mb-4">Vista Previa del Reporte</h3>
            <?php
            if ($reportType === 'athlete_list' && !empty($data['athletes'])) {
                include '_athletes_report.php';
            } elseif ($reportType === 'financial' && !empty($data['payments'])) {
                include '_financial_report.php';
            } elseif ($reportType === 'athlete_card' && !empty($data['Athlete'])) {
                include '_athlete_card_report.php';
            } elseif ($reportType === 'payment_history' && !empty($data['Athlete'])) {
                include '_payment_history_report.php';
            }
            ?>
        </div>
    <?php endif; ?>
</div>

<script defer src="<?php echo defined('BASE_URL') ? BASE_URL : '/project/'; ?>views/assets/js/alpine.min.js?v=<?php echo defined('BASE_PATH') && is_file(BASE_PATH . 'views/assets/js/alpine.min.js') ? @filemtime(BASE_PATH . 'views/assets/js/alpine.min.js') : time(); ?>"></script>
<script>
    function reports() {
        return {
            selectedReport: '<?php echo $reportType ?? 'athlete_list'; ?>',
            init() {
                // Observar cambios en el tipo de reporte
                this.$watch('selectedReport', (newValue, oldValue) => {
                    if (newValue !== oldValue) {
                        // Limpiar los campos de búsqueda de atleta cuando se cambia de reporte
                        const athleteIdField = document.getElementById('athlete_id');
                        const athleteNameField = document.getElementById('athlete_name');
                        const athleteSearchField = document.getElementById('athlete_search');
                        
                        if (athleteIdField) athleteIdField.value = '';
                        if (athleteNameField) athleteNameField.value = '';
                        if (athleteSearchField) athleteSearchField.value = '';
                    }
                });
            },
            filterOnScreen() {
                const form = document.getElementById('report-form');
                form.action = '<?php echo BASE_URL; ?>reports';
                form.target = '_self';
                form.method = 'GET';
                form.submit();
            },
            generatePdf() {
                const form = document.getElementById('report-form');
                const originalAction = form.action;
                const originalTarget = form.target;
                const originalMethod = form.method;

                form.action = '<?php echo BASE_URL; ?>reports/generate';
                form.target = '_blank';
                form.method = 'POST';
                form.submit();

                // Restore original form attributes
                setTimeout(() => {
                    form.action = originalAction;
                    form.target = originalTarget;
                    form.method = originalMethod;
                }, 100);
            },
            onCategoryChange() {
                const form = document.getElementById('report-form');
                form.action = '<?php echo BASE_URL; ?>reports';
                form.target = '_self';
                form.method = 'GET';
                form.submit();
            },
            searchAthletes(event) {
                // Esta función ya no se usa, se reemplaza por initAutocomplete
            }
        }
    }
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Búsqueda por cédula para reportes
    initAutocomplete(
        'athlete_search',
        'autocomplete-results-reports',
        '<?php echo BASE_URL; ?>athletes/autocompleteUnified',
        function(result, term) {
            return athleteResultFormatter(result, '<?php echo BASE_URL; ?>', term);
        },
        'athlete_id',
        function(result) {
            // Callback cuando se selecciona un atleta
            document.getElementById('athlete_name').value = `${result.first_name} ${result.last_name}`;
            // Auto-submit del formulario
            document.getElementById('report-form').submit();
        }
    );
});
</script>
