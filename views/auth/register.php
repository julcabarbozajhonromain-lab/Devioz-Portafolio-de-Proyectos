<?php

declare(strict_types=1);
?>

<!-- Cabecera de la Tarjeta Isla -->
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2 d-lg-none">
        <img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz OS" width="28" height="28" class="rounded">
        <span class="fw-bold text-navy fs-5">Devioz<span style="color: var(--ui-cyan);">OS</span></span>
    </div>
    <h3 class="fw-bold mb-1" style="color: var(--ui-navy); letter-spacing: -0.5px;">Crear Cuenta en Devioz OS</h3>
    <p class="text-secondary small mb-0">Únase a la plataforma de gestión de proyectos y talento TI</p>
</div>

<!-- Formulario de Registro -->
<form action="<?= url('/register') ?>" method="POST" autocomplete="on" id="formRegister">
    <?= csrf_field() ?>

    <!-- Nombre Completo -->
    <div class="mb-3">
        <label for="name" class="form-label-custom">Nombre Completo</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                <i class="fa-regular fa-user"></i>
            </span>
            <input type="text"
                   class="form-control form-control-custom border-start-0"
                   id="name"
                   name="name"
                   value="<?= e(old('name')) ?>"
                   placeholder="Ej: Juan Pérez Morales"
                   autocomplete="name"
                   required
                   autofocus
                   style="border-radius: 0 8px 8px 0;">
        </div>
    </div>

    <!-- Correo Corporativo -->
    <div class="mb-3">
        <label for="email" class="form-label-custom">Correo Corporativo / Académico</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                <i class="fa-regular fa-envelope"></i>
            </span>
            <input type="email"
                   class="form-control form-control-custom border-start-0"
                   id="email"
                   name="email"
                   value="<?= e(old('email')) ?>"
                   placeholder="nombre.apellido@devioz.io"
                   autocomplete="email"
                   required
                   style="border-radius: 0 8px 8px 0;">
        </div>
    </div>

    <!-- Hub Operativo Devioz -->
    <div class="mb-3">
        <label for="sede" class="form-label-custom">Hub Operativo Devioz</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 8px 0 0 8px;">
                <i class="fa-solid fa-building-columns"></i>
            </span>
            <select class="form-select form-select-custom border-start-0" id="sede" name="sede" required style="border-radius: 0 8px 8px 0;">
                <option value="" disabled <?= old('sede') === '' ? 'selected' : '' ?>>Seleccione su hub operativo</option>
                <option value="Cercado" <?= old('sede') === 'Cercado' ? 'selected' : '' ?>>Hub Cercado (Sede Central)</option>
                <option value="Lima Norte" <?= old('sede') === 'Lima Norte' ? 'selected' : '' ?>>Hub Lima Norte (Independencia)</option>
                <option value="Villa" <?= old('sede') === 'Villa' ? 'selected' : '' ?>>Hub Sur (Villa El Salvador)</option>
            </select>
        </div>
    </div>

    <!-- Contraseñas -->
    <div class="row g-2 mb-3">
        <div class="col-sm-6">
            <label for="password" class="form-label-custom">Contraseña</label>
            <div class="input-group auth-password-wrapper">
                <input type="password"
                       class="form-control form-control-custom pe-5"
                       id="password"
                       name="password"
                       placeholder="Mín. 8 caracteres"
                       autocomplete="new-password"
                       required
                       style="border-radius: 8px;">
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
        <div class="col-sm-6">
            <label for="password_confirmation" class="form-label-custom">Confirmar</label>
            <div class="input-group auth-password-wrapper">
                <input type="password"
                       class="form-control form-control-custom pe-5"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Repetir clave"
                       autocomplete="new-password"
                       required
                       style="border-radius: 8px;">
                <button type="button"
                        class="btn-toggle-password"
                        data-target="password_confirmation"
                        aria-label="Mostrar u ocultar contraseña"
                        title="Mostrar contraseña"
                        tabindex="-1">
                    <i class="fa-regular fa-eye eye-open"></i>
                    <i class="fa-regular fa-eye-slash eye-closed d-none"></i>
                </button>
            </div>
        </div>
    </div>
    <div id="passwordMatchHint" class="small mb-3 d-none"></div>

    <!-- Botón de Envío -->
    <button type="submit" class="btn-ui-auth-submit mt-2" id="btnRegisterSubmit">
        <span>Crear mi cuenta</span>
        <i class="fa-solid fa-arrow-right"></i>
    </button>
</form>

<!-- Enlace para Volver a Iniciar Sesión -->
<div class="text-center mt-4 pt-3 border-top">
    <p class="text-secondary small mb-0">
        ¿Ya tiene una cuenta registrada?
        <a href="<?= url('/login') ?>" class="fw-bold text-decoration-none" style="color: var(--ui-cyan);">Inicie sesión</a>
    </p>
</div>

<!-- Validación en Vivo de Contraseñas -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pass = document.getElementById('password');
        const passConf = document.getElementById('password_confirmation');
        const hint = document.getElementById('passwordMatchHint');

        function checkMatch() {
            if (!pass || !passConf || !hint) return;
            const val1 = pass.value;
            const val2 = passConf.value;

            if (val2.length === 0) {
                hint.classList.add('d-none');
                passConf.style.borderColor = '';
                return;
            }

            hint.classList.remove('d-none');
            if (val1 === val2) {
                hint.textContent = 'Las contraseñas coinciden correctamente.';
                hint.className = 'small mb-3 text-success fw-semibold';
                passConf.style.borderColor = 'var(--ui-cyan)';
            } else {
                hint.textContent = 'Las contraseñas aún no coinciden.';
                hint.className = 'small mb-3 text-danger fw-semibold';
                passConf.style.borderColor = '#EF4444';
            }
        }

        if (pass && passConf) {
            pass.addEventListener('input', checkMatch);
            passConf.addEventListener('input', checkMatch);
        }
    });
</script>