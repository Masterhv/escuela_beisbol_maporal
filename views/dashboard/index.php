<?php
/**
 * Vista del Panel de Control (Dashboard).
 * 
 * Presenta el resumen ejecutivo del sistema adaptado al rol del usuario:
 * - Para Administradores / Entrenadores: Total de atletas, atletas activos, pagos pendientes
 *   y balance financiero global.
 * - Para Representantes: Conteo de atletas a su cargo y estatus de sus pagos.
 * - Accesos directos a las operaciones más frecuentes.
 * 
 * Variables recibidas desde DashboardController::index():
 * @var array $stats           Métricas y totales estadísticos.
 * @var bool  $isRepresentante Indica si la vista se personaliza para perfil de representante.
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h2>
            <p class="text-black"><?php echo !empty($isRepresentante) ? 'Resumen de tus atletas y pagos' : 'Resumen general del sistema'; ?></p>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="text-sm text-black">Última actualización: <?php echo date('d/m/Y H:i'); ?></span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="stats-card rounded-xl px-6 py-4 text-white card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100"><?php echo !empty($isRepresentante) ? 'Mis Atletas' : 'Total Atletas'; ?></p>
                    <p class="text-3xl font-bold mt-2"><?php echo $stats['totalAthletes']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>

        <?php if ($stats['activeAthletes'] !== null): ?>
        <div class="stats-card red rounded-xl px-6 py-4 text-white card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100">Atletas Activos</p>
                    <p class="text-3xl font-bold mt-2"><?php echo $stats['activeAthletes']; ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-user-check text-2xl"></i>
                </div>
            </div>
        </div>

        <?php endif; ?>

        <?php if (!empty($showMoney)): ?>
        <div class="stats-card dark rounded-xl px-6 py-4 text-white card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100">Por cobrar</p>
                    <p class="text-3xl font-bold mt-2"><?php echo (int)$stats['pendingPayments']; ?></p>
                    <p class="text-xs text-red-100 mt-1"><?php echo (int)$stats['inReviewPayments']; ?> en revisión</p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="stats-card rounded-xl px-6 py-4 text-white card-hover col-span-1 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100">Ingresos aprobados</p>
                    <p class="text-3xl font-bold mt-2">Bs. <?php echo number_format($stats['totalRevenue'], 2); ?></p>
                </div>
                <div class="bg-white bg-opacity-20 p-3 rounded-lg">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent athletes -->
        <div class="bg-white rounded-xl shadow-sm border p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-users text-red-700 mr-2"></i>
                    Atletas Recientes
                </h3>
                <a href="<?php echo BASE_URL; ?>athletes" class="text-red-700 hover:text-red-800 text-sm">Ver todos</a>
            </div>
            <div class="space-y-3">
                <?php foreach ($recentAthletes as $Athlete): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?></p>
                        <p class="text-sm text-black">Categoría: <?php echo htmlspecialchars($Athlete['category']); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-black"><?php echo date('d/m/Y', strtotime($Athlete['registration_date'])); ?></p>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo $Athlete['status'] === 'active' ? 'bg-red-50 text-red-800' : 'bg-gray-100 text-gray-800'; ?>">
                            <?php echo $Athlete['status'] === 'active' ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Payments (no se muestra al entrenador) -->
        <?php if (!empty($showMoney) || !empty($isRepresentante)): ?>
        <div class="bg-white rounded-xl shadow-sm border p-6 card-hover">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-credit-card text-red-700 mr-2"></i>
                    Pagos Recientes
                </h3>
                <a href="<?php echo BASE_URL; ?>payments" class="text-red-700 hover:text-red-800 text-sm">Ver todos</a>
            </div>
            <div class="space-y-3">
                <?php if (empty($recentPayments)): ?>
                <p class="text-sm text-gray-500">Aún no hay pagos registrados.</p>
                <?php endif; ?>
                <?php foreach ($recentPayments as $payment): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></p>
                        <p class="text-sm text-black"><?php echo htmlspecialchars($payment['month'] . ' ' . $payment['year']); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="font-medium text-gray-900">Bs. <?php echo number_format($payment['amount'], 2); ?></p>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo PaymentWorkflow::badge($payment['status']); ?>">
                            <?php echo PaymentWorkflow::label($payment['status']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>