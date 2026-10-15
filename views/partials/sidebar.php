<?php

declare(strict_types=1);

$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
$currentUser = auth()->user();
$isAdmin = auth()->isAdmin();
$isDeveloper = auth()->isDeveloper();

$isActive = function(string $route) use ($currentUri): string {
    return str_contains($currentUri, $route) ? 'active' : '';
};
$isDashboardActive = str_contains($currentUri, '/dashboard') || $currentUri === '/' || str_ends_with(rtrim($currentUri, '/'), '/public');
$isGestionActive = str_contains($currentUri, '/admin') || str_contains($currentUri, '/projects');
$isComunidadActive = str_contains($currentUri, '/reels') || str_contains($currentUri, '/lounge') || str_contains($currentUri, '/gallery') || str_contains($currentUri, '/team');
?>

<aside class="sidebar" id="mainSidebar">
    <!-- Header de la Barra Lateral (Estilo ChatGPT: Logo <-> Botón de Despliegue en Hover) -->
    <div class="sidebar-header" id="sidebarHeader">
        <a href="<?= url('/dashboard') ?>" class="sidebar-brand text-decoration-none p-0 d-flex align-items-center" title="Devioz OS">
            <img src="<?= asset('images/devioz-logo.webp') ?>" alt="Devioz OS" class="sidebar-logo-full">
            <img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz OS" class="sidebar-logo-icon">
        </a>
        <button type="button" class="sidebar-collapse-btn d-none d-lg-flex" id="sidebarCollapseBtn" title="Cerrar barra lateral (Ctrl+B)" aria-label="Cerrar barra lateral">
            <i class="fa-solid fa-chevron-left"></i>
            <span class="sidebar-toggle-tooltip" id="sidebarToggleTooltip">Cerrar barra lateral</span>
        </button>
    </div>

    <!-- Menú de Navegación Encapsulado (Compacto y Elegante) -->
    <!-- Menú de Navegación Encapsulado (Compacto y Elegante) -->
    <div class="sidebar-menu" id="sidebarMenuAccordion">
        <div class="sidebar-category">Colaboración</div>
        <a href="<?= url('/dashboard') ?>" class="nav-link-custom <?= $isDashboardActive ? 'active' : '' ?>" title="Feed / Flujo Principal">
            <span class="icon-wrapper">
                <i class="fa-regular fa-newspaper" style="font-size: 1rem;"></i>
            </span>
            <span>Feed</span>
        </a>

        <!-- Chat / Messenger Bitrix Suite Trigger -->
        <a href="<?= url('/chat') ?>" class="nav-link-custom <?= (str_contains($currentUri, '/chat') && (($_GET['channel'] ?? '') !== 'copilot')) ? 'active' : '' ?>" id="sidebarBitrixChatTrigger" title="Messenger Corporativo">
            <span class="icon-wrapper text-info">
                <i class="fa-regular fa-comment-dots" style="font-size: 1rem;"></i>
            </span>
            <span>Messenger</span>
        </a>

        <!-- CoPilot AI Trigger Encapsulado en Bitrix24 Messenger -->
        <a href="<?= url('/chat?channel=copilot') ?>" class="nav-link-custom <?= (str_contains($currentUri, '/chat') && (($_GET['channel'] ?? '') === 'copilot')) ? 'active' : '' ?>" id="sidebarBitrixCopilotTrigger" title="CoPilot LABVA AI">
            <span class="icon-wrapper" style="color: #A855F7;">
                <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 0.95rem;"></i>
            </span>
            <span>CoPilot</span>
        </a>

        <a href="<?= url('/lounge') ?>" class="nav-link-custom <?= $isActive('/lounge') ?>" title="Dev Lounge">
            <span class="icon-wrapper text-emerald">
                <i class="fa-solid fa-mug-hot" style="font-size: 0.95rem;"></i>
            </span>
            <span>Dev Lounge</span>
        </a>

        <?php if ($isDeveloper): ?>
            <a href="<?= url('/my-project') ?>" class="nav-link-custom <?= $isActive('/my-project') ?>" title="Mi Proyecto Activo">
                <span class="icon-wrapper text-primary">
                    <i class="fa-solid fa-briefcase" style="font-size: 0.95rem;"></i>
                </span>
                <span>Mi Proyecto</span>
            </a>
        <?php endif; ?>

        <!-- Módulo Proyectos & SDLC -->
        <div class="sidebar-category mt-2">Proyectos & SDLC</div>
        <a href="<?= url('/projects') ?>" class="nav-link-custom <?= ($currentUri === '/projects' || (str_contains($currentUri, '/projects') && !str_contains($currentUri, '/admin') && !str_contains($currentUri, '/create'))) ? 'active' : '' ?>" title="Directorio de Proyectos">
            <span class="icon-wrapper text-primary">
                <i class="fa-solid fa-diagram-project" style="font-size: 0.95rem;"></i>
            </span>
            <span>Proyectos</span>
        </a>

        <?php if ($isAdmin): ?>
            <a href="<?= url('/admin/review') ?>" class="nav-link-custom <?= $isActive('/admin/review') ?>" title="Tablero de Revisión Kanban">
                <span class="icon-wrapper text-warning">
                    <i class="fa-solid fa-table-columns" style="font-size: 0.95rem;"></i>
                </span>
                <span class="flex-grow-1">Tableros</span>
                <span class="badge bg-secondary-subtle text-secondary gl-sidebar-badge" style="font-size: 0.62rem; padding: 2px 6px;">KANBAN</span>
            </a>

            <a href="<?= url('/admin/assignments') ?>" class="nav-link-custom <?= $isActive('/admin/assignments') ?>" title="Asignaciones">
                <span class="icon-wrapper text-info">
                    <i class="fa-solid fa-users-gear" style="font-size: 0.95rem;"></i>
                </span>
                <span>Asignaciones</span>
            </a>

            <a href="<?= url('/admin/evaluations') ?>" class="nav-link-custom <?= $isActive('/admin/evaluations') ?>" title="Auditorías y Dictámenes QA">
                <span class="icon-wrapper text-success">
                    <i class="fa-solid fa-certificate" style="font-size: 0.95rem;"></i>
                </span>
                <span>Auditorías & QA</span>
            </a>
        <?php endif; ?>

        <!-- Módulo Red & Multimedia -->
        <div class="sidebar-category mt-2">Red & Recursos</div>
        <a href="<?= url('/reels') ?>" class="nav-link-custom <?= $isActive('/reels') ?>" title="Devioz Reels">
            <span class="icon-wrapper text-danger">
                <i class="fa-solid fa-play" style="font-size: 0.95rem;"></i>
            </span>
            <span>Reels</span>
        </a>

        <a href="<?= url('/gallery') ?>" class="nav-link-custom <?= $isActive('/gallery') ?>" title="Galería Pública">
            <span class="icon-wrapper" style="color: #EC4899;">
                <i class="fa-solid fa-images" style="font-size: 0.95rem;"></i>
            </span>
            <span>Galería</span>
        </a>

        <a href="<?= url('/team') ?>" class="nav-link-custom <?= $isActive('/team') ?>" title="Equipo Técnico">
            <span class="icon-wrapper text-secondary">
                <i class="fa-solid fa-id-badge" style="font-size: 0.95rem;"></i>
            </span>
            <span>Talento & Red</span>
        </a>

        <div class="sidebar-category mt-2">Sistema</div>
        <!-- Enlace Directo a Configuración de Cuenta -->
        <a href="<?= url('/profile') ?>" class="nav-link-custom <?= $isActive('/profile') ?>" title="Configuración de Cuenta y Perfil">
            <span class="icon-wrapper">
                <i class="fa-solid fa-gear" style="font-size: 1rem;"></i>
            </span>
            <span>Configuración</span>
        </a>
    </div>


    <!-- Footer de la Barra Lateral: Perfil de Usuario Clickable hacia Configuración -->
    <?php if ($currentUser): ?>
        <div class="sidebar-footer">
            <a href="<?= url('/profile') ?>" class="user-profile-badge text-decoration-none d-flex align-items-center gap-2 <?= $isActive('/profile') ? 'active-profile' : '' ?>" title="Configurar cuenta y perfil (<?= e($currentUser->name) ?>)">
                <img src="<?= e($currentUser->avatarUrl()) ?>" alt="<?= e($currentUser->name) ?>" class="user-avatar-img" loading="lazy">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold text-truncate small sidebar-user-name text-white" style="color: #FFFFFF !important; font-weight: 700 !important; font-size: 0.88rem !important; text-shadow: 0 1px 3px rgba(0,0,0,0.6);"><?= e($currentUser->name) ?></div>
                    <div class="d-flex align-items-center mt-1">
                        <span class="badge <?= $isAdmin ? 'bg-danger' : 'bg-emerald' ?> p-1" style="font-size: 0.65rem;">
                            <?= $isAdmin ? 'Administrador' : 'Desarrollador' ?>
                        </span>
                    </div>
                </div>
                <div class="sidebar-user-action p-1" title="Configurar perfil">
                    <i class="fa-solid fa-gear" style="font-size: 0.95rem; color: #CBD5E1;"></i>
                </div>
            </a>
        </div>
    <?php endif; ?>

</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
