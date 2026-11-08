<?php
/**
 * Vista de Creación de Cronograma de Entrenamiento.
 * 
 * Permite registrar un plan de trabajo deportivo asignado a una categoría:
 * - Título, categoría destinataria (Infantil, Pre-juvenil, Juvenil, Junior) y entrenador responsable.
 * - Fechas de inicio y fin del plan de entrenamiento.
 * - Campos dinámicos para agregar múltiples sesiones semanales con día, hora inicio/fin, lugar e intensidad.
 * 
 * Variables recibidas desde TrainingController::create():
 * @var array|null  $schedule Datos del cronograma en caso de reintento.
 * @var array       $coaches  Lista de entrenadores disponibles.
 * @var string|null $error    Mensaje de validación o error.
 */
$categories  = ['Infantil','Pre-juvenil','Juvenil','Junior'];
$days        = ['lunes'=>'Lunes','martes'=>'Martes','miercoles'=>'Miércoles','jueves'=>'Jueves','viernes'=>'Viernes','sabado'=>'Sábado','domingo'=>'Domingo'];
$intensities = ['baja'=>'Baja','media'=>'Media','alta'=>'Alta'];
$isCoach     = ($_SESSION['admin_role'] === 'entrenador');
?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="<?php echo BASE_URL; ?>training" class="text-gray-400 hover:text-red-700 transition-colors">
            <i class="fas fa-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-calendar-plus text-red-700"></i>
                Nuevo Cronograma de Entrenamiento
            </h1>
            <p class="text-gray-500 text-sm mt-0.5">Complete la información del plan de entrenamiento</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
        <p class="text-red-600 text-sm"><i class="fas fa-exclamation-circle mr-2"></i><?php echo $error; ?></p>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>training/create" class="space-y-6" id="training-form">

        <!-- SECCIÓN 1: Información general -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-red-700 text-white text-xs flex items-center justify-center font-bold">1</span>
                Información General
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título del Cronograma *</label>
                    <input type="text" name="title" required maxlength="150"
                           value="<?php echo htmlspecialchars($data['title'] ?? ''); ?>"
                           placeholder="Ej: Plan de entrenamiento Juvenil – Temporada 2026"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                    <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                        <option value="">Seleccionar categoría...</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo (($data['category'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!$isCoach): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Entrenador *</label>
                    <select name="coach_id" id="coach_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                        <option value="">Seleccionar entrenador...</option>
                        <?php foreach ($coaches as $coach): ?>
                        <option value="<?php echo $coach['id']; ?>"
                                data-name="<?php echo htmlspecialchars($coach['full_name']); ?>"
                                <?php echo (($data['coach_id'] ?? 0) == $coach['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($coach['full_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="coach_name" id="coach_name" value="<?php echo htmlspecialchars($data['coach_name'] ?? ''); ?>">
                </div>
                <?php else: ?>
                <input type="hidden" name="coach_id"   value="<?php echo $_SESSION['admin_id']; ?>">
                <input type="hidden" name="coach_name" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lugar de Entrenamiento *</label>
                    <input type="text" name="location" required maxlength="150"
                           value="<?php echo htmlspecialchars($data['location'] ?? ''); ?>"
                           placeholder="Ej: Campo Principal Maporal"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio *</label>
                    <input type="date" name="start_date" required
                           value="<?php echo htmlspecialchars($data['start_date'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <input type="date" name="end_date"
                           value="<?php echo htmlspecialchars($data['end_date'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estatus</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                        <option value="activo"     <?php echo (($data['status'] ?? 'activo') === 'activo')     ? 'selected':''; ?>>Activo</option>
                        <option value="pausado"    <?php echo (($data['status'] ?? '') === 'pausado')    ? 'selected':''; ?>>Pausado</option>
                        <option value="finalizado" <?php echo (($data['status'] ?? '') === 'finalizado') ? 'selected':''; ?>>Finalizado</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Objetivo General</label>
                    <textarea name="objective" rows="3" maxlength="1000"
                              placeholder="Describa el objetivo principal del plan de entrenamiento..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent resize-none"><?php echo htmlspecialchars($data['objective'] ?? ''); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales</label>
                    <textarea name="notes" rows="2" maxlength="1000"
                              placeholder="Observaciones, recomendaciones, condiciones especiales..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent resize-none"><?php echo htmlspecialchars($data['notes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: Sesiones de entrenamiento -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-red-700 text-white text-xs flex items-center justify-center font-bold">2</span>
                    Horario de Sesiones
                </h2>
                <button type="button" id="add-session-btn"
                        class="flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium text-sm px-3 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus"></i> Agregar sesión
                </button>
            </div>

            <div id="sessions-container" class="space-y-3">
                <!-- Sesiones se agregan dinámicamente -->
            </div>

            <p id="no-sessions-msg" class="text-center text-gray-400 text-sm py-6">
                <i class="fas fa-clock text-2xl mb-2 block"></i>
                No hay sesiones. Haga clic en "Agregar sesión" para comenzar.
            </p>
        </div>

        <!-- Botones -->
        <div class="flex gap-4 pb-6">
            <button type="submit" class="flex-1 btn-primary text-white font-medium py-3 px-4 rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i>Guardar Cronograma
            </button>
            <a href="<?php echo BASE_URL; ?>training"
               class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors text-center">
                <i class="fas fa-times mr-2"></i>Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Template de sesión (hidden) -->
<template id="session-template">
    <div class="session-row border border-gray-200 rounded-xl p-4 bg-gray-50 relative">
        <button type="button" class="remove-session absolute top-3 right-3 text-gray-400 hover:text-red-600 transition-colors" title="Eliminar sesión">
            <i class="fas fa-times-circle"></i>
        </button>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Día *</label>
                <select name="session_day[]" required class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent">
                    <?php foreach ($days as $val => $label): ?>
                    <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Hora inicio *</label>
                <input type="time" name="session_start[]" required value="08:00"
                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Hora fin *</label>
                <input type="time" name="session_end[]" required value="09:00"
                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent">
            </div>
            <div class="col-span-2 md:col-span-1 lg:col-span-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Actividad *</label>
                <input type="text" name="session_activity[]" required maxlength="200"
                       placeholder="Ej: Bateo, Pitcheo, Fieldeo..."
                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Intensidad</label>
                <select name="session_intensity[]" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent">
                    <option value="baja">Baja</option>
                    <option value="media" selected>Media</option>
                    <option value="alta">Alta</option>
                </select>
            </div>
            <div class="col-span-2 lg:col-span-6">
                <label class="block text-xs font-medium text-gray-600 mb-1">Materiales / Equipamiento</label>
                <input type="text" name="session_materials[]" maxlength="255"
                       placeholder="Ej: Bates, pelotas, guantes, conos..."
                       class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent">
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container  = document.getElementById('sessions-container');
    const noMsg      = document.getElementById('no-sessions-msg');
    const template   = document.getElementById('session-template');
    const addBtn     = document.getElementById('add-session-btn');

    function updateVisibility() {
        noMsg.classList.toggle('hidden', container.children.length !== 0);
    }

    function addSession() {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('.session-row');
        row.querySelector('.remove-session').addEventListener('click', function() {
            row.remove();
            updateVisibility();
        });
        container.appendChild(clone);
        updateVisibility();
        // Scroll to new row
        container.lastElementChild.scrollIntoView({behavior:'smooth', block:'nearest'});
    }

    addBtn.addEventListener('click', addSession);

    // Sync coach_name from coach_id select
    const coachSelect = document.getElementById('coach_id');
    if (coachSelect) {
        coachSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            document.getElementById('coach_name').value = opt.dataset.name || '';
        });
    }

    updateVisibility();
});
</script>
