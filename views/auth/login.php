<?php
/**
 * Vista de Inicio de Sesión (Login).
 * 
 * Interfaz de autenticación para administradores, entrenadores, secretarios y representantes:
 * - Panel lateral con imagen institucional y lema de la escuela.
 * - Formulario con usuario o correo electrónico y contraseña (con botón para mostrar/ocultar).
 * - Mensajes de estado por redirección (cierre de sesión, expiración por inactividad, etc.).
 * - Enlace para recuperación de contraseña olvidada.
 * 
 * Variables recibidas desde AuthController::login():
 * @var string|null $error  Mensaje de error si las credenciales son incorrectas.
 */
?>
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
                <h2 class="auth-card__title">Iniciar sesión</h2>
                <p class="auth-card__subtitle">Ingresa tus credenciales para acceder al sistema.</p>
            </div>

            <?php
            // Avisos que el sistema envía por la URL al redirigir al login (cierre de sesión,
            // inactividad, enlace de recuperación vencido y contraseña actualizada).
            $notices = [
                'logged_out'       => ['info',    'fa-info-circle',        'Has cerrado sesión correctamente.'],
                'inactive'         => ['info',    'fa-clock',              'Tu sesión se cerró por inactividad. Inicia sesión de nuevo.'],
                'invalid_token'    => ['error',   'fa-exclamation-circle', 'El enlace de recuperación no es válido o ya venció. Solicita uno nuevo.'],
                'password_updated' => ['success', 'fa-check-circle',       'Tu contraseña se actualizó. Ya puedes iniciar sesión.'],
            ];
            $noticeKey = null;
            if (!empty($_GET['logged_out']))                          $noticeKey = 'logged_out';
            elseif (($_GET['reason'] ?? '') === 'inactive')           $noticeKey = 'inactive';
            elseif (($_GET['error'] ?? '') === 'invalid_token')       $noticeKey = 'invalid_token';
            elseif (($_GET['status'] ?? '') === 'password_updated')   $noticeKey = 'password_updated';
            ?>

            <?php if (!isset($error) && $noticeKey): [$noticeType, $noticeIcon, $noticeText] = $notices[$noticeKey]; ?>
                <div class="auth-alert auth-alert--<?php echo $noticeType; ?>" role="status">
                    <i class="fas <?php echo $noticeIcon; ?>"></i>
                    <span><?php echo htmlspecialchars($noticeText); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="auth-alert auth-alert--error" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="auth-field">
                    <label for="username" class="auth-label">Usuario</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-user auth-input-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autofocus
                            autocomplete="username"
                            class="auth-input"
                            placeholder="Ingresa tu usuario"
                        >
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">Contraseña</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="auth-input auth-input--with-toggle"
                            placeholder="Ingresa tu contraseña"
                        >
                        <button type="button" id="toggle-login-password" class="auth-toggle" aria-label="Mostrar u ocultar la contraseña" title="Mostrar u ocultar la contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar sesión
                </button>
            </form>

            <div class="auth-links">
                <a href="<?php echo BASE_URL; ?>password/request" class="auth-link">¿Olvidaste tu contraseña?</a>
            </div>

            <p class="auth-footer">Escuela de Béisbol Menor Maporal</p>
        </div>
    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('password');
    const toggle = document.getElementById('toggle-login-password');
    if (!input || !toggle) return;

    toggle.addEventListener('click', function() {
        const show = input.getAttribute('type') === 'password';
        input.setAttribute('type', show ? 'text' : 'password');
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
        input.focus();
    });
});
</script>
