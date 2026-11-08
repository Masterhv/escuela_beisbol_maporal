

<?php if (!isset($Athlete) || !$Athlete): ?>
<p class="pdf-empty">No se encontró información del atleta.</p>
<?php else:

/* ── Foto base64 ── */
$imageSrc = null;
if (!empty($Athlete['photo'])) {
    $photoPath = realpath(__DIR__ . '/../../../' . $Athlete['photo']);
    if ($photoPath && file_exists($photoPath)) {
        $ext  = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
        $mime = ($ext === 'jpg') ? 'jpeg' : $ext;
        $imageSrc = 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($photoPath));
    }
}

/* ── Estatus ── */
$statusMap   = ['active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido'];
$statusClass = ['active' => 'status-active', 'inactive' => 'status-inactive', 'suspended' => 'status-suspended'];
$statusLabel = $statusMap[$Athlete['status']] ?? ucfirst($Athlete['status']);
$statusCss   = $statusClass[$Athlete['status']] ?? '';

/* ── Helpers ── */
$h = fn($v) => htmlspecialchars($v ?? '—');
$d = fn($v) => (!empty($v) && $v !== '0000-00-00') ? date('d/m/Y', strtotime($v)) : '—';

/* ── Logo institucional ── */
$logoSrc = null;
$logoFile = BASE_PATH . 'img' . DIRECTORY_SEPARATOR . 'logo-maporal.png';
if (file_exists($logoFile)) {
    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile));
}
?>

<div class="report-header">
    <?php if ($logoSrc): ?>
    <img src="<?php echo $logoSrc; ?>" alt="Logo Maporal">
    <?php endif; ?>
    <h1>Escuela de Beisbol Menor Maporal</h1>
    <p>Ficha del Atleta</p>
</div>

<div class="ficha-body">

    <!-- BLOQUE SUPERIOR: foto izquierda + datos derecha -->
    <div class="top-row">

        <!-- Foto -->
        <div class="col-photo">
            <div class="photo-wrap">
                <?php if ($imageSrc): ?>
                <img src="<?php echo $imageSrc; ?>" alt="Foto">
                <?php else: ?>
                <table class="photo-placeholder-table">
                    <tr><td class="photo-placeholder-cell">
                        <?php echo strtoupper(substr($Athlete['first_name'],0,1).substr($Athlete['last_name'],0,1)); ?>
                    </td></tr>
                </table>
                <?php endif; ?>
            </div>
            <?php if (!empty($Athlete['jersey_number'])): ?>
            <div class="jersey-badge"># <?php echo $h($Athlete['jersey_number']); ?> &nbsp;·&nbsp; <?php echo $h($Athlete['category']); ?></div>
            <?php endif; ?>
        </div>

        <!-- Datos -->
        <div class="col-info">

            <!-- Nombre + meta -->
            <div class="athlete-name"><?php echo $h($Athlete['first_name'].' '.$Athlete['last_name']); ?></div>
            <div class="athlete-meta">
                <span class="status-chip <?php echo $statusCss; ?>"><?php echo $statusLabel; ?></span>
                <?php if (!empty($Athlete['id_card'])): ?>
                <span class="athlete-meta-gap">C.I. <?php echo $h($Athlete['id_card']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Información Personal -->
            <div class="section">
                <div class="section-title">Información Personal</div>
                <div class="ft">
                    <div class="fr">
                        <div class="fc"><div class="fl">Fecha de Nacimiento</div><div class="fv"><?php echo $d($Athlete['date_of_birth']); ?></div></div>
                        <div class="fc"><div class="fl">Edad</div><div class="fv"><?php echo $h($Athlete['age']); ?> años</div></div>
                    </div>
                    <div class="fr">
                        <div class="fc"><div class="fl">Categoría</div><div class="fv"><?php echo $h($Athlete['category']); ?></div></div>
                        <div class="fc"><div class="fl">Fecha de Registro</div><div class="fv"><?php echo $d($Athlete['registration_date']); ?></div></div>
                    </div>
                    <div class="fr">
                        <div class="fc full"><div class="fl">Dirección</div><div class="fv"><?php echo $h($Athlete['address']); ?></div></div>
                    </div>
                </div>
            </div>

            <!-- Representante -->
            <div class="section">
                <div class="section-title">Representante / Tutor</div>
                <div class="ft">
                    <div class="fr">
                        <div class="fc"><div class="fl">Nombre</div><div class="fv"><?php echo $h($Athlete['parent_name']); ?></div></div>
                        <div class="fc"><div class="fl">Cédula</div><div class="fv"><?php echo $h($Athlete['parent_id_card']); ?></div></div>
                    </div>
                    <div class="fr">
                        <div class="fc"><div class="fl">Teléfono</div><div class="fv"><?php echo $h($Athlete['parent_phone']); ?></div></div>
                        <div class="fc"><div class="fl">Email</div><div class="fv fv--small"><?php echo $h($Athlete['parent_email']); ?></div></div>
                    </div>
                </div>
            </div>

        </div><!-- /col-info -->
    </div><!-- /top-row -->

    <hr class="divider">

    <!-- BLOQUE INFERIOR: 3 columnas -->
    <div class="bottom-row">

        <div class="bc">
            <div class="section-title">Información Deportiva</div>
            <div class="mt-4">
                <div class="fl">Número de Jersey</div>
                <div class="bv"><?php echo $h($Athlete['jersey_number'] ?? '—'); ?></div>
            </div>
            <div class="mt-5">
                <div class="fl">Categoría</div>
                <div class="bv"><?php echo $h($Athlete['category']); ?></div>
            </div>
        </div>

        <div class="bc">
            <div class="section-title">Información Médica</div>
            <div class="mt-4">
                <div class="fl">Condiciones Médicas</div>
                <div class="bv"><?php echo $h(!empty($Athlete['medical_conditions']) ? $Athlete['medical_conditions'] : 'No posee'); ?></div>
            </div>
        </div>

        <div class="bc">
            <div class="section-title">Contacto de Emergencia</div>
            <div class="mt-4">
                <div class="fl">Nombre</div>
                <div class="bv"><?php echo $h($Athlete['emergency_contact']); ?></div>
            </div>
            <div class="mt-5">
                <div class="fl">Teléfono</div>
                <div class="bv"><?php echo $h($Athlete['emergency_phone']); ?></div>
            </div>
        </div>

    </div><!-- /bottom-row -->

</div><!-- /ficha-body -->

<?php endif; ?>
