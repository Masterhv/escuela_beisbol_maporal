<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Stats -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumen Financiero</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pagos Aprobados</span>
                    <span class="font-bold text-lg text-green-600"><?php echo $data['stats']['approved_payments'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">En Revisión</span>
                    <span class="font-bold text-lg text-blue-600"><?php echo $data['stats']['in_review_payments'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Pendientes</span>
                    <span class="font-bold text-lg text-yellow-600"><?php echo $data['stats']['pending_payments'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Rechazados</span>
                    <span class="font-bold text-lg text-red-600"><?php echo $data['stats']['rejected_payments'] ?? 0; ?></span>
                </div>
                <div class="flex justify-between items-center mt-4 pt-4 border-t">
                    <span class="text-gray-600 font-semibold">Ingresos aprobados</span>
                    <span class="font-bold text-xl text-gray-800">Bs. <?php echo number_format($data['stats']['total_revenue'], 2); ?></span>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ingresos Mensuales</h3>
            <div class="space-y-2">
                <?php 
                $months = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
                foreach ($data['monthlyRevenue'] as $revenue): ?>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600"><?php echo $months[(int)$revenue['month']] . ' ' . $revenue['year']; ?></span>
                    <span class="font-bold text-sm text-gray-800">Bs. <?php echo number_format($revenue['revenue'], 2); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalle de Pagos (Rango de Fechas)</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atleta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edad/Categoría</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto / Mes-Año</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Pago</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($data['payments'] as $payment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <p class="text-gray-900">
                                    <?php
                                    if (isset($payment['age']) && $payment['age'] !== null && $payment['age'] !== '') {
                                        echo htmlspecialchars($payment['age']) . ' años';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    <?php echo htmlspecialchars($payment['category'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div>
                                <p class="text-gray-900">
                                    Bs. <?php echo number_format($payment['amount'], 2); ?>
                                </p>
                                <p class="text-xs text-gray-600">
                                    <?php
                                    if (!empty($payment['month']) || !empty($payment['year'])) {
                                        echo htmlspecialchars(trim(($payment['month'] ?? '') . ' ' . ($payment['year'] ?? '')));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo PaymentWorkflow::badge($payment['status']); ?>">
                                <?php echo PaymentWorkflow::label($payment['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $payment['payment_date'] ? date('d/m/Y', strtotime($payment['payment_date'])) : 'N/A'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>