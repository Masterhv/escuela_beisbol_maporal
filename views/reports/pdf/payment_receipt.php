

<?php if (!isset($payment) || !$payment): ?>
<p class="pdf-empty">No se encontró información del pago.</p>
<?php else:

/* Helpers */
$h = fn($v) => htmlspecialchars($v ?? '—');
$d = fn($v) => (!empty($v) && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : '—';

$statusLabel = PaymentWorkflow::label($payment['status']);

/* El logo, el nombre de la escuela y el título "Recibo de Pago" los imprime la cabecera fija del
   layout (views/reports/pdf/layouts/main.php), igual que en la planilla de inscripción y la
   solvencia, así que aquí no se repiten. */
?>

<div class="receipt-body">

    <div class="receipt-title">
        <h2>Comprobante de Pago N.° <?php echo $h($payment['id']); ?></h2>
        <div class="receipt-period">
            <span class="receipt-period-label">Pago correspondiente al mes de</span>
            <span class="receipt-period-value"><?php echo $h($payment['month'] . ' ' . $payment['year']); ?></span>
        </div>
        <div class="status-chip"><?php echo $statusLabel; ?></div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Atleta</div>
        <div class="ft">
            <div class="fr">
                <div class="fc"><div class="fl">Nombre</div><div class="fv"><?php echo $h($payment['first_name'] . ' ' . $payment['last_name']); ?></div></div>
                <div class="fc"><div class="fl">Categoría</div><div class="fv"><?php echo $h($payment['category'] ?? 'N/A'); ?></div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Representante</div>
        <div class="ft">
            <div class="fr">
                <div class="fc"><div class="fl">Nombre</div><div class="fv"><?php echo $h($payment['parent_name'] ?? 'N/A'); ?></div></div>
                <div class="fc"><div class="fl">Teléfono</div><div class="fv"><?php echo $h($payment['parent_phone'] ?? 'N/A'); ?></div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detalle del Pago</div>
        <div class="ft">
            <div class="fr">
                <div class="fc"><div class="fl">Fecha de Pago</div><div class="fv"><?php echo $d($payment['payment_date']); ?></div></div>
                <div class="fc"><div class="fl">Fecha de Vencimiento</div><div class="fv"><?php echo $d($payment['due_date']); ?></div></div>
            </div>
        </div>

        <div class="amount-box">
            <div class="amount-label">Monto Pagado &middot; <?php echo $h($payment['month'] . ' ' . $payment['year']); ?></div>
            <div class="amount-value">Bs. <?php echo number_format($payment['amount'], 2); ?></div>
        </div>

        <?php if (!empty($payment['notes'])): ?>
        <div class="notes-box">
            <strong>Notas:</strong> <?php echo $h($payment['notes']); ?>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /receipt-body -->

<?php endif; ?>
