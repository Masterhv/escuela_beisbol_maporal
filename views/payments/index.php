<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-credit-card text-red-700"></i>
                Gestión de Pagos
            </h2>
            <p class="text-black mt-1"><?php echo $_SESSION['admin_role'] === 'representante' ? 'Estado de cuenta de tus atletas. Reporta aquí tus pagos.' : 'Administra los cobros y pagos de los atletas'; ?></p>
        </div>
        
        <?php if (in_array($_SESSION['admin_role'], ['administrador', 'secretario'])): ?>
        <a href="<?php echo BASE_URL; ?>payments/create" class="btn-primary text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Generar Cobro
        </a>
        <?php endif; ?>
    </div>

    <!-- Filter Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <form method="GET" action="<?php echo BASE_URL; ?>payments" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div class="lg:col-span-2 relative">
                    <label for="smart_search" class="block text-sm font-medium text-black"><?php echo !empty($canSearchReferences) ? 'Búsqueda por cédula o referencia' : 'Búsqueda por cédula'; ?></label>
                    <input type="text" id="smart_search" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="<?php echo !empty($canSearchReferences) ? 'C.I. del atleta o representante, o N.° de referencia (mín. 4 dígitos)...' : 'C.I. del atleta o representante...'; ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md" autocomplete="off">
                    <div id="autocomplete-results-smart" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 hidden shadow-lg"></div>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-black">Estatus</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
                        <option value="">Todos</option>
                        <?php foreach (PaymentWorkflow::LABELS as $statusKey => $statusLabel): ?>
                        <option value="<?php echo $statusKey; ?>" <?php echo (isset($filters['status']) && $filters['status'] === $statusKey) ? 'selected' : ''; ?>><?php echo $statusLabel; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="month" class="block text-sm font-medium text-black">Mes</label>
                    <select id="month" name="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
                        <option value="">Todos</option>
                        <?php
                        $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        foreach ($months as $month) {
                            $selected = (isset($filters['month']) && $filters['month'] === $month) ? 'selected' : '';
                            echo "<option value=\"$month\" $selected>$month</option>";
                        }
                        ?>
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-black">Año</label>
                    <input type="number" id="year" name="year" value="<?php echo $filters['year'] ?? ''; ?>" placeholder="Ej: <?php echo date('Y'); ?>" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-black">Categoría</label>
                    <select name="category" id="category" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
                        <option value="">Todas</option>
                        <option value="Infantil" <?php echo (isset($filters['category']) && $filters['category'] === 'Infantil') ? 'selected' : ''; ?>>Infantil (8-10 años)</option>
                        <option value="Pre-juvenil" <?php echo (isset($filters['category']) && $filters['category'] === 'Pre-juvenil') ? 'selected' : ''; ?>>Pre-juvenil (11-12 años)</option>
                        <option value="Juvenil" <?php echo (isset($filters['category']) && $filters['category'] === 'Juvenil') ? 'selected' : ''; ?>>Juvenil (13-15 años)</option>
                        <option value="Junior" <?php echo (isset($filters['category']) && $filters['category'] === 'Junior') ? 'selected' : ''; ?>>Junior (16-18 años)</option>
                    </select>
                </div>
                <div>
                    <label for="age" class="block text-sm font-medium text-black">Edad</label>
                    <select name="age" id="age" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
                        <option value="">Todas</option>
                        <?php for ($i = 8; $i <= 18; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo (isset($filters['age']) && $filters['age'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?> años</option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                         <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Atleta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Edad/Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Concepto / Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Estatus</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-black uppercase tracking-wider">Comprobante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="fas fa-file-invoice-dollar text-black text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron pagos</h3>
                            <p class="text-black">Intenta ajustar los filtros o registra un nuevo pago.</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-medium text-gray-900">
                                    <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-gray-900">
                                        <?php
                                        if (isset($payment['age']) && $payment['age'] !== null && $payment['age'] !== '') {
                                            echo htmlspecialchars($payment['age']) . ' años';
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </p>
                                    <p class="text-sm text-black">
                                        <?php echo htmlspecialchars($payment['category'] ?? 'N/A'); ?>
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-gray-900">
                                        Bs. <?php echo number_format($payment['amount'], 2); ?>
                                    </p>
                                    <p class="text-sm text-black">
                                        <?php echo htmlspecialchars(($payment['concept_name'] ?? 'Mensualidad') . ' · ' . $payment['month'] . ' ' . $payment['year']); ?>
                                    </p>
                                    <?php if (!empty($canSearchReferences) && !empty($payment['reference_number'])): ?>
                                    <?php
                                        // Solo administrador y secretario ven la referencia aqui. Se escapa PRIMERO y despues se resalta lo buscado:
                                        // asi el <mark> no abre la puerta a inyectar HTML.
                                        $refText = htmlspecialchars($payment['reference_number']);
                                        $q = trim((string)($filters['q'] ?? ''));
                                        if (($payment['matched_by'] ?? null) === 'reference' || ($payment['matched_by'] ?? null) === 'both') {
                                            $refText = str_replace(htmlspecialchars($q), '<mark class="match-mark">' . htmlspecialchars($q) . '</mark>', $refText);
                                        }
                                    ?>
                                    <p class="text-xs text-gray-600 mt-0.5">Ref. <span class="ref-number"><?php echo $refText; ?></span></p>
                                    <?php endif; ?>
                                    <?php if (!empty($canSearchReferences) && !empty($payment['matched_by'])): ?>
                                    <span class="match-badge match-badge--<?php echo htmlspecialchars($payment['matched_by']); ?>">
                                        <?php echo ['reference' => !empty($payment['matched_exact']) ? 'Referencia exacta' : 'Coincide por referencia', 'id_card' => 'Coincide por cédula', 'both' => 'Referencia y cédula'][$payment['matched_by']] ?? ''; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo PaymentWorkflow::badge($payment['status']); ?>">
                                    <?php echo PaymentWorkflow::label($payment['status']); ?>
                                </span>
                                <?php if (Payment::isPastDue($payment)): ?>
                                <p class="text-xs font-semibold text-red-600 mt-1" title="Venció el <?php echo date('d/m/Y', strtotime($payment['due_date'])); ?>">
                                    <i class="fas fa-exclamation-circle"></i> Venció <?php echo date('d/m/Y', strtotime($payment['due_date'])); ?>
                                </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <?php if (!empty($payment['receipt_image'])): ?>
                                <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo $payment['id']; ?>"
                                   title="Ver comprobante adjunto" class="inline-flex items-center justify-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                                        <i class="fas fa-paperclip"></i>Adjunto
                                    </span>
                                </a>
                                <?php else: ?>
                                <span class="text-gray-300 text-xs">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo $payment['id']; ?>"
                                       class="text-red-700 hover:text-red-800 p-1" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php
                                    // Las acciones salen del modelo (PaymentWorkflow), no de comparar roles aquí.
                                    $rowActions = PaymentWorkflow::allowedActions($payment, $_SESSION['admin_role'], $_SESSION['admin_id']);
                                    $isStaffRow = in_array($_SESSION['admin_role'], ['administrador', 'secretario'], true);
                                    ?>
                                    <?php if (in_array('report', $rowActions, true)): ?>
                                        <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo $payment['id']; ?>" class="text-blue-700 hover:text-blue-800 p-1" title="Reportar pago">
                                            <i class="fas fa-paper-plane"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (in_array('approve', $rowActions, true)): ?>
                                        <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo $payment['id']; ?>" class="text-green-700 hover:text-green-800 p-1" title="Conciliar">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if ($isStaffRow && in_array($payment['status'], ['pending', 'rejected'], true)): ?>
                                        <a href="<?php echo BASE_URL; ?>payments/edit?id=<?php echo $payment['id']; ?>" class="text-red-600 hover:text-red-800 p-1" title="Editar cobro">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Auto-submit cuando cambian los selectores
    document.getElementById('status').addEventListener('change', function() {
        form.submit();
    });
    
    document.getElementById('month').addEventListener('change', function() {
        form.submit();
    });
    
    document.getElementById('year').addEventListener('change', function() {
        form.submit();
    });
    
    document.getElementById('category').addEventListener('change', function() {
        form.submit();
    });
    
    document.getElementById('age').addEventListener('change', function() {
        form.submit();
    });
    
    // Búsqueda por cédula del atleta o representante
    initAutocomplete(
        'smart_search',
        'autocomplete-results-smart',
        '<?php echo BASE_URL; ?>athletes/autocompleteUnified',
        function(result, term) {
            return athleteResultFormatter(result, '<?php echo BASE_URL; ?>', term);
        },
        null,
        function(result) {
            // Callback cuando se selecciona un atleta
            const searchInput = document.getElementById('smart_search');
            // Establecer la cédula en el campo de búsqueda
            const cedula = result.id_card || result.parent_id_card || '';
            searchInput.value = cedula;
            
            // Esperar un momento para que el valor se establezca antes de enviar
            setTimeout(function() {
                form.submit();
            }, 100);
        }
    );
});
</script>
