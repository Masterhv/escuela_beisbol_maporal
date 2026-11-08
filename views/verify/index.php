<?php
/**
 * Vista Pública de Verificación de Documentos y Códigos QR.
 * 
 * Permite a cualquier persona verificar la autenticidad e integridad de un documento emitido
 * por la Escuela de Béisbol Menor Maporal (constancias, fichas, recibos, solvencias):
 * - Búsqueda por token o escaneo automático del código QR.
 * - Validación criptográfica mediante HMAC-SHA256 para detectar cualquier alteración externa.
 * - Muestra los datos certificados originales almacenados en el sistema al momento de la emisión.
 * 
 * Variables recibidas desde VerifyController::index():
 * @var array|null  $result            Resultado de la verificación (válido, alterado, no encontrado).
 * @var array|null  $certificateStatus Estado de vigencia de constancias de pertenencia.
 * @var string      $token             Token de verificación consultado.
 */
$reportLabels = [
    'athlete_card'     => 'Ficha de Atleta',
    'athlete_list'     => 'Lista de Atletas',
    'payment_history'  => 'Historial de Pagos',
    'financial'        => 'Reporte Financiero',
    'enrollment_form'  => 'Planilla de Inscripción',
    'payment_receipt'  => 'Recibo de Pago',
    'withdrawal_clearance' => 'Solvencia de Retiro',
    'membership_certificate' => 'Constancia de Pertenencia',
];
$payloadLabels = [
    // Ficha atleta / Historial pagos
    'athlete_name'       => 'Atleta',
    'athlete_id_card'    => 'Cédula del Atleta',
    'athlete_category'   => 'Categoría',
    'athlete_dob'        => 'Fecha de Nacimiento',
    'parent_name'        => 'Representante',
    'parent_phone'       => 'Teléfono',
    'parent_email'       => 'Email',
    'registration_date'  => 'Fecha de Registro',
    'total_payments'     => 'Total de Pagos',
    'total_amount'       => 'Monto Total (Bs.)',
    // Solvencia de retiro
    'clearance_number'   => 'N.° de Solvencia',
    'withdrawn_at'       => 'Fecha de Retiro',
    'issued_by'          => 'Emitida por',
    'paid_payments'      => 'Pagos Cancelados',
    // Constancia de pertenencia
    'certificate_number' => 'N.° de Constancia',
    'issued_at'          => 'Fecha de Emisión',
    'valid_until'        => 'Válida hasta',
    // Recibo de pago
    'payment_id'         => 'N.° de Recibo',
    'amount'             => 'Monto (Bs.)',
    'month'              => 'Mes',
    'year'               => 'Año',
    'payment_date'       => 'Fecha de Pago',
    // Lista
    'total_athletes'     => 'Total de Atletas',
    'filters_applied'    => 'Filtros Aplicados',
    // Financiero
    'total_revenue'      => 'Total Recaudado (Bs.)',
    'paid_count'         => 'Pagos Realizados',
    'pending_count'      => 'Pagos Pendientes',
    // Comunes
    'generated_at'       => 'Generado el',
    'generated_by'       => 'Generado por',
    'filename'           => 'Nombre del Archivo',
    'report_type'        => 'Tipo de Reporte',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Documento — <?php echo defined('SITE_NAME') ? SITE_NAME : 'Escuela de Béisbol Maporal'; ?></title>
    <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : '/project/'; ?>views/assets/css/style.css?v=<?php echo defined('BASE_PATH') ? (@filemtime(BASE_PATH . 'views/assets/css/style.css') ?: time()) : time(); ?>">
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header simple -->
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
            <img src="<?php echo defined('BASE_URL') ? BASE_URL : '/project/'; ?>img/logo-maporal.png"
                 alt="Logo" class="h-10 w-auto">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Escuela de Béisbol Menor Maporal</h1>
                <p class="text-xs text-gray-500">Sistema de Verificación de Documentos</p>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-10 space-y-6">

        <!-- Título -->
        <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-red-700 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Verificación de Autenticidad</h2>
            <p class="text-gray-500 text-sm mt-1">
                Compruebe que el documento PDF no ha sido alterado desde su generación.
            </p>
        </div>

        <?php if ($result === null): ?>
        <!-- Formulario de búsqueda manual -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-search text-red-700"></i> Ingresar token de verificación
            </h3>
            <form method="GET" action="" class="flex gap-3">
                <input type="text" name="token" placeholder="Pegue el token del documento..."
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-700 focus:border-transparent font-mono">
                <button type="submit" class="btn-primary text-white font-medium text-sm py-2 px-4 rounded-lg">
                    Verificar
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-3">
                <i class="fas fa-info-circle mr-1"></i>
                El token se obtiene escaneando el código QR impreso en el PDF o copiándolo del pie de página del documento.
            </p>
        </div>

        <?php elseif (!$result['valid'] && $result['record'] === null): ?>
        <!-- Token no encontrado -->
        <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-times-circle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-red-800 mb-1">Documento No Encontrado</h3>
            <p class="text-red-700 text-sm">
                El token <code class="bg-red-100 px-1 rounded font-mono"><?php echo $token; ?></code>
                no existe en el sistema. El documento puede ser falso o el QR fue alterado.
            </p>
            <a href="?" class="mt-4 inline-block text-red-700 hover:underline text-sm">← Intentar con otro token</a>
        </div>

        <?php elseif (!$result['valid']): ?>
        <!-- Registro encontrado pero fingerprint no coincide — datos manipulados -->
        <div class="bg-red-50 border-2 border-red-500 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-14 h-14 rounded-full bg-red-200 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-700 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-red-800">¡ALERTA: Documento Alterado</h3>
                    <p class="text-red-700 text-sm">La integridad del documento no pudo ser verificada.</p>
                </div>
            </div>
            <div class="bg-red-100 border border-red-300 rounded-lg p-4 text-sm text-red-900">
                <p class="font-semibold mb-1"><i class="fas fa-ban mr-1"></i>El contenido de este PDF ha sido modificado externamente.</p>
                <p>Los datos del documento no coinciden con los registros originales del sistema. No confíe en la información de este documento.</p>
            </div>
        </div>

        <?php else: ?>
        <!-- ✅ VÁLIDO -->
        <?php
            $record  = $result['record'];
            $payload = $result['payload'];
            $typeLabel = $reportLabels[$record['report_type']] ?? ucfirst($record['report_type']);
        ?>
        <div class="bg-green-50 border-2 border-green-500 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-14 h-14 rounded-full bg-green-200 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-green-700 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-green-800">Documento Auténtico</h3>
                    <p class="text-green-700 text-sm">La integridad del documento ha sido verificada exitosamente.</p>
                </div>
            </div>
        </div>

        <?php if (!empty($certificateStatus)): ?>
        <!-- Estado de la constancia: ser auténtica no basta, también debe estar vigente y el atleta seguir activo -->
        <?php if ($certificateStatus['is_current']): ?>
        <div class="bg-green-50 border border-green-400 text-green-800 rounded-xl p-4 flex items-start gap-3">
            <i class="fas fa-calendar-check mt-1"></i>
            <p class="text-sm"><strong>Constancia vigente</strong> hasta el <?php echo date('d/m/Y', strtotime($certificateStatus['valid_until'])); ?>.</p>
        </div>
        <?php else: ?>
        <div class="bg-red-50 border-2 border-red-500 text-red-800 rounded-xl p-4 flex items-start gap-3">
            <i class="fas fa-calendar-times mt-1"></i>
            <p class="text-sm"><strong>Constancia vencida.</strong> Fue válida hasta el <?php echo date('d/m/Y', strtotime($certificateStatus['valid_until'])); ?>. El documento es auténtico, pero ya no es válido: solicite una nueva en la escuela.</p>
        </div>
        <?php endif; ?>

        <?php if ($certificateStatus['athlete_active'] === false): ?>
        <div class="bg-yellow-50 border-2 border-yellow-400 text-yellow-800 rounded-xl p-4 flex items-start gap-3">
            <i class="fas fa-exclamation-triangle mt-1"></i>
            <p class="text-sm"><strong>Atención:</strong> a la fecha de esta consulta, el atleta ya no figura como activo en la escuela.</p>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Información del documento -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-file-pdf text-red-700"></i> Información del Documento
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">Tipo de Reporte</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($typeLabel); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">Archivo</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($record['filename']); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">Generado el</p>
                    <p class="font-semibold text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($record['generated_at'])); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">Generado por</p>
                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($record['generated_by_name'] ?? 'Sistema'); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">Verificaciones realizadas</p>
                    <p class="font-semibold text-gray-900"><?php echo (int)$record['verified_count']; ?> vez<?php echo $record['verified_count'] != 1 ? 'es' : ''; ?></p>
                </div>
                <?php if ($record['last_verified_at']): ?>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-0.5">Última verificación</p>
                    <p class="font-semibold text-gray-900"><?php echo date('d/m/Y H:i:s', strtotime($record['last_verified_at'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Datos del reporte -->
        <?php if (!empty($payload)): ?>
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-database text-red-700"></i> Datos Originales del Reporte
            </h3>
            <div class="space-y-2 text-sm">
                <?php foreach ($payload as $key => $value):
                    if ($value === null || $value === '') continue;
                    $label = $payloadLabels[$key] ?? str_replace('_', ' ', ucfirst($key));
                ?>
                <div class="flex items-start gap-3 border-b border-gray-100 pb-2 last:border-0">
                    <span class="text-gray-500 w-44 flex-shrink-0 font-medium"><?php echo htmlspecialchars($label); ?>:</span>
                    <span class="text-gray-900"><?php
                        // 'report_type' es un identificador interno (p. ej. withdrawal_clearance): se muestra su etiqueta.
                        $shown = ($key === 'report_type') ? ($reportLabels[$value] ?? $value) : $value;
                        echo nl2br(htmlspecialchars((string)$shown));
                    ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Token técnico -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-500 uppercase font-medium mb-1">Token de Verificación</p>
            <code class="text-xs font-mono text-gray-700 break-all"><?php echo $token; ?></code>
            <p class="text-xs text-gray-400 mt-1">
                Huella digital (HMAC-SHA256): <code class="font-mono"><?php echo substr($record['fingerprint'], 0, 16); ?>...</code>
            </p>
        </div>

        <?php endif; ?>

    </main>

    <footer class="text-center py-6 text-xs text-gray-400 border-t border-gray-200 mt-8">
        Escuela de Béisbol Menor Maporal — Sistema de Verificación de Documentos
    </footer>
</body>
</html>
