<?php

declare(strict_types=1);

/**
 * Devioz OS — Catálogo de Sustentaciones en Video (Devioz Reels)
 * Estilo Bilibili ("Bibi") con Vidrio Líquido (Liquid Glass), Botón Azul Play al Hover y Modal Cinema
 */
?>

<div class="bili-experience-container">
    <!-- 1. BARRA SUPERIOR DE CONTROL LIQUID GLASS -->
    <div class="bili-feed-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= url('/gallery') ?>" class="btn btn-outline-light btn-sm rounded-pill d-inline-flex align-items-center gap-2" title="Volver a la Galería General" style="backdrop-filter: blur(8px); background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.2);">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <span class="d-none d-sm-inline">Galería</span>
            </a>
            <div>
                <h4 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 20px; color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.8) !important;">
                    <i class="fa-solid fa-film text-info"></i>
                    <span>Devioz Reels</span>
                </h4>
                <p class="small mb-0 d-none d-md-block" style="font-size: 12.5px; color: rgba(255, 255, 255, 0.82) !important;">
                    Demostraciones técnicas y sustentaciones de software (Catálogo interactivo estilo Bilibili).
                </p>
            </div>
        </div>

        <!-- Filtros Rápidos por Categoría -->
        <div class="bili-filter-pills d-none d-lg-flex">
            <button type="button" class="bili-filter-btn active" data-filter="all">Todos</button>
            <button type="button" class="bili-filter-btn" data-filter="web">Web & SaaS</button>
            <button type="button" class="bili-filter-btn" data-filter="movil">Móvil</button>
            <button type="button" class="bili-filter-btn" data-filter="cloud">Cloud & DevOps</button>
            <button type="button" class="bili-filter-btn" data-filter="qa">QA 100</button>
        </div>

        <!-- Buscador en tiempo real y contador -->
        <div class="d-flex align-items-center gap-2 ms-auto ms-lg-0">
            <div class="bili-search-box">
                <i class="fa-solid fa-magnifying-glass bili-search-icon"></i>
                <input type="text" id="biliSearchInput" class="bili-search-input" placeholder="Buscar demo o tecnología..." style="background: rgba(255, 255, 255, 0.12) !important; color: #FFFFFF !important; border: 1px solid rgba(255, 255, 255, 0.25) !important;">
            </div>
            <span class="badge rounded-pill py-2 px-3 small font-monospace d-none d-sm-inline-flex align-items-center gap-1.5" style="background: rgba(16, 185, 129, 0.25); border: 1px solid rgba(16, 185, 129, 0.5); color: #FFFFFF;">
                <i class="fa-solid fa-play text-info"></i>
                <span id="reelsCountBadge"><?= count($reels) ?></span> Demos
            </span>
        </div>
    </div>

    <!-- 2. CUADRÍCULA 5 COLUMNAS BILIBILI ("FOR YOU") -->
    <?php if (!empty($reels)): ?>
        <div class="bili-grid" id="biliGrid">
            <?php foreach ($reels as $index => $reel): ?>
                <?php 
                    $dev = $reel->developer(); 
                    $embedUrl = getEmbedUrl($reel->video_url, true);
                    $thumbUrl = getReelThumbnail($reel->video_url, $reel->coverUrl());
                    $likes = 14 + ($reel->id * 5);
                    $techStack = !empty($reel->tech_stack) && is_array($reel->tech_stack) ? $reel->tech_stack : [];
                    $primaryTech = !empty($techStack) ? $techStack[0] : 'Software TI';
                    $score = $reel->final_score ? number_format((float)$reel->final_score, 1) : '9.8';
                    $searchData = strtolower($reel->name . ' ' . $reel->code . ' ' . $reel->clientName() . ' ' . implode(' ', $techStack) . ' ' . ($dev ? $dev->name : ''));
                ?>
                <div class="bili-card js-bili-item" 
                     data-reel-index="<?= $index ?>"
                     data-reel-id="<?= $reel->id ?>"
                     data-title="<?= e($reel->name) ?>"
                     data-embed="<?= e($embedUrl) ?>"
                     data-thumb="<?= e($thumbUrl) ?>"
                     data-lead="<?= e($dev ? $dev->name : 'Devioz Engineer') ?>"
                     data-avatar="<?= e($dev ? $dev->avatarUrl() : '') ?>"
                     data-sede="<?= e($reel->sede ?: 'Sede Corporativa') ?>"
                     data-score="<?= $score ?>"
                     data-client="<?= e($reel->clientName()) ?>"
                     data-likes="<?= $likes ?>"
                     data-desc="<?= e($reel->description ?: 'Demostración técnica de software, arquitectura de sistemas y aseguramiento de calidad.') ?>"
                     data-slug="<?= url("/gallery/{$reel->slug}") ?>"
                     data-stack='<?= json_encode($techStack) ?>'
                     data-search="<?= e($searchData) ?>">
                    
                    <!-- Caja del Poster con Proporción Vertical (~3:4) -->
                    <div class="bili-poster-box">
                        <img src="<?= e($thumbUrl) ?>" alt="<?= e($reel->name) ?>" class="bili-poster-img" loading="<?= $index < 10 ? 'eager' : 'lazy' ?>">
                        
                        <!-- Badges en las Esquinas estilo Bilibili -->
                        <span class="bili-badge-pink">
                            <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i> Free
                        </span>
                        <span class="bili-badge-glass">
                            <i class="fa-solid fa-code me-1"></i><?= e($reel->code ?: 'DEV') ?>
                        </span>

                        <!-- Overlay Liquid Glass que se activa con el Cursor (:hover) -->
                        <div class="bili-hover-overlay">
                            <div class="bili-overlay-meta">
                                <span class="bili-tag-pill"><?= e($primaryTech) ?></span>
                                <div class="bili-duration-text">
                                    <i class="fa-solid fa-star text-warning me-1"></i> QA Score <?= $score ?>/10
                                </div>
                            </div>
                            <!-- Botón Azul de Play de Bilibili -->
                            <button type="button" class="bili-play-btn" title="Reproducir sustentación en pantalla completa">
                                <i class="fa-solid fa-play"></i> Play
                            </button>
                        </div>
                    </div>

                    <!-- Información Debajo de la Tarjeta -->
                    <div class="bili-info-box">
                        <h6 class="bili-card-title" title="<?= e($reel->name) ?>" style="color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.95) !important;">
                            <?= e($reel->name) ?>
                        </h6>
                        <div class="bili-dev-line" style="color: #FFFFFF !important;">
                            <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" alt="Dev" class="bili-dev-avatar" loading="lazy">
                            <span class="text-truncate" style="color: rgba(255, 255, 255, 0.95) !important;"><?= e($dev ? $dev->name : 'Devioz Engineer') ?></span>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div id="noResultsNotice" class="text-center py-5 d-none">
            <div class="display-6 mb-3 text-secondary"><i class="fa-solid fa-magnifying-glass"></i></div>
            <h5 class="fw-bold text-white mb-2">No se encontraron demostraciones</h5>
            <p class="text-white-50 small mb-3">Intenta buscar con otros términos como "Laravel", "React", "Mobile" o el nombre del proyecto.</p>
            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="document.getElementById('biliSearchInput').value=''; document.getElementById('biliSearchInput').dispatchEvent(new Event('input'));">
                Limpiar búsqueda
            </button>
        </div>
    <?php else: ?>
        <div class="card-custom p-5 text-center my-5" style="max-width: 500px; margin: 0 auto; background: rgba(255,255,255,0.06); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.15);">
            <div class="display-6 mb-3 text-secondary"><i class="fa-solid fa-film" aria-hidden="true"></i></div>
            <h5 class="fw-bold text-white mb-2">No hay demostraciones en video disponibles</h5>
            <p class="text-white-50 small mb-4">Los ingenieros Devioz pueden registrar las URLs de sustentación en sus proyectos para que aparezcan aquí automáticamente.</p>
            <a href="<?= url('/gallery') ?>" class="btn btn-brand btn-sm">Explorar Galería General</a>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- 3. MODAL CINEMA LIQUID GLASS (ESTILO WATCH PAGE BILIBILI — IMAGEN 2)      -->
<!-- ========================================================================= -->
<div class="bili-cinema-modal" id="biliCinemaModal" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="cinemaVideoTitle">
    <div class="bili-cinema-container">
        
        <!-- Cabecera del Cinema Player -->
        <div class="bili-cinema-header">
            <div class="d-flex align-items-center gap-2 text-truncate me-3">
                <span class="badge bg-danger px-2.5 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.65rem;">
                    <i class="fa-solid fa-circle-play me-1"></i> Bilibili Cinema
                </span>
                <span class="text-white-50 small d-none d-md-inline" style="font-size: 13px;">| Devioz Technology Solutions</span>
            </div>
            <button type="button" class="bili-cinema-close-btn" id="biliCloseCinemaBtn" aria-label="Cerrar reproductor">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Cuerpo del Cinema Player (Reproductor 16:9 + Lista Recomendados lateral) -->
        <div class="bili-cinema-body">
            
            <!-- Columna Izquierda: Reproductor Principal y Metadatos -->
            <div class="bili-player-pane">
                <!-- Marco del Video 16:9 con soporte para YouTube, Loom o Vimeo -->
                <div class="bili-video-frame-box mb-3">
                    <iframe id="cinemaIframePlayer" src="" title="Reproductor de Demostración" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>

                <!-- Título y Estadísticas de la Demo -->
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
                    <div style="flex: 1; min-width: 250px;">
                        <h4 class="fw-bold mb-1" id="cinemaVideoTitle" style="font-size: 1.25rem; color: #FFFFFF !important; text-shadow: 0 1px 4px rgba(0, 0, 0, 0.95) !important;">
                            Título de la Demostración
                        </h4>
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.80rem; color: rgba(255, 255, 255, 0.8) !important;">
                            <span id="cinemaVideoClient" class="text-info fw-semibold">
                                <i class="fa-solid fa-building me-1"></i> Cliente Corporativo
                            </span>
                            <span>•</span>
                            <span class="badge bg-success bg-opacity-75 text-white" id="cinemaVideoScore">
                                QA Score 9.8/10
                            </span>
                            <span>•</span>
                            <span id="cinemaVideoSede" class="badge bg-dark border border-secondary" style="color: rgba(255, 255, 255, 0.85) !important;">
                                Sede
                            </span>
                        </div>
                    </div>

                    <!-- Botones de Acción Rápida -->
                    <div class="d-flex align-items-center gap-2">
                        <!-- Me Gusta -->
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 d-flex align-items-center gap-1.5" id="cinemaLikeBtn" title="Dar Me Gusta a esta Sustentación">
                            <i class="fa-solid fa-heart"></i>
                            <span id="cinemaLikeCount" class="fw-bold">24</span>
                        </button>
                        <!-- Compartir -->
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 d-flex align-items-center gap-1.5" id="cinemaShareBtn" title="Copiar enlace directo">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span class="d-none d-sm-inline">Compartir</span>
                        </button>
                        <!-- Ver Ficha Completa -->
                        <a href="#" target="_blank" class="btn btn-brand btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5" id="cinemaProjectSheetLink" title="Abrir ficha técnica y repositorio">
                            <span>Ver Ficha</span>
                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.72rem;"></i>
                        </a>
                    </div>
                </div>

                <!-- Tarjeta del Desarrollador / Responsable -->
                <div class="p-3 rounded-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.12);">
                    <div class="d-flex align-items-center gap-2.5">
                        <img src="" id="cinemaDevAvatar" class="rounded-circle border border-info" width="42" height="42" style="object-fit: cover;" alt="Dev">
                        <div>
                            <div class="fw-bold small d-flex align-items-center gap-1.5" id="cinemaDevName" style="color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.85) !important;">
                                Desarrollador Devioz
                                <i class="fa-solid fa-circle-check text-info" style="font-size: 0.8rem;" title="Ingeniero Certificado"></i>
                            </div>
                            <span style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.75) !important;">Ingeniero de Software • Aseguramiento de Calidad Devioz</span>
                        </div>
                    </div>
                    <div id="cinemaTechStackChips" class="d-flex flex-wrap gap-1">
                        <!-- Chips de tecnologías inyectados dinámicamente -->
                    </div>
                </div>

                <!-- Descripción del Proyecto -->
                <div class="p-3 rounded-3" style="background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em; color: rgba(255, 255, 255, 0.75) !important;">
                        Resumen de la Solución TI
                    </div>
                    <p class="mb-0 small" id="cinemaVideoDesc" style="font-size: 0.85rem; line-height: 1.55; color: rgba(255, 255, 255, 0.95) !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8) !important;">
                        Descripción completa de la sustentación técnica y componentes entregados.
                    </p>
                </div>
            </div>

            <!-- Columna Derecha: "Recomendados para ti" (Playlist Bilibili — Imagen 2) -->
            <div class="bili-sidebar-pane">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2" style="font-size: 0.88rem; color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9) !important;">
                        <i class="fa-solid fa-list-ul text-info"></i>
                        <span>Recomendados para ti</span>
                    </h6>
                    <span class="badge bg-secondary bg-opacity-25 font-monospace" style="font-size: 0.70rem; color: rgba(255, 255, 255, 0.75) !important;">
                        Auto-Next
                    </span>
                </div>

                <!-- Lista de Otras Demos en Playlist Interactiva -->
                <div id="cinemaPlaylistContainer">
                    <!-- Elementos inyectados por JavaScript -->
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. LÓGICA INTERACTIVA DE CATÁLOGO & REPRODUCTOR CINEMA BILIBILI           -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('biliCinemaModal');
    const closeBtn = document.getElementById('biliCloseCinemaBtn');
    const iframe = document.getElementById('cinemaIframePlayer');
    const titleEl = document.getElementById('cinemaVideoTitle');
    const clientEl = document.getElementById('cinemaVideoClient');
    const scoreEl = document.getElementById('cinemaVideoScore');
    const sedeEl = document.getElementById('cinemaVideoSede');
    const devAvatarEl = document.getElementById('cinemaDevAvatar');
    const devNameEl = document.getElementById('cinemaDevName');
    const descEl = document.getElementById('cinemaVideoDesc');
    const stackEl = document.getElementById('cinemaTechStackChips');
    const likeBtn = document.getElementById('cinemaLikeBtn');
    const likeCountEl = document.getElementById('cinemaLikeCount');
    const shareBtn = document.getElementById('cinemaShareBtn');
    const sheetLink = document.getElementById('cinemaProjectSheetLink');
    const playlistContainer = document.getElementById('cinemaPlaylistContainer');

    const searchInput = document.getElementById('biliSearchInput');
    const filterBtns = document.querySelectorAll('.bili-filter-btn');
    const cards = Array.from(document.querySelectorAll('.js-bili-item'));
    const countBadge = document.getElementById('reelsCountBadge');
    const noResultsNotice = document.getElementById('noResultsNotice');

    let currentReelIndex = 0;
    let activeFilter = 'all';

    // 1. Filtrado dinámico por categoría y texto
    function filterCards() {
        const query = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            const stack = card.getAttribute('data-stack') || '';
            let matchesCategory = true;

            if (activeFilter === 'web') {
                matchesCategory = /web|saas|php|laravel|react|vue|angular|node|django|spring/i.test(searchData);
            } else if (activeFilter === 'movil') {
                matchesCategory = /móvil|movil|flutter|android|ios|swift|react native|kotlin/i.test(searchData);
            } else if (activeFilter === 'cloud') {
                matchesCategory = /cloud|devops|aws|docker|kubernetes|azure|microservicios/i.test(searchData);
            } else if (activeFilter === 'qa') {
                const score = parseFloat(card.getAttribute('data-score') || '0');
                matchesCategory = score >= 9.5;
            }

            const matchesQuery = query === '' || searchData.includes(query);

            if (matchesCategory && matchesQuery) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (countBadge) countBadge.textContent = visibleCount;
        if (noResultsNotice) {
            noResultsNotice.classList.toggle('d-none', visibleCount > 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCards);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeFilter = btn.getAttribute('data-filter') || 'all';
            filterCards();
        });
    });

    // 2. Construcción de la Playlist Lateral en el Modal
    function buildPlaylist(activeIndex) {
        if (!playlistContainer) return;
        playlistContainer.replaceChildren();

        cards.forEach((card, idx) => {
            if (card.style.display === 'none') return; // no incluir ocultos

            const title = card.getAttribute('data-title') || 'Demo';
            const thumb = card.getAttribute('data-thumb') || '';
            const lead = card.getAttribute('data-lead') || 'Dev';
            const score = card.getAttribute('data-score') || '9.8';

            const item = document.createElement('div');
            item.className = 'bili-playlist-item' + (idx === activeIndex ? ' active' : '');
            item.setAttribute('data-index', idx);
            item.innerHTML = `
                <img class="bili-playlist-thumb" loading="lazy">
                <div class="overflow-hidden" style="flex: 1;">
                    <h6 class="small fw-bold mb-1 text-truncate" style="font-size: 0.78rem; color: #FFFFFF !important; text-shadow: 0 1px 2px rgba(0, 0, 0, 0.9) !important;"></h6>
                    <div class="d-flex align-items-center justify-content-between" style="font-size: 0.70rem; color: rgba(255, 255, 255, 0.75) !important;">
                        <span class="text-truncate" style="max-width: 120px; color: rgba(255, 255, 255, 0.85) !important;"></span>
                        <span class="badge bg-success bg-opacity-75 text-white" style="font-size: 0.65rem;"></span>
                    </div>
                </div>
            `;
            const thumbnail = item.querySelector('.bili-playlist-thumb');
            thumbnail.src = thumb;
            thumbnail.alt = title;
            thumbnail.onerror = () => { thumbnail.removeAttribute('src'); };
            const titleElement = item.querySelector('h6');
            titleElement.title = title;
            titleElement.textContent = title;
            item.querySelector('span.text-truncate').textContent = lead;
            item.querySelector('.badge').textContent = `★ ${score}`;

            item.addEventListener('click', () => {
                openCinemaAtIndex(idx);
            });

            playlistContainer.appendChild(item);
        });
    }

    // 3. Apertura del Cinema Player en un índice específico
    function openCinemaAtIndex(index) {
        if (!modal || index < 0 || index >= cards.length) return;
        currentReelIndex = index;
        const card = cards[index];

        const title = card.getAttribute('data-title') || 'Demostración Técnica';
        const embed = card.getAttribute('data-embed') || '';
        const client = card.getAttribute('data-client') || 'Cliente Corporativo';
        const lead = card.getAttribute('data-lead') || 'Devioz Engineer';
        const avatar = card.getAttribute('data-avatar') || '';
        const sede = card.getAttribute('data-sede') || 'Sede';
        const score = card.getAttribute('data-score') || '9.8';
        const likes = card.getAttribute('data-likes') || '18';
        const desc = card.getAttribute('data-desc') || '';
        const slug = card.getAttribute('data-slug') || '#';
        const stackRaw = card.getAttribute('data-stack') || '[]';

        if (titleEl) titleEl.textContent = title;
        if (clientEl) {
            const clientIcon = document.createElement('i');
            clientIcon.className = 'fa-solid fa-building me-1';
            clientEl.replaceChildren(clientIcon, document.createTextNode(' ' + client));
        }
        if (scoreEl) scoreEl.textContent = 'QA Score ' + score + ' / 10';
        if (sedeEl) sedeEl.textContent = sede;
        if (devNameEl) {
            const verifiedIcon = document.createElement('i');
            verifiedIcon.className = 'fa-solid fa-circle-check text-info';
            verifiedIcon.style.fontSize = '0.8rem';
            verifiedIcon.title = 'Ingeniero Certificado';
            devNameEl.replaceChildren(document.createTextNode(lead + ' '), verifiedIcon);
        }
        if (devAvatarEl) devAvatarEl.src = avatar;
        if (descEl) descEl.textContent = desc;
        if (likeCountEl) likeCountEl.textContent = likes;
        if (sheetLink) sheetLink.href = slug;

        // Inyectar Tech Stack Chips
        if (stackEl) {
            stackEl.replaceChildren();
            try {
                const stack = JSON.parse(stackRaw);
                if (Array.isArray(stack)) {
                    stack.slice(0, 4).forEach(t => {
                        const span = document.createElement('span');
                        span.className = 'badge bg-dark border border-secondary text-info font-monospace';
                        span.style.fontSize = '0.70rem';
                        span.textContent = '#' + t;
                        stackEl.appendChild(span);
                    });
                }
            } catch (e) {}
        }

        // Cargar video con autoplay
        if (iframe) {
            iframe.src = embed;
        }

        buildPlaylist(index);

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // 4. Cerrar Cinema Player
    function closeCinema() {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
        if (iframe) {
            iframe.src = ''; // Detiene la reproducción inmediatamente
        }
    }

    // Clic en tarjetas o botón azul Play
    cards.forEach((card, idx) => {
        card.addEventListener('click', (e) => {
            openCinemaAtIndex(idx);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeCinema);

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeCinema();
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal?.classList.contains('active')) {
            closeCinema();
        } else if (e.key === 'ArrowRight' && modal?.classList.contains('active')) {
            openCinemaAtIndex((currentReelIndex + 1) % cards.length);
        } else if (e.key === 'ArrowLeft' && modal?.classList.contains('active')) {
            openCinemaAtIndex((currentReelIndex - 1 + cards.length) % cards.length);
        }
    });

    // Like interactivo con confeti visual
    let liked = false;
    likeBtn?.addEventListener('click', () => {
        if (!liked) {
            liked = true;
            likeBtn.classList.remove('btn-outline-danger');
            likeBtn.classList.add('btn-danger', 'text-white');
            let current = parseInt(likeCountEl.textContent || '0', 10);
            likeCountEl.textContent = current + 1;
        }
    });

    // Compartir con copia al portapapeles
    shareBtn?.addEventListener('click', () => {
        const url = sheetLink?.href || window.location.href;
        navigator.clipboard?.writeText(url).then(() => {
            alert('¡Enlace de la demostración copiado al portapapeles!');
        }).catch(() => {
            prompt('Copia este enlace de la demo:', url);
        });
    });
});
</script>
