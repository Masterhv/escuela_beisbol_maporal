<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-users text-red-700"></i>
                Gestión de Atletas
            </h2>
            <p class="text-black mt-1">Administra la información de los atletas inscritos</p>
        </div>
        
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?php echo BASE_URL; ?>athletes/downloadBlankEnrollmentForm" class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <i class="fas fa-download"></i>
                Descargar planilla en blanco
            </a>
            <?php if (in_array($_SESSION['admin_role'], ['administrador', 'secretario'])): ?>
            <a href="<?php echo BASE_URL; ?>athletes/create" class="btn-primary text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <i class="fas fa-plus"></i>
                Nuevo Atleta
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($_GET['success'] ?? '') === 'created'): ?>
    <div class="pay-notice pay-notice--approved"><p class="pay-notice__text">
        Atleta inscrito correctamente.
        <?php if (isset($_GET['plan'])): ?>
            Se generaron <?php echo (int)$_GET['plan']; ?> cobro(s) pendiente(s) para el representante.
            <?php if (!empty($_GET['plan_missing'])): ?>
                No se generó: <?php echo htmlspecialchars($_GET['plan_missing']); ?> (concepto inactivo o inexistente).
            <?php endif; ?>
        <?php endif; ?>
        <?php if (($_GET['account'] ?? '') === 'created'): ?>
            La cuenta de acceso del representante fue creada con el usuario indicado en el formulario.
        <?php elseif (($_GET['account'] ?? '') === 'exists'): ?>
            El representante ya tenía cuenta de acceso: se reutilizó, no se creó otra.
        <?php endif; ?>
    </p></div>
    <?php if (!empty($_GET['plan_error'])): ?>
    <div class="pay-notice pay-notice--rejected"><p class="pay-notice__text">No se pudieron generar los cobros automáticos. Genéralos desde Pagos &gt; Generar cobro.</p></div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Filter Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <form method="GET" action="<?php echo BASE_URL; ?>athletes" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="relative">
                    <label for="smart_search" class="block text-sm font-medium text-black">Búsqueda por cédula</label>
                    <input type="text" id="smart_search" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>" placeholder="C.I. del atleta o representante..." class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md" autocomplete="off">
                    <div id="autocomplete-results-smart" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 hidden shadow-lg"></div>
                </div>
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
                <div>
                    <label for="status" class="block text-sm font-medium text-black">Estatus</label>
                    <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md">
                        <option value="">Todos</option>
                        <option value="active" <?php echo (isset($filters['status']) && $filters['status'] === 'active') ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactive" <?php echo (isset($filters['status']) && $filters['status'] === 'inactive') ? 'selected' : ''; ?>>Inactivo</option>
                        <option value="suspended" <?php echo (isset($filters['status']) && $filters['status'] === 'suspended') ? 'selected' : ''; ?>>Suspendido</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Statistics Section -->
    <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl shadow-sm border border-red-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-4 border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total de Atletas</p>
                        <p class="text-3xl font-bold text-red-700 mt-1">
                            <?php echo isset($stats['total_athletes']) ? (int)$stats['total_athletes'] : 0; ?>
                        </p>
                    </div>
                    <i class="fas fa-users text-red-200 text-4xl"></i>
                </div>
            </div>
            
            <?php if (!empty($filters['category'])): ?>
            <div class="bg-white rounded-lg p-4 border border-blue-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Atletas en <?php echo htmlspecialchars($filters['category']); ?>
                        </p>
                        <p class="text-3xl font-bold text-blue-600 mt-1">
                            <?php echo isset($stats['category_count']) && $stats['category_count'] !== null ? (int)$stats['category_count'] : 0; ?>
                        </p>
                    </div>
                    <i class="fas fa-filter text-blue-200 text-4xl"></i>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($filters['status'])): ?>
            <div class="bg-white rounded-lg p-4 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Estatus: <?php
                            $statusTranslations = [
                                'active' => 'Activo',
                                'inactive' => 'Inactivo',
                                'suspended' => 'Suspendido'
                            ];
                            echo $statusTranslations[$filters['status']] ?? ucfirst($filters['status']);
                        ?></p>
                        <p class="text-3xl font-bold text-green-600 mt-1"><?php echo count($athletes); ?></p>
                    </div>
                    <i class="fas fa-check-circle text-green-200 text-4xl"></i>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($filters['age'])): ?>
            <div class="bg-white rounded-lg p-4 border border-purple-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">
                            Edad: <?php echo htmlspecialchars($filters['age']); ?> años
                        </p>
                        <p class="text-3xl font-bold text-red-700 mt-1">
                            <?php echo isset($stats['age_count']) && $stats['age_count'] !== null ? (int)$stats['age_count'] : 0; ?>
                        </p>
                    </div>
                    <i class="fas fa-birthday-cake text-purple-200 text-4xl"></i>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- athletes Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                            Atleta
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                            Edad/Categoría
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                            Padre/Madre
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                            Estatus
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($athletes)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <i class="fas fa-users text-black text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron atletas</h3>
                            <p class="text-black">
                                Intenta ajustar los filtros o agrega un nuevo atleta.
                            </p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($athletes as $Athlete): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <?php if (!empty($Athlete['photo']) && file_exists($Athlete['photo'])): ?>
                                    <div class="avatar-md flex-shrink-0 border-2 border-red-300 shadow-md ring-2 ring-red-50 rounded-full overflow-hidden">
                                        <img src="<?php echo BASE_URL; ?>athletes/photo?id=<?php echo (int)$Athlete['id']; ?>" alt="Foto" class="w-full h-full object-cover object-center">
                                    </div>
                                    <?php else: ?>
                                    <div class="avatar-md flex-shrink-0 border-2 border-gray-300 shadow-md ring-2 ring-gray-50 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400 text-3xl"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?>
                                        </p>
                                        <p class="text-sm text-black">
                                            Jersey #<?php echo $Athlete['jersey_number'] ?? 'N/A'; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-gray-900"><?php echo $Athlete['age']; ?> años</p>
                                    <p class="text-sm text-black"><?php echo htmlspecialchars($Athlete['category']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($Athlete['parent_name']); ?></p>
                                    <p class="text-sm text-black"><?php echo htmlspecialchars($Athlete['parent_phone']); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php
                                    echo $Athlete['status'] === 'active' ? 'bg-red-50 text-red-800' :
                                        ($Athlete['status'] === 'inactive' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800');
                                ?>">
                                    <?php 
                                        echo $Athlete['status'] === 'active' ? 'Activo' : 
                                            ($Athlete['status'] === 'inactive' ? 'Inactivo' : 'Suspendido'); 
                                    ?>
                                </span>
                                <?php if ($Athlete['status'] !== 'active' && empty($Athlete['withdrawn_at'])): ?>
                                <?php
                                    // 'status_reason' solo llega para administrador/secretario (el controlador lo quita para el resto).
                                    $tip = !empty($Athlete['status_reason']) ? $Athlete['status_reason'] : (array_key_exists('status_reason', $Athlete) ? 'Motivo no registrado' : '');
                                ?>
                                <p class="text-xs text-gray-500 mt-1"<?php echo $tip !== '' ? ' title="' . htmlspecialchars($tip) . '"' : ''; ?>>
                                    <?php if (!empty($Athlete['status_changed_at'])): ?>
                                        desde el <?php echo date('d/m/Y', strtotime($Athlete['status_changed_at'])); ?>
                                    <?php elseif (array_key_exists('status_reason', $Athlete)): ?>
                                        sin detalle registrado
                                    <?php endif; ?>
                                    <?php if ($tip !== ''): ?><i class="fas fa-info-circle ml-1" aria-hidden="true"></i><?php endif; ?>
                                </p>
                                <?php endif; ?>
                                <?php if (!empty($Athlete['withdrawn_at'])): ?>
                                <p class="text-xs font-semibold text-red-700 mt-1" title="Solvencia <?php echo htmlspecialchars($Athlete['clearance_number']); ?>">
                                    <i class="fas fa-file-signature"></i> Retirado <?php echo date('d/m/Y', strtotime($Athlete['withdrawn_at'])); ?>
                                </p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo BASE_URL; ?>athletes/view?id=<?php echo $Athlete['id']; ?>"
                                       class="text-red-700 hover:text-red-800 p-1" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    <?php if ($_SESSION['admin_role'] === 'administrador'): ?>
                                    <a href="<?php echo BASE_URL; ?>athletes/edit?id=<?php echo $Athlete['id']; ?>"
                                       class="text-red-600 hover:text-red-800 p-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if ($_SESSION['admin_role'] === 'administrador'): ?>
                                        <?php if (ALLOW_DELETE_ATHLETES): ?>
                                        <button onclick="deleteAthlete(<?php echo $Athlete['id']; ?>)"
                                                class="text-red-600 hover:text-red-800 p-1" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php else: ?>
                                        <button type="button" disabled aria-disabled="true"
                                                class="btn-disabled text-gray-400 p-1"
                                                title="Eliminar deshabilitado: la eliminación de atletas no está disponible">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
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

<!-- Modal: Clave de Operaciones para eliminar atleta -->
<div id="delete-athlete-modal" class="modal-overlay">
    <div class="modal-box">
        <!-- Ícono + título -->
        <div class="pin-modal__header">
            <div class="pin-modal__icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h3 class="pin-modal__title">Confirmar Eliminación</h3>
                <p class="pin-modal__subtitle">Clave de operaciones requerida</p>
            </div>
            <!-- Botón cerrar -->
            <button id="cancel-delete-athlete" class="modal-close" title="Cerrar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <p class="pin-modal__warning">
            Esta acción es <strong>permanente</strong> y no puede deshacerse.
        </p>

        <!-- Campo PIN -->
        <div class="pin-modal__field">
            <label class="pin-modal__label">
                <i class="fas fa-lock"></i>Clave de Operaciones
            </label>
            <div class="pin-modal__input-wrap">
                <input type="password" id="delete-athlete-pin" class="pin-modal__input"
                       maxlength="4" inputmode="numeric" placeholder="• • • •">
                <button type="button" id="toggle-athlete-pin" class="pin-modal__toggle">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            <p id="delete-athlete-error" class="pin-modal__error"></p>
        </div>

        <!-- Botones -->
        <div class="pin-modal__actions">
            <button id="confirm-delete-athlete" class="btn-danger">
                <i class="fas fa-trash"></i>Eliminar
            </button>
            <button id="cancel-delete-athlete-btn" class="btn-cancel">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let pendingAthleteId = null;
    const modal = document.getElementById('delete-athlete-modal');
    const errEl = document.getElementById('delete-athlete-error');
    const pinInput = document.getElementById('delete-athlete-pin');

    function showError(message) {
        errEl.textContent = message;
        errEl.classList.add('is-visible');
    }

    function hideError() {
        errEl.classList.remove('is-visible');
    }

    function closeModal() {
        modal.classList.remove('is-open');
        pendingAthleteId = null;
    }

    function openModal(id) {
        pendingAthleteId = id;
        pinInput.value = '';
        hideError();
        modal.classList.add('is-open');
        setTimeout(() => pinInput.focus(), 100);
    }

    window.deleteAthlete = openModal;

    // Close on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.getElementById('cancel-delete-athlete').addEventListener('click', closeModal);
    document.getElementById('cancel-delete-athlete-btn').addEventListener('click', closeModal);

    pinInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
        hideError();
    });

    pinInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('confirm-delete-athlete').click();
        if (e.key === 'Escape') closeModal();
    });

    document.getElementById('toggle-athlete-pin').addEventListener('click', function() {
        const type = pinInput.getAttribute('type') === 'password' ? 'text' : 'password';
        pinInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    document.getElementById('confirm-delete-athlete').addEventListener('click', function() {
        const pin = pinInput.value;

        if (!/^\d{4}$/.test(pin)) {
            showError('La clave debe ser de 4 dígitos numéricos.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Eliminando...';

        ajaxRequest('<?php echo BASE_URL; ?>athletes/delete', {id: pendingAthleteId, operation_key: pin}, function(response) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i>Eliminar';
            if (response.success) {
                closeModal();
                location.reload();
            } else {
                showError(response.message || 'Error al eliminar el atleta.');
                pinInput.value = '';
                pinInput.focus();
            }
        });
    });
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // Auto-submit cuando cambian los selectores
    document.getElementById('category').addEventListener('change', function() {
        form.submit();
    });
    
    document.getElementById('age').addEventListener('change', function() {
        form.submit();
    });
    
    document.getElementById('status').addEventListener('change', function() {
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
            searchInput.value = result.id_card || result.parent_id_card || '';
            // Auto-submit del formulario
            form.submit();
        }
    );
});
</script>