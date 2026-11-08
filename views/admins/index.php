<div class="space-y-6">
    <!-- Error Message -->
    <?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline"><?php echo $error; ?></span>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2"><?php echo $title; ?></h2>
            <p class="text-black">Gestiona los usuarios con acceso al panel de administración.</p>
        </div>
        <?php if ($_SESSION['admin_role'] === 'administrador'): ?>
        <a href="<?php echo BASE_URL; ?>admins/create" class="btn-primary text-white font-bold py-2 px-4 rounded-lg inline-flex items-center mt-4 sm:mt-0">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Administrador
        </a>
        <?php endif; ?>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <form method="GET" action="<?php echo BASE_URL; ?>admins" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 items-end">
                <div class="relative">
                    <label for="admin_search" class="block text-sm font-medium text-black">Búsqueda por cédula</label>
                    <input type="text" id="admin_search" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="C.I. del administrador..." class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-red-700 focus:border-red-700 sm:text-sm rounded-md" autocomplete="off">
                    <div id="autocomplete-results-admin" class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 hidden shadow-lg"></div>
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-700">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Admins Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Nombre Completo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Cédula</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Edad</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Estatus</th>
                        <?php if (in_array($_SESSION['admin_role'], ['administrador', 'entrenador'])): ?>
                        <th class="px-6 py-3 text-center text-xs font-medium text-black uppercase tracking-wider">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                        $roles = [
                            'secretario' => 'Secretario',
                            'administrador' => 'Administrador',
                            'entrenador' => 'Entrenador',
                            'representante' => 'Representante'
                        ];
                    ?>
                    <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($admin['full_name']); ?></div>
                            <div class="text-sm text-black"><?php echo htmlspecialchars($admin['email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo htmlspecialchars($admin['id_card'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo htmlspecialchars($admin['phone'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo htmlspecialchars($admin['age'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            <?php
                                $role = strtolower($admin['role'] ?? '');
                                if ($role === 'administrador') {
                                    echo 'Administrador';
                                } elseif ($role === 'secretario') {
                                    echo 'Secretario';
                                } elseif ($role === 'entrenador') {
                                    echo 'Entrenador';
                                } elseif ($role === 'representante') {
                                    echo 'Representante';
                                } elseif (empty($role)) {
                                    echo 'No asignado';
                                } else {
                                    echo ucfirst(htmlspecialchars($admin['role']));
                                }
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $admin['status'] === 'active' ? 'bg-red-50 text-red-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $admin['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <?php if (in_array($_SESSION['admin_role'], ['administrador', 'entrenador'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?php echo BASE_URL; ?>admins/view?id=<?php echo $admin['id']; ?>" class="text-red-700 hover:text-red-800 p-1" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if ($_SESSION['admin_role'] === 'administrador'): ?>
                                <a href="<?php echo BASE_URL; ?>admins/edit?id=<?php echo $admin['id']; ?>" class="text-red-600 hover:text-red-800 p-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                <button onclick="deleteAdmin(<?php echo $admin['id']; ?>)" class="text-red-600 hover:text-red-800 p-1" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteAdmin(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este administrador?')) {
        fetch('<?php echo BASE_URL; ?>admins/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Búsqueda por cédula del administrador
    initAutocomplete(
        'admin_search',
        'autocomplete-results-admin',
        '<?php echo BASE_URL; ?>admins/autocompleteByIdCard',
        function(admin) {
            const role = admin.role === 'administrador' ? 'Administrador' :
                        admin.role === 'secretario' ? 'Secretario' :
                        admin.role === 'entrenador' ? 'Entrenador' :
                        admin.role === 'representante' ? 'Representante' : admin.role;
            const ci = admin.id_card ? `CI: ${admin.id_card}` : '';
            return {
                id: admin.id,
                text: `${admin.full_name} — ${ci} — ${role}`
            };
        }
    );
});
</script>