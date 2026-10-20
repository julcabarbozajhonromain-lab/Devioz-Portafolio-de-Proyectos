<?php

declare(strict_types=1);

/** @var \App\Models\User $currentUser */
/** @var array $contacts */
/** @var int $selectedContactId */
/** @var string $selectedChannel */
/** @var array $companyNews */
/** @var array $generalMessages */
/** @var array $personalNotes */
?>
<?php
$isEmptyState = ($selectedChannel === '' && $selectedContactId === 0);
$directCount = count($contacts);
?>
<style>
    /* En el Messenger unificado ocultamos los accesos flotantes duplicados */
    #deviozFloatingDock, #botAvatarSofiaBtn, #botPillSofiaBtn, #deviozBotWindow { display: none !important; }
</style>

<div class="devioz-messenger-wrapper" data-initial-channel="<?= e($selectedChannel) ?>" data-initial-contact="<?= (int)$selectedContactId ?>">
    <!-- Contenedor Principal (Estilo Devioz Messenger: Rail + Lista + Conversación) -->
    <div class="devioz-messenger-container has-rail">

        <!-- ===================================================================== -->
        <!-- RAIL VERTICAL DE CATEGORÍAS (Devioz: All · Tasks · CoPilot · Canales) -->
        <!-- ===================================================================== -->
        <nav class="ui-msg-rail" aria-label="Categorías del Messenger">
            <button type="button" class="ui-rail-btn active" data-rail-filter="all" title="Todos los chats">
                <span class="ui-rail-icon"><i class="fa-solid fa-house"></i></span>
                <span class="ui-rail-label">Todos</span>
                <span class="ui-rail-badge d-none" id="uiRailUnreadBadge">0</span>
            </button>
            <button type="button" class="ui-rail-btn" data-rail-filter="copilot" title="CoPilot LABVA AI">
                <span class="ui-rail-icon ui-rail-icon-copilot"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                <span class="ui-rail-label">CoPilot</span>
            </button>
            <button type="button" class="ui-rail-btn" data-rail-filter="channels" title="Canales">
                <span class="ui-rail-icon"><i class="fa-solid fa-bullhorn"></i></span>
                <span class="ui-rail-label">Canales</span>
            </button>
            <button type="button" class="ui-rail-btn" data-rail-filter="direct" title="Equipo técnico">
                <span class="ui-rail-icon"><i class="fa-solid fa-user-group"></i></span>
                <span class="ui-rail-label">Equipo</span>
            </button>
            <button type="button" class="ui-rail-btn" data-rail-filter="notes" title="Mis notas">
                <span class="ui-rail-icon"><i class="fa-solid fa-bookmark"></i></span>
                <span class="ui-rail-label">Notas</span>
            </button>
        </nav>

        <!-- ===================================================================== -->
        <!-- PANEL IZQUIERDO: LISTA DE CHATS, CANALES Y EMPLEADOS                 -->
        <!-- ===================================================================== -->
        <aside class="devioz-chat-sidebar">
            <!-- Barra Superior de Herramientas -->
            <div class="devioz-chat-sidebar-header">
                <button type="button" class="btn-icon-devioz" title="Directorio de Contactos" id="chatContactsDirectoryBtn">
                    <i class="fa-solid fa-address-book"></i>
                </button>
                <div class="devioz-chat-search-box flex-grow-1">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="deviozChatSearchInput" placeholder="Buscar un empleado o chat" autocomplete="off">
                    <button type="button" class="clear-search-btn d-none" id="deviozChatSearchClear">&times;</button>
                </div>
                <button type="button" class="btn-icon-devioz text-primary" title="Nuevo Chat" id="chatNewConversationBtn">
                    <i class="fa-regular fa-pen-to-square"></i>
                </button>
            </div>

            <!-- Chips de Filtro (Devioz: All / Unread) -->
            <div class="ui-msg-chips">
                <button type="button" class="ui-msg-chip active" data-read-filter="all">Todos</button>
                <button type="button" class="ui-msg-chip" data-read-filter="unread">No leídos <span class="ui-chip-count d-none" id="uiUnreadChipCount">0</span></button>
            </div>

            <!-- Listado de Conversaciones & Canales -->
            <div class="devioz-chat-list" id="deviozChatList">

                <!-- 1. Canal CoPilot LABVA AI / Soporte -->
                <div class="devioz-chat-item <?= $selectedChannel === 'copilot' ? 'active' : '' ?>"
                     data-rail-group="copilot"
                     data-channel-type="copilot"
                     data-channel-id="copilot"
                     data-channel-title="Soporte Devioz &bull; CoPilot LABVA"
                     data-channel-subtitle="Estamos listos para responder sus preguntas técnicas.">
                    <div class="devioz-chat-avatar position-relative">
                        <img src="<?= asset('images/sofia-avatar.webp') ?>" alt="CoPilot" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=CoPilot&background=A855F7&color=fff';">
                        <span class="copilot-sparkle-dot"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                    </div>
                    <div class="devioz-chat-info">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="chat-title d-flex align-items-center gap-1">
                                <span class="badge-devioz-bot">24</span>
                                <strong>Soporte Devioz</strong>
                            </span>
                            <span class="chat-time text-muted">Hoy</span>
                        </div>
                        <div class="chat-snippet text-truncate">
                            ¡Hola! Soy su asistente de Devioz 24. Puedo ofrecerle ayuda para...
                        </div>
                    </div>
                </div>

                <!-- 2. Canal Noticias de la Compañía -->
                <div class="devioz-chat-item <?= $selectedChannel === 'news' ? 'active' : '' ?>"
                     data-rail-group="channels" data-channel-type="news"
                     data-channel-id="news"
                     data-channel-title="Noticias de la compañía"
                     data-channel-subtitle="1 suscriptor &bull; Comunicados oficiales y arquitectura">
                    <div class="devioz-chat-avatar">
                        <div class="channel-icon-box bg-emerald-box">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                    </div>
                    <div class="devioz-chat-info">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="chat-title"><strong>Noticias de la compañía</strong></span>
                            <span class="chat-time text-muted">Sáb</span>
                        </div>
                        <div class="chat-snippet text-truncate">
                            Comparta noticias e información importante. Sígalas para mantenerse al tanto...
                        </div>
                    </div>
                </div>

                <!-- 3. Canal Chat General -->
                <div class="devioz-chat-item <?= $selectedChannel === 'general' ? 'active' : '' ?>"
                     data-rail-group="channels" data-channel-type="general"
                     data-channel-id="general"
                     data-channel-title="Chat general"
                     data-channel-subtitle="Canal público de ingeniería &bull; Todo el equipo">
                    <div class="devioz-chat-avatar">
                        <div class="channel-icon-box bg-emerald-box">
                            <i class="fa-solid fa-comments"></i>
                        </div>
                    </div>
                    <div class="devioz-chat-info">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="chat-title"><strong>Chat general</strong></span>
                            <span class="chat-time text-muted">Sáb</span>
                        </div>
                        <div class="chat-snippet text-truncate">
                            Utilice el chat general para comunicarse, intercambiar ideas y compartir su...
                        </div>
                    </div>
                </div>

                <!-- 4. Canal Notas Personales -->
                <div class="devioz-chat-item <?= $selectedChannel === 'notes' ? 'active' : '' ?>"
                     data-rail-group="notes" data-channel-type="notes"
                     data-channel-id="notes"
                     data-channel-title="Notas"
                     data-channel-subtitle="Visible solamente para usted &bull; Bloc de notas personal">
                    <div class="devioz-chat-avatar">
                        <div class="channel-icon-box bg-blue-box">
                            <i class="fa-solid fa-bookmark"></i>
                        </div>
                    </div>
                    <div class="devioz-chat-info">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="chat-title"><strong>Notas</strong></span>
                            <span class="chat-time text-muted">0:23</span>
                        </div>
                        <div class="chat-snippet text-truncate">
                            Mis notas: Un bloc de notas para guardar mensajes, archivos y enlaces...
                        </div>
                    </div>
                </div>

                <!-- Separador de Equipo Técnico -->
                <div class="devioz-chat-category-header">
                    <span>EQUIPO TÉCNICO & DIRECTO</span>
                    <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;"><?= count($contacts) ?></span>
                </div>

                <!-- Lista de Contactos Directos (Usuarios 1 a 1) -->
                <?php foreach ($contacts as $contact): ?>
                    <div class="devioz-chat-item <?= ($selectedContactId === (int)$contact->id) ? 'active' : '' ?>"
                         data-rail-group="direct" data-channel-type="direct"
                         data-contact-id="<?= (int)$contact->id ?>"
                         data-channel-title="<?= e($contact->name) ?>"
                         data-channel-subtitle="<?= e($contact->isAdmin() ? 'Administrador QA' : 'Ingeniero de Software') ?> &bull; <?= e($contact->sede ?: 'Central') ?>"
                         data-contact-avatar="<?= e($contact->avatarUrl()) ?>"
                         data-contact-role="<?= e($contact->role) ?>">
                        <div class="devioz-chat-avatar position-relative">
                            <img src="<?= e($contact->avatarUrl()) ?>" alt="<?= e($contact->name) ?>" class="rounded-circle" loading="lazy">
                            <span class="online-indicator-dot"></span>
                        </div>
                        <div class="devioz-chat-info">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="chat-title text-truncate">
                                    <strong><?= e($contact->name) ?></strong>
                                </span>
                                <span class="badge-sede" style="font-size: 0.60rem;"><?= e($contact->sede ?: 'Cercado') ?></span>
                            </div>
                            <div class="chat-snippet text-truncate">
                                <?= e($contact->isAdmin() ? 'Supervisión Técnica' : 'Desarrollo Activo') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </aside>

        <!-- ===================================================================== -->
        <!-- PANEL DERECHO: VENTANA DE CONVERSACIÓN ACTIVA                         -->
        <!-- ===================================================================== -->
        <main class="devioz-chat-main <?= $isEmptyState ? 'is-empty' : '' ?>" id="deviozChatMain">

            <!-- ESTADO VACÍO (Devioz: "Seleccione un chat para empezar a comunicarse") -->
            <div class="ui-msg-empty-state" id="uiMsgEmptyState">
                <div class="ui-empty-illustration">
                    <div class="ui-empty-card ui-empty-card-back"><i class="fa-solid fa-table-columns"></i></div>
                    <div class="ui-empty-card ui-empty-card-front">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="ui-empty-bubble ui-empty-bubble-copilot"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                    <div class="ui-empty-bubble ui-empty-bubble-chat"><i class="fa-solid fa-comments"></i></div>
                    <div class="ui-empty-bubble ui-empty-bubble-video"><i class="fa-solid fa-video"></i></div>
                </div>
                <h5 class="ui-empty-title">Seleccione un chat para empezar a comunicarse</h5>
                <div class="ui-empty-or">o</div>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <button type="button" class="ui-empty-btn" data-open-channel="copilot">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Preguntar a CoPilot
                    </button>
                    <button type="button" class="ui-empty-btn" id="uiEmptyNewChatBtn">
                        <i class="fa-regular fa-pen-to-square me-1"></i> Nuevo chat
                    </button>
                </div>
            </div>

            <!-- Cabecera de la Conversación Activa -->
            <header class="devioz-chat-header" id="activeChatHeader">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="active-chat-avatar" id="activeChatAvatarBox">
                        <img src="<?= asset('images/sofia-avatar.webp') ?>" alt="Avatar" class="rounded-circle" id="activeChatAvatarImg" onerror="this.src='https://ui-avatars.com/api/?name=CoPilot&background=A855F7&color=fff';">
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-devioz-bot d-none" id="activeChatBotBadge">24</span>
                            <h6 class="fw-bold mb-0 text-dark text-truncate" id="activeChatTitle" style="font-size: 1rem;">
                                Soporte Devioz &bull; CoPilot LABVA
                            </h6>
                        </div>
                        <div class="text-secondary small text-truncate" id="activeChatSubtitle" style="font-size: 0.75rem;">
                            Estamos listos para responder sus preguntas técnicas.
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas del Header (Video llamada Meet, Búsqueda, etc.) -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <!-- Botón Videollamada HD Devioz Meet -->
                    <button type="button" class="btn btn-sm btn-devioz-videocall d-flex align-items-center gap-2" id="chatHeaderVideocallBtn" title="Iniciar Videollamada HD Devioz Meet">
                        <i class="fa-solid fa-video"></i>
                        <span class="d-none d-md-inline">Reunión Meet</span>
                    </button>
                    <button type="button" class="btn-icon-devioz" title="Buscar en esta conversación" id="chatSearchInThreadBtn">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <button type="button" class="btn-icon-devioz" title="Alternar panel de información" id="chatToggleInfoPanelBtn">
                        <i class="fa-solid fa-table-columns"></i>
                    </button>
                </div>
            </header>

            <!-- Área de Mensajes Desplazable -->
            <div class="devioz-chat-stream" id="deviozChatStream">

                <!-- VISTA 1: CoPilot LABVA AI / Soporte Devioz (Por Defecto) -->
                <div class="chat-view-container <?= ($selectedChannel === 'copilot' && $selectedContactId === 0) ? '' : 'd-none' ?>" id="viewCopilot">
                    <div class="chat-date-divider">
                        <span>ayer</span>
                    </div>

                    <!-- Mensaje de Bienvenida del Asistente -->
                    <div class="devioz-msg-bubble incoming">
                        <div class="msg-avatar">
                            <img src="<?= asset('images/sofia-avatar.webp') ?>" alt="CoPilot" class="rounded-circle" onerror="this.src='https://ui-avatars.com/api/?name=CoPilot&background=A855F7&color=fff';">
                        </div>
                        <div class="msg-content">
                            <div class="msg-author d-flex align-items-center gap-2">
                                <span class="badge-devioz-bot">24</span>
                                <strong>Soporte de Devioz &bull; CoPilot</strong>
                            </div>
                            <div class="msg-body">
                                <p class="mb-2">¡Hola! Soy el asistente de <strong>Devioz 24</strong>.</p>
                                <p class="mb-2">Puedo ofrecerle ayuda técnica para:</p>
                                <ul class="mb-3 ps-3">
                                    <li>Auditar estándares de calidad de software <strong>ISO/IEC 25010</strong></li>
                                    <li>Verificar cumplimiento de seguridad <strong>OWASP Top 10</strong></li>
                                    <li>Inspeccionar Quality Gate y métricas <strong>SonarQube</strong></li>
                                    <li>Generar el <strong>Dossier Oficial de Certificación</strong> en PDF</li>
                                    <li>¡O responder consultas de arquitectura y base de datos cuando lo necesite!</li>
                                </ul>
                                <p class="mb-2">Pregunte con confianza, estoy en línea 24/7, u obtenga más información con los accesos directos:</p>

                                <!-- Chips Rápidos de CoPilot -->
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button type="button" class="copilot-chip-btn" data-copilot-prompt="Realiza una auditoría de calidad de software bajo la norma ISO/IEC 25010 para este sistema.">
                                        <i class="fa-solid fa-chart-radar text-info me-1"></i> Análisis ISO 25010
                                    </button>
                                    <button type="button" class="copilot-chip-btn" data-copilot-prompt="Evalúa los riesgos de seguridad y controles según OWASP Top 10 en los módulos del proyecto.">
                                        <i class="fa-solid fa-shield-halved text-warning me-1"></i> Auditoría OWASP
                                    </button>
                                    <button type="button" class="copilot-chip-btn" data-copilot-prompt="Genera el reporte de SonarQube con deuda técnica y estado de Quality Gate.">
                                        <i class="fa-solid fa-code text-success me-1"></i> Quality Gate
                                    </button>
                                    <button type="button" class="copilot-chip-btn" data-copilot-prompt="Prepara el resumen ejecutivo del dossier de certificación técnica.">
                                        <i class="fa-solid fa-file-contract text-primary me-1"></i> Dossier PDF
                                    </button>
                                </div>

                                <!-- Tarjeta de Enlace Devioz AI Assistant -->
                                <div class="devioz-link-attachment-card">
                                    <div class="attachment-border-line"></div>
                                    <div class="attachment-body">
                                        <div class="fw-bold text-dark small mb-1">Asistente de IA en los chats corporativos</div>
                                        <div class="text-secondary small">
                                            En los chats de Devioz 24 cuentas con un copiloto de ingeniería de software diseñado para acelerar tus revisiones de código y dictámenes de QA.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="msg-footer text-end">
                                <span class="msg-time">16:27</span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensajes dinámicos generados por el usuario con CoPilot -->
                    <div id="copilotDynamicMessages"></div>
                </div>

                <!-- VISTA 2: Notas Personales -->
                <div class="chat-view-container <?= $selectedChannel === 'notes' ? '' : 'd-none' ?>" id="viewNotes">
                    <div class="chat-date-divider">
                        <span>hoy</span>
                    </div>

                    <!-- Placeholder Ilustrado de Mis Notas -->
                    <div class="devioz-channel-banner-card text-center my-4">
                        <div class="channel-banner-icon bg-blue-subtle text-primary mb-3">
                            <i class="fa-solid fa-bookmark fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Mis notas</h5>
                        <p class="text-secondary small mx-auto" style="max-width: 460px;">
                            Un bloc de notas para guardar mensajes, archivos y enlaces importantes en un solo lugar. Solo tú tienes acceso a este espacio.
                        </p>
                    </div>

                    <div id="notesMessagesContainer">
                        <?php foreach ($personalNotes as $note): ?>
                            <div class="devioz-msg-bubble outgoing">
                                <div class="msg-content">
                                    <div class="msg-body">
                                        <?= nl2br(e($note['body'])) ?>
                                    </div>
                                    <div class="msg-footer text-end">
                                        <span class="msg-time"><?= date('H:i', strtotime($note['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- VISTA 3: Noticias de la Compañía -->
                <div class="chat-view-container <?= $selectedChannel === 'news' ? '' : 'd-none' ?>" id="viewNews">
                    <div class="chat-date-divider">
                        <span>ayer</span>
                    </div>

                    <!-- Banner Informativo de Noticias -->
                    <div class="devioz-channel-banner-card text-center my-4">
                        <div class="channel-banner-icon bg-emerald-subtle text-emerald mb-3">
                            <i class="fa-solid fa-newspaper fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Noticias de la compañía</h5>
                        <ul class="text-secondary small list-unstyled d-flex flex-column gap-1 mx-auto" style="max-width: 440px;">
                            <li><i class="fa-solid fa-share text-emerald me-2"></i> Publique noticias y anuncios de la compañía</li>
                            <li><i class="fa-regular fa-eye text-info me-2"></i> Manténgase informado sobre los acontecimientos</li>
                            <li><i class="fa-regular fa-thumbs-up text-primary me-2"></i> Deje comentarios y Me gusta</li>
                        </ul>
                    </div>

                    <!-- Listado de Noticias Corporativas -->
                    <div class="d-flex flex-column gap-3" id="companyNewsFeed">
                        <?php foreach ($companyNews as $news): ?>
                            <?php $author = $news->user(); ?>
                            <div class="devioz-feed-post-card">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img src="<?= e($author ? $author->avatarUrl() : '') ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                                    <div>
                                        <strong class="text-dark small"><?= e($author ? $author->name : 'Administración Devioz') ?></strong>
                                        <div class="text-muted" style="font-size: 0.70rem;"><?= format_date($news->created_at) ?></div>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-1"><?= e($news->title) ?></h6>
                                <p class="text-secondary small mb-2"><?= nl2br(e($news->content)) ?></p>
                                <?php if ($news->hasLinkPreview()): ?>
                                    <a href="<?= e($news->link_url) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none d-block p-2 rounded bg-light border small text-primary mb-2">
                                        <i class="fa-solid fa-link me-1"></i> <?= e($news->link_title ?: $news->link_url) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- VISTA 4: Chat General -->
                <div class="chat-view-container <?= $selectedChannel === 'general' ? '' : 'd-none' ?>" id="viewGeneral">
                    <div class="chat-date-divider">
                        <span>ayer</span>
                    </div>

                    <!-- Banner Informativo del Chat General -->
                    <div class="devioz-channel-banner-card text-center my-4">
                        <div class="channel-banner-icon bg-info-subtle text-info mb-3">
                            <i class="fa-solid fa-comments fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Chat general</h5>
                        <ul class="text-secondary small list-unstyled d-flex flex-column gap-1 mx-auto" style="max-width: 440px;">
                            <li><i class="fa-solid fa-users text-info me-2"></i> Comuníquese y comparta su experiencia</li>
                            <li><i class="fa-solid fa-bolt text-warning me-2"></i> Intercambie ideas de arquitectura</li>
                            <li><i class="fa-solid fa-rotate text-success me-2"></i> Manténgase sincronizado con su equipo</li>
                        </ul>
                    </div>

                    <div id="generalMessagesContainer">
                        <?php foreach ($generalMessages as $gMsg): ?>
                            <div class="devioz-msg-bubble <?= ($gMsg['sender_id'] == $currentUser->id) ? 'outgoing' : 'incoming' ?>">
                                <?php if ($gMsg['sender_id'] != $currentUser->id): ?>
                                    <div class="msg-avatar">
                                        <img src="<?= e(!empty($gMsg['sender_avatar']) ? $gMsg['sender_avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($gMsg['sender_name']).'&background=00ACE3&color=fff') ?>" alt="Avatar" class="rounded-circle">
                                    </div>
                                <?php endif; ?>
                                <div class="msg-content">
                                    <?php if ($gMsg['sender_id'] != $currentUser->id): ?>
                                        <div class="msg-author">
                                            <strong><?= e($gMsg['sender_name']) ?></strong>
                                        </div>
                                    <?php endif; ?>
                                    <div class="msg-body">
                                        <?= nl2br(e($gMsg['body'])) ?>
                                    </div>
                                    <div class="msg-footer text-end">
                                        <span class="msg-time"><?= date('H:i', strtotime($gMsg['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- VISTA 5: Conversación Directa 1 a 1 con Usuario -->
                <div class="chat-view-container <?= $selectedContactId > 0 ? '' : 'd-none' ?>" id="viewDirect">
                    <div class="chat-date-divider">
                        <span id="directChatDateLabel">hoy</span>
                    </div>
                    <div id="directMessagesContainer" class="d-flex flex-column gap-2">
                        <!-- Mensajes cargados dinámicamente vía AJAX desde /api/chat/conversation -->
                        <div class="text-center py-4 text-muted small" id="directLoadingIndicator">
                            <i class="fa-solid fa-spinner fa-spin me-2"></i> Cargando conversación segura...
                        </div>
                    </div>
                </div>

            </div>

            <!-- ================================================================= -->
            <!-- PIE FLOTANTE: COMPOSITOR DE MENSAJE Devioz                  -->
            <!-- ================================================================= -->
            <footer class="devioz-chat-footer">
                <form id="deviozComposerForm" class="devioz-composer-box">
                    <!-- Botón Clip Adjuntos -->
                    <button type="button" class="composer-icon-btn" id="composerAttachBtn" title="Adjuntar archivo o imagen">
                        <i class="fa-solid fa-paperclip"></i>
                    </button>

                    <!-- Campo de Entrada Principal -->
                    <div class="composer-input-wrapper flex-grow-1">
                        <textarea id="deviozMessageInput"
                                  rows="1"
                                  placeholder="Escriba @ o + para mencionar a una persona, un chat o una IA"
                                  autocomplete="off"></textarea>
                    </div>

                    <!-- Botones de Acción Derecha: Grid, Emojis, Micrófono y Enviar -->
                    <div class="composer-actions-right d-flex align-items-center gap-1">
                        <button type="button" class="composer-icon-btn d-none d-md-flex" title="Plantillas y Comandos" id="composerTemplatesBtn">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                        <button type="button" class="composer-icon-btn" title="Insertar Emoticono" id="composerEmojiBtn">
                            <i class="fa-regular fa-face-smile"></i>
                        </button>
                        <button type="button" class="composer-icon-btn d-none d-sm-flex" title="Mensaje de voz (Presione para grabar)" id="composerMicBtn">
                            <i class="fa-solid fa-microphone"></i>
                        </button>
                        <button type="submit" class="composer-send-btn" id="composerSendBtn" title="Enviar mensaje (Enter)">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </footer>

        </main>

    </div>
</div>

<!-- Modal para Crear Nuevo Chat / Contacto -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="fa-regular fa-comment-dots text-primary"></i>
                    <span>Iniciar Nueva Conversación</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-secondary small mb-3">Selecciona un miembro del equipo técnico para iniciar una conversación 1 a 1:</p>
                <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                    <?php foreach ($contacts as $c): ?>
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-2 py-2 start-contact-direct-btn" data-contact-id="<?= $c->id ?>">
                            <img src="<?= e($c->avatarUrl()) ?>" class="rounded-circle" width="32" height="32" alt="Avatar">
                            <div class="flex-grow-1 text-start">
                                <div class="fw-semibold small text-dark"><?= e($c->name) ?></div>
                                <div class="text-muted" style="font-size: 0.70rem;"><?= e($c->role) ?> &bull; <?= e($c->sede ?: 'Central') ?></div>
                            </div>
                            <span class="badge bg-primary-subtle text-primary" style="font-size: 0.65rem;">Chatear</span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

