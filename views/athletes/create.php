<?php
/**
 * Vista de Inscripción y Registro de Atleta.
 * 
 * Formulario integral para dar de alta un nuevo atleta:
 * - Datos personales: Nombres, apellidos, cédula (si posee), fecha de nacimiento y cálculo automático de edad/categoría.
 * - Subida de fotografía del atleta con previsualización interactiva.
 * - Datos deportivos: Posición de juego, brazo de lanzar, lado de bateo, dorsal deseado.
 * - Ficha médica: Condiciones médicas, alergias, tipo de sangre, contacto de emergencia.
 * - Datos del Representante: Nombre, cédula, teléfono, correo electrónico y dirección.
 * - Generación automática de usuario de acceso para el representante y envío de planilla de inscripción.
 * 
 * Variables recibidas desde AthletesController::create():
 * @var array|null  $Athlete  Valores previamente enviados en caso de error.
 * @var string|null $error    Mensaje de validación o fallo de inserción.
 */
?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user-plus text-red-700"></i>
                Nuevo Atleta
            </h2>
            <p class="text-gray-600 mt-1">Complete la información del atleta</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Información Personal -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input 
                            type="text" 
                            name="first_name"
                            id="first_name"
                            required
                            value="<?php echo isset($Athlete['first_name']) ? htmlspecialchars($Athlete['first_name']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                        <input 
                            type="text" 
                            name="last_name"
                            id="last_name"
                            required
                            value="<?php echo isset($Athlete['last_name']) ? htmlspecialchars($Athlete['last_name']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>

                    <div id="id_card_container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                        <input
                            type="text"
                            name="id_card"
                            id="id_card"
                            maxlength="8"
                            value="<?php echo isset($Athlete['id_card']) ? htmlspecialchars($Athlete['id_card']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento *</label>
                        <input
                            type="date"
                            name="date_of_birth"
                            id="date_of_birth"
                            required
                            value="<?php echo isset($Athlete['date_of_birth']) ? htmlspecialchars($Athlete['date_of_birth']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Edad *</label>
                        <input 
                            type="number" 
                            name="age"
                            id="age"
                            min="5"
                            max="18"
                            required
                            value="<?php echo isset($Athlete['age']) ? htmlspecialchars($Athlete['age']) : ''; ?>"
                           readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent bg-gray-100"
                       >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                        <select 
                            name="category"
                            id="category"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                        >
                            <option value="">Seleccionar categoría</option>
                            <option value="Infantil" data-min-age="8" data-max-age="10" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Infantil') ? 'selected' : ''; ?>>Infantil (8-10 años)</option>
                            <option value="Pre-juvenil" data-min-age="11" data-max-age="12" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Pre-juvenil') ? 'selected' : ''; ?>>Pre-juvenil (11-12 años)</option>
                            <option value="Juvenil" data-min-age="13" data-max-age="15" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Juvenil') ? 'selected' : ''; ?>>Juvenil (13-15 años)</option>
                            <option value="Junior" data-min-age="16" data-max-age="18" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Junior') ? 'selected' : ''; ?>>Junior (16-18 años)</option>
                        </select>
                        <p id="category-error" class="text-red-500 text-sm mt-1 hidden"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Jersey *</label>
                        <input
                            type="number"
                            name="jersey_number"
                            min="1"
                            max="99"
                            required
                            value="<?php echo isset($Athlete['jersey_number']) ? htmlspecialchars($Athlete['jersey_number']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                    </div>
					<div class="md:col-span-2">
						<label class="block text-sm font-medium text-gray-700 mb-1">Condiciones Médicas</label>
						<textarea 
							name="medical_conditions"
							rows="3"
							required
							placeholder="Especificar cualquier condición médica relevante o escribir 'Ninguna'"
							class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
						><?php echo isset($Athlete['medical_conditions']) ? htmlspecialchars($Athlete['medical_conditions']) : ''; ?></textarea>
					</div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fotografía</label>
                        <input
                            type="file"
                            name="photo"
                            id="photo"
                            accept="image/jpeg,image/jpg,image/png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-sm text-gray-500 mt-1">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB</p>
                       <div id="photo-preview" class="mt-2 hidden">
                           <div class="w-32 h-32 rounded-lg overflow-hidden border">
                               <img src="" alt="Vista previa" class="w-full h-full object-cover">
                           </div>
                       </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estatus *</label>
                        <select 
                            id="status-select"
                            name="status" 
                            data-current="active"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                        >
                           <option value="active" <?php echo (isset($Athlete['status']) && $Athlete['status'] === 'active') ? 'selected' : ''; ?>>Activo</option>
                           <option value="inactive" <?php echo (isset($Athlete['status']) && $Athlete['status'] === 'inactive') ? 'selected' : ''; ?>>Inactivo</option>
                           <option value="suspended" <?php echo (isset($Athlete['status']) && $Athlete['status'] === 'suspended') ? 'selected' : ''; ?>>Suspendido</option>
                       </select>
                        <div id="status-reason-box" class="status-reason-field hidden mt-3">
                            <label for="status-reason" class="block text-sm font-medium text-gray-700 mb-1">Motivo del cambio *</label>
                            <textarea id="status-reason" name="status_reason" maxlength="500" placeholder="Ej: Se mudó de ciudad; falta disciplinaria; a solicitud del representante..."><?php echo htmlspecialchars($Athlete['status_reason'] ?? ''); ?></textarea>
                            <p class="text-xs text-gray-500 mt-1"><span id="status-reason-count">0 / 500</span> · Queda registrado con tu nombre y la fecha. Lo ven el administrador y el secretario.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Padre/Madre -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Padre/Madre</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Padre/Madre *</label>
                        <input 
                            type="text" 
                            name="parent_name"
                            id="parent_name"
                            required
                            value="<?php echo isset($Athlete['parent_name']) ? htmlspecialchars($Athlete['parent_name']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula del Padre/Madre *</label>
                        <input
                            type="text"
                            name="parent_id_card"
                            id="parent_id_card"
                            required
                            maxlength="8"
                            value="<?php echo isset($Athlete['parent_id_card']) ? htmlspecialchars($Athlete['parent_id_card']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                        <div class="flex gap-2">
                            <select
                                name="parent_phone_code"
                                id="parent_phone_code"
                                required
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                            >
                                <option value="0412" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0412') === 0) ? 'selected' : ''; ?>>0412</option>
                                <option value="0414" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0414') === 0) ? 'selected' : ''; ?>>0414</option>
                                <option value="0416" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0416') === 0) ? 'selected' : ''; ?>>0416</option>
                                <option value="0424" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0424') === 0) ? 'selected' : ''; ?>>0424</option>
                                <option value="0426" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0426') === 0) ? 'selected' : ''; ?>>0426</option>
                                <option value="0422" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0422') === 0) ? 'selected' : ''; ?>>0422</option>
                            </select>
                            <input
                                type="text"
                                name="parent_phone_number"
                                id="parent_phone_number"
                                required
                                placeholder="1234567"
                                maxlength="7"
                                pattern="[0-9]{7}"
                                value="<?php echo isset($Athlete['parent_phone']) ? htmlspecialchars(substr($Athlete['parent_phone'], -7)) : ''; ?>"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                            >
                            <input type="hidden" name="parent_phone" id="parent_phone" value="">
                        </div>
                        <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input 
                            type="email" 
                            name="parent_email"
                            id="parent_email"
                            required
                            value="<?php echo isset($Athlete['parent_email']) ? htmlspecialchars($Athlete['parent_email']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                        <textarea
                            name="address"
                            rows="2"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                        ><?php echo isset($Athlete['address']) ? htmlspecialchars($Athlete['address']) : ''; ?></textarea>
                        <p class="text-xs text-gray-500 mt-1">Dirección de la casa del atleta o del representante.</p>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Contacto de Emergencia <span class="text-red-600">*</span></h4>
                        <p class="text-xs text-gray-500 mb-3">Persona a llamar si el atleta sufre un accidente o urgencia <strong>durante entrenamientos y juegos</strong>. Se guarda en la ficha del atleta y también en la cuenta del representante. Puede ser la misma persona o alguien distinto (por ejemplo un familiar cercano que viva cerca).</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                                <input
                                    type="text"
                                    name="emergency_contact"
                                    id="emergency_contact"
                                    required
                                    placeholder="Nombre y apellido"
                                    value="<?php echo isset($Athlete['emergency_contact']) ? htmlspecialchars($Athlete['emergency_contact']) : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                >
                                <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Parentesco con el atleta *</label>
                                <input
                                    type="text"
                                    name="account_emergency_relationship"
                                    id="account_emergency_relationship"
                                    required
                                    maxlength="100"
                                    placeholder="Ej: madre, esposo(a), abuelo(a), tío(a)"
                                    value="<?php echo htmlspecialchars($Athlete['account_emergency_relationship'] ?? ''); ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                >
                                <p class="text-xs text-gray-500 mt-1">Su relación con el atleta (también queda en la cuenta del representante).</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                                <div class="flex gap-2">
                                    <select
                                        name="emergency_phone_code"
                                        id="emergency_phone_code"
                                        required
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                    >
                                        <option value="0412" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0412') === 0) ? 'selected' : ''; ?>>0412</option>
                                        <option value="0414" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0414') === 0) ? 'selected' : ''; ?>>0414</option>
                                        <option value="0416" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0416') === 0) ? 'selected' : ''; ?>>0416</option>
                                        <option value="0424" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0424') === 0) ? 'selected' : ''; ?>>0424</option>
                                        <option value="0426" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0426') === 0) ? 'selected' : ''; ?>>0426</option>
                                        <option value="0422" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0422') === 0) ? 'selected' : ''; ?>>0422</option>
                                    </select>
                                    <input
                                        type="text"
                                        name="emergency_phone_number"
                                        id="emergency_phone_number"
                                        required
                                        placeholder="1234567"
                                        maxlength="7"
                                        pattern="[0-9]{7}"
                                        value="<?php echo isset($Athlete['emergency_phone']) ? htmlspecialchars(substr($Athlete['emergency_phone'], -7)) : ''; ?>"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                    >
                                    <input type="hidden" name="emergency_phone" id="emergency_phone" value="">
                                </div>
                                <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuenta de acceso del representante: obligatoria, se crea aqui con los datos ya escritos -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-1">Cuenta de acceso del representante <span class="text-red-600">*</span></h3>
                <p class="text-sm text-gray-600 mb-4">La cuenta se crea <strong>siempre</strong> en este mismo paso, usando el nombre, c&eacute;dula, tel&eacute;fono, correo, direcci&oacute;n y contacto de emergencia ya escritos arriba. Si el representante ya tiene cuenta (otro atleta inscrito antes), se reutiliza y no se pide nada de esto.</p>
                <div id="account-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento del Representante *</label>
                        <input
                            type="date"
                            name="account_birth_date"
                            id="account_birth_date"
                            required
                            value="<?php echo htmlspecialchars($Athlete['account_birth_date'] ?? ''); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                        >
                        <p class="text-xs text-gray-500 mt-1">Debe ser mayor de 18 a&ntilde;os.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario *</label>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                name="account_username"
                                id="account_username"
                                required
                                maxlength="30"
                                pattern="[A-Za-z0-9._-]{3,30}"
                                value="<?php echo htmlspecialchars($Athlete['account_username'] ?? ''); ?>"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                            >
                            <button type="button" id="suggest_username" class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Sugerir</button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Letras, n&uacute;meros, punto, guion y guion bajo. 3 a 30 caracteres. La sugerencia arma: apellido + inicial del nombre + &uacute;ltimos 3 d&iacute;gitos de la c&eacute;dula.</p>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contrase&ntilde;a *</label>
                        <div class="relative">
                            <input type="password" name="account_password" id="account_password" required minlength="8"
                                   value="<?php echo htmlspecialchars($Athlete['account_password'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent pr-32">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" id="toggle-account-password" class="px-3 text-gray-500">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" id="generate-account-password" class="px-3 text-sm text-gray-700 hover:text-gray-900 border-l border-gray-300">Generar</button>
                            </div>
                        </div>
                        <div id="account-password-strength" class="mt-2 space-y-1 text-sm">
                            <div id="acct-pw-length" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>M&iacute;nimo 8 caracteres</div>
                            <div id="acct-pw-uppercase" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Una letra may&uacute;scula</div>
                            <div id="acct-pw-lowercase" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Una letra min&uacute;scula</div>
                            <div id="acct-pw-number" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Un n&uacute;mero</div>
                            <div id="acct-pw-symbol" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Un s&iacute;mbolo (!@#$%^&amp;*)</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contrase&ntilde;a *</label>
                        <div class="relative">
                            <input type="password" name="account_password_confirm" id="account_password_confirm" required minlength="8"
                                   value="<?php echo htmlspecialchars($Athlete['account_password_confirm'] ?? ''); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent pr-32">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" id="toggle-account-password-confirm" class="px-3 text-gray-500">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <p id="account-password-mismatch" class="text-red-500 text-sm mt-1 hidden">Las contrase&ntilde;as no coinciden.</p>
                    </div>
                </div>
            </div>

            <!-- Plan de cobros: montos definidos por el administrador en Conceptos -->
            <?php
            $planConcepts = $planConcepts ?? (new PaymentWorkflow())->planConcepts();
            $planEnrollment = $planConcepts['enrollment'];
            $planMonthly = $planConcepts['monthly'];
            $planChecked = !isset($Athlete) || !empty($Athlete['generate_plan']);
            $planMonths = isset($Athlete['plan_months']) ? (int)$Athlete['plan_months'] : 5;
            ?>
            <div class="pb-6 border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-1">Plan de cobros</h3>
                <p class="text-sm text-gray-600 mb-4">Al crear el atleta se generan sus cobros como <strong>Pendientes</strong> para que el representante los reporte desde "Mis pagos". Los montos son los del módulo de Conceptos.</p>
                <?php if (!$planEnrollment && !$planMonthly): ?>
                <p class="text-sm text-red-700">No hay conceptos activos de Inscripción ni de Mensualidad: los cobros deberán generarse a mano.</p>
                <?php else: ?>
                <label class="flex items-center gap-2 mb-3">
                    <input type="checkbox" name="generate_plan" value="1" <?php echo $planChecked ? 'checked' : ''; ?>>
                    <span class="text-sm text-gray-800">Generar cobros automáticamente</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="text-sm text-gray-800">
                        <strong>Inscripción:</strong>
                        <?php echo $planEnrollment ? 'Bs. ' . number_format((float)$planEnrollment['default_amount'], 2) . ' (vence a los 7 días)' : 'sin concepto activo, no se generará'; ?>
                    </div>
                    <div>
                        <label for="plan_months" class="block text-sm font-medium text-gray-700 mb-1">
                            Mensualidades <?php echo $planMonthly ? '(Bs. ' . number_format((float)$planMonthly['default_amount'], 2) . ' c/u)' : '(sin concepto activo)'; ?>
                        </label>
                        <input type="number" id="plan_months" name="plan_months" min="0" max="<?php echo PaymentWorkflow::PLAN_MAX_MONTHS; ?>" value="<?php echo $planMonths; ?>" <?php echo $planMonthly ? '' : 'disabled'; ?> class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Desde el mes actual; cada una vence el mismo día del mes siguiente.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button 
                    type="submit" 
                    class="flex-1 btn-primary text-white font-medium py-3 px-4 rounded-lg transition-colors"
                >
                    <i class="fas fa-save mr-2"></i>
                    Crear Atleta
                </button>
                <a 
                    href="<?php echo BASE_URL; ?>athletes" 
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors text-center"
                >
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // --- Age & Category Validation ---
    const birthDateInput = document.getElementById('date_of_birth');
    const ageInput = document.getElementById('age');
    const categorySelect = document.getElementById('category');
    const categoryError = document.getElementById('category-error');
    const idCardContainer = document.getElementById('id_card_container');

    function calculateAge(birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        return age;
    }

    function validateCategory() {
        const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
        const category = selectedCategory.value;
        const age = parseInt(ageInput.value, 10);
        if (!category || !age) {
            categoryError.classList.add('hidden');
            return true;
        }
        const minAge = parseInt(selectedCategory.dataset.minAge, 10);
        const maxAge = parseInt(selectedCategory.dataset.maxAge, 10);
        if (age < minAge || age > maxAge) {
            categoryError.textContent = `La edad (${age}) no corresponde a la categoría "${category}" (${minAge}-${maxAge} años).`;
            categoryError.classList.remove('hidden');
            return false;
        }
        categoryError.classList.add('hidden');
        return true;
    }

    birthDateInput.addEventListener('input', function() {
        if (this.value) {
            const age = calculateAge(this.value);
            ageInput.value = age;
            validateCategory();
        }
    });
    categorySelect.addEventListener('input', validateCategory);

    // --- Phone Number Combination ---
    const parentPhoneCode = document.getElementById('parent_phone_code');
    const parentPhoneNumber = document.getElementById('parent_phone_number');
    const parentPhoneHidden = document.getElementById('parent_phone');
    
    const emergencyPhoneCode = document.getElementById('emergency_phone_code');
    const emergencyPhoneNumber = document.getElementById('emergency_phone_number');
    const emergencyPhoneHidden = document.getElementById('emergency_phone');

    function updateParentPhone() {
        if (parentPhoneCode && parentPhoneNumber && parentPhoneHidden) {
            parentPhoneHidden.value = parentPhoneCode.value + '-' + parentPhoneNumber.value;
        }
    }

    function updateEmergencyPhone() {
        if (emergencyPhoneCode && emergencyPhoneNumber && emergencyPhoneHidden) {
            emergencyPhoneHidden.value = emergencyPhoneCode.value + '-' + emergencyPhoneNumber.value;
        }
    }

    if (parentPhoneCode && parentPhoneNumber) {
        parentPhoneCode.addEventListener('change', updateParentPhone);
        parentPhoneNumber.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            updateParentPhone();
        });
        updateParentPhone();
    }

    if (emergencyPhoneCode && emergencyPhoneNumber) {
        emergencyPhoneCode.addEventListener('change', updateEmergencyPhone);
        emergencyPhoneNumber.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            updateEmergencyPhone();
        });
        updateEmergencyPhone();
    }

    // --- Field Format Validation ---
    const validations = {
        'first_name': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El nombre solo debe contener letras y espacios.' },
        'last_name': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El apellido solo debe contener letras y espacios.' },
        'parent_name': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El nombre solo debe contener letras y espacios.' },
        'emergency_contact': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El nombre solo debe contener letras y espacios.' },
        'id_card': { regex: /^[0-9]{8}$/, message: 'La cédula debe contener 8 dígitos.', optional: true },
        'parent_email': { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Por favor, introduce un email válido.' },
        'parent_phone_number': { regex: /^[0-9]{7}$/, message: 'Debe contener exactamente 7 dígitos.' },
        'emergency_phone_number': { regex: /^[0-9]{7}$/, message: 'Debe contener exactamente 7 dígitos.' },
        'parent_id_card': { regex: /^[0-9]{7,8}$/, message: 'La cédula debe contener entre 7 y 8 dígitos.' }
    };

    function validateField(input) {
        const rule = validations[input.id];
        if (!rule) return true;
        
        const errorElement = input.nextElementSibling;
        
        // Si el campo es opcional y está vacío, es válido
        if (rule.optional && input.value.trim() === '') {
            errorElement.classList.add('hidden');
            return true;
        }
        
        // Si el campo tiene contenido, validar formato
        if (input.value.trim() !== '' && !rule.regex.test(input.value)) {
            errorElement.textContent = rule.message;
            errorElement.classList.remove('hidden');
            return false;
        }
        
        errorElement.classList.add('hidden');
        return true;
    }

    Object.keys(validations).forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', () => validateField(input));
        }
    });

    // --- Form Submission ---
    form.addEventListener('submit', function(event) {
        let isFormValid = true;
        
        // Check category validation
        if (!validateCategory()) {
            isFormValid = false;
        }

        // Check all field validations (solo si tienen contenido o no son opcionales)
        Object.keys(validations).forEach(id => {
            const input = document.getElementById(id);
            const rule = validations[id];
            if (input) {
                // Solo validar si el campo tiene contenido o no es opcional
                if (!rule.optional || input.value.trim() !== '') {
                    if (!validateField(input)) {
                        isFormValid = false;
                    }
                }
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            alert('Por favor, corrija los errores resaltados antes de enviar el formulario.');
        }

    // --- Photo Preview ---
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (2MB máximo)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen no debe superar los 2MB');
                    this.value = '';
                    photoPreview.classList.add('hidden');
                    return;
                }
                
                // Validar tipo
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    alert('Solo se permiten imágenes JPG, JPEG o PNG');
                    this.value = '';
                    photoPreview.classList.add('hidden');
                    return;
                }
                
                // Mostrar vista previa
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.querySelector('img').src = e.target.result;
                    photoPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.classList.add('hidden');
            }
        });
    }
    });
});
</script>

<script>
// Campo de motivo del estatus: visible solo al dejar al atleta Inactivo o Suspendido (y no si ya estaba asi).
// La validacion real es la del servidor; esto solo evita enviar el formulario sin motivo.
(function () {
    const select = document.getElementById('status-select');
    const box = document.getElementById('status-reason-box');
    const area = document.getElementById('status-reason');
    const count = document.getElementById('status-reason-count');
    if (!select || !box || !area) return;
    const current = select.getAttribute('data-current') || '';

    function sync() {
        const v = select.value;
        const needs = v !== 'active' && v !== current;
        box.classList.toggle('hidden', !needs);
        area.required = needs;
        if (!needs) { area.value = ''; }
        if (count) { count.textContent = area.value.length + ' / 500'; }
    }
    select.addEventListener('change', sync);
    area.addEventListener('input', function () { if (count) count.textContent = area.value.length + ' / 500'; });
    sync();
})();
</script>
<script>
// Cuenta del representante (obligatoria): el boton "Sugerir" arma usuario
// apellido + inicial del nombre + ultimos 3 digitos de la cedula.
// Si la cedula del representante ya tiene cuenta (hermano inscrito antes), los campos de
// usuario/contrasena se ocultan y se desactivan: la cuenta se REUTILIZA y no se crea otra.
(function () {
    const BASE_URL_JS = '<?php echo BASE_URL; ?>';
    const idCardInput = document.getElementById('parent_id_card');
    const fields = document.getElementById('account-fields');
    const ids = ['account_birth_date', 'account_username', 'account_password', 'account_password_confirm', 'account_emergency_relationship'];
    const newOnly = ['account_birth_date', 'account_username', 'account_password', 'account_password_confirm', 'account_emergency_relationship'];
    const hintId = 'account-existing-hint';
    let lastChecked = null;

    function setExisting(isExisting) {
        if (!fields) return;
        fields.classList.toggle('hidden', isExisting);
        newOnly.forEach(function (id) {
            const el = document.getElementById(id);
            if (el) {
                el.disabled = isExisting;
                el.required = !isExisting;
            }
        });
        let hint = document.getElementById(hintId);
        if (isExisting && !hint && fields.parentNode) {
            hint = document.createElement('p');
            hint.id = hintId;
            hint.className = 'text-sm text-green-700 mt-2';
            hint.textContent = 'Este representante ya tiene cuenta de acceso: se reutilizará y no se creará otra. Solo complete los datos del atleta.';
            fields.parentNode.insertBefore(hint, fields.nextSibling);
        } else if (!isExisting && hint) {
            hint.remove();
        }
    }

    if (idCardInput && fields) {
        idCardInput.addEventListener('change', function () {
            const digits = (idCardInput.value || '').replace(/\D/g, '');
            if (digits.length < 7) { lastChecked = null; setExisting(false); return; }
            if (lastChecked === digits) return;
            lastChecked = digits;
            fetch(BASE_URL_JS + 'athletes/checkRepresentativeAccount?id_card=' + encodeURIComponent(digits))
                .then(function (r) { return r.json(); })
                .then(function (data) { setExisting(!!data.exists); })
                .catch(function () { setExisting(false); });
        });
    }

    const btn = document.getElementById('suggest_username');
    if (btn) {
        // Sugerencia: primer apellido + inicial del nombre + ultimos 3 digitos de la cedula.
        // Ej. "Edita Rattia de Escobar" (31654321) -> rattiae321.
        btn.addEventListener('click', function () {
            const name = (document.getElementById('parent_name') || {}).value || '';
            const idCard = (document.getElementById('parent_id_card') || {}).value || '';
            const parts = name.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z\s]/g, '').trim().split(/\s+/).filter(Boolean);
            const first = parts.length ? parts[0] : '';
            const surname = parts.length > 1 ? parts[1] : first;
            let user = surname + (first ? first.charAt(0) : '');
            const digits = idCard.replace(/\D/g, '');
            if (digits.length >= 3) { user += digits.slice(-3); }
            const input = document.getElementById('account_username');
            if (user && input) { input.value = user.slice(0, 30); }
        });
    }

    // --- Contraseña de la cuenta: checklist en vivo, Generar y ojo (igual que en Administradores) ---
    const acctPwInput = document.getElementById('account_password');
    const acctPwConfirm = document.getElementById('account_password_confirm');
    const acctPwMismatch = document.getElementById('account-password-mismatch');
    const acctPwCriteria = {
        length: document.getElementById('acct-pw-length'),
        uppercase: document.getElementById('acct-pw-uppercase'),
        lowercase: document.getElementById('acct-pw-lowercase'),
        number: document.getElementById('acct-pw-number'),
        symbol: document.getElementById('acct-pw-symbol')
    };

    function validateAccountPassword() {
        if (!acctPwInput) return false;
        const value = acctPwInput.value;
        const criteria = {
            length: value.length >= 8,
            uppercase: /[A-Z]/.test(value),
            lowercase: /[a-z]/.test(value),
            number: /[0-9]/.test(value),
            symbol: /[!@#$%^&*]/.test(value)
        };
        let strength = 0;
        for (const key in criteria) {
            const el = acctPwCriteria[key];
            if (!el) continue;
            if (criteria[key]) {
                el.classList.replace('text-gray-500', 'text-green-600');
                el.innerHTML = el.innerHTML.replace('fa-times-circle', 'fa-check-circle');
                strength++;
            } else {
                el.classList.replace('text-green-600', 'text-gray-500');
                el.innerHTML = el.innerHTML.replace('fa-check-circle', 'fa-times-circle');
            }
        }
        if (acctPwMismatch) {
            acctPwMismatch.classList.toggle('hidden', !acctPwConfirm.value || acctPwConfirm.value === value);
        }
        return strength === 5;
    }
    if (acctPwInput) { acctPwInput.addEventListener('input', validateAccountPassword); }
    if (acctPwConfirm) { acctPwConfirm.addEventListener('input', validateAccountPassword); }

    const generateAcctPwBtn = document.getElementById('generate-account-password');
    if (generateAcctPwBtn) {
        generateAcctPwBtn.addEventListener('click', function () {
            const length = 12;
            const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let password = "";
            for (let i = 0, n = charset.length; i < length; ++i) {
                password += charset.charAt(Math.floor(Math.random() * n));
            }
            acctPwInput.value = password;
            validateAccountPassword();
        });
    }

    [['toggle-account-password', acctPwInput], ['toggle-account-password-confirm', acctPwConfirm]].forEach(function ([toggleId, input]) {
        const btnToggle = document.getElementById(toggleId);
        if (btnToggle && input) {
            btnToggle.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    });
})();
</script>
