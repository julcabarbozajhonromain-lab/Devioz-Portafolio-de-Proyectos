/**
 * DEVIOZ 24 — JavaScript Enterprise Suite v3.0 (Devioz UI/UX Architecture)
 * Video Lightbox, Reels (Shorts), Command Palette (Ctrl+K), LABVA AI, Modales & Kanban
 */

// Helper para resolución de URLs base (compatible con carpetas raíz y subdirectorios de XAMPP/cPanel)
const appBaseMeta = document.querySelector('meta[name="app-url"]')?.getAttribute('content') || '';
function appUrl(path) {
    if (!path) return '';
    if (path.startsWith('http://') || path.startsWith('https://')) return path;
    const cleanBase = appBaseMeta.replace(/\/+$/, '');
    const cleanPath = path.replace(/^\/+/, '');
    return cleanBase ? `${cleanBase}/${cleanPath}` : `/${cleanPath}`;
}

// Interceptor HTTP Global para peticiones Fetch (Captura de errores 401 - Sesión Expirada)
const _originalFetch = window.fetch;
window.fetch = async function(...args) {
    try {
        const response = await _originalFetch.apply(this, args);
        if (response.status === 401) {
            if (!window.location.pathname.includes('/login')) {
                window.location.href = appUrl('/login?session_expired=1');
            }
        }
        return response;
    } catch (error) {
        throw error;
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const initializers = [
        ['Theme', initTheme],
        ['WallpaperCustomizer', initWallpaperCustomizer],
        ['ResponsiveTopbarNav', initResponsiveTopbarNav],
        ['Sidebar', initSidebar],
        ['SidebarCollapse', initSidebarCollapse],
        ['FlashAutoDismiss', initFlashAutoDismiss],
        ['KanbanSortable', initKanbanSortable],
        ['FileUploadPreviews', initFileUploadPreviews],
        ['ConfirmDialogs', initConfirmDialogs],
        ['CopyButtons', initCopyButtons],
        ['StatsChart', initStatsChart],
        ['VideoLightbox', initVideoLightbox],
        ['CommandPalette', initCommandPalette],
        ['DeviozBot', initDeviozBot],
        ['ReelsInteractions', initReelsInteractions],
        ['LoungeInteractive', initLoungeInteractive],
        ['DeviozSuite', initDeviozSuite],
        ['ReactiveLiveSearch', initReactiveLiveSearch],
        ['LinkPreviewListeners', initLinkPreviewListeners],
        ['SidePanel', initSidePanel],
        ['DeviozMessenger', initDeviozMessenger],
        ['CheckCelebration', checkForCelebration]
    ];

    initializers.forEach(([name, fn]) => {
        try {
            if (typeof fn === 'function') fn();
        } catch (e) {
            console.warn(`[Devioz 24] Error inicializando módulo ${name}:`, e);
        }
    });
});

/**
 * 1. Control del Tema Corporativo (Devioz Light por defecto)
 */
function initTheme() {
    const htmlElement = document.documentElement;
    htmlElement.setAttribute('data-theme', 'light');
}

function initWallpaperCustomizer() {
    const themeModalEl = document.getElementById('deviozThemeModal');
    const allThemeKeys = [
        'vibecode', 'meadow', 'saturn', 'halloween', 'winter',
        'nebula', 'jellyfish', 'earth', 'spring', 'meadow-dew',
        'ocean-silk', 'devioz-blue', 'autumn', 'cyber', 'aurora', 'light'
    ];

    let currentTheme = localStorage.getItem('devioz_visual_theme') || 'vibecode';
    let themeBeforeModal = currentTheme;

    function applyTheme(themeKey, persist = false) {
        if (!allThemeKeys.includes(themeKey)) {
            themeKey = 'vibecode';
        }

        allThemeKeys.forEach(t => {
            document.body.classList.remove(`theme-${t}`);
            document.documentElement.classList.remove(`theme-${t}`);
        });

        document.body.style.backgroundImage = '';
        document.body.style.backgroundAttachment = '';
        document.body.style.backgroundSize = '';
        document.body.style.backgroundPosition = '';
        document.body.classList.add(`theme-${themeKey}`);
        document.documentElement.classList.add(`theme-${themeKey}`);

        currentTheme = themeKey;
        if (persist) {
            localStorage.setItem('devioz_visual_theme', themeKey);
        }

        // Sincronizar estado visual de tarjetas (tanto en modal como en pestañas de perfil)
        document.querySelectorAll('.devioz-theme-card, .theme-preview-card').forEach(c => {
            if (c.getAttribute('data-theme-key') === themeKey) {
                c.classList.add('active');
            } else {
                c.classList.remove('active');
            }
        });
    }

    // Aplicar tema inicial persistido
    applyTheme(currentTheme, true);

    // Manejador global para cualquier disparador del modal de temas (Perfil, Dropdown, Menú Más)
    const themeTriggers = document.querySelectorAll('[data-theme-modal-trigger], #popoverThemeToggle');
    themeTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            themeBeforeModal = localStorage.getItem('devioz_visual_theme') || 'vibecode';

            // Cerrar cualquier dropdown de Bootstrap abierto para evitar colisiones de backdrop
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                const toggle = menu.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle && window.bootstrap?.Dropdown) {
                    const inst = bootstrap.Dropdown.getInstance(toggle);
                    if (inst) inst.hide();
                } else {
                    menu.classList.remove('show');
                }
                const parentDropdown = menu.closest('.dropdown');
                if (parentDropdown) parentDropdown.classList.remove('show');
            });
            const moreBtn = document.getElementById('topbarMoreNavBtn');
            if (moreBtn) moreBtn.setAttribute('aria-expanded', 'false');

            if (themeModalEl && window.bootstrap?.Modal) {
                const modal = bootstrap.Modal.getOrCreateInstance(themeModalEl);
                modal.show();
            }
        });
    });

    // Delegación de clic en tarjetas de temas (Modal y página de ajustes)
    document.addEventListener('click', (e) => {
        const card = e.target.closest('.devioz-theme-card, .theme-preview-card');
        if (card) {
            const themeKey = card.getAttribute('data-theme-key');
            if (themeKey) {
                applyTheme(themeKey, true);
            }
        }
    });

    // Botón GUARDAR estilo Devioz
    const saveBtn = document.getElementById('deviozSaveThemeBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', () => {
            localStorage.setItem('devioz_visual_theme', currentTheme);
            themeBeforeModal = currentTheme;
            if (themeModalEl && window.bootstrap?.Modal) {
                const modal = bootstrap.Modal.getInstance(themeModalEl);
                if (modal) modal.hide();
            }
            if (typeof window.showToast === 'function') {
                window.showToast('Tema visual guardado exitosamente', 'success');
            }
        });
    }

    // Botón CANCELAR estilo Devioz
    if (themeModalEl) {
        themeModalEl.addEventListener('hidden.bs.modal', () => {
            // Si el usuario cerró sin guardar cambios explícitos, asegurar que el tema guardado permanezca
            const confirmedTheme = localStorage.getItem('devioz_visual_theme') || 'vibecode';
            applyTheme(confirmedTheme, true);
        });
    }

    // Botón TEMA PERSONALIZADO estilo Devioz
    const customThemeBtn = document.getElementById('deviozCustomThemeBtn');
    const customWallpaperInput = document.getElementById('deviozCustomWallpaperInput');
    if (customThemeBtn && customWallpaperInput) {
        customThemeBtn.addEventListener('click', () => {
            customWallpaperInput.click();
        });

        customWallpaperInput.addEventListener('change', (e) => {
            const file = e.target.files?.[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('Por favor selecciona un archivo de imagen válido (JPG, PNG o WEBP).');
                    return;
                }
                if (file.size > 8 * 1024 * 1024) {
                    alert('La imagen excede el límite máximo permitido de 8MB.');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (event) => {
                    const base64Img = event.target.result;
                    localStorage.setItem('devioz_custom_wallpaper_data', base64Img);
                    applyTheme('custom', true);
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

/**
 * 1.1 Navegación Dinámica Responsive Devioz (Colapso Inteligente en "Más ▾")
 * Al hacer zoom o en resoluciones menores, consume los elementos que no entran hacia "Más ▾".
 * Al reducir el zoom o agrandar la pantalla, los suelta de regreso inmediatamente.
 * Además, garantiza apertura y cierre 100% confiable del menú desplegable "Más ▾".
 */
function initResponsiveTopbarNav() {
    const navContainer = document.getElementById('topbarHorizontalNav');
    const itemsWrapper = document.getElementById('topbarVisibleNavItems');
    const moreDropdown = document.getElementById('topbarMoreNavDropdown');
    const moreDropdownMenu = document.getElementById('topbarMoreDropdownMenu');
    const dynamicItemsContainer = document.getElementById('topbarMoreDynamicItems');
    const dynamicDivider = document.getElementById('topbarMoreDivider');
    const moreBtn = document.getElementById('topbarMoreNavBtn');
    const topbar = document.querySelector('.topbar');
    const leftArea = document.getElementById('topbarLeftArea');
    const rightArea = document.getElementById('topbarRightArea');

    if (!navContainer || !itemsWrapper || !moreDropdown || !moreDropdownMenu || !dynamicItemsContainer || !moreBtn) return;

    const originalItems = Array.from(itemsWrapper.querySelectorAll('.topbar-nav-pill-item'));
    if (!originalItems.length) return;

    // Almacenar metadatos y medir el ancho intrínseco natural de cada elemento
    const navData = originalItems.map(item => {
        const textSpan = item.querySelector('span');
        const label = textSpan ? textSpan.textContent.trim() : item.innerText.trim();
        // Medir ancho real inicial
        const rect = item.getBoundingClientRect();
        const measuredW = rect.width > 0 ? Math.ceil(rect.width) : Math.ceil(label.length * 8.5 + 34);
        return {
            element: item,
            id: item.getAttribute('data-nav-id') || '',
            label: item.getAttribute('data-nav-label') || label,
            icon: item.getAttribute('data-nav-icon') || 'fa-solid fa-circle',
            href: item.getAttribute('href') || '#',
            isActive: item.classList.contains('active'),
            naturalWidth: measuredW
        };
    });

    // Control de apertura y cierre del botón "Más ▾" (independiente y confiable)
    moreBtn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = moreDropdownMenu.classList.contains('show');

        // Cerrar otros dropdowns activos
        document.querySelectorAll('.dropdown-menu.show').forEach(m => {
            if (m !== moreDropdownMenu) {
                m.classList.remove('show');
                const p = m.closest('.dropdown');
                if (p) p.classList.remove('show');
            }
        });

        if (isOpen) {
            moreDropdownMenu.classList.remove('show');
            moreDropdown.classList.remove('show');
            moreBtn.setAttribute('aria-expanded', 'false');
        } else {
            moreDropdownMenu.classList.add('show');
            moreDropdown.classList.add('show');
            moreBtn.setAttribute('aria-expanded', 'true');
        }
    });

    // Cerrar al hacer clic fuera del menú
    document.addEventListener('click', (e) => {
        if (!moreDropdown.contains(e.target)) {
            moreDropdownMenu.classList.remove('show');
            moreDropdown.classList.remove('show');
            moreBtn.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar al hacer clic en cualquier enlace del menú
    moreDropdownMenu.addEventListener('click', (e) => {
        if (e.target.closest('a')) {
            moreDropdownMenu.classList.remove('show');
            moreDropdown.classList.remove('show');
            moreBtn.setAttribute('aria-expanded', 'false');
        }
    });

    function getAvailableNavWidth() {
        if (!topbar || !rightArea) return 0;
        const topbarW = topbar.getBoundingClientRect().width;
        const leftW = leftArea ? leftArea.getBoundingClientRect().width : 0;
        const rightW = rightArea.getBoundingClientRect().width;
        const moreW = moreBtn.getBoundingClientRect().width || 76;

        // Espacio físico disponible garantizado
        return Math.max(0, topbarW - leftW - rightW - moreW - 24);
    }

    let resizeDebounce = null;

    function recalculateNav() {
        // Asegurar que el botón Más esté disponible
        moreDropdown.style.display = 'inline-flex';

        const availableWidth = getAvailableNavWidth();
        if (availableWidth <= 0) return;

        let accumulatedWidth = 0;
        let overflowItems = [];
        let anyActiveOverflowed = false;

        navData.forEach(item => {
            const itemW = item.naturalWidth + 4; // Ancho natural + gap
            if (accumulatedWidth + itemW <= availableWidth) {
                accumulatedWidth += itemW;
                item.element.style.display = 'inline-flex';
            } else {
                item.element.style.display = 'none';
                overflowItems.push(item);
                if (item.isActive) {
                    anyActiveOverflowed = true;
                }
            }
        });

        // Actualizar los elementos dinámicos dentro de "Más ▾"
        dynamicItemsContainer.innerHTML = '';
        if (overflowItems.length > 0) {
            overflowItems.forEach(item => {
                const a = document.createElement('a');
                a.className = `devioz-more-item ${item.isActive ? 'active fw-bold' : ''}`;
                a.href = item.href;
                a.innerHTML = `<i class="${item.icon} text-primary me-2"></i><span>${item.label}</span>`;
                dynamicItemsContainer.appendChild(a);
            });
            if (dynamicDivider) dynamicDivider.classList.remove('d-none');
        } else {
            if (dynamicDivider) dynamicDivider.classList.add('d-none');
        }

        // Iluminar botón "Más" si contiene la página activa
        if (anyActiveOverflowed) {
            moreBtn.classList.add('active');
        } else {
            moreBtn.classList.remove('active');
        }
    }

    // Inicializaciones progresivas para absorber carga de tipografías
    requestAnimationFrame(recalculateNav);
    setTimeout(recalculateNav, 60);
    setTimeout(recalculateNav, 250);
    setTimeout(recalculateNav, 600);

    window.addEventListener('resize', () => {
        clearTimeout(resizeDebounce);
        resizeDebounce = setTimeout(recalculateNav, 30);
    });

    if (window.ResizeObserver && topbar) {
        const ro = new ResizeObserver(() => {
            clearTimeout(resizeDebounce);
            resizeDebounce = setTimeout(recalculateNav, 30);
        });
        ro.observe(topbar);
    }
}

/**
 * 2. Sidebar Colapsable y Responsive (Estilo ChatGPT)
 * Soporta alternancia fluida en escritorio y off-canvas en móviles, con atajo Ctrl + B
 */
function toggleDesktopSidebarCollapse() {
    const isNowCollapsed = !document.body.classList.contains('sidebar-collapsed');
    document.body.classList.toggle('sidebar-collapsed', isNowCollapsed);
    document.documentElement.classList.toggle('sidebar-collapsed', isNowCollapsed);
    localStorage.setItem('devioz_sidebar_collapsed', isNowCollapsed ? 'true' : 'false');
    updateSidebarCollapseUI(isNowCollapsed);
}

function updateSidebarCollapseUI(isCollapsed) {
    const tooltip = document.getElementById('sidebarToggleTooltip');
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const label = isCollapsed ? 'Abrir barra lateral (Ctrl+B)' : 'Cerrar barra lateral (Ctrl+B)';

    if (tooltip) {
        tooltip.textContent = isCollapsed ? 'Abrir barra lateral' : 'Cerrar barra lateral';
    }
    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.setAttribute('title', label);
        sidebarCollapseBtn.setAttribute('aria-label', label);
        const icon = sidebarCollapseBtn.querySelector('i');
        if (icon) {
            icon.className = isCollapsed ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
        }
    }
    if (sidebarToggleBtn) {
        sidebarToggleBtn.setAttribute('title', label);
        sidebarToggleBtn.setAttribute('aria-label', label);
    }
}

function initSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar) return;

    if (toggleBtn) {
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (window.innerWidth >= 992) {
                toggleDesktopSidebarCollapse();
            } else {
                sidebar.classList.toggle('mobile-open');
                if (overlay) overlay.classList.toggle('active');
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }
}

function initSidebarCollapse() {
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');

    // Sincronizar estado inicial desde localStorage
    const isCollapsed = localStorage.getItem('devioz_sidebar_collapsed') === 'true';
    if (isCollapsed && window.innerWidth >= 992) {
        document.body.classList.add('sidebar-collapsed');
        document.documentElement.classList.add('sidebar-collapsed');
        updateSidebarCollapseUI(true);
    } else {
        updateSidebarCollapseUI(false);
    }

    if (sidebarCollapseBtn) {
        sidebarCollapseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleDesktopSidebarCollapse();
        });
    }

    const sidebarHeader = document.getElementById('sidebarHeader');
    if (sidebarHeader) {
        sidebarHeader.addEventListener('click', (e) => {
            if (document.body.classList.contains('sidebar-collapsed')) {
                e.preventDefault();
                toggleDesktopSidebarCollapse();
            }
        });
    }

    // Atajo de teclado: Ctrl + B o Cmd + B
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            const tag = document.activeElement?.tagName?.toLowerCase();
            if (tag === 'input' || tag === 'textarea' || document.activeElement?.isContentEditable) {
                return; // Evitar interferir si el usuario está escribiendo en un formulario
            }
            e.preventDefault();
            if (window.innerWidth >= 992) {
                toggleDesktopSidebarCollapse();
            } else {
                const sidebar = document.getElementById('mainSidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar) {
                    sidebar.classList.toggle('mobile-open');
                    if (overlay) overlay.classList.toggle('active');
                }
            }
        }
    });

    // Si el sidebar está en modo mini/colapsado y se hace clic en un grupo, expandir automáticamente
    document.querySelectorAll('.sidebar-group-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            if (document.body.classList.contains('sidebar-collapsed')) {
                toggleDesktopSidebarCollapse();
            }
        });
    });
}

/**
 * 4. Auto-ocultar Mensajes Flash tras 4 segundos
 */
function initFlashAutoDismiss() {
    const flashAlerts = document.querySelectorAll('.alert-flash');
    flashAlerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });
}

/**
 * 5. Tablero Kanban con SortableJS y actualización asíncrona
 */
function initKanbanSortable() {
    const columns = document.querySelectorAll('.kanban-cards-wrapper');
    if (columns.length === 0 || typeof Sortable === 'undefined') return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    columns.forEach((column) => {
        new Sortable(column, {
            group: 'kanban',
            animation: 180,
            ghostClass: 'sortable-ghost',
            onEnd: function (evt) {
                const itemEl = evt.item;
                const projectId = itemEl.getAttribute('data-project-id');
                const targetColumn = evt.to;
                const newStatus = targetColumn.getAttribute('data-status');

                if (!projectId || !newStatus) return;

                fetch(appUrl(`/admin/review/${projectId}/status`), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ status: newStatus }),
                })
                    .then((res) => res.json())
                    .then((data) => {
                        if (!data.success) {
                            alert('No se pudo mover el proyecto al nuevo estado.');
                            window.location.reload();
                        } else {
                            if (newStatus === 'approved') {
                                triggerConfetti();
                            }
                            updateKanbanCounters();
                        }
                    })
                    .catch((err) => {
                        console.error('Error al actualizar estado:', err);
                        window.location.reload();
                    });
            },
        });
    });

    updateKanbanCounters();
}

function updateKanbanCounters() {
    document.querySelectorAll('.kanban-column').forEach((col) => {
        const countBadge = col.querySelector('.kanban-count, .ui-chevron-counter, .gl-col-counter');
        const cards = col.querySelectorAll('.kanban-card');
        if (countBadge) {
            countBadge.textContent = cards.length;
        }
    });
}

/**
 * 6. REPRODUCTOR DE VIDEO CINEMATOGRÁFICO EMBEBIDO (LIGHTBOX)
 */
function initVideoLightbox() {
    const modal = document.getElementById('videoLightboxModal');
    const container = document.getElementById('videoLightboxContainer');
    const titleEl = document.getElementById('videoLightboxTitle');

    if (!modal || !container) return;

    document.querySelectorAll('[data-video-url]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const rawUrl = btn.getAttribute('data-video-url');
            const projectTitle = btn.getAttribute('data-video-title') || 'Demostración de Software';

            if (!rawUrl) return;

            const embedHtml = parseVideoEmbed(rawUrl);
            container.innerHTML = embedHtml;
            if (titleEl) titleEl.textContent = projectTitle;

            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        });
    });

    // Detener reproducción al cerrar el modal
    modal.addEventListener('hidden.bs.modal', () => {
        container.innerHTML = '';
    });
}

/**
 * Helper global para abrir el reproductor de video (Lightbox) desde cualquier componente
 */
window.openVideoLightbox = function(rawUrl, projectTitle) {
    const modal = document.getElementById('videoLightboxModal');
    const container = document.getElementById('videoLightboxContainer');
    const titleEl = document.getElementById('videoLightboxTitle');
    if (!modal || !container || !rawUrl) return;

    const embedHtml = parseVideoEmbed(rawUrl);
    container.innerHTML = embedHtml;
    if (titleEl) titleEl.textContent = projectTitle || 'Demostración de Software';

    const bsModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
    bsModal.show();
};

function parseVideoEmbed(url) {
    // 1. YouTube (Sin cookies publicitarias de DoubleClick para evitar errores de red)
    const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (ytMatch) {
        return `<iframe src="https://www.youtube-nocookie.com/embed/${ytMatch[1]}?autoplay=1&rel=0&modestbranding=1&iv_load_policy=3&playsinline=1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen playsinline></iframe>`;
    }

    // 2. Loom
    const loomMatch = url.match(/loom\.com\/(?:share|embed)\/([a-zA-Z0-9]+)/);
    if (loomMatch) {
        return `<iframe src="https://www.loom.com/embed/${loomMatch[1]}?autoplay=1" allowfullscreen></iframe>`;
    }

    // 3. Vimeo
    const vimeoMatch = url.match(/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/);
    if (vimeoMatch) {
        return `<iframe src="https://player.vimeo.com/video/${vimeoMatch[3]}?autoplay=1" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
    }

    // 4. Video MP4 / WebM directo
    if (url.match(/\.(mp4|webm|ogg)$/i)) {
        return `<video controls autoplay class="w-100 h-100" style="object-fit: contain;"><source src="${url}">Tu navegador no soporta reproducción de video.</video>`;
    }

    // Fallback Iframe genérico
    return `<iframe src="${url}" allowfullscreen></iframe>`;
}

/**
 * 7. COMMAND PALETTE (CTRL + K SPOTLIGHT)
 */
function initCommandPalette() {
    const palette = document.getElementById('commandPaletteBackdrop');
    const input = document.getElementById('commandPaletteInput');
    const results = document.getElementById('commandPaletteResults');
    const triggerBtn = document.getElementById('commandPaletteTrigger');

    if (!palette || !input || !results) return;

    let selectedIndex = 0;

    function getItems() {
        return Array.from(results.querySelectorAll('.command-item'));
    }

    function updateSelection() {
        const items = getItems();
        if (!items.length) {
            selectedIndex = -1;
            return;
        }
        if (selectedIndex < 0) selectedIndex = 0;
        if (selectedIndex >= items.length) selectedIndex = items.length - 1;

        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('selected');
                item.classList.add('active');
                item.setAttribute('aria-selected', 'true');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('selected');
                item.classList.remove('active');
                item.removeAttribute('aria-selected');
            }
        });
    }

    function renderDefaultQuickActions() {
        const quickActions = [
            {
                label: 'Explorar Todos los Proyectos',
                sub: 'Ver portafolio completo, filtros y métricas',
                href: appUrl('/projects'),
                icon: '<i class="fa-solid fa-folder-open text-primary" style="font-size: 1rem; width: 18px;"></i>'
            },
            {
                label: 'Dev Lounge Comunitario',
                sub: 'Canal de discusión técnica y publicaciones',
                href: appUrl('/lounge'),
                icon: '<i class="fa-solid fa-comments text-info" style="font-size: 1rem; width: 18px;"></i>'
            },
            {
                label: 'Devioz Reels (Shorts Técnicos)',
                sub: 'Demostraciones y grabaciones cortas de proyectos',
                href: appUrl('/reels'),
                icon: '<i class="fa-solid fa-video text-danger" style="font-size: 1rem; width: 18px;"></i>'
            },
            {
                label: 'Directorio de Desarrolladores y Administradores',
                sub: 'Buscar colaboradores, perfiles y sedes',
                href: appUrl('/team'),
                icon: '<i class="fa-solid fa-users text-warning" style="font-size: 1rem; width: 18px;"></i>'
            },
            {
                label: 'Configuración de Cuenta y Perfil',
                sub: 'Cambiar avatar, modificar contraseña y datos personales',
                href: appUrl('/profile'),
                icon: '<i class="fa-solid fa-user-gear text-success" style="font-size: 1rem; width: 18px;"></i>'
            }
        ];

        let html = `<div class="text-secondary small fw-bold px-3 py-1 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Acciones Rápidas</div>`;
        quickActions.forEach(qa => {
            if (qa.href) {
                html += `
                    <a href="${qa.href}" class="command-item">
                        <span class="d-flex align-items-center justify-content-center" style="width: 24px;">${qa.icon}</span>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-white small text-truncate">${qa.label}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">${qa.sub}</div>
                        </div>
                    </a>
                `;
            } else if (qa.action) {
                html += `
                    <a href="#" class="command-item" data-command-action="${qa.action}">
                        <span class="d-flex align-items-center justify-content-center" style="width: 24px;">${qa.icon}</span>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-white small text-truncate">${qa.label}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">${qa.sub}</div>
                        </div>
                    </a>
                `;
            }
        });

        results.innerHTML = html;
        bindResultEvents();
        selectedIndex = 0;
        updateSelection();
    }

    function openPalette() {
        palette.classList.add('active');
        input.value = '';
        renderDefaultQuickActions();
        input.focus();
    }

    function closePalette() {
        palette.classList.remove('active');
    }

    if (triggerBtn) {
        triggerBtn.addEventListener('click', openPalette);
    }

    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (palette.classList.contains('active')) {
                closePalette();
            } else {
                openPalette();
            }
        }
        if (e.key === 'Escape' && palette.classList.contains('active')) {
            closePalette();
        }
    });

    palette.addEventListener('click', (e) => {
        if (e.target === palette) closePalette();
    });

    input.addEventListener('keydown', (e) => {
        const items = getItems();
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateSelection();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            updateSelection();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && items[selectedIndex]) {
                items[selectedIndex].click();
            }
        }
    });

    let debounceTimer;
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();
        if (!query) {
            renderDefaultQuickActions();
            return;
        }
        debounceTimer = setTimeout(() => {
            fetchResults(query);
        }, 150);
    });

    function fetchResults(query) {
        fetch(appUrl(`/api/search?q=${encodeURIComponent(query)}`))
            .then(res => res.json())
            .then(data => {
                renderResults(data);
            })
            .catch(err => {
                results.innerHTML = `<div class="p-3 text-secondary small text-center">Escribe para buscar proyectos, desarrolladores o hitos...</div>`;
            });
    }

    function renderResults(data) {
        if (!data || (!data.projects?.length && !data.users?.length)) {
            results.innerHTML = `<div class="p-3 text-secondary small text-center">No se encontraron resultados coincidentes.</div>`;
            return;
        }

        let html = '';
        if (data.projects?.length) {
            html += `<div class="text-secondary small fw-bold px-3 py-1 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Proyectos</div>`;
            data.projects.forEach(p => {
                html += `
                    <a href="${appUrl('/projects/' + p.id)}" class="command-item">
                        <span class="badge bg-dark border border-secondary text-emerald fw-bold">${p.code}</span>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-white small text-truncate">${p.name}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">Sede: ${p.sede || '—'} &bull; ${p.status}</div>
                        </div>
                    </a>
                `;
            });
        }

        if (data.users?.length) {
            html += `<div class="text-secondary small fw-bold px-3 py-1 mt-2 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Desarrolladores y Administradores</div>`;
            data.users.forEach(u => {
                html += `
                    <a href="${appUrl('/team?q=' + encodeURIComponent(u.name))}" class="command-item">
                        <img src="${u.avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name) + '&background=10b981&color=fff'}" class="rounded-circle" width="24" height="24">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="fw-semibold text-white small text-truncate">${u.name}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">${u.email} &bull; ${u.sede || u.role}</div>
                        </div>
                    </a>
                `;
            });
        }

        results.innerHTML = html;
        bindResultEvents();
        selectedIndex = 0;
        updateSelection();
    }

    function bindResultEvents() {
        const items = getItems();
        items.forEach((item, idx) => {
            item.addEventListener('mouseenter', () => {
                selectedIndex = idx;
                updateSelection();
            });

            const action = item.getAttribute('data-command-action');
            if (action) {
                // otras acciones si las hubiera
            }
        });
    }
}

/**
 * 8. DEVIOZ BOT (ASISTENTE VIRTUAL: LABVA AI - ORBE REACTIVO X.AI / GROK)
 */
function initDeviozBot() {
    const launchersContainer = document.getElementById('botLaunchersContainer');
    const pillSofiaBtn = document.getElementById('botPillSofiaBtn');
    const avatarSofiaBtn = document.getElementById('botAvatarSofiaBtn');
    const windowEl = document.getElementById('deviozBotWindow');
    const closeBtn = document.getElementById('deviozBotClose');
    const form = document.getElementById('deviozBotForm');
    const input = document.getElementById('deviozBotInput');
    const body = document.getElementById('deviozBotBody');

    if (!windowEl || !form) return;

    let isSearching = false;
    const conversationId = (() => {
        const bytes = new Uint8Array(16);
        if (window.crypto?.getRandomValues) {
            window.crypto.getRandomValues(bytes);
            return Array.from(bytes, value => value.toString(16).padStart(2, '0')).join('');
        }
        return `${Date.now().toString(16)}${Math.random().toString(16).slice(2)}`.padEnd(32, '0').slice(0, 32);
    })();

    const labvaMiniOrbSvg = `
        <div class="labva-orb-container state-idle flex-shrink-0" style="width: 28px; height: 28px; margin-top: 2px;">
            <svg class="labva-orb-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle class="labva-sphere" cx="50" cy="50" r="44"></circle>
                <g class="labva-eyes-wrap">
                    <rect class="labva-eye eye-left" x="52" y="34" width="7.5" height="15" rx="3.75"></rect>
                    <rect class="labva-eye eye-right" x="65" y="33" width="7.5" height="14" rx="3.75"></rect>
                </g>
            </svg>
        </div>
    `;

    function setLabvaState(state) {
        const orbs = document.querySelectorAll('.labva-orb-container');
        orbs.forEach(orb => {
            orb.classList.remove('state-idle', 'state-typing', 'state-searching', 'state-happy');
            orb.classList.add('state-' + state);
        });

        const statusEl = document.getElementById('labvaStatusText');
        if (statusEl) {
            if (state === 'searching') {
                statusEl.innerHTML = '<span class="bot-status-dot-inline" style="color: #10B981;">●</span> Analizando consulta...';
            } else if (state === 'typing') {
                statusEl.innerHTML = '<span class="bot-status-dot-inline" style="color: #10B981;">●</span> Escuchando...';
            } else if (state === 'happy') {
                statusEl.innerHTML = '<span class="bot-status-dot-inline" style="color: #16A34A;">●</span> Respuesta lista';
            } else {
                statusEl.innerHTML = '<span class="bot-status-dot-inline" style="color: #16A34A;">●</span> En línea &bull; Asistente Devioz 24';
            }
        }
    }

    function toggleBot() {
        windowEl.classList.toggle('active');
        if (windowEl.classList.contains('active')) {
            setTimeout(() => {
                if (input) input.focus();
                if (body) body.scrollTop = body.scrollHeight;
            }, 120);
        } else {
            if (!isSearching) setLabvaState('idle');
        }
    }

    if (pillSofiaBtn) {
        pillSofiaBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleBot();
        });
    }



    if (avatarSofiaBtn) {
        avatarSofiaBtn.addEventListener('mouseenter', () => {
            if (!isSearching && !windowEl.classList.contains('active')) {
                setLabvaState('typing');
            }
        });

        avatarSofiaBtn.addEventListener('mouseleave', () => {
            if (!isSearching && !windowEl.classList.contains('active')) {
                setLabvaState('idle');
            }
        });

        avatarSofiaBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleBot();
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            windowEl.classList.remove('active');
            if (!isSearching) setLabvaState('idle');
        });
    }

    // Cerrar la ventana flotante si el usuario hace clic fuera de ella
    document.addEventListener('click', (e) => {
        if (windowEl.classList.contains('active')) {
            if (!windowEl.contains(e.target) && (!launchersContainer || !launchersContainer.contains(e.target))) {
                windowEl.classList.remove('active');
                if (!isSearching) setLabvaState('idle');
            }
        }
    });

    // Cerrar con la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && windowEl.classList.contains('active')) {
            windowEl.classList.remove('active');
            if (!isSearching) setLabvaState('idle');
        }
    });

    // Reactividad en el campo de texto (Focus, Input, Blur)
    if (input) {
        input.addEventListener('focus', () => {
            if (!isSearching) setLabvaState('typing');
        });

        input.addEventListener('input', () => {
            if (!isSearching) {
                if (input.value.trim().length > 0) {
                    setLabvaState('typing');
                } else {
                    setLabvaState('idle');
                }
            }
        });

        input.addEventListener('blur', () => {
            if (!isSearching) setLabvaState('idle');
        });
    }

    // Manejo de chips de preguntas rápidas
    document.querySelectorAll('[data-bot-prompt]').forEach(chip => {
        chip.addEventListener('click', () => {
            const prompt = chip.getAttribute('data-bot-prompt');
            askBot(prompt);
        });
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg) return;
        askBot(msg);
        input.value = '';
    });

    function askBot(userMessage) {
        if (isSearching) return;
        appendMessage(userMessage, 'user');
        isSearching = true;
        setLabvaState('searching');

        const loaderId = 'botLoader_' + Date.now();
        const loaderHtml = `
            <div class="bot-message-row" id="${loaderId}">
                <div class="labva-orb-container state-searching flex-shrink-0" style="width: 28px; height: 28px; margin-top: 2px;">
                    <svg class="labva-orb-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle class="labva-aura" cx="50" cy="50" r="47"></circle>
                        <circle class="labva-sphere" cx="50" cy="50" r="44"></circle>
                        <g class="labva-eyes-wrap">
                            <rect class="labva-eye eye-left" x="52" y="34" width="7.5" height="15" rx="3.75"></rect>
                            <rect class="labva-eye eye-right" x="65" y="33" width="7.5" height="14" rx="3.75"></rect>
                        </g>
                    </svg>
                </div>
                <div class="bot-speech-bubble d-flex align-items-center gap-2">
                    <span class="spinner-border spinner-border-sm" style="width: 12px; height: 12px; color: #10B981;" role="status"></span>
                    <span style="font-size: 0.85rem; color: #5E6C84;">LABVA está analizando la consulta...</span>
                </div>
            </div>
        `;
        body.insertAdjacentHTML('beforeend', loaderHtml);
        body.scrollTop = body.scrollHeight;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch(appUrl('/api/bot'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message: userMessage, conversation_id: conversationId })
        })
        .then(res => res.json())
        .then(data => {
            const loaderEl = document.getElementById(loaderId);
            if (loaderEl) loaderEl.remove();
            isSearching = false;
            setLabvaState('happy');
            appendMessage(data.reply || 'No pude encontrar información para esa consulta.', 'bot', data.response_id);
            setTimeout(() => {
                if (!isSearching) setLabvaState('idle');
            }, 1400);
        })
        .catch(err => {
            const loaderEl = document.getElementById(loaderId);
            if (loaderEl) loaderEl.remove();
            isSearching = false;
            setLabvaState('idle');
            appendMessage('Ocurrió un error al contactar con el servicio LABVA AI.', 'bot');
        });
    }

    function formatBotMarkdown(rawText) {
        if (!rawText) return '';
        let html = rawText;

        // Limpieza de emojis para mantener estética 100% sobria y profesional
        html = html.replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{1F700}-\u{1F77F}\u{1F780}-\u{1F7FF}\u{1F800}-\u{1F8FF}\u{1F900}-\u{1F9FF}\u{1FA00}-\u{1FA6F}\u{1FA70}-\u{1FAFF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu, '');

        // 1. Escapar HTML base para evitar inyección
        html = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        // 2. Negritas **texto**
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 3. Cursivas *texto*
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

        // 4. Código en línea `code`
        html = html.replace(/`([^`]+)`/g, '<code class="bot-inline-code">$1</code>');

        // 5. Enlaces markdown [label](url)
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function (match, label, href) {
            let fullHref = href;
            if (href.startsWith('/')) {
                fullHref = appUrl(href);
            }
            return `<a href="${fullHref}" class="bot-link">${label} &rarr;</a>`;
        });

        // 6. Elementos de lista con viñeta (• o -)
        html = html.replace(/(?:^|\n)[•\-]\s+(.+)/g, '<div class="bot-list-item"><span class="bot-bullet">●</span> <span>$1</span></div>');

        // 7. Saltos de párrafo y líneas
        html = html.replace(/\n\n/g, '<div class="bot-paragraph-gap"></div>');
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    function appendMessage(text, sender, responseId = null) {
        if (sender === 'user') {
            const bubble = document.createElement('div');
            bubble.className = 'bot-msg-user-bubble';
            bubble.textContent = text;
            body.appendChild(bubble);
        } else {
            const row = document.createElement('div');
            row.className = 'bot-message-row';

            const orbWrap = document.createElement('div');
            orbWrap.innerHTML = labvaMiniOrbSvg.trim();
            const orbEl = orbWrap.firstElementChild;

            const bubble = document.createElement('div');
            bubble.className = 'bot-speech-bubble';
            bubble.innerHTML = formatBotMarkdown(text);

            if (typeof responseId === 'string' && /^[a-f0-9]{32}$/.test(responseId)) {
                const feedback = document.createElement('div');
                feedback.className = 'd-flex align-items-center flex-wrap gap-2 mt-3 pt-2 border-top bot-response-feedback';

                const prompt = document.createElement('span');
                prompt.className = 'small text-secondary';
                prompt.textContent = '¿Te resultó útil?';
                feedback.appendChild(prompt);

                const status = document.createElement('span');
                status.className = 'small text-secondary';

                [
                    { rating: 'helpful', label: 'Sí', icon: 'fa-thumbs-up' },
                    { rating: 'not_helpful', label: 'No', icon: 'fa-thumbs-down' },
                ].forEach(option => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'btn btn-sm btn-outline-secondary rounded-pill';
                    button.setAttribute('aria-label', option.rating === 'helpful' ? 'Valorar respuesta como útil' : 'Valorar respuesta como no útil');
                    button.innerHTML = `<i class="fa-regular ${option.icon}" aria-hidden="true"></i> ${option.label}`;
                    button.addEventListener('click', () => {
                        feedback.querySelectorAll('button').forEach(item => { item.disabled = true; });
                        status.textContent = 'Registrando…';

                        fetch(appUrl('/api/bot/feedback'), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: JSON.stringify({ response_id: responseId, rating: option.rating })
                        })
                        .then(response => response.json().then(data => ({ ok: response.ok, data })))
                        .then(result => {
                            if (!result.ok) throw new Error(result.data.error || 'No se pudo registrar la valoración.');
                            prompt.textContent = 'Gracias por tu valoración.';
                            feedback.querySelectorAll('button').forEach(item => item.remove());
                            status.remove();
                        })
                        .catch(() => {
                            status.textContent = 'No se pudo guardar. Inténtalo otra vez.';
                            feedback.querySelectorAll('button').forEach(item => { item.disabled = false; });
                        });
                    });
                    feedback.appendChild(button);
                });

                feedback.appendChild(status);
                bubble.appendChild(feedback);
            }

            row.appendChild(orbEl);
            row.appendChild(bubble);
            body.appendChild(row);
        }
        body.scrollTop = body.scrollHeight;
    }
}

/**
 * 9. INTERACCIONES DE DEVIOZ REELS
 */
function initReelsInteractions() {
    const reelsViewport = document.getElementById('reelsViewport');
    if (!reelsViewport) return;

    const reelCards = Array.from(reelsViewport.querySelectorAll('.reel-card'));
    if (!reelCards.length) return;

    // Cache inicial de fachadas (posters) para restauración sin parpadeos
    reelCards.forEach(card => {
        const videoBg = card.querySelector('.reel-video-bg');
        if (videoBg && !videoBg.hasAttribute('data-initial-facade')) {
            videoBg.setAttribute('data-initial-facade', videoBg.innerHTML);
        }
    });

    // Estado global de audio para el feed (persistido durante la sesión)
    // Inicialmente silenciado para cumplir con la política estricta de Autoplay de Chrome/Edge/Safari
    let isAudioMuted = sessionStorage.getItem('devioz_reels_muted') !== 'false';
    let currentActiveIndex = -1;

    // Sincronización de todos los botones de audio del feed
    function updateAudioControls() {
        document.querySelectorAll('.reel-audio-toggle').forEach(btn => {
            const icon = btn.querySelector('.reel-audio-icon') || btn.querySelector('i');
            const label = btn.querySelector('.reel-audio-label');
            if (isAudioMuted) {
                if (icon) icon.className = 'fa-solid fa-volume-xmark reel-audio-icon';
                if (label) label.textContent = 'Silenciado';
                btn.classList.remove('is-unmuted');
                btn.setAttribute('title', 'Activar Sonido (Actualmente Silenciado)');
            } else {
                if (icon) icon.className = 'fa-solid fa-volume-high reel-audio-icon';
                if (label) label.textContent = 'Sonido Activado';
                btn.classList.add('is-unmuted');
                btn.setAttribute('title', 'Silenciar Sonido');
            }
        });
    }
    updateAudioControls();

    function toggleFeedAudio() {
        isAudioMuted = !isAudioMuted;
        sessionStorage.setItem('devioz_reels_muted', isAudioMuted ? 'true' : 'false');
        updateAudioControls();

        // Enviar comando al video actualmente activo
        if (currentActiveIndex >= 0 && reelCards[currentActiveIndex]) {
            const iframe = reelCards[currentActiveIndex].querySelector('iframe');
            if (iframe && iframe.contentWindow) {
                const cmd = isAudioMuted ? 'mute' : 'unMute';
                iframe.contentWindow.postMessage(JSON.stringify({
                    event: 'command',
                    func: cmd,
                    args: ''
                }), '*');
            }
        }
    }

    document.querySelectorAll('.reel-audio-toggle').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleFeedAudio();
        });
    });

    // Función para activar un reel (montar iframe, autoplay inmediato)
    function activateReel(card) {
        const index = parseInt(card.getAttribute('data-reel-index') || '0', 10);
        if (currentActiveIndex === index && card.classList.contains('is-active-reel')) {
            return;
        }

        // 1. Cortar de inmediato cualquier otro video activo
        reelCards.forEach((c, i) => {
            if (i !== index) {
                deactivateReel(c);
            }
        });

        currentActiveIndex = index;
        card.classList.add('is-active-reel');

        const videoBg = card.querySelector('.reel-video-bg');
        if (!videoBg) return;

        const rawEmbed = videoBg.getAttribute('data-video-embed');
        if (!rawEmbed) return;

        const separator = rawEmbed.includes('?') ? '&' : '?';
        const muteParam = isAudioMuted ? '&mute=1' : '&mute=0';
        // Añadir parámetros para limpiar la interfaz del reproductor (YouTube)
        const fullEmbed = `${rawEmbed}${separator}autoplay=1&enablejsapi=1&playsinline=1&controls=0&modestbranding=1&rel=0&iv_load_policy=3${muteParam}`;

        const existingIframe = videoBg.querySelector('iframe');
        if (!existingIframe) {
            videoBg.innerHTML = `
                <iframe
                    src="${fullEmbed}"
                    class="w-100 h-100 border-0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    playsinline>
                </iframe>
            `;
        } else {
            // Si ya existe, reanudar
            try {
                existingIframe.contentWindow.postMessage(JSON.stringify({
                    event: 'command',
                    func: 'playVideo',
                    args: ''
                }), '*');
            } catch (e) {}
        }

        updateLiquidVisuals(index);
    }

    // Función para desactivar un reel (corte instantáneo de audio y video)
    function deactivateReel(card) {
        card.classList.remove('is-active-reel');
        const videoBg = card.querySelector('.reel-video-bg');
        if (!videoBg) return;

        const iframe = videoBg.querySelector('iframe');
        if (iframe) {
            // 1. Pausar inmediatamente via postMessage
            try {
                iframe.contentWindow.postMessage(JSON.stringify({
                    event: 'command',
                    func: 'pauseVideo',
                    args: ''
                }), '*');
            } catch (e) {}

            // 2. Restaurar fachada inicial para cortar 100% audio y liberar memoria
            const initialFacade = videoBg.getAttribute('data-initial-facade');
            if (initialFacade) {
                videoBg.innerHTML = initialFacade;
            } else {
                videoBg.innerHTML = '';
            }
        }
    }

    // Permitir clic manual en la portada para activar/reproducir
    reelsViewport.addEventListener('click', (e) => {
        const posterWrapper = e.target.closest('.reel-poster-wrapper');
        if (posterWrapper) {
            const card = posterWrapper.closest('.reel-card');
            if (card) {
                activateReel(card);
            }
        }
    });

    // IntersectionObserver con umbral de 58% para transición estilo TikTok
    let observer;
    if ('IntersectionObserver' in window) {
        observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && entry.intersectionRatio >= 0.58) {
                    activateReel(entry.target);
                } else if (!entry.isIntersecting || entry.intersectionRatio < 0.25) {
                    deactivateReel(entry.target);
                }
            });
        }, {
            root: reelsViewport,
            threshold: [0.1, 0.25, 0.58, 0.85]
        });

        reelCards.forEach(card => observer.observe(card));
    } else {
        activateReel(reelCards[0]);
    }

    // =========================================================================
    // MEDIDOR LÍQUIDO DE PROGRESO (FLUID TUBE SCROLL GAUGE)
    // =========================================================================
    const liquidFluid = document.getElementById('liquidFluidBody');
    const liquidPercentNum = document.getElementById('liquidPercentNum');
    const liquidReelStep = document.getElementById('liquidReelStep');
    const liquidDots = document.querySelectorAll('.liquid-dot');
    const liquidPrevBtn = document.getElementById('liquidPrevBtn');
    const liquidNextBtn = document.getElementById('liquidNextBtn');
    const liquidCapsule = document.getElementById('liquidCapsuleGlass');

    function updateLiquidVisuals(activeIndex) {
        if (liquidReelStep) {
            liquidReelStep.textContent = `${activeIndex + 1} / ${reelCards.length}`;
        }
        liquidDots.forEach((dot, idx) => {
            if (idx === activeIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    let isTicking = false;
    function syncLiquidWithScroll() {
        const maxScroll = reelsViewport.scrollHeight - reelsViewport.clientHeight;
        const currentScroll = reelsViewport.scrollTop;
        const scrollPercent = maxScroll > 0 ? (currentScroll / maxScroll) * 100 : 0;
        const clampedPercent = Math.min(100, Math.max(0, scrollPercent));

        if (liquidFluid) {
            // Mínimo de 12% para que la superficie ondulante siempre sea visible
            const displayHeight = Math.min(100, Math.max(12, clampedPercent));
            liquidFluid.style.height = `${displayHeight}%`;
        }

        if (liquidPercentNum) {
            liquidPercentNum.textContent = `${Math.round(clampedPercent)}%`;
        }

        isTicking = false;
    }

    reelsViewport.addEventListener('scroll', () => {
        if (!isTicking) {
            window.requestAnimationFrame(syncLiquidWithScroll);
            isTicking = true;
        }
    }, { passive: true });

    // Clic en la probeta para saltar al porcentaje proporcional
    if (liquidCapsule) {
        liquidCapsule.addEventListener('click', (e) => {
            const rect = liquidCapsule.getBoundingClientRect();
            const clickY = e.clientY - rect.top;
            const height = rect.height;
            const ratioFromTop = Math.max(0, Math.min(1, clickY / height));
            const targetScroll = (reelsViewport.scrollHeight - reelsViewport.clientHeight) * ratioFromTop;
            reelsViewport.scrollTo({
                top: targetScroll,
                behavior: 'smooth'
            });
        });
    }

    // Clic en los marcadores discretos (dots)
    liquidDots.forEach(dot => {
        dot.addEventListener('click', (e) => {
            e.stopPropagation();
            const targetIdx = parseInt(dot.getAttribute('data-reel-target') || '0', 10);
            if (reelCards[targetIdx]) {
                reelCards[targetIdx].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // Flechas Arriba / Abajo en el widget de líquido
    function scrollToReel(index) {
        const clampedIndex = Math.max(0, Math.min(reelCards.length - 1, index));
        if (reelCards[clampedIndex]) {
            reelCards[clampedIndex].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    if (liquidPrevBtn) {
        liquidPrevBtn.addEventListener('click', () => {
            scrollToReel((currentActiveIndex > 0 ? currentActiveIndex : 0) - 1);
        });
    }

    if (liquidNextBtn) {
        liquidNextBtn.addEventListener('click', () => {
            scrollToReel((currentActiveIndex >= 0 ? currentActiveIndex : 0) + 1);
        });
    }

    // Soporte para teclas de flechas Arriba y Abajo dentro del viewport
    reelsViewport.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            scrollToReel((currentActiveIndex >= 0 ? currentActiveIndex : 0) + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            scrollToReel((currentActiveIndex > 0 ? currentActiveIndex : 0) - 1);
        }
    });

    // Likes con confeti
    document.querySelectorAll('.reel-like-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const countSpan = btn.querySelector('.reel-like-count');
            let count = parseInt(countSpan?.textContent || '0');
            count++;
            if (countSpan) countSpan.textContent = count;
            triggerConfetti();
        });
    });

    // Inicializar estado líquido inicial
    syncLiquidWithScroll();
}

/**
 * 10. DEV LOUNGE: VISTA PREVIA DE ENLACES (LINK PREVIEWS), REACCIONES Y COMENTARIOS
 */
function initLoungeInteractive() {
    initLoungeReactions();
    initLoungeComments();
    initLoungeLinkPreview();
}

function initLoungeReactions() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const reactionIcons = {
        like: '<i class="fa-solid fa-thumbs-up" aria-hidden="true"></i>',
        rocket: '<i class="fa-solid fa-rocket" aria-hidden="true"></i>',
        idea: '<i class="fa-solid fa-lightbulb" aria-hidden="true"></i>',
        fire: '<i class="fa-solid fa-fire" aria-hidden="true"></i>',
        heart: '<i class="fa-solid fa-heart" aria-hidden="true"></i>'
    };
    const labelMap = {
        'like': 'Me gusta',
        'rocket': 'Brutal',
        'idea': 'Inspirador',
        'fire': 'Crack',
        'heart': 'Me encanta'
    };

    function sendReaction(postId, reactionType, triggerBtn, popover) {
        if (!postId) return;

        fetch(appUrl(`/lounge/${postId}/react`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ type: reactionType })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Actualizar botón de reacción principal
                const iconEl = triggerBtn ? triggerBtn.querySelector('.reaction-icon-active') : null;
                const labelEl = triggerBtn ? triggerBtn.querySelector('.reaction-label-active') : null;
                const totalEl = triggerBtn ? triggerBtn.querySelector('.reaction-total-count') : null;

                if (totalEl) totalEl.textContent = data.total;

                if (triggerBtn) {
                    if (data.current_user_reaction) {
                        triggerBtn.classList.add('active');
                        if (iconEl) iconEl.innerHTML = reactionIcons[data.current_user_reaction] || reactionIcons.like;
                        if (labelEl) labelEl.textContent = labelMap[data.current_user_reaction] || 'Reaccionar';
                    } else {
                        triggerBtn.classList.remove('active');
                        if (iconEl) iconEl.innerHTML = reactionIcons.like;
                        if (labelEl) labelEl.textContent = 'Reaccionar';
                    }

                    // Micro-animación de rebote (bounce pop)
                    triggerBtn.style.transform = 'scale(1.18)';
                    setTimeout(() => { triggerBtn.style.transform = ''; }, 200);
                }

                // Actualizar resumen de pills de reacciones
                const summaryContainer = document.getElementById(`reactionsSummary-${postId}`);
                if (summaryContainer && data.summary) {
                    let html = '';
                    for (const [type, count] of Object.entries(data.summary)) {
                        if (count > 0) {
                            const isActive = data.current_user_reaction === type ? 'active' : '';
                            html += `<span class="reaction-pill ${isActive}" data-reaction="${type}">${reactionIcons[type]} ${count}</span>`;
                        }
                    }
                    summaryContainer.innerHTML = html;
                }

                if (popover) {
                    popover.classList.remove('show');
                }
            }
        })
        .catch(err => console.error('Error al reaccionar:', err));
    }

    // Manejar clics en el popover flotante
    document.querySelectorAll('.reaction-choice-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const container = btn.closest('.lounge-reactions-container');
            const postId = container?.getAttribute('data-post-id');
            const triggerBtn = container?.querySelector('.reaction-trigger-btn');
            const popover = container?.querySelector('.reaction-picker-popover');
            const type = btn.getAttribute('data-reaction') || 'like';

            sendReaction(postId, type, triggerBtn, popover);
        });
    });

    // Manejar clic directo en el botón principal
    document.querySelectorAll('.reaction-trigger-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const container = btn.closest('.lounge-reactions-container');
            const postId = container?.getAttribute('data-post-id');
            const popover = container?.querySelector('.reaction-picker-popover');

            if (window.innerWidth < 768) {
                if (popover) popover.classList.toggle('show');
            } else {
                sendReaction(postId, 'like', btn, popover);
            }
        });
    });

    // Manejar clic en las pastillas resumen
    document.addEventListener('click', (e) => {
        const pill = e.target.closest('.reaction-pill');
        if (pill) {
            const postWrapper = pill.closest('.card-custom');
            const container = postWrapper?.querySelector('.lounge-reactions-container');
            const postId = container?.getAttribute('data-post-id');
            const triggerBtn = container?.querySelector('.reaction-trigger-btn');
            const type = pill.getAttribute('data-reaction') || 'like';

            sendReaction(postId, type, triggerBtn, null);
        }
    });
}

function initLoungeComments() {
    document.querySelectorAll('.lounge-comment-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const postId = form.getAttribute('data-post-id');
            const input = form.querySelector('.comment-input-text');
            const content = input?.value?.trim();
            const submitBtn = form.querySelector('button[type="submit"]');

            if (!content || !postId) return;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar';
                }

                if (data.success && data.comment) {
                    const list = document.getElementById(`commentsList-${postId}`);
                    const emptyMsg = list?.querySelector('.empty-comments-msg');
                    if (emptyMsg) emptyMsg.remove();

                    // Crear elemento de comentario con animación suave
                    const item = document.createElement('div');
                    item.className = 'd-flex gap-2 align-items-start comment-item';
                    item.style.animation = 'fadeIn 0.3s ease';
                    item.innerHTML = `
                        <img src="${data.comment.user_avatar}" class="rounded-circle border border-secondary flex-shrink-0" width="32" height="32" alt="${data.comment.user_name}">
                        <div class="comment-bubble">
                            <div class="d-flex justify-content-between align-items-center mb-1 gap-2">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <span class="fw-bold small text-white">${data.comment.user_name}</span>
                                    <span class="badge ${data.comment.user_role === 'Administrador' || data.comment.user_role === 'Docente' ? 'bg-danger' : 'bg-emerald'} p-1" style="font-size: 0.6rem;">${data.comment.user_role}</span>
                                    ${data.comment.user_sede ? `<span class="badge-sede" style="font-size: 0.6rem; padding: 1px 5px;">${data.comment.user_sede}</span>` : ''}
                                </div>
                                <span class="text-secondary small font-monospace" style="font-size: 0.68rem;">${data.comment.created_at}</span>
                            </div>
                            <div class="text-secondary small" style="line-height: 1.45;">${data.comment.content}</div>
                        </div>
                    `;
                    list.appendChild(item);

                    // Actualizar contadores
                    const counterBadge = document.querySelector(`#post-${postId} .comments-counter-badge`);
                    if (counterBadge && data.comments_count) {
                        counterBadge.textContent = data.comments_count;
                    }

                    if (input) input.value = '';
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar';
                }
                console.error('Error al comentar:', err);
            });
        });
    });
}

function initLoungeLinkPreview() {
    const linkInput = document.getElementById('loungeLinkInput');
    const detectBtn = document.getElementById('btnDetectLink');
    const previewContainer = document.getElementById('linkLivePreview');
    const previewCard = document.getElementById('linkLivePreviewCard');
    const clearBtn = document.getElementById('btnClearPreview');

    const hiddenTitle = document.getElementById('hiddenLinkTitle');
    const hiddenDesc = document.getElementById('hiddenLinkDesc');
    const hiddenImage = document.getElementById('hiddenLinkImage');
    const hiddenDomain = document.getElementById('hiddenLinkDomain');
    const titleInput = document.getElementById('postTitleInput');

    if (!linkInput || !previewContainer || !previewCard) return;

    let debounceTimer = null;

    function fetchPreview(url) {
        if (!url || !url.startsWith('http')) return;

        if (detectBtn) {
            detectBtn.disabled = true;
            detectBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Analizando...';
        }

        fetch(appUrl(`/lounge/preview-link?url=${encodeURIComponent(url)}`))
            .then(res => res.json())
            .then(data => {
                if (detectBtn) {
                    detectBtn.disabled = false;
                    detectBtn.textContent = 'Analizar Enlace';
                }

                if (data.success && data.preview) {
                    const p = data.preview;

                    if (hiddenTitle) hiddenTitle.value = p.title || '';
                    if (hiddenDesc) hiddenDesc.value = p.description || '';
                    if (hiddenImage) hiddenImage.value = p.image || '';
                    if (hiddenDomain) hiddenDomain.value = p.domain || '';

                    if (titleInput && (!titleInput.value || titleInput.value.trim() === '')) {
                        titleInput.value = p.title || '';
                    }

                    previewCard.innerHTML = `
                        <div class="lounge-link-card mb-0">
                            <div class="lounge-link-img-wrapper position-relative">
                                <img src="${p.image}" alt="${p.title}" class="lounge-link-img">
                                ${p.is_youtube ? `
                                    <div class="position-absolute top-50 start-50 translate-middle">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 44px; height: 44px; background: rgba(239, 68, 68, 0.95); color: #fff;">
                                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="lounge-link-body">
                                <div class="lounge-link-domain">
                                    <span class="badge ${p.is_youtube ? 'bg-danger' : 'bg-brand'} p-1" style="font-size: 0.6rem;">${p.domain.toUpperCase()}</span>
                                    <span>${p.domain}</span>
                                </div>
                                <div class="lounge-link-title">${p.title}</div>
                                <div class="lounge-link-desc">${p.description || 'Vista previa enriquecida para la comunidad.'}</div>
                            </div>
                        </div>
                    `;

                    previewContainer.classList.remove('d-none');
                    previewContainer.classList.add('has-preview');
                }
            })
            .catch(err => {
                if (detectBtn) {
                    detectBtn.disabled = false;
                    detectBtn.textContent = 'Analizar Enlace';
                }
                console.error('Error al detectar enlace:', err);
            });
    }

    linkInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const url = linkInput.value.trim();
        if (url.startsWith('http://') || url.startsWith('https://')) {
            debounceTimer = setTimeout(() => fetchPreview(url), 400);
        }
    });

    linkInput.addEventListener('paste', () => {
        setTimeout(() => {
            const url = linkInput.value.trim();
            if (url.startsWith('http://') || url.startsWith('https://')) {
                fetchPreview(url);
            }
        }, 100);
    });

    if (detectBtn) {
        detectBtn.addEventListener('click', () => {
            const url = linkInput.value.trim();
            fetchPreview(url);
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            linkInput.value = '';
            previewContainer.classList.add('d-none');
            previewContainer.classList.remove('has-preview');
            previewCard.innerHTML = '';
            if (hiddenTitle) hiddenTitle.value = '';
            if (hiddenDesc) hiddenDesc.value = '';
            if (hiddenImage) hiddenImage.value = '';
            if (hiddenDomain) hiddenDomain.value = '';
        });
    }
}

/**
 * 11. CONFETTI DE CELEBRACIÓN
 */
function triggerConfetti() {
    if (typeof confetti === 'function') {
        confetti({
            particleCount: 80,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#10b981', '#3b82f6', '#f59e0b', '#ffffff']
        });
    }
}

function checkForCelebration() {
    const successAlert = document.querySelector('.alert-success');
    if (successAlert && (successAlert.textContent.includes('Aprobado') || successAlert.textContent.includes('completado todos los hitos'))) {
        triggerConfetti();
    }
}

/**
 * 12. Helpers Previos (Previews, Confirmaciones, Copy, Chart)
 */
function initFileUploadPreviews() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    fileInputs.forEach((input) => {
        const previewTargetId = input.getAttribute('data-preview');
        const previewTarget = document.getElementById(previewTargetId);
        if (!previewTarget) return;

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewTarget.src = e.target.result;
                    previewTarget.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

function initConfirmDialogs() {
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (e) => {
            const message = element.getAttribute('data-confirm') || '¿Estás seguro de continuar con esta acción?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

function initCopyButtons() {
    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const textToCopy = btn.getAttribute('data-copy');
            if (!textToCopy) return;

            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = `<span class="text-success">¡Copiado!</span>`;
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 1800);
            });
        });
    });
}

function initStatsChart() {
    const canvas = document.getElementById('dashboardActivityChart');
    if (!canvas || typeof Chart === 'undefined') return;

    let chartInstance = null;

    function renderChart(labels, hours) {
        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: (labels && labels.length) ? labels : ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [
                    {
                        label: 'Horas Trabajadas Reportadas',
                        data: (hours && hours.length) ? hours : [4, 6, 7.5, 5, 8, 3, 2],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y} hrs de actividad`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(31, 41, 55, 0.08)' },
                        ticks: { color: '#78909C' },
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#78909C' },
                    },
                },
            },
        });
    }

    fetch(appUrl('/api/stats'))
        .then((res) => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then((data) => {
            const rawActivity = data.activity || [];
            if (rawActivity.length > 0) {
                const labels = rawActivity.map((item) => {
                    const parts = (item.check_in_date || '').split('-');
                    return parts.length === 3 ? `${parts[2]}/${parts[1]}` : item.check_in_date;
                });
                const hours = rawActivity.map((item) => parseFloat(item.total_hours || 0));
                renderChart(labels, hours);
            } else {
                renderChart(null, null);
            }
        })
        .catch((err) => {
            console.warn('[Devioz 24] Usando estadísticas base para gráfica:', err);
            renderChart(null, null);
        });
}

/**
 * ==============================================================================
 * DEVIOZ ENTERPRISE SUITE (CHAT 1 A 1, DOCK, MEET, NOTIFICACIONES Y TRACKER)
 * ==============================================================================
 */
function initDeviozSuite() {
    const appUrlMeta = document.querySelector('meta[name="app-url"]');
    const baseUrl = appUrlMeta ? appUrlMeta.getAttribute('content').replace(/\/+$/, '') : '';

    let activeContactId = null;
    let activeProjectId = null;
    let chatPollInterval = null;
    let currentCallRoomUrl = '';

    // Elementos del Chat Drawer
    const chatDrawer = document.getElementById('deviozChatDrawer');
    const chatBackdrop = document.getElementById('deviozChatBackdrop');
    const chatCloseBtn = document.getElementById('deviozChatCloseBtn');
    const chatForm = document.getElementById('deviozChatForm');
    const chatInput = document.getElementById('deviozChatInput');
    const chatMessagesBody = document.getElementById('deviozChatMessagesBody');
    const chatContactName = document.getElementById('deviozChatContactName');
    const chatContactRole = document.getElementById('deviozChatContactRole');
    const chatContactAvatar = document.getElementById('deviozChatContactAvatar');
    const chatProjectBanner = document.getElementById('deviozChatProjectBanner');
    const chatProjectCode = document.getElementById('deviozChatProjectCode');
    const chatProjectName = document.getElementById('deviozChatProjectName');

    // Botones de Apertura de Chat
    const dockChatBtn = document.getElementById('deviozDockChatBtn');
    const topbarChatBtn = document.getElementById('topbarChatBtn');
    const projectOpenChatBtn = document.getElementById('projectOpenChatBtn');

    // Elementos de Videollamada Devioz Meet
    const videoModalEl = document.getElementById('deviozVideoModal');
    const videoContainer = document.getElementById('deviozVideoContainer');
    const dockVideoBtn = document.getElementById('deviozDockVideoBtn');
    const chatHeaderVideoBtn = document.getElementById('deviozChatHeaderVideoBtn');
    const projectLaunchVideoBtn = document.getElementById('projectLaunchVideoBtn');
    const copyMeetingLinkBtn = document.getElementById('copyMeetingLinkBtn');

    // Elementos de Solicitud de Llamada
    const dockCallBtn = document.getElementById('deviozDockCallBtn');
    const chatHeaderCallBtn = document.getElementById('deviozChatHeaderCallBtn');
    const projectRequestCallBtn = document.getElementById('projectRequestCallBtn');
    const requestCallModalEl = document.getElementById('deviozRequestCallModal');
    const requestCallForm = document.getElementById('deviozRequestCallForm');
    const callRequestReason = document.getElementById('callRequestReason');
    const callRequestQuickSelect = document.getElementById('callRequestQuickSelect');
    const callRequestFeedback = document.getElementById('callRequestFeedback');

    // Elementos de Notificaciones
    const notifCountBadge = document.getElementById('topbarNotificationsCount');
    const notifListEl = document.getElementById('deviozNotificationsList');
    const notifMarkReadBtn = document.getElementById('markAllNotificationsReadBtn');
    const dockChatBadge = document.getElementById('deviozDockChatBadge');
    const topbarChatBadge = document.getElementById('topbarChatBadge');

    // --------------------------------------------------------------------------
    // 1. CHAT DIRECTO 1 A 1
    // --------------------------------------------------------------------------

    function openChat(contactId = null, projectId = null) {
        if (!chatDrawer) return;
        activeContactId = contactId || activeContactId;
        activeProjectId = projectId || activeProjectId;

        chatDrawer.classList.add('active');
        if (chatBackdrop) chatBackdrop.classList.add('active');

        loadConversation(activeContactId, activeProjectId);

        if (!chatPollInterval) {
            // El SSE ya notifica mensajes nuevos al instante; el polling es solo respaldo.
            chatPollInterval = setInterval(() => {
                if (document.hidden) return;
                loadConversation(activeContactId, activeProjectId, false);
            }, 20000);
        }
    }

    function closeChat() {
        if (!chatDrawer) return;
        chatDrawer.classList.remove('active');
        if (chatBackdrop) chatBackdrop.classList.remove('active');
        if (chatPollInterval) {
            clearInterval(chatPollInterval);
            chatPollInterval = null;
        }
    }

    function loadConversation(contactId, projectId, showSpinner = true) {
        if (!chatMessagesBody) return;

        let queryParams = [];
        if (contactId) queryParams.push(`contact_id=${contactId}`);
        if (projectId) queryParams.push(`project_id=${projectId}`);
        const queryString = queryParams.length ? `?${queryParams.join('&')}` : '';

        if (showSpinner && chatMessagesBody.children.length === 0) {
            chatMessagesBody.innerHTML = `
                <div class="text-center py-4 text-muted small">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <div>Cargando conversación...</div>
                </div>`;
        }

        fetch(`${baseUrl}/api/chat/conversation${queryString}`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // Actualizar datos del interlocutor
                if (data.contact) {
                    activeContactId = data.contact.id;
                    if (chatContactName) chatContactName.textContent = data.contact.name;
                    if (chatContactRole) chatContactRole.textContent = data.contact.role_label;
                    if (chatContactAvatar) chatContactAvatar.src = data.contact.avatar;
                }

                // Actualizar banner de proyecto
                if (data.project && chatProjectBanner) {
                    chatProjectBanner.classList.remove('d-none');
                    if (chatProjectCode) chatProjectCode.textContent = data.project.code;
                    if (chatProjectName) chatProjectName.textContent = data.project.name;
                }

                // Guardar link de reunión
                if (data.call_room) {
                    currentCallRoomUrl = data.call_room.room_url;
                }

                // Renderizar mensajes
                renderMessages(data.messages);

                // Actualizar badges
                updateBadges(data.unread_total);
            })
            .catch(err => console.error('Error cargando chat:', err));
    }

    function renderMessages(messages) {
        if (!chatMessagesBody) return;

        if (!messages || messages.length === 0) {
            chatMessagesBody.innerHTML = `
                <div class="text-center py-5 text-muted small">
                    <div class="mb-2"><i class="fa-solid fa-comments fs-2 text-secondary opacity-50"></i></div>
                    <div class="fw-semibold">Canal Directo de Comunicación</div>
                    <p class="small text-secondary mb-0">Escribe tu primer mensaje o consulta técnica con la administración.</p>
                </div>`;
            return;
        }

        const isScrolledToBottom = (chatMessagesBody.scrollHeight - chatMessagesBody.clientHeight) <= (chatMessagesBody.scrollTop + 50);

        let html = '';
        messages.forEach(msg => {
            const wrapClass = msg.is_mine ? 'mine' : 'theirs';
            const alertClass = (msg.type === 'alert') ? 'bubble-alert' : '';

            html += `
                <div class="devioz-chat-bubble-wrap ${wrapClass}">
                    <div class="devioz-chat-bubble ${alertClass}">
                        ${escapeHtml(msg.body)}
                    </div>
                    <div class="devioz-chat-time">
                        ${msg.date_label} ${msg.is_mine ? (msg.is_read ? '<span class="text-info">✓✓</span>' : '<span>✓</span>') : ''}
                    </div>
                </div>`;
        });

        chatMessagesBody.innerHTML = html;

        if (isScrolledToBottom || !chatMessagesBody.dataset.scrolledOnce) {
            chatMessagesBody.scrollTop = chatMessagesBody.scrollHeight;
            chatMessagesBody.dataset.scrolledOnce = 'true';
        }
    }

    function sendChatMessage() {
        if (!chatInput) return;
        const text = chatInput.value.trim();
        if (!text) return;

        chatInput.value = '';

        fetch(`${baseUrl}/api/chat/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                receiver_id: activeContactId,
                project_id: activeProjectId,
                body: text,
                type: 'general',
                priority: 'normal'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadConversation(activeContactId, activeProjectId, false);
            }
        })
        .catch(err => console.error('Error enviando mensaje:', err));
    }

    // Eventos de apertura de chat
    if (dockChatBtn) dockChatBtn.addEventListener('click', () => openChat());
    if (topbarChatBtn) topbarChatBtn.addEventListener('click', () => openChat());

    if (chatBackdrop) chatBackdrop.addEventListener('click', closeChat);
    if (chatCloseBtn) chatCloseBtn.addEventListener('click', closeChat);

    if (projectOpenChatBtn) {
        projectOpenChatBtn.addEventListener('click', () => {
            const contactId = projectOpenChatBtn.getAttribute('data-contact-id');
            openChat(contactId);
        });
    }

    // Envío del chat
    if (chatForm) {
        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            sendChatMessage();
        });
    }

    if (chatInput) {
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage();
            }
        });
    }

    // Chips de preguntas frecuentes
    document.querySelectorAll('.devioz-chip-btn').forEach(chip => {
        chip.addEventListener('click', () => {
            if (chatInput) {
                chatInput.value = chip.getAttribute('data-chat-text') || '';
                chatInput.focus();
            }
        });
    });

    // --------------------------------------------------------------------------
    // 2. VIDEOLLAMADA HD DEVIOZ MEET (SIN SALIR DE LA PLATAFORMA)
    // --------------------------------------------------------------------------

    function launchDeviozVideoMeet(customRoomUrl = null) {
        if (!videoContainer || !videoModalEl) return;
        const roomUrl = customRoomUrl || currentCallRoomUrl || `https://meet.jit.si/Devioz-Meet-Room`;

        videoContainer.innerHTML = `
            <iframe
                src="${roomUrl}#config.prejoinPageEnabled=false&config.startWithAudioMuted=false&config.disableDeepLinking=true&config.toolbarButtons=%5B%22microphone%22%2C%22camera%22%2C%22desktop%22%2C%22chat%22%2C%22hangup%22%2C%22tileview%22%2C%22fullscreen%22%2C%22raisehand%22%5D"
                allow="camera; microphone; fullscreen; display-capture; autoplay"
                class="devioz-video-iframe">
            </iframe>`;

        const modal = bootstrap.Modal.getOrCreateInstance(videoModalEl);
        modal.show();
    }

    if (dockVideoBtn) {
        dockVideoBtn.addEventListener('click', () => launchDeviozVideoMeet());
    }

    if (chatHeaderVideoBtn) {
        chatHeaderVideoBtn.addEventListener('click', () => launchDeviozVideoMeet());
    }

    if (projectLaunchVideoBtn) {
        projectLaunchVideoBtn.addEventListener('click', () => {
            const pCode = projectLaunchVideoBtn.getAttribute('data-project-code') || 'DEV';
            const cleanCode = pCode.replace(/[^a-zA-Z0-9]/g, '');
            launchDeviozVideoMeet(`https://meet.jit.si/Devioz-Meet-${cleanCode}`);
        });
    }

    if (videoModalEl) {
        videoModalEl.addEventListener('hidden.bs.modal', () => {
            if (videoContainer) videoContainer.innerHTML = '';
        });
    }

    if (copyMeetingLinkBtn) {
        copyMeetingLinkBtn.addEventListener('click', () => {
            const url = currentCallRoomUrl || 'https://meet.jit.si/Devioz-Meet-Room';
            navigator.clipboard.writeText(url).then(() => {
                copyMeetingLinkBtn.innerHTML = '<i class="fa-solid fa-check me-1 text-success"></i> Copiado!';
                setTimeout(() => {
                    copyMeetingLinkBtn.innerHTML = '<i class="fa-solid fa-link me-1"></i> Copiar Link';
                }, 2200);
            });
        });
    }

    // --------------------------------------------------------------------------
    // 3. SOLICITUD DE LLAMADA / CONTACTO URGENTE
    // --------------------------------------------------------------------------

    function openRequestCallModal(contactId = null, projectId = null) {
        if (!requestCallModalEl) return;
        activeContactId = contactId || activeContactId;
        activeProjectId = projectId || activeProjectId;
        const modal = bootstrap.Modal.getOrCreateInstance(requestCallModalEl);
        modal.show();
    }

    if (dockCallBtn) dockCallBtn.addEventListener('click', () => openRequestCallModal());
    if (chatHeaderCallBtn) chatHeaderCallBtn.addEventListener('click', () => openRequestCallModal());

    if (projectRequestCallBtn) {
        projectRequestCallBtn.addEventListener('click', () => {
            const cId = projectRequestCallBtn.getAttribute('data-contact-id');
            const pId = projectRequestCallBtn.getAttribute('data-project-id');
            openRequestCallModal(cId, pId);
        });
    }

    if (callRequestQuickSelect && callRequestReason) {
        callRequestQuickSelect.addEventListener('change', () => {
            if (callRequestQuickSelect.value !== 'Otro motivo personalizado') {
                callRequestReason.value = callRequestQuickSelect.value;
            } else {
                callRequestReason.value = '';
                callRequestReason.focus();
            }
        });
    }

    if (requestCallForm) {
        requestCallForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!activeContactId) {
                if (callRequestFeedback) {
                    callRequestFeedback.className = 'small text-danger';
                    callRequestFeedback.textContent = 'Selecciona un contacto o inicia la solicitud desde un proyecto asignado.';
                }
                return;
            }
            const reason = callRequestReason ? callRequestReason.value.trim() : '';

            if (callRequestFeedback) {
                callRequestFeedback.className = 'small text-secondary';
                callRequestFeedback.textContent = 'Enviando invitación y creando la sala…';
            }
            fetch(`${baseUrl}/api/chat/request-call`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    receiver_id: activeContactId,
                    project_id: activeProjectId,
                    reason: reason || 'Revisión técnica urgente'
                })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success || !data.room_url) {
                    throw new Error(data.message || data.error || 'No se pudo enviar la solicitud.');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(requestCallModalEl);
                    if (modal) modal.hide();

                    currentCallRoomUrl = data.room_url;

                    // Abrir la sala de video de inmediato
                    launchDeviozVideoMeet(data.room_url);

                    // Si el chat estaba abierto, refrescar
                    if (chatDrawer && chatDrawer.classList.contains('active')) {
                        loadConversation(activeContactId, activeProjectId, false);
                    }
                }
            })
            .catch(err => {
                console.error('Error enviando solicitud de llamada:', err);
                if (callRequestFeedback) {
                    callRequestFeedback.className = 'small text-danger';
                    callRequestFeedback.textContent = err.message || 'No se pudo enviar la solicitud.';
                }
            });
        });
    }

    // --------------------------------------------------------------------------
    // 4. CENTRO DE NOTIFICACIONES EN VIVO (TOPBAR)
    // --------------------------------------------------------------------------

    function fetchNotifications() {
        fetch(`${baseUrl}/api/notifications`)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                updateBadges(data.unread_count);

                if (notifListEl) {
                    if (!data.notifications || data.notifications.length === 0) {
                        notifListEl.innerHTML = '<div class="p-3 text-center text-muted small">No tienes notificaciones pendientes.</div>';
                        return;
                    }

                    let html = '';
                    data.notifications.forEach(n => {
                        const urgentClass = n.is_urgent ? 'urgent' : '';
                        html += `
                            <div class="devioz-notification-item ${urgentClass}" data-contact-id="${n.contact_id || ''}" data-action="${n.action_url}">
                                <img src="${n.avatar}" class="rounded-circle border flex-shrink-0" width="34" height="34" style="object-fit: cover;">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold small text-truncate text-dark" style="font-size: 0.80rem;">${escapeHtml(n.title)}</div>
                                        <span class="text-muted" style="font-size: 0.65rem;">${n.time}</span>
                                    </div>
                                    <div class="text-secondary small text-truncate" style="font-size: 0.75rem;">${escapeHtml(n.body)}</div>
                                </div>
                            </div>`;
                    });

                    notifListEl.innerHTML = html;

                    // Click en notificación abre chat si aplica
                    notifListEl.querySelectorAll('.devioz-notification-item').forEach(item => {
                        item.addEventListener('click', () => {
                            const cId = item.getAttribute('data-contact-id');
                            const action = item.getAttribute('data-action');
                            if (action && action.startsWith('https://meet.jit.si/')) {
                                launchDeviozVideoMeet(action);
                            } else if (action === '#chat-open' || cId) {
                                openChat(cId);
                            } else if (action && !action.startsWith('#')) {
                                window.location.href = action;
                            }
                        });
                    });
                }
            })
            .catch(err => console.error('Error cargando notificaciones:', err));
    }

    function updateBadges(count) {
        const cnt = parseInt(count, 10) || 0;

        if (notifCountBadge) {
            if (cnt > 0) {
                notifCountBadge.textContent = cnt;
                notifCountBadge.classList.remove('d-none');
            } else {
                notifCountBadge.classList.add('d-none');
            }
        }

        if (dockChatBadge) {
            if (cnt > 0) {
                dockChatBadge.textContent = cnt;
                dockChatBadge.classList.remove('d-none');
            } else {
                dockChatBadge.classList.add('d-none');
            }
        }

        if (topbarChatBadge) {
            if (cnt > 0) {
                topbarChatBadge.textContent = cnt;
                topbarChatBadge.classList.remove('d-none');
            } else {
                topbarChatBadge.classList.add('d-none');
            }
        }
    }

    if (notifMarkReadBtn) {
        notifMarkReadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            fetch(`${baseUrl}/api/notifications/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        updateBadges(0);
                        if (notifListEl) {
                            notifListEl.innerHTML = '<div class="p-3 text-center text-muted small">Todas las notificaciones leídas.</div>';
                        }
                    }
                });
        });
    }

    // --------------------------------------------------------------------------
    // Motor en Tiempo Real (Devioz SSE)
    // --------------------------------------------------------------------------
    let sseSource = null;
    let lastMsgId = 0;

    function initSSE() {
        if (sseSource) sseSource.close();

        sseSource = new EventSource(appUrl('/api/chat/stream?last_msg_id=' + lastMsgId));

        sseSource.onmessage = function(event) {
            const data = JSON.parse(event.data);

            // Actualizar Badge global
            if (data.unread_count !== undefined) {
                if (dockChatBadge) {
                    if (data.unread_count > 0) {
                        dockChatBadge.textContent = data.unread_count;
                        dockChatBadge.classList.remove('d-none');
                    } else {
                        dockChatBadge.classList.add('d-none');
                    }
                }
            }

            if (data.messages && data.messages.length > 0) {
                lastMsgId = data.messages[data.messages.length - 1].id;

                // Si el chat esta abierto, recargarlo
                if (chatDrawer && chatDrawer.classList.contains('active')) {
                    loadConversation(activeContactId, activeProjectId, false);
                }

                // Actualizar notificaciones en la campana
                fetchNotifications();
            }
        };

        sseSource.onerror = function() {
            sseSource.close();
            setTimeout(initSSE, 5000); // Reconectar tras 5s
        };

        sseSource.addEventListener('close', function() {
            sseSource.close();
            initSSE();
        });
    }

    // Iniciar SSE
    initSSE();

    // Liberar la conexión PHP cuando la pestaña está oculta y reconectar al volver
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (sseSource) { sseSource.close(); sseSource = null; }
        } else if (!sseSource) {
            initSSE();
            fetchNotifications();
        }
    });

    window.addEventListener('beforeunload', () => {
        if (sseSource) {
            sseSource.close();
            sseSource = null;
        }
    });

    // --------------------------------------------------------------------------
    // 5. TEMPORIZADOR DE JORNADA (DEVIOZ WORKDAY TRACKER)
    // --------------------------------------------------------------------------

    const trackerToggleBtn = document.getElementById('deviozTrackerToggle');
    const trackerTimerText = document.getElementById('trackerTimerText');
    const trackerActionLabel = document.getElementById('trackerActionLabel');
    const copyTrackerHoursBtn = document.getElementById('copyTrackerHoursBtn');
    const hoursWorkedInput = document.getElementById('hours_worked');

    // Elementos del Popover Devioz Workday
    const popoverTimerText = document.getElementById('popoverTimerText');
    const popoverProgressBar = document.getElementById('popoverProgressBar');
    const popoverDailyProgressPercent = document.getElementById('popoverDailyProgressPercent');
    const workdayBadgeStatus = document.getElementById('workdayBadgeStatus');
    const workdayStatusSubtitle = document.getElementById('workdayStatusSubtitle');
    const workdayCheckinTimeText = document.getElementById('workdayCheckinTimeText');
    const workdayWeeklyHoursText = document.getElementById('workdayWeeklyHoursText');
    const popoverStartBtn = document.getElementById('popoverStartBtn');
    const popoverStartIcon = document.getElementById('popoverStartIcon');
    const popoverStartLabel = document.getElementById('popoverStartLabel');
    const popoverBreakBtn = document.getElementById('popoverBreakBtn');
    const popoverFinishBtn = document.getElementById('popoverFinishBtn');
    const workdayStandupInput = document.getElementById('workdayStandupInput');
    const workdayStandupSaveBtn = document.getElementById('workdayStandupSaveBtn');
    const workdayStandupSavedMsg = document.getElementById('workdayStandupSavedMsg');

    // Elementos del Popover de Perfil (Configuración de perfil)
    const popoverWorkdayClock = document.getElementById('popoverWorkdayClock');
    const popoverWorkdayLabel = document.getElementById('popoverWorkdayLabel');
    const profileWorkdayDot = document.getElementById('profileWorkdayDot');
    const popoverQuickWorkdayBtn = document.getElementById('popoverQuickWorkdayBtn');
    const popoverQuickWorkdayIcon = document.getElementById('popoverQuickWorkdayIcon');
    const popoverQuickWorkdayLabel = document.getElementById('popoverQuickWorkdayLabel');
    const popoverFinishWorkdayBtn = document.getElementById('popoverFinishWorkdayBtn');

    let trackerRunning = localStorage.getItem('devioz_tracker_running') === 'true';
    let trackerSeconds = parseInt(localStorage.getItem('devioz_tracker_seconds') || '0', 10);
    let trackerTimerInterval = null;

    function formatTrackerTime(totalSec) {
        const hrs = Math.floor(totalSec / 3600);
        const mins = Math.floor((totalSec % 3600) / 60);
        const secs = totalSec % 60;
        return [
            hrs.toString().padStart(2, '0'),
            mins.toString().padStart(2, '0'),
            secs.toString().padStart(2, '0')
        ].join(':');
    }

    function updateTrackerUI() {
        const timeFormatted = formatTrackerTime(trackerSeconds);
        if (trackerTimerText) {
            trackerTimerText.textContent = timeFormatted;
        }
        if (popoverTimerText) {
            popoverTimerText.textContent = timeFormatted;
        }
        if (popoverWorkdayClock) {
            popoverWorkdayClock.textContent = timeFormatted;
        }

        // Progreso hacia la meta diaria de 8 horas (28,800 segundos)
        const progressPercent = Math.min(100, Math.round((trackerSeconds / 28800) * 100));
        if (popoverProgressBar) {
            popoverProgressBar.style.width = progressPercent + '%';
        }
        if (popoverDailyProgressPercent) {
            popoverDailyProgressPercent.textContent = progressPercent + '%';
        }

        // Acumulado de horas
        const totalHrs = (trackerSeconds / 3600).toFixed(1);
        if (workdayWeeklyHoursText) {
            workdayWeeklyHoursText.textContent = totalHrs + ' / 40 hrs';
        }

        // Hora de marcación de ingreso
        const checkinTime = localStorage.getItem('devioz_workday_checkin') || '—';
        if (workdayCheckinTimeText) {
            workdayCheckinTimeText.textContent = checkinTime;
        }

        if (trackerRunning) {
            if (trackerToggleBtn) trackerToggleBtn.classList.add('running');
            if (trackerActionLabel) trackerActionLabel.textContent = 'Pausar';

            if (workdayBadgeStatus) {
                workdayBadgeStatus.textContent = 'En Turno';
                workdayBadgeStatus.className = 'badge bg-success';
            }
            if (workdayStatusSubtitle) {
                workdayStatusSubtitle.textContent = 'Turno activo de ingeniería';
            }
            if (popoverStartBtn) {
                popoverStartBtn.className = 'btn btn-sm btn-warning d-flex align-items-center justify-content-center gap-2 py-2';
            }
            if (popoverStartLabel) {
                popoverStartLabel.textContent = 'Pausar Jornada';
            }
            if (popoverStartIcon) {
                popoverStartIcon.className = 'fa-solid fa-pause';
            }

            // Elementos del Popover de Perfil
            if (popoverWorkdayLabel) {
                popoverWorkdayLabel.innerHTML = '<span class="text-success fw-semibold">En el trabajo</span>';
            }
            if (profileWorkdayDot) {
                profileWorkdayDot.style.backgroundColor = '#10B981';
                profileWorkdayDot.style.boxShadow = '0 0 6px rgba(16, 185, 129, 0.7)';
            }
            if (popoverQuickWorkdayBtn) {
                popoverQuickWorkdayBtn.style.background = '#F59E0B';
                popoverQuickWorkdayBtn.title = 'Pausar jornada';
            }
            if (popoverQuickWorkdayLabel) {
                popoverQuickWorkdayLabel.textContent = 'Pausar';
            }
            if (popoverQuickWorkdayIcon) {
                popoverQuickWorkdayIcon.className = 'fa-solid fa-pause';
            }
            if (popoverFinishWorkdayBtn) {
                popoverFinishWorkdayBtn.classList.remove('d-none');
            }
        } else {
            if (trackerToggleBtn) trackerToggleBtn.classList.remove('running');
            if (trackerActionLabel) {
                trackerActionLabel.textContent = (trackerSeconds > 0) ? 'Continuar' : 'Iniciar';
            }

            if (workdayBadgeStatus) {
                if (trackerSeconds > 0) {
                    workdayBadgeStatus.textContent = 'En Pausa';
                    workdayBadgeStatus.className = 'badge bg-warning text-dark';
                } else {
                    workdayBadgeStatus.textContent = 'Detenido';
                    workdayBadgeStatus.className = 'badge bg-secondary-subtle text-secondary';
                }
            }
            if (workdayStatusSubtitle) {
                workdayStatusSubtitle.textContent = (trackerSeconds > 0) ? 'Pausa activa / refrigerio' : 'Turno no iniciado';
            }
            if (popoverStartBtn) {
                popoverStartBtn.className = 'btn btn-sm btn-brand d-flex align-items-center justify-content-center gap-2 py-2';
            }
            if (popoverStartLabel) {
                popoverStartLabel.textContent = (trackerSeconds > 0) ? 'Continuar Jornada' : 'Iniciar Jornada';
            }
            if (popoverStartIcon) {
                popoverStartIcon.className = 'fa-solid fa-play';
            }

            // Elementos del Popover de Perfil
            if (trackerSeconds > 0) {
                if (popoverWorkdayLabel) {
                    popoverWorkdayLabel.innerHTML = '<span class="text-warning fw-semibold">En pausa</span>';
                }
                if (profileWorkdayDot) {
                    profileWorkdayDot.style.backgroundColor = '#F59E0B';
                    profileWorkdayDot.style.boxShadow = 'none';
                }
                if (popoverQuickWorkdayBtn) {
                    popoverQuickWorkdayBtn.style.background = '#059669';
                    popoverQuickWorkdayBtn.title = 'Continuar jornada';
                }
                if (popoverQuickWorkdayLabel) {
                    popoverQuickWorkdayLabel.textContent = 'Continuar';
                }
                if (popoverQuickWorkdayIcon) {
                    popoverQuickWorkdayIcon.className = 'fa-solid fa-play';
                }
                if (popoverFinishWorkdayBtn) {
                    popoverFinishWorkdayBtn.classList.remove('d-none');
                }
            } else {
                if (popoverWorkdayLabel) {
                    popoverWorkdayLabel.textContent = 'Fuera del trabajo';
                }
                if (profileWorkdayDot) {
                    profileWorkdayDot.style.backgroundColor = '#94A3B8';
                    profileWorkdayDot.style.boxShadow = 'none';
                }
                if (popoverQuickWorkdayBtn) {
                    popoverQuickWorkdayBtn.style.background = '#059669';
                    popoverQuickWorkdayBtn.title = 'Iniciar jornada';
                }
                if (popoverQuickWorkdayLabel) {
                    popoverQuickWorkdayLabel.textContent = 'Iniciar';
                }
                if (popoverQuickWorkdayIcon) {
                    popoverQuickWorkdayIcon.className = 'fa-solid fa-play';
                }
                if (popoverFinishWorkdayBtn) {
                    popoverFinishWorkdayBtn.classList.add('d-none');
                }
            }
        }
    }

    function startTracker() {
        trackerRunning = true;
        localStorage.setItem('devioz_tracker_running', 'true');

        // Registrar hora de ingreso si no existe aún hoy
        if (!localStorage.getItem('devioz_workday_checkin')) {
            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            localStorage.setItem('devioz_workday_checkin', timeStr);
        }

        updateTrackerUI();
        if (!trackerTimerInterval) {
            trackerTimerInterval = setInterval(() => {
                trackerSeconds++;
                localStorage.setItem('devioz_tracker_seconds', trackerSeconds.toString());
                updateTrackerUI();
            }, 1000);
        }
    }

    function pauseTracker() {
        trackerRunning = false;
        localStorage.setItem('devioz_tracker_running', 'false');
        if (trackerTimerInterval) {
            clearInterval(trackerTimerInterval);
            trackerTimerInterval = null;
        }
        updateTrackerUI();
    }

    function toggleTracker() {
        if (trackerRunning) {
            pauseTracker();
        } else {
            startTracker();
        }
    }

    // Inicializar listeners del Popover y del Timer
    if (popoverWorkdayClock || trackerToggleBtn) {
        updateTrackerUI();
        if (trackerRunning) {
            startTracker();
        }
    }

    if (popoverQuickWorkdayBtn) {
        popoverQuickWorkdayBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleTracker();
        });
    }

    if (popoverFinishWorkdayBtn) {
        popoverFinishWorkdayBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (confirm('¿Confirmas que deseas finalizar tu jornada laboral de hoy? Se registrará el tiempo acumulado.')) {
                pauseTracker();
                localStorage.removeItem('devioz_workday_checkin');
                trackerSeconds = 0;
                localStorage.setItem('devioz_tracker_seconds', '0');
                updateTrackerUI();
            }
        });
    }

    if (popoverStartBtn) {
        popoverStartBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleTracker();
        });
    }

    if (popoverBreakBtn) {
        popoverBreakBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (trackerRunning) {
                pauseTracker();
            }
            if (workdayStatusSubtitle) {
                workdayStatusSubtitle.textContent = 'Pausa de refrigerio activa';
            }
            if (workdayBadgeStatus) {
                workdayBadgeStatus.textContent = 'Refrigerio';
                workdayBadgeStatus.className = 'badge bg-warning text-dark';
            }
        });
    }

    if (popoverFinishBtn) {
        popoverFinishBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (confirm('¿Confirmas que deseas finalizar tu jornada laboral de hoy? Se registrará el tiempo acumulado.')) {
                pauseTracker();
                localStorage.removeItem('devioz_workday_checkin');
                trackerSeconds = 0;
                localStorage.setItem('devioz_tracker_seconds', '0');
                updateTrackerUI();
            }
        });
    }

    // Persistencia del Standup diario
    if (workdayStandupInput) {
        workdayStandupInput.value = localStorage.getItem('devioz_workday_standup') || '';

        if (workdayStandupSaveBtn) {
            workdayStandupSaveBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const val = workdayStandupInput.value.trim();
                localStorage.setItem('devioz_workday_standup', val);
                if (workdayStandupSavedMsg) {
                    workdayStandupSavedMsg.textContent = '✓ Standup guardado correctamente';
                    workdayStandupSavedMsg.style.color = '#10B981';
                    setTimeout(() => {
                        workdayStandupSavedMsg.textContent = '';
                    }, 2500);
                }
            });
        }
    }

    if (copyTrackerHoursBtn && hoursWorkedInput) {
        copyTrackerHoursBtn.addEventListener('click', () => {
            // Convertir segundos a horas redondeadas al medio paso más cercano (mínimo 0.5)
            const hrs = trackerSeconds / 3600;
            const rounded = Math.max(0.5, Math.round(hrs * 2) / 2);
            hoursWorkedInput.value = rounded;
            copyTrackerHoursBtn.innerHTML = `<i class="fa-solid fa-check text-success me-1"></i> ${rounded} hrs copiadas`;
            setTimeout(() => {
                copyTrackerHoursBtn.innerHTML = '<i class="fa-solid fa-stopwatch me-1"></i> Copiar de Jornada Devioz';
            }, 2500);
        });
    }

    // --------------------------------------------------------------------------
    // 6. ALTERNADOR DE VISTAS DE HITOS (LISTA, GANTT, CALENDARIO)
    // --------------------------------------------------------------------------

    const viewSwitcher = document.getElementById('milestoneViewSwitcher');
    const timelineView = document.getElementById('milestonesTimelineView');
    const ganttView = document.getElementById('milestonesGanttView');
    const calendarView = document.getElementById('milestonesCalendarView');

    if (viewSwitcher && timelineView && ganttView && calendarView) {
        viewSwitcher.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetView = btn.getAttribute('data-view');

                viewSwitcher.querySelectorAll('button').forEach(b => {
                    b.classList.remove('active', 'text-white');
                    b.classList.add('text-secondary');
                });
                btn.classList.add('active');
                btn.classList.remove('text-secondary');

                timelineView.classList.add('d-none');
                ganttView.classList.add('d-none');
                calendarView.classList.add('d-none');

                if (targetView === 'gantt') {
                    ganttView.classList.remove('d-none');
                } else if (targetView === 'calendar') {
                    calendarView.classList.remove('d-none');
                } else {
                    timelineView.classList.remove('d-none');
                }
            });
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}

/**
 * 10. Búsqueda Predictiva Reactiva y Selección Automática en Tiempo Real
 * Filtra en vivo en /gallery, /team y /projects, autoselecciona la mejor coincidencia y permite abrir con Enter
 */
function initReactiveLiveSearch() {
    const searchInputs = document.querySelectorAll('input[name="q"], .reactive-search-input');
    if (!searchInputs.length) return;

    searchInputs.forEach(input => {
        const container = document.querySelector('.row.g-4, .row.g-3');
        if (!container) return;

        const cards = Array.from(container.querySelectorAll('.col-md-6, .col-lg-4, .col-xl-4'));
        if (!cards.length) return;

        let debounceTimeout = null;

        // Auto-seleccionar y filtrar en tiempo real
        input.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                const query = input.value.trim().toLowerCase();
                let bestMatchCard = null;
                let highestScore = -1;
                let visibleCount = 0;

                cards.forEach(cardCol => {
                    cardCol.classList.remove('is-auto-selected');
                    const text = (cardCol.textContent || '').toLowerCase();

                    if (!query) {
                        cardCol.classList.remove('d-none');
                        visibleCount++;
                        return;
                    }

                    if (text.includes(query)) {
                        cardCol.classList.remove('d-none');
                        visibleCount++;

                        // Relevancia: coincidencia en títulos/nombres
                        const titleEl = cardCol.querySelector('h4, h5, h6, .card-title, .fw-bold');
                        const titleText = titleEl ? (titleEl.textContent || '').toLowerCase() : '';
                        let score = 1;
                        if (titleText.startsWith(query)) score = 10;
                        else if (titleText.includes(query)) score = 5;

                        if (score > highestScore) {
                            highestScore = score;
                            bestMatchCard = cardCol;
                        }
                    } else {
                        cardCol.classList.add('d-none');
                    }
                });

                // Auto-seleccionar visualmente el resultado más relevante
                if (bestMatchCard && query) {
                    bestMatchCard.classList.add('is-auto-selected');
                }

                // Manejo de mensaje si no hay resultados
                let noResultEl = container.querySelector('.search-no-results');
                if (query && visibleCount === 0) {
                    if (!noResultEl) {
                        noResultEl = document.createElement('div');
                        noResultEl.className = 'col-12 search-no-results';
                        container.appendChild(noResultEl);
                    }
                    noResultEl.innerHTML = `<i class="fa-solid fa-magnifying-glass mb-2 fs-4 text-secondary"></i><div class="fw-semibold">No se encontraron resultados para "${input.value}"</div><div class="small text-muted">Prueba con otro término o tecnología.</div>`;
                    noResultEl.classList.remove('d-none');
                } else if (noResultEl) {
                    noResultEl.classList.add('d-none');
                }
            }, 80);
        });

        // Al presionar Enter, abrir directamente la mejor coincidencia sin esperar recarga
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const autoSelected = container.querySelector('.is-auto-selected');
                if (autoSelected) {
                    e.preventDefault();
                    const targetLink = autoSelected.querySelector('a.btn, a[href]');
                    if (targetLink && targetLink.href) {
                        window.location.href = targetLink.href;
                    }
                }
            }
        });
    });
}

/**
 * 11. Previsualización Automática de Enlaces (Open Graph Previews)
 * Detecta cuando un usuario escribe o pega una URL y genera tarjeta visual interactiva
 */
function initLinkPreviewListeners() {
    const urlInputs = document.querySelectorAll('input[type="url"], input[data-link-preview], #repository_url, #live_url, #video_url');
    if (!urlInputs.length) return;

    urlInputs.forEach(input => {
        let debounceTimer = null;
        let previewContainer = input.parentElement.querySelector('.link-preview-slot');
        if (!previewContainer) {
            previewContainer = document.createElement('div');
            previewContainer.className = 'link-preview-slot';
            input.parentElement.appendChild(previewContainer);
        }

        function checkAndPreview() {
            const rawUrl = input.value.trim();
            if (!rawUrl || !/^https?:\/\//i.test(rawUrl)) {
                previewContainer.innerHTML = '';
                return;
            }

            previewContainer.innerHTML = `
                <div class="d-flex align-items-center gap-2 py-2 text-muted small">
                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    <span>Obteniendo tarjeta de vista previa...</span>
                </div>
            `;

            fetch(appUrl(`/api/link-preview?url=${encodeURIComponent(rawUrl)}`))
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        previewContainer.innerHTML = '';
                        return;
                    }

                    function esc(s) {
                        if (!s) return '';
                        return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                    }

                    previewContainer.innerHTML = `
                        <a href="${esc(data.url)}" target="_blank" rel="noopener noreferrer" class="link-preview-card">
                            ${data.image ? `<img src="${esc(data.image)}" alt="Preview" class="link-preview-image" loading="lazy" onerror="this.style.display='none'">` : ''}
                            <div class="link-preview-body">
                                <div class="link-preview-site">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    <span>${esc(data.site_name || 'Enlace Externo')}</span>
                                </div>
                                <div class="link-preview-title text-truncate">${esc(data.title || data.url)}</div>
                                <div class="link-preview-desc">${esc(data.description || 'Recurso oficial vinculado')}</div>
                            </div>
                        </a>
                    `;
                })
                .catch(() => {
                    previewContainer.innerHTML = '';
                });
        }

        input.addEventListener('paste', () => {
            setTimeout(checkAndPreview, 150);
        });

        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(checkAndPreview, 350);
        });

        if (input.value.trim().startsWith('http')) {
            checkAndPreview();
        }
    });
}

/**
 * ==============================================================================
 * Devioz SIDEPANEL SLIDER (CAJÓN LATERAL DESLIZANTE)
 * ==============================================================================
 */
function initSidePanel() {
    const backdrop = document.getElementById('deviozSidepanelBackdrop');
    const slider = document.getElementById('deviozSidepanelSlider');
    const closeBtn = document.getElementById('spCloseBtn');

    if (!backdrop || !slider) return;

    window.DeviozSidePanel = {
        isOpen: false,
        activeProjectId: null,

        open: function(projectId) {
            if (!projectId) return;
            this.activeProjectId = projectId;
            this.isOpen = true;

            backdrop.classList.add('active');
            slider.classList.add('active');
            slider.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            const loading = document.getElementById('spLoadingState');
            const content = document.getElementById('spContentState');
            if (loading) loading.classList.remove('d-none');
            if (content) content.classList.add('d-none');

            const iframe = document.getElementById('spVideoIframe');
            if (iframe) iframe.src = '';

            fetch(appUrl(`/api/projects/${projectId}/preview`))
                .then(res => {
                    if (!res.ok) throw new Error('Error al consultar proyecto: ' + res.status);
                    return res.json();
                })
                .then(res => {
                    if (!res.success || !res.data) throw new Error(res.error || 'Datos no disponibles');
                    this.render(res.data);
                })
                .catch(err => {
                    console.error('[Devioz SidePanel] Error:', err);
                    if (loading) loading.classList.add('d-none');
                    if (content) {
                        content.classList.remove('d-none');
                        content.innerHTML = `
                            <div class="alert alert-danger my-4 text-center">
                                <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                                <strong>No se pudo cargar la vista previa del proyecto.</strong>
                                <div class="small mt-1 text-muted">${err.message || 'Error de conexión'}</div>
                            </div>
                        `;
                    }
                });
        },

        render: function(p) {
            const loading = document.getElementById('spLoadingState');
            const content = document.getElementById('spContentState');

            const codeEl = document.getElementById('spProjectCode');
            if (codeEl) codeEl.textContent = '#' + (p.code || 'DEV-000');

            const badgeEl = document.getElementById('spProjectStatusBadge');
            if (badgeEl) badgeEl.innerHTML = p.status_badge_html || '';

            const clientEl = document.getElementById('spClientNameText');
            if (clientEl) clientEl.textContent = p.client_name || 'Cliente Corporativo';

            const fullLink = document.getElementById('spFullPageLink');
            if (fullLink) fullLink.href = p.url;
            const primaryBtn = document.getElementById('spPrimaryActionBtn');
            if (primaryBtn) primaryBtn.href = p.url;

            const titleEl = document.getElementById('spProjectTitle');
            if (titleEl) titleEl.textContent = p.name;

            const stackEl = document.getElementById('spTechStack');
            if (stackEl) {
                stackEl.innerHTML = '';
                if (Array.isArray(p.tech_stack) && p.tech_stack.length > 0) {
                    p.tech_stack.forEach(tech => {
                        const span = document.createElement('span');
                        span.className = 'badge text-dark border';
                        span.style.background = '#F1F5F9';
                        span.style.fontSize = '0.72rem';
                        span.style.borderColor = '#DBE2E8';
                        span.textContent = tech;
                        stackEl.appendChild(span);
                    });
                }
            }

            const progressText = document.getElementById('spProgressText');
            if (progressText) progressText.textContent = `${p.progress || 0}%`;
            const progressBar = document.getElementById('spProgressBar');
            if (progressBar) progressBar.style.width = `${p.progress || 0}%`;

            const scoreText = document.getElementById('spScoreText');
            if (scoreText) scoreText.textContent = p.final_score ? `${p.final_score}/100` : 'En Auditoría';
            const scoreBadge = document.getElementById('spScoreBadge');
            if (scoreBadge) {
                if (p.final_score) {
                    scoreBadge.textContent = p.final_score >= 80 ? 'ISO 25010 PASS' : 'REVISIONES QA';
                    scoreBadge.className = p.final_score >= 80 ? 'badge bg-success-subtle text-success p-0 px-1 font-monospace' : 'badge bg-warning-subtle text-warning p-0 px-1 font-monospace';
                } else {
                    scoreBadge.textContent = 'PENDIENTE';
                    scoreBadge.className = 'badge bg-secondary-subtle text-secondary p-0 px-1 font-monospace';
                }
            }

            const sedeText = document.getElementById('spSedeText');
            if (sedeText) sedeText.textContent = p.sede || 'Cercado Central';

            const videoWrap = document.getElementById('spVideoWrapper');
            const videoIframe = document.getElementById('spVideoIframe');
            if (videoWrap && videoIframe) {
                if (p.video_url) {
                    videoIframe.src = p.video_url;
                    videoWrap.classList.remove('d-none');
                } else {
                    videoIframe.src = '';
                    videoWrap.classList.add('d-none');
                }
            }

            const devWrap = document.getElementById('spDeveloperWrapper');
            if (devWrap) {
                if (p.developer) {
                    devWrap.classList.remove('d-none');
                    const devAvatar = document.getElementById('spDevAvatar');
                    if (devAvatar) devAvatar.src = p.developer.avatar || '';
                    const devName = document.getElementById('spDevName');
                    if (devName) devName.textContent = p.developer.name || 'Desarrollador Asignado';
                    const devEmail = document.getElementById('spDevEmail');
                    if (devEmail) devEmail.textContent = p.developer.email || '';
                    const devSede = document.getElementById('spDevSede');
                    if (devSede) devSede.textContent = p.developer.sede || p.sede || 'Devioz Hub';
                } else {
                    devWrap.classList.add('d-none');
                }
            }

            const descEl = document.getElementById('spDescriptionContent');
            if (descEl) {
                descEl.innerHTML = p.description || '<p class="text-muted">Sin descripción detallada registrada.</p>';
            }

            const milestonesWrap = document.getElementById('spMilestonesContainer');
            if (milestonesWrap) {
                milestonesWrap.innerHTML = '';
                if (Array.isArray(p.milestones) && p.milestones.length > 0) {
                    p.milestones.forEach(m => {
                        const item = document.createElement('div');
                        const statusClass = m.status === 'completed' ? 'completed' : (m.status === 'in_progress' ? 'in_progress' : 'pending');
                        const iconHtml = m.status === 'completed'
                            ? '<i class="fa-solid fa-circle-check text-success me-2"></i>'
                            : (m.status === 'in_progress' ? '<i class="fa-solid fa-spinner fa-spin text-primary me-2"></i>' : '<i class="fa-regular fa-circle text-secondary me-2"></i>');

                        const badgeStatus = m.status === 'completed'
                            ? '<span class="badge bg-success-subtle text-success" style="font-size: 0.65rem;">Completado</span>'
                            : (m.status === 'in_progress' ? '<span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">En Curso</span>' : '<span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">Pendiente</span>');

                        item.className = `sidepanel-milestone-item ${statusClass}`;
                        item.innerHTML = `
                            <div class="d-flex align-items-center min-w-0">
                                ${iconHtml}
                                <span class="fw-semibold text-dark text-truncate">${m.name}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="text-muted font-monospace" style="font-size: 0.7rem;">${m.due_date || 'Sin fecha'}</span>
                                ${badgeStatus}
                            </div>
                        `;
                        milestonesWrap.appendChild(item);
                    });
                } else {
                    milestonesWrap.innerHTML = `
                        <div class="p-3 text-center text-muted small border rounded" style="background: #FAFAFA;">
                            <i class="fa-solid fa-clipboard-list me-1"></i> No se han registrado hitos para este ciclo SDLC.
                        </div>
                    `;
                }
            }

            const repoLink = document.getElementById('spRepoLink');
            if (repoLink) {
                if (p.repository_url) {
                    repoLink.href = p.repository_url;
                    repoLink.classList.remove('d-none');
                } else {
                    repoLink.classList.add('d-none');
                }
            }
            const demoLink = document.getElementById('spDemoLink');
            if (demoLink) {
                if (p.live_url) {
                    demoLink.href = p.live_url;
                    demoLink.classList.remove('d-none');
                } else {
                    demoLink.classList.add('d-none');
                }
            }

            if (loading) loading.classList.add('d-none');
            if (content) content.classList.remove('d-none');
        },

        close: function() {
            if (!this.isOpen) return;
            this.isOpen = false;
            this.activeProjectId = null;

            backdrop.classList.remove('active');
            slider.classList.remove('active');
            slider.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';

            const iframe = document.getElementById('spVideoIframe');
            if (iframe) iframe.src = '';
        }
    };

    if (closeBtn) closeBtn.addEventListener('click', () => window.DeviozSidePanel.close());
    backdrop.addEventListener('click', () => window.DeviozSidePanel.close());

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && window.DeviozSidePanel && window.DeviozSidePanel.isOpen) {
            window.DeviozSidePanel.close();
        }
    });

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-sidepanel-project]');
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();
            const pid = trigger.getAttribute('data-sidepanel-project');
            if (pid && window.DeviozSidePanel) {
                window.DeviozSidePanel.open(pid);
            }
        }
    });
}

// ==============================================================================
// 10. CONTROLADOR DEL MÓDULO MESSENGER & CHATS Devioz (/chat & /online)
// ==============================================================================
function initDeviozMessenger() {
    const messengerWrapper = document.querySelector('.devioz-messenger-wrapper');
    if (!messengerWrapper) return;

    let activeChannelType = 'copilot';
    let activeContactId = null;

    const chatItems = document.querySelectorAll('.devioz-chat-item');
    const mainEl = document.getElementById('deviozChatMain');
    const chatTitle = document.getElementById('activeChatTitle');
    const chatSubtitle = document.getElementById('activeChatSubtitle');
    const chatAvatarImg = document.getElementById('activeChatAvatarImg');
    const chatBotBadge = document.getElementById('activeChatBotBadge');
    const composerForm = document.getElementById('deviozComposerForm');
    const messageInput = document.getElementById('deviozMessageInput');
    const actionStatus = document.getElementById('deviozChatActionStatus');
    const searchInput = document.getElementById('deviozChatSearchInput');
    const searchClear = document.getElementById('deviozChatSearchClear');
    const videocallBtn = document.getElementById('chatHeaderVideocallBtn');
    const directMessagesContainer = document.getElementById('directMessagesContainer');
    const directLoadingIndicator = document.getElementById('directLoadingIndicator');
    const railButtons = document.querySelectorAll('.ui-rail-btn[data-rail-filter]');
    const categoryHeader = document.querySelector('.devioz-chat-category-header');
    const chipButtons = document.querySelectorAll('.ui-msg-chip[data-read-filter]');

    function setActionStatus(message, isError = false) {
        if (!actionStatus) return;
        actionStatus.textContent = message;
        actionStatus.className = `small px-3 pt-2 ${isError ? 'text-danger' : 'text-secondary'}`;
    }

    function switchChatView(type, contactId = null, title = '', subtitle = '', avatarUrl = '', isBot = false) {
        activeChannelType = type;
        activeContactId = contactId;
        setActionStatus('');

        if (mainEl) mainEl.classList.remove('is-empty');

        document.querySelectorAll('.chat-view-container').forEach(el => el.classList.add('d-none'));

        if (chatTitle) chatTitle.innerHTML = title;
        if (chatSubtitle) chatSubtitle.textContent = subtitle;
        if (chatAvatarImg && avatarUrl) chatAvatarImg.src = avatarUrl;
        if (chatBotBadge) {
            if (isBot) chatBotBadge.classList.remove('d-none');
            else chatBotBadge.classList.add('d-none');
        }

        if (type === 'copilot') {
            const v = document.getElementById('viewCopilot');
            if (v) v.classList.remove('d-none');
        } else if (type === 'notes') {
            const v = document.getElementById('viewNotes');
            if (v) v.classList.remove('d-none');
        } else if (type === 'news') {
            const v = document.getElementById('viewNews');
            if (v) v.classList.remove('d-none');
        } else if (type === 'general') {
            const v = document.getElementById('viewGeneral');
            if (v) v.classList.remove('d-none');
        } else if (type === 'direct') {
            const v = document.getElementById('viewDirect');
            if (v) v.classList.remove('d-none');
            loadDirectConversation(contactId);
        }

        const stream = document.getElementById('deviozChatStream');
        if (stream) stream.scrollTop = stream.scrollHeight;
    }

    function loadDirectConversation(contactId) {
        if (!contactId || !directMessagesContainer) return;
        if (directLoadingIndicator) directLoadingIndicator.classList.remove('d-none');

        return fetch(appUrl(`/api/chat/conversation?contact_id=${encodeURIComponent(contactId)}`))
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || data.error || 'No se pudo cargar la conversación.');
                }
                return data;
            })
            .then(data => {
                if (directLoadingIndicator) directLoadingIndicator.classList.add('d-none');
                directMessagesContainer.innerHTML = '';
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(m => {
                        appendDirectBubble(m.body, m.is_mine, m.date_label);
                    });
                } else {
                    directMessagesContainer.innerHTML = `
                        <div class="text-center py-5 text-muted small">
                            <i class="fa-regular fa-comment-dots fs-3 mb-2 d-block text-secondary"></i>
                            <div>No hay mensajes anteriores con este contacto.</div>
                            <div class="text-secondary" style="font-size: 0.70rem;">Envía un mensaje para comenzar la conversación segura.</div>
                        </div>`;
                }
                const stream = document.getElementById('deviozChatStream');
                if (stream) stream.scrollTop = stream.scrollHeight;
            })
            .catch(err => {
                console.error(err);
                if (directLoadingIndicator) directLoadingIndicator.classList.add('d-none');
                directMessagesContainer.innerHTML = `
                    <div class="alert alert-danger small m-3" role="alert">
                        No se pudo cargar esta conversación. ${escapeHtml(err.message)}
                    </div>`;
            });
    }

    function appendDirectBubble(text, isMine, time) {
        if (!directMessagesContainer) return;
        const bubble = document.createElement('div');
        bubble.className = `devioz-msg-bubble ${isMine ? 'outgoing' : 'incoming'}`;
        bubble.innerHTML = `
            <div class="msg-content">
                <div class="msg-body">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
                <div class="msg-footer text-end">
                    <span class="msg-time">${time || 'Ahora'}</span>
                </div>
            </div>`;
        directMessagesContainer.appendChild(bubble);
        const stream = document.getElementById('deviozChatStream');
        if (stream) stream.scrollTop = stream.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Click en item del listado de chats
    chatItems.forEach(item => {
        item.addEventListener('click', () => {
            chatItems.forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            const type = item.getAttribute('data-channel-type');
            const contactId = item.getAttribute('data-contact-id');
            const title = item.getAttribute('data-channel-title') || '';
            const subtitle = item.getAttribute('data-channel-subtitle') || '';
            const avatar = item.getAttribute('data-contact-avatar') || (item.querySelector('img') ? item.querySelector('img').src : '');
            const isBot = (type === 'copilot');

            switchChatView(type, contactId, title, subtitle, avatar, isBot);
        });
    });

    // Filtros del Rail Vertical (All / CoPilot / Canales / Equipo / Notas)
    railButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            railButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.getAttribute('data-rail-filter');

            chatItems.forEach(item => {
                const group = item.getAttribute('data-rail-group') || item.getAttribute('data-channel-type');
                if (filter === 'all') {
                    item.classList.remove('ui-hidden');
                    item.style.display = 'flex';
                } else if (filter === group) {
                    item.classList.remove('ui-hidden');
                    item.style.display = 'flex';
                } else {
                    item.classList.add('ui-hidden');
                    item.style.display = 'none';
                }
            });

            if (categoryHeader) {
                categoryHeader.style.display = (filter === 'all' || filter === 'direct') ? 'flex' : 'none';
            }

            if (filter === 'copilot') {
                const copilotItem = document.querySelector('.devioz-chat-item[data-channel-type="copilot"]');
                if (copilotItem) copilotItem.click();
            } else if (filter === 'notes') {
                const notesItem = document.querySelector('.devioz-chat-item[data-channel-type="notes"]');
                if (notesItem) notesItem.click();
            }
        });
    });

    // Chips All / Unread
    chipButtons.forEach(chip => {
        chip.addEventListener('click', () => {
            chipButtons.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const readFilter = chip.getAttribute('data-read-filter');

            chatItems.forEach(item => {
                if (item.classList.contains('ui-hidden')) return;
                if (readFilter === 'all') {
                    item.style.display = 'flex';
                } else if (readFilter === 'unread') {
                    const hasBadge = item.querySelector('.badge-devioz-bot, .badge, .ui-counter') !== null;
                    item.style.display = hasBadge ? 'flex' : 'none';
                }
            });
        });
    });

    // Botones del Estado Vacío
    document.querySelectorAll('[data-open-channel="copilot"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const copilotItem = document.querySelector('.devioz-chat-item[data-channel-type="copilot"]');
            if (copilotItem) copilotItem.click();
        });
    });

    const emptyNewChatBtn = document.getElementById('uiEmptyNewChatBtn');
    if (emptyNewChatBtn) {
        emptyNewChatBtn.addEventListener('click', () => {
            const el = document.getElementById('newChatModal');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    }

    // Buscador en lista de chats
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            if (searchClear) {
                if (query.length > 0) searchClear.classList.remove('d-none');
                else searchClear.classList.add('d-none');
            }
            chatItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    if (searchClear && searchInput) {
        searchClear.addEventListener('click', () => {
            searchInput.value = '';
            searchClear.classList.add('d-none');
            chatItems.forEach(item => {
                if (!item.classList.contains('ui-hidden')) {
                    item.style.display = 'flex';
                }
            });
        });
    }

    // Formulario de envío
    if (composerForm && messageInput) {
        composerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text) return;

            if (activeChannelType === 'direct') {
                if (!activeContactId) {
                    setActionStatus('Selecciona un contacto antes de enviar el mensaje.', true);
                    return;
                }

                const sendButton = composerForm.querySelector('[type="submit"]');
                if (sendButton) sendButton.disabled = true;
                setActionStatus('Enviando mensaje…');
                try {
                    const response = await fetch(appUrl('/api/chat/send'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            receiver_id: activeContactId,
                            body: text
                        })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || data.error || 'El servidor rechazó el mensaje.');
                    }

                    messageInput.value = '';
                    setActionStatus('Mensaje enviado y guardado.');
                    await loadDirectConversation(activeContactId);
                } catch (error) {
                    console.error('Error enviando mensaje directo:', error);
                    setActionStatus(error.message || 'No se pudo enviar el mensaje. Intenta nuevamente.', true);
                } finally {
                    if (sendButton) sendButton.disabled = false;
                }
                return;
            }

            messageInput.value = '';
            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            if (activeChannelType === 'copilot') {
                const copilotContainer = document.getElementById('copilotDynamicMessages');
                if (copilotContainer) {
                    const outBubble = document.createElement('div');
                    outBubble.className = 'devioz-msg-bubble outgoing';
                    outBubble.innerHTML = `
                        <div class="msg-content">
                            <div class="msg-body">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
                            <div class="msg-footer text-end"><span class="msg-time">${timeStr}</span></div>
                        </div>`;
                    copilotContainer.appendChild(outBubble);

                    const typingBubble = document.createElement('div');
                    typingBubble.className = 'devioz-msg-bubble incoming';
                    typingBubble.id = 'copilotTypingIndicator';
                    typingBubble.innerHTML = `
                        <div class="msg-avatar">
                            <img src="${appUrl('/images/sofia-avatar.webp')}" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=CoPilot&background=A855F7&color=fff';">
                        </div>
                        <div class="msg-content">
                            <div class="msg-author"><strong>CoPilot LABVA AI</strong></div>
                            <div class="text-secondary small"><i class="fa-solid fa-spinner fa-spin me-2"></i> Procesando consulta técnica...</div>
                        </div>`;
                    copilotContainer.appendChild(typingBubble);

                    const stream = document.getElementById('deviozChatStream');
                    if (stream) stream.scrollTop = stream.scrollHeight;

                    fetch(appUrl('/api/projects/1/copilot'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ prompt: text })
                    })
                    .then(r => r.json())
                    .then(data => {
                        const ind = document.getElementById('copilotTypingIndicator');
                        if (ind) ind.remove();

                        const inBubble = document.createElement('div');
                        inBubble.className = 'devioz-msg-bubble incoming';
                        inBubble.innerHTML = `
                            <div class="msg-avatar">
                                <img src="${appUrl('/images/sofia-avatar.webp')}" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=CoPilot&background=A855F7&color=fff';">
                            </div>
                            <div class="msg-content">
                                <div class="msg-author d-flex align-items-center gap-2">
                                    <span class="badge-devioz-bot">24</span>
                                    <strong>CoPilot LABVA AI</strong>
                                </div>
                                <div class="msg-body copilot-markdown-content">${data.output || escapeHtml(data.message || 'Análisis completado.')}</div>
                                <div class="msg-footer text-end"><span class="msg-time">${timeStr}</span></div>
                            </div>`;
                        copilotContainer.appendChild(inBubble);
                        if (stream) stream.scrollTop = stream.scrollHeight;
                    })
                    .catch(() => {
                        const ind = document.getElementById('copilotTypingIndicator');
                        if (ind) ind.remove();
                    });
                }
            } else if (activeChannelType === 'notes') {
                const notesContainer = document.getElementById('notesMessagesContainer');
                if (notesContainer) {
                    const bubble = document.createElement('div');
                    bubble.className = 'devioz-msg-bubble outgoing';
                    bubble.innerHTML = `
                        <div class="msg-content">
                            <div class="msg-body">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
                            <div class="msg-footer text-end"><span class="msg-time">${timeStr}</span></div>
                        </div>`;
                    notesContainer.appendChild(bubble);
                }
            } else if (activeChannelType === 'general') {
                const gContainer = document.getElementById('generalMessagesContainer');
                if (gContainer) {
                    const bubble = document.createElement('div');
                    bubble.className = 'devioz-msg-bubble outgoing';
                    bubble.innerHTML = `
                        <div class="msg-content">
                            <div class="msg-body">${escapeHtml(text).replace(/\n/g, '<br>')}</div>
                            <div class="msg-footer text-end"><span class="msg-time">${timeStr}</span></div>
                        </div>`;
                    gContainer.appendChild(bubble);
                }
            }
        });

        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                composerForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Chips de CoPilot
    document.querySelectorAll('.copilot-chip-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const prompt = btn.getAttribute('data-copilot-prompt');
            if (prompt && messageInput) {
                messageInput.value = prompt;
                if (composerForm) composerForm.dispatchEvent(new Event('submit'));
            }
        });
    });

    if (videocallBtn) {
        videocallBtn.addEventListener('click', async () => {
            if (activeChannelType !== 'direct' || !activeContactId) {
                setActionStatus('Abre una conversación directa y selecciona a la persona antes de iniciar la videollamada.', true);
                return;
            }

            const meetingTab = window.open('about:blank', '_blank');
            if (!meetingTab) {
                setActionStatus('El navegador bloqueó la ventana de la reunión. Permite ventanas emergentes para este sitio.', true);
                return;
            }

            setActionStatus('Enviando la invitación y preparando la sala Jitsi…');
            try {
                const response = await fetch(appUrl('/api/chat/request-call'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        receiver_id: activeContactId,
                        reason: 'Videollamada directa desde Messenger'
                    })
                });
                const data = await response.json();
                if (!response.ok || !data.success || !data.room_url) {
                    throw new Error(data.message || data.error || 'No se pudo crear la sala de llamada.');
                }

                meetingTab.location.href = data.room_url;
                setActionStatus('Invitación enviada. La otra persona puede abrir la sala desde su notificación.');
            } catch (error) {
                meetingTab.close();
                console.error('Error iniciando videollamada:', error);
                setActionStatus(error.message || 'No se pudo iniciar la videollamada.', true);
            }
        });
    }

    document.querySelectorAll('.start-contact-direct-btn').forEach(b => {
        b.addEventListener('click', () => {
            const cid = b.getAttribute('data-contact-id');
            const target = document.querySelector(`.devioz-chat-item[data-contact-id="${cid}"]`);
            if (target) {
                target.click();
                const m = bootstrap.Modal.getInstance(document.getElementById('newChatModal'));
                if (m) m.hide();
            }
        });
    });

    const newChatBtn = document.getElementById('chatNewConversationBtn');
    if (newChatBtn) {
        newChatBtn.addEventListener('click', () => {
            const el = document.getElementById('newChatModal');
            if (el) bootstrap.Modal.getOrCreateInstance(el).show();
        });
    }

    // Inicialización según parámetros de URL o estado vacío Devioz
    const initialChannel = messengerWrapper.getAttribute('data-initial-channel') || '';
    const initialContact = messengerWrapper.getAttribute('data-initial-contact') || '0';

    if (initialContact && parseInt(initialContact, 10) > 0) {
        const contactItem = document.querySelector(`.devioz-chat-item[data-contact-id="${initialContact}"]`);
        if (contactItem) contactItem.click();
    } else if (initialChannel && initialChannel !== '') {
        const channelItem = document.querySelector(`.devioz-chat-item[data-channel-type="${initialChannel}"]`);
        if (channelItem) {
            channelItem.click();
            const railTarget = document.querySelector(`.ui-rail-btn[data-rail-filter="${initialChannel}"]`);
            if (railTarget) {
                railButtons.forEach(b => b.classList.remove('active'));
                railTarget.classList.add('active');
            }
        }
    } else {
        // Estado vacío exacto a Devioz
        if (mainEl) mainEl.classList.add('is-empty');
        chatItems.forEach(i => i.classList.remove('active'));
    }
}
