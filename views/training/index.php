<?php
$days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayLabels = ['lunes'=>'Lun','martes'=>'Mar','miercoles'=>'Mié','jueves'=>'Jue','viernes'=>'Vie','sabado'=>'Sáb','domingo'=>'Dom'];
$dayFull   = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];
$categories = ['Infantil','Pre-juvenil','Juvenil','Junior'];

// Collect all sessions grouped by day for the weekly calendar
$allSessions = [];
foreach ($schedules as $s) {
    foreach (($s['sessions'] ?? []) as $sess) {
        $allSessions[$sess['day_of_week']][] = array_merge($sess, ['schedule_title' => $s['title'], 'schedule_id' => $s['id'], 'category' => $s['category']]);
    }
}

$isRepresentante = $isRepresentante ?? false;
?>

<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-red-700"></i>
                <?php echo $isRepresentante ? 'Horario de Entrenamientos' : 'Cronogramas de Entrenamiento'; ?>
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                <?php echo $isRepresentante
                    ? 'Consulte los días y horarios de entrenamiento de su hijo'
                    : 'Planificación semanal de actividades deportivas'; ?>
            </p>
        </div>
        <?php if (in_array($_SESSION['admin_role'], ['administrador','secretario','entrenador'])): ?>
        <a href="<?php echo BASE_URL; ?>training/create"
           class="btn-primary text-white font-medium py-2 px-4 rounded-lg flex items-center gap-2 self-start">
            <i class="fas fa-plus"></i> Nuevo Cronograma
        </a>
        <?php endif; ?>
    </div>

    <!-- Aviso informativo para representantes -->
    <?php if ($isRepresentante): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3">
        <i class="fas fa-info-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
        <p class="text-sm text-blue-800">
            Aquí puede ver los cronogramas de entrenamiento <strong>activos</strong>.
            Haga clic en <strong>Ver detalle</strong> para consultar el horario completo día por día de cada categoría.
        </p>
    </div>
    <?php endif; ?>

    <!-- Alertas -->
    <?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center gap-2 text-sm">
        <i class="fas fa-check-circle"></i>
        <?php echo $success === 'created' ? 'Cronograma creado exitosamente.' : 'Cronograma actualizado exitosamente.'; ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 flex items-center gap-2 text-sm">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <!-- Estadísticas (ocultas para representantes) -->
    <?php if (!$isRepresentante): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $statCards = [
            ['label'=>'Total',       'value'=>$stats['total']??0,       'icon'=>'fa-calendar-alt', 'color'=>'text-red-700'],
            ['label'=>'Activos',     'value'=>$stats['activos']??0,     'icon'=>'fa-play-circle',  'color'=>'text-green-700'],
            ['label'=>'Pausados',    'value'=>$stats['pausados']??0,    'icon'=>'fa-pause-circle', 'color'=>'text-yellow-600'],
            ['label'=>'Finalizados', 'value'=>$stats['finalizados']??0, 'icon'=>'fa-flag-checkered','color'=>'text-gray-500'],
        ];
        foreach ($statCards as $c): ?>
        <div class="bg-white rounded-xl shadow-sm border p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
                <i class="fas <?php echo $c['icon']; ?> <?php echo $c['color']; ?>"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500"><?php echo $c['label']; ?></p>
                <p class="text-2xl font-bold text-gray-900"><?php echo $c['value']; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Filtros (simplificado para representantes) -->
    <div class="bg-white rounded-xl shadow-sm border p-4">
        <form method="GET" action="<?php echo BASE_URL; ?>training"
              class="grid grid-cols-1 <?php echo $isRepresentante ? 'sm:grid-cols-2' : 'sm:grid-cols-2 md:grid-cols-4'; ?> gap-3">
            <input type="text" name="q" placeholder="Buscar por título, lugar..." value="<?php echo htmlspecialchars($filters['q']); ?>"
                   class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-red-700">
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">
                <option value="">Todas las categorías</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat; ?>" <?php echo ($filters['category'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!$isRepresentante): ?>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700">
                <option value="">Todos los estados</option>
                <option value="activo"     <?php echo ($filters['status']==='activo')     ? 'selected':''; ?>>Activo</option>
                <option value="pausado"    <?php echo ($filters['status']==='pausado')    ? 'selected':''; ?>>Pausado</option>
                <option value="finalizado" <?php echo ($filters['status']==='finalizado') ? 'selected':''; ?>>Finalizado</option>
            </select>
            <?php endif; ?>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 btn-primary text-white text-sm font-medium py-2 px-3 rounded-lg">
                    <i class="fas fa-search mr-1"></i>Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>training" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-3 rounded-lg text-center transition-colors">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Vista en pestañas: Lista / Calendario (calendario por defecto para representantes) -->
    <div x-data="{ tab: '<?php echo $isRepresentante ? 'calendario' : 'lista'; ?>' }">
        <div class="flex gap-1 bg-gray-100 rounded-lg p-1 w-fit mb-4">
            <button @click="tab='lista'" :class="tab==='lista' ? 'bg-white shadow text-red-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 rounded-md text-sm transition-all">
                <i class="fas fa-list mr-1"></i>Lista
            </button>
            <button @click="tab='calendario'" :class="tab==='calendario' ? 'bg-white shadow text-red-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-1.5 rounded-md text-sm transition-all">
                <i class="fas fa-calendar-week mr-1"></i>Calendario semanal
            </button>
        </div>

        <!-- TAB: Lista -->
        <div x-show="tab==='lista'">
            <?php if (empty($schedules)): ?>
            <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
                <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 font-medium">No hay cronogramas registrados</p>
                <?php if (in_array($_SESSION['admin_role'], ['administrador','secretario','entrenador'])): ?>
                <a href="<?php echo BASE_URL; ?>training/create" class="mt-4 inline-block btn-primary text-white text-sm font-medium py-2 px-4 rounded-lg">
                    Crear primer cronograma
                </a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($schedules as $s):
                    $statusColor = ['activo'=>'bg-green-100 text-green-800','pausado'=>'bg-yellow-100 text-yellow-800','finalizado'=>'bg-gray-100 text-gray-600'];
                    $sc = $statusColor[$s['status']] ?? 'bg-gray-100 text-gray-600';
                    $sessionCount = 0;
                    // Count sessions
                    $sessRows = [];
                    foreach ($days as $d) $sessRows[$d] = [];
                ?>
                <div class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow flex flex-col">
                    <div class="p-5 flex-1">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-base truncate"><?php echo htmlspecialchars($s['title']); ?></h3>
                                <p class="text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($s['category']); ?></p>
                            </div>
                            <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0 <?php echo $sc; ?>">
                                <?php echo ucfirst($s['status']); ?>
                            </span>
                        </div>

                        <div class="space-y-1.5 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-tie text-red-700 w-4"></i>
                                <span class="truncate"><?php echo htmlspecialchars($s['coach_full_name'] ?? $s['coach_name']); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-map-marker-alt text-red-700 w-4"></i>
                                <span class="truncate"><?php echo htmlspecialchars($s['location']); ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar text-red-700 w-4"></i>
                                <span><?php echo date('d/m/Y', strtotime($s['start_date'])); ?>
                                <?php if ($s['end_date']): ?> — <?php echo date('d/m/Y', strtotime($s['end_date'])); ?><?php endif; ?></span>
                            </div>
                        </div>

                        <?php if (!empty($s['objective'])): ?>
                        <p class="mt-3 text-xs text-gray-500 line-clamp-2"><?php echo htmlspecialchars($s['objective']); ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="border-t border-gray-100 px-5 py-3 flex items-center justify-between">
                        <span class="text-xs text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            Creado <?php echo date('d/m/Y', strtotime($s['created_at'])); ?>
                        </span>
                        <div class="flex items-center gap-2">
                            <a href="<?php echo BASE_URL; ?>training/view?id=<?php echo $s['id']; ?>"
                               class="text-red-700 hover:text-red-800 p-1" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (in_array($_SESSION['admin_role'], ['administrador','secretario']) || ($s['coach_id'] == $_SESSION['admin_id'])): ?>
                            <a href="<?php echo BASE_URL; ?>training/edit?id=<?php echo $s['id']; ?>"
                               class="text-red-600 hover:text-red-800 p-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($_SESSION['admin_role'] === 'administrador' || ($s['coach_id'] == $_SESSION['admin_id'] && $_SESSION['admin_role'] === 'entrenador')): ?>
                            <button onclick="deleteSchedule(<?php echo $s['id']; ?>, '<?php echo htmlspecialchars(addslashes($s['title'])); ?>')"
                                    class="text-red-600 hover:text-red-800 p-1" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- TAB: Calendario semanal -->
        <div x-show="tab==='calendario'" x-cloak>
            <?php
            // Recolectar todas las sesiones de todos los cronogramas activos
            $calSessions = [];
            foreach ($schedules as $s) {
                if ($s['status'] === 'finalizado') continue;
                $sessions = (new TrainingSchedule())->getSessionsBySchedule($s['id']);
                foreach ($sessions as $sess) {
                    $calSessions[$sess['day_of_week']][] = [
                        'id'           => $sess['id'],
                        'schedule_id'  => $s['id'],
                        'title'        => $s['title'],
                        'category'     => $s['category'],
                        'start_time'   => $sess['start_time'],
                        'end_time'     => $sess['end_time'],
                        'activity'     => $sess['activity'],
                        'intensity'    => $sess['intensity'],
                        'location'     => $s['location'],
                    ];
                }
            }
            $intensityColor = ['baja'=>'border-green-400 bg-green-50','media'=>'border-yellow-400 bg-yellow-50','alta'=>'border-red-400 bg-red-50'];
            ?>
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <div class="grid grid-cols-7 divide-x divide-gray-100">
                    <?php foreach ($days as $day): ?>
                    <div class="min-w-0">
                        <div class="bg-gray-50 px-2 py-3 text-center border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wide"><?php echo $dayLabels[$day]; ?></p>
                        </div>
                        <div class="p-1.5 space-y-1.5 min-h-32">
                            <?php if (!empty($calSessions[$day])): ?>
                                <?php
                                usort($calSessions[$day], fn($a,$b) => strcmp($a['start_time'],$b['start_time']));
                                foreach ($calSessions[$day] as $ev):
                                    $ic = $intensityColor[$ev['intensity']] ?? 'border-gray-300 bg-gray-50';
                                ?>
                                <div class="rounded-lg border-l-4 px-2 py-1.5 <?php echo $ic; ?> cursor-pointer hover:opacity-80 transition-opacity"
                                     title="<?php echo htmlspecialchars($ev['activity']); ?>"
                                     onclick="window.location='<?php echo BASE_URL; ?>training/view?id=<?php echo $ev['schedule_id']; ?>'">
                                    <p class="text-xs font-bold text-gray-800 leading-tight truncate"><?php echo htmlspecialchars(mb_strimwidth($ev['activity'],0,22,'…')); ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        <?php echo substr($ev['start_time'],0,5); ?>–<?php echo substr($ev['end_time'],0,5); ?>
                                    </p>
                                    <p class="text-xs text-gray-400 truncate"><?php echo htmlspecialchars($ev['category']); ?></p>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-xs text-gray-300 text-center py-4">—</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Leyenda -->
                <div class="border-t border-gray-100 px-4 py-2 flex items-center gap-4 text-xs text-gray-500">
                    <span class="font-medium">Intensidad:</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-l-4 border-green-400 bg-green-50 inline-block"></span>Baja</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-l-4 border-yellow-400 bg-yellow-50 inline-block"></span>Media</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-l-4 border-red-400 bg-red-50 inline-block"></span>Alta</span>
                </div>
            </div>
        </div>
    </div><!-- /x-data -->
</div>

<!-- Modal confirmar eliminación -->
<div id="delete-schedule-modal" class="modal-overlay">
    <div class="modal-box modal-box--md">
        <div class="modal-header">
            <div class="modal-icon-danger">
                <i class="fas fa-trash"></i>
            </div>
            <div>
                <h3 class="modal-title">Eliminar Cronograma</h3>
                <p id="delete-schedule-name" class="modal-subtitle"></p>
            </div>
        </div>
        <p class="modal-text">Se eliminarán también todas las sesiones asociadas. Esta acción no puede deshacerse.</p>
        <div class="modal-actions">
            <button id="confirm-delete-schedule" class="btn-danger">
                <i class="fas fa-trash"></i>Eliminar
            </button>
            <button id="cancel-delete-schedule" class="btn-cancel">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    let pendingId = null;
    const modal = document.getElementById('delete-schedule-modal');

    window.deleteSchedule = function(id, title) {
        pendingId = id;
        document.getElementById('delete-schedule-name').textContent = title;
        modal.classList.add('is-open');
    };

    modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });
    document.getElementById('cancel-delete-schedule').addEventListener('click', closeModal);

    function closeModal() { modal.classList.remove('is-open'); pendingId = null; }

    document.getElementById('confirm-delete-schedule').addEventListener('click', function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Eliminando...';
        ajaxRequest('<?php echo BASE_URL; ?>training/delete', {id: pendingId}, function(r) {
            if (r.success) { closeModal(); location.reload(); }
            else { alert(r.message || 'Error al eliminar.'); btn.disabled = false; btn.innerHTML = '<i class="fas fa-trash"></i>Eliminar'; }
        });
    });
})();
</script>
