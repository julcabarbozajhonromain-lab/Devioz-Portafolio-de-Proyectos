<?php
/** @var array $delivery */
/** @var array $isoCriteria */
/** @var array $owaspCriteria */
?>
<div class="mb-4"><a href="<?= url('/admin/evaluations') ?>" class="small">← Volver a auditorías</a><h1 class="h3 fw-bold mt-2 mb-1">Auditoría QA basada en evidencias</h1><div class="text-secondary">#<?= e($delivery['project_code']) ?> · <?= e($delivery['project_name']) ?> · <?= e($delivery['title']) ?> · Responsable: <?= e($delivery['developer_name']) ?></div></div>
<div class="alert alert-warning"><strong>Dictamen interno:</strong> completa solo controles revisados. No registres PASS sin adjuntar o enlazar evidencia. Esta evaluación no es una certificación ISO.</div>
<form method="post" action="<?= url('/admin/quality-audits/' . $delivery['id']) ?>">
    <?= csrf_field() ?>
    <section class="card-enterprise p-3 p-lg-4 mb-4">
        <h2 class="h5">Identificación de la versión</h2><div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="release_version">Versión / release</label><input class="form-control" id="release_version" name="release_version" maxlength="100" placeholder="Ej. 1.4.0"></div>
            <div class="col-md-8"><label class="form-label" for="commit_sha">Commit SHA revisado</label><input class="form-control font-monospace" id="commit_sha" name="commit_sha" maxlength="64" pattern="[a-fA-F0-9]{7,64}" placeholder="Hash del commit exacto (opcional)"></div>
        </div>
    </section>
    <section class="card-enterprise p-3 p-lg-4 mb-4">
        <h2 class="h5">Calidad del producto · ISO/IEC 25010:2023</h2><p class="text-secondary small">Valora cada atributo de 0 (no demostrado) a 4 (demostrado para el alcance). Cada puntuación requiere evidencia.</p>
        <?php foreach ($isoCriteria as $key => [$label, $prompt]): ?>
            <div class="border rounded p-3 mb-3"><div class="fw-semibold mb-2"><?= e($label) ?></div><div class="small text-secondary mb-2"><?= e($prompt) ?></div><div class="row g-2"><div class="col-md-3"><label class="form-label small" for="iso_<?= e($key) ?>">Valoración</label><select class="form-select" id="iso_<?= e($key) ?>" name="iso[<?= e($key) ?>][rating]"><option value="">Sin evaluar</option><?php for ($rating = 0; $rating <= 4; $rating++): ?><option value="<?= $rating ?>"><?= $rating ?> / 4</option><?php endfor; ?></select></div><div class="col-md-9"><label class="form-label small" for="evidence_<?= e($key) ?>">Evidencia verificable</label><textarea class="form-control" id="evidence_<?= e($key) ?>" name="iso[<?= e($key) ?>][evidence]" rows="2" placeholder="Enlace, caso de prueba, métrica observada, archivo o ubicación de evidencia"></textarea></div></div></div>
        <?php endforeach; ?>
    </section>
    <section class="card-enterprise p-3 p-lg-4 mb-4">
        <h2 class="h5">Revisión de seguridad · OWASP Top 10:2025</h2><p class="text-secondary small">Registra el alcance revisado y la evidencia para cada categoría. “Sin hallazgos” no equivale a riesgo cero.</p>
        <?php foreach ($owaspCriteria as $key => $label): ?><div class="row g-2 align-items-end border-bottom py-2"><div class="col-md-4 fw-semibold"><?= e($key) ?> · <?= e($label) ?></div><div class="col-md-3"><label class="visually-hidden" for="owasp_<?= e($key) ?>">Resultado <?= e($key) ?></label><select class="form-select" id="owasp_<?= e($key) ?>" name="owasp[<?= e($key) ?>][result]"><option value="not_assessed">Sin evaluar</option><option value="no_finding">Sin hallazgos en el alcance</option><option value="finding">Hallazgo identificado</option><option value="not_applicable">No aplica (justificar)</option></select></div><div class="col-md-5"><label class="visually-hidden" for="owasp_evidence_<?= e($key) ?>">Evidencia</label><input class="form-control" id="owasp_evidence_<?= e($key) ?>" name="owasp[<?= e($key) ?>][evidence]" placeholder="Evidencia o justificación"></div></div><?php endforeach; ?>
    </section>
    <section class="card-enterprise p-3 p-lg-4 mb-4">
        <h2 class="h5">Herramientas y ejecuciones</h2><p class="text-secondary small">Estado informado por el auditor. La integración automática de herramientas se habilitará aparte; este formulario no ejecuta SonarQube ni escáneres.</p>
        <?php foreach (['automated_tests' => 'Pruebas automatizadas', 'static_analysis' => 'Análisis estático (p. ej. SonarQube)', 'security_scan' => 'Análisis de seguridad (SAST / DAST / dependencias)'] as $key => $label): ?>
            <div class="row g-2 mb-3"><div class="col-md-3"><label class="form-label" for="tool_<?= e($key) ?>"><?= e($label) ?></label><select class="form-select" id="tool_<?= e($key) ?>" name="tools[<?= e($key) ?>][status]"><option value="not_run">No ejecutado</option><option value="passed">Aprobado</option><option value="failed">Fallido</option><option value="error">Error al ejecutar</option></select></div><div class="col-md-9"><label class="form-label" for="tool_evidence_<?= e($key) ?>">Evidencia / resultado</label><input class="form-control" id="tool_evidence_<?= e($key) ?>" name="tools[<?= e($key) ?>][evidence]" placeholder="URL del pipeline, reporte, versión de herramienta, métricas y fecha"></div></div>
        <?php endforeach; ?>
    </section>
    <section class="card-enterprise p-3 p-lg-4 mb-4"><h2 class="h5">Hallazgos</h2><p class="small text-secondary">Incluye problemas observados; los hallazgos críticos y altos bloquean el dictamen.</p>
        <?php for ($i = 0; $i < 3; $i++): ?><div class="border rounded p-3 mb-3"><div class="row g-2"><div class="col-md-3"><label class="form-label" for="severity_<?= $i ?>">Severidad</label><select class="form-select" id="severity_<?= $i ?>" name="findings[<?= $i ?>][severity]"><?php foreach (['critical' => 'Crítica', 'high' => 'Alta', 'medium' => 'Media', 'low' => 'Baja', 'info' => 'Informativa'] as $value => $title): ?><option value="<?= e($value) ?>" <?= $value === 'medium' ? 'selected' : '' ?>><?= e($title) ?></option><?php endforeach; ?></select></div><div class="col-md-9"><label class="form-label" for="finding_title_<?= $i ?>">Título</label><input class="form-control" id="finding_title_<?= $i ?>" name="findings[<?= $i ?>][title]" maxlength="255"></div><div class="col-12"><label class="form-label" for="finding_desc_<?= $i ?>">Descripción y evidencia</label><textarea class="form-control" id="finding_desc_<?= $i ?>" name="findings[<?= $i ?>][description]" rows="2"></textarea></div></div></div><?php endfor; ?>
    </section>
    <section class="card-enterprise p-3 p-lg-4 mb-4"><label class="form-label fw-semibold" for="feedback">Conclusión y acciones requeridas</label><textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Resume el alcance revisado, limitaciones, riesgos aceptados y próximos pasos"></textarea></section>
    <div class="d-flex justify-content-end gap-2 mb-5"><a href="<?= url('/admin/evaluations') ?>" class="btn btn-outline-secondary">Cancelar</a><button class="btn btn-devioz-primary" type="submit">Guardar dictamen QA</button></div>
</form>
