<div class="auth-page">

    <!-- Panel de marca -->
    <aside class="auth-brand">
        <div class="auth-brand__logo">
            <img src="<?php echo BASE_URL; ?>img/logo-maporal.png" alt="Logo Maporal">
        </div>
        <p class="auth-brand__eyebrow">Sistema de Gestión</p>
        <h1 class="auth-brand__title">Escuela de Béisbol<br>Menor Maporal</h1>
        <div class="auth-brand__rule"></div>
        <p class="auth-brand__tagline">Atletas, pagos, entrenamientos y torneos en un solo lugar.</p>
    </aside>

    <!-- Formulario -->
    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-card__header">
                <h2 class="auth-card__title">Nueva contraseña</h2>
                <p class="auth-card__subtitle">Ingresa tu nueva contraseña segura.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="auth-alert auth-alert--error" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>password/update" id="reset-form">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="auth-field">
                    <label for="password" class="auth-label">Nueva contraseña</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="auth-input auth-input--with-actions"
                            placeholder="Ingresa tu nueva contraseña"
                        >
                        <div class="auth-actions">
                            <button type="button" id="toggle-password" class="auth-toggle auth-toggle--inline" aria-label="Mostrar u ocultar la contraseña" title="Mostrar u ocultar la contraseña">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" id="generate-password" class="auth-generate" title="Generar una contraseña segura">Generar</button>
                        </div>
                    </div>
                    <div id="password-strength" class="auth-hints">
                        <div id="length" class="text-gray-500"><i class="fas fa-times-circle"></i>Mínimo 8 caracteres</div>
                        <div id="uppercase" class="text-gray-500"><i class="fas fa-times-circle"></i>Una letra mayúscula</div>
                        <div id="lowercase" class="text-gray-500"><i class="fas fa-times-circle"></i>Una letra minúscula</div>
                        <div id="number" class="text-gray-500"><i class="fas fa-times-circle"></i>Un número</div>
                        <div id="symbol" class="text-gray-500"><i class="fas fa-times-circle"></i>Un símbolo (!@#$%^&*)</div>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password_confirm" class="auth-label">Confirmar nueva contraseña</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            id="password_confirm"
                            name="password_confirm"
                            required
                            autocomplete="new-password"
                            class="auth-input auth-input--with-toggle"
                            placeholder="Confirma tu nueva contraseña"
                        >
                        <button type="button" id="toggle-password-confirm" class="auth-toggle" aria-label="Mostrar u ocultar la confirmación" title="Mostrar u ocultar la confirmación">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="fas fa-save"></i>
                    Guardar contraseña
                </button>
            </form>

            <p class="auth-footer">Escuela de Béisbol Menor Maporal</p>
        </div>
    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const generatePasswordBtn = document.getElementById('generate-password');
    const togglePasswordBtn = document.getElementById('toggle-password');
    const togglePasswordConfirmBtn = document.getElementById('toggle-password-confirm');
    const form = document.getElementById('reset-form');
    
    const strengthCriteria = {
        length: document.getElementById('length'),
        uppercase: document.getElementById('uppercase'),
        lowercase: document.getElementById('lowercase'),
        number: document.getElementById('number'),
        symbol: document.getElementById('symbol')
    };

    function validatePassword() {
        const value = passwordInput.value;
        const criteria = {
            length: value.length >= 8,
            uppercase: /[A-Z]/.test(value),
            lowercase: /[a-z]/.test(value),
            number: /[0-9]/.test(value),
            symbol: /[!@#$%^&*]/.test(value)
        };

        let allValid = true;
        for (const key in criteria) {
            const el = strengthCriteria[key];
            const icon = el.querySelector('i');
            if (criteria[key]) {
                el.classList.remove('text-gray-500');
                el.classList.add('text-green-600');
                if (icon) {
                    icon.classList.remove('fa-times-circle');
                    icon.classList.add('fa-check-circle');
                }
            } else {
                el.classList.remove('text-green-600');
                el.classList.add('text-gray-500');
                if (icon) {
                    icon.classList.remove('fa-check-circle');
                    icon.classList.add('fa-times-circle');
                }
                allValid = false;
            }
        }
        return allValid;
    }

    function generatePassword() {
        const length = 12;
        const uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        const lowercase = "abcdefghijklmnopqrstuvwxyz";
        const numbers = "0123456789";
        const symbols = "!@#$%^&*";
        
        // Asegurar que la contraseña tenga al menos un carácter de cada tipo
        let password = "";
        password += uppercase.charAt(Math.floor(Math.random() * uppercase.length));
        password += lowercase.charAt(Math.floor(Math.random() * lowercase.length));
        password += numbers.charAt(Math.floor(Math.random() * numbers.length));
        password += symbols.charAt(Math.floor(Math.random() * symbols.length));
        
        // Completar el resto de la contraseña
        const allChars = uppercase + lowercase + numbers + symbols;
        for (let i = password.length; i < length; i++) {
            password += allChars.charAt(Math.floor(Math.random() * allChars.length));
        }
        
        // Mezclar la contraseña
        password = password.split('').sort(() => Math.random() - 0.5).join('');
        
        passwordInput.value = password;
        passwordConfirmInput.value = password;
        validatePassword();
    }

    passwordInput.addEventListener('input', validatePassword);
    generatePasswordBtn.addEventListener('click', generatePassword);

    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    togglePasswordConfirmBtn.addEventListener('click', function() {
        const type = passwordConfirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirmInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    form.addEventListener('submit', function(event) {
        const errors = [];

        if (!validatePassword()) {
            errors.push('La contraseña no cumple con todos los criterios de seguridad.');
        }

        if (passwordInput.value !== passwordConfirmInput.value) {
            errors.push('Las contraseñas no coinciden.');
        }

        if (passwordInput.value.length === 0) {
            errors.push('La contraseña no puede estar vacía.');
        }

        if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join('\n'));
        }
    });
    
    // Validar al cargar la página si hay contenido
    if (passwordInput.value) {
        validatePassword();
    }
});
</script>