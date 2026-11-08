

<?php
$blank = !empty($blank);
$Athlete = $Athlete ?? null;

if (!$blank && !$Athlete): ?>
<p class="pdf-empty">No se encontró información del atleta.</p>
<?php else:

/* Helpers */
$h = fn($v) => htmlspecialchars((string)($v ?? ''));
$d = fn($v) => (!empty($v) && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : '';
$val = function ($key) use ($blank, $Athlete, $h) {
    if ($blank || !isset($Athlete[$key]) || $Athlete[$key] === '' || $Athlete[$key] === null) {
        return '';
    }
    return $h($Athlete[$key]);
};
/* Celda de campo: muestra el valor o una línea en blanco para escribir a mano */
$cell = function ($label, $value, $extraClass = '') use ($blank) {
    $isEmpty = ($value === '');
    $cls = 'fv' . (($blank || $isEmpty) ? ' blank' : '');
    $content = $isEmpty ? '&nbsp;' : $value;
    return '<div class="fc ' . $extraClass . '"><div class="fl">' . $label . '</div><div class="' . $cls . '">' . $content . '</div></div>';
};

$fullName = $blank ? '' : $h(trim(($Athlete['first_name'] ?? '') . ' ' . ($Athlete['last_name'] ?? '')));
$enrollmentNumber = $blank ? '' : $h($Athlete['id']);
$enrollmentDate = $blank ? '' : $d($Athlete['registration_date'] ?? '');
$birthDate = $blank ? '' : $d($Athlete['date_of_birth'] ?? '');
$ageText = ($blank || !isset($Athlete['age'])) ? '' : $h($Athlete['age']) . ' años';
$medical = $blank ? '' : $h(!empty($Athlete['medical_conditions']) ? $Athlete['medical_conditions'] : 'No posee');
$emergency = $blank ? '' : $h($Athlete['emergency_contact'] ?? '');
?>

<div class="form-title">
    <h2>Planilla de Inscripción<?php echo $blank ? ' (formato en blanco)' : ''; ?></h2>
</div>

<div class="meta-box">
    <div class="meta-cell">
        <div class="meta-label">N.° de inscripción</div>
        <div class="meta-value"><?php echo $blank ? '______________' : $enrollmentNumber; ?></div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Fecha de inscripción</div>
        <div class="meta-value"><?php echo $blank ? '____ / ____ / ________' : $enrollmentDate; ?></div>
    </div>
</div>

<div class="section">
    <div class="section-title">Datos del Atleta</div>
    <div class="ft">
        <div class="fr">
            <?php echo $cell('Nombres y apellidos', $fullName); ?>
            <?php echo $cell('Cédula', $val('id_card')); ?>
        </div>
        <div class="fr">
            <?php echo $cell('Fecha de nacimiento', $birthDate); ?>
            <?php echo $cell('Edad', $ageText); ?>
        </div>
        <div class="fr">
            <?php echo $cell('Categoría', $val('category')); ?>
            <?php echo $cell('N.° de camiseta', $val('jersey_number')); ?>
        </div>
        <div class="fr">
            <?php echo $cell('Condiciones médicas', $medical, 'full'); ?>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Datos del Representante</div>
    <div class="ft">
        <div class="fr">
            <?php echo $cell('Nombres y apellidos', $val('parent_name')); ?>
            <?php echo $cell('Cédula', $val('parent_id_card')); ?>
        </div>
        <div class="fr">
            <?php echo $cell('Teléfono', $val('parent_phone')); ?>
            <?php echo $cell('Correo electrónico', $val('parent_email')); ?>
        </div>
        <div class="fr">
            <?php echo $cell('Dirección', $val('address'), 'full'); ?>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Contacto de Emergencia</div>
    <div class="ft">
        <div class="fr">
            <?php echo $cell('Nombre', $emergency); ?>
            <?php echo $cell('Teléfono', $val('emergency_phone')); ?>
        </div>
    </div>
</div>

<div class="declaration">
    Yo, representante del atleta arriba identificado, declaro que la información suministrada en esta planilla es veraz,
    autorizo su participación en las actividades deportivas de la Escuela de Beisbol Menor Maporal, y me comprometo a
    cumplir con las normas de la institución y a informar oportunamente cualquier cambio en los datos aquí registrados,
    en especial los relativos a su salud y a los contactos de emergencia.
</div>

<div class="signatures">
    <div class="sig-cell">
        <div class="sig-line">Firma del representante</div>
        <div class="sig-sub">C.I.: <?php echo $blank ? '________________' : $val('parent_id_card'); ?></div>
    </div>
    <div class="sig-cell">
        <div class="sig-line">Por la Escuela de Beisbol Menor Maporal</div>
        <div class="sig-sub">Sello y firma</div>
    </div>
</div>

<?php endif; ?>
