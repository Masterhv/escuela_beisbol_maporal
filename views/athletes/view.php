<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user text-black"></i>
                Detalles de <?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?>
            </h2>
            <p class="text-black mt-1">Información completa del atleta.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>athletes" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            Volver a la lista
        </a>
    </div>

    <?php
    $infoMessages = [
        'already_withdrawn'            => ['bg-yellow-50 border-yellow-400 text-yellow-800', 'fa-info-circle', 'Este atleta ya tiene una solvencia de retiro emitida. Puede descargarla de nuevo abajo.'],
        'clearance_issued_pdf_failed'  => ['bg-yellow-50 border-yellow-400 text-yellow-800', 'fa-exclamation-triangle', 'La solvencia se emitió y el atleta quedó retirado, pero no se pudo generar el PDF. Use "Descargar solvencia" para volver a intentarlo.'],
    ];
    $infoKey = $_GET['info'] ?? null;
    $errorKey = $_GET['error'] ?? null;
    if ($infoKey && isset($infoMessages[$infoKey])): [$infoClass, $infoIcon, $infoText] = $infoMessages[$infoKey]; ?>
    <div class="<?php echo $infoClass; ?> border rounded-lg p-4 flex items-start gap-3">
        <i class="fas <?php echo $infoIcon; ?> mt-1"></i>
        <p class="text-sm font-medium"><?php echo htmlspecialchars($infoText); ?></p>
    </div>
    <?php endif; ?>
    <?php if ($errorKey === 'pdf_failed'): ?>
    <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-circle mt-1"></i>
        <p class="text-sm font-medium">No se pudo generar el documento PDF. Intente de nuevo.</p>
    </div>
    <?php endif; ?>

    <!-- Athlete Details -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <?php if (!empty($Athlete['photo']) && file_exists($Athlete['photo'])): ?>
        <div class="mb-6 flex justify-center">
            <div class="w-48 h-48 rounded-lg overflow-hidden border-4 border-black shadow-lg">
                <img src="<?php echo BASE_URL; ?>athletes/photo?id=<?php echo (int)$Athlete['id']; ?>" alt="Foto de <?php echo htmlspecialchars($Athlete['first_name']); ?>" class="w-full h-full object-cover">
            </div>
        </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Personal Information -->
            <div>
                <h3 class="font-semibold text-lg text-gray-800 border-b pb-2 mb-4">Información Personal</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-black">Nombre Completo</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['first_name'] . ' ' . $Athlete['last_name']); ?></dd>
                    </div>
                    <?php if (!empty($Athlete['id_card'])): ?>
                    <div>
                        <dt class="text-sm font-medium text-black">Cédula</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['id_card']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <div>
                        <dt class="text-sm font-medium text-black">Fecha de Nacimiento</dt>
                        <dd class="mt-1 text-gray-900"><?php echo date('d/m/Y', strtotime($Athlete['date_of_birth'])); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-black">Edad</dt>
                        <dd class="mt-1 text-gray-900"><?php echo $Athlete['age']; ?> años</dd>
                    </div>
                     <div>
                        <dt class="text-sm font-medium text-black">Dirección</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['address']); ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Contact Information -->
            <div>
                <h3 class="font-semibold text-lg text-gray-800 border-b pb-2 mb-4">Información de Contacto</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-black">Nombre del Padre/Madre</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_name']); ?></dd>
                    </div>
                    <?php if (!empty($Athlete['parent_id_card'])): ?>
                    <div>
                        <dt class="text-sm font-medium text-black">Cédula del Padre/Madre</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_id_card']); ?></dd>
                    </div>
                    <?php endif; ?>
                    <div>
                        <dt class="text-sm font-medium text-black">Teléfono del Padre/Madre</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_phone']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-black">Email del Padre/Madre</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['parent_email']); ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Academic & Medical Information -->
            <div>
                <h3 class="font-semibold text-lg text-gray-800 border-b pb-2 mb-4">Información Académica y Médica</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-black">Categoría</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['category']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-black">Número de Jersey</dt>
                        <dd class="mt-1 text-gray-900"><?php echo $Athlete['jersey_number'] ?? 'N/A'; ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-black">Fecha de Registro</dt>
                        <dd class="mt-1 text-gray-900"><?php echo date('d/m/Y', strtotime($Athlete['registration_date'])); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-black">Condiciones Médicas</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['medical_conditions']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-black">Contacto de Emergencia</dt>
                        <dd class="mt-1 text-gray-900"><?php echo htmlspecialchars($Athlete['emergency_contact'] . ' (' . $Athlete['emergency_phone'] . ')'); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <?php
        $canResend = $_SESSION['admin_role'] === 'administrador'; // reenvio de planilla: parte del flujo de inscripcion
        $enrollStatusLabels = ['sent' => 'Enviado', 'failed' => 'Fallido', 'not_sent' => 'No enviado'];
        $enrollStatusColors = [
            'sent' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'not_sent' => 'bg-gray-100 text-gray-700',
        ];
        $enrollChannels = [
            'email' => [
                'label' => 'Planilla por Correo',
                'icon' => 'fas fa-envelope text-blue-600',
                'btnId' => 'resend-enroll-email-btn',
                'btnClass' => 'bg-blue-600 hover:bg-blue-700',
                'btnIcon' => 'fas fa-envelope',
                'btnText' => 'Reenviar por Correo',
                'fn' => 'resendEnrollmentEmail',
            ],
            'whatsapp' => [
                'label' => 'Planilla por WhatsApp',
                'icon' => 'fab fa-whatsapp text-green-600',
                'btnId' => 'resend-enroll-wa-btn',
                'btnClass' => 'bg-green-600 hover:bg-green-700',
                'btnIcon' => 'fab fa-whatsapp',
                'btnText' => 'Reenviar por WhatsApp',
                'fn' => 'resendEnrollmentWhatsApp',
            ],
        ];
        ?>

        <!-- Inscripción: envío de la planilla y descargas -->
        <div class="mt-6 pt-4 border-t">
            <h3 class="font-semibold text-lg text-gray-800 mb-4">Planilla de Inscripción</h3>

            <?php foreach ($enrollChannels as $channel => $cfg):
                $status = $Athlete["enrollment_{$channel}_status"] ?? 'not_sent';
                $sentAt = $Athlete["enrollment_{$channel}_sent_at"] ?? null;
                $error = $Athlete["enrollment_{$channel}_error"] ?? null;
                $statusLabel = $enrollStatusLabels[$status] ?? ucfirst($status);
                $statusColor = $enrollStatusColors[$status] ?? 'bg-gray-100 text-gray-700';
            ?>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="<?php echo $cfg['icon']; ?> mr-1"></i><?php echo $cfg['label']; ?>
                </label>
                <div class="flex items-center gap-3">
                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?php echo $statusColor; ?>">
                        <?php echo $statusLabel; ?>
                    </span>
                    <?php if (!empty($sentAt)): ?>
                    <span class="text-sm text-gray-500">
                        Último envío: <?php echo date('d/m/Y H:i', strtotime($sentAt)); ?>
                    </span>
                    <?php endif; ?>

                    <?php if ($canResend): ?>
                    <div class="ml-auto flex items-center gap-2">
                        <button onclick="<?php echo $cfg['fn']; ?>('<?php echo $Athlete['id']; ?>')" id="<?php echo $cfg['btnId']; ?>"
                                class="<?php echo $cfg['btnClass']; ?> text-white font-medium py-2 px-4 rounded-lg text-sm">
                            <i class="<?php echo $cfg['btnIcon']; ?> mr-2"></i><?php echo $cfg['btnText']; ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($status === 'failed' && !empty($error)): ?>
                <p class="text-xs text-red-600 mt-2">Último error: <?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <!-- Descargas (disponibles para todos los roles) -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-download text-gray-500 mr-1"></i>Descargas
                </label>
                <div class="flex items-center gap-2 flex-wrap">
                    <a href="<?php echo BASE_URL; ?>athletes/downloadEnrollmentForm?id=<?php echo $Athlete['id']; ?>"
                       class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-download mr-2"></i>Descargar planilla
                    </a>
                    <a href="<?php echo BASE_URL; ?>athletes/downloadAthleteCard?id=<?php echo $Athlete['id']; ?>"
                       class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg text-sm">
                        <i class="fas fa-download mr-2"></i>Descargar ficha técnica
                    </a>
                </div>
            </div>

            <!-- Retiro y solvencia -->
            <?php
            $isWithdrawn = !empty($Athlete['withdrawn_at']);
            $canClear = in_array($_SESSION['admin_role'], ['administrador', 'secretario']);
            $statusLabels = ['active' => 'Activo', 'inactive' => 'Inactivo', 'suspended' => 'Suspendido'];
            $statusColors = [
                'active'    => 'bg-green-100 text-green-800',
                'inactive'  => 'bg-gray-100 text-gray-700',
                'suspended' => 'bg-yellow-100 text-yellow-800',
            ];
            $athleteStatus = $Athlete['status'] ?? 'active';
            ?>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-file-signature text-gray-500 mr-1"></i>Estado y retiro
                </label>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full <?php echo $statusColors[$athleteStatus] ?? 'bg-gray-100 text-gray-700'; ?>">
                        <?php echo $statusLabels[$athleteStatus] ?? ucfirst($athleteStatus); ?>
                    </span>

                    <?php if ($isWithdrawn): ?>
                        <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-red-100 text-red-800">
                            Retirado el <?php echo date('d/m/Y', strtotime($Athlete['withdrawn_at'])); ?>
                        </span>
                        <span class="text-sm text-gray-500">Solvencia <?php echo htmlspecialchars($Athlete['clearance_number']); ?></span>
                        <div class="ml-auto flex items-center gap-2">
                            <a href="<?php echo BASE_URL; ?>athletes/downloadClearance?id=<?php echo (int)$Athlete['id']; ?>"
                               class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg text-sm">
                                <i class="fas fa-download mr-2"></i>Descargar solvencia
                            </a>
                        </div>
                    <?php elseif ($canClear): ?>
                        <div class="ml-auto flex items-center gap-2">
                            <a href="<?php echo BASE_URL; ?>athletes/clearance?id=<?php echo (int)$Athlete['id']; ?>"
                               class="bg-red-700 hover:bg-red-800 text-white font-medium py-2 px-4 rounded-lg text-sm">
                                <i class="fas fa-file-signature mr-2"></i>Solvencia de retiro
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($isWithdrawn && !empty($Athlete['clearance_issued_by']) && in_array($_SESSION['admin_role'], ['administrador', 'secretario'], true)): ?>
                <p class="text-xs text-gray-500 mt-2">Emitida por <?php echo htmlspecialchars($Athlete['clearance_issued_by']); ?>.</p>
                <?php endif; ?>

                <?php if (!empty($statusDetail)): ?>
                <?php $isSusp = $statusDetail['status'] === 'suspended'; ?>
                <div class="status-detail <?php echo $isSusp ? 'status-detail--suspended' : 'status-detail--inactive'; ?>">
                    <p class="status-detail__title">
                        <i class="fas <?php echo $isSusp ? 'fa-pause-circle' : 'fa-user-slash'; ?> mr-1"></i>
                        Atleta <?php echo mb_strtolower(htmlspecialchars($statusDetail['label'])); ?>
                        <?php if (!empty($statusDetail['since'])): ?>
                            desde el <?php echo date('d/m/Y', strtotime($statusDetail['since'])); ?>
                        <?php endif; ?>
                    </p>

                    <?php if ($statusDetail['full']): ?>
                        <?php if (!empty($statusDetail['reason'])): ?>
                            <p class="status-detail__label">Motivo</p>
                            <p class="status-detail__reason"><?php echo htmlspecialchars($statusDetail['reason']); ?></p>
                            <?php if (!empty($statusDetail['by_name'])): ?>
                            <p class="status-detail__meta">
                                Registrado por <?php echo htmlspecialchars($statusDetail['by_name']); ?><?php
                                    $roleNames = ['administrador' => 'administrador', 'secretario' => 'secretario', 'retiro' => 'solvencia de retiro'];
                                    $r = $statusDetail['by_role'] ?? '';
                                    echo $r !== '' ? ' (' . htmlspecialchars($roleNames[$r] ?? $r) . ')' : '';
                                ?><?php echo !empty($statusDetail['since']) ? ' · ' . date('d/m/Y H:i', strtotime($statusDetail['since'])) : ''; ?>
                            </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="status-detail__meta">
                                Motivo no registrado: este cambio se hizo antes de que el sistema guardara el detalle del estatus.
                                Al editar al atleta y cambiar su estatus se registrará con motivo.
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="status-detail__meta">
                            <?php echo $isSusp
                                ? 'El atleta no puede participar mientras esté suspendido. Para más información comunícate con la escuela.'
                                : 'El atleta no figura como activo en la escuela. Para más información comunícate con la escuela.'; ?>
                        </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <?php /* Constancia de pertenencia (solo administrador y secretario) */ ?>
            <?php if ($canClear): ?>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-certificate text-gray-500 mr-1"></i>Constancia de pertenencia
                </label>
                <div class="flex items-center gap-3 flex-wrap">
                    <?php if (($Athlete['status'] ?? '') === 'active' && !$isWithdrawn): ?>
                        <span class="text-sm text-gray-500">Acredita que el atleta forma parte de la escuela. Válida por <?php echo (int)MembershipCertificate::VALIDITY_DAYS; ?> días.</span>
                        <div class="ml-auto flex items-center gap-2">
                            <a href="<?php echo BASE_URL; ?>athletes/certificate?id=<?php echo (int)$Athlete['id']; ?>"
                               class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg text-sm">
                                <i class="fas fa-certificate mr-2"></i>Constancia
                            </a>
                        </div>
                    <?php else: ?>
                        <span class="text-sm text-gray-500">Solo se emite a atletas activos.</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function sendEnrollmentAction(url, id, btnId, okMessage, errPrefix, originalHtml) {
    const btn = document.getElementById(btnId);
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...'; }

    ajaxRequest(url, {id: id}, function(response) {
        if (response.success) {
            alert(okMessage);
            location.reload();
        } else {
            alert(errPrefix + (response.message || 'Error desconocido'));
            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
        }
    });
}

function resendEnrollmentEmail(id) {
    sendEnrollmentAction(
        '<?php echo BASE_URL; ?>athletes/resendEnrollmentEmail', id, 'resend-enroll-email-btn',
        'Planilla enviada por correo correctamente.',
        'Error al enviar la planilla por correo: ',
        '<i class="fas fa-envelope mr-2"></i>Reenviar por Correo'
    );
}

function resendEnrollmentWhatsApp(id) {
    sendEnrollmentAction(
        '<?php echo BASE_URL; ?>athletes/resendEnrollmentWhatsApp', id, 'resend-enroll-wa-btn',
        'Planilla enviada por WhatsApp correctamente.',
        'Error al enviar la planilla por WhatsApp: ',
        '<i class="fab fa-whatsapp mr-2"></i>Reenviar por WhatsApp'
    );
}
</script>