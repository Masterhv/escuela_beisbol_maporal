<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-edit text-yellow-600"></i> Editar Torneo
        </h2>
        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($tournament['name']); ?></p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        <ul class="list-disc list-inside text-sm space-y-1">
            <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Torneo *</label>
                    <input type="text" id="name" name="name" required
                           value="<?php echo htmlspecialchars($tournament['name']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                    <select id="category" name="category" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <?php foreach (['Infantil','Pre-juvenil','Juvenil','Junior','Todas las categorías'] as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($tournament['category'] === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Lugar *</label>
                    <input type="text" id="location" name="location" required
                           value="<?php echo htmlspecialchars($tournament['location']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio *</label>
                    <input type="date" id="start_date" name="start_date" required
                           value="<?php echo htmlspecialchars($tournament['start_date']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin <span class="text-gray-400 font-normal">(Opcional)</span></label>
                    <input type="date" id="end_date" name="end_date"
                           value="<?php echo htmlspecialchars($tournament['end_date'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Estatus *</label>
                    <select id="status" name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <option value="programado" <?php echo ($tournament['status'] === 'programado') ? 'selected' : ''; ?>>Programado</option>
                        <option value="en_curso"   <?php echo ($tournament['status'] === 'en_curso')   ? 'selected' : ''; ?>>En Curso</option>
                        <option value="finalizado" <?php echo ($tournament['status'] === 'finalizado') ? 'selected' : ''; ?>>Finalizado</option>
                        <option value="cancelado"  <?php echo ($tournament['status'] === 'cancelado')  ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción <span class="text-gray-400 font-normal">(Opcional)</span></label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700"><?php echo htmlspecialchars($tournament['description'] ?? ''); ?></textarea>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notas Internas <span class="text-gray-400 font-normal">(Opcional)</span></label>
                <textarea id="notes" name="notes" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700"><?php echo htmlspecialchars($tournament['notes'] ?? ''); ?></textarea>
            </div>

            <div class="flex justify-end gap-4 pt-2">
                <a href="<?php echo BASE_URL; ?>tournaments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">Cancelar</a>
                <button type="submit" class="btn-primary text-white font-medium py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
