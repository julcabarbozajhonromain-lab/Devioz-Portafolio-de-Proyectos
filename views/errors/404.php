<?php

declare(strict_types=1);
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6 text-center">
        <div class="card-custom p-5 border shadow-sm" style="background: #ffffff; border-color: #DBE2E8 !important; border-radius: 12px; box-shadow: 0 1px 3px rgba(31, 41, 55, 0.05);">
            <div class="mb-4">
                <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fs-6 fw-bold">HTTP 404</span>
            </div>
            <h1 class="display-3 fw-bold text-warning mb-2" style="letter-spacing: -1px;">404</h1>
            <h4 class="fw-bold mb-3" style="color: #1F2937;">Página No Encontrada</h4>
            <p class="mb-4" style="color: #626F86; font-size: 0.95rem; line-height: 1.5;">
                <?= e($message ?: 'La ruta o recurso corporativo solicitado no existe o fue reubicado en Devioz OS.') ?>
            </p>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= url('/dashboard') ?>" class="btn px-4 py-2 text-white fw-semibold" style="background: #10B981; border-radius: 8px;">
                    <i class="fa-solid fa-arrow-left me-1"></i> Regresar al Panel
                </a>
                <a href="<?= url('/gallery') ?>" class="btn px-4 py-2 fw-semibold" style="background: #F4F5F7; color: #1F2937; border: 1px solid #DBE2E8; border-radius: 8px;">
                    Portafolio
                </a>
            </div>
        </div>
    </div>
</div>
