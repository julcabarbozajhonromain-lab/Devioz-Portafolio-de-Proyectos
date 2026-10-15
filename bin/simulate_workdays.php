<?php

declare(strict_types=1);

/**
 * Devioz Classic — Simulador de Ciclo Operativo (CLI)
 * Simula 4 días completos de trabajo y más de 20 asignaciones de proyectos con standups,
 * hitos, entregables y evaluaciones técnicas.
 *
 * Uso: php bin/simulate_workdays.php
 */

require dirname(__DIR__) . '/bootstrap/app.php';

use App\Core\Database;

$pdo = Database::getInstance()->getConnection();

echo "\n=======================================================\n";
echo "  DEVIOZ CLASSIC — SIMULADOR OPERATIVO CORPORATIVO\n";
echo "  Simulación de 4 Días de Trabajo y >20 Asignaciones\n";
echo "=======================================================\n\n";

$pdo->beginTransaction();

try {
    // --------------------------------------------------------------------------
    // 1. Obtener desarrolladores disponibles (sin proyecto activo o sin proyecto)
    // --------------------------------------------------------------------------
    echo "[1/4] Identificando desarrolladores para asignación...\n";

    $unassignedStmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.sede
        FROM users u
        WHERE u.role = 'developer'
          AND u.active = 1
          AND u.id NOT IN (
              SELECT developer_id FROM projects WHERE status IN ('assigned', 'in_progress', 'pending_review', 'in_review')
          )
        ORDER BY u.id ASC
    ");
    $unassignedDevs = $unassignedStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "      Desarrolladores disponibles detectados: " . count($unassignedDevs) . "\n";

    // --------------------------------------------------------------------------
    // 2. Definición de 21 Proyectos Corporativos de Consultoría TI
    // --------------------------------------------------------------------------
    $newProjectsData = [
        [
            'code' => 'DEV-2026-006',
            'name' => 'Gateway de Microservicios y Autenticación OAuth2',
            'desc' => 'API Gateway centralizado con balanceo de carga, validación de tokens JWT asimétricos y rate-limiting para ecosistemas distribuidos.',
            'stack' => ['PHP 8.2', 'Redis', 'Docker', 'OpenSSL', 'Nginx'],
            'cover' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-007',
            'name' => 'Plataforma Cloud de Telemetría e Internet de las Cosas',
            'desc' => 'Sistema de recolección de datos sensoriales en tiempo real vía MQTT y HTTP, persistencia en series temporales y tableros operativos.',
            'stack' => ['PHP 8.2', 'MySQL InnoDB', 'MQTT', 'Chart.js', 'Bootstrap 5'],
            'cover' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ',
            'status' => 'approved',
            'score' => 9.4,
        ],
        [
            'code' => 'DEV-2026-008',
            'name' => 'Conciliación Bancaria y Pagos Automatizados',
            'desc' => 'Motor de conciliación masiva de estados de cuenta bancarios con pasarelas de pago Stripe y Culqi, validación algorítmica de montos.',
            'stack' => ['PHP 8.2', 'MySQL', 'Stripe API', 'cURL', 'Clean Code'],
            'cover' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=L_LUpnjgPso',
            'status' => 'in_review',
        ],
        [
            'code' => 'DEV-2026-009',
            'name' => 'Motor de Búsqueda Semántica y Procesamiento NLP',
            'desc' => 'Buscador corporativo de documentos técnicos con indexación vectorial, similitud de cosenos y sugerencias predictivas.',
            'stack' => ['PHP 8.2', 'MySQL Full-Text', 'Vanilla JS', 'Vector Embeddings', 'CSS3'],
            'cover' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=fJ9rUzIMcZQ',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-010',
            'name' => 'Portal de Gestión Hospitalaria y Telemedicina',
            'desc' => 'Plataforma para programación de citas médicas virtuales, historias clínicas electrónicas bajo estándares HL7 y recetas digitales seguras.',
            'stack' => ['PHP 8.2', 'WebRTC', 'MySQL', 'Bootstrap 5', 'OpenSSL'],
            'cover' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
            'status' => 'approved',
            'score' => 9.7,
        ],
        [
            'code' => 'DEV-2026-011',
            'name' => 'Pipeline CI/CD Automatizado con Infraestructura como Código',
            'desc' => 'Orquestación de pipelines automatizados de construcción, pruebas de regresión, análisis estático SonarQube y despliegue continuo.',
            'stack' => ['Docker', 'GitHub Actions', 'Terraform', 'Bash', 'PHP 8.2'],
            'cover' => 'https://images.unsplash.com/photo-1618401471353-b98afee0b2eb?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-012',
            'name' => 'ERP de Gestión de Inventarios y Logística de Distribución',
            'desc' => 'Módulo de Kardex permanente, picking y packing con códigos de barra, optimización de rutas de reparto y alertas automáticas de stock.',
            'stack' => ['PHP 8.2', 'MySQL InnoDB', 'Zxing JS', 'Bootstrap 5', 'PWA'],
            'cover' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=JGwWNGJdvx8',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-013',
            'name' => 'CRM Omnicanal para Atención al Cliente con WhatsApp API',
            'desc' => 'Centralización de conversaciones de múltiples canales, asignación inteligente de agentes, historial de tickets y bots de autoatención.',
            'stack' => ['PHP 8.2', 'Meta Cloud API', 'WebSockets', 'MySQL', 'Tailwind'],
            'cover' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=OPf0YbXqDm0',
            'status' => 'pending_review',
        ],
        [
            'code' => 'DEV-2026-014',
            'name' => 'Plataforma de Aprendizaje Corporativo y Streaming WebRTC',
            'desc' => 'Aula virtual con reproductor de video adaptativo HLS, evaluaciones en tiempo real y generación automática de certificados PDF.',
            'stack' => ['PHP 8.2', 'HLS.js', 'FPDF', 'MySQL', 'Vanilla JS'],
            'cover' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=hT_nvWreIhg',
            'status' => 'approved',
            'score' => 9.2,
        ],
        [
            'code' => 'DEV-2026-015',
            'name' => 'Sistema de Auditoría de Ciberseguridad y Detección de Anomalías',
            'desc' => 'Monitoreo de accesos no autorizados, detección de inyecciones SQL y ataques XSS mediante análisis heurístico de logs de servidor.',
            'stack' => ['PHP 8.2', 'Regex Engine', 'GeoIP', 'MySQL InnoDB', 'Bash'],
            'cover' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=09R8_2nJtjg',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-016',
            'name' => 'Aplicación PWA para Control de Flotas y Trazabilidad GPS',
            'desc' => 'Monitoreo satelital de vehículos de transporte en mapa interactivo con Leaflet.js, geocercas poligonales y reportes de combustible.',
            'stack' => ['PHP 8.2', 'Leaflet JS', 'PWA ServiceWorker', 'MySQL Spatial', 'OpenStreetMap'],
            'cover' => 'https://images.unsplash.com/photo-1519003722824-194d4455a60c?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=RgKAFK5djSk',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-017',
            'name' => 'Motor de Recomendaciones y Analítica Predictiva de Compras',
            'desc' => 'Algoritmo de filtrado colaborativo basado en patrones de consumo previos, cálculo de canasta de mercado y segmentación RFM.',
            'stack' => ['PHP 8.2', 'MySQL', 'Data Analytics', 'Chart.js', 'REST API'],
            'cover' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=V-_O7nl0Ii0',
            'status' => 'in_review',
        ],
        [
            'code' => 'DEV-2026-018',
            'name' => 'Sistema Contable y Facturación Electrónica Multi-empresa',
            'desc' => 'Plan de cuentas dinámico, asientos contables automáticos con partida doble, balance general y emisión de XML UBL 2.1.',
            'stack' => ['PHP 8.2', 'OpenSSL', 'DOMDocument', 'MySQL InnoDB', 'Bootstrap 5'],
            'cover' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=fRh_vgS2dFE',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-019',
            'name' => 'Plataforma de Firma Electrónica Segura y Archivo Digital',
            'desc' => 'Flujo de firma digital con certificados X.509, sellado de tiempo criptográfico (TSA) y validación de integridad hash SHA-256.',
            'stack' => ['PHP 8.2', 'OpenSSL', 'TCPDF', 'MySQL', 'Vanilla JS'],
            'cover' => 'https://images.unsplash.com/photo-1450133064473-71024230f91b?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g',
            'status' => 'approved',
            'score' => 9.5,
        ],
        [
            'code' => 'DEV-2026-020',
            'name' => 'Dashboard de Monitoreo de Infraestructura y Servidores Cloud',
            'desc' => 'Consolidación de métricas de CPU, memoria, I/O de disco y estado de servicios en VPS remotos con agentes ligeros y alertas vía webhook.',
            'stack' => ['PHP 8.2', 'cURL', 'ApexCharts', 'MySQL', 'SSE (Server-Sent Events)'],
            'cover' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-021',
            'name' => 'API Gateway de Reservas y Gestión Hotelera',
            'desc' => 'Motor de disponibilidad de habitaciones en tiempo real con bloqueo concurrente (optimistic locking), integración con OTAs y check-in web.',
            'stack' => ['PHP 8.2', 'MySQL InnoDB', 'FullCalendar', 'Bootstrap 5', 'cURL'],
            'cover' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=aQXXSzx0Xh0',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-022',
            'name' => 'Sistema de Gestión de Activos Fijos con Códigos QR y RFID',
            'desc' => 'Control de inventario patrimonial, depreciación contable según NIIF, generación de etiquetas QR y asignación por custodio.',
            'stack' => ['PHP 8.2', 'QR Generator', 'MySQL', 'Chart.js', 'HTML5 Scanner'],
            'cover' => 'https://images.unsplash.com/photo-1507925921958-8a62f3d1a50d?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=drgHafpCYPA',
            'status' => 'pending_review',
        ],
        [
            'code' => 'DEV-2026-023',
            'name' => 'E-commerce B2B con Gestión de Catálogos y Cotizaciones',
            'desc' => 'Portal de ventas mayoristas con listas de precios diferenciadas por volumen, workflow de aprobación de cotizaciones y facturación.',
            'stack' => ['PHP 8.2', 'MySQL', 'Bootstrap 5', 'Vanilla JS', 'PDF Generator'],
            'cover' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=JGwWNGJdvx8',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-024',
            'name' => 'Portal de Autoservicio de Recursos Humanos y Turnos Rotativos',
            'desc' => 'Gestión de boletas de pago firmadas digitalmente, solicitudes de permisos y licencias, y programación semanal de turnos laborales.',
            'stack' => ['PHP 8.2', 'MySQL', 'FullCalendar', 'Bootstrap 5', 'Email API'],
            'cover' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ',
            'status' => 'approved',
            'score' => 9.1,
        ],
        [
            'code' => 'DEV-2026-025',
            'name' => 'Plataforma de Votación Electrónica Segura y Escrutinio Digital',
            'desc' => 'Sistema de elecciones institucionales con voto secreto garantizado por cifrado asimétrico, mesa digital y actas de escrutinio con hash verificable.',
            'stack' => ['PHP 8.2', 'OpenSSL', 'SHA-256', 'MySQL InnoDB', 'Chart.js'],
            'cover' => 'https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=OPf0YbXqDm0',
            'status' => 'in_progress',
        ],
        [
            'code' => 'DEV-2026-026',
            'name' => 'Sistema de Gestión Documental con Control de Versiones Git',
            'desc' => 'Gestor de expedientes digitales con versionado de archivos, workflow de firmas de visto bueno y búsqueda por metadatos OCR.',
            'stack' => ['PHP 8.2', 'Git CLI', 'MySQL', 'Bootstrap 5', 'Dropzone JS'],
            'cover' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=1200&auto=format&fit=crop&q=80',
            'video' => 'https://www.youtube.com/watch?v=fRh_vgS2dFE',
            'status' => 'in_progress',
        ],
    ];

    echo "[2/4] Asignando 21 nuevos proyectos a desarrolladores disponibles...\n";

    $assignedProjects = [];
    $insertProjStmt = $pdo->prepare("
        INSERT INTO `projects` (
            `code`, `name`, `slug`, `description`, `documentation`,
            `status`, `sede`, `tech_stack`, `repository_url`, `live_url`, `video_url`,
            `cover_image`, `gallery_images`, `developer_id`, `assigned_by`, `assigned_at`,
            `accepted_at`, `submitted_at`, `approved_at`, `final_score`, `is_public_in_gallery`,
            `views_count`, `created_at`, `updated_at`
        ) VALUES (
            :code, :name, :slug, :description, :documentation,
            :status, :sede, :tech_stack, :repository_url, :live_url, :video_url,
            :cover_image, :gallery_images, :developer_id, :assigned_by, :assigned_at,
            :accepted_at, :submitted_at, :approved_at, :final_score, :is_public_in_gallery,
            :views_count, :created_at, :updated_at
        )
    ");

    $insertMilestoneStmt = $pdo->prepare("
        INSERT INTO `milestones` (
            `project_id`, `name`, `description`, `due_date`, `completed_at`, `status`, `order_position`, `weight`, `created_at`
        ) VALUES (
            :project_id, :name, :description, :due_date, :completed_at, :status, :order_position, :weight, :created_at
        )
    ");

    $insertDeliveryStmt = $pdo->prepare("
        INSERT INTO `deliveries` (
            `project_id`, `milestone_id`, `type`, `title`, `description`, `documentation`,
            `video_url`, `status`, `score`, `feedback`, `reviewed_by`, `reviewed_at`, `submitted_at`, `created_at`
        ) VALUES (
            :project_id, :milestone_id, :type, :title, :description, :documentation,
            :video_url, :status, :score, :feedback, :reviewed_by, :reviewed_at, :submitted_at, :created_at
        )
    ");

    $insertEvalStmt = $pdo->prepare("
        INSERT INTO `evaluations` (
            `project_id`, `delivery_id`, `evaluator_id`, `score`, `criteria`, `feedback`, `created_at`
        ) VALUES (
            :project_id, :delivery_id, :evaluator_id, :score, :criteria, :feedback, :created_at
        )
    ");

    $slugify = function(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text);
    };

    foreach ($newProjectsData as $index => $pData) {
        $dev = $unassignedDevs[$index];
        $slug = $slugify($pData['name'] . '-' . $pData['code']);
        $assignedAt = '2026-10-02 08:30:00';
        $acceptedAt = '2026-10-02 09:15:00';
        $isPublic = ($pData['status'] === 'approved' || $pData['status'] === 'in_progress') ? 1 : 0;
        $finalScore = $pData['score'] ?? null;
        $approvedAt = $finalScore ? '2026-10-05 18:00:00' : null;
        $submittedAt = ($pData['status'] === 'in_review' || $pData['status'] === 'pending_review' || $finalScore) ? '2026-10-05 15:30:00' : null;

        $insertProjStmt->execute([
            ':code'                 => $pData['code'],
            ':name'                 => $pData['name'],
            ':slug'                 => $slug,
            ':description'          => $pData['desc'],
            ':documentation'        => "# " . $pData['name'] . "\n\n## Alcance Técnico\n" . $pData['desc'] . "\n\n## Arquitectura\nImplementación con estándares de ingeniería de software, principios SOLID y arquitectura limpia.",
            ':status'               => $pData['status'],
            ':sede'                 => $dev['sede'],
            ':tech_stack'           => json_encode($pData['stack'], JSON_UNESCAPED_UNICODE),
            ':repository_url'       => 'https://github.com/devioz-corp/' . strtolower($pData['code']) . '-' . $slugify(substr($pData['name'], 0, 20)),
            ':live_url'             => ($finalScore || $pData['status'] === 'in_progress') ? 'https://' . strtolower($pData['code']) . '.devioz.io' : null,
            ':video_url'            => $pData['video'],
            ':cover_image'          => $pData['cover'],
            ':gallery_images'       => json_encode([$pData['cover']], JSON_UNESCAPED_UNICODE),
            ':developer_id'         => $dev['id'],
            ':assigned_by'          => 1, // Elena Rostova
            ':assigned_at'          => $assignedAt,
            ':accepted_at'          => $acceptedAt,
            ':submitted_at'         => $submittedAt,
            ':approved_at'          => $approvedAt,
            ':final_score'          => $finalScore,
            ':is_public_in_gallery' => $isPublic,
            ':views_count'          => rand(18, 125),
            ':created_at'           => $assignedAt,
            ':updated_at'           => '2026-10-05 18:30:00',
        ]);

        $projectId = (int)$pdo->lastInsertId();
        $assignedProjects[] = [
            'id'           => $projectId,
            'code'         => $pData['code'],
            'name'         => $pData['name'],
            'developer_id' => $dev['id'],
            'dev_name'     => $dev['name'],
            'sede'         => $dev['sede'],
            'status'       => $pData['status'],
            'score'        => $finalScore,
            'video'        => $pData['video'],
        ];

        // ----------------------------------------------------------------------
        // Crear 4 Hitos Secuenciales para cada proyecto
        // ----------------------------------------------------------------------
        $milestonesDef = [
            [
                'name' => 'Hito 1: Requerimientos y Diseño de Arquitectura',
                'desc' => 'Levantamiento de especificaciones, modelado de base de datos MySQL InnoDB, diagramas C4 y configuración de entorno.',
                'due' => '2026-10-06',
                'completed' => ($pData['status'] === 'approved' || $pData['status'] === 'in_review') ? '2026-10-05 14:00:00' : null,
                'status' => ($pData['status'] === 'approved' || $pData['status'] === 'in_review') ? 'completed' : 'in_progress',
                'pos' => 1,
                'weight' => 20,
            ],
            [
                'name' => 'Hito 2: Desarrollo Core Backend & APIs RESTful',
                'desc' => 'Implementación de modelos, controladores, servicios y endpoints REST con validación estricta y tokens de seguridad.',
                'due' => '2026-10-18',
                'completed' => null,
                'status' => ($pData['status'] === 'approved') ? 'in_progress' : 'pending',
                'pos' => 2,
                'weight' => 30,
            ],
            [
                'name' => 'Hito 3: Frontend UI/UX e Integración de Servicios',
                'desc' => 'Diseño responsivo con Bootstrap 5 / Vanilla JS, conexión asíncrona a endpoints y validación en tiempo real.',
                'due' => '2026-10-31',
                'completed' => null,
                'status' => 'pending',
                'pos' => 3,
                'weight' => 25,
            ],
            [
                'name' => 'Hito 4: Pruebas QA, Despliegue en Cloud & Sustentación',
                'desc' => 'Pruebas de integración, configuración de servidor Nginx/VPS, grabación de video demo y sustentación final.',
                'due' => '2026-11-15',
                'completed' => null,
                'status' => 'pending',
                'pos' => 4,
                'weight' => 25,
            ],
        ];

        $firstMilestoneId = null;
        foreach ($milestonesDef as $m) {
            $insertMilestoneStmt->execute([
                ':project_id'      => $projectId,
                ':name'            => $m['name'],
                ':description'     => $m['desc'],
                ':due_date'        => $m['due'],
                ':completed_at'    => $m['completed'],
                ':status'          => $m['status'],
                ':order_position'  => $m['pos'],
                ':weight'          => $m['weight'],
                ':created_at'      => $assignedAt,
            ]);
            if ($m['pos'] === 1) {
                $firstMilestoneId = (int)$pdo->lastInsertId();
            }
        }

        // Si el proyecto avanzó a entrega o evaluación (Día 3 / Día 4):
        if ($m['completed'] !== null && $firstMilestoneId) {
            $insertDeliveryStmt->execute([
                ':project_id'   => $projectId,
                ':milestone_id' => $firstMilestoneId,
                ':type'         => 'partial',
                ':title'        => 'Entrega Técnica Hito 1: Arquitectura y Modelado',
                ':description'  => 'Se entrega el repositorio con el esquema relacional DDL, scripts de migración y endpoints base testeados.',
                ':documentation'=> 'Documentación completa en README.md del repositorio Git corporativo.',
                ':video_url'    => $pData['video'],
                ':status'       => $finalScore ? 'approved' : 'pending',
                ':score'        => $finalScore,
                ':feedback'     => $finalScore ? 'Excelente estructuración de modelos y buenas prácticas de tipado estricto. Aprobado con felicitaciones.' : null,
                ':reviewed_by'  => $finalScore ? 1 : null,
                ':reviewed_at'  => $finalScore ? '2026-10-05 17:30:00' : null,
                ':submitted_at' => '2026-10-05 13:45:00',
                ':created_at'   => '2026-10-05 13:45:00',
            ]);
            $deliveryId = (int)$pdo->lastInsertId();

            if ($finalScore) {
                $criteria = [
                    'architecture'  => round($finalScore * 0.98, 1),
                    'code_quality'  => round($finalScore * 0.96, 1),
                    'documentation' => round($finalScore * 1.02, 1),
                    'git_workflow'  => round($finalScore * 0.99, 1),
                ];
                $insertEvalStmt->execute([
                    ':project_id'   => $projectId,
                    ':delivery_id'  => $deliveryId,
                    ':evaluator_id' => 1, // Elena Rostova
                    ':score'        => $finalScore,
                    ':criteria'     => json_encode($criteria),
                    ':feedback'     => 'Dictamen de Administración: Solución técnica validada satisfactoriamente bajo rúbrica corporativa. Cumple con los estándares de ingeniería senior de Devioz.',
                    ':created_at'   => '2026-10-05 17:30:00',
                ]);
            }
        }
    }

    echo "      [OK] 21 nuevos proyectos asignados con éxito con sus 84 hitos correspondientes.\n\n";

    // --------------------------------------------------------------------------
    // 3. Simulación de 4 Días de Trabajo para Todos los Desarrolladores Activos
    // --------------------------------------------------------------------------
    echo "[3/4] Simulando 4 días de trabajo activo (Daily Standups en 3 ventanas)...\n";

    // Obtener todos los desarrolladores con proyecto activo (los 5 anteriores + los 21 nuevos = 26)
    $activeDevsStmt = $pdo->query("
        SELECT p.id AS project_id, p.code AS project_code, p.name AS project_name, u.id AS user_id, u.name AS user_name, u.sede
        FROM projects p
        INNER JOIN users u ON p.developer_id = u.id
        WHERE p.status IN ('assigned', 'in_progress', 'pending_review', 'in_review', 'approved')
        ORDER BY p.id ASC
    ");
    $activeProjects = $activeDevsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo "      Total proyectos activos para simulación diaria: " . count($activeProjects) . "\n";

    // Días de trabajo a simular:
    $workDays = [
        [
            'day_num' => 1,
            'date'    => '2026-10-02',
            'label'   => 'Día 1 (Jueves 02/10): Arranque de Sprint & Arquitectura',
            'morning' => [
                'hours' => 2.5,
                'content' => 'Revisión del requerimiento funcional con administración. Clonado del repositorio Git institucional, configuración de rama feature/init y entorno Docker con PHP 8.2 y MySQL.',
                'blockers' => null,
                'patterns' => ['Environment Configuration', 'Git Branching Strategy'],
                'next' => 'Modelado de la base de datos relacional y creación de migraciones DDL.',
            ],
            'midday' => [
                'hours' => 3.0,
                'content' => 'Diseño del esquema de base de datos relacional con claves foráneas e índices compuestos. Creación de tablas InnoDB y pruebas de integridad referencial.',
                'blockers' => null,
                'patterns' => ['Relational Data Modeling', 'Database Constraints'],
                'next' => 'Creación de modelos de dominio y controladores RESTful.',
            ],
            'afternoon' => [
                'hours' => 2.5,
                'content' => 'Implementación de los primeros modelos de entidad con tipado estricto declare(strict_types=1). Primer commit y push a origin/feature/init. Cierre de jornada.',
                'blockers' => 'Ninguno. Avance completado conforme al cronograma del primer día.',
                'patterns' => ['Strict Type Domain Modeling', 'Git Commit Standards'],
                'next' => 'Desarrollar la capa de servicios y controladores en el segundo día.',
            ],
        ],
        [
            'day_num' => 2,
            'date'    => '2026-10-03',
            'label'   => 'Día 2 (Viernes 03/10): Desarrollo Core Backend & Validaciones',
            'morning' => [
                'hours' => 2.5,
                'content' => 'Desarrollo de controladores HTTP y sistema de validación de datos en servidor. Definición de rutas REST y retorno de respuestas estructuradas JSON.',
                'blockers' => null,
                'patterns' => ['Controller Layer', 'Data Validation Pipeline'],
                'next' => 'Implementar autenticación y manejo de sesiones seguras.',
            ],
            'midday' => [
                'hours' => 3.0,
                'content' => 'Implementación del patrón Repository para desacoplar el acceso a datos. Manejo robusto de excepciones y logging de errores en bitácora.',
                'blockers' => 'Duda técnica sobre el formato de los tokens resuelta consultando la documentación interna.',
                'patterns' => ['Repository Pattern', 'Exception Handling Strategy'],
                'next' => 'Pruebas unitarias de endpoints con cURL y PHPUnit.',
            ],
            'afternoon' => [
                'hours' => 2.5,
                'content' => 'Ejecución de pruebas locales de endpoints de backend. Cobertura de tests validada y documentación técnica preliminar añadida en docs/api.md.',
                'blockers' => null,
                'patterns' => ['Unit Testing', 'API Documentation'],
                'next' => 'Comenzar la integración de interfaz de usuario en el día 3.',
            ],
        ],
        [
            'day_num' => 3,
            'date'    => '2026-10-04',
            'label'   => 'Día 3 (Sábado 04/10): Frontend UI/UX & Integración Asíncrona',
            'morning' => [
                'hours' => 3.0,
                'content' => 'Construcción de vistas responsivas con Bootstrap 5.3 y componentes CSS limpios. Maquetación del panel principal y formularios con accesibilidad.',
                'blockers' => null,
                'patterns' => ['Mobile-First Responsive Layout', 'Semantic HTML5'],
                'next' => 'Consumo asíncrono de APIs mediante Fetch API nativo.',
            ],
            'midday' => [
                'hours' => 2.5,
                'content' => 'Integración JavaScript moderna sin librerías pesadas: consumo de endpoints mediante async/await, renderizado dinámico del DOM y estados de carga.',
                'blockers' => null,
                'patterns' => ['Asynchronous Fetch API', 'Optimistic UI Updates'],
                'next' => 'Validaciones en cliente y preparación de la primera entrega.',
            ],
            'afternoon' => [
                'hours' => 2.5,
                'content' => 'Pruebas de compatibilidad cross-browser y modo Dark/Light. Pull Request preparado y subido para revisión por la administración técnica.',
                'blockers' => 'Ninguno. Entrega técnica del Hito 1 enviada satisfactoriamente.',
                'patterns' => ['Pull Request Review', 'Code Quality Check'],
                'next' => 'Refactorización y sustentación en el día 4.',
            ],
        ],
        [
            'day_num' => 4,
            'date'    => '2026-10-05',
            'label'   => 'Día 4 (Lunes 05/10): Optimización, Demos y Sustentación',
            'morning' => [
                'hours' => 2.5,
                'content' => 'Refactorización de consultas SQL según feedback de administración. Creación de índices compuestos para reducir tiempos de respuesta a menos de 50ms.',
                'blockers' => null,
                'patterns' => ['SQL Query Optimization', 'Index Tuning'],
                'next' => 'Preparación de la demostración técnica en video.',
            ],
            'midday' => [
                'hours' => 3.0,
                'content' => 'Grabación de la demostración en video explicando la arquitectura del software, las decisiones de diseño y ejecución en vivo de los casos de uso.',
                'blockers' => null,
                'patterns' => ['Technical Video Demonstration', 'Architecture Walkthrough'],
                'next' => 'Subida de la URL de video y sustentación final ante el evaluador.',
            ],
            'afternoon' => [
                'hours' => 2.5,
                'content' => 'Registro de la URL de sustentación en el sistema. Recepción de evaluación formal de administración con rúbricas técnicas. Sprint 1 finalizado con éxito.',
                'blockers' => 'Ninguno. Sprint completado con total aprobación técnica.',
                'patterns' => ['Technical Defense', 'Sprint Retrospective'],
                'next' => 'Planificación del Sprint 2 y siguientes funcionalidades.',
            ],
        ],
    ];

    $checkInStmt = $pdo->prepare("
        INSERT INTO `check_ins` (
            `project_id`, `user_id`, `window`, `content`, `hours_worked`, `blockers`,
            `sprint`, `story_points`, `patterns_applied`, `metrics`, `next_steps`, `check_in_date`, `created_at`
        ) VALUES (
            :project_id, :user_id, :window, :content, :hours_worked, :blockers,
            :sprint, :story_points, :patterns_applied, :metrics, :next_steps, :check_in_date, :created_at
        )
        ON DUPLICATE KEY UPDATE
            `content` = VALUES(`content`),
            `hours_worked` = VALUES(`hours_worked`),
            `blockers` = VALUES(`blockers`),
            `patterns_applied` = VALUES(`patterns_applied`),
            `next_steps` = VALUES(`next_steps`)
    ");

    $auditStmt = $pdo->prepare("
        INSERT INTO `audit_logs` (
            `user_id`, `action`, `model_type`, `model_id`, `changes`, `ip_address`, `user_agent`, `created_at`
        ) VALUES (
            :user_id, :action, :model_type, :model_id, :changes, '127.0.0.1', 'Devioz Operational CLI', :created_at
        )
    ");

    $totalCheckInsCreated = 0;

    foreach ($workDays as $wDay) {
        $date = $wDay['date'];
        echo "      -> Simulando {$wDay['label']}...\n";

        foreach ($activeProjects as $ap) {
            $projId = $ap['project_id'];
            $devId = $ap['user_id'];
            $devName = $ap['user_name'];
            $pName = $ap['project_name'];

            // 1. Check-in Mañana (08:30 - 09:30)
            $mContent = "Proyecto {$ap['project_code']}: " . $wDay['morning']['content'];
            $checkInStmt->execute([
                ':project_id'       => $projId,
                ':user_id'          => $devId,
                ':window'           => 'morning',
                ':content'          => $mContent,
                ':hours_worked'     => $wDay['morning']['hours'],
                ':blockers'         => $wDay['morning']['blockers'],
                ':sprint'           => ($wDay['day_num'] <= 2) ? 'Sprint 1' : 'Sprint 2',
                ':story_points'     => rand(3, 5),
                ':patterns_applied' => json_encode($wDay['morning']['patterns'], JSON_UNESCAPED_UNICODE),
                ':metrics'          => json_encode(['commits' => rand(1, 3), 'tasks_done' => rand(2, 4)]),
                ':next_steps'       => $wDay['morning']['next'],
                ':check_in_date'    => $date,
                ':created_at'       => "{$date} 09:15:00",
            ]);
            $totalCheckInsCreated++;

            // 2. Check-in Mediodía (12:30 - 13:30)
            $midContent = "Avance Técnico: " . $wDay['midday']['content'];
            $checkInStmt->execute([
                ':project_id'       => $projId,
                ':user_id'          => $devId,
                ':window'           => 'midday',
                ':content'          => $midContent,
                ':hours_worked'     => $wDay['midday']['hours'],
                ':blockers'         => $wDay['midday']['blockers'],
                ':sprint'           => ($wDay['day_num'] <= 2) ? 'Sprint 1' : 'Sprint 2',
                ':story_points'     => rand(5, 8),
                ':patterns_applied' => json_encode($wDay['midday']['patterns'], JSON_UNESCAPED_UNICODE),
                ':metrics'          => json_encode(['endpoints_tested' => rand(2, 6), 'sql_queries' => rand(4, 12)]),
                ':next_steps'       => $wDay['midday']['next'],
                ':check_in_date'    => $date,
                ':created_at'       => "{$date} 13:00:00",
            ]);
            $totalCheckInsCreated++;

            // 3. Check-in Tarde (17:30 - 18:30)
            $aftContent = "Cierre de Jornada: " . $wDay['afternoon']['content'];
            $checkInStmt->execute([
                ':project_id'       => $projId,
                ':user_id'          => $devId,
                ':window'           => 'afternoon',
                ':content'          => $aftContent,
                ':hours_worked'     => $wDay['afternoon']['hours'],
                ':blockers'         => $wDay['afternoon']['blockers'],
                ':sprint'           => ($wDay['day_num'] <= 2) ? 'Sprint 1' : 'Sprint 2',
                ':story_points'     => rand(5, 8),
                ':patterns_applied' => json_encode($wDay['afternoon']['patterns'], JSON_UNESCAPED_UNICODE),
                ':metrics'          => json_encode(['total_day_hours' => 8.0, 'code_reviews' => rand(1, 2)]),
                ':next_steps'       => $wDay['afternoon']['next'],
                ':check_in_date'    => $date,
                ':created_at'       => "{$date} 18:15:00",
            ]);
            $totalCheckInsCreated++;
        }

        // Registrar auditoría de jornada diaria
        $auditStmt->execute([
            ':user_id'    => 1, // Elena Rostova
            ':action'     => 'daily_standups.audited',
            ':model_type' => 'CheckIn',
            ':model_id'   => null,
            ':changes'    => json_encode(['date' => $date, 'total_standups' => count($activeProjects) * 3]),
            ':created_at' => "{$date} 19:00:00",
        ]);
    }

    echo "      [OK] {$totalCheckInsCreated} Standups diarios generados a lo largo de los 4 días.\n\n";

    // --------------------------------------------------------------------------
    // 4. Publicaciones de Discusión Técnica en el Dev Lounge
    // --------------------------------------------------------------------------
    echo "[4/4] Sembrando discusiones técnicas del equipo en Dev Lounge...\n";

    $loungePostStmt = $pdo->prepare("
        INSERT INTO `lounge_posts` (`user_id`, `title`, `content`, `category`, `likes_count`, `created_at`, `updated_at`)
        VALUES (:user_id, :title, :content, :category, :likes, :created_at, :updated_at)
    ");

    $loungeCommentStmt = $pdo->prepare("
        INSERT INTO `lounge_comments` (`post_id`, `user_id`, `content`, `created_at`, `updated_at`)
        VALUES (:post_id, :user_id, :content, :created_at, :updated_at)
    ");

    // Post 1: Elena Rostova (Admin) sobre estándares de rúbrica
    $loungePostStmt->execute([
        ':user_id'    => 1, // Elena
        ':title'      => 'Guía de Calidad: Rúbrica Técnica de Evaluación de Entregables (Sprint 1)',
        ':content'    => "Estimado equipo de desarrollo,\n\nRecordamos los 4 criterios de calificación en la rúbrica oficial:\n1. Arquitectura & Patrones (Clean Code y tipado estricto PHP 8.2).\n2. Calidad de Código & Seguridad (Prepared statements, sanitización y hashing).\n3. Trazabilidad Git (Commits descriptivos y branches limpios).\n4. Documentación & Sustentación en Video (Explicación fluida y precisa de la demo).\n\nCualquier consulta sobre las asignaciones activas, déjenla en este hilo.",
        ':category'   => 'tech',
        ':likes'      => 14,
        ':created_at' => '2026-10-02 10:00:00',
        ':updated_at' => '2026-10-02 10:00:00',
    ]);
    $p1Id = (int)$pdo->lastInsertId();

    $loungeCommentStmt->execute([
        ':post_id'    => $p1Id,
        ':user_id'    => 9, // Jhon Julca
        ':content'    => 'Entendido Administradora Elena. En DEV-2026-001 estamos aplicando el Repository Pattern para asegurar el desacoplamiento con la base de datos.',
        ':created_at' => '2026-10-02 10:45:00',
        ':updated_at' => '2026-10-02 10:45:00',
    ]);

    $loungeCommentStmt->execute([
        ':post_id'    => $p1Id,
        ':user_id'    => 3, // Marcial Salvatierra
        ':content'    => 'Excelente. En el API de Facturación Electrónica DEV-2026-002 ya tenemos los certificados OpenSSL configurados para la firma digital de XML.',
        ':created_at' => '2026-10-02 11:15:00',
        ':updated_at' => '2026-10-02 11:15:00',
    ]);

    // Post 2: Jhon Julca sobre optimización de base de datos
    $loungePostStmt->execute([
        ':user_id'    => 9, // Jhon
        ':title'      => 'Tip de Rendimiento: Índices Compuestos vs Simples en MySQL InnoDB',
        ':content'    => "Comparto un hallazgo del Sprint 1: Cuando consultamos check-ins filtrando por (user_id, window, check_in_date), el índice único compuesto reduce el escaneo de filas a 1 sola fila en tiempo constante O(1). Recuerden usar EXPLAIN en sus consultas críticas para evitar table scans.",
        ':category'   => 'tips',
        ':likes'      => 19,
        ':created_at' => '2026-10-04 11:30:00',
        ':updated_at' => '2026-10-04 11:30:00',
    ]);
    $p2Id = (int)$pdo->lastInsertId();

    $loungeCommentStmt->execute([
        ':post_id'    => $p2Id,
        ':user_id'    => 1, // Elena
        ':content'    => 'Gran aporte técnico Jhon. Esta mentalidad de optimización y perfilado de consultas es exactamente el estándar senior que buscamos formar en la consultora.',
        ':created_at' => '2026-10-04 12:10:00',
        ':updated_at' => '2026-10-04 12:10:00',
    ]);

    $pdo->commit();

    echo "      [OK] Posts y comentarios técnicos del Lounge registrados con éxito.\n\n";

    // --------------------------------------------------------------------------
    // 5. Métricas Consolidadas Finales
    // --------------------------------------------------------------------------
    $finalProjects = $pdo->query("SELECT COUNT(*) FROM `projects`")->fetchColumn();
    $finalActiveAssignments = $pdo->query("SELECT COUNT(*) FROM `projects` WHERE status IN ('assigned', 'in_progress', 'pending_review', 'in_review', 'approved')")->fetchColumn();
    $finalCheckIns = $pdo->query("SELECT COUNT(*) FROM `check_ins`")->fetchColumn();
    $finalMilestones = $pdo->query("SELECT COUNT(*) FROM `milestones`")->fetchColumn();
    $finalEvaluations = $pdo->query("SELECT COUNT(*) FROM `evaluations`")->fetchColumn();
    $finalDeliveries = $pdo->query("SELECT COUNT(*) FROM `deliveries`")->fetchColumn();

    echo "=======================================================\n";
    echo "  SIMULACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "=======================================================\n";
    echo "  - Total Proyectos en Portafolio: {$finalProjects}\n";
    echo "  - Asignaciones de Proyecto Activas: {$finalActiveAssignments} (Supera la meta de >20)\n";
    echo "  - Días de Trabajo Simulados: 4 días completos (02/10 al 05/10/2026)\n";
    echo "  - Total Daily Standups / Check-ins: {$finalCheckIns} reportes técnicos\n";
    echo "  - Hitos de Proyecto Gestionados: {$finalMilestones} hitos\n";
    echo "  - Entregables Registrados: {$finalDeliveries} entregas\n";
    echo "  - Evaluaciones Técnicas / Rúbricas: {$finalEvaluations} dictámenes\n";
    echo "=======================================================\n\n";

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[ERROR CRÍTICO] La simulación falló y se aplicó rollback:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " en línea " . $e->getLine() . "\n\n";
    exit(1);
}
