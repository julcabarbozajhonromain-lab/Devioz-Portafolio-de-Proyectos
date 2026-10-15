<?php

declare(strict_types=1);

use App\Core\Session;

$flashes = Session::allFlash();
if (empty($flashes)) {
    return;
}
?>

<div class="flash-messages-container mb-4">
    <?php foreach ($flashes as $type => $messages): ?>
        <?php
        $bootstrapClass = match ($type) {
            'success' => 'alert-success border-success text-success-emphasis',
            'error'   => 'alert-danger border-danger text-danger-emphasis',
            'warning' => 'alert-warning border-warning text-warning-emphasis',
            'info'    => 'alert-info border-info text-info-emphasis',
            default   => 'alert-secondary',
        };

        $icon = match ($type) {
            'success' => 'fa-circle-check',
            'error'   => 'fa-circle-xmark',
            'warning' => 'fa-triangle-exclamation',
            'info'    => 'fa-circle-info',
            default   => 'fa-bell',
        };
        ?>
        <?php foreach ($messages as $msg): ?>
            <div class="alert <?= $bootstrapClass ?> alert-dismissible fade show alert-flash shadow-sm d-flex align-items-center gap-2" role="alert">
                <i class="fa-solid <?= $icon ?> fs-5" aria-hidden="true"></i>
                <div class="flex-grow-1 fw-medium"><?= e($msg) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
