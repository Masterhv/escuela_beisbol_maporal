<?php
$days = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
$dayFull = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];
$intensityLabel = ['baja'=>'Baja','media'=>'Media','alta'=>'Alta'];
$intensityBadge = ['baja'=>'bg-green-100 text-green-800','media'=>'bg-yellow-100 text-yellow-800','alta'=>'bg-red-100 text-red-800'];
$statusBadge    = ['activo'=>'bg-green-100 text-green-800','pausado'=>'bg-yellow-100 text-yellow-800','finalizado'=>'bg-gray-100 text-gray-600'];

$sessions      = $schedule['sessions'] ?? [];
$sessionsByDay = [];
foreach ($sessions as $s) { $sessionsByDay[$s['day_of_week']][] = $s; }

$canEdit = in_array($_SESSION['admin_role'], ['administrador','secretario']) || ($schedule['coach_id'] == $_SESSION['admin_id']);
?>

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="<?php echo BASE_URL; ?>training" class="text-gray-400 hover:text-red-700 mt-1 transition-colors">
                <i class="fas fa-arrow-left text-lg"></i>
            </a>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($schedule['title']); ?></h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo $statusBadge[$schedule['status']] ?? 'bg-gray-100 text-gray-600'; ?>">
                        <?php echo ucfirst($schedule['status']); ?>
                    </span>
                </div>
                <p class="text-gray-500 text-sm mt-0.5">
                    <?php echo htmlspecialchars($schedule['category']); ?> &middot;
                    <?php echo htmlspecialchars($schedule['location']); ?>
                </p>
            </div>
        </div>
        <?php if ($canEdit): ?>
        <div class="flex gap-2 flex-shrink-0">
            <a href="<?php echo BASE_URL; ?>training/edit?id=<?php echo $schedule['id']; ?>"
               class="flex items-center gap-2 btn-primary text-white font-medium text-sm py-2 px-4 rounded-lg">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <i class="fas fa-check-circle"></i> Cambios guardados exitosamente.
    </div>
    <?php endif; ?>

    <!-- Información general -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border p-5 md:col-span-2 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-info-circle text-red-700"></i> Información General
            </h2>
            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Entrenador</p>
                    <p class="text-gray-900 font-medium mt-0.5"><?php echo htmlspecialchars($schedule['coach_full_name'] ?? $schedule['coach_name']); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Categoría</p>
                    <p class="text-gray-900 font-medium mt-0.5"><?php echo htmlspecialchars($schedule['category']); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Lugar</p>
                    <p class="text-gray-900 mt-0.5"><?php echo htmlspecialchars($schedule['location']); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Período</p>
                    <p class="text-gray-900 mt-0.5">
                        <?php echo date('d/m/Y', strtotime($schedule['start_date'])); ?>
                        <?php if ($schedule['end_date']): ?> — <?php echo date('d/m/Y', strtotime($schedule['end_date'])); ?><?php else: ?> <span class="text-gray-400">en curso</span><?php endif; ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Total de sesiones</p>
                    <p class="text-gray-900 font-bold text-lg mt-0.5"><?php echo count($sessions); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Creado</p>
                    <p class="text-gray-900 mt-0.5"><?php echo date('d/m/Y H:i', strtotime($schedule['created_at'])); ?></p>
                </div>
            </div>
            <?php if (!empty($schedule['objective'])): ?>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Objetivo General</p>
                <p class="text-sm text-gray-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($schedule['objective'])); ?></p>
            </div>
            <?php endif; ?>
            <?php if (!empty($schedule['notes'])): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p class="text-xs text-yellow-700 font-semibold uppercase tracking-wide mb-1"><i class="fas fa-sticky-note mr-1"></i>Notas</p>
                <p class="text-sm text-yellow-900 leading-relaxed"><?php echo nl2br(htmlspecialchars($schedule['notes'])); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Mini stats -->
        <div class="space-y-3">
            <?php
            $byIntensity = ['baja'=>0,'media'=>0,'alta'=>0];
            $totalMinutes = 0;
            foreach ($sessions as $s) {
                $byIntensity[$s['intensity']] = ($byIntensity[$s['intensity']] ?? 0) + 1;
                $start = strtotime($s['start_time']);
                $end   = strtotime($s['end_time']);
                $totalMinutes += max(0, ($end - $start) / 60);
            }
            $totalHours = floor($totalMinutes / 60);
            $remMins    = $totalMinutes % 60;
            ?>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 uppercase font-medium mb-2">Horas semanales</p>
                <p class="text-3xl font-bold text-red-700"><?php echo $totalHours; ?>h <?php echo $remMins > 0 ? $remMins.'min' : ''; ?></p>
                <p class="text-xs text-gray-400 mt-1"><?php echo count($sessions); ?> sesión<?php echo count($sessions) !== 1 ? 'es' : ''; ?> programada<?php echo count($sessions) !== 1 ? 's' : ''; ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4">
                <p class="text-xs text-gray-500 uppercase font-medium mb-3">Por intensidad</p>
                <?php foreach (['baja'=>'Baja','media'=>'Media','alta'=>'Alta'] as $k => $l): ?>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs <?php echo ['baja'=>'text-green-700','media'=>'text-yellow-700','alta'=>'text-red-700'][$k]; ?> font-medium"><?php echo $l; ?></span>
                    <span class="text-sm font-bold text-gray-700"><?php echo $byIntensity[$k]; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Calendario semanal de sesiones -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-calendar-week text-red-700"></i>
                Horario Semanal
            </h2>
            <span class="text-xs text-gray-400"><?php echo count($sessions); ?> sesión<?php echo count($sessions)!==1?'es':''; ?></span>
        </div>

        <?php if (empty($sessions)): ?>
        <div class="p-10 text-center text-gray-400">
            <i class="fas fa-calendar-times text-4xl mb-3 block"></i>
            <p class="text-sm">No hay sesiones registradas en este cronograma.</p>
            <?php if ($canEdit): ?>
            <a href="<?php echo BASE_URL; ?>training/edit?id=<?php echo $schedule['id']; ?>"
               class="mt-3 inline-block btn-primary text-white text-sm font-medium py-2 px-4 rounded-lg">
                Agregar sesiones
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-7 divide-x divide-gray-100">
            <?php foreach ($days as $day): ?>
            <div class="min-w-0">
                <div class="bg-gray-50 px-2 py-2.5 text-center border-b border-gray-100 sticky top-0">
                    <p class="text-xs font-bold text-gray-600 uppercase"><?php echo ['lunes'=>'Lun','martes'=>'Mar','miercoles'=>'Mié','jueves'=>'Jue','viernes'=>'Vie','sabado'=>'Sáb','domingo'=>'Dom'][$day]; ?></p>
                </div>
                <div class="p-1.5 space-y-1.5 min-h-24">
                    <?php if (!empty($sessionsByDay[$day])): ?>
                        <?php foreach ($sessionsByDay[$day] as $s): ?>
                        <?php $bc = $intensityBadge[$s['intensity']] ?? 'bg-gray-100 text-gray-700';
                              $borderC = ['baja'=>'border-green-400','media'=>'border-yellow-400','alta'=>'border-red-400'][$s['intensity']] ?? 'border-gray-300';
                        ?>
                        <div class="rounded-lg border-l-4 <?php echo $borderC; ?> bg-gray-50 px-2 py-1.5 group relative"
                             title="<?php echo htmlspecialchars($s['activity']); ?>">
                            <p class="text-xs font-semibold text-gray-800 leading-tight truncate"><?php echo htmlspecialchars(mb_strimwidth($s['activity'],0,24,'…')); ?></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <?php echo substr($s['start_time'],0,5); ?>–<?php echo substr($s['end_time'],0,5); ?>
                            </p>
                            <?php if (!empty($s['materials'])): ?>
                            <p class="text-xs text-gray-400 truncate mt-0.5" title="<?php echo htmlspecialchars($s['materials']); ?>">
                                <i class="fas fa-box-open mr-0.5"></i><?php echo htmlspecialchars(mb_strimwidth($s['materials'],0,20,'…')); ?>
                            </p>
                            <?php endif; ?>
                            <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-xs font-medium <?php echo $bc; ?>">
                                <?php echo $intensityLabel[$s['intensity']]; ?>
                            </span>
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
        <div class="border-t border-gray-100 px-4 py-2 flex flex-wrap items-center gap-4 text-xs text-gray-500">
            <span class="font-medium">Intensidad:</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-l-2 border-green-400 inline-block bg-green-50"></span>Baja</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-l-2 border-yellow-400 inline-block bg-yellow-50"></span>Media</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded border-l-2 border-red-400 inline-block bg-red-50"></span>Alta</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lista detallada de sesiones -->
    <?php if (!empty($sessions)): ?>
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <i class="fas fa-list text-red-700"></i>
                Detalle de Sesiones
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Día</th>
                        <th class="px-4 py-3 text-left">Horario</th>
                        <th class="px-4 py-3 text-left">Actividad</th>
                        <th class="px-4 py-3 text-left">Intensidad</th>
                        <th class="px-4 py-3 text-left">Materiales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($sessions as $s): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo $dayFull[$s['day_of_week']] ?? ucfirst($s['day_of_week']); ?></td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                            <?php echo substr($s['start_time'],0,5); ?> – <?php echo substr($s['end_time'],0,5); ?>
                        </td>
                        <td class="px-4 py-3 text-gray-800"><?php echo htmlspecialchars($s['activity']); ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $intensityBadge[$s['intensity']] ?? 'bg-gray-100 text-gray-700'; ?>">
                                <?php echo $intensityLabel[$s['intensity']] ?? ucfirst($s['intensity']); ?>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs"><?php echo htmlspecialchars($s['materials'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>
