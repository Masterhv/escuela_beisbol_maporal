<?php if (isset($data['Athlete']) && $data['Athlete']): $Athlete = $data['Athlete']; ?>
<div class="bg-white p-6 rounded-xl shadow-sm border">
    <h3 class="text-xl font-semibold text-gray-900 mb-4">Ficha del Atleta: <?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?></h3>
    
    <?php if (!empty($Athlete['photo']) && file_exists($Athlete['photo'])): ?>
    <div class="mb-6 flex justify-center">
        <div class="w-48 h-48 rounded-lg overflow-hidden border-4 border-blue-500 shadow-lg">
            <img src="<?php echo BASE_URL; ?>athletes/photo?id=<?php echo (int)$Athlete['id']; ?>" alt="Foto de <?php echo htmlspecialchars($Athlete['first_name']); ?>" class="w-full h-full object-cover">
        </div>
    </div>
    <?php endif; ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Personal Information -->
        <div>
            <h4 class="font-semibold text-lg text-gray-800 border-b pb-2 mb-4">Información Personal</h4>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?></dd>
                </div>
                <?php if (!empty($Athlete['id_card'])): ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Cédula</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['id_card']); ?></dd>
                </div>
                <?php endif; ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Fecha de Nacimiento</dt>
                    <dd class="mt-1 text-gray-900"><?php echo date('d/m/Y', strtotime($Athlete['date_of_birth'])); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Edad</dt>
                    <dd class="mt-1 text-gray-900"><?php echo $Athlete['age']; ?> años</dd>
                </div>
                 <div>
                    <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['address']); ?></dd>
                </div>
            </dl>
        </div>

        <!-- Contact Information -->
        <div>
            <h4 class="font-semibold text-lg text-gray-800 border-b pb-2 mb-4">Información de Contacto</h4>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nombre del Padre/Madre</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_name']); ?></dd>
                </div>
                <?php if (!empty($Athlete['parent_id_card'])): ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Cédula del Padre/Madre</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_id_card']); ?></dd>
                </div>
                <?php endif; ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Teléfono del Padre/Madre</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_phone']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email del Padre/Madre</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_email']); ?></dd>
                </div>
            </dl>
        </div>

        <!-- Academic & Medical Information -->
        <div>
            <h4 class="font-semibold text-lg text-gray-800 border-b pb-2 mb-4">Información Académica y Médica</h4>
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['category']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Número de Jersey</dt>
                    <dd class="mt-1 text-gray-900"><?php echo $Athlete['jersey_number'] ?? 'N/A'; ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Fecha de Registro</dt>
                    <dd class="mt-1 text-gray-900"><?php echo date('d/m/Y', strtotime($Athlete['registration_date'])); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Condiciones Médicas</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['medical_conditions']); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Contacto de Emergencia</dt>
                    <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['emergency_contact'] . ' (' . $Athlete['emergency_phone'] . ')'); ?></dd>
                </div>
            </dl>
        </div>
    </div>
</div>
<?php else: ?>
<p class="text-center text-gray-500 mt-6">Busque y seleccione un atleta para ver su ficha.</p>
<?php endif; ?>