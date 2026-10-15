-- ==============================================================================
-- DEVIOZ CLASSIC — Datos Iniciales y Semilla de Pruebas (Seed Data)
-- Users: 2 Admins + 34 Developers reales distribuidos por sedes
-- Password de todos los usuarios: Devioz2026!
-- Hash BCRYPT: $2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa
-- ==============================================================================

USE `devioz_classic`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `messages`;
TRUNCATE TABLE `comments`;
TRUNCATE TABLE `check_ins`;
TRUNCATE TABLE `project_files`;
TRUNCATE TABLE `evaluations`;
TRUNCATE TABLE `deliveries`;
TRUNCATE TABLE `milestones`;
TRUNCATE TABLE `projects`;
TRUNCATE TABLE `users`;

-- ------------------------------------------------------------------------------
-- 1. Inserción de Usuarios: 2 Admins (Docentes) + 34 Practicantes (Developers)
-- ------------------------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `active`, `sede`, `headline`, `bio`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Elena Rostova', 'elena.rostova@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'admin', 1, NULL, 'Docente Principal y Coordinadora de Prácticas', 'Supervisión técnica de proyectos, revisión de entregables y aseguramiento de calidad de software.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(2, 'Admin Backup', 'admin@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'admin', 1, NULL, 'Administrador del Sistema Académico', 'Soporte de infraestructura, gestión de accesos y auditoría de la plataforma.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(3, 'Marcial Salvatierra de la Cruz', 'marcial.salvatierra@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Villa', 'Backend Developer Jr | PHP & MySQL', 'Estudiante de desarrollo de software enfocado en arquitecturas orientadas a servicios y APIs REST.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(4, 'Marjori Zeta Alvarado', 'marjori.zeta@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Villa', 'Frontend Developer Jr | UI/UX', 'Pasión por el diseño de interfaces limpias, accesibilidad web y Bootstrap.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(5, 'Mariana Laurente', 'mariana.laurente@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Developer Jr | Base de Datos', 'Especializada en optimización de consultas SQL y modelado relacional.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(6, 'Alejandra Trujillo', 'alejandra.trujillo@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Data Analyst & Web Developer', 'Desarrollo de cuadros de mando, integración con Chart.js y reportes ejecutivos.', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(7, 'Analhy Maquera Choque', 'analhy.maquera@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'QA Tester & Junior Developer', 'Interés en pruebas unitarias, aseguramiento de calidad y metodologías ágiles.', 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(8, 'David Niels', 'david.niels@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Software Crafter', 'Comprometido con código limpio, principios SOLID y patrones de diseño.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(9, 'Jhon Julca', 'jhon.julca@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Backend Developer | MVC Specialist', 'Enfocado en desarrollo de sistemas web corporativos en PHP nativo.', 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(10, 'Analy Aguirre', 'analy.aguirre@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Full Stack Developer Jr', 'Desarrollo integral de aplicaciones web responsivas.', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(11, 'Alexander Bustamante', 'alexander.bustamante@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Developer Jr | Security Enthusiast', 'Aplicando buenas prácticas de seguridad informática en aplicaciones web.', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(12, 'Yennder Marcelo Custodio', 'yennder.marcelo@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Software Engineer in Progress', 'Entusiasta del desarrollo backend y optimización de servidores.', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(13, 'Daniel Gomez Moreno', 'daniel.gomez@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Frontend & Layout Developer', 'Maquetación semántica y diseño visual modular.', 'https://images.unsplash.com/photo-1501196354995-cbb51c65aaea?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(14, 'Brayan Borja Pedraza', 'brayan.borja@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Web Developer', 'Soluciones de gestión empresarial e integración de pagos.', 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(15, 'Estiven Enrique Ccente', 'estiven.enrique@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Junior Developer', 'Desarrollo de módulos administrativos y lógica transaccional.', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(16, 'André Miguel Núñez', 'andre.miguel@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Full Stack Developer | Cloud & Web', 'Desarrollo de portales web interactivos y soluciones para clientes.', 'https://images.unsplash.com/photo-1513956589380-bad6acb9b9d4?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(17, 'Angel Cama', 'angel.cama@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Software Developer Jr', 'Creación de aplicaciones web modulares y escalables.', 'https://images.unsplash.com/photo-1528892952291-009c663ce843?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(18, 'Jhostin Galarza', 'jhostin.galarza@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Developer Jr | Systems Design', 'Diagramación UML y lógica de negocio orientada a objetos.', 'https://images.unsplash.com/photo-1522529599102-193c0d76b5b6?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(19, 'Melissa Flores Tomaylla', 'melissa.flores@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Junior Web Developer', 'Experiencia en formularios dinámicos y validaciones seguras.', 'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(20, 'Dylan Sanchez', 'dylan.sanchez@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Backend Apprentice', 'Implementando servicios API y consumo con Vanilla JS.', 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(21, 'Jhosep Huamani Garcia', 'jhosep.huamani@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Junior Software Developer', 'Programación estructurada y orientada a objetos en PHP.', 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(22, 'Duberli Sahuanga Maticorena', 'duberli.sahuanga@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Web Developer', 'Integraciones web y soporte técnico de software.', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(23, 'Gabriela Jazmín Rojas Moreno', 'gabriela.jazmin@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Frontend & UI Designer Jr', 'Diseño de experiencias centradas en el usuario y prototipado.', 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(24, 'Leonel Gutierrez', 'leonel.gutierrez@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Developer Jr | Microservices', 'Enfoque en integración de sistemas y persistencia relacional.', 'https://images.unsplash.com/photo-1520409364224-63400afe26e5?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(25, 'Diego Espinoza', 'diego.espinoza@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Backend Developer', 'Desarrollo backend robusto con MySQL y estándares PSR.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(26, 'Richar Lopez', 'richar.lopez@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Mobile & Web Developer', 'Creación de interfaces web fluidas y aplicaciones multiplataforma.', 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(27, 'Gerson Flores', 'gerson.flores@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Junior Software Developer', 'Programación orientada a objetos y arquitectura MVC.', 'https://images.unsplash.com/photo-1463453091185-61582044d556?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(28, 'Bryan Rubio', 'bryan.rubio@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Developer Jr', 'Desarrollo de módulos de inventario y facturación.', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(29, 'Jose Andres Rojas', 'jose.andres@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Web Application Builder', 'Lógica de negocio sólida y manejo eficiente de sesiones.', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(30, 'Diego Cisneros', 'diego.cisneros@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Junior Developer | Web & Data', 'Manejo de datasets y persistencia estructurada.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(31, 'Jose Guillermo Damian Vilca', 'jose.guillermo@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Junior Backend Engineer', 'Desarrollo de librerías utilitarias y componentes desacoplados.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(32, 'Aldo Torres', 'aldo.torres@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Full Stack Trainee', 'Desarrollo web con foco en usabilidad y performance.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(33, 'Jhoe Cornelio', 'jhoe.cornelio@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Software Developer Jr', 'Implementación de reportes y exportación en formatos planos/PDF.', 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(34, 'Danniel Leandro', 'danniel.leandro@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Lima Norte', 'Junior Web Developer', 'Buenas prácticas de programación y documentación técnica.', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(35, 'Cristhian Jheremy Flores Melgarejo', 'cristhian.jheremy@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Cercado', 'Junior Full Stack Developer', 'Desarrollo web moderno con PHP puro y JavaScript nativo.', 'https://images.unsplash.com/photo-1513956589380-bad6acb9b9d4?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW()),
(36, 'Lucio', 'lucio@devioz.io', NOW(), '$2y$10$AP3GTpq0a0gZiSOv2rYQcOmWbVDP2Vg6aHfPWb.RIxbYCfxB5rgWa', 'developer', 1, 'Villa', 'Full Stack Explorer', 'Construyendo soluciones eficientes y seguras.', 'https://images.unsplash.com/photo-1528892952291-009c663ce843?w=256&h=256&auto=format&fit=crop&crop=faces&q=80', NOW(), NOW());

-- ------------------------------------------------------------------------------
-- 2. Proyectos Iniciales de Ejemplo (Casos del flujo del sistema)
-- ------------------------------------------------------------------------------
INSERT INTO `projects` (
    `id`, `code`, `name`, `slug`, `description`, `documentation`,
    `status`, `sede`, `tech_stack`, `repository_url`, `live_url`, `video_url`,
    `cover_image`, `gallery_images`, `developer_id`, `assigned_by`, `reviewed_by`, `approved_by`,
    `assigned_at`, `accepted_at`, `submitted_at`, `approved_at`, `completed_at`,
    `final_score`, `is_public_in_gallery`, `views_count`, `created_at`, `updated_at`
) VALUES
-- Proyecto 1: Estado 'assigned' (Asignado recién a Jhon Julca, aún sin iniciar entregas)
(
    1,
    'DEV-2026-001',
    'Sistema de Gestión Web',
    'sistema-de-gestion-web-dev-2026-001',
    'Plataforma integral para la administración de inventario, proveedores y compras institucionales.',
    '# Sistema de Gestión Web\n\n## Alcance\nDesarrollo de un sistema administrativo modular para el control de inventarios.\n\n## Módulos Principales\n1. Autenticación y roles.\n2. Gestión de almacenes.\n3. Kardex valorizado.',
    'assigned',
    'Cercado',
    '["PHP 8.2", "MySQL", "Bootstrap 5", "Vanilla JS", "Chart.js"]',
    'https://github.com/devioz-corp/dev-2026-001-gestion-web',
    NULL,
    'https://www.youtube.com/watch?v=aQXXSzx0Xh0',
    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&auto=format&fit=crop&q=80',
    '["https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&auto=format&fit=crop&q=80"]',
    9, -- Jhon Julca
    1, -- Asignado por Elena Rostova
    NULL,
    NULL,
    NOW() - INTERVAL 10 DAY,
    NOW() - INTERVAL 9 DAY,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    14,
    NOW() - INTERVAL 10 DAY,
    NOW()
),

-- Proyecto 2: Estado 'in_progress' (Marcial Salvatierra trabajando en Villa)
(
    2,
    'DEV-2026-002',
    'API de Facturación Electrónica',
    'api-de-facturacion-electronica-dev-2026-002',
    'Servicio RESTful en PHP puro para generación, firma digital de XML UBL 2.1 y comunicación con la entidad tributaria.',
    '# API de Facturación Electrónica\n\nServicio de emisión y firma de comprobantes de pago electrónicos conforme a especificaciones UBL 2.1.',
    'in_progress',
    'Villa',
    '["PHP 8.2", "MySQL", "OpenSSL", "XML DOM", "cURL"]',
    'https://github.com/devioz-corp/dev-2026-002-api-facturacion',
    'https://facturacion-demo.devioz.io',
    'https://www.youtube.com/watch?v=drgHafpCYPA',
    'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=1200&auto=format&fit=crop&q=80',
    '["https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&auto=format&fit=crop&q=80"]',
    3, -- Marcial Salvatierra
    1, -- Asignado por Elena Rostova
    NULL,
    NULL,
    NOW() - INTERVAL 25 DAY,
    NOW() - INTERVAL 24 DAY,
    NULL,
    NULL,
    NULL,
    NULL,
    0,
    45,
    NOW() - INTERVAL 25 DAY,
    NOW()
),

-- Proyecto 3: Estado 'in_review' (André Miguel Núñez ha enviado entregas, docente revisando)
(
    3,
    'DEV-2026-003',
    'Portal de Clientes y Seguimiento',
    'portal-de-clientes-dev-2026-003',
    'Portal de autoservicio para consulta de tickets, estados de cuenta y descargas de reportes en tiempo real.',
    '# Portal de Clientes\n\nPermite a los usuarios consultar estado de solicitudes con actualización asíncrona mediante Vanilla JS Fetch API.',
    'in_review',
    'Cercado',
    '["PHP 8.2", "MySQL", "Bootstrap 5", "HTML5", "CSS3"]',
    'https://github.com/devioz-corp/dev-2026-003-portal-clientes',
    'https://portal-clientes.devioz.io',
    'https://www.youtube.com/watch?v=HF3xwnr9wRY',
    'https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=1200&auto=format&fit=crop&q=80',
    '["https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=800&auto=format&fit=crop&q=80"]',
    16, -- André Miguel Núñez
    1,  -- Asignado por Elena Rostova
    1,  -- Revisado por Elena Rostova
    NULL,
    NOW() - INTERVAL 35 DAY,
    NOW() - INTERVAL 34 DAY,
    NOW() - INTERVAL 2 DAY,
    NULL,
    NULL,
    NULL,
    0,
    98,
    NOW() - INTERVAL 35 DAY,
    NOW()
),

-- Proyecto 4: Estado 'approved' (Alejandra Trujillo aprobada, en galería pública)
(
    4,
    'DEV-2026-004',
    'Dashboard Analytics y Reportería',
    'dashboard-analytics-dev-2026-004',
    'Plataforma analítica con visualización de KPIs de rendimiento, gráficos interactivos con Chart.js y exportación Excel.',
    '# Dashboard Analytics\n\nSistema de monitoreo de métricas empresariales con renderizado server-side y componentes Bootstrap 5 con tema dark/light.',
    'approved',
    'Cercado',
    '["PHP 8.2", "MySQL", "Chart.js", "Bootstrap 5", "Export CSV/PDF"]',
    'https://github.com/devioz-corp/dev-2026-004-dashboard-analytics',
    'https://analytics.devioz.io',
    'https://www.youtube.com/watch?v=IdJ52yEUDIo',
    'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&auto=format&fit=crop&q=80',
    '["https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&auto=format&fit=crop&q=80"]',
    6, -- Alejandra Trujillo
    1, -- Elena Rostova
    1, -- Elena Rostova
    1, -- Aprobado por Elena Rostova
    NOW() - INTERVAL 50 DAY,
    NOW() - INTERVAL 49 DAY,
    NOW() - INTERVAL 12 DAY,
    NOW() - INTERVAL 8 DAY,
    NULL,
    9.5,
    1, -- is_public_in_gallery
    230,
    NOW() - INTERVAL 50 DAY,
    NOW()
),

-- Proyecto 5: Estado 'completed' (Richar Lopez completó el ciclo con nota máxima)
(
    5,
    'DEV-2026-005',
    'App Móvil de Ventas y Distribución',
    'app-movil-de-ventas-dev-2026-005',
    'Progressive Web App (PWA) optimizada para toma de pedidos en ruta con soporte offline mediante Service Workers y LocalStorage.',
    '# PWA de Ventas y Distribución\n\nDiseñada mobile-first con sincronización automática al recuperar conexión a Internet.',
    'completed',
    'Lima Norte',
    '["PHP 8.2", "MySQL", "PWA", "Service Workers", "Bootstrap 5"]',
    'https://github.com/devioz-corp/dev-2026-005-pwa-ventas',
    'https://ventas.devioz.io',
    'https://www.youtube.com/watch?v=Fu29qjFB7_g',
    'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=1200&auto=format&fit=crop&q=80',
    '["https://images.unsplash.com/photo-1526470608268-f674ce90ebd4?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=800&auto=format&fit=crop&q=80"]',
    26, -- Richar Lopez
    1,  -- Elena Rostova
    1,  -- Elena Rostova
    1,  -- Elena Rostova
    NOW() - INTERVAL 70 DAY,
    NOW() - INTERVAL 69 DAY,
    NOW() - INTERVAL 15 DAY,
    NOW() - INTERVAL 10 DAY,
    NOW() - INTERVAL 5 DAY,
    9.8,
    1, -- is_public_in_gallery
    480,
    NOW() - INTERVAL 70 DAY,
    NOW()
);

-- ------------------------------------------------------------------------------
-- 3. Hitos por Proyecto (Milestones)
-- ------------------------------------------------------------------------------
INSERT INTO `milestones` (`id`, `project_id`, `name`, `description`, `due_date`, `completed_at`, `status`, `order_position`, `weight`) VALUES
-- Hitos para Proyecto 1 (DEV-2026-001)
(1, 1, 'Requerimientos y Diagrama Entidad-Relación', 'Documento de especificación de requisitos y schema MySQL normalizado en 3FN.', CURDATE() + INTERVAL 7 DAY, NULL, 'in_progress', 1, 1),
(2, 1, 'Prototipo UI/UX y Wireframes', 'Diseño de pantallas y navegación en Bootstrap 5 responsive.', CURDATE() + INTERVAL 14 DAY, NULL, 'pending', 2, 1),
(3, 1, 'Desarrollo del Core MVC y CRUDs', 'Controladores, modelos PDO y vistas de inventario.', CURDATE() + INTERVAL 28 DAY, NULL, 'pending', 3, 2),
(4, 1, 'Validaciones, Pruebas y Despliegue', 'Aseguramiento de seguridad CSRF/XSS y manual de usuario.', CURDATE() + INTERVAL 40 DAY, NULL, 'pending', 4, 2),

-- Hitos para Proyecto 2 (DEV-2026-002)
(5, 2, 'Especificación de API y Contratos JSON', 'Diseño de endpoints OpenAPI/Swagger y contratos de datos.', CURDATE() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY, 'completed', 1, 1),
(6, 2, 'Generación de XML UBL 2.1 y Firma Digital', 'Algoritmo de firma digital XMLDSig usando OpenSSL en PHP.', CURDATE() + INTERVAL 5 DAY, NULL, 'in_progress', 2, 2),
(7, 2, 'Consumo de Web Services SOAP de SUNAT', 'Envío de paquetes ZIP con cURL y recepción de CDR.', CURDATE() + INTERVAL 20 DAY, NULL, 'pending', 3, 2),

-- Hitos para Proyecto 3 (DEV-2026-003)
(8, 3, 'Arquitectura de Datos y Auth de Clientes', 'Tabla de clientes, sesiones protegidas y perfiles de acceso.', CURDATE() - INTERVAL 20 DAY, NOW() - INTERVAL 20 DAY, 'completed', 1, 1),
(9, 3, 'Módulo de Tickets y Seguimiento Asíncrono', 'Peticiones Fetch API para actualización en tiempo real sin recarga.', CURDATE() - INTERVAL 5 DAY, NOW() - INTERVAL 3 DAY, 'completed', 2, 2),
(10, 3, 'Descarga Segura de Comprobantes PDF/XML', 'Generación de enlaces temporales protegidos contra descargas no autorizadas.', CURDATE() + INTERVAL 8 DAY, NULL, 'in_progress', 3, 2),

-- Hitos para Proyecto 4 (DEV-2026-004)
(11, 4, 'Modelado y Extracción de Métricas SQL', 'Vistas SQL agregadas y optimización con índices compuestos.', CURDATE() - INTERVAL 35 DAY, NOW() - INTERVAL 35 DAY, 'completed', 1, 1),
(12, 4, 'Visualización de Gráficos con Chart.js', 'Integración de gráficos de barras, líneas y donas reactivos al tema.', CURDATE() - INTERVAL 20 DAY, NOW() - INTERVAL 20 DAY, 'completed', 2, 2),
(13, 4, 'Filtros Avanzados y Exportación CSV/PDF', 'Generación de reportes ejecutivos con filtros por rango de fechas.', CURDATE() - INTERVAL 8 DAY, NOW() - INTERVAL 8 DAY, 'completed', 3, 2),

-- Hitos para Proyecto 5 (DEV-2026-005)
(14, 5, 'Diseño PWA y Manifiesto Web', 'Configuración de manifest.json, iconos responsive y splash screens.', CURDATE() - INTERVAL 50 DAY, NOW() - INTERVAL 50 DAY, 'completed', 1, 1),
(15, 5, 'Service Worker y Caché Offline', 'Estrategia Cache-First para catálogo de productos y almacenamiento indexedDB.', CURDATE() - INTERVAL 30 DAY, NOW() - INTERVAL 30 DAY, 'completed', 2, 2),
(16, 5, 'Módulo de Sincronización en Segundo Plano', 'Envío de transacciones en cola al recuperar conexión a Internet.', CURDATE() - INTERVAL 10 DAY, NOW() - INTERVAL 10 DAY, 'completed', 3, 2),
(17, 5, 'Presentación Final y Documentación Técnica', 'Sustentación del proyecto y entrega de manuales.', CURDATE() - INTERVAL 5 DAY, NOW() - INTERVAL 5 DAY, 'completed', 4, 3);

-- ------------------------------------------------------------------------------
-- 4. Entregas (Deliveries)
-- ------------------------------------------------------------------------------
INSERT INTO `deliveries` (
    `id`, `project_id`, `milestone_id`, `type`, `title`, `description`,
    `documentation`, `video_url`, `files`, `submitted_at`, `status`, `score`, `feedback`, `reviewed_by`, `reviewed_at`
) VALUES
-- Entrega completada del Proyecto 2
(
    1, 2, 5, 'partial',
    'Entrega Hito 1: Especificación de API y Contratos JSON',
    'Documentación exhaustiva de endpoints REST para comprobantes de pago.',
    'Se diseñaron los endpoints para Factura, Boleta, Nota de Crédito y Débito con validaciones de esquemas JSON.',
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    '[{"name": "spec_api_v1.pdf", "path": "/uploads/deliveries/2/spec_api_v1.pdf", "size": "1.2 MB"}]',
    NOW() - INTERVAL 12 DAY,
    'approved',
    9.5,
    'Excelente nivel de detalle en la definición de códigos de error y respuestas HTTP.',
    1,
    NOW() - INTERVAL 10 DAY
),

-- Entrega en revisión del Proyecto 3
(
    2, 3, 9, 'partial',
    'Entrega Hito 2: Módulo de Tickets y Seguimiento Asíncrono',
    'Implementación completa del flujo de tickets con fetch API y Bootstrap modales.',
    'Se implementó la arquitectura modular con controladores REST ligeros en PHP que responden en formato JSON.',
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    '[{"name": "codigo_tickets.zip", "path": "/uploads/deliveries/3/codigo_tickets.zip", "size": "4.5 MB"}]',
    NOW() - INTERVAL 2 DAY,
    'pending',
    NULL,
    NULL,
    NULL,
    NULL
),

-- Entregas finalizadas del Proyecto 4
(
    3, 4, 13, 'final',
    'Entrega Final: Dashboard Analytics y Reportería Completa',
    'Sistema completo con gráficos interactivos, filtros dinámicos y reportes exportables.',
    'Toda la documentación técnica se encuentra consolidada en el repositorio oficial.',
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    '[{"name": "informe_final_analytics.pdf", "path": "/uploads/deliveries/4/informe_final.pdf", "size": "3.1 MB"}]',
    NOW() - INTERVAL 9 DAY,
    'approved',
    9.5,
    'Excelente trabajo. Cumple con todos los estándares solicitados y el diseño es muy intuitivo.',
    1,
    NOW() - INTERVAL 8 DAY
),

-- Entrega final del Proyecto 5 (Aprobada)
(
    4, 5, 17, 'final',
    'Entrega Definitiva: PWA Ventas y Distribución',
    'Versión de producción lista con Service Workers, offline cache y sincronización en background.',
    'Incluye manual de usuario, manual de despliegue en servidor y pruebas de carga.',
    'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    '[{"name": "proyecto_completo_pwa.zip", "path": "/uploads/deliveries/5/pwa_full.zip", "size": "8.4 MB"}]',
    NOW() - INTERVAL 7 DAY,
    'approved',
    9.8,
    'Trabajo sobresaliente. Código muy limpio, excelente uso de buenas prácticas y arquitectura sin dependencias pesadas.',
    1,
    NOW() - INTERVAL 5 DAY
);

-- ------------------------------------------------------------------------------
-- 5. Evaluaciones Formales (Evaluations)
-- ------------------------------------------------------------------------------
INSERT INTO `evaluations` (`id`, `project_id`, `delivery_id`, `evaluator_id`, `score`, `criteria`, `feedback`, `week_number`, `evaluated_at`) VALUES
(
    1, 4, 3, 1, 9.5,
    '{"arquitectura": 10, "calidad_codigo": 9.5, "ui_ux": 9.5, "documentacion": 9.0}',
    'Demostró gran destreza en la integración de Chart.js y organización modular sin frameworks pesados.',
    6,
    NOW() - INTERVAL 8 DAY
),
(
    2, 5, 4, 1, 9.8,
    '{"arquitectura": 10, "calidad_codigo": 9.8, "ui_ux": 10, "documentacion": 9.5}',
    'Proyecto con nivel profesional. Gran dominio del ciclo de vida de Service Workers y persistencia en cliente.',
    8,
    NOW() - INTERVAL 5 DAY
);

-- ------------------------------------------------------------------------------
-- 6. Check-ins Diarios (Reportes de avance del practicante)
-- ------------------------------------------------------------------------------
INSERT INTO `check_ins` (
    `id`, `project_id`, `user_id`, `window`, `content`, `hours_worked`, `blockers`,
    `sprint`, `story_points`, `patterns_applied`, `metrics`, `next_steps`, `check_in_date`
) VALUES
-- Standup de Jhon Julca (DEV-2026-001)
(
    1, 1, 9, 'morning',
    'Definiendo el diccionario de datos para los módulos de almacén y kardex.',
    3.5,
    'Ninguno por el momento.',
    'Sprint 1',
    3.0,
    '["Repository Pattern", "Singleton DB"]',
    '{"tables_modeled": 8, "fk_established": 12}',
    'Crear script de migración y validar con el docente.',
    CURDATE()
),

-- Standup de Marcial Salvatierra (DEV-2026-002)
(
    2, 2, 3, 'morning',
    'Generación de certificados autofirmados para entorno local y test de canonización XML c14n.',
    4.0,
    'Resolviendo problema con el namespace en el nodo Signature.',
    'Sprint 2',
    5.0,
    '["Strategy Pattern", "Adapter"]',
    '{"xml_tested": 15, "valid_signatures": 14}',
    'Implementar digest value y validación con cert X.509.',
    CURDATE()
),
(
    3, 2, 3, 'midday',
    'Pruebas exitosas de canonización c14n e inserción de tag Signature en el encabezado UBL.',
    3.0,
    NULL,
    'Sprint 2',
    2.0,
    '["Decorator"]',
    '{"tests_passing": 22}',
    'Iniciar construcción del cliente cURL SOAP.',
    CURDATE()
),

-- Standup de André Miguel Núñez (DEV-2026-003)
(
    4, 3, 16, 'morning',
    'Refactorización del módulo de comentarios en tickets utilizando event delegation en Vanilla JS.',
    3.5,
    'Esperando feedback del docente sobre la entrega del hito 2.',
    'Sprint 3',
    3.0,
    '["Observer Pattern", "MVC"]',
    '{"load_time_ms": 120, "dom_nodes": 45}',
    'Avanzar en el generador de tokens de descarga.',
    CURDATE()
);

-- ------------------------------------------------------------------------------
-- 7. Comentarios e Hilos de Proyecto (Comments)
-- ------------------------------------------------------------------------------
INSERT INTO `comments` (`id`, `project_id`, `user_id`, `content`, `created_at`) VALUES
(
    1, 1, 1,
    'Jhon, recuerda que el modelo de base de datos debe contemplar movimientos de inventario tanto por ingreso de compras como por mermas.',
    NOW() - INTERVAL 8 DAY
),
(
    2, 1, 9,
    'Entendido profesora Elena. Ya incluí la tabla `inventory_movements` con el tipo de movimiento enum y referencia al motivo.',
    NOW() - INTERVAL 7 DAY
),
(
    3, 3, 1,
    'André, la entrega del hito 2 está bien estructurada. En el transcurso del día reviso el repositorio para validar el manejo de errores en las llamadas asíncronas.',
    NOW() - INTERVAL 1 DAY
),
(
    4, 3, 16,
    'Perfecto, gracias profesora. El código incluye un bloque catch centralizado que muestra notificaciones Toast de Bootstrap ante cualquier falla de red.',
    NOW() - INTERVAL 20 HOUR
);

-- ------------------------------------------------------------------------------
-- 8. Mensajes Internos (Messages)
-- ------------------------------------------------------------------------------
INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `project_id`, `subject`, `body`, `type`, `priority`, `read_at`, `created_at`) VALUES
(
    1, 1, 9, 1,
    'Bienvenido a Devioz Classic — Asignación de Proyecto DEV-2026-001',
    'Estimado Jhon, se te ha asignado el proyecto "Sistema de Gestión Web". Por favor revisa los hitos iniciales en tu panel y realiza tu primer check-in hoy.',
    'general',
    'normal',
    NOW() - INTERVAL 9 DAY,
    NOW() - INTERVAL 10 DAY
),
(
    2, 1, 16, 3,
    'Revisión en proceso del Hito 2',
    'Hola André, tu entrega para el Hito 2 ya ingresó a la cola de revisión de la jefatura de prácticas.',
    'feedback',
    'normal',
    NOW() - INTERVAL 1 DAY,
    NOW() - INTERVAL 2 DAY
),
(
    3, 16, 1, 3,
    'Consulta técnica sobre expiración de enlaces de descarga',
    'Profesora Elena, ¿cuál es el tiempo de expiración recomendado para las URLs firmadas de descarga de facturas en el portal de clientes?',
    'question',
    'normal',
    NOW() - INTERVAL 10 HOUR,
    NOW() - INTERVAL 18 HOUR
);

-- ------------------------------------------------------------------------------
-- 9. Trazabilidad y Auditoría (Audit Logs)
-- ------------------------------------------------------------------------------
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `model_type`, `model_id`, `changes`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'project.assigned', 'Project', 1, '{"code": "DEV-2026-001", "developer_id": 9, "status": "assigned"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 10 DAY),
(2, 1, 'project.assigned', 'Project', 2, '{"code": "DEV-2026-002", "developer_id": 3, "status": "assigned"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 25 DAY),
(3, 1, 'delivery.reviewed', 'Delivery', 1, '{"status": "approved", "score": 9.5}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 10 DAY),
(4, 16, 'delivery.submitted', 'Delivery', 2, '{"milestone_id": 9, "status": "pending"}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 2 DAY),
(5, 1, 'project.approved', 'Project', 4, '{"status": "approved", "final_score": 9.5}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 8 DAY),
(6, 1, 'project.completed', 'Project', 5, '{"status": "completed", "final_score": 9.8}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', NOW() - INTERVAL 5 DAY);

SET FOREIGN_KEY_CHECKS = 1;
