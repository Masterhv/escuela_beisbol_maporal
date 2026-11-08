
<?php if (!isset($Athlete) || !$Athlete): ?>
<p class="pdf-empty">No se encontró información del atleta.</p>
<?php else:

/* Helpers */
$h = fn($v) => htmlspecialchars((string)($v ?? ''));
$d = fn($v) => (!empty($v) && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : '';

$athleteName = trim($Athlete['first_name'] . ' ' . $Athlete['last_name']);
$summary = $summary ?? ['paid_count' => 0, 'paid_total' => 0, 'last_period' => null, 'total_count' => 0];

/* Meses en español para la fecha larga de emisión */
$monthNames = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
               7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
$ts = strtotime($Athlete['withdrawn_at']);
$longDate = date('j', $ts) . ' de ' . $monthNames[(int)date('n', $ts)] . ' de ' . date('Y', $ts);

/* El logo, el nombre de la escuela y el título "Solvencia de Retiro" los imprime la cabecera
   fija del layout (views/reports/pdf/layouts/main.php), así que aquí no se repiten. */
?>

<div class="clearance-body">

    <div class="clearance-number-box">
        <div class="clearance-number-cell">
            <div class="clearance-label">N.° de solvencia</div>
            <div class="clearance-value"><?php echo $h($Athlete['clearance_number']); ?></div>
        </div>
        <div class="clearance-number-cell">
            <div class="clearance-label">Fecha de emisión</div>
            <div class="clearance-value"><?php echo $d($Athlete['withdrawn_at']); ?></div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Atleta</div>
        <div class="ft">
            <div class="fr">
                <div class="fc"><div class="fl">Nombres y apellidos</div><div class="fv"><?php echo $h($athleteName); ?></div></div>
                <div class="fc"><div class="fl">Cédula</div><div class="fv"><?php echo $h(!empty($Athlete['id_card']) ? $Athlete['id_card'] : '—'); ?></div></div>
            </div>
            <div class="fr">
                <div class="fc"><div class="fl">Categoría</div><div class="fv"><?php echo $h($Athlete['category']); ?></div></div>
                <div class="fc"><div class="fl">Fecha de inscripción</div><div class="fv"><?php echo $d($Athlete['registration_date']); ?></div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Representante</div>
        <div class="ft">
            <div class="fr">
                <div class="fc"><div class="fl">Nombres y apellidos</div><div class="fv"><?php echo $h($Athlete['parent_name']); ?></div></div>
                <div class="fc"><div class="fl">Cédula</div><div class="fv"><?php echo $h(!empty($Athlete['parent_id_card']) ? $Athlete['parent_id_card'] : '—'); ?></div></div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Estado de Cuenta</div>
        <div class="ft">
            <div class="fr">
                <div class="fc"><div class="fl">Pagos registrados y cancelados</div><div class="fv"><?php echo (int)$summary['paid_count']; ?></div></div>
                <div class="fc"><div class="fl">Total cancelado</div><div class="fv">Bs. <?php echo number_format((float)$summary['paid_total'], 2); ?></div></div>
            </div>
            <div class="fr">
                <div class="fc"><div class="fl">Último período cancelado</div><div class="fv"><?php echo $h($summary['last_period'] ?? '—'); ?></div></div>
                <div class="fc"><div class="fl">Pagos pendientes</div><div class="fv">Ninguno</div></div>
            </div>
        </div>
    </div>

    <div class="declaration">
        La Escuela de Beisbol Menor Maporal hace constar que el atleta <strong><?php echo $h($athleteName); ?></strong>,
        representado por <strong><?php echo $h($Athlete['parent_name']); ?></strong>, se encuentra <strong>solvente por todo
        concepto</strong> con la institución a la fecha de emisión de este documento, y que su retiro queda registrado a partir
        del <?php echo $h($longDate); ?>. Se expide la presente solvencia a solicitud del representante.
    </div>

    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line">Firma del representante</div>
            <div class="sig-sub">C.I.: <?php echo $h(!empty($Athlete['parent_id_card']) ? $Athlete['parent_id_card'] : '________________'); ?></div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">Por la Escuela de Beisbol Menor Maporal</div>
            <div class="sig-sub">Emitida por: <?php echo $h($Athlete['clearance_issued_by'] ?? ''); ?></div>
        </div>
    </div>

</div><!-- /clearance-body -->

<?php endif; ?>
