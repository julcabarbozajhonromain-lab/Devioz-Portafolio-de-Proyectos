<?php

declare(strict_types=1);
?>

<div class="card-custom p-5 text-center my-4">
    <div class="stat-icon mx-auto mb-3" style="width: 72px; height: 72px; font-size: 2rem;">
        <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
    </div>
    <h3 class="fw-bold text-white mb-2">No tienes un proyecto asignado</h3>
    <p class="text-secondary mx-auto mb-4" style="max-width: 500px;">
        Tu administrador o lead técnico aún no te ha asignado un proyecto activo. En cuanto se realice la asignación, podrás ver tus hitos, subir entregas y registrar tus reportes diarios aquí.
    </p>
    <div class="d-flex justify-content-center gap-3">
        <a href="<?= url('/gallery') ?>" class="btn btn-brand">Ver Proyectos de Referencia</a>
        <a href="<?= url('/dashboard') ?>" class="btn btn-ghost">Regresar al Panel</a>
    </div>
</div>
