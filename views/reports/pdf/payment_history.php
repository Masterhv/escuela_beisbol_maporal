

<?php if (isset($Athlete) && $Athlete): ?>
    <div class="info-container">
        <div class="Athlete-info">
            <strong>Atleta:</strong> <?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?><br>
            <strong>Categoría:</strong> <?php echo htmlspecialchars($Athlete['category']); ?><br>
            <strong>Representante:</strong> <?php echo htmlspecialchars($Athlete['parent_name']); ?><br>
            <strong>Teléfono:</strong> <?php echo htmlspecialchars($Athlete['parent_phone']); ?><br>
            <strong>Email:</strong> <?php echo htmlspecialchars($Athlete['parent_email']); ?>
        </div>
        <div class="photo-container">
            <?php
            if (!empty($Athlete['photo'])):
                $photoPath = __DIR__ . '/../../../' . $Athlete['photo'];
                $photoPath = realpath($photoPath);
                
                if ($photoPath && file_exists($photoPath)) {
                    $imageData = base64_encode(file_get_contents($photoPath));
                    $imageType = pathinfo($photoPath, PATHINFO_EXTENSION);
                    $imageType = strtolower($imageType);
                    if ($imageType === 'jpg') {
                        $imageType = 'jpeg';
                    }
                    $imageSrc = 'data:image/' . $imageType . ';base64,' . $imageData;
            ?>
                <img src="<?php echo $imageSrc; ?>" alt="Foto de <?php echo htmlspecialchars($Athlete['first_name']); ?>" class="photo-img">
            <?php
                } else {
            ?>
                <div class="photo-placeholder">
                    <div>
                        <div class="silhouette-head"></div>
                        <div class="silhouette-body"></div>
                    </div>
                </div>
            <?php
                }
            else:
            ?>
                <div class="photo-placeholder">
                    <div>
                        <div class="silhouette-head"></div>
                        <div class="silhouette-body"></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="photo-label">Foto del atleta</div>
        </div>
    </div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Monto</th>
            <th>Mes</th>
            <th>Año</th>
            <th>Estatus</th>
            <th>Fecha Vencimiento</th>
            <th>Fecha Pago</th>
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
                    <td>Bs. <?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($payment['month']); ?></td>
                    <td><?php echo htmlspecialchars($payment['year']); ?></td>
                    <td>
                        <?php
                        // ReportsController entrega la etiqueta ya traducida; se recupera la clave para el color.
                        $statusKey = array_search($payment['status'], PaymentWorkflow::LABELS, true);
                        if ($statusKey === false) { $statusKey = $payment['status']; }
                        ?>
                        <span class="status-<?php echo htmlspecialchars($statusKey); ?>"><?php echo htmlspecialchars($payment['status']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($payment['due_date']))); ?></td>
                    <td><?php echo $payment['payment_date'] ? htmlspecialchars(date('d/m/Y', strtotime($payment['payment_date']))) : 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="pdf-no-results">No hay pagos registrados para este atleta.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>