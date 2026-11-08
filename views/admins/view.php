<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user-shield text-red-700"></i>
                Detalles del Administrador
            </h2>
            <p class="text-black mt-1">Información completa de <?php echo htmlspecialchars($admin['full_name']); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>admins" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Volver a la lista
        </a>
    </div>

    <!-- Admin Details Card -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Información Personal -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Información Personal</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Nombre Completo</dt>
                        <dd class="mt-1 text-gray-900 font-medium"><?php echo htmlspecialchars($admin['full_name']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Cédula</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['id_card'] ?? 'No especificado'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Fecha de Nacimiento</dt>
                        <dd class="mt-1 text-gray-900"><?php echo !empty($admin['birth_date']) ? date('d/m/Y', strtotime($admin['birth_date'])) : 'No especificado'; ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Edad</dt>
                        <dd class="mt-1 text-gray-900">
                            <?php
                            if (!empty($admin['birth_date'])) {
                                $birthDate = new DateTime($admin['birth_date']);
                                $today = new DateTime('today');
                                echo $birthDate->diff($today)->y . ' años';
                            } else {
                                echo 'No especificado';
                            }
                            ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Teléfono</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['phone'] ?? 'No especificado'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Dirección</dt>
                        <dd class="mt-1 text-gray-900"><?php echo nl2br(htmlspecialchars($admin['address'] ?? 'No especificado')); ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Contacto de Emergencia -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Contacto de Emergencia</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Nombre del Contacto</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['emergency_contact_name'] ?? 'No especificado'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Parentesco</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['emergency_contact_relationship'] ?? 'No especificado'); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Teléfono del Contacto</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['emergency_contact_phone'] ?? 'No especificado'); ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Información de la Cuenta -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Información de la Cuenta</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Nombre de Usuario</dt>
                        <dd class="mt-1 text-gray-900 font-medium"><?php echo htmlspecialchars($admin['username']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Email</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($admin['email']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Rol</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">
                                <?php
                                $roleNames = [
                                    'administrador' => 'Administrador',
                                    'secretario' => 'Secretario',
                                    'entrenador' => 'Entrenador',
                                    'representante' => 'Representante'
                                ];
                                echo $roleNames[$admin['role']] ?? ucfirst($admin['role']);
                                ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Estatus</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?php echo $admin['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $admin['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Fecha de Creación</dt>
                        <dd class="mt-1 text-gray-900"><?php echo date('d/m/Y H:i', strtotime($admin['created_date'])); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700">Última Actualización</dt>
                        <dd class="mt-1 text-gray-900"><?php echo date('d/m/Y H:i', strtotime($admin['updated_at'])); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Acciones -->
        <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
            <a href="<?php echo BASE_URL; ?>admins/edit?id=<?php echo $admin['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <i class="fas fa-edit"></i>
                Editar Administrador
            </a>
            <a href="<?php echo BASE_URL; ?>admins" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Volver a Administradores
            </a>
        </div>
    </div>
</div>