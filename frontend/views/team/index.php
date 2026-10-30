<?php

declare(strict_types=1);

/** @var array $members */
/** @var string $search */
/** @var string $sede */
/** @var string $role */
/** @var string $status */
/** @var array $stats */
/** @var bool $isAdmin */

$status = $status ?? 'active';
?>

<!-- Header del Directorio de Talento -->
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1 page-header-title text-white d-flex align-items-center gap-2">
            <i class="fa-solid fa-users-gear text-emerald"></i>
            <span>Directorio de Talento & Consultoría TI</span>
        </h3>
        <p class="page-header-subtitle text-white-50 small mb-0">
            Gestión corporativa de ingenieros de software, control de disponibilidad y asignación de roles Devioz
        </p>
    </div>
    <?php if ($isAdmin): ?>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-brand btn-sm d-flex align-items-center gap-2 px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateMember">
                <i class="fa-solid fa-user-plus"></i>
                <span class="fw-semibold">Nuevo Programador</span>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- 4 Tarjetas de Métricas (KPIs de Capacidad de Equipo) -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1">
                <div class="stat-value text-white"><?= (int)($stats['total'] ?? count($members)) ?></div>
                <div class="stat-label text-truncate" title="Total de Consultores Registrados">Total de Consultores</div>
            </div>
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6;">
                <i class="fa-solid fa-users fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1">
                <div class="stat-value text-emerald"><?= (int)($stats['developers'] ?? 0) ?></div>
                <div class="stat-label text-truncate" title="Desarrolladores Activos">Desarrolladores Activos</div>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.15); color: #10B981;">
                <i class="fa-solid fa-laptop-code fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1">
                <div class="stat-value text-info"><?= (int)($stats['leads_admin'] ?? 0) ?></div>
                <div class="stat-label text-truncate" title="Leads Técnicos & QA">Leads Técnicos & QA</div>
            </div>
            <div class="stat-icon" style="background: rgba(14, 165, 233, 0.15); color: #0EA5E9;">
                <i class="fa-solid fa-user-shield fs-5"></i>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="min-w-0 flex-grow-1">
                <div class="stat-value text-warning"><?= (int)($stats['available'] ?? 0) ?></div>
                <div class="stat-label text-truncate" title="Disponibles para Asignar">Disponibles p/ Asignar</div>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.15); color: #F59E0B;">
                <i class="fa-solid fa-briefcase fs-5"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda y Segmentación -->
<div class="card-custom p-3 mb-4">
    <form action="<?= url('/team') ?>" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="position-relative">
                <input type="text" name="q" value="<?= e($search) ?>" class="form-control form-control-custom w-100 ps-4" placeholder="Buscar por nombre, correo o especialidad...">
                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size: 0.85rem;"></i>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <select name="sede" class="form-select form-select-custom w-100">
                <option value="">Todas las Sedes</option>
                <option value="Cercado" <?= $sede === 'Cercado' ? 'selected' : '' ?>>Cercado</option>
                <option value="Lima Norte" <?= $sede === 'Lima Norte' ? 'selected' : '' ?>>Lima Norte</option>
                <option value="Villa" <?= $sede === 'Villa' ? 'selected' : '' ?>>Villa</option>
            </select>
        </div>
        <div class="col-md-3 col-6">
            <select name="role" class="form-select form-select-custom w-100">
                <option value="">Todos los Roles</option>
                <option value="developer" <?= $role === 'developer' ? 'selected' : '' ?>>Desarrolladores</option>
                <option value="lider" <?= $role === 'lider' ? 'selected' : '' ?>>Líderes Técnicos / PM</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Administradores / QA</option>
            </select>
        </div>
        <?php if ($isAdmin): ?>
            <div class="col-md-2 col-8">
                <select name="status" class="form-select form-select-custom w-100">
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Solo Activos</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Solo Inactivos (Bajas)</option>
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Todos los Estados</option>
                </select>
            </div>
        <?php endif; ?>
        <div class="<?= $isAdmin ? 'col-md-1 col-4' : 'col-md-3 col-12' ?> text-end">
            <button type="submit" class="btn btn-ghost w-100 py-2 d-flex align-items-center justify-content-center gap-1" title="Aplicar Filtros">
                <i class="fa-solid fa-filter" aria-hidden="true"></i>
                <span class="d-md-none">Filtrar</span>
            </button>
        </div>
    </form>
</div>

<!-- Grid de Miembros y Desarrolladores -->
<div class="row g-3" id="teamMembersGrid">
    <?php foreach ($members as $m): ?>
        <?php
        $avatarUrl = !empty($m['avatar'])
            ? asset($m['avatar'])
            : "https://ui-avatars.com/api/?name=" . urlencode($m['name']) . "&background=10b981&color=ffffff&bold=true";
        $isActive = (int)($m['active'] ?? 1) === 1 && empty($m['deleted_at']);
        $roleClass = match ($m['role']) {
            'admin' => 'bg-danger',
            'lider' => 'bg-primary',
            default => 'bg-emerald',
        };
        $roleTitle = match ($m['role']) {
            'admin' => 'Administrador / QA',
            'lider' => 'Líder Técnico / PM',
            default => 'Desarrollador',
        };
        ?>
        <div class="col-md-6 col-xl-4 team-member-card-wrapper" data-member-id="<?= $m['id'] ?>">
            <div class="card-custom p-3 h-100 hover-lift d-flex flex-column justify-content-between position-relative <?= !$isActive ? 'opacity-75 bg-light-subtle' : '' ?>">
                <div>
                    <!-- Header Card: Avatar + Información Principal + Estado -->
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="position-relative flex-shrink-0">
                                <img src="<?= e($avatarUrl) ?>" alt="<?= e($m['name']) ?>" class="rounded-circle border" width="48" height="48" style="object-fit: cover;" loading="lazy">
                                <span class="position-absolute bottom-0 end-0 p-1 rounded-circle border border-white" style="background-color: <?= $isActive ? '#10B981' : '#94A3B8' ?>;" title="<?= $isActive ? 'Activo en plataforma' : 'Inactivo / Dado de baja' ?>"></span>
                            </div>
                            <div class="min-w-0">
                                <h6 class="fw-bold mb-0 text-truncate" style="color: #1F2937;" title="<?= e($m['name']) ?>"><?= e($m['name']) ?></h6>
                                <span class="text-secondary small text-truncate d-block" title="<?= e($m['email']) ?>"><?= e($m['email']) ?></span>
                            </div>
                        </div>

                        <!-- Acciones Rápidas (Chat directo) -->
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <a href="<?= url('/chat?contact_id=' . $m['id']) ?>" class="btn btn-sm btn-ghost p-1 rounded-circle" title="Enviar Mensaje Directo" aria-label="Enviar Mensaje">
                                <i class="fa-regular fa-comment-dots text-primary" style="font-size: 0.95rem;"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Badges de Rol, Sede y Estado -->
                    <div class="d-flex flex-wrap align-items-center gap-2 my-2">
                        <!-- Badge de Rol -->
                        <span class="badge <?= $roleClass ?> p-1 px-2 role-badge-el" style="font-size: 0.70rem;" id="badgeRole-<?= $m['id'] ?>">
                            <?= e($roleTitle) ?>
                        </span>

                        <!-- Badge de Sede -->
                        <?php if (!empty($m['sede'])): ?>
                            <span class="badge-sede"><i class="fa-solid fa-location-dot me-1"></i><?= e($m['sede']) ?></span>
                        <?php endif; ?>

                        <!-- Badge de Inactivo si aplica -->
                        <?php if (!$isActive): ?>
                            <span class="badge bg-secondary p-1 px-2" style="font-size: 0.65rem;">
                                <i class="fa-solid fa-ban me-1"></i>Inactivo
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Headline / Especialidad Técnica -->
                    <p class="text-secondary small mb-3 text-truncate-2" style="font-size: 0.82rem; min-height: 2.4rem;">
                        <?= e($m['headline'] ?: 'Consultor y desarrollador de software Devioz.') ?>
                    </p>
                </div>

                <!-- Footer: Proyecto Activo + Herramientas Administrativas -->
                <div class="pt-2 border-top" style="border-color: var(--border-color) !important;">
                    <!-- Proyecto Activo / Estado -->
                    <div class="mb-2">
                        <?php if (!empty($m['project_id'])): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="overflow-hidden me-2">
                                    <div class="text-muted text-uppercase" style="font-size: 0.65rem; font-weight: 600;">Proyecto Activo</div>
                                    <div class="small fw-semibold text-truncate" title="<?= e($m['project_name']) ?>">
                                        <span class="badge bg-primary-subtle text-primary font-monospace me-1"><?= e($m['project_code']) ?></span>
                                        <?= e($m['project_name']) ?>
                                    </div>
                                </div>
                                <a href="<?= url("/projects/{$m['project_id']}") ?>" class="btn btn-ghost btn-sm py-1 px-2 text-nowrap" title="Ver Proyecto">Ver</a>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small fst-italic" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i>Disponible para asignación
                                </span>
                                <?php if ($isAdmin && $m['role'] === 'developer' && $isActive): ?>
                                    <a href="<?= url("/projects/create?dev={$m['id']}") ?>" class="btn btn-brand btn-sm py-1 px-2" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-plus me-1"></i>Asignar
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Barra de Gestión de Administrador (CRUD de Programadores & Asignación de Roles) -->
                    <?php if ($isAdmin): ?>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-1" style="border-color: rgba(0,0,0,0.06) !important;">
                            <!-- Dropdown para Cambio Rápido de Rol -->
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm py-1 px-2 dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.72rem;" title="Cambiar Rol">
                                    <i class="fa-solid fa-user-tag text-secondary"></i>
                                    <span>Rol</span>
                                </button>
                                <ul class="dropdown-menu shadow-sm" style="font-size: 0.78rem;">
                                    <li><h6 class="dropdown-header">Asignar Rol a <?= e(explode(' ', $m['name'])[0]) ?></h6></li>
                                    <li><button type="button" class="dropdown-item btn-change-role <?= $m['role'] === 'developer' ? 'active' : '' ?>" data-id="<?= $m['id'] ?>" data-role="developer"><i class="fa-solid fa-laptop-code text-emerald me-2"></i> Desarrollador</button></li>
                                    <li><button type="button" class="dropdown-item btn-change-role <?= $m['role'] === 'lider' ? 'active' : '' ?>" data-id="<?= $m['id'] ?>" data-role="lider"><i class="fa-solid fa-user-tie text-primary me-2"></i> Líder Técnico / PM</button></li>
                                    <li><button type="button" class="dropdown-item btn-change-role <?= $m['role'] === 'admin' ? 'active' : '' ?>" data-id="<?= $m['id'] ?>" data-role="admin"><i class="fa-solid fa-user-shield text-danger me-2"></i> Administrador / QA</button></li>
                                </ul>
                            </div>

                            <div class="d-flex align-items-center gap-1">
                                <!-- Botón Modificar / Editar -->
                                <button type="button" class="btn btn-ghost btn-sm py-1 px-2 btn-open-edit-modal" data-member-id="<?= $m['id'] ?>" title="Modificar perfil de programador" style="font-size: 0.72rem;">
                                    <i class="fa-solid fa-pen-to-square text-info me-1"></i> Editar
                                </button>

                                <!-- Botón Eliminar / Dar de Baja / Reactivar -->
                                <?php if ($isActive): ?>
                                    <button type="button" class="btn btn-ghost btn-sm py-1 px-2 text-danger btn-open-delete-modal" data-member-id="<?= $m['id'] ?>" data-member-name="<?= e($m['name']) ?>" title="Dar de baja / Desactivar" style="font-size: 0.72rem;">
                                        <i class="fa-solid fa-user-xmark"></i>
                                    </button>
                                <?php else: ?>
                                    <form action="<?= url("/team/{$m['id']}/restore") ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-ghost btn-sm py-1 px-2 text-success" title="Reactivar usuario" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-rotate-left me-1"></i> Reactivar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($members)): ?>
        <div class="col-12">
            <div class="card-custom p-5 text-center">
                <i class="fa-solid fa-users-slash fs-1 text-secondary opacity-50 mb-3 d-block"></i>
                <h5 class="fw-bold text-dark">No se encontraron miembros</h5>
                <p class="text-secondary small mb-3">No hay registros que coincidan con los criterios de búsqueda aplicados.</p>
                <a href="<?= url('/team') ?>" class="btn btn-ghost btn-sm">Limpiar Filtros</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODALES ADMINISTRATIVOS (CRUD DE PROGRAMADORES & ASIGNACIÓN DE ROLES)    -->
<!-- ========================================================================= -->
<?php if ($isAdmin): ?>
    <!-- 1. MODAL: REGISTRAR NUEVO PROGRAMADOR -->
    <div class="modal fade" id="modalCreateMember" tabindex="-1" aria-labelledby="modalCreateMemberTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalCreateMemberTitle">
                        <span class="p-2 rounded-circle bg-emerald-subtle text-emerald">
                            <i class="fa-solid fa-user-plus"></i>
                        </span>
                        <span>Registrar Nuevo Programador / Consultor</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="<?= url('/team') ?>" method="POST" enctype="multipart/form-data" id="formCreateMember">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Nombre Completo -->
                            <div class="col-md-6">
                                <label for="createName" class="form-label-custom">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="createName" class="form-control form-control-custom w-100" placeholder="Ej: Carlos Alberto Mendoza" required minlength="3">
                            </div>

                            <!-- Correo Corporativo -->
                            <div class="col-md-6">
                                <label for="createEmail" class="form-label-custom">Correo Electrónico Corporativo <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="createEmail" class="form-control form-control-custom w-100" placeholder="carlos.mendoza@devioz.com" required>
                            </div>

                            <!-- Rol en Devioz -->
                            <div class="col-md-6">
                                <label for="createRole" class="form-label-custom">Rol Técnico / Responsabilidad <span class="text-danger">*</span></label>
                                <select name="role" id="createRole" class="form-select form-select-custom w-100" required>
                                    <option value="developer" selected>💻 Desarrollador / Consultor de Software</option>
                                    <option value="lider">👔 Líder Técnico / Scrum Master / PM</option>
                                    <option value="admin">🛡️ Lead Técnico & Auditor QA (Admin)</option>
                                </select>
                            </div>

                            <!-- Sede Académica / Corporativa -->
                            <div class="col-md-6">
                                <label for="createSede" class="form-label-custom">Sede Asignada <span class="text-danger">*</span></label>
                                <select name="sede" id="createSede" class="form-select form-select-custom w-100" required>
                                    <option value="Cercado" selected>Cercado</option>
                                    <option value="Lima Norte">Lima Norte</option>
                                    <option value="Villa">Villa</option>
                                </select>
                            </div>

                            <!-- Contraseña Inicial -->
                            <div class="col-md-6">
                                <label for="createPassword" class="form-label-custom">Contraseña Inicial <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" name="password" id="createPassword" class="form-control form-control-custom w-100" placeholder="Mínimo 6 caracteres" required minlength="6">
                                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted toggle-password-visibility" data-target="createPassword">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Foto de Perfil / Avatar -->
                            <div class="col-md-6">
                                <label for="createAvatar" class="form-label-custom">Foto de Perfil / Avatar (Opcional)</label>
                                <input type="file" name="avatar" id="createAvatar" class="form-control form-control-custom w-100" accept="image/jpeg,image/png,image/webp">
                            </div>

                            <!-- Headline / Especialidad Técnica -->
                            <div class="col-12">
                                <label for="createHeadline" class="form-label-custom">Especialidad Técnica / Headline</label>
                                <input type="text" name="headline" id="createHeadline" class="form-control form-control-custom w-100" placeholder="Ej: Full Stack Developer & Cloud Engineer (PHP, Vue, Docker)">
                            </div>

                            <!-- Biografía / Resumen de Habilidades -->
                            <div class="col-12">
                                <label for="createBio" class="form-label-custom">Perfil Profesional y Competencias</label>
                                <textarea name="bio" id="createBio" rows="3" class="form-control form-control-custom w-100" placeholder="Describe brevemente su trayectoria técnica, certificaciones y tecnologías dominadas..."></textarea>
                            </div>
                        </div>

                        <div id="createMemberFeedback" class="small mt-2" role="status" aria-live="polite"></div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4 bg-light-subtle rounded-bottom-4">
                        <button type="button" class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-brand btn-sm d-flex align-items-center gap-2" id="btnSubmitCreateMember">
                            <i class="fa-solid fa-check"></i>
                            <span>Registrar Programador</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. MODAL: MODIFICAR / EDITAR PROGRAMADOR Y ASIGNAR ROL -->
    <div class="modal fade" id="modalEditMember" tabindex="-1" aria-labelledby="modalEditMemberTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalEditMemberTitle">
                        <span class="p-2 rounded-circle bg-info-subtle text-info">
                            <i class="fa-solid fa-user-pen"></i>
                        </span>
                        <span>Modificar Datos de Consultor / Programador</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data" id="formEditMember">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- Nombre Completo -->
                            <div class="col-md-6">
                                <label for="editName" class="form-label-custom">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="editName" class="form-control form-control-custom w-100" required minlength="3">
                            </div>

                            <!-- Correo Corporativo -->
                            <div class="col-md-6">
                                <label for="editEmail" class="form-label-custom">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="editEmail" class="form-control form-control-custom w-100" required>
                            </div>

                            <!-- Rol en Devioz -->
                            <div class="col-md-6">
                                <label for="editRole" class="form-label-custom">Asignar Rol <span class="text-danger">*</span></label>
                                <select name="role" id="editRole" class="form-select form-select-custom w-100" required>
                                    <option value="developer">💻 Desarrollador / Consultor de Software</option>
                                    <option value="lider">👔 Líder Técnico / Scrum Master / PM</option>
                                    <option value="admin">🛡️ Lead Técnico & Auditor QA (Admin)</option>
                                </select>
                            </div>

                            <!-- Sede Académica -->
                            <div class="col-md-6">
                                <label for="editSede" class="form-label-custom">Sede Asignada <span class="text-danger">*</span></label>
                                <select name="sede" id="editSede" class="form-select form-select-custom w-100" required>
                                    <option value="Cercado">Cercado</option>
                                    <option value="Lima Norte">Lima Norte</option>
                                    <option value="Villa">Villa</option>
                                </select>
                            </div>

                            <!-- Restablecer Contraseña (Opcional) -->
                            <div class="col-md-6">
                                <label for="editPassword" class="form-label-custom">Nueva Contraseña (Opcional)</label>
                                <div class="position-relative">
                                    <input type="password" name="password" id="editPassword" class="form-control form-control-custom w-100" placeholder="Dejar en blanco para no modificar">
                                    <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-2 text-muted toggle-password-visibility" data-target="editPassword">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Estado Activo / Inactivo -->
                            <div class="col-md-6">
                                <label for="editActive" class="form-label-custom">Estado de la Cuenta</label>
                                <select name="active" id="editActive" class="form-select form-select-custom w-100">
                                    <option value="1">🟢 Activo en plataforma</option>
                                    <option value="0">🔴 Inactivo / Suspendido</option>
                                </select>
                            </div>

                            <!-- Cambiar Foto de Perfil -->
                            <div class="col-12">
                                <label for="editAvatar" class="form-label-custom">Actualizar Avatar / Foto de Perfil</label>
                                <input type="file" name="avatar" id="editAvatar" class="form-control form-control-custom w-100" accept="image/jpeg,image/png,image/webp">
                            </div>

                            <!-- Headline / Especialidad Técnica -->
                            <div class="col-12">
                                <label for="editHeadline" class="form-label-custom">Especialidad Técnica / Headline</label>
                                <input type="text" name="headline" id="editHeadline" class="form-control form-control-custom w-100">
                            </div>

                            <!-- Biografía -->
                            <div class="col-12">
                                <label for="editBio" class="form-label-custom">Perfil Profesional y Competencias</label>
                                <textarea name="bio" id="editBio" rows="3" class="form-control form-control-custom w-100"></textarea>
                            </div>
                        </div>

                        <div id="editMemberFeedback" class="small mt-2" role="status" aria-live="polite"></div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4 bg-light-subtle rounded-bottom-4">
                        <button type="button" class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-brand btn-sm d-flex align-items-center gap-2" id="btnSubmitEditMember">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. MODAL: CONFIRMAR BAJA / DESACTIVACIÓN -->
    <div class="modal fade" id="modalDeleteMember" tabindex="-1" aria-labelledby="modalDeleteMemberTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold text-danger d-flex align-items-center gap-2" id="modalDeleteMemberTitle">
                        <span class="p-2 rounded-circle bg-danger-subtle text-danger">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                        <span>Confirmar Baja del Miembro</span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="" method="POST" id="formDeleteMember">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <p class="text-dark mb-2">
                            ¿Estás seguro de que deseas dar de baja a <strong id="deleteMemberName" class="text-danger">este profesional</strong>?
                        </p>
                        <div class="alert alert-warning small mb-0 d-flex gap-2 align-items-start">
                            <i class="fa-solid fa-shield-halved mt-1 flex-shrink-0"></i>
                            <div>
                                El usuario no podrá iniciar sesión en la plataforma. Sus proyectos, entregas, auditorías QA y mensajes históricos permanecerán íntegros y protegidos en la base de datos.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-2 px-4 bg-light-subtle rounded-bottom-4">
                        <button type="button" class="btn btn-ghost btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm d-flex align-items-center gap-2">
                            <i class="fa-solid fa-user-xmark"></i>
                            <span>Dar de Baja</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script de Inicialización y Eventos del CRUD -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const editModalEl = document.getElementById('modalEditMember');
            const deleteModalEl = document.getElementById('modalDeleteMember');
            const formEdit = document.getElementById('formEditMember');
            const formDelete = document.getElementById('formDeleteMember');
            const deleteMemberNameEl = document.getElementById('deleteMemberName');

            // 1. Mostrar/Ocultar contraseñas
            document.querySelectorAll('.toggle-password-visibility').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    btn.innerHTML = isPassword ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
                });
            });

            // 2. Abrir Modal de Edición con datos pre-cargados
            document.querySelectorAll('.btn-open-edit-modal').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const memberId = btn.getAttribute('data-member-id');
                    if (!memberId) return;

                    formEdit.action = `<?= url('/team') ?>/${memberId}/update`;

                    try {
                        const response = await fetch(`<?= url('/api/team') ?>/${memberId}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await response.json();

                        if (data.success && data.member) {
                            const m = data.member;
                            document.getElementById('editName').value = m.name || '';
                            document.getElementById('editEmail').value = m.email || '';
                            document.getElementById('editRole').value = m.role || 'developer';
                            document.getElementById('editSede').value = m.sede || 'Cercado';
                            document.getElementById('editHeadline').value = m.headline || '';
                            document.getElementById('editBio').value = m.bio || '';
                            document.getElementById('editActive').value = m.active !== undefined ? m.active : 1;
                            document.getElementById('editPassword').value = '';

                            const modal = bootstrap.Modal.getOrCreateInstance(editModalEl);
                            modal.show();
                        } else {
                            alert(data.message || 'No se pudo cargar la información del miembro.');
                        }
                    } catch (err) {
                        console.error('Error cargando miembro:', err);
                        alert('Error de conexión al obtener datos del miembro.');
                    }
                });
            });

            // 3. Abrir Modal de Confirmación de Baja
            document.querySelectorAll('.btn-open-delete-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    const memberId = btn.getAttribute('data-member-id');
                    const memberName = btn.getAttribute('data-member-name');
                    if (!memberId) return;

                    formDelete.action = `<?= url('/team') ?>/${memberId}/delete`;
                    if (deleteMemberNameEl) deleteMemberNameEl.textContent = memberName || 'este miembro';

                    const modal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
                    modal.show();
                });
            });

            // 4. Asignación Rápida de Rol vía Dropdown
            document.querySelectorAll('.btn-change-role').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const memberId = btn.getAttribute('data-id');
                    const newRole = btn.getAttribute('data-role');
                    if (!memberId || !newRole) return;

                    const formData = new FormData();
                    formData.append('role', newRole);

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const response = await fetch(`<?= url('/team') ?>/${memberId}/role`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        const data = await response.json();

                        if (data.success) {
                            // Actualizar badge visual en la tarjeta
                            const badge = document.getElementById(`badgeRole-${memberId}`);
                            if (badge) {
                                badge.className = `badge ${data.badge_class} p-1 px-2 role-badge-el`;
                                badge.textContent = data.role_label;
                            }

                            // Marcar opción activa en el dropdown de esa tarjeta
                            const parentMenu = btn.closest('.dropdown-menu');
                            if (parentMenu) {
                                parentMenu.querySelectorAll('.btn-change-role').forEach(b => b.classList.remove('active'));
                                btn.classList.add('active');
                            }

                            if (typeof window.showToast === 'function') {
                                window.showToast(data.message, 'success');
                            }
                        } else {
                            alert(data.message || 'No se pudo actualizar el rol.');
                        }
                    } catch (err) {
                        console.error('Error al cambiar rol:', err);
                        alert('Error de conexión al modificar el rol.');
                    }
                });
            });
        });
    </script>
<?php endif; ?>
