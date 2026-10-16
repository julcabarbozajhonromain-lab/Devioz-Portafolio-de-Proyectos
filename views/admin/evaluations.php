<?php
/** @var array $pendingDeliveries */
/** @var array $qualityAudits */
$gateLabels = ['passed' => 'Aprobada', 'failed' => 'No supera la compuerta', 'blocked' => 'Bloqueada', 'pending' => 'Pendiente', 'not_run' => 'Sin ejecutar'];
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">Calidad y auditorías QA</h1>
        <p class="text-secondary mb-0">Revisión interna basada en evidencias · ISO/IEC 25010:2023 · OWASP Top 10:2025</p>
    </div>
    <span class="badge text-bg-warning"><?= count($pendingDeliveries) ?> entregas pendientes</span>
</div>
<div class="alert alert-info"><strong>Alcance:</strong> este módulo emite dictámenes internos de calidad. No certifica ISO ni sustituye una auditoría independiente. Un resultado solo se considera aprobado cuando se registran criterios, evidencias y controles requeridos.</div>

<section class="card-enterprise p-3 p-lg-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Entregas por revisar</h2><span class="text-secondary small">La aprobación del hito depende de la compuerta QA</span></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Proyecto</th><th>Desarrollador</th><th>Entrega</th><th>Enviado</th><th></th></tr></thead><tbody>
    <?php foreach ($pendingDeliveries as $delivery): ?>
        <tr>
            <td><a href="<?= url('/projects/' . $delivery['project_id']) ?>" class="fw-semibold">#<?= e($delivery['project_code']) ?></a><div class="small text-secondary"><?= e($delivery['project_name']) ?></div></td>
            <td><?= e($delivery['developer_name']) ?></td><td><?= e($delivery['title']) ?><div class="small text-secondary"><?= e($delivery['milestone_name'] ?: 'Sin hito asociado') ?></div></td>
            <td><?= !empty($delivery['submitted_at']) ? format_date($delivery['submitted_at']) : '—' ?></td>
            <td class="text-end"><a class="btn btn-devioz-primary btn-sm" href="<?= url('/admin/quality-audits/create/' . $delivery['id']) ?>"><i class="fa-solid fa-clipboard-check me-1"></i>Iniciar auditoría</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$pendingDeliveries): ?><tr><td colspan="5" class="text-center text-secondary py-4">No hay entregas pendientes de revisión.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>

<section class="card-enterprise p-3 p-lg-4">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Historial de auditorías</h2><span class="small text-secondary">Las auditorías anteriores permanecen trazables</span></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Proyecto</th><th>Versión / commit</th><th>Auditor</th><th>Score</th><th>Compuerta</th><th>Fecha</th><th></th></tr></thead><tbody>
    <?php foreach ($qualityAudits as $audit): ?>
        <tr>
            <td><span class="fw-semibold">#<?= e($audit['project_code']) ?></span><div class="small text-secondary"><?= e($audit['project_name']) ?></div></td>
            <td><?= e($audit['release_version'] ?: 'Sin versión') ?><div class="small text-secondary font-monospace"><?= e($audit['commit_sha'] ?: 'Commit no informado') ?></div></td>
            <td><?= e($audit['evaluator_name']) ?></td><td><?= $audit['score'] !== null ? number_format((float)$audit['score'], 1) . '/100' : 'Sin score' ?></td>
            <td><span class="badge text-bg-<?= $audit['gate_status'] === 'passed' ? 'success' : ($audit['gate_status'] === 'failed' || $audit['gate_status'] === 'blocked' ? 'danger' : 'secondary') ?>"><?= e($gateLabels[$audit['gate_status']] ?? $audit['gate_status']) ?></span></td>
            <td><?= format_date($audit['created_at']) ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= url('/admin/quality-audits/' . $audit['id']) ?>">Ver informe</a></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$qualityAudits): ?><tr><td colspan="7" class="text-center text-secondary py-4">Aún no hay auditorías registradas con evidencia.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
