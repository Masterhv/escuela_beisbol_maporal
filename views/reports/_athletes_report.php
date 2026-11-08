<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Stats -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas de Atletas</h3>
            <div class="space-y-4">
                <?php if (!empty($filters['category'])): ?>
                <div class="flex justify-between items-center bg-blue-50 p-3 rounded-lg border border-blue-200">
                    <span class="text-black font-semibold">Atletas en <?php echo htmlspecialchars($filters['category']); ?></span>
                    <span class="font-bold text-xl text-blue-600"><?php echo count($data['athletes']); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center">
                    <span class="text-black">Total de Atletas</span>
                    <span class="font-bold text-lg text-black"><?php echo $data['stats']['total']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-black">Activos</span>
                    <span class="font-bold text-lg text-green-600"><?php echo $data['stats']['active']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-black">Inactivos</span>
                    <span class="font-bold text-lg text-yellow-600"><?php echo $data['stats']['inactive']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-black">Suspendidos</span>
                    <span class="font-bold text-lg text-red-600"><?php echo $data['stats']['suspended']; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- athletes Table -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lista de Atletas</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Edad/Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Representante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Estatus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php
                    $statusTranslations = [
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                        'suspended' => 'Suspendido'
                    ];
                    $categoryRanges = [
                        'Infantil' => 'Infantil (8-10 años)',
                        'Pre-juvenil' => 'Pre-juvenil (11-12 años)',
                        'Juvenil' => 'Juvenil (13-15 años)',
                        'Junior' => 'Junior (16-18 años)'
                    ];
                    foreach ($data['athletes'] as $Athlete):
                    ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?></div>
                            <div class="text-sm text-black"><?php echo htmlspecialchars($Athlete['parent_email']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900"><?php echo htmlspecialchars($Athlete['age']); ?> años</div>
                            <div class="text-sm text-black">
                                <?php
                                $category = $Athlete['category'];
                                echo htmlspecialchars($categoryRanges[$category] ?? $category);
                                ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900"><?php echo htmlspecialchars($Athlete['parent_name']); ?></div>
                            <div class="text-sm text-black"><?php echo htmlspecialchars($Athlete['parent_phone']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php
                                if ($Athlete['status'] === 'active') echo 'bg-green-100 text-green-800';
                                elseif ($Athlete['status'] === 'inactive') echo 'bg-yellow-100 text-yellow-800';
                                else echo 'bg-red-100 text-red-800';
                            ?>">
                                <?php echo $statusTranslations[$Athlete['status']] ?? ucfirst($Athlete['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black"><?php echo date('d/m/Y', strtotime($Athlete['registration_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>