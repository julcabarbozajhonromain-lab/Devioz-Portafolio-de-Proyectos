<?php

declare(strict_types=1);

/** @var array $evaluations */
/** @var array $pendingDeliveries */
?>



<!-- Encabezado de auditorías QA -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 page-header-title text-white">Auditorías de Calidad & QA Software</h4>
        <div class="page-header-subtitle text-white-50 small mt-1">
            Evaluación normativa ISO/IEC 25010, seguridad OWASP Top 10 y dictámenes de entregables
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge-liquid-glass">
            <i class="fa-solid fa-certificate me-1 text-warning"></i> <?= count($pendingDeliveries) ?> pendientes de dictamen
        </span>
    </div>
</div>

<!-- SECCIÓN 1: ENTREGAS PENDIENTES DE AUDITORÍA QA -->
<div class="card-enterprise mb-4">
    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom" style="border-color: #E0E8ED !important;">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle p-1" style="width: 10px; height: 10px; background-color: #f59e0b;"></span>
            <span class="fw-bold text-dark" style="font-size: 16px;">Entregas Pendientes de Auditoría QA (<?= count($pendingDeliveries) ?>)</span>
        </div>
        <span class="badge whitespace-nowrap" style="background: rgba(245, 158, 11, 0.15); color: #d97706; font-weight: 600;">Requieren Dictamen</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small border-bottom" style="border-color: #E0E8ED !important; font-size: 12px; letter-spacing: 0.03em;">
                    <th style="min-width: 130px;">PROYECTO</th>
                    <th class="col-author" style="min-width: 150px;">DESARROLLADOR</th>
                    <th style="min-width: 180px;">HITO / ENTREGABLE</th>
                    <th class="whitespace-nowrap" style="min-width: 120px;">FECHA ENVÍO</th>
                    <th style="min-width: 110px;">EVIDENCIAS</th>
                    <th class="text-end col-actions whitespace-nowrap" style="min-width: 140px;">DICTAMINAR QA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingDeliveries as $del): ?>
                    <tr class="border-bottom" style="border-color: #E0E8ED !important;">
                        <td>
                            <span class="fw-bold font-monospace text-primary" style="color: #10B981 !important;">#<?= e($del['project_code']) ?></span>
                            <div class="small text-secondary text-truncate" style="max-width: 160px;"><?= e($del['project_name']) ?></div>
                        </td>
                        <td class="fw-medium text-dark whitespace-nowrap"><?= e($del['developer_name']) ?></td>
                        <td>
                            <div class="fw-semibold text-dark small"><?= e($del['title']) ?></div>
                            <div class="text-muted small" style="font-size: 0.75rem;">Hito: <?= e($del['milestone_name']) ?></div>
                        </td>
                        <td class="small text-secondary whitespace-nowrap"><?= format_date($del['submitted_at']) ?></td>
                        <td>
                            <?php if (!empty($del['video_url'])): ?>
                                <button type="button" class="btn btn-devioz-ghost btn-sm py-0 px-2 whitespace-nowrap" data-video-url="<?= e($del['video_url']) ?>" data-video-title="Entrega: <?= e($del['title']) ?> (<?= e($del['developer_name']) ?>)">
                                    <i class="fa-solid fa-play text-primary me-1" style="color: #10B981 !important;"></i> Reel
                                </button>
                            <?php endif; ?>
                            <?php if (!empty($del['files'])): ?>
                                <?php foreach ((json_decode((string)$del['files'], true) ?: []) as $fileIndex => $file): ?>
                                    <a class="d-block small" href="<?= url("/deliveries/{$del['id']}/files/{$fileIndex}") ?>"><i class="fa-solid fa-paperclip" aria-hidden="true"></i> <?= e(is_array($file) ? ($file['original_name'] ?? $file['name'] ?? 'Descargar adjunto') : 'Descargar adjunto') ?></a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button type="button"
                                    class="btn btn-devioz-primary btn-sm whitespace-nowrap"
                                    data-bs-toggle="modal"
                                    data-bs-target="#gradeModal"
                                    data-delivery-id="<?= $del['id'] ?>"
                                    data-delivery-title="<?= e($del['title']) ?>"
                                    data-dev-name="<?= e($del['developer_name']) ?>">
                                <i class="fa-solid fa-check me-1"></i> Dictaminar Hito
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pendingDeliveries)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary small">
                            <i class="fa-solid fa-circle-check text-success me-1" style="color: #16A34A !important;"></i> ¡Al día! No hay entregables pendientes de auditoría en este momento.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SECCIÓN 2: HISTORIAL DE DICTÁMENES DE CALIDAD -->
<div class="card-enterprise">
    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom" style="border-color: #E0E8ED !important;">
        <div class="fw-bold text-dark" style="font-size: 16px;">Historial de Dictámenes de Calidad & QA</div>
        <span class="badge bg-primary-subtle text-primary whitespace-nowrap" style="color: #10B981 !important; font-weight: 600;">Checklist Certificado</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="text-secondary small border-bottom" style="border-color: #E0E8ED !important; font-size: 12px; letter-spacing: 0.03em;">
                    <th style="min-width: 120px;">PROYECTO</th>
                    <th class="col-author" style="min-width: 150px;">DESARROLLADOR</th>
                    <th style="min-width: 170px;">LEAD TECH AUDITOR</th>
                    <th class="whitespace-nowrap" style="min-width: 130px;">QA SCORE</th>
                    <th style="min-width: 220px;">ESTÁNDARES DE ARQUITECTURA & CHECKLIST</th>
                    <th class="whitespace-nowrap" style="min-width: 120px;">FECHA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluations as $ev): ?>
                    <tr class="border-bottom" style="border-color: #E0E8ED !important;">
                        <td class="fw-bold font-monospace text-primary" style="color: #10B981 !important;">#<?= e($ev['project_code']) ?></td>
                        <td class="fw-medium text-dark whitespace-nowrap"><?= e($ev['developer_name']) ?></td>
                        <td class="small text-secondary whitespace-nowrap">
                            <i class="fa-solid fa-user-shield me-1 text-primary" style="color: #10B981 !important;"></i> <?= e($ev['evaluator_name']) ?>
                        </td>
                        <td>
                            <span class="badge whitespace-nowrap" style="background: rgba(22, 163, 74, 0.15); color: #5ea300; font-size: 13px; font-weight: 700; border: 1px solid rgba(22, 163, 74, 0.25);">
                                QA: <?= number_format((float)$ev['score'], 1) ?>
                            </span>
                        </td>
                        <td class="small text-secondary fst-italic"><?= e($ev['feedback']) ?></td>
                        <td class="small text-secondary whitespace-nowrap"><?= format_date($ev['evaluated_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($evaluations)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-secondary small">No hay dictámenes de auditoría registrados en el historial.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Dictaminar Entrega & QA Score -->
<div class="modal fade" id="gradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-enterprise p-0 overflow-hidden" style="border-radius: 12px;">
            <div class="modal-header p-3 px-4" style="border-bottom: 1px solid #E0E8ED;">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="gradeModalTitle" style="font-size: 18px;">
                    <i class="fa-solid fa-clipboard-check text-primary" style="color: #10B981 !important;"></i>
                    <span>Dictaminar Entrega & QA Score</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="gradeForm" action="" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="text-secondary small mb-3">
                        Desarrollador Responsable: <strong id="gradeDevName" class="text-dark"></strong>
                    </p>

                    <div class="mb-3">
                        <label for="score" class="ui-form-label">Score de Calidad QA (Escala 0 a 100) *</label>
                        <input type="number" step="0.5" min="0" max="100" name="score" id="score" class="form-control ui-form-input" placeholder="Ej: 95.0" required autofocus>
                        <div class="mt-1 text-muted" style="font-size: 12px;">Score &ge; 60 aprueba formalmente el hito. Score &ge; 90 otorga Certificación de Excelencia.</div>
                    </div>

                    <!-- Plantillas Rápidas de Dictamen Normativo -->
                    <div class="mb-2">
                        <label class="ui-form-label d-flex justify-content-between align-items-center">
                            <span>Estándares de Arquitectura & Checklist QA *</span>
                            <span class="text-secondary small" style="font-size: 0.7rem;">Plantillas rápidas:</span>
                        </label>
                        <div class="d-flex gap-1 mb-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-devioz-ghost py-0 px-2 small" style="font-size: 0.72rem;" onclick="insertAuditTemplate('iso')">
                                <i class="fa-solid fa-certificate text-primary me-1"></i> ISO/IEC 25010
                            </button>
                            <button type="button" class="btn btn-sm btn-devioz-ghost py-0 px-2 small" style="font-size: 0.72rem;" onclick="insertAuditTemplate('owasp')">
                                <i class="fa-solid fa-shield-halved text-success me-1"></i> OWASP Top 10
                            </button>
                            <button type="button" class="btn btn-sm btn-devioz-ghost py-0 px-2 small" style="font-size: 0.72rem;" onclick="insertAuditTemplate('full')">
                                <i class="fa-solid fa-star text-warning me-1"></i> Auditoría Completa
                            </button>
                        </div>
                        <textarea name="feedback" id="feedback" rows="5" class="form-control ui-form-input w-100 font-monospace" style="font-size: 0.82rem;" placeholder="Escribe el dictamen técnico, checklist validado y observaciones de calidad..." required></textarea>
                    </div>

                    <div class="p-2 rounded bg-light border d-flex align-items-center justify-content-between small text-secondary">
                        <span style="font-size: 0.72rem;"><i class="fa-solid fa-circle-check text-success me-1"></i> Verifica ISO 25010 & OWASP Top 10</span>
                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Devioz QA Gate</span>
                    </div>
                </div>
                <div class="modal-footer p-3 px-4" style="border-top: 1px solid #E0E8ED;">
                    <button type="button" class="btn btn-devioz-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-devioz-primary">Aprobar Hito y Emitir Dictamen QA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function insertAuditTemplate(type) {
    const textarea = document.getElementById('feedback');
    const scoreInput = document.getElementById('score');
    if (!textarea) return;

    if (type === 'iso') {
        textarea.value = `[AUDITORÍA ISO/IEC 25010]\n- Adecuación Funcional: Conforme con los requerimientos del cliente.\n- Eficiencia y Rendimiento: Tiempos de respuesta dentro del SLA.\n- Usabilidad: Interfaz ergonómica estándar Devioz.\n- Mantenibilidad: Código ordenado, PSR-12 y sin duplicidad crítica.`;
        if (scoreInput && !scoreInput.value) scoreInput.value = '95.0';
    } else if (type === 'owasp') {
        textarea.value = `[AUDITORÍA OWASP TOP 10 & APPSEC]\n- A01 Broken Access Control: Validado RBAC estricto.\n- A02 Cryptographic Failures: Claves protegidas, hashes seguros.\n- A03 Injection: Consultas 100% preparadas en PDO.\n- A05 Security Misconfig: Headers y tokens CSRF conformes.`;
        if (scoreInput && !scoreInput.value) scoreInput.value = '96.5';
    } else if (type === 'full') {
        textarea.value = `[DICTAMEN COMPLETO DEVIOZ ENTERPRISE QA]\n1. Calidad de Software: Aprobado bajo norma ISO/IEC 25010.\n2. Seguridad de Aplicaciones: Aprobado bajo controles OWASP Top 10.\n3. Deuda Técnica: Aceptable, SonarQube Quality Gate en verde.\n4. Recomendación: Hito apto para consumo corporativo y release en PaaS.`;
        if (scoreInput && !scoreInput.value) scoreInput.value = '98.0';
    }
    textarea.focus();
}

document.addEventListener('DOMContentLoaded', () => {
    const gradeModal = document.getElementById('gradeModal');
    if (gradeModal) {
        gradeModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const deliveryId = button.getAttribute('data-delivery-id');
            const deliveryTitle = button.getAttribute('data-delivery-title');
            const devName = button.getAttribute('data-dev-name');

            const form = gradeModal.querySelector('#gradeForm');
            const modalTitle = gradeModal.querySelector('#gradeModalTitle span');
            const devNameEl = gradeModal.querySelector('#gradeDevName');

            form.action = `<?= url('/admin/deliveries') ?>/${deliveryId}/grade`;
            modalTitle.textContent = `Dictamen QA: ${deliveryTitle}`;
            devNameEl.textContent = devName;
        });
    }
});
</script>
