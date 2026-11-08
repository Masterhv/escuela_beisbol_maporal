<?php
$statusLabels = ['programado'=>'Programado','en_curso'=>'En Curso','finalizado'=>'Finalizado','cancelado'=>'Cancelado'];
$statusColors = ['programado'=>'bg-blue-100 text-blue-800','en_curso'=>'bg-green-100 text-green-800','finalizado'=>'bg-gray-100 text-gray-700','cancelado'=>'bg-red-100 text-red-800'];
$sLabel = $statusLabels[$tournament['status']] ?? $tournament['status'];
$sColor = $statusColors[$tournament['status']] ?? 'bg-gray-100 text-gray-700';
$canEdit = in_array($_SESSION['admin_role'], ['administrador','secretario']);
$noPhoto = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23e5e7eb'/%3E%3Ccircle cx='20' cy='16' r='7' fill='%239ca3af'/%3E%3Cellipse cx='20' cy='36' rx='12' ry='8' fill='%239ca3af'/%3E%3C/svg%3E";
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-trophy text-yellow-500"></i>
                    <?php echo htmlspecialchars($tournament['name']); ?>
                </h2>
                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?php echo $sColor; ?>">
                    <?php echo $sLabel; ?>
                </span>
            </div>
            <p class="text-gray-500 text-sm mt-1">
                <i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($tournament['category']); ?> &nbsp;·&nbsp;
                <i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($tournament['location']); ?>
            </p>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            <?php if ($canEdit): ?>
            <a href="<?php echo BASE_URL; ?>tournaments/edit?id=<?php echo $tournament['id']; ?>"
               class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>tournaments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg text-sm">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- Info general + participantes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna izquierda: detalles -->
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b pb-2">Información General</h3>
                <div class="text-sm space-y-2">
                    <div>
                        <span class="text-gray-500">Fecha de inicio:</span>
                        <span class="font-medium text-gray-900 ml-1"><?php echo date('d/m/Y', strtotime($tournament['start_date'])); ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Fecha de fin:</span>
                        <span class="font-medium text-gray-900 ml-1">
                            <?php echo $tournament['end_date'] ? date('d/m/Y', strtotime($tournament['end_date'])) : 'Por definir'; ?>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500">Participantes:</span>
                        <span class="font-medium text-gray-900 ml-1"><?php echo count($tournament['participants']); ?> atletas</span>
                    </div>
                    <?php if (!empty($tournament['creator_name'])): ?>
                    <div>
                        <span class="text-gray-500">Registrado por:</span>
                        <span class="font-medium text-gray-900 ml-1"><?php echo htmlspecialchars($tournament['creator_name']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($tournament['description'])): ?>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b pb-2 mb-3">Descripción</h3>
                <p class="text-sm text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($tournament['description'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($tournament['notes'])): ?>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b pb-2 mb-3">Notas Internas</h3>
                <p class="text-sm text-gray-600 leading-relaxed"><?php echo nl2br(htmlspecialchars($tournament['notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna derecha: participantes -->
        <div class="lg:col-span-2 space-y-4">

            <!-- Agregar participante -->
            <?php if ($canEdit && !empty($availableAthletes)): ?>
            <div class="bg-white rounded-xl shadow-sm border p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider border-b pb-2 mb-4">
                    <i class="fas fa-user-plus text-green-600 mr-1"></i>Inscribir Atleta
                </h3>
                <div class="flex gap-2 flex-wrap">
                    <select id="add-athlete-select" class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">
                        <option value="">Seleccionar atleta...</option>
                        <?php foreach ($availableAthletes as $a): ?>
                        <option value="<?php echo $a['id']; ?>">
                            <?php echo htmlspecialchars($a['last_name'] . ', ' . $a['first_name'] . ' — ' . $a['category']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button onclick="addParticipant()"
                            class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded-lg flex items-center gap-1">
                        <i class="fas fa-plus"></i>Inscribir
                    </button>
                </div>
                <p id="add-participant-error" class="text-xs text-red-600 mt-2 hidden"></p>
            </div>
            <?php endif; ?>

            <!-- Lista de participantes -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                        <i class="fas fa-users text-blue-600 mr-1"></i>Atletas Inscritos
                        <span class="ml-2 bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded-full" id="participant-count">
                            <?php echo count($tournament['participants']); ?>
                        </span>
                    </h3>
                </div>

                <?php if (empty($tournament['participants'])): ?>
                <div class="py-10 text-center text-gray-400">
                    <i class="fas fa-users text-3xl mb-2"></i>
                    <p class="text-sm">No hay atletas inscritos aún.</p>
                </div>
                <?php else: ?>
                <div id="participants-list">
                    <?php foreach ($tournament['participants'] as $p): ?>
                    <div class="participant-row flex items-center gap-3 px-5 py-3 border-b border-gray-100 hover:bg-gray-50" data-athlete-id="<?php echo $p['athlete_id']; ?>">
                        <img src="<?php echo $p['photo'] ? BASE_URL . 'athletes/photo?id=' . (int)$p['athlete_id'] : $noPhoto; ?>"
                             alt="<?php echo htmlspecialchars($p['first_name']); ?>"
                             onerror="this.src='<?php echo $noPhoto; ?>'"
                             class="w-9 h-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['last_name'] . ', ' . $p['first_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($p['category']); ?> · <?php echo $p['age']; ?> años</p>
                        </div>
                        <?php if ($canEdit): ?>
                        <button onclick="removeParticipant(<?php echo $tournament['id']; ?>, <?php echo $p['athlete_id']; ?>, this)"
                                class="text-red-400 hover:text-red-600 p-1 rounded" title="Retirar del torneo">
                            <i class="fas fa-user-minus text-xs"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const TOURNAMENT_ID = <?php echo (int)$tournament['id']; ?>;
const BASE = '<?php echo BASE_URL; ?>';
const NO_PHOTO = "<?php echo $noPhoto; ?>";

function addParticipant() {
    const sel = document.getElementById('add-athlete-select');
    const errEl = document.getElementById('add-participant-error');
    const athleteId = sel.value;
    if (!athleteId) {
        errEl.textContent = 'Selecciona un atleta.';
        errEl.classList.remove('hidden');
        return;
    }
    errEl.classList.add('hidden');

    ajaxRequest(BASE + 'tournaments/addParticipant', { tournament_id: TOURNAMENT_ID, athlete_id: athleteId }, function(res) {
        if (res.success) {
            const a = res.athlete;
            const list = document.getElementById('participants-list') || (() => {
                const d = document.createElement('div');
                d.id = 'participants-list';
                document.querySelector('.bg-white.rounded-xl.shadow-sm.border.overflow-hidden').appendChild(d);
                // Remove empty state div if present
                const empty = document.querySelector('#participants-list').previousElementSibling;
                if (empty && empty.classList.contains('py-10')) empty.remove();
                return d;
            })();

            // Se pide por el id del atleta al endpoint autenticado (la ruta del archivo ya no es accesible)
            const photo = a.photo ? BASE + 'athletes/photo?id=' + encodeURIComponent(a.id) : NO_PHOTO;
            const row = document.createElement('div');
            row.className = 'participant-row flex items-center gap-3 px-5 py-3 border-b border-gray-100 hover:bg-gray-50';
            row.dataset.athleteId = a.id;
            row.innerHTML = `
                <img src="${photo}" onerror="this.src='${NO_PHOTO}'" class="w-9 h-9 rounded-full object-cover border border-gray-200 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${a.last_name || ''}, ${a.first_name}</p>
                    <p class="text-xs text-gray-500">${a.category} · ${a.age} años</p>
                </div>
                <button onclick="removeParticipant(${TOURNAMENT_ID}, ${a.id}, this)" class="text-red-400 hover:text-red-600 p-1 rounded" title="Retirar del torneo">
                    <i class="fas fa-user-minus text-xs"></i>
                </button>`;

            // Remove empty state if present
            const emptyState = document.querySelector('.py-10.text-center');
            if (emptyState) emptyState.remove();

            document.getElementById('participants-list') ? document.getElementById('participants-list').appendChild(row)
                : (() => { const d = document.createElement('div'); d.id='participants-list'; d.appendChild(row); document.querySelector('.overflow-hidden').appendChild(d); })();

            // Remove selected option
            sel.querySelector(`option[value="${a.id}"]`).remove();
            sel.value = '';

            // Update count
            const countEl = document.getElementById('participant-count');
            if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;
        } else {
            errEl.textContent = res.message || 'Error al inscribir.';
            errEl.classList.remove('hidden');
        }
    });
}

function removeParticipant(tournamentId, athleteId, btn) {
    if (!confirm('¿Retirar a este atleta del torneo?')) return;
    ajaxRequest(BASE + 'tournaments/removeParticipant', { tournament_id: tournamentId, athlete_id: athleteId }, function(res) {
        if (res.success) {
            const row = btn.closest('.participant-row');
            row.remove();
            // Update count
            const countEl = document.getElementById('participant-count');
            if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
        } else {
            alert(res.message || 'Error al retirar al atleta.');
        }
    });
}
</script>
