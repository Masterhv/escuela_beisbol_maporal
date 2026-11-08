<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user-edit text-black"></i>
                Editar Administrador
            </h2>
            <p class="text-black mt-1">Actualice la información del usuario administrador.</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                <p class="text-red-600 text-sm"><?php echo $error; ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>admins/edit?id=<?php echo $admin['id']; ?>" class="space-y-6">
            <!-- Información Personal -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo *</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($admin['full_name']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    <div>
                        <label for="id_card" class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                        <input type="text" id="id_card" name="id_card" maxlength="8" value="<?php echo htmlspecialchars($admin['id_card'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                        <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento</label>
                        <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($admin['birth_date'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                     <div>
                        <label for="age" class="block text-sm font-medium text-gray-700 mb-1">Edad</label>
                        <input type="text" id="age" name="age" value="<?php echo htmlspecialchars($admin['age'] ?? 'N/A'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100" readonly>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="tel" id="phone" name="phone" placeholder="+58 414-1234567" maxlength="13" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                        <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <textarea id="address" name="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent"><?php echo htmlspecialchars($admin['address'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Contacto de Emergencia</label>
                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?php echo htmlspecialchars($admin['emergency_contact_name'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    <div>
                        <label for="emergency_contact_relationship" class="block text-sm font-medium text-gray-700 mb-1">Parentesco</label>
                        <input type="text" id="emergency_contact_relationship" name="emergency_contact_relationship" value="<?php echo htmlspecialchars($admin['emergency_contact_relationship'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Teléfono de Emergencia</label>
                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" placeholder="+58 426-7654321" maxlength="13" value="<?php echo htmlspecialchars($admin['emergency_contact_phone'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                        <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                </div>
            </div>

            <!-- Información de la Cuenta -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información de la Cuenta</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Nombre de Usuario *</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($admin['username']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($admin['email']); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent pr-32">
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                <button type="button" id="toggle-password" class="px-3 text-gray-500">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" id="generate-password" class="px-3 text-sm text-black hover:text-gray-800 border-l border-gray-300">Generar</button>
                            </div>
                        </div>
                        <div id="password-strength" class="mt-2 space-y-1 text-sm">
                            <div id="length" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Mínimo 8 caracteres</div>
                            <div id="uppercase" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Una letra mayúscula</div>
                            <div id="lowercase" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Una letra minúscula</div>
                            <div id="number" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Un número</div>
                            <div id="symbol" class="text-gray-500"><i class="fas fa-times-circle mr-2"></i>Un símbolo (!@#$%^&*)</div>
                        </div>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                        <select id="role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent">
                            <option value="secretario" <?php echo ($admin['role'] === 'secretario') ? 'selected' : ''; ?>>Secretario</option>
                            <option value="administrador" <?php echo ($admin['role'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="entrenador" <?php echo ($admin['role'] === 'entrenador') ? 'selected' : ''; ?>>Entrenador</option>
                            <option value="representante" <?php echo ($admin['role'] === 'representante') ? 'selected' : ''; ?>>Representante</option>
                        </select>
                    </div>
                     <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Estatus</label>
                        <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-black focus:border-black sm:text-sm rounded-md">
                            <option value="active" <?php echo ($admin['status'] === 'active') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactive" <?php echo ($admin['status'] === 'inactive') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Clave de Operaciones Especiales (solo Administrador) -->
            <div id="operation-key-section" class="border-b border-gray-200 pb-6 <?php echo ($admin['role'] === 'administrador') ? '' : 'hidden'; ?>">
                <h3 class="text-lg font-medium text-gray-900 mb-1 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-red-700"></i>
                    Clave de Operaciones Especiales
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    <?php if (!empty($admin['operation_key'])): ?>
                        Ya tiene una clave configurada. Ingrese una nueva para cambiarla (dejar en blanco para mantener la actual).
                    <?php else: ?>
                        Esta clave de 4 dígitos se requerirá para eliminar atletas o pagos si esa función se reactiva. Actualmente la eliminación de atletas y pagos está deshabilitada.
                    <?php endif; ?>
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="operation_key" class="block text-sm font-medium text-gray-700 mb-1">
                            Nueva Clave (4 dígitos)<?php echo empty($admin['operation_key']) ? ' *' : ''; ?>
                        </label>
                        <div class="relative">
                            <input type="password" id="operation_key" name="operation_key"
                                   maxlength="4" pattern="\d{4}" inputmode="numeric"
                                   placeholder="••••"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent tracking-widest text-center text-xl">
                            <button type="button" id="toggle-op-key" class="absolute inset-y-0 right-3 text-gray-500">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p id="op-key-error" class="text-red-500 text-sm mt-1 hidden"></p>
                    </div>
                    <div>
                        <label for="operation_key_confirm" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Nueva Clave<?php echo empty($admin['operation_key']) ? ' *' : ''; ?>
                        </label>
                        <div class="relative">
                            <input type="password" id="operation_key_confirm" name="operation_key_confirm"
                                   maxlength="4" pattern="\d{4}" inputmode="numeric"
                                   placeholder="••••"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent tracking-widest text-center text-xl">
                            <button type="button" id="toggle-op-key-confirm" class="absolute inset-y-0 right-3 text-gray-500">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p id="op-key-confirm-error" class="text-red-500 text-sm mt-1 hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 btn-primary text-white font-medium py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Actualizar Administrador
                </button>
                <a href="<?php echo BASE_URL; ?>admins" class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors text-center">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const birthDateInput = document.getElementById('birth_date');
    const ageInput = document.getElementById('age');
    const passwordInput = document.getElementById('password');
    const generatePasswordBtn = document.getElementById('generate-password');
    const strengthCriteria = {
        length: document.getElementById('length'),
        uppercase: document.getElementById('uppercase'),
        lowercase: document.getElementById('lowercase'),
        number: document.getElementById('number'),
        symbol: document.getElementById('symbol')
    };

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

    function validatePassword(checkIfEmpty = false) {
        const value = passwordInput.value;
        if (checkIfEmpty && value.trim() === '') {
            Object.values(strengthCriteria).forEach(el => {
                el.classList.replace('text-green-600', 'text-gray-500');
                el.innerHTML = el.innerHTML.replace('fa-check-circle', 'fa-times-circle');
            });
            return true;
        }
        
        let strength = 0;
        const criteria = {
            length: value.length >= 8,
            uppercase: /[A-Z]/.test(value),
            lowercase: /[a-z]/.test(value),
            number: /[0-9]/.test(value),
            symbol: /[!@#$%^&*]/.test(value)
        };

        for (const key in criteria) {
            const el = strengthCriteria[key];
            if (criteria[key]) {
                el.classList.replace('text-gray-500', 'text-green-600');
                el.innerHTML = el.innerHTML.replace('fa-times-circle', 'fa-check-circle');
                strength++;
            } else {
                el.classList.replace('text-green-600', 'text-gray-500');
                el.innerHTML = el.innerHTML.replace('fa-check-circle', 'fa-times-circle');
            }
        }
        return strength === 5;
    }

    function generatePassword() {
        const length = 12;
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let password = "";
        for (let i = 0, n = charset.length; i < length; ++i) {
            password += charset.charAt(Math.floor(Math.random() * n));
        }
        passwordInput.value = password;
        validatePassword();
    }

    birthDateInput.addEventListener('input', function() {
        if (this.value) {
            const age = calculateAge(this.value);
            ageInput.value = age >= 0 ? age : '';
        } else {
            ageInput.value = '';
        }
    });
    
    if (birthDateInput.value) {
        const age = calculateAge(birthDateInput.value);
        ageInput.value = age >= 0 ? age : '';
    }

    passwordInput.addEventListener('input', () => validatePassword(true));
    generatePasswordBtn.addEventListener('click', generatePassword);

    const togglePasswordBtn = document.getElementById('toggle-password');
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // --- Field Format Validation ---
    const validations = {
        'id_card': { regex: /^[0-9]{7,8}$/, message: 'La cédula debe contener entre 7 y 8 dígitos numéricos.' },
        'phone': { regex: /^\+?[0-9\s\-()]{7,}$/, message: 'Introduce un número de teléfono válido.' },
        'emergency_contact_phone': { regex: /^\+?[0-9\s\-()]{7,}$/, message: 'Introduce un número de teléfono válido.' }
    };

    function validateField(input) {
        const rule = validations[input.id];
        if (!rule) return true;

        const errorElement = input.nextElementSibling;
        // Only validate if there is a value or if it's not optional
        if (input.value === '' && rule.optional) {
             errorElement.classList.add('hidden');
             return true;
        }

        if (rule.regex.test(input.value)) {
            errorElement.classList.add('hidden');
            return true;
        } else {
            errorElement.textContent = rule.message;
            errorElement.classList.remove('hidden');
            return false;
        }
    }

    Object.keys(validations).forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', () => validateField(input));
        }
    });
 
     const form = document.querySelector('form');
     form.addEventListener('submit', function(event) {
        if (passwordInput.value.trim() !== '' && !validatePassword()) {
            event.preventDefault();
            alert('La nueva contraseña no cumple con todos los criterios de seguridad.');
            return;
        }

        if (birthDateInput.value) {
            const age = calculateAge(birthDateInput.value);
            if (age < 18) {
                event.preventDefault();
                alert('El administrador debe ser mayor de 18 años.');
            }
        } else {
            event.preventDefault();
            alert('La fecha de nacimiento es obligatoria.');
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const birthDateInput = document.getElementById('birth_date');
    const ageInput = document.getElementById('age');

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

    birthDateInput.addEventListener('input', function() {
        if (this.value) {
            const age = calculateAge(this.value);
            ageInput.value = age >= 0 ? age : '';
        } else {
            ageInput.value = '';
        }
    });

    // Trigger on load in case there's an existing date
    if (birthDateInput.value) {
        const age = calculateAge(birthDateInput.value);
        ageInput.value = age >= 0 ? age : '';
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Clave de Operaciones Especiales ---
    const roleSelect = document.getElementById('role');
    const opKeySection = document.getElementById('operation-key-section');
    const opKeyInput = document.getElementById('operation_key');
    const opKeyConfirmInput = document.getElementById('operation_key_confirm');
    const hasExistingKey = <?php echo !empty($admin['operation_key']) ? 'true' : 'false'; ?>;

    function toggleOpKeySection() {
        if (roleSelect.value === 'administrador') {
            opKeySection.classList.remove('hidden');
        } else {
            opKeySection.classList.add('hidden');
            opKeyInput.value = '';
            opKeyConfirmInput.value = '';
        }
    }

    roleSelect.addEventListener('change', toggleOpKeySection);

    // Only allow digits
    [opKeyInput, opKeyConfirmInput].forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 4);
        });
    });

    // Inline match validation
    opKeyConfirmInput.addEventListener('input', function() {
        const err = document.getElementById('op-key-confirm-error');
        if (this.value && this.value !== opKeyInput.value) {
            err.textContent = 'Las claves no coinciden.';
            err.classList.remove('hidden');
        } else {
            err.classList.add('hidden');
        }
    });

    // Toggle visibility
    document.getElementById('toggle-op-key').addEventListener('click', function() {
        const type = opKeyInput.getAttribute('type') === 'password' ? 'text' : 'password';
        opKeyInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    document.getElementById('toggle-op-key-confirm').addEventListener('click', function() {
        const type = opKeyConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        opKeyConfirmInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    // Form submit validation for operation key
    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        if (roleSelect.value !== 'administrador') return;
        const opKey = opKeyInput.value;
        const opKeyConfirm = opKeyConfirmInput.value;

        // If no existing key, it's mandatory
        if (!hasExistingKey && !opKey) {
            event.preventDefault();
            alert('La clave de operaciones especiales es obligatoria para el rol Administrador.');
            return;
        }

        // If a new key was entered, validate it
        if (opKey) {
            if (!/^\d{4}$/.test(opKey)) {
                event.preventDefault();
                alert('La clave de operaciones debe ser exactamente 4 dígitos numéricos.');
                return;
            }
            if (opKey !== opKeyConfirm) {
                event.preventDefault();
                alert('Las claves de operaciones especiales no coinciden.');
                return;
            }
        }
    });
});
</script>