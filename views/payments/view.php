<?php
/** @var array $payment @var array $history @var string[] $actions @var array $methods */
$role   = $_SESSION['admin_role'];
$status = $payment['status'];
$isStaff = in_array($role, ['administrador', 'secretario'], true);
$hasReceipt = !empty($payment['receipt_image']) && file_exists($payment['receipt_image']);
$receiptUrl = BASE_URL . 'payments/receiptImage?id=' . (int)$payment['id'];

$errorMessages = [
    'reference'          => 'La referencia debe tener solo números, entre 4 y 20 dígitos, sin espacios, guiones ni letras. Escríbela completa, tal como aparece en tu comprobante.',
    'invalid_reference'  => 'La referencia debe tener solo números, entre 4 y 20 dígitos, sin espacios, guiones ni letras.',
    'method'             => 'Selecciona un método de pago válido.',
    'date'               => 'La fecha de pago no es válida (no puede ser futura ni de hace más de un año).',
    'receipt_upload'     => 'No se pudo subir el comprobante. Usa una imagen JPG, PNG o WEBP de hasta 5 MB.',
    'receipt_required'   => 'Adjunta la captura o foto del comprobante (solo el pago en efectivo no la requiere).',
    'report_incomplete'  => 'Completa la referencia y el método de pago.',
    'duplicate_reference'=> 'Esa referencia bancaria ya está asociada a otro pago.',
    'invalid_transition' => 'El pago ya cambió de estado. Se muestra la información actualizada.',
    'forbidden_role'     => 'Tu rol no tiene permiso para esa acción.',
    'locked'             => 'Un pago en revisión o aprobado no se puede modificar.',
    'not_approved'       => 'El recibo solo está disponible para pagos aprobados.',
    'pdf_failed'         => 'No se pudo generar el recibo. Inténtalo de nuevo.',
    'conflict'           => 'El pago cambió mientras lo procesabas. Recarga e inténtalo de nuevo.',
];
$successMessages = ['reported' => 'Pago reportado. Quedó En Revisión y el equipo fue avisado para conciliarlo.'];
$err = $_GET['error'] ?? '';
$ok  = $_GET['success'] ?? '';

$actionLabels = [
    'report' => 'Reportó el pago', 'approve' => 'Aprobó el pago', 'reject' => 'Rechazó el pago',
    'reopen' => 'Reabrió el pago', 'create' => 'Generó el cobro', 'migrate' => 'Pago ya cobrado (migración)',
    'update' => 'Editó el cobro',
];
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-receipt text-red-700"></i>
                Detalles del Pago
            </h2>
            <p class="text-black mt-1">
                <?php echo htmlspecialchars($payment['concept_name'] ?? 'Pago'); ?> ·
                <?php echo htmlspecialchars($payment['month'] . ' ' . $payment['year']); ?>
            </p>
        </div>
        <a href="<?php echo BASE_URL; ?>payments" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <?php if ($err !== ''): ?>
    <div class="pay-notice pay-notice--rejected"><p class="pay-notice__text"><?php echo htmlspecialchars($errorMessages[$err] ?? 'No se pudo completar la operación.'); ?></p></div>
    <?php endif; ?>
    <?php if ($ok !== '' && isset($successMessages[$ok])): ?>
    <div class="pay-notice pay-notice--approved"><p class="pay-notice__text"><?php echo htmlspecialchars($successMessages[$ok]); ?></p></div>
    <?php endif; ?>

    <!-- Aviso segun el estado: dice QUE pasa ahora y QUE toca hacer -->
    <?php if ($status === 'pending'): ?>
    <div class="pay-notice pay-notice--pending">
        <p class="pay-notice__title">Pendiente de pago</p>
        <p class="pay-notice__text">Aún no se ha reportado ningún pago para este cobro.<?php echo Payment::isPastDue($payment) ? ' <strong>Venció el ' . date('d/m/Y', strtotime($payment['due_date'])) . '.</strong>' : ''; ?></p>
    </div>
    <?php elseif ($status === 'in_review'): ?>
    <div class="pay-notice pay-notice--review">
        <p class="pay-notice__title">En revisión</p>
        <p class="pay-notice__text">El pago fue reportado y espera que un secretario o el administrador concilie la referencia bancaria. Mientras tanto no se considera cobrado.</p>
    </div>
    <?php elseif ($status === 'rejected'): ?>
    <div class="pay-notice pay-notice--rejected">
        <p class="pay-notice__title">Pago rechazado</p>
        <p class="pay-notice__text">Motivo: <?php echo htmlspecialchars($payment['review_comment'] ?? 'No indicado'); ?></p>
        <p class="pay-notice__text">Puedes volver a reportarlo con los datos correctos.</p>
    </div>
    <?php elseif ($status === 'approved'): ?>
    <div class="pay-notice pay-notice--approved">
        <p class="pay-notice__title">Pago aprobado</p>
        <p class="pay-notice__text">Verificado<?php echo !empty($payment['reviewed_by_name']) ? ' por ' . htmlspecialchars($payment['reviewed_by_name']) : ''; ?><?php echo !empty($payment['reviewed_at']) ? ' el ' . date('d/m/Y H:i', strtotime($payment['reviewed_at'])) : ''; ?>. Este pago está cerrado y no se puede modificar.</p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Atleta</h3>
                <div class="space-y-1 text-sm">
                    <p><span class="font-semibold text-gray-700">Atleta:</span> <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></span></p>
                    <p><span class="font-semibold text-gray-700">Categoría:</span> <span class="text-gray-900"><?php echo htmlspecialchars($payment['category'] ?? 'N/A'); ?></span></p>
                    <p><span class="font-semibold text-gray-700">Representante:</span> <span class="text-gray-900"><?php echo htmlspecialchars($payment['parent_name'] ?? 'N/A'); ?></span></p>
                    <?php if ($isStaff): ?>
                    <p><span class="font-semibold text-gray-700">Teléfono:</span> <span class="text-gray-900"><?php echo htmlspecialchars($payment['parent_phone'] ?? 'N/A'); ?></span></p>
                    <p><span class="font-semibold text-gray-700">Email:</span> <span class="text-gray-900"><?php echo htmlspecialchars($payment['parent_email'] ?? 'N/A'); ?></span></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Cobro</h3>
                <div class="space-y-1 text-sm">
                    <p><span class="font-semibold text-gray-700">Concepto:</span> <span class="text-gray-900"><?php echo htmlspecialchars($payment['concept_name'] ?? 'N/A'); ?></span></p>
                    <p><span class="font-semibold text-gray-700">Monto:</span> <span class="text-gray-900 font-medium">Bs. <?php echo number_format($payment['amount'], 2); ?></span></p>
                    <p><span class="font-semibold text-gray-700">Vencimiento:</span>
                        <span class="font-medium <?php echo Payment::isPastDue($payment) ? 'text-red-600' : 'text-gray-900'; ?>">
                            <?php echo date('d/m/Y', strtotime($payment['due_date'])); ?><?php echo Payment::isPastDue($payment) ? ' (vencido)' : ''; ?>
                        </span>
                    </p>
                    <p><span class="font-semibold text-gray-700">Estado:</span>
                        <span class="inline-flex px-3 py-1 text-xs font-medium rounded-full <?php echo PaymentWorkflow::badge($status); ?>"><?php echo PaymentWorkflow::label($status); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <?php if (!empty($payment['reference_number'])): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t text-sm">
            <p><span class="font-semibold text-gray-700 block">Referencia reportada</span><span class="text-gray-900"><?php echo htmlspecialchars($payment['reference_number']); ?></span></p>
            <p><span class="font-semibold text-gray-700 block">Método</span><span class="text-gray-900"><?php echo htmlspecialchars($methods[$payment['payment_method']] ?? $payment['payment_method']); ?></span></p>
            <p><span class="font-semibold text-gray-700 block">Fecha de pago</span><span class="text-gray-900"><?php echo !empty($payment['payment_date']) ? date('d/m/Y', strtotime($payment['payment_date'])) : '—'; ?></span></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($payment['notes'])): ?>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Notas</label>
            <div class="mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200"><p class="text-gray-900"><?php echo htmlspecialchars($payment['notes']); ?></p></div>
        </div>
        <?php endif; ?>

        <!-- Comprobante: se sirve por un endpoint autenticado, nunca por URL directa -->
        <?php if ($hasReceipt): ?>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-paperclip text-gray-500 mr-1"></i>Comprobante de pago</label>
            <div class="flex items-start gap-4">
                <div class="cursor-pointer" onclick="openReceiptLightbox()">
                    <img src="<?php echo $receiptUrl; ?>" alt="Comprobante de pago" class="max-h-48 rounded-lg border border-gray-200 shadow-sm hover:opacity-90 transition-opacity">
                </div>
                <a href="<?php echo $receiptUrl; ?>" target="_blank" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-external-link-alt"></i>Ver en tamaño completo
                </a>
            </div>
            <div id="receipt-lightbox" class="lightbox" onclick="closeReceiptLightbox()">
                <div class="lightbox__content" onclick="event.stopPropagation()">
                    <img src="<?php echo $receiptUrl; ?>" alt="Comprobante de pago" class="lightbox__image">
                    <button onclick="closeReceiptLightbox()" class="lightbox__close"><i class="fas fa-times text-gray-700"></i></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recibo y envios: solo cuando el pago esta aprobado -->
        <?php if ($status === 'approved'): ?>
        <div class="mt-6 pt-6 border-t">
            <label class="block text-sm font-medium text-gray-700 mb-3"><i class="fas fa-file-invoice text-gray-500 mr-1"></i>Recibo</label>
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?php echo BASE_URL; ?>payments/downloadReceipt?id=<?php echo (int)$payment['id']; ?>" class="bg-gray-700 hover:bg-gray-800 text-white font-medium py-2 px-4 rounded-lg text-sm">
                    <i class="fas fa-download mr-2"></i>Descargar recibo
                </a>
                <?php if ($isStaff): ?>
                    <?php
                    $channels = [
                        'wa'    => ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp text-green-600', 'status' => $payment['whatsapp_status'] ?? 'not_sent', 'at' => $payment['whatsapp_sent_at'] ?? null, 'err' => $payment['whatsapp_error'] ?? null, 'btn' => 'bg-green-600 hover:bg-green-700', 'fn' => 'resendWhatsApp'],
                        'email' => ['label' => 'Correo',   'icon' => 'fas fa-envelope text-blue-600',  'status' => $payment['email_status'] ?? 'not_sent',    'at' => $payment['email_sent_at'] ?? null,    'err' => $payment['email_error'] ?? null,    'btn' => 'bg-blue-600 hover:bg-blue-700',   'fn' => 'resendEmail'],
                    ];
                    $stLabels = ['sent' => 'Enviado', 'failed' => 'Fallido', 'not_sent' => 'No enviado'];
                    $stColors = ['sent' => 'bg-green-100 text-green-800', 'failed' => 'bg-red-100 text-red-800', 'not_sent' => 'bg-gray-100 text-gray-700'];
                    foreach ($channels as $key => $ch): ?>
                    <div class="flex items-center gap-2">
                        <i class="<?php echo $ch['icon']; ?>"></i>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full <?php echo $stColors[$ch['status']] ?? $stColors['not_sent']; ?>"><?php echo $stLabels[$ch['status']] ?? 'No enviado'; ?></span>
                        <button onclick="<?php echo $ch['fn']; ?>('<?php echo (int)$payment['id']; ?>')" id="resend-<?php echo $key; ?>-btn" class="<?php echo $ch['btn']; ?> text-white font-medium py-1 px-3 rounded-lg text-xs">Reenviar</button>
                    </div>
                    <?php if ($ch['status'] === 'failed' && !empty($ch['err'])): ?>
                    <p class="text-xs text-red-600 w-full">Último error (<?php echo $ch['label']; ?>): <?php echo htmlspecialchars($ch['err']); ?></p>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario para reportar el pago (representante, o personal en su nombre) -->
        <?php if (in_array('report', $actions, true)): ?>

        <!-- Datos bancarios de la escuela: donde depositar. Los carga el administrador en Conceptos. -->
        <?php if (!empty($bank) && (SchoolBankAccount::hasTransfer($bank) || SchoolBankAccount::hasMobile($bank))): ?>
        <div class="mt-6 pt-6 border-t">
            <div class="bank-card">
                <h3 class="bank-card__title"><i class="fas fa-university mr-2"></i>Datos para realizar tu pago</h3>
                <p class="bank-card__hint">Deposita o transfiere el monto de este cobro (<strong>Bs. <?php echo number_format($payment['amount'], 2); ?></strong>) a una de estas cuentas y luego registra aquí la referencia y el comprobante.</p>
                <dl class="bank-card__grid">
                    <?php if (!empty($bank['holder_name'])): ?>
                    <div><dt>Titular</dt><dd class="bank-copy-value copy-holder-name"><?php echo htmlspecialchars($bank['holder_name']); ?></dd><button type="button" class="bank-copy" data-copy-target=".copy-holder-name" title="Copiar"><i class="fas fa-copy"></i><span>Copiar</span></button></div>
                    <?php endif; ?>
                    <?php if (!empty($bank['holder_id'])): ?>
                    <div><dt>Cédula / RIF</dt><dd class="bank-card__mono bank-copy-value copy-holder-id"><?php echo htmlspecialchars($bank['holder_id']); ?></dd><button type="button" class="bank-copy" data-copy-target=".copy-holder-id" title="Copiar"><i class="fas fa-copy"></i><span>Copiar</span></button></div>
                    <?php endif; ?>
                </dl>
                <?php if (SchoolBankAccount::hasTransfer($bank)): ?>
                <div class="bank-card__method">
                    <p class="bank-card__method-title">Transferencia bancaria</p>
                    <dl class="bank-card__grid">
                        <div><dt>Banco</dt><dd class="bank-copy-value copy-bank-name"><?php echo htmlspecialchars($bank['bank_name']); ?></dd><button type="button" class="bank-copy" data-copy-target=".copy-bank-name" title="Copiar"><i class="fas fa-copy"></i><span>Copiar</span></button></div>
                        <div><dt>Tipo de cuenta</dt><dd><?php echo htmlspecialchars(ucfirst((string)$bank['account_type'])); ?></dd></div>
                        <div class="bank-card__wide"><dt>N.° de cuenta</dt><dd class="bank-card__mono bank-copy-value copy-account-number"><?php echo htmlspecialchars($bank['account_number']); ?></dd><button type="button" class="bank-copy" data-copy-target=".copy-account-number" title="Copiar"><i class="fas fa-copy"></i><span>Copiar</span></button></div>
                    </dl>
                </div>
                <?php endif; ?>
                <?php if (SchoolBankAccount::hasMobile($bank)): ?>
                <div class="bank-card__method">
                    <p class="bank-card__method-title">Pago móvil</p>
                    <dl class="bank-card__grid">
                        <div><dt>Banco</dt><dd><?php echo htmlspecialchars($bank['mobile_bank']); ?></dd></div>
                        <div><dt>Teléfono</dt><dd class="bank-card__mono bank-copy-value copy-mobile-phone"><?php echo htmlspecialchars($bank['mobile_phone']); ?></dd><button type="button" class="bank-copy" data-copy-target=".copy-mobile-phone" title="Copiar"><i class="fas fa-copy"></i><span>Copiar</span></button></div>
                        <div><dt>Cédula / RIF</dt><dd class="bank-card__mono bank-copy-value copy-mobile-id"><?php echo htmlspecialchars($bank['mobile_id']); ?></dd><button type="button" class="bank-copy" data-copy-target=".copy-mobile-id" title="Copiar"><i class="fas fa-copy"></i><span>Copiar</span></button></div>
                    </dl>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php elseif ($bank !== null): ?>
        <div class="mt-6 pt-6 border-t">
            <div class="pay-notice pay-notice--pending">
                <p class="pay-notice__title">Datos de pago no disponibles</p>
                <p class="pay-notice__text">La escuela aún no ha registrado sus datos bancarios en el sistema. Consulta con la escuela a qué cuenta debes depositar antes de reportar tu pago.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-6 pt-6 border-t">
            <h3 class="text-lg font-semibold text-gray-900 mb-1"><?php echo $status === 'rejected' ? 'Volver a reportar el pago' : 'Reportar un pago'; ?></h3>
            <p class="text-sm text-gray-600 mb-4">Indica los datos de tu transferencia o pago móvil y adjunta la captura. El secretario la verificará contra la cuenta de la escuela.</p>
            <form method="POST" action="<?php echo BASE_URL; ?>payments/report" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" name="id" value="<?php echo (int)$payment['id']; ?>">
                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Número de referencia completo</label>
                    <?php /* type="text" + inputmode="numeric" y NO type="number": una referencia puede empezar por ceros (004812377) y
                             type="number" los eliminaria; ademas admite e, + y -. El servidor revalida: esto solo ayuda al escribir. */ ?>
                    <input type="text" id="reference_number" name="reference_number" required inputmode="numeric" autocomplete="off" minlength="4" maxlength="20" pattern="[0-9]{4,20}" title="Solo números, entre 4 y 20 dígitos" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700" placeholder="Ej: 004812377">
                    <p class="text-xs text-gray-500 mt-1">Solo números, completo, tal como aparece en tu comprobante (con los ceros del inicio, si los tiene).</p>
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Método de pago</label>
                    <select id="payment_method" name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                        <?php foreach ($methods as $k => $label): ?>
                        <option value="<?php echo htmlspecialchars($k); ?>"><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha en que pagaste</label>
                    <input type="date" id="payment_date" name="payment_date" required max="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d', strtotime('-1 year')); ?>" value="<?php echo date('Y-m-d'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700">
                </div>
                <div>
                    <label for="receipt_image" class="block text-sm font-medium text-gray-700 mb-1">Comprobante (JPG, PNG o WEBP, máx. 5 MB)</label>
                    <input type="file" id="receipt_image" name="receipt_image" accept="image/jpeg,image/png,image/webp" class="w-full text-sm text-gray-700">
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="btn-primary text-white font-medium py-2 px-5 rounded-lg"><i class="fas fa-paper-plane mr-2"></i>Enviar a revisión</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Acciones del personal -->
        <?php
        $canApprove = in_array('approve', $actions, true);
        $canReject  = in_array('reject', $actions, true);
        $canReopen  = in_array('reopen', $actions, true);
        $canEdit    = $role === 'administrador' && in_array($status, ['pending', 'rejected'], true); // editar cobros: solo administrador
        // Quien registro el pago no puede conciliarlo (secretario o administrador): se le explica por que no hay botones.
        $selfNote   = in_array($role, ['secretario', 'administrador'], true) && $status === 'in_review' && !$canApprove && (int)($payment['reported_by'] ?? 0) === (int)$_SESSION['admin_id'];
        ?>
        <?php if ($canApprove || $canReject || $canReopen || $canEdit || $selfNote): ?>
        <div class="flex flex-wrap justify-end items-center gap-3 mt-6 pt-6 border-t">
            <?php if ($selfNote): ?>
            <p class="text-sm text-gray-600 ml-auto"><i class="fas fa-user-lock mr-1"></i>Registraste este pago tú mismo: lo debe conciliar otra persona (secretario o administrador).</p>
            <?php endif; ?>
            <?php if ($canEdit): ?>
            <a href="<?php echo BASE_URL; ?>payments/edit?id=<?php echo (int)$payment['id']; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg"><i class="fas fa-edit mr-2"></i>Editar cobro</a>
            <?php endif; ?>
            <?php if ($canReject): ?>
            <button type="button" onclick="openReasonModal('reject')" class="btn-reject"><i class="fas fa-times-circle mr-2"></i>Rechazar</button>
            <?php endif; ?>
            <?php if ($canApprove): ?>
            <button type="button" onclick="openReasonModal('approve')" class="btn-primary text-white font-medium py-2 px-4 rounded-lg"><i class="fas fa-check-circle mr-2"></i>Aprobar</button>
            <?php endif; ?>
            <?php if ($canReopen): ?>
            <button type="button" onclick="openReasonModal('reopen')" class="btn-warning"><i class="fas fa-undo mr-2"></i>Reabrir pago</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Historial: quien hizo que y cuando. Solo-lectura. -->
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><i class="fas fa-history text-gray-500 mr-2"></i>Historial</h3>
        <ol class="pay-timeline">
            <?php foreach (array_reverse($history) as $h): ?>
            <li class="pay-timeline__item pay-timeline__item--<?php echo htmlspecialchars($h['action']); ?>">
                <span class="pay-timeline__dot"></span>
                <p class="pay-timeline__title">
                    <?php echo htmlspecialchars($actionLabels[$h['action']] ?? $h['action']); ?>
                    <?php if ($h['from_status'] !== null): ?>
                    <span class="text-xs font-normal text-gray-500">(<?php echo PaymentWorkflow::label($h['from_status'] === 'paid' ? 'approved' : $h['from_status']); ?> → <?php echo PaymentWorkflow::label($h['to_status']); ?>)</span>
                    <?php endif; ?>
                </p>
                <p class="pay-timeline__meta"><?php echo htmlspecialchars($h['actor_name']); ?> · <?php echo htmlspecialchars(ucfirst($h['actor_role'])); ?> · <?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></p>
                <?php if (!empty($h['comment'])): ?><p class="pay-timeline__note"><?php echo htmlspecialchars($h['comment']); ?></p><?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
</div>

<!-- Modal de confirmacion / motivo -->
<div id="reason-modal" class="modal-overlay">
    <div class="modal-box modal-box--wide">
        <div class="modal-header">
            <div>
                <h3 id="reason-title" class="modal-title"></h3>
                <p id="reason-subtitle" class="modal-subtitle"></p>
            </div>
        </div>
        <label id="reason-label" for="reason-text" class="block text-sm font-medium text-gray-700 mb-1"></label>
        <textarea id="reason-text" class="pay-modal__textarea" maxlength="500"></textarea>
        <p id="reason-count" class="pay-modal__count">0 / 500</p>
        <p id="reason-error" class="pay-modal__error"></p>
        <div class="modal-actions">
            <button type="button" id="reason-confirm" class="btn-primary text-white font-medium py-2 px-4 rounded-lg"></button>
            <button type="button" id="reason-cancel" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg">Cancelar</button>
        </div>
    </div>
</div>

<script>
// Campo de referencia: solo digitos. Descarta cualquier otra cosa al escribir y, sobre todo, al PEGAR (copiar de un mensaje del
// banco suele traer espacios, guiones o texto). No altera el numero: solo quita lo que no es digito. El servidor revalida.
(function () {
    const input = document.getElementById('reference_number');
    if (!input) return;
    function clean() {
        const digits = input.value.replace(/\D+/g, '').slice(0, 20);
        if (digits !== input.value) { input.value = digits; }
    }
    input.addEventListener('input', clean);
    input.addEventListener('paste', function () { setTimeout(clean, 0); });
    input.addEventListener('keypress', function (e) { if (e.key.length === 1 && !/[0-9]/.test(e.key) && !e.ctrlKey && !e.metaKey) { e.preventDefault(); } });
})();

// --- Copiar datos bancarios (estilo Cashea): cada dato tiene su boton Copiar ---
(function () {
    let toastTimer = null;
    function toast(msg) {
        let t = document.getElementById('bank-copy-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'bank-copy-toast';
            t.className = 'bank-copy-toast';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.classList.add('bank-copy-toast--show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { t.classList.remove('bank-copy-toast--show'); }, 2200);
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        // Fallback sin HTTPS (XAMPP local): textarea oculto + execCommand
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) { return Promise.reject(e); }
        document.body.removeChild(ta);
        return Promise.resolve();
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.bank-copy');
        if (!btn) return;
        const valueEl = document.querySelector(btn.dataset.copyTarget);
        if (!valueEl) return;
        copyText(valueEl.textContent.trim()).then(function () {
            const label = btn.querySelector('span');
            const icon = btn.querySelector('i');
            btn.classList.add('bank-copy--done');
            if (label) label.textContent = '¡Copiado!';
            if (icon) { icon.classList.replace('fa-copy', 'fa-check'); }
            toast('Copiado: ' + valueEl.textContent.trim());
            setTimeout(function () {
                btn.classList.remove('bank-copy--done');
                if (label) label.textContent = 'Copiar';
                if (icon) { icon.classList.replace('fa-check', 'fa-copy'); }
            }, 2200);
        }).catch(function () {
            toast('No se pudo copiar automáticamente; selecciónalo y copia manualmente.');
        });
    });
})();
</script>
<script>
(function () {
    const PAYMENT_ID = '<?php echo (int)$payment['id']; ?>';
    const BASE = '<?php echo BASE_URL; ?>';
    const CONFIG = {
        approve: { url: 'payments/approve', title: 'Aprobar pago',  sub: 'Confirmas que la referencia coincide con el banco.', label: 'Nota (opcional)', required: false, btn: 'Aprobar y enviar recibo' },
        reject:  { url: 'payments/reject',  title: 'Rechazar pago', sub: 'El representante recibirá este motivo por correo.',   label: 'Motivo del rechazo',  required: true,  btn: 'Rechazar pago' },
        reopen:  { url: 'payments/reopen',  title: 'Reabrir pago aprobado', sub: 'Vuelve a En Revisión y el recibo anterior queda sin efecto.', label: 'Motivo de la reapertura', required: true, btn: 'Reabrir pago' }
    };
    let current = null;

    const modal = document.getElementById('reason-modal');
    const text = document.getElementById('reason-text');
    const err = document.getElementById('reason-error');
    const confirmBtn = document.getElementById('reason-confirm');

    window.openReasonModal = function (kind) {
        current = kind;
        const c = CONFIG[kind];
        document.getElementById('reason-title').textContent = c.title;
        document.getElementById('reason-subtitle').textContent = c.sub;
        document.getElementById('reason-label').textContent = c.label;
        confirmBtn.textContent = c.btn;
        text.value = ''; err.classList.remove('is-visible');
        document.getElementById('reason-count').textContent = '0 / 500';
        modal.classList.add('is-open');
        setTimeout(function () { text.focus(); }, 80);
    };
    function close() { modal.classList.remove('is-open'); current = null; }

    text.addEventListener('input', function () {
        document.getElementById('reason-count').textContent = text.value.length + ' / 500';
        err.classList.remove('is-visible');
    });
    document.getElementById('reason-cancel').addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { close(); closeReceiptLightbox(); } });

    confirmBtn.addEventListener('click', function () {
        const c = CONFIG[current];
        const comment = text.value.trim();
        if (c.required && comment.length < 5) {
            err.textContent = 'Escribe un motivo de al menos 5 caracteres.';
            err.classList.add('is-visible');
            return;
        }
        confirmBtn.disabled = true;
        const original = confirmBtn.textContent;
        confirmBtn.textContent = 'Procesando...';
        ajaxRequest(BASE + c.url, { id: PAYMENT_ID, comment: comment }, function (r) {
            confirmBtn.disabled = false; confirmBtn.textContent = original;
            if (r.success) { location.reload(); return; }
            err.textContent = r.message || 'No se pudo completar la operación.';
            err.classList.add('is-visible');
            if (r.code === 'invalid_transition' || r.code === 'conflict') { setTimeout(function () { location.reload(); }, 1800); }
        });
    });
})();

function resendWhatsApp(id) { resend('payments/resendWhatsApp', 'resend-wa-btn', id, 'WhatsApp'); }
function resendEmail(id)    { resend('payments/resendEmail',    'resend-email-btn', id, 'correo'); }
function resend(url, btnId, id, label) {
    const btn = document.getElementById(btnId);
    if (btn) { btn.disabled = true; btn.textContent = 'Enviando...'; }
    ajaxRequest('<?php echo BASE_URL; ?>' + url, { id: id }, function (r) {
        if (r.success) { alert('Recibo enviado por ' + label + '.'); location.reload(); return; }
        alert('No se pudo enviar por ' + label + ': ' + (r.message || 'error desconocido'));
        if (btn) { btn.disabled = false; btn.textContent = 'Reenviar'; }
    });
}

function openReceiptLightbox() {
    const lb = document.getElementById('receipt-lightbox');
    if (lb) { lb.classList.add('is-open'); document.body.classList.add('body-locked'); }
}
function closeReceiptLightbox() {
    const lb = document.getElementById('receipt-lightbox');
    if (lb) { lb.classList.remove('is-open'); document.body.classList.remove('body-locked'); }
}
</script>
