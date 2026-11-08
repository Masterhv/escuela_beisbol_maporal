
<?php if (!isset($Athlete) || !$Athlete || !isset($Certificate) || !$Certificate): ?>
<p class="pdf-empty">No se encontró información de la constancia.</p>
<?php else:

/* Helpers */
$h = fn($v) => htmlspecialchars((string)($v ?? ''));
$d = fn($v) => (!empty($v) && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : '';

$athleteName = trim($Athlete['first_name'] . ' ' . $Athlete['last_name']);

/* Fecha larga en español: "14 de octubre de 2026" */
$monthNames = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
               7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
$longDate = function ($value) use ($monthNames) {
    $ts = strtotime($value);
    return date('j', $ts) . ' de ' . $monthNames[(int)date('n', $ts)] . ' de ' . date('Y', $ts);
};

/* El logo, el nombre de la escuela y el título "Constancia de Pertenencia" los imprime la cabecera
   fija del layout (views/reports/pdf/layouts/main.php), así que aquí no se repiten. */
?>

<div class="certificate-body">

    <div class="certificate-number-box">
        <div class="certificate-number-cell">
            <div class="certificate-label">N.° de constancia</div>
            <div class="certificate-value"><?php echo $h($Certificate['certificate_number']); ?></div>
        </div>
        <div class="certificate-number-cell">
            <div class="certificate-label">Fecha de emisión</div>
            <div class="certificate-value"><?php echo $d($Certificate['issued_at']); ?></div>
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

    <div class="declaration">
        La Escuela de Béisbol Menor Maporal hace constar que el atleta <strong><?php echo $h($athleteName); ?></strong><?php if (!empty($Athlete['id_card'])): ?>,
        titular de la cédula de identidad <strong><?php echo $h($Athlete['id_card']); ?></strong>,<?php endif; ?>
        se encuentra inscrito en esta institución desde el <?php echo $h($longDate($Athlete['registration_date'])); ?>,
        forma parte de la categoría <strong><?php echo $h($Athlete['category']); ?></strong> y se encuentra
        <strong>activo</strong> a la fecha de emisión de este documento. Se expide la presente constancia a solicitud de su
        representante, <?php echo $h($Athlete['parent_name']); ?>.
    </div>

    <div class="validity-note">
        Esta constancia es válida por <?php echo (int)MembershipCertificate::VALIDITY_DAYS; ?> días a partir de su emisión,
        hasta el <strong><?php echo $h($longDate($Certificate['valid_until'])); ?></strong>. Pasada esa fecha, solicite una nueva.
    </div>

    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line">Por la Escuela de Béisbol Menor Maporal</div>
            <div class="sig-sub">Firma y sello</div>
        </div>
    </div>

</div><!-- /certificate-body -->

<?php endif; ?>
