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
                <h2 class="auth-card__title">Restablecer contraseña</h2>
                <p class="auth-card__subtitle">Ingresa tu correo electrónico y te enviaremos un enlace para crear una contraseña nueva.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="auth-alert auth-alert--error" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>password/send">
                <div class="auth-field">
                    <label for="email" class="auth-label">Correo electrónico</label>
                    <div class="auth-input-wrap">
                        <i class="fas fa-envelope auth-input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            class="auth-input"
                            placeholder="tucorreo@ejemplo.com"
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                        >
                    </div>
                </div>

                <button type="submit" class="auth-submit">
                    <i class="fas fa-paper-plane"></i>
                    Enviar enlace
                </button>
            </form>

            <div class="auth-links">
                <a href="<?php echo BASE_URL; ?>auth/login" class="auth-link">
                    <i class="fas fa-arrow-left"></i> Volver a iniciar sesión
                </a>
            </div>

            <p class="auth-footer">Escuela de Béisbol Menor Maporal</p>
        </div>
    </main>

</div>
