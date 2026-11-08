<?php if (isset($data['Athlete']) && $data['Athlete']): $Athlete = $data['Athlete']; $payments = $data['payments']; ?>
<div class="bg-white p-6 rounded-xl shadow-sm border">
    <h3 class="text-xl font-semibold text-gray-900 mb-4">Historial de Pagos de <?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?></h3>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mes/Año</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Pago</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Bs. <?php echo number_format($payment['amount'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($payment['month'] . '/' . $payment['year']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo PaymentWorkflow::badge($payment['status']); ?>">
                                <?php echo PaymentWorkflow::label($payment['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $payment['payment_date'] ? date('d/m/Y', strtotime($payment['payment_date'])) : 'N/A'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4 text-gray-500">No hay pagos registrados para este atleta.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php else: ?>
<p class="text-center text-gray-500 mt-6">Busque y seleccione un atleta para ver su historial de pagos.</p>
<?php endif; ?>