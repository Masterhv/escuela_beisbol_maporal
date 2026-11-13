<?php
$isAthleteCardReport = (($view ?? '') === 'athlete_card');

// Estilos de los PDF: Dompdf no carga CSS por URL, así que se incrusta el archivo.
$pdfCss = file_get_contents(BASE_PATH . 'views/assets/css/pdf.css');
?>
<!DOCTYPE html>
<html lang="es" class="pdf-<?php echo htmlspecialchars($view ?? 'report'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $title ?? 'Reporte'; ?></title>
    <style><?php echo $pdfCss; ?></style>
</head>
<body>
    <?php if (!$isAthleteCardReport): ?>
    <header>
        <?php
        $logoFile = BASE_PATH . 'img' . DIRECTORY_SEPARATOR . 'logo-maporal.png';
        if (file_exists($logoFile)):
            $logoB64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoFile));
        ?>
        <img src="<?php echo $logoB64; ?>" alt="Logo Maporal">
        <?php endif; ?>
        <h1>Escuela de Beisbol Menor Maporal</h1>
        <p><?php echo $title ?? 'Reporte'; ?></p>
    </header>
    <?php endif; ?>

    <footer>
        Generado el <?php echo date('d/m/Y H:i:s'); ?> | Página <span class="pagenum"></span>
    </footer>

    <main>
        <?php require "views/reports/pdf/{$view}.php"; ?>

        <?php if (!empty($_qr_base64) && !empty($_token)): ?>
        <div class="qr-block">
            <div class="qr-image-cell">
                <img src="<?php echo $_qr_base64; ?>" alt="QR de verificación">
            </div>
            <div class="qr-text-cell">
                <div class="qr-title">&#128737; Verificación de Autenticidad del Documento</div>
                <div class="qr-desc">
                    Escanee el código QR para verificar que este documento es auténtico y no ha sido alterado.<br>
                    O acceda directamente a: <strong><?php echo htmlspecialchars($_verify_url ?? ''); ?></strong>
                </div>
                <div class="qr-token">Token: <?php echo htmlspecialchars($_token); ?></div>
                <div class="qr-fingerprint">Huella: <?php echo htmlspecialchars($_fingerprint ?? ''); ?></div>
            </div>
            <div class="qr-text-cell">
                <div class="qr-title">Datos de Creaci&#243;n del Documento</div>
                <div class="qr-desc">
                    <strong>Tipo de reporte:</strong> <?php echo htmlspecialchars($_report_type ?? ''); ?><br>
                    <strong>Generado por:</strong> <?php echo htmlspecialchars($_generated_by ?? ''); ?><br>
                    <strong>Fecha de generaci&#243;n:</strong> <?php echo htmlspecialchars($_generated_at ?? ''); ?><br>
                    <strong>Archivo:</strong> <?php echo htmlspecialchars($_generated_file ?? ''); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
