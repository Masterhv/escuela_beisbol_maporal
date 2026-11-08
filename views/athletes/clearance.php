<?php
/**
 * Pantalla de confirmación de la solvencia de retiro.
 * Variables: $Athlete (array), $eligibility (array, ver Athlete::checkClearanceEligibility).
 * La emisión real es un POST a athletes/issueClearance; esta pantalla solo informa y pide confirmar.
 */
$athleteName = trim($Athlete['first_name'] . ' ' . $Athlete['last_name']);
$eligible = !empty($eligibility['eligible']);
$pending = $eligibility['pending'] ?? [];
$paymentsCount = (int)($eligibility['payments_count'] ?? 0);

/* Motivos de rechazo que pueden llegar por la URL tras un intento de emisión fallido */
$errorMessages = [
    'pending_payments'  => 'No se pudo emitir: el atleta tiene pagos pendientes. Revise la lista y vuelva a intentarlo.',
    'already_withdrawn' => 'Este atleta ya tiene una solvencia de retiro emitida.',
    'failed'            => 'Ocurrió un error al emitir la solvencia. No se realizó ningún cambio; intente de nuevo.',
];
$errorKey = $_GET['error'] ?? null;
$errorText = $errorKey ? ($errorMessages[$errorKey] ?? 'No se pudo emitir la solvencia.') : null;
?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-file-signature text-black"></i>
                Solvencia de Retiro
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

        <?php if ($eligible): ?>

            <!-- Elegible -->
            <div class="bg-green-50 border border-green-400 text-green-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <div>
                    <p class="font-semibold">El atleta no tiene pagos pendientes.</p>
                    <p class="text-sm mt-1">Se puede emitir la solvencia de retiro.</p>
                </div>
            </div>

            <?php if ($paymentsCount === 0): ?>
            <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-triangle mt-1"></i>
                <div>
                    <p class="font-semibold">Este atleta no tiene ningún pago registrado.</p>
                    <p class="text-sm mt-1">El sistema solo conoce los pagos que se registraron. Confirme que el representante no adeuda mensualidades antes de emitir la solvencia.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-user-slash mt-1"></i>
                <div>
                    <p class="font-semibold">Al emitirla, el atleta quedará inactivo.</p>
                    <p class="text-sm mt-1">Se registrará el retiro con la fecha de hoy y su número de solvencia. Para volver a activarlo habrá que editar su ficha, y eso anulará la solvencia.</p>
                </div>
            </div>

            <form method="POST" action="<?php echo BASE_URL; ?>athletes/issueClearance" class="flex flex-wrap items-center gap-3 pt-2">
                <input type="hidden" name="id" value="<?php echo (int)$Athlete['id']; ?>">
                <button type="submit" class="btn-primary text-white font-medium py-2 px-5 rounded-lg flex items-center gap-2">
                    <i class="fas fa-file-signature"></i>
                    Emitir solvencia y retirar al atleta
                </button>
                <a href="<?php echo BASE_URL; ?>athletes/view?id=<?php echo (int)$Athlete['id']; ?>"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-5 rounded-lg transition-colors">
                    Cancelar
                </a>
            </form>

        <?php else: ?>

            <!-- No elegible: pagos pendientes -->
            <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg p-4 flex items-start gap-3">
                <i class="fas fa-ban mt-1"></i>
                <div>
                    <p class="font-semibold">No se puede emitir la solvencia: el atleta tiene pagos pendientes.</p>
                    <p class="text-sm mt-1">Debe saldarlos antes. Total adeudado: <strong>Bs. <?php echo number_format((float)($eligibility['pending_total'] ?? 0), 2); ?></strong></p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-700">
                            <th class="px-4 py-2">Período</th>
                            <th class="px-4 py-2">Vencimiento</th>
                            <th class="px-4 py-2 text-right">Monto</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($pending as $p): ?>
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900"><?php echo htmlspecialchars($p['month'] . ' ' . $p['year']); ?></td>
                            <td class="px-4 py-3 <?php echo $p['due_date'] < date('Y-m-d') ? 'text-red-600 font-semibold' : 'text-gray-900'; ?>">
                                <?php echo date('d/m/Y', strtotime($p['due_date'])); ?>
                                <?php if ($p['due_date'] < date('Y-m-d')): ?><span class="text-xs">(vencido)</span><?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">Bs. <?php echo number_format((float)$p['amount'], 2); ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?php echo BASE_URL; ?>payments/view?id=<?php echo (int)$p['id']; ?>" class="text-red-700 hover:text-red-800 font-medium">Ver pago</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-sm text-black">Cuando todos estos pagos estén en estado <strong>Pagado</strong>, vuelva a esta pantalla para emitir la solvencia.</p>

        <?php endif; ?>

    </div>
</div>
