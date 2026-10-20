/**
 * Devioz InfiniteSpiral — Componente Helicoidal 3D Interactivo
 * Port Nativo Vanilla JS del componente @react-bits/InfiniteSpiral
 * Con Reproducción Inline de Video en 3D y Modo Teatro Lightbox
 */

(function () {
    'use strict';

    const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
    const modulo = (value, divisor) => ((value % divisor) + divisor) % divisor;
    const smoothstep = (min, max, value) => {
        const x = clamp((value - min) / (max - min || 1), 0, 1);
        return x * x * (3 - 2 * x);
    };

    class InfiniteSpiral {
        constructor(rootElement, userOptions = {}) {
            this.root = typeof rootElement === 'string' ? document.querySelector(rootElement) : rootElement;
            if (!this.root) return;

            const defaults = {
                speed: 0.55,
                direction: 'up',
                animationMode: 'all',
                radius: 195,
                cardWidth: 185,
                cardHeight: 270,
                verticalSpacing: 78,
                perspective: 1000,
                cardsPerTurn: 7,
                rotation: 0,
                cardTilt: 0,
                cardRadius: 16,
                centerScale: 1.2,
                edgeFade: 0.3,
                edgeBlur: 6,
                pauseOnHover: true,
                grayscale: 0
            };

            this.options = Object.assign({}, defaults, userOptions);
            this.cards = Array.from(this.root.querySelectorAll('.infinite-spiral__item'));
            this.count = this.cards.length;
            if (this.count === 0) return;

            this.progress = 0;
            this.targetProgress = 0;
            this.autoSpeed = 0;
            this.isHovered = false;
            this.isVisible = true;
            this.isDragging = false;
            this.lastPointerY = 0;
            this.isPausedByUser = false;
            this.baseSpeed = this.options.speed;
            this.frameId = null;
            this.previousTime = performance.now();
            this.activePlayingCard = null;

            this.init();
        }

        init() {
            this.bounds = this.root.getBoundingClientRect();

            this.root.style.setProperty('--infinite-spiral-card-width', `${this.options.cardWidth}px`);
            this.root.style.setProperty('--infinite-spiral-card-height', `${this.options.cardHeight}px`);
            this.root.style.setProperty('--infinite-spiral-card-radius', `${this.options.cardRadius}px`);
            this.root.style.perspective = `${this.options.perspective}px`;

            const dragEnabled = this.options.animationMode === 'drag' || this.options.animationMode === 'all';
            this.root.style.cursor = dragEnabled ? 'grab' : 'default';
            this.root.style.touchAction = dragEnabled ? 'pan-x' : 'auto';

            this.resizeObserver = new ResizeObserver(() => {
                this.bounds = this.root.getBoundingClientRect();
            });
            this.resizeObserver.observe(this.root);

            this.intersectionObserver = new IntersectionObserver(([entry]) => {
                this.isVisible = entry.isIntersecting;
            }, { threshold: 0.02 });
            this.intersectionObserver.observe(this.root);

            this.root.addEventListener('mouseenter', () => {
                this.isHovered = true;
            });
            this.root.addEventListener('mouseleave', () => {
                this.isHovered = false;
            });

            this.setupPointerAndClickEvents();

            this.root.addEventListener('wheel', (e) => {
                e.preventDefault();
                const delta = clamp(e.deltaY, -120, 120);
                const scrollMultiplier = Math.max(this.options.speed, 0.4) / 0.55;
                this.targetProgress += (delta * 0.0035 * scrollMultiplier);
            }, { passive: false });

            this.renderBound = this.render.bind(this);
            this.frameId = requestAnimationFrame(this.renderBound);
        }

        setupPointerAndClickEvents() {
            let pointerStartX = 0;
            let pointerStartY = 0;
            let pointerStartTime = 0;
            let isPressed = false;
            let capturedPointerId = null;
            let candidateCard = null;

            this.root.addEventListener('pointerdown', (e) => {
                if (e.button !== 0) return;
                // Si el clic es en enlace externo, iframe o botón interno no iniciar drag
                if (e.target.closest('a, iframe, input, select')) return;

                isPressed = true;
                this.isDragging = false;
                pointerStartX = e.clientX;
                pointerStartY = e.clientY;
                this.lastPointerY = e.clientY;
                pointerStartTime = performance.now();
                candidateCard = e.target.closest('.infinite-spiral__item');
            });

            window.addEventListener('pointermove', (e) => {
                if (!isPressed) return;

                const deltaY = e.clientY - this.lastPointerY;
                const totalDist = Math.hypot(e.clientX - pointerStartX, e.clientY - pointerStartY);

                // Solo iniciar drag si el movimiento es superior a 7 píxeles reales
                if (totalDist > 7) {
                    if (!this.isDragging) {
                        this.isDragging = true;
                        try {
                            this.root.setPointerCapture(e.pointerId);
                            capturedPointerId = e.pointerId;
                        } catch (err) {}
                        this.root.style.cursor = 'grabbing';
                    }
                    this.lastPointerY = e.clientY;
                    this.targetProgress -= deltaY / Math.max(this.options.verticalSpacing, 1);
                }
            });

            const handlePointerUp = (e) => {
                if (!isPressed) return;
                isPressed = false;

                const totalDist = Math.hypot(e.clientX - pointerStartX, e.clientY - pointerStartY);
                const duration = performance.now() - pointerStartTime;

                if (this.isDragging) {
                    this.isDragging = false;
                    if (capturedPointerId !== null) {
                        try {
                            this.root.releasePointerCapture(capturedPointerId);
                        } catch (err) {}
                        capturedPointerId = null;
                    }
                    this.root.style.cursor = 'grab';
                }

                // Clic / Tap limpio confirmado (movimiento mínimo y duración menor a 700ms)
                if (totalDist <= 7 && duration < 700) {
                    const card = candidateCard || e.target.closest('.infinite-spiral__item');
                    if (card) {
                        this.handleCardAction(card, e);
                    }
                }

                candidateCard = null;
            };

            window.addEventListener('pointerup', handlePointerUp);
            window.addEventListener('pointercancel', handlePointerUp);
        }

        handleCardAction(card, e) {
            // 1. Si el usuario hizo clic en "Ficha / Ver Proyecto", permitir navegación normal
            if (e.target.closest('a')) {
                return;
            }

            const videoUrl = card.getAttribute('data-video-url');
            const videoEmbed = card.getAttribute('data-video-embed');
            const videoTitle = card.getAttribute('data-video-title') || 'Demostración de Software';

            // 2. Si el usuario hizo clic en el botón de "Modo Teatro", abrir Lightbox directamente
            if (e.target.closest('.spiral-card-theatre-btn') || e.target.closest('.spiral-player-expand-btn')) {
                e.stopPropagation();
                if (videoUrl && typeof window.openVideoLightbox === 'function') {
                    window.openVideoLightbox(videoUrl, videoTitle);
                }
                return;
            }

            // 3. Si el usuario hizo clic en el botón de cerrar video inline
            if (e.target.closest('.spiral-player-close-btn')) {
                e.stopPropagation();
                this.closeInlineVideo(card);
                return;
            }

            // 4. Si la tarjeta ya está reproduciendo, abrir Lightbox al hacer clic
            if (card.classList.contains('is-playing')) {
                if (videoUrl && typeof window.openVideoLightbox === 'function') {
                    window.openVideoLightbox(videoUrl, videoTitle);
                }
                return;
            }

            // 5. Iniciar Reproducción Inline directa en la tarjeta 3D
            this.playCardInline(card, videoUrl, videoEmbed, videoTitle);
        }

        playCardInline(card, videoUrl, videoEmbed, videoTitle) {
            if (!videoUrl && !videoEmbed) return;

            // Cerrar cualquier otro video activo en el carrusel
            this.cards.forEach(c => {
                if (c !== card && c.classList.contains('is-playing')) {
                    this.closeInlineVideo(c);
                }
            });

            // Centrar suavemente esta tarjeta en el frente de la espiral
            const cardIndex = parseInt(card.getAttribute('data-card-index') || '0', 10);
            this.targetProgress = cardIndex;

            // Pausar auto-giro para comodidad del espectador
            this.isPausedByUser = true;
            this.updatePlayButtonUI(false);

            // Marcar tarjeta como activa/reproduciendo
            card.classList.add('is-playing');
            this.activePlayingCard = card;

            const inner = card.querySelector('.spiral-card-inner');
            if (!inner) return;

            // Guardar el HTML original del póster para poder restaurarlo al cerrar
            if (!card._originalInnerHtml) {
                card._originalInnerHtml = inner.innerHTML;
            }

            const embedUrl = videoEmbed || this.formatEmbedUrl(videoUrl);

            inner.innerHTML = `
                <div class="spiral-card-player-container">
                    <iframe 
                        src="${embedUrl}" 
                        class="spiral-card-player-iframe" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                        allowfullscreen 
                        playsinline>
                    </iframe>
                    <div class="spiral-card-player-topbar">
                        <span class="badge bg-danger text-white font-monospace fw-bold shadow-sm" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                            ● REPRODUCIENDO
                        </span>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="spiral-player-expand-btn" title="Ampliar en Modo Teatro (Pantalla Completa)">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                                <span>Teatro</span>
                            </button>
                            <button type="button" class="spiral-player-close-btn" title="Detener y cerrar video">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Enlazar botones del reproductor inline
            const expandBtn = inner.querySelector('.spiral-player-expand-btn');
            if (expandBtn) {
                expandBtn.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    if (typeof window.openVideoLightbox === 'function') {
                        window.openVideoLightbox(videoUrl, videoTitle);
                    }
                });
            }

            const closeBtn = inner.querySelector('.spiral-player-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    this.closeInlineVideo(card);
                });
            }
        }

        closeInlineVideo(card) {
            if (!card || !card.classList.contains('is-playing')) return;
            card.classList.remove('is-playing');

            const inner = card.querySelector('.spiral-card-inner');
            if (inner && card._originalInnerHtml) {
                inner.innerHTML = card._originalInnerHtml;
            }

            if (this.activePlayingCard === card) {
                this.activePlayingCard = null;
            }
        }

        formatEmbedUrl(url) {
            const ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/);
            if (ytMatch) {
                return `https://www.youtube-nocookie.com/embed/${ytMatch[1]}?autoplay=1&rel=0&modestbranding=1&iv_load_policy=3&playsinline=1`;
            }
            const loomMatch = url.match(/loom\.com\/(?:share|embed)\/([a-zA-Z0-9]+)/);
            if (loomMatch) {
                return `https://www.loom.com/embed/${loomMatch[1]}?autoplay=1`;
            }
            const vimeoMatch = url.match(/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/);
            if (vimeoMatch) {
                return `https://player.vimeo.com/video/${vimeoMatch[3]}?autoplay=1`;
            }
            return url;
        }

        render(time) {
            const delta = Math.min((time - this.previousTime) / 1000, 0.05);
            this.previousTime = time;

            const autoEnabled = this.options.animationMode === 'auto' || this.options.animationMode === 'all';
            const motionPaused = this.isPausedByUser || this.isDragging || (this.options.pauseOnHover && this.isHovered);
            const directionMultiplier = this.options.direction === 'down' ? -1 : 1;
            const desiredAutoSpeed = (autoEnabled && this.isVisible && !motionPaused)
                ? this.options.speed * directionMultiplier
                : 0;

            const speedBlend = 1 - Math.exp(-delta * 7);
            this.autoSpeed += (desiredAutoSpeed - this.autoSpeed) * speedBlend;
            this.targetProgress += this.autoSpeed * delta;

            const followBlend = 1 - Math.exp(-delta * (this.isDragging ? 22 : 11));
            this.progress += (this.targetProgress - this.progress) * followBlend;

            const half = this.count / 2;
            const width = Math.max(this.bounds.width, 1);
            const height = Math.max(this.bounds.height, 1);
            const fit = Math.min(1, width / (this.options.cardWidth * 2.8), height / (this.options.cardHeight * 2.35));
            const responsiveRadius = Math.min(this.options.radius, Math.max(72, width * 0.36)) * fit;
            const fadeStart = clamp(1 - this.options.edgeFade, 0, 0.98);
            const turnSize = Math.max(this.options.cardsPerTurn, 1);

            for (let index = 0; index < this.count; index++) {
                const card = this.cards[index];
                if (!card) continue;

                let offset = index - this.progress;
                offset = modulo(offset + half, this.count) - half;

                const edge = Math.min(Math.abs(offset) / Math.max(half, 1), 1);
                const opacity = 1 - smoothstep(fadeStart, 1, edge);
                const focus = 1 - Math.min(Math.abs(offset) / Math.max(turnSize * 0.65, 1), 1);
                const isPlayingThis = card.classList.contains('is-playing');
                const scale = (1 + (this.options.centerScale - 1) * focus) * fit * (isPlayingThis ? 1.15 : 1);
                const angle = offset * (360 / turnSize) + this.options.rotation;
                const angleRadians = (angle * Math.PI) / 180;
                const x = Math.sin(angleRadians) * responsiveRadius;
                const z = Math.cos(angleRadians) * responsiveRadius;
                const depthScale = clamp(this.options.perspective / Math.max(this.options.perspective - z, 1), 0.72, 1.45);
                const visualScale = scale * depthScale;
                const depth = (z / Math.max(responsiveRadius, 1) + 1) / 2;
                const blur = isPlayingThis ? 0 : (this.options.edgeBlur * smoothstep(0.35, 1, edge));

                card.style.transform = `translate(-50%, -50%) translate3d(${x.toFixed(2)}px, ${(offset * this.options.verticalSpacing * fit).toFixed(2)}px, 0) rotateZ(${this.options.cardTilt}deg) scale(${visualScale.toFixed(3)})`;
                card.style.opacity = isPlayingThis ? '1' : opacity.toFixed(3);
                card.style.filter = blur > 0.01 ? `blur(${blur.toFixed(2)}px)` : 'none';
                card.style.zIndex = isPlayingThis ? '999999' : String(Math.round(depth * 100000) + index);
                card.style.pointerEvents = opacity > 0.25 || isPlayingThis ? 'auto' : 'none';
            }

            this.frameId = requestAnimationFrame(this.renderBound);
        }

        updatePlayButtonUI(isPlaying) {
            const playBtn = document.getElementById('spiralPlayToggleBtn');
            if (playBtn) {
                const textSpan = playBtn.querySelector('.spiral-btn-text');
                const iconSpan = playBtn.querySelector('.spiral-btn-icon');
                if (isPlaying) {
                    if (textSpan) textSpan.textContent = 'Pausar Giro';
                    if (iconSpan) iconSpan.innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
                } else {
                    if (textSpan) textSpan.textContent = 'Reanudar Giro';
                    if (iconSpan) iconSpan.innerHTML = '<path d="M8 5v14l11-7z"/>';
                }
            }
        }

        togglePlay() {
            this.isPausedByUser = !this.isPausedByUser;
            this.updatePlayButtonUI(!this.isPausedByUser);
            return !this.isPausedByUser;
        }

        toggleDirection() {
            this.options.direction = this.options.direction === 'up' ? 'down' : 'up';
            return this.options.direction;
        }

        setSpeed(multiplier) {
            this.options.speed = this.baseSpeed * multiplier;
        }

        destroy() {
            if (this.frameId) {
                cancelAnimationFrame(this.frameId);
            }
            if (this.resizeObserver) {
                this.resizeObserver.disconnect();
            }
            if (this.intersectionObserver) {
                this.intersectionObserver.disconnect();
            }
        }
    }

    // Inicializador del módulo Reels
    function initDeviozReelsPage() {
        const spiralRoot = document.getElementById('infiniteSpiralStage');
        let spiralInstance = null;

        if (spiralRoot) {
            spiralInstance = new InfiniteSpiral(spiralRoot, {
                speed: 0.55,
                direction: 'up',
                animationMode: 'all',
                radius: 205,
                cardWidth: 185,
                cardHeight: 270,
                verticalSpacing: 78,
                perspective: 1000,
                cardsPerTurn: 7,
                centerScale: 1.22,
                edgeFade: 0.28,
                edgeBlur: 6,
                pauseOnHover: true
            });

            // Controles de barra
            const playBtn = document.getElementById('spiralPlayToggleBtn');
            if (playBtn) {
                playBtn.addEventListener('click', () => {
                    spiralInstance.togglePlay();
                });
            }

            const dirBtn = document.getElementById('spiralDirectionBtn');
            if (dirBtn) {
                dirBtn.addEventListener('click', () => {
                    const newDir = spiralInstance.toggleDirection();
                    const textSpan = dirBtn.querySelector('.spiral-btn-text');
                    if (textSpan) {
                        textSpan.textContent = newDir === 'up' ? 'Dirección: ▲ Subir' : 'Dirección: ▼ Bajar';
                    }
                });
            }

            document.querySelectorAll('[data-spiral-speed]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const speed = parseFloat(this.getAttribute('data-spiral-speed') || '1');
                    spiralInstance.setSpeed(speed);
                    document.querySelectorAll('[data-spiral-speed]').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        }

        // Switcher entre vista Espiral 3D y Feed Vertical
        const spiralContainer = document.getElementById('infiniteSpiralContainer');
        const feedContainer = document.getElementById('reelsViewport');
        const btnViewSpiral = document.getElementById('btnViewSpiral');
        const btnViewFeed = document.getElementById('btnViewFeed');

        function setReelsView(view) {
            if (!spiralContainer || !feedContainer) return;

            if (view === 'feed') {
                spiralContainer.classList.add('d-none');
                feedContainer.classList.remove('d-none');
                feedContainer.style.display = 'flex';
                btnViewFeed?.classList.add('active');
                btnViewSpiral?.classList.remove('active');
                if (spiralInstance) {
                    spiralInstance.isPausedByUser = true;
                    // Cerrar video activo si cambia de vista
                    if (spiralInstance.activePlayingCard) {
                        spiralInstance.closeInlineVideo(spiralInstance.activePlayingCard);
                    }
                }
            } else {
                feedContainer.classList.add('d-none');
                feedContainer.style.display = 'none';
                spiralContainer.classList.remove('d-none');
                btnViewSpiral?.classList.add('active');
                btnViewFeed?.classList.remove('active');
                if (spiralInstance) {
                    spiralInstance.isPausedByUser = false;
                    spiralInstance.updatePlayButtonUI(true);
                    spiralInstance.bounds = spiralRoot.getBoundingClientRect();
                }
            }
            try {
                localStorage.setItem('devioz_reels_view', view);
            } catch (e) {}
        }

        if (btnViewSpiral && btnViewFeed) {
            btnViewSpiral.addEventListener('click', () => setReelsView('spiral'));
            btnViewFeed.addEventListener('click', () => setReelsView('feed'));

            const savedView = localStorage.getItem('devioz_reels_view') || 'spiral';
            setReelsView(savedView);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDeviozReelsPage);
    } else {
        initDeviozReelsPage();
    }

    window.DeviozInfiniteSpiral = InfiniteSpiral;
})();
