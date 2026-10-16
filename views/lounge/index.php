<?php

declare(strict_types=1);

/**
 * Devioz OS — Vista de Dev Lounge (Comunidad Técnica de Consultoría)
 * Enlaces con vista previa OpenGraph, debates con comentarios, reacciones interactivas y categorías especializadas.
 */
?>

<div class="container-fluid px-0">
    <!-- Header del Lounge -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0 page-header-title lounge-header-title text-white" style="font-size: 24px; color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.65), 0 2px 8px rgba(0, 0, 0, 0.35) !important;">Comunidad Devioz</h3>
        </div>

        <button class="btn btn-brand d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#composerCollapse" aria-expanded="false" aria-controls="composerCollapse">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span>Nueva Publicación</span>
        </button>
    </div>

    <!-- Compositor de Publicaciones (Colapsable con Vista Previa de Enlaces en Vivo) -->
    <div class="collapse mb-4" id="composerCollapse">
        <div class="card-custom p-4 border border-emerald border-opacity-50 shadow-lg">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-white mb-0 d-flex align-items-center gap-2">
                    <span>Compartir Publicación o Enlace con la Consultora</span>
                </h5>
                <span class="badge bg-emerald bg-opacity-10 text-emerald border border-emerald border-opacity-25 small">Generador de Vista Previa Activo</span>
            </div>

            <form action="<?= url('/lounge/store') ?>" method="POST" id="loungeComposerForm">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label-custom">Título de la publicación o recurso</label>
                        <input type="text" name="title" id="postTitleInput" class="form-control form-control-custom" placeholder="Ej: Pipeline ETL con Databricks y Azure Data Factory en Producción..." required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label-custom">Categoría de Consultoría</label>
                        <select name="category" class="form-select form-control-custom" required>
                            <option value="cloud_devops">Cloud & DevOps (AWS, GCP, Azure, K8s, Docker, Terraform)</option>
                            <option value="data_analytics">Data & Analytics (Databricks, ADF, Big Data, SQL)</option>
                            <option value="servers_infra">Servidores & Infra (Linux, Redes, Sysadmin)</option>
                            <option value="software_dev">Desarrollo & Software (Backend, Frontend, APIs)</option>
                            <option value="tip">Tip Técnico / Solución Rápida</option>
                            <option value="idea">Idea & Arquitectura</option>
                            <option value="win">Logro & Avance</option>
                            <option value="blocker">Consultas Técnicas & Escalabilidad</option>
                        </select>
                    </div>
                </div>

                <!-- Campo de Tags Tecnológicos -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label-custom mb-0">Tags Tecnológicos (Separados por coma)</label>
                        <span class="text-secondary small">Ej: AWS, Terraform, Docker, Kubernetes, Databricks</span>
                    </div>
                    <input type="text" name="tech_tags" class="form-control form-control-custom" placeholder="AWS, Azure, Docker, Kubernetes, Terraform, Databricks, Jenkins, Linux...">
                </div>

                <!-- Campo de Contenido -->
                <div class="mb-3">
                    <label class="form-label-custom">Contenido / Explicación del tema</label>
                    <textarea name="content" rows="3" class="form-control form-control-custom" placeholder="Describe la solución, arquitectura, contexto del enlace o duda técnica..." required></textarea>
                </div>

                <!-- Apartado para Compartir Enlace con Generación de Vista Previa (Tipo WhatsApp / Redes Sociales) -->
                <div class="mb-3">
                    <label class="form-label-custom d-flex align-items-center gap-1">
                        <span>Compartir Enlace / URL (YouTube, Instagram, Facebook, GitHub, AWS, Blog...)</span>
                    </label>
                    <div class="input-group mb-2">
                        <span class="input-group-text bg-dark border-secondary text-secondary">https://</span>
                        <input type="url" name="link_url" id="loungeLinkInput" class="form-control form-control-custom" placeholder="Pega el enlace aquí (se generará la imagen y descripción automáticamente)...">
                        <button type="button" class="btn btn-outline-custom" id="btnDetectLink">Analizar Enlace</button>
                    </div>

                    <!-- Contenedor de Vista Previa en Vivo -->
                    <div id="linkLivePreview" class="link-live-preview-box d-none mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-bold text-emerald d-flex align-items-center gap-1">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Vista previa detectada estilo WhatsApp / OpenGraph:</span>
                            </span>
                            <button type="button" class="btn btn-ghost btn-sm py-0 px-2 text-danger" id="btnClearPreview">Quitar enlace</button>
                        </div>
                        <div id="linkLivePreviewCard"></div>
                    </div>

                    <!-- Campos Ocultos para Persistir Metadatos Extraídos del Enlace -->
                    <input type="hidden" name="link_title" id="hiddenLinkTitle">
                    <input type="hidden" name="link_description" id="hiddenLinkDesc">
                    <input type="hidden" name="link_image" id="hiddenLinkImage">
                    <input type="hidden" name="link_domain" id="hiddenLinkDomain">
                </div>

                <!-- Snippet de Código Opcional -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label-custom mb-0">Snippet de Código / Manifiesto YAML / Dockerfile (Opcional)</label>
                        <span class="text-secondary small font-monospace">Python, YAML, PHP, Bash, SQL</span>
                    </div>
                    <textarea name="code_snippet" rows="3" class="form-control form-control-custom font-monospace" style="font-size: 0.85rem;" placeholder="# Pega aquí tus comandos, manifiestos o código relevante..."></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3">
                    <button type="button" class="btn btn-ghost btn-sm" data-bs-toggle="collapse" data-bs-target="#composerCollapse">Cancelar</button>
                    <button type="submit" class="btn btn-brand btn-sm px-4">Publicar en Comunidad</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filtros de Categoría Especializados de la Consultora -->
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <a href="<?= url('/lounge') ?>" class="category-filter-chip <?= $currentCategory === 'all' ? 'active' : '' ?>">
            <span>Todos</span>
            <span class="badge bg-dark ms-1"><?= $counts['all'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=cloud_devops') ?>" class="category-filter-chip <?= $currentCategory === 'cloud_devops' ? 'active' : '' ?>">
            <span>Cloud & DevOps</span>
            <span class="badge bg-dark ms-1"><?= $counts['cloud_devops'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=data_analytics') ?>" class="category-filter-chip <?= $currentCategory === 'data_analytics' ? 'active' : '' ?>">
            <span>Data & Analytics</span>
            <span class="badge bg-dark ms-1"><?= $counts['data_analytics'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=servers_infra') ?>" class="category-filter-chip <?= $currentCategory === 'servers_infra' ? 'active' : '' ?>">
            <span>Servidores & Infra</span>
            <span class="badge bg-dark ms-1"><?= $counts['servers_infra'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=software_dev') ?>" class="category-filter-chip <?= $currentCategory === 'software_dev' ? 'active' : '' ?>">
            <span>Desarrollo & Software</span>
            <span class="badge bg-dark ms-1"><?= $counts['software_dev'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=tip') ?>" class="category-filter-chip <?= $currentCategory === 'tip' ? 'active' : '' ?>">
            <span>Tips Técnicos</span>
            <span class="badge bg-dark ms-1"><?= $counts['tip'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=idea') ?>" class="category-filter-chip <?= $currentCategory === 'idea' ? 'active' : '' ?>">
            <span>Ideas & Arquitectura</span>
            <span class="badge bg-dark ms-1"><?= $counts['idea'] ?></span>
        </a>
        <a href="<?= url('/lounge?category=blocker') ?>" class="category-filter-chip <?= $currentCategory === 'blocker' ? 'active' : '' ?>">
            <span>Consultas Técnicas & Escalabilidad</span>
            <span class="badge bg-dark ms-1"><?= $counts['blocker'] ?></span>
        </a>
    </div>

    <!-- Feed de Publicaciones -->
    <div class="row g-4" id="loungeFeed">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <?php 
                    $author = $post->user(); 
                    $comments = $post->comments();
                    $commentsCount = count($comments);
                    $reactions = $post->reactionsSummary();
                    $totalReactions = $post->totalReactions();
                    $userReact = $post->userReaction($currentUserId);
                    $tags = $post->tagsArray();
                    $ytId = $post->youTubeId();
                ?>
                <div class="col-12" id="post-<?= $post->id ?>">
                    <div class="card-custom p-4">
                        <!-- Header del Post: Autor + Sede + Categoría -->
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= e($author ? $author->avatarUrl() : '') ?>" alt="<?= e($author ? $author->name : 'Dev') ?>" class="rounded-circle border border-secondary" width="44" height="44" style="object-fit: cover;" loading="lazy">
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold small" style="color: #1F2937;"><?= e($author ? $author->name : 'Consultor Devioz') ?></span>
                                        <span class="badge <?= ($author && $author->isAdmin()) ? 'bg-danger' : 'bg-emerald' ?>" style="font-size: 0.65rem; padding: 2px 6px;">
                                            <?= ($author && $author->isAdmin()) ? 'Administrador' : 'Desarrollador' ?>
                                        </span>
                                        <?php if ($author && $author->sede): ?>
                                            <span class="badge-sede" style="font-size: 0.65rem; padding: 2px 6px;"><?= e($author->sede) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-secondary" style="font-size: 0.72rem;"><?= format_date($post->created_at) ?></span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <?= $post->categoryBadge() ?>
                            </div>
                        </div>

                        <!-- Título del Post -->
                        <h5 class="fw-bold text-white mb-2"><?= e($post->title) ?></h5>

                        <!-- Tags Tecnológicos si existen -->
                        <?php if (!empty($tags)): ?>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <?php foreach ($tags as $tag): ?>
                                    <span class="tech-tag-badge">#<?= e($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Cuerpo de la Publicación -->
                        <p class="text-secondary small mb-3" style="line-height: 1.6; white-space: pre-line;"><?= e($post->content) ?></p>

                        <!-- Tarjeta de Vista Previa de Enlace Compartido (Tipo WhatsApp / Redes Sociales) -->
                        <?php if ($post->hasLinkPreview()): ?>
                            <a href="<?= e($post->link_url) ?>" target="_blank" rel="noopener noreferrer" class="lounge-link-card" title="Abrir enlace en nueva pestaña">
                                <div class="lounge-link-img-wrapper position-relative">
                                    <img src="<?= e($post->link_image ?: 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&auto=format&fit=crop&q=80') ?>" alt="<?= e($post->link_title) ?>" class="lounge-link-img" loading="lazy">
                                    <?php if ($ytId): ?>
                                        <!-- Play button overlay para YouTube sin oscurecer la imagen -->
                                        <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 2;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.95); color: #fff;">
                                                <svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="lounge-link-body">
                                    <div class="lounge-link-domain">
                                        <?php if ($ytId): ?>
                                            <span class="badge bg-danger p-1" style="font-size: 0.6rem;">YOUTUBE</span>
                                        <?php elseif (str_contains($post->link_domain ?? '', 'github')): ?>
                                            <span class="badge bg-dark border border-secondary p-1" style="font-size: 0.6rem;">GITHUB</span>
                                        <?php elseif (str_contains($post->link_domain ?? '', 'aws')): ?>
                                            <span class="badge bg-warning text-dark p-1" style="font-size: 0.6rem;">AWS CLOUD</span>
                                        <?php else: ?>
                                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                                        <?php endif; ?>
                                        <span><?= e($post->link_domain ?: 'ENLACE COMPARTIDO') ?></span>
                                    </div>
                                    <div class="lounge-link-title"><?= e($post->link_title ?: $post->link_url) ?></div>
                                    <?php if ($post->link_description): ?>
                                        <div class="lounge-link-desc"><?= e($post->link_description) ?></div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endif; ?>

                        <!-- Bloque de Código Mejorado (IDE Editor Theme) -->
                        <?php if (!empty($post->code_snippet)): ?>
                            <div class="lounge-code-wrapper">
                                <div class="lounge-code-header">
                                    <span class="d-flex align-items-center gap-2">
                                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                                        <span>CODE SNIPPET / MANIFIESTO</span>
                                    </span>
                                    <button type="button" class="btn btn-ghost btn-sm py-0 px-2 text-secondary" data-copy="<?= e($post->code_snippet) ?>" title="Copiar código">Copiar</button>
                                </div>
                                <pre class="lounge-code-body"><?= e($post->code_snippet) ?></pre>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap justify-content-between align-items-center pt-2 gap-2">
                            <!-- Botón y Menú de Reacciones -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="lounge-reactions-container" data-post-id="<?= $post->id ?>">
                                    <button type="button" class="reaction-trigger-btn <?= $userReact ? 'active' : '' ?>" id="reactionTrigger-<?= $post->id ?>" title="Reaccionar a esta publicación">
                                        <span class="reaction-icon-active">
                                            <?= match($userReact) {
                                                'rocket' => '<i class="fa-solid fa-rocket" aria-hidden="true"></i>',
                                                'idea'   => '<i class="fa-solid fa-lightbulb" aria-hidden="true"></i>',
                                                'fire'   => '<i class="fa-solid fa-fire" aria-hidden="true"></i>',
                                                'heart'  => '<i class="fa-solid fa-heart" aria-hidden="true"></i>',
                                                default  => '<i class="fa-solid fa-thumbs-up" aria-hidden="true"></i>'
                                            } ?>
                                        </span>
                                        <span class="reaction-label-active">
                                            <?= match($userReact) {
                                                'rocket' => 'Brutal',
                                                'idea'   => 'Inspirador',
                                                'fire'   => 'Crack',
                                                'heart'  => 'Me encanta',
                                                default  => 'Reaccionar'
                                            } ?>
                                        </span>
                                        <span class="badge bg-dark border border-secondary reaction-total-count"><?= $totalReactions ?></span>
                                    </button>

                                    <!-- Popover Flotante de Emojis al Pasar el Mouse o Clic -->
                                    <div class="reaction-picker-popover shadow-lg" id="reactionPicker-<?= $post->id ?>">
                                        <button type="button" class="reaction-choice-btn" data-reaction="like" title="Me gusta"><i class="fa-solid fa-thumbs-up" aria-hidden="true"></i></button>
                                        <button type="button" class="reaction-choice-btn" data-reaction="rocket" title="Brutal / Despegue"><i class="fa-solid fa-rocket" aria-hidden="true"></i></button>
                                        <button type="button" class="reaction-choice-btn" data-reaction="idea" title="Buena Idea / Arquitectura"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i></button>
                                        <button type="button" class="reaction-choice-btn" data-reaction="fire" title="Crack / Fuego"><i class="fa-solid fa-fire" aria-hidden="true"></i></button>
                                        <button type="button" class="reaction-choice-btn" data-reaction="heart" title="Me encanta"><i class="fa-solid fa-heart" aria-hidden="true"></i></button>
                                    </div>
                                </div>

                                <!-- Resumen de Reacciones Activas -->
                                <div class="d-flex align-items-center gap-1 reactions-summary-pills" id="reactionsSummary-<?= $post->id ?>">
                                    <?php if ($reactions['like'] > 0): ?>
                                        <span class="reaction-pill <?= $userReact === 'like' ? 'active' : '' ?>" data-reaction="like"><i class="fa-solid fa-thumbs-up" aria-hidden="true"></i> <?= $reactions['like'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($reactions['rocket'] > 0): ?>
                                        <span class="reaction-pill <?= $userReact === 'rocket' ? 'active' : '' ?>" data-reaction="rocket"><i class="fa-solid fa-rocket" aria-hidden="true"></i> <?= $reactions['rocket'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($reactions['idea'] > 0): ?>
                                        <span class="reaction-pill <?= $userReact === 'idea' ? 'active' : '' ?>" data-reaction="idea"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> <?= $reactions['idea'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($reactions['fire'] > 0): ?>
                                        <span class="reaction-pill <?= $userReact === 'fire' ? 'active' : '' ?>" data-reaction="fire"><i class="fa-solid fa-fire" aria-hidden="true"></i> <?= $reactions['fire'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($reactions['heart'] > 0): ?>
                                        <span class="reaction-pill <?= $userReact === 'heart' ? 'active' : '' ?>" data-reaction="heart"><i class="fa-solid fa-heart" aria-hidden="true"></i> <?= $reactions['heart'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Botón para Abrir / Cerrar Pestaña de Comentarios -->
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-ghost btn-sm d-flex align-items-center gap-2 text-secondary" data-bs-toggle="collapse" data-bs-target="#commentsSection-<?= $post->id ?>" aria-expanded="false" aria-controls="commentsSection-<?= $post->id ?>">
                                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span>Comentarios</span>
                                    <span class="badge bg-dark border border-secondary comments-counter-badge"><?= $commentsCount ?></span>
                                </button>
                                <span class="text-secondary small font-monospace d-none d-md-inline" style="font-size: 0.72rem;">#<?= $post->id ?></span>
                            </div>
                        </div>

                        <!-- Sección / Pestaña de Comentarios (Colapsable) -->
                        <div class="collapse <?= $commentsCount > 0 ? '' : '' ?>" id="commentsSection-<?= $post->id ?>">
                            <div class="lounge-comments-section">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fw-bold small text-white d-flex align-items-center gap-2">
                                        <span>Hilo de Debate y Retroalimentación Técnica</span>
                                    </span>
                                    <span class="small text-secondary font-monospace"><?= $commentsCount ?> aportes</span>
                                </div>

                                <!-- Lista de Comentarios -->
                                <div class="comments-list d-flex flex-column gap-2 mb-3" id="commentsList-<?= $post->id ?>">
                                    <?php if (!empty($comments)): ?>
                                        <?php foreach ($comments as $comment): ?>
                                            <?php $cUser = $comment->user(); ?>
                                            <div class="d-flex gap-2 align-items-start comment-item">
                                                <img src="<?= e($cUser ? $cUser->avatarUrl() : '') ?>" class="rounded-circle border border-secondary flex-shrink-0" width="32" height="32" alt="<?= e($cUser ? $cUser->name : 'Dev') ?>" loading="lazy">
                                                <div class="comment-bubble">
                                                    <div class="d-flex justify-content-between align-items-center mb-1 gap-2">
                                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                                            <span class="fw-bold small" style="color: #1F2937;"><?= e($cUser ? $cUser->name : 'Usuario') ?></span>
                                                            <span class="badge <?= ($cUser && $cUser->isAdmin()) ? 'bg-danger' : 'bg-emerald' ?> p-1" style="font-size: 0.6rem;">
                                                                <?= ($cUser && $cUser->isAdmin()) ? 'Administrador' : 'Desarrollador' ?>
                                                            </span>
                                                            <?php if ($cUser && $cUser->sede): ?>
                                                                <span class="badge-sede" style="font-size: 0.6rem; padding: 1px 5px;"><?= e($cUser->sede) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="text-secondary small font-monospace" style="font-size: 0.68rem;"><?= format_date($comment->created_at) ?></span>
                                                    </div>
                                                    <div class="text-secondary small" style="line-height: 1.45;"><?= nl2br(e($comment->content)) ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-center py-3 text-secondary small empty-comments-msg">
                                            Aún no hay comentarios en esta publicación. ¡Sé el primero en aportar una respuesta o duda!
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Formulario Rápido de Nuevo Comentario -->
                                <form action="<?= url("/lounge/{$post->id}/comments") ?>" method="POST" class="lounge-comment-form d-flex gap-2 align-items-center" data-post-id="<?= $post->id ?>">
                                    <?= csrf_field() ?>
                                    <input type="text" name="content" class="form-control form-control-custom form-control-sm comment-input-text" placeholder="Escribe tu comentario o respuesta técnica..." required autocomplete="off">
                                    <button type="submit" class="btn btn-brand btn-sm px-3 flex-shrink-0">Enviar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card-custom p-5 text-center">
                    <div class="display-6 mb-2"><i class="fa-solid fa-comments" aria-hidden="true"></i></div>
                    <h5 class="fw-bold text-white mb-2">No hay publicaciones en esta categoría de consultoría</h5>
                    <p class="text-secondary small mb-3">Sé el primero en compartir un repositorio, video de sustentación, tip de nube o arquitectura con el equipo.</p>
                    <button class="btn btn-brand btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#composerCollapse">Crear Publicación</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
