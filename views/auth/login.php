<?php

declare(strict_types=1);
?>

<!-- Cabecera de la Tarjeta Isla -->
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2 d-lg-none">
        <img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz OS" width="28" height="28" class="rounded">
        <span class="fw-bold text-navy fs-5">Devioz<span style="color: var(--ui-cyan);">OS</span></span>
    </div>
    <h3 class="fw-bold mb-1" style="color: var(--ui-navy); letter-spacing: -0.5px;">Inicie sesión en Devioz OS</h3>
    <p class="text-secondary small mb-0">Ingrese sus credenciales para acceder al sistema operativo y portafolio</p>
</div>

<!-- Alerta de Sesión Expirada -->
<?php if (isset($_GET['session_expired']) && $_GET['session_expired'] == '1'): ?>
    <div class="alert alert-warning py-2 px-3 mb-3 small d-flex align-items-center gap-2 border-0 shadow-sm" style="background: #FFFBE6; color: #D46B08; border-left: 4px solid #FAAD14 !important; border-radius: 6px;">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>Su sesión ha expirado por inactividad. Por favor ingrese nuevamente.</div>
    </div>
<?php endif; ?>

<!-- Formulario Principal -->
<form action="<?= url('/login') ?>" method="POST" autocomplete="on" id="mainLoginForm">
    <?= csrf_field() ?>

    <!-- Campo Correo Electrónico -->
    <div class="mb-3">
        <label for="email" class="form-label-custom">Correo Electrónico</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                <i class="fa-regular fa-envelope"></i>
            </span>
            <input type="email"
                   class="form-control form-control-custom border-start-0"
                   id="email"
                   name="email"
                   value="<?= e(old('email')) ?>"
                   placeholder="usuario@devioz.io"
                   autocomplete="email"
                   required
                   autofocus
                   style="border-radius: 0 8px 8px 0;">
        </div>
    </div>

    <!-- Campo Contraseña -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label-custom mb-0">Contraseña</label>
            <a href="<?= url('/login') ?>" class="text-muted small text-decoration-none" style="font-size: 0.78rem;" title="Recuperar contraseña">
                ¿Olvidó su contraseña?
            </a>
        </div>
        <div class="input-group auth-password-wrapper">
            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                <i class="fa-solid fa-key"></i>
            </span>
            <input type="password"
                   class="form-control form-control-custom border-start-0 pe-5"
                   id="password"
                   name="password"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   required
                   style="border-radius: 0 8px 8px 0;">
            <button type="button"
                    class="btn-toggle-password"
                    data-target="password"
                    aria-label="Mostrar u ocultar contraseña"
                    title="Mostrar contraseña"
                    tabindex="-1">
                <i class="fa-regular fa-eye eye-open"></i>
                <i class="fa-regular fa-eye-slash eye-closed d-none"></i>
            </button>
        </div>
    </div>

    <!-- Recordar Sesión -->
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
        <label class="form-check-label small text-secondary" for="remember">
            Recordar mi sesión en este equipo
        </label>
    </div>

    <!-- Botón de Envío Devioz Cyan -->
    <button type="submit" class="btn-ui-auth-submit" id="btnLoginSubmit">
        <span>Iniciar sesión</span>
        <i class="fa-solid fa-arrow-right"></i>
    </button>
</form>

<!-- Separador de Métodos Alternativos -->
<div class="position-relative my-4 text-center">
    <hr class="text-muted opacity-25 m-0">
    <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small fw-semibold" style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">
        O continuar con
    </span>
</div>

<!-- Inicio de Sesión Profesional con Google y GitHub -->
<div class="mb-4">
    <div class="row g-2">
        <div class="col-6">
            <a href="<?= url('/auth/google') ?>" class="btn-oauth-provider btn-oauth-google w-100" id="btnAuthGoogle" title="Continuar con Google Workspace">
                <svg width="18" height="18" viewBox="0 0 24 24" class="me-2" aria-hidden="true">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Google</span>
            </a>
        </div>
        <div class="col-6">
            <a href="<?= url('/auth/github') ?>" class="btn-oauth-provider btn-oauth-github w-100" id="btnAuthGithub" title="Continuar con GitHub Enterprise">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2 text-dark" aria-hidden="true">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                </svg>
                <span>GitHub</span>
            </a>
        </div>
    </div>
</div>

<!-- Enlace para Registro de Nueva Cuenta -->
<div class="text-center pt-2 border-top">
    <p class="text-secondary small mb-0">
        ¿Aún no tiene una cuenta?
        <a href="<?= url('/register') ?>" class="fw-bold text-decoration-none" style="color: var(--ui-cyan);">Regístrese gratis</a>
    </p>
</div>