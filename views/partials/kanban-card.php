<?php

declare(strict_types=1);

/** @var \App\Models\Project $p */
/** @var string $statusLabel */

$dev = $p->developer();
$devName = $dev ? $dev->name : 'Desarrollador';
$devAvatar = $dev ? $dev->avatarUrl() : asset('images/devioz-icon.png');

$admin = $p->assignedBy();
$adminName = $admin ? $admin->name : 'Elena Rostova (Admin)';
$adminAvatar = $admin ? $admin->avatarUrl() : asset('images/devioz-icon.png');

$techs = is_array($p->tech_stack) ? $p->tech_stack : (json_decode($p->tech_stack ?? '[]', true) ?: []);
$techString = implode(' ', $techs);

// Semáforo de deadline según estado
$deadlineClass = match ($p->status) {
    'approved'       => 'ui-deadline-green',
    'rejected'       => 'ui-deadline-amber',
    'in_review'      => 'ui-deadline-blue',
    default          => 'ui-deadline-amber',
};

// Estimación de fecha de entrega
$dueDate = $p->assigned_at ? date('d M, 18:00', strtotime($p->assigned_at . ' +14 days')) : date('d M, 18:00', strtotime('+7 days'));
?>

<div class="gl-card kanban-card"
     data-project-id="<?= $p->id ?>"
     data-search-title="<?= e(strtolower($p->name)) ?>"
     data-search-code="<?= e(strtolower($p->code)) ?>"
     data-search-dev="<?= e(strtolower($devName)) ?>"
     data-search-client="<?= e(strtolower($p->clientName())) ?>"
     data-search-sede="<?= e(strtolower($p->sede ?: '')) ?>"
     data-search-tech="<?= e(strtolower($techString)) ?>">

    <!-- Top: Badge de Cliente/Empresa + Código de Proyecto -->
    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
        <span class="ui-client-badge" title="Empresa / Cliente: <?= e($p->clientName()) ?>">
            <i class="fa-solid fa-briefcase"></i>
            <span><?= e($p->clientName()) ?></span>
        </span>
        <span class="gl-issue-ref font-monospace fw-bold text-emerald">#<?= e($p->code) ?></span>
    </div>

    <!-- Título del Proyecto -->
    <a href="<?= url("/projects/{$p->id}") ?>" class="gl-card-title text-decoration-none fw-semibold mb-1" title="<?= e($p->name) ?>">
        <?= e($p->name) ?>
    </a>

    <!-- Miniatura Visual del Entregable (Media Preview estilo Devioz) -->
    <div class="ui-card-cover-wrapper">
        <img src="<?= e($p->coverUrl()) ?>" alt="<?= e($p->name) ?>" class="ui-card-cover-img" loading="lazy">
    </div>

    <!-- Plazo Límite con Semáforo + Sede -->
    <div class="d-flex align-items-center justify-content-between gap-1 my-1">
        <span class="ui-deadline-badge <?= $deadlineClass ?>">
            <i class="fa-regular fa-clock"></i>
            <span><?= e($dueDate) ?></span>
        </span>
        <?php if ($p->sede): ?>
            <span class="badge-sede"><?= e($p->sede) ?></span>
        <?php endif; ?>
    </div>

    <!-- Entregables Técnicos y Chips Tecnológicos -->
    <div class="gl-labels-row mt-1">
        <?php if ($p->final_score && $p->status === 'approved'): ?>
            <span class="gl-label gl-label-green">Nota: <?= number_format((float)$p->final_score, 1) ?></span>
        <?php endif; ?>

        <?php
        $cIdx = 0;
        foreach (array_slice($techs, 0, 2) as $t):
            $colorPills = ['gl-label-emerald', 'gl-label-blue', 'gl-label-purple', 'gl-label-orange'];
            $cClass = $colorPills[$cIdx % 4];
            $cIdx++;
        ?>
            <span class="gl-label <?= $cClass ?>"><?= e($t) ?></span>
        <?php endforeach; ?>

        <?php if ($p->video_url): ?>
            <button type="button" class="gl-label gl-label-interactive gl-label-video" title="Reproducir sustentación" data-video-url="<?= e($p->video_url) ?>" data-video-title="<?= e($p->name) ?>">
                <i class="fa-solid fa-play"></i> Reel
            </button>
        <?php endif; ?>
        <?php if ($p->documentation): ?>
            <span class="gl-label gl-label-docs" title="Documentación técnica disponible"><i class="fa-solid fa-file-lines"></i> Docs</span>
        <?php endif; ?>
        <?php if ($p->live_url): ?>
            <a href="<?= e($p->live_url) ?>" target="_blank" rel="noopener noreferrer" class="gl-label gl-label-live" title="Ver demo en vivo"><i class="fa-solid fa-arrow-up-right-from-square"></i> Demo</a>
        <?php endif; ?>
    </div>

    <?php if ($p->rejection_reason && $p->status === 'rejected'): ?>
        <div class="gl-card-alert-note text-danger small mt-2 p-1 px-2 rounded bg-danger-subtle border border-danger-subtle d-flex align-items-center gap-1" title="<?= e($p->rejection_reason) ?>">
            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
            <span class="text-truncate" style="max-width: 220px;"><?= e($p->rejection_reason) ?></span>
        </div>
    <?php endif; ?>

    <!-- Footer: Flujo de Delegación (Supervisor -> Dev) + Enlace de Detalle -->
    <div class="d-flex align-items-center justify-content-between pt-2 mt-2 border-top" style="border-color: var(--border-color) !important;">
        <div class="ui-delegation-flow" title="Asignado por: <?= e($adminName) ?> → Desarrollador: <?= e($devName) ?>">
            <img src="<?= e($adminAvatar) ?>" alt="<?= e($adminName) ?>" class="ui-avatar-supervisor" title="Supervisor: <?= e($adminName) ?>" loading="lazy">
            <span class="ui-delegation-arrow">→</span>
            <img src="<?= e($devAvatar) ?>" alt="<?= e($devName) ?>" class="ui-avatar-dev" title="Desarrollador: <?= e($devName) ?>" loading="lazy">
            <span class="small text-secondary text-truncate ms-1" style="max-width: 90px;"><?= e(explode(' ', $devName)[0]) ?></span>
        </div>
        <a href="<?= url("/projects/{$p->id}") ?>" class="gl-card-link small">
            <span>Detalle</span>
            <span class="gl-link-arrow">→</span>
        </a>
    </div>
</div>
