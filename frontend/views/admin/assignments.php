<?php

declare(strict_types=1);

/** @var array $unassigned */
/** @var array $assigned */
/** @var array $unassignedProjects */
$unassignedProjects = $unassignedProjects ?? [];
?>

<!-- Encabezado de asignaciones -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 page-header-title text-white">Matriz de Asignaciones de Talento</h4>
        <div class="page-header-subtitle text-white-50 small mt-1">
            Gestión operativa integral: Asignación, Reasignación, Liberación y Retiro de Proyectos SDLC
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= url('/projects/create') ?>" class="btn btn-devioz-green btn-sm d-flex align-items-center gap-1 shadow-sm">
            <i class="fa-solid fa-plus"></i>
            <span>Nueva Asignación Directa</span>
        </a>
    </div>
</div>

<!-- TARJETAS RESUMEN DE DISPONIBILIDAD OPERATIVA -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-custom p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.12); color: #F59E0B; font-size: 1.2rem;">
                <i class="fa-solid fa-user-clock"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 text-dark lh-1"><?= count($unassigned) ?></div>
                <div class="text-secondary small mt-1">Desarrolladores Disponibles</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-custom p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.12); color: #10B981; font-size: 1.2rem;">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 text-dark lh-1"><?= count($assigned) ?></div>
                <div class="text-secondary small mt-1">Proyectos en Curso Activo</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card-custom p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.12); color: #10B981; font-size: 1.2rem;">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <div class="fw-bold fs-5 text-dark lh-1"><?= count($unassignedProjects) ?></div>
                <div class="text-secondary small mt-1">Proyectos en Espera (Pool)</div>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN: PROYECTOS SIN ASIGNAR / EN ESPERA (SI EXISTEN) -->
<?php if (!empty($unassignedProjects)): ?>
<div class="card-custom mb-4" style="border-left: 4px solid #10B981;">
    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-info p-1" style="width: 10px; height: 10px;"></span>
            <span class="fw-bold" style="color: var(--text-primary);">Proyectos en Espera de Asignación (<?= count($unassignedProjects) ?>)</span>
            <span class="badge bg-info-subtle text-info ms-2" style="font-size: 0.72rem;">Sin Desarrollador Asignado</span>
        </div>
        <span class="text-secondary small" style="font-size: 0.75rem;">Proyectos listos para delegarse a un nuevo practicante</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
                <tr class="text-secondary small border-bottom" style="border-color: var(--border-color) !important;">
                    <th>CÓDIGO</th>
                    <th>PROYECTO</th>
                    <th class="col-sede">SEDE</th>
                    <th class="col-status">ESTADO</th>
                    <th>REGISTRADO EL</th>
                    <th class="text-end col-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unassignedProjects as $up): ?>
                    <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                        <td>
                            <span class="badge font-monospace text-dark bg-light border px-2 py-1" style="font-size: 0.75rem;"><?= e($up['project_code']) ?></span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($up['project_name']) ?></div>
                        </td>
                        <td class="col-sede"><span class="badge-sede"><?= e($up['project_sede'] ?: 'Lima Norte') ?></span></td>
                        <td>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle fw-semibold" style="font-size: 0.72rem;">En Espera</span>
                        </td>
                        <td class="small text-secondary"><?= format_date($up['created_at']) ?></td>
                        <td class="text-end col-actions text-nowrap">
                            <button type="button" class="btn btn-devioz-green btn-sm px-2 py-1"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalReassign"
                                    data-project-id="<?= $up['project_id'] ?>"
                                    data-project-code="<?= e($up['project_code']) ?>"
                                    data-project-name="<?= e($up['project_name']) ?>"
                                    data-current-dev="Sin Asignar (En Espera)">
                                <i class="fa-solid fa-user-plus me-1"></i> Asignar
                            </button>
                            <a href="<?= url("/projects/{$up['project_id']}/edit") ?>" class="btn btn-ghost btn-sm px-2 py-1" title="Editar Ficha">
                                <i class="fa-solid fa-pen-to-square text-secondary"></i>
                            </a>
                            <button type="button" class="btn btn-ghost btn-sm px-2 py-1 text-danger" title="Eliminar Proyecto"
                                    onclick="confirmDelete('<?= $up['project_id'] ?>', '<?= e(addslashes($up['project_code'])) ?>')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- SECCIÓN 1: DESARROLLADORES SIN PROYECTO ASIGNADO -->
<div class="card-custom mb-4">
    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-warning p-1" style="width: 10px; height: 10px;"></span>
            <span class="fw-bold" style="color: var(--text-primary);">Desarrolladores Disponibles (<?= count($unassigned) ?>)</span>
            <span class="badge bg-secondary-subtle text-secondary ms-2" style="font-size: 0.72rem;">Sin Proyecto Activo</span>
        </div>
        <a href="<?= url('/projects/create') ?>" class="btn btn-brand btn-sm d-flex align-items-center gap-1">
            <i class="fa-solid fa-plus"></i>
            <span>Nueva Asignación Directa</span>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
                <tr class="text-secondary small border-bottom" style="border-color: var(--border-color) !important;">
                    <th>DESARROLLADOR</th>
                    <th>CORREO CORPORATIVO</th>
                    <th class="col-sede">SEDE</th>
                    <th>ESPECIALIDAD / HEADLINE</th>
                    <th class="text-end col-actions">ACCIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unassigned as $u): ?>
                    <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= e($u->avatarUrl()) ?>" class="rounded-circle" width="30" height="30" loading="lazy" alt="<?= e($u->name) ?>" style="object-fit: cover;">
                                <span class="fw-semibold" style="color: #1F2937;"><?= e($u->name) ?></span>
                            </div>
                        </td>
                        <td class="small text-secondary"><?= e($u->email) ?></td>
                        <td class="col-sede"><span class="badge-sede"><?= e($u->sede ?: 'Sin sede') ?></span></td>
                        <td class="small text-secondary"><?= e($u->headline ?: 'Desarrollo de Software') ?></td>
                        <td class="text-end">
                            <a href="<?= url("/projects/create?dev=" . $u->id) ?>" class="btn btn-brand btn-sm">
                                <span>Asignar Proyecto</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($unassigned)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-emerald small">
                            <i class="fa-solid fa-circle-check me-1"></i> Todos los desarrolladores cuentan actualmente con un proyecto activo asignado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- SECCIÓN 2: DESARROLLADORES CON PROYECTO EN CURSO -->
<div class="card-custom">
    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-emerald p-1" style="width: 10px; height: 10px;"></span>
            <span class="fw-bold" style="color: var(--text-primary);">Desarrolladores con Proyecto en Curso (<?= count($assigned) ?>)</span>
            <span class="badge-brand ms-2">En Desarrollo</span>
        </div>
        <span class="text-secondary small" style="font-size: 0.75rem;">1 Practicante = 1 Proyecto Activo</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
            <thead>
                <tr class="text-secondary small border-bottom" style="border-color: var(--border-color) !important;">
                    <th>DESARROLLADOR</th>
                    <th class="col-sede">SEDE</th>
                    <th>CÓDIGO</th>
                    <th>PROYECTO</th>
                    <th class="col-status">ESTADO</th>
                    <th>FECHA ASIGNADO</th>
                    <th class="text-end col-actions">GESTIÓN OPERATIVA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assigned as $row): ?>
                    <tr class="border-bottom" style="border-color: var(--border-color) !important;">
                        <td>
                            <div class="fw-semibold text-dark"><?= e($row['dev_name']) ?></div>
                            <div class="text-secondary" style="font-size: 0.72rem;"><?= e($row['dev_email']) ?></div>
                        </td>
                        <td class="col-sede"><span class="badge-sede"><?= e($row['dev_sede']) ?></span></td>
                        <td class="fw-bold" style="color: #10B981;"><?= e($row['project_code']) ?></td>
                        <td class="fw-medium text-dark"><?= e($row['project_name']) ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= e(ucfirst($row['project_status'])) ?></span>
                        </td>
                        <td class="small text-secondary"><?= format_date($row['assigned_at']) ?></td>
                        <td class="text-end col-actions text-nowrap">
                            <div class="d-inline-flex align-items-center gap-1">
                                <!-- Botón Ver Ficha -->
                                <a href="<?= url("/projects/{$row['project_id']}") ?>" class="btn btn-ghost btn-sm px-2 py-1" title="Ver Detalle del Proyecto">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-secondary"></i>
                                </a>

                                <!-- Menú Desplegable de Gestión -->
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-ghost btn-sm dropdown-toggle px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Opciones de Proyecto">
                                        <i class="fa-solid fa-ellipsis-vertical text-secondary"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border py-1" style="font-size: 0.82rem; min-width: 190px;">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 py-1" href="<?= url("/projects/{$row['project_id']}") ?>">
                                                <i class="fa-solid fa-eye text-primary" style="width: 16px;"></i>
                                                <span>Ver Proyecto</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-2 py-1" href="<?= url("/projects/{$row['project_id']}/edit") ?>">
                                                <i class="fa-solid fa-pen-to-square text-info" style="width: 16px;"></i>
                                                <span>Editar Proyecto</span>
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 text-dark"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalReassign"
                                                    data-project-id="<?= $row['project_id'] ?>"
                                                    data-project-code="<?= e($row['project_code']) ?>"
                                                    data-project-name="<?= e($row['project_name']) ?>"
                                                    data-current-dev="<?= e($row['dev_name']) ?>">
                                                <i class="fa-solid fa-user-pen text-warning" style="width: 16px;"></i>
                                                <span>Reasignar Desarrollador</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 text-danger"
                                                    onclick="confirmUnassign('<?= $row['project_id'] ?>', '<?= e(addslashes($row['project_code'])) ?>', '<?= e(addslashes($row['dev_name'])) ?>')">
                                                <i class="fa-solid fa-user-xmark text-danger" style="width: 16px;"></i>
                                                <span>Desasignar / Liberar</span>
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 py-1 text-danger"
                                                    onclick="confirmDelete('<?= $row['project_id'] ?>', '<?= e(addslashes($row['project_code'])) ?>')">
                                                <i class="fa-solid fa-trash-can text-danger" style="width: 16px;"></i>
                                                <span>Eliminar del Catálogo</span>
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($assigned)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary small">No hay asignaciones activas registradas actualmente.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- FORMULARIOS OCULTOS DE ACCIÓN OPERATIVA                                  -->
<!-- ========================================================================= -->
<form id="formUnassign" method="POST" action="" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="redirect_to" value="<?= url('/admin/assignments') ?>">
</form>

<form id="formDeleteProject" method="POST" action="" class="d-none">
    <?= csrf_field() ?>
    <input type="hidden" name="redirect_to" value="<?= url('/admin/assignments') ?>">
</form>

<!-- ========================================================================= -->
<!-- MODAL Devioz: REASIGNAR / ASIGNAR DESARROLLADOR                     -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalReassign" tabindex="-1" aria-labelledby="modalReassignLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0" style="border-radius: 16px; overflow: hidden; background: #FFFFFF;">

            <div class="modal-header border-bottom px-4 py-3 bg-light d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.12); color: #10B981;">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="modalReassignLabel">Gestión de Asignación SDLC</h6>
                        <span class="text-secondary" style="font-size: 0.72rem;">Transfiere o asigna un proyecto sin pérdida de hitos ni historial</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formReassignModal" method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="<?= url('/admin/assignments') ?>">

                <div class="modal-body p-4">
                    <!-- Tarjeta informativa del proyecto seleccionado -->
                    <div class="p-3 mb-3 rounded border" style="background: #F8FAFC; border-color: #E2E8F0 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge font-monospace text-dark bg-white border" id="modalReassignCode">DEV-2026-XXX</span>
                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">Transferencia de Asignación</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-2" id="modalReassignTitle">Nombre del Proyecto</h6>
                        <div class="text-secondary small d-flex align-items-center gap-2">
                            <i class="fa-solid fa-user text-muted"></i>
                            <span>Desarrollador actual: <strong class="text-dark" id="modalReassignCurrentDev">Nombre</strong></span>
                        </div>
                    </div>

                    <!-- Selector de nuevo desarrollador asignado -->
                    <div class="mb-3">
                        <label for="modalReassignSelect" class="form-label fw-semibold text-dark small mb-1">
                            Nuevo Desarrollador Asignado *
                        </label>
                        <?php if (!empty($unassigned)): ?>
                            <select name="developer_id" id="modalReassignSelect" class="form-select form-control-custom" required>
                                <option value="" disabled selected>-- Selecciona un ingeniero disponible --</option>
                                <?php foreach ($unassigned as $u): ?>
                                    <option value="<?= $u->id ?>">
                                        <?= e($u->name) ?> <?= $u->sede ? '— ' . e($u->sede) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text mt-1 text-secondary" style="font-size: 0.73rem;">
                                Listado exclusivo de ingenieros con disponibilidad inmediata (sin proyectos en curso).
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 px-3 small mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                                <span>No hay desarrolladores disponibles en este momento. Puedes desasignar/liberar a otro ingeniero primero.</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-2 rounded bg-light border text-secondary small d-flex align-items-start gap-2" style="font-size: 0.73rem;">
                        <i class="fa-solid fa-shield-halved text-info mt-1"></i>
                        <span>El nuevo desarrollador conservará de forma íntegra todo el repositorio, hitos completados y métricas de calidad ISO/IEC ya registradas.</span>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-2 bg-light d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-ghost" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-brand px-3" <?= empty($unassigned) ? 'disabled' : '' ?>>
                        <i class="fa-solid fa-check me-1"></i> Confirmar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalReassign');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (!button) return;

            const projectId = button.getAttribute('data-project-id');
            const projectCode = button.getAttribute('data-project-code') || '';
            const projectName = button.getAttribute('data-project-name') || '';
            const currentDev = button.getAttribute('data-current-dev') || 'Sin Asignar';

            const form = document.getElementById('formReassignModal');
            if (form && projectId) {
                form.action = '<?= url("/admin/projects") ?>/' + encodeURIComponent(projectId) + '/reassign';
            }

            const codeEl = document.getElementById('modalReassignCode');
            if (codeEl) codeEl.textContent = projectCode;

            const titleEl = document.getElementById('modalReassignTitle');
            if (titleEl) titleEl.textContent = projectName;

            const devEl = document.getElementById('modalReassignCurrentDev');
            if (devEl) devEl.textContent = currentDev;
        });
    }
});

function confirmUnassign(projectId, projectCode, devName) {
    if (confirm('¿Estás seguro de desasignar a ' + devName + ' del proyecto [' + projectCode + ']?\n\nEl desarrollador quedará libre inmediatamente y el proyecto pasará al banco de proyectos en espera.')) {
        const form = document.getElementById('formUnassign');
        if (form) {
            form.action = '<?= url("/admin/projects") ?>/' + encodeURIComponent(projectId) + '/unassign';
            form.submit();
        }
    }
}

function confirmDelete(projectId, projectCode) {
    if (confirm('¿Estás seguro de eliminar el proyecto [' + projectCode + '] del catálogo?\n\nEsta acción archivará el proyecto y liberará al desarrollador asignado.')) {
        const form = document.getElementById('formDeleteProject');
        if (form) {
            form.action = '<?= url("/projects") ?>/' + encodeURIComponent(projectId) + '/delete';
            form.submit();
        }
    }
}
</script>
