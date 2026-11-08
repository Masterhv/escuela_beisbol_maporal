
 
<?php if (isset($stats)): ?>
    <?php $hasFilterDescription = isset($filterDescription) && trim($filterDescription) !== ''; ?>
    <div class="stats">
        <?php if ($hasFilterDescription): ?>
            <h2>Estadísticas según filtros aplicados</h2>
            <div class="stats-row stats-row--spaced">
                <span><strong>Filtros:</strong> <?php echo htmlspecialchars($filterDescription); ?></span>
            </div>
        <?php else: ?>
            <h2>Estadísticas Generales</h2>
        <?php endif; ?>
        <div class="stats-row">
            <span>
                <strong><?php echo $hasFilterDescription ? 'Total aprobado en el filtro:' : 'Total aprobado:'; ?></strong>
                <?php echo htmlspecialchars(number_format($stats['total_revenue'] ?? 0, 2)); ?> Bs.
            </span>
        </div>
        <div class="stats-row">
            <span><strong>Aprobados:</strong> <?php echo htmlspecialchars($stats['approved_payments'] ?? 0); ?></span>
            <span><strong>En Revisión:</strong> <?php echo htmlspecialchars($stats['in_review_payments'] ?? 0); ?></span>
            <span><strong>Pendientes:</strong> <?php echo htmlspecialchars($stats['pending_payments'] ?? 0); ?></span>
            <span><strong>Rechazados:</strong> <?php echo htmlspecialchars($stats['rejected_payments'] ?? 0); ?></span>
            <span><strong>Cantidad de Atletas:</strong> <?php echo htmlspecialchars($stats['total_athletes'] ?? 0); ?></span>
        </div>
    </div>
<?php endif; ?>
 
<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Atleta</th>
            <th>Categoría</th>
            <th>Edad</th>
            <th>Monto</th>
            <th>Mes/Año</th>
            <th>Estatus</th>
            <th>Fecha de Pago</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($payments) && !empty($payments)): ?>
            <?php
            $counter = 1;
            foreach ($payments as $payment):
            ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($payment['category'] ?? 'N/A'); ?></td>
                    <td>
                        <?php
                        if (isset($payment['age']) && $payment['age'] !== null && $payment['age'] !== '') {
                            echo htmlspecialchars($payment['age']) . ' años';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </td>
                    <td>Bs. <?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($payment['month'] . '/' . $payment['year']); ?></td>
                    <td>
                        <span class="status-<?php echo htmlspecialchars($payment['status']); ?>"><?php echo htmlspecialchars(PaymentWorkflow::label($payment['status'])); ?></span>
                    </td>
                    <td><?php echo $payment['payment_date'] ? htmlspecialchars(date('d/m/Y', strtotime($payment['payment_date']))) : 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="pdf-no-results">No hay pagos para mostrar según los filtros aplicados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>