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

    <!-- Confirmación -->
    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-status-icon">
                <i class="fas fa-check"></i>
            </div>

            <div class="auth-card__header">
                <h2 class="auth-card__title">Enlace enviado</h2>
                <p class="auth-card__subtitle">Hemos enviado un enlace para restablecer tu contraseña a:</p>
            </div>

            <p class="auth-email"><?php echo isset($email) ? htmlspecialchars($email) : 'tu correo electrónico'; ?></p>

            <div class="auth-alert auth-alert--info" role="status">
                <i class="fas fa-info-circle"></i>
                <span>Revisa tu bandeja de entrada y la carpeta de spam. El enlace expirará en 1 hora.</span>
            </div>

            <div class="auth-links">
                <a href="<?php echo BASE_URL; ?>auth/login" class="auth-link">
                    <i class="fas fa-arrow-left"></i> Volver a iniciar sesión
                </a>
            </div>

            <p class="auth-footer">Escuela de Béisbol Menor Maporal</p>
        </div>
    </main>

</div>
