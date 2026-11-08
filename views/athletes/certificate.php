<?php
/**
 * Pantalla de la constancia de pertenencia.
 * Variables: $Athlete (array), $eligibility (array, ver MembershipCertificate::checkEligibility),
 *            $validCertificate (array|null, la constancia vigente si ya existe).
 * La emisión real es un POST (issueCertificate / sendCertificateEmail); esta pantalla informa y ofrece las acciones.
 */
$athleteName = trim($Athlete['first_name'] . ' ' . $Athlete['last_name']);
$eligible = !empty($eligibility['eligible']);
$reason = $eligibility['reason'] ?? 'ok';
$parentEmail = trim($Athlete['parent_email'] ?? '');
$emailOk = $parentEmail !== '' && filter_var($parentEmail, FILTER_VALIDATE_EMAIL);

/* Por qué no se puede emitir */
$blockedMessages = [
    'inactive'  => ['Atleta inactivo', 'La constancia solo se emite a atletas activos. Si el atleta debe figurar como activo, cambie su estado desde Editar.'],
    'suspended' => ['Atleta suspendido', 'La constancia solo se emite a atletas activos. Mientras esté suspendido no se puede emitir.'],
    'withdrawn' => ['Atleta retirado', 'Este atleta tiene una solvencia de retiro emitida, por lo que ya no pertenece a la escuela y no se le puede emitir una constancia.'],
];

/* Errores que pueden llegar por la URL tras un intento fallido */
$errorMessages = [
    'inactive'  => 'No se pudo emitir: el atleta está inactivo. Solo se emite constancia a atletas activos.',
    'suspended' => 'No se pudo emitir: el atleta está suspendido. Solo se emite constancia a atletas activos.',
    'withdrawn' => 'No se pudo emitir: el atleta fue retirado.',
    'failed'    => 'Ocurrió un error al emitir la constancia. No se realizó ningún cambio; intente de nuevo.',
    'pdf_failed'=> 'La constancia se emitió, pero no se pudo generar el PDF. Use "Descargar constancia" para volver a intentarlo.',
];
$errorKey = $_GET['error'] ?? null;
$errorText = $errorKey ? ($errorMessages[$errorKey] ?? 'No se pudo completar la operación.') : null;

$days = (int)MembershipCertificate::VALIDITY_DAYS;
?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-certificate text-black"></i>
                Constancia de Pertenencia
            </h2>
            <p class="text-black mt-1"><?php echo htmlspecialchars($athleteName); ?> &middot; <?php echo htmlspecialchars($Athlete['category']); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>athletes/view?id=<?php echo (int)$Athlete['id']; ?>"
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Volver al atleta
        </a>
    </div>

    <?php if ($errorText): ?>
    <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-circle mt-1"></i>
        <p class="text-sm font-medium"><?php echo htmlspecialchars($errorText); ?></p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">

        <?php if (!$eligible): ?>

            <!-- No elegible -->
            <?php [$blockedTitle, $blockedText] = $blockedMessages[$reason] ?? ['No se puede emitir', 'El atleta no cumple las condiciones para recibir una constancia.']; ?>
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-ban mt-1"></i>
                <div>
                    <p class="font-semibold">No se puede emitir la constancia: <?php echo htmlspecialchars($blockedTitle); ?>.</p>
                    <p class="text-sm mt-1"><?php echo htmlspecialchars($blockedText); ?></p>
                </div>
            </div>

        <?php else: ?>

            <!-- Elegible -->
            <div class="bg-green-50 border border-green-400 text-green-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <div>
                    <p class="font-semibold">El atleta está activo.</p>
                    <p class="text-sm mt-1">Se puede emitir la constancia. Es válida por <?php echo $days; ?> días desde su emisión.</p>
                </div>
            </div>

            <?php if (!empty($validCertificate)): ?>
            <!-- Ya hay una vigente: se entregará esa misma -->
            <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-info-circle mt-1"></i>
                <div>
                    <p class="font-semibold">Ya existe una constancia vigente: <?php echo htmlspecialchars($validCertificate['certificate_number']); ?></p>
                    <p class="text-sm mt-1">
                        Emitida el <?php echo date('d/m/Y', strtotime($validCertificate['issued_at'])); ?>,
                        válida hasta el <?php echo date('d/m/Y', strtotime($validCertificate['valid_until'])); ?>.
                        Se entregará esa misma (mismo número y fecha); no se emite otra.
                        <?php if (($validCertificate['email_status'] ?? '') === 'sent'): ?>
                        Se envió por correo a <?php echo htmlspecialchars($validCertificate['email_sent_to']); ?> el <?php echo date('d/m/Y H:i', strtotime($validCertificate['email_sent_at'])); ?>.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Descargar -->
            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-2"><i class="fas fa-download text-gray-500 mr-1"></i>Descargar</p>
                <form method="POST" action="<?php echo BASE_URL; ?>athletes/issueCertificate" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="id" value="<?php echo (int)$Athlete['id']; ?>">
                    <button type="submit" class="btn-primary text-white font-medium py-2 px-5 rounded-lg flex items-center gap-2">
                        <i class="fas fa-file-download"></i>
                        <?php echo !empty($validCertificate) ? 'Descargar constancia' : 'Generar y descargar constancia'; ?>
                    </button>
                </form>
            </div>

            <!-- Enviar por correo -->
            <div class="border-t pt-4">
                <p class="text-sm font-medium text-gray-700 mb-2"><i class="fas fa-envelope text-gray-500 mr-1"></i>Enviar por correo</p>
                <?php if ($emailOk): ?>
                    <p class="text-sm text-gray-700 mb-3">Se enviará al representante, <strong><?php echo htmlspecialchars($Athlete['parent_name']); ?></strong>, a: <strong class="break-all"><?php echo htmlspecialchars($parentEmail); ?></strong></p>
                    <button type="button" id="send-certificate-email" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg flex items-center gap-2">
                        <i class="fas fa-envelope"></i>
                        Enviar constancia por correo
                    </button>
                    <p id="certificate-email-result" class="hidden text-sm font-medium mt-3"></p>
                <?php else: ?>
                    <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 rounded-lg p-3 flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle mt-1"></i>
                        <p class="text-sm">El representante no tiene un correo válido registrado. Corríjalo desde Editar para poder enviarle la constancia.</p>
                    </div>
                <?php endif; ?>
            </div>

            <p class="text-xs text-gray-500 border-t pt-4">
                La constancia certifica que el atleta forma parte de la escuela a la fecha de emisión. No incluye datos médicos ni de contacto.
                Si el atleta se retira dentro de su vigencia, el documento ya entregado seguirá mostrando su fecha; la verificación por QR indica si el atleta ya no figura como activo.
            </p>

        <?php endif; ?>

    </div>
</div>

<?php if ($eligible && $emailOk): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('send-certificate-email');
    const out = document.getElementById('certificate-email-result');
    if (!btn) return;
    const original = btn.innerHTML;

    function show(ok, text) {
        out.textContent = text;
        out.classList.remove('hidden', 'text-green-800', 'text-red-800');
        out.classList.add(ok ? 'text-green-800' : 'text-red-800');
    }

    btn.addEventListener('click', function() {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        out.classList.add('hidden');

        ajaxRequest('<?php echo BASE_URL; ?>athletes/sendCertificateEmail', {id: <?php echo (int)$Athlete['id']; ?>}, function(response) {
            btn.disabled = false;
            btn.innerHTML = original;
            if (response.success) {
                show(true, 'Constancia ' + response.certificate_number + ' enviada a ' + response.sent_to + '.');
            } else {
                show(false, 'No se pudo enviar: ' + (response.message || 'Error desconocido'));
            }
        });
    });
});
</script>
<?php endif; ?>
