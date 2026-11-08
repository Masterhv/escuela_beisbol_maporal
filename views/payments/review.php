<?php
/** @var array $payments Pagos En Revision, del mas antiguo al mas reciente. */
$me = (int)$_SESSION['admin_id'];
// Secretario y administrador concilian por igual; solo se les oculta lo que ellos mismos registraron.
?>
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-clipboard-check text-red-700"></i>
            Pagos en Revisión
        </h2>
        <p class="text-black mt-1">Pagos reportados que esperan conciliación. Los más antiguos primero.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Atleta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Concepto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Monto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Referencia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Reportado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-black uppercase tracking-wider">Acción</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                            <p>No hay pagos esperando revisión.</p>
                        </td>
                    </tr>
                    <?php else: foreach ($payments as $p):
                        $own = (int)($p['reported_by'] ?? 0) === $me; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900"><?php echo htmlspecialchars($p['first_name'] . ' ' . $p['last_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-gray-900"><?php echo htmlspecialchars($p['concept_name'] ?? 'Pago'); ?></p>
                            <p class="text-sm text-black"><?php echo htmlspecialchars($p['month'] . ' ' . $p['year']); ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">Bs. <?php echo number_format($p['amount'], 2); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <p class="text-gray-900"><?php echo htmlspecialchars($p['reference_number'] ?? '—'); ?></p>
                            <p class="text-sm text-black"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $p['payment_method'] ?? ''))); ?></p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-black">
                            <?php echo !empty($p['reported_at']) ? date('d/m/Y H:i', strtotime($p['reported_at'])) : '—'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo (int)$p['id']; ?>" class="btn-primary text-white text-sm font-medium py-1.5 px-3 rounded-lg inline-flex items-center gap-2">
                                <i class="fas <?php echo $own ? 'fa-eye' : 'fa-search'; ?>"></i><?php echo $own ? 'Ver' : 'Conciliar'; ?>
                            </a>
                            <?php if ($own): ?>
                            <p class="text-xs text-gray-500 mt-1">Lo registraste tú</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
