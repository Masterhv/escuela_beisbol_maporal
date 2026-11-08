<?php
$statusLabels = [
    'programado' => 'Programado',
    'en_curso'   => 'En Curso',
    'finalizado' => 'Finalizado',
    'cancelado'  => 'Cancelado',
];
$statusColors = [
    'programado' => 'bg-blue-100 text-blue-800',
    'en_curso'   => 'bg-green-100 text-green-800',
    'finalizado' => 'bg-gray-100 text-gray-700',
    'cancelado'  => 'bg-red-100 text-red-800',
];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-trophy text-red-700"></i>
                Cronograma de Torneos
            </h2>
            <p class="text-gray-600 mt-1">Gestiona los torneos y competencias de la escuela</p>
        </div>
        <?php if (in_array($_SESSION['admin_role'], ['administrador', 'secretario'])): ?>
        <a href="<?php echo BASE_URL; ?>tournaments/create"
           class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i> Nuevo Torneo
        </a>
        <?php endif; ?>
    </div>

    <!-- Alertas -->
    <?php if (isset($_GET['success'])): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
        <i class="fas fa-check-circle"></i>
        <?php
        $msgs = ['created' => 'Torneo registrado correctamente.', 'updated' => 'Torneo actualizado correctamente.', 'deleted' => 'Torneo eliminado correctamente.'];
        echo $msgs[$_GET['success']] ?? 'Operación realizada.';
        ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $statCards = [
            ['label' => 'Total',       'value' => $stats['total']       ?? 0, 'color' => 'text-gray-700',  'icon' => 'fa-trophy'],
            ['label' => 'Programados', 'value' => $stats['programados'] ?? 0, 'color' => 'text-blue-600',  'icon' => 'fa-calendar'],
            ['label' => 'En Curso',    'value' => $stats['en_curso']    ?? 0, 'color' => 'text-green-600', 'icon' => 'fa-play-circle'],
            ['label' => 'Finalizados', 'value' => $stats['finalizados'] ?? 0, 'color' => 'text-gray-500',  'icon' => 'fa-flag-checkered'],
        ];
        foreach ($statCards as $card):
        ?>
        <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                <i class="fas <?php echo $card['icon']; ?> <?php echo $card['color']; ?>"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900"><?php echo $card['value']; ?></p>
                <p class="text-xs text-gray-500"><?php echo $card['label']; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border p-4">
        <form method="GET" action="<?php echo BASE_URL; ?>tournaments" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>"
                       placeholder="Nombre, lugar o categoría..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-red-700">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Estatus</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">
                    <option value="">Todos</option>
                    <?php foreach ($statusLabels as $val => $lbl): ?>
                    <option value="<?php echo $val; ?>" <?php echo (($filters['status'] ?? '') === $val) ? 'selected' : ''; ?>><?php echo $lbl; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Categoría</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">
                    <option value="">Todas</option>
                    <?php foreach (['Infantil','Pre-juvenil','Juvenil','Junior','Todas las categorías'] as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo (($filters['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="min-w-[100px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Año</label>
                <input type="number" name="year" value="<?php echo htmlspecialchars($filters['year'] ?? ''); ?>"
                       placeholder="<?php echo date('Y'); ?>" min="2020" max="2099"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary text-white text-sm font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-search mr-1"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>tournaments" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de torneos -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <?php if (empty($tournaments)): ?>
        <div class="py-16 text-center text-gray-500">
            <i class="fas fa-trophy text-4xl text-gray-300 mb-3"></i>
            <p class="font-medium text-gray-700">No se encontraron torneos</p>
            <p class="text-sm mt-1">Ajusta los filtros o registra un nuevo torneo.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-px bg-gray-200">
            <?php foreach ($tournaments as $t): ?>
            <?php
                $sColor = $statusColors[$t['status']] ?? 'bg-gray-100 text-gray-700';
                $sLabel = $statusLabels[$t['status']] ?? $t['status'];
                $dateFrom = date('d/m/Y', strtotime($t['start_date']));
                $dateTo   = $t['end_date'] ? date('d/m/Y', strtotime($t['end_date'])) : 'Por definir';
            ?>
            <div class="bg-white p-5 flex flex-col gap-3 hover:bg-gray-50 transition-colors">
                <!-- Nombre + estatus -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-yellow-50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-trophy text-yellow-500 text-sm"></i>
                        </div>
                        <p class="font-semibold text-gray-900 text-sm leading-tight truncate"><?php echo htmlspecialchars($t['name']); ?></p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full flex-shrink-0 <?php echo $sColor; ?>">
                        <?php echo $sLabel; ?>
                    </span>
                </div>

                <!-- Detalles -->
                <div class="text-xs text-gray-500 space-y-1">
                    <p><i class="fas fa-tag w-3 mr-1"></i><?php echo htmlspecialchars($t['category']); ?></p>
                    <p><i class="fas fa-map-marker-alt w-3 mr-1"></i><?php echo htmlspecialchars($t['location']); ?></p>
                    <p><i class="fas fa-calendar w-3 mr-1"></i><?php echo $dateFrom; ?> — <?php echo $dateTo; ?></p>
                </div>

                <!-- Acciones -->
                <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
                    <a href="<?php echo BASE_URL; ?>tournaments/view?id=<?php echo $t['id']; ?>"
                       class="text-xs text-red-700 hover:text-red-900 font-medium flex items-center gap-1">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                    <?php if (in_array($_SESSION['admin_role'], ['administrador', 'secretario'])): ?>
                    <a href="<?php echo BASE_URL; ?>tournaments/edit?id=<?php echo $t['id']; ?>"
                       class="text-xs text-gray-600 hover:text-gray-900 font-medium flex items-center gap-1">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <?php endif; ?>
                    <?php if ($_SESSION['admin_role'] === 'administrador'): ?>
                    <button onclick="deleteTournament(<?php echo $t['id']; ?>)"
                            class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1 ml-auto">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal eliminar -->
<div id="delete-tournament-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="pin-modal__header pin-modal__header--spaced">
            <div class="pin-modal__icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h3 class="pin-modal__title">Confirmar Eliminación</h3>
                <p class="pin-modal__subtitle">Clave de operaciones requerida</p>
            </div>
            <button id="cancel-delete-tournament" class="modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="pin-modal__warning pin-modal__warning--tight">Esta acción es <strong>permanente</strong>. Se eliminará el torneo y todos sus participantes inscritos.</p>
        <div class="pin-modal__field pin-modal__field--tight">
            <label class="pin-modal__label">
                <i class="fas fa-lock"></i>Clave de Operaciones
            </label>
            <input type="password" id="delete-tournament-pin" class="pin-modal__input pin-modal__input--no-toggle"
                   maxlength="4" inputmode="numeric" placeholder="• • • •">
            <p id="delete-tournament-error" class="pin-modal__error"></p>
        </div>
        <div class="pin-modal__actions">
            <button id="confirm-delete-tournament" class="btn-danger">
                <i class="fas fa-trash"></i>Eliminar
            </button>
            <button id="cancel-delete-tournament-btn" class="btn-cancel">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let pendingId = null;
    const modal   = document.getElementById('delete-tournament-modal');
    const pinEl   = document.getElementById('delete-tournament-pin');
    const errEl   = document.getElementById('delete-tournament-error');

    function showError(message) {
        errEl.textContent = message;
        errEl.classList.add('is-visible');
    }
    function hideError() {
        errEl.classList.remove('is-visible');
    }

    function openModal(id) {
        pendingId = id;
        pinEl.value = '';
        hideError();
        modal.classList.add('is-open');
        setTimeout(() => pinEl.focus(), 100);
    }
    function closeModal() {
        modal.classList.remove('is-open');
        pendingId = null;
    }

    window.deleteTournament = openModal;
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.getElementById('cancel-delete-tournament').addEventListener('click', closeModal);
    document.getElementById('cancel-delete-tournament-btn').addEventListener('click', closeModal);

    pinEl.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g,'').slice(0,4);
        hideError();
    });
    pinEl.addEventListener('keydown', e => {
        if (e.key === 'Enter') document.getElementById('confirm-delete-tournament').click();
        if (e.key === 'Escape') closeModal();
    });

    document.getElementById('confirm-delete-tournament').addEventListener('click', function() {
        const pin = pinEl.value;
        if (!/^\d{4}$/.test(pin)) {
            showError('La clave debe ser de 4 dígitos.');
            return;
        }
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Eliminando...';

        ajaxRequest('<?php echo BASE_URL; ?>tournaments/delete', { id: pendingId, operation_key: pin }, function(response) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i>Eliminar';
            if (response.success) {
                closeModal();
                location.reload();
            } else {
                showError(response.message || 'Error al eliminar.');
                pinEl.value = '';
                pinEl.focus();
            }
        });
    });
})();
</script>
