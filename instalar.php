<?php
/**
 * ==============================================================================
 * DEVIOZ 24 — INSTALADOR AUTOMÁTICO PROFESIONAL PARA HOSTINGER
 * ==============================================================================
 * Compatible con: PHP 8.2+, MySQL 8.0+ / MariaDB 10.4+
 * Diseñado para despliegue independiente en public_html/nombre_proyecto/
 * ==============================================================================
 */

declare(strict_types=1);

session_start();

// ------------------------------------------------------------------------------
// 1. CONFIGURACIÓN DE SEGURIDAD INSTITUCIONAL
// ------------------------------------------------------------------------------
// Clave privada requerida para autorizar la instalación en Hostinger
define('INSTALL_SECRET_KEY', 'DEVIOZ_HOSTINGER_2026_INSTALL');

// Rutas clave del proyecto
define('ROOT_PATH', __DIR__);
define('LOCK_FILE', ROOT_PATH . '/installed.lock');
define('ENV_FILE', ROOT_PATH . '/.env');
define('SQL_FILE_COMBINED', ROOT_PATH . '/database/devioz_hostinger_portafolio.sql');
define('SQL_FILE_SCHEMA', ROOT_PATH . '/database/schema.sql');
define('SQL_FILE_SEED', ROOT_PATH . '/database/seed.sql');

// ------------------------------------------------------------------------------
// 2. COMPROBACIÓN DE BLOQUEO DE INSTALACIÓN (installed.lock)
// ------------------------------------------------------------------------------
$isLocked = file_exists(LOCK_FILE);

// ------------------------------------------------------------------------------
// 3. DETECCIÓN AUTOMÁTICA DE URL BASE
// ------------------------------------------------------------------------------
function detectAppUrl(): string
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

    return $protocol . $host . $dir;
}

// ------------------------------------------------------------------------------
// 4. API AJAX: ACCIONES ASÍNCRONAS DEL INSTALADOR
// ------------------------------------------------------------------------------
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($isLocked) {
        echo json_encode(['success' => false, 'message' => 'El sistema ya se encuentra instalado y bloqueado por seguridad.']);
        exit;
    }

    $action = $_GET['action'];

    // Acción 1: Validar Clave Privada
    if ($action === 'verify_key') {
        $key = trim((string)($_POST['secret_key'] ?? ''));
        if ($key === INSTALL_SECRET_KEY) {
            $_SESSION['installer_authorized'] = true;
            echo json_encode(['success' => true, 'message' => 'Clave privada autorizada correctamente.']);
        } else {
            $_SESSION['installer_authorized'] = false;
            echo json_encode(['success' => false, 'message' => 'La clave privada de instalación ingresada es incorrecta o no autorizada.']);
        }
        exit;
    }

    // Para cualquier otra acción, exigir autenticación de la clave privada
    if (empty($_SESSION['installer_authorized'])) {
        echo json_encode(['success' => false, 'message' => 'Sesión no autorizada. Ingresa la clave privada de instalación.']);
        exit;
    }

    // Acción 2: Probar Conexión a MySQL
    if ($action === 'test_db') {
        $host = trim((string)($_POST['db_host'] ?? 'localhost'));
        $port = trim((string)($_POST['db_port'] ?? '3306'));
        $database = trim((string)($_POST['db_name'] ?? ''));
        $username = trim((string)($_POST['db_user'] ?? ''));
        $password = (string)($_POST['db_pass'] ?? '');

        if (empty($database) || empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Debes indicar el nombre de la base de datos y el usuario MySQL.']);
            exit;
        }

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $version = $pdo->query("SELECT VERSION()")->fetchColumn();
            echo json_encode([
                'success' => true,
                'message' => "¡Conexión exitosa a MySQL!",
                'server_info' => "MySQL Server Version: {$version}"
            ]);
        } catch (\PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error de conexión MySQL: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Acción 3: Ejecutar Instalación Completa
    if ($action === 'run_install') {
        $host = trim((string)($_POST['db_host'] ?? 'localhost'));
        $port = trim((string)($_POST['db_port'] ?? '3306'));
        $database = trim((string)($_POST['db_name'] ?? ''));
        $username = trim((string)($_POST['db_user'] ?? ''));
        $password = (string)($_POST['db_pass'] ?? '');
        $appUrl = rtrim(trim((string)($_POST['app_url'] ?? detectAppUrl())), '/');

        $adminName = trim((string)($_POST['admin_name'] ?? 'Elena Rostova'));
        $adminEmail = strtolower(trim((string)($_POST['admin_email'] ?? 'admin@devioz.com')));
        $adminPassword = (string)($_POST['admin_pass'] ?? '');

        if (empty($database) || empty($username)) {
            echo json_encode(['success' => false, 'message' => 'Faltan credenciales de base de datos.']);
            exit;
        }

        if (empty($adminEmail) || empty($adminPassword) || strlen($adminPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña del administrador debe tener al menos 6 caracteres.']);
            exit;
        }

        try {
            // 1. Conectar a MySQL
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            // 2. Determinar archivo SQL
            $sqlContent = '';
            if (file_exists(SQL_FILE_COMBINED)) {
                $sqlContent = file_get_contents(SQL_FILE_COMBINED);
            } elseif (file_exists(SQL_FILE_SCHEMA)) {
                $sqlContent = file_get_contents(SQL_FILE_SCHEMA);
                if (file_exists(SQL_FILE_SEED)) {
                    $sqlContent .= "\n" . file_get_contents(SQL_FILE_SEED);
                }
            } else {
                throw new Exception("No se encontró el archivo de estructura SQL en database/");
            }

            // Limpieza estricta: remover comandos CREATE DATABASE o USE para evitar errores en Hostinger
            $sqlContent = preg_replace('/CREATE DATABASE IF NOT EXISTS[^;]+;/i', '', $sqlContent);
            $sqlContent = preg_replace('/USE\s+`?[a-zA-Z0-9_-]+`?\s*;/i', '', $sqlContent);

            // 3. Ejecutar las sentencias SQL en la base de datos
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

            // Normalizar saltos de línea y dividir sentencias respetando delimitadores
            $sqlStatements = preg_split('/;\s*[\r\n]+/', $sqlContent);
            $executedCount = 0;

            if ($sqlStatements) {
                foreach ($sqlStatements as $statement) {
                    $trimmed = trim($statement);
                    if ($trimmed === '') {
                        continue;
                    }
                    // Omitir bloques que sean puramente comentarios
                    $cleanLines = array_filter(explode("\n", $trimmed), function ($line) {
                        $l = trim($line);
                        return $l !== '' && !str_starts_with($l, '--') && !str_starts_with($l, '/*');
                    });
                    if (empty($cleanLines)) {
                        continue;
                    }

                    $pdo->exec($trimmed);
                    $executedCount++;
                }
            }

            // 4. Configurar / Actualizar Administrador Principal
            $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);
            
            // Verificar si el usuario ya existe por ID o email
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR id = 1 LIMIT 1");
            $checkStmt->execute(['email' => $adminEmail]);
            $existingAdminId = $checkStmt->fetchColumn();

            if ($existingAdminId) {
                $updateAdmin = $pdo->prepare("
                    UPDATE users SET 
                        name = :name, 
                        email = :email, 
                        password = :pass, 
                        role = 'admin', 
                        active = 1, 
                        updated_at = NOW() 
                    WHERE id = :id
                ");
                $updateAdmin->execute([
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'pass' => $hashedPassword,
                    'id' => $existingAdminId
                ]);
            } else {
                $insertAdmin = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, active, headline, bio, created_at, updated_at)
                    VALUES (:name, :email, :pass, 'admin', 1, 'Lead Técnico & Administrador QA', 'Coordinación técnica y administración del sistema.', NOW(), NOW())
                ");
                $insertAdmin->execute([
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'pass' => $hashedPassword,
                ]);
            }

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            // 5. Generar archivo .env en producción
            $envContent = <<<ENV
APP_NAME="Devioz 24"
APP_ENV=production
APP_DEBUG=false
APP_URL={$appUrl}
APP_TIMEZONE=America/Lima

DB_HOST={$host}
DB_PORT={$port}
DB_DATABASE={$database}
DB_USERNAME={$username}
DB_PASSWORD={$password}

UPLOAD_MAX_SIZE=524288000
UPLOAD_ALLOWED_TYPES=pdf,zip,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp,gif,svg,mp3,wav,ogg,webm,m4a,mp4,txt,json

GROQ_API_KEY=
GROQ_MODEL=llama-3.3-70b-versatile
GEMINI_API_KEY=
GEMINI_MODEL=gemini-1.5-flash
ENV;

            if (file_put_contents(ENV_FILE, $envContent) === false) {
                throw new Exception("No se pudo escribir el archivo .env. Verifica los permisos de escritura en la carpeta raíz.");
            }

            // 6. Crear archivo installed.lock para bloquear futuras instalaciones
            $lockData = sprintf(
                "DEVIOZ 24 — INSTALACIÓN FINALIZADA CON ÉXITO\nFecha: %s\nIP: %s\nURL: %s\nBase de datos: %s\n",
                date('Y-m-d H:i:s'),
                $_SERVER['REMOTE_ADDR'] ?? 'Desconocida',
                $appUrl,
                $database
            );
            file_put_contents(LOCK_FILE, $lockData);

            // Limpiar sesión de instalación
            unset($_SESSION['installer_authorized']);

            echo json_encode([
                'success' => true,
                'message' => '¡Instalación completada con éxito en Hostinger!',
                'redirect_url' => $appUrl . '/login',
                'admin_email' => $adminEmail,
            ]);

        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error durante la instalación: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    exit;
}

// ------------------------------------------------------------------------------
// 5. DIAGNÓSTICO DEL ENTORNO PHP Y PERMISOS
// ------------------------------------------------------------------------------
$phpVersionOk = version_compare(PHP_VERSION, '8.2.0', '>=');
$extPdo = extension_loaded('pdo_mysql');
$extMbstring = extension_loaded('mbstring');
$extFileinfo = extension_loaded('fileinfo');
$extCurl = extension_loaded('curl');
$extGd = extension_loaded('gd');

$isWritableRoot = is_writable(ROOT_PATH);
$isWritableStorage = is_dir(ROOT_PATH . '/backend/storage') ? is_writable(ROOT_PATH . '/backend/storage') : is_writable(ROOT_PATH . '/backend');
$isWritableUploads = is_dir(ROOT_PATH . '/frontend/public/uploads') ? is_writable(ROOT_PATH . '/frontend/public/uploads') : true;

$allRequirementsOk = $phpVersionOk && $extPdo && $extMbstring && $extFileinfo && $isWritableRoot;
$defaultAppUrl = detectAppUrl();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Oficial — Devioz 24 (Hostinger Edition)</title>
    
    <!-- Bootstrap 5.3.3 & FontAwesome 6.5.1 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --devioz-bg: #0b1329;
            --devioz-card: rgba(18, 28, 56, 0.85);
            --devioz-card-border: rgba(255, 255, 255, 0.12);
            --devioz-emerald: #10B981;
            --devioz-cyan: #00D2FF;
            --devioz-text-muted: #94A3B8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgba(0, 210, 255, 0.12) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.15) 0%, transparent 40%),
                        var(--devioz-bg);
            background-attachment: fixed;
            color: #F8FAFC;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .installer-container {
            width: 100%;
            max-width: 780px;
        }

        .liquid-card {
            background: var(--devioz-card);
            border: 1px solid var(--devioz-card-border);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.55);
            overflow: hidden;
        }

        .installer-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.01) 100%);
            border-bottom: 1px solid var(--devioz-card-border);
            padding: 1.75rem 2rem;
        }

        .brand-badge {
            background: rgba(16, 185, 129, 0.18);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #34D399;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 30px;
            letter-spacing: 0.5px;
        }

        .step-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--devioz-text-muted);
            padding: 0.4rem 0.8rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            transition: all 0.3s ease;
        }

        .step-pill.active {
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
            color: #34D399;
        }

        .step-pill.completed {
            background: rgba(0, 210, 255, 0.15);
            border-color: rgba(0, 210, 255, 0.4);
            color: #38BDF8;
        }

        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 600;
            color: #E2E8F0;
            margin-bottom: 0.35rem;
        }

        .form-control-custom, .form-select-custom {
            background: rgba(15, 23, 42, 0.65) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #FFFFFF !important;
            border-radius: 10px !important;
            padding: 0.65rem 0.9rem !important;
            font-size: 0.88rem !important;
            transition: all 0.2s ease !important;
        }

        .form-control-custom:focus, .form-select-custom:focus {
            border-color: var(--devioz-emerald) !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25) !important;
        }

        .form-control-custom::placeholder {
            color: #64748B !important;
        }

        .btn-brand-install {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 10px;
            padding: 0.70rem 1.5rem;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            transition: all 0.2s ease;
        }

        .btn-brand-install:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
            color: #FFFFFF;
        }

        .btn-ghost-install {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #E2E8F0;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.70rem 1.25rem;
            transition: all 0.2s ease;
        }

        .btn-ghost-install:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
        }

        .diagnostic-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 0.85rem 1rem;
        }

        .diagnostic-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.45rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.82rem;
        }

        .diagnostic-row:last-child {
            border-bottom: none;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body>

<div class="installer-container">

    <!-- CASO 1: SISTEMA YA INSTALADO Y BLOQUEADO -->
    <?php if ($isLocked): ?>
        <div class="liquid-card p-5 text-center">
            <div class="mb-4">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.15); color: #10B981; border: 2px solid rgba(16, 185, 129, 0.3);">
                    <i class="fa-solid fa-lock fs-1"></i>
                </span>
            </div>
            <span class="brand-badge mb-2 d-inline-block">HOSTINGER EDITION • PRODUCCIÓN</span>
            <h3 class="fw-bold text-white mb-3">Devioz 24 ya se encuentra instalado</h3>
            <p class="text-secondary small mx-auto mb-4" style="max-width: 520px; line-height: 1.6;">
                Por motivos de seguridad institucional y protección de la base de datos, el instalador web ha sido bloqueado con la bandera <code class="text-info font-mono">installed.lock</code>.
            </p>
            <div class="alert alert-dark border border-secondary border-opacity-25 p-3 rounded-3 text-start small font-mono mx-auto mb-4" style="max-width: 520px; background: rgba(15, 23, 42, 0.75);">
                <div class="text-muted mb-1">// Para reconfigurar o reinstalar desde cero:</div>
                <div class="text-warning">rm public_html/nombre_proyecto/installed.lock</div>
            </div>
            <div class="d-flex justify-content-center gap-2">
                <a href="<?= e($defaultAppUrl) ?>/login" class="btn btn-brand-install d-inline-flex align-items-center gap-2 px-4">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Ir al Sistema (Login)</span>
                </a>
            </div>
        </div>
    <?php else: ?>

    <!-- CASO 2: ASISTENTE PASO A PASO DEL INSTALADOR -->
    <div class="liquid-card">
        <!-- Encabezado con Identidad Visual -->
        <div class="installer-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="brand-badge">PORTAFOLIO DE PRÁCTICAS</span>
                    <span class="text-muted" style="font-size: 0.72rem;">&bull; HOSTINGER CLOUD</span>
                </div>
                <h4 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-server text-emerald"></i>
                    <span>Instalador Automático Devioz 24</span>
                </h4>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary bg-opacity-25 text-light font-mono px-2 py-1" style="font-size: 0.72rem;">PHP <?= PHP_VERSION ?></span>
            </div>
        </div>

        <!-- Indicador Visual de Pasos (Wizard Stepper) -->
        <div class="px-4 pt-3 pb-2 border-bottom" style="border-color: var(--devioz-card-border) !important;">
            <div class="d-flex flex-wrap gap-2">
                <div class="step-pill active" id="stepPill1">
                    <i class="fa-solid fa-key"></i>
                    <span>1. Clave Privada</span>
                </div>
                <div class="step-pill" id="stepPill2">
                    <i class="fa-solid fa-database"></i>
                    <span>2. Base de Datos</span>
                </div>
                <div class="step-pill" id="stepPill3">
                    <i class="fa-solid fa-user-shield"></i>
                    <span>3. Administrador & URL</span>
                </div>
                <div class="step-pill" id="stepPill4">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>4. Finalizar</span>
                </div>
            </div>
        </div>

        <div class="p-4 p-md-5">

            <!-- ============================================================= -->
            <!-- PASO 1: VALIDACIÓN DE CLAVE PRIVADA & REQUISITOS DEL SISTEMA  -->
            <!-- ============================================================= -->
            <div id="stepView1">
                <h5 class="fw-bold text-white mb-2">Paso 1: Autorización y Diagnóstico</h5>
                <p class="text-secondary small mb-4">
                    Ingresa la clave privada de instalación entregada por la jefatura para desbloquear el asistente.
                </p>

                <!-- Tarjeta de Diagnóstico Rápido de Hostinger -->
                <div class="diagnostic-card mb-4">
                    <div class="fw-bold small text-white-50 mb-2 text-uppercase font-mono" style="font-size: 0.70rem;">
                        Diagnóstico del Servidor Hostinger
                    </div>
                    <div class="diagnostic-row">
                        <span>Versión de PHP (>= 8.2 requerida)</span>
                        <span class="<?= $phpVersionOk ? 'text-emerald' : 'text-danger' ?> fw-bold">
                            <i class="fa-solid <?= $phpVersionOk ? 'fa-check' : 'fa-xmark' ?> me-1"></i>
                            PHP <?= PHP_VERSION ?>
                        </span>
                    </div>
                    <div class="diagnostic-row">
                        <span>Extensión PDO MySQL</span>
                        <span class="<?= $extPdo ? 'text-emerald' : 'text-danger' ?> fw-bold">
                            <i class="fa-solid <?= $extPdo ? 'fa-check' : 'fa-xmark' ?> me-1"></i>
                            <?= $extPdo ? 'Habilitada' : 'Faltante' ?>
                        </span>
                    </div>
                    <div class="diagnostic-row">
                        <span>Extensión FileInfo (Uploads & Audios WebM)</span>
                        <span class="<?= $extFileinfo ? 'text-emerald' : 'text-danger' ?> fw-bold">
                            <i class="fa-solid <?= $extFileinfo ? 'fa-check' : 'fa-xmark' ?> me-1"></i>
                            <?= $extFileinfo ? 'Habilitada' : 'Faltante' ?>
                        </span>
                    </div>
                    <div class="diagnostic-row">
                        <span>Permisos de Escritura en Raíz (.env & lock)</span>
                        <span class="<?= $isWritableRoot ? 'text-emerald' : 'text-warning' ?> fw-bold">
                            <i class="fa-solid <?= $isWritableRoot ? 'fa-check' : 'fa-triangle-exclamation' ?> me-1"></i>
                            <?= $isWritableRoot ? 'Escritura Correcta' : 'Solo Lectura (Verificar Chmod)' ?>
                        </span>
                    </div>
                </div>

                <!-- Input Clave Privada -->
                <form id="formStep1" class="mb-3">
                    <div class="mb-3">
                        <label for="inputSecretKey" class="form-label-custom">
                            Clave Privada de Instalación <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary border-opacity-50 text-white-50">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <input type="password" id="inputSecretKey" class="form-control form-control-custom font-mono" placeholder="DEVIOZ_HOSTINGER_2026_INSTALL" required autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary border-secondary border-opacity-50 text-white-50" id="btnToggleKeyVisibility">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">
                            Clave institucional única entregada en la pauta de despliegue.
                        </div>
                    </div>
                    <div id="step1Feedback" class="small mb-3"></div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-brand-install d-inline-flex align-items-center gap-2" id="btnVerifyKey">
                            <span>Verificar Clave y Continuar</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- PASO 2: CONEXIÓN A BASE DE DATOS MYSQL EN HOSTINGER           -->
            <!-- ============================================================= -->
            <div id="stepView2" class="d-none">
                <h5 class="fw-bold text-white mb-2">Paso 2: Conexión MySQL de Hostinger</h5>
                <p class="text-secondary small mb-4">
                    Ingresa los datos de la base de datos MySQL creada previamente en el panel de Hostinger (hPanel / cPanel).
                </p>

                <form id="formStep2" class="mb-3">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="dbHost" class="form-label-custom">Host MySQL <span class="text-danger">*</span></label>
                            <input type="text" id="dbHost" class="form-control form-control-custom font-mono" value="localhost" placeholder="localhost" required>
                            <div class="form-text text-muted" style="font-size: 0.70rem;">En Hostinger generalmente es <code>localhost</code> o <code>127.0.0.1</code>.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="dbPort" class="form-label-custom">Puerto</label>
                            <input type="text" id="dbPort" class="form-control form-control-custom font-mono" value="3306" placeholder="3306" required>
                        </div>

                        <div class="col-md-6">
                            <label for="dbName" class="form-label-custom">Nombre de la Base de Datos <span class="text-danger">*</span></label>
                            <input type="text" id="dbName" class="form-control form-control-custom font-mono" placeholder="u123456789_devioz" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dbUser" class="form-label-custom">Usuario MySQL <span class="text-danger">*</span></label>
                            <input type="text" id="dbUser" class="form-control form-control-custom font-mono" placeholder="u123456789_admin" required>
                        </div>

                        <div class="col-12">
                            <label for="dbPass" class="form-label-custom">Contraseña MySQL</label>
                            <div class="input-group">
                                <input type="password" id="dbPass" class="form-control form-control-custom font-mono" placeholder="Tu contraseña de base de datos de Hostinger">
                                <button type="button" class="btn btn-outline-secondary border-secondary border-opacity-50 text-white-50" id="btnToggleDbPass">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="step2Feedback" class="small mb-3"></div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-info btn-sm d-inline-flex align-items-center gap-2" id="btnTestDbConnection">
                            <i class="fa-solid fa-plug"></i>
                            <span>Probar Conexión</span>
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-ghost-install" id="btnBackToStep1">Atrás</button>
                            <button type="submit" class="btn btn-brand-install d-inline-flex align-items-center gap-2" id="btnGoToStep3">
                                <span>Continuar</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- PASO 3: URL DEL PROYECTO & CONFIGURACIÓN DEL ADMINISTRADOR    -->
            <!-- ============================================================= -->
            <div id="stepView3" class="d-none">
                <h5 class="fw-bold text-white mb-2">Paso 3: URL del Proyecto y Administrador</h5>
                <p class="text-secondary small mb-4">
                    Configura la dirección URL donde residirá el sistema y define las credenciales del Administrador principal.
                </p>

                <form id="formStep3" class="mb-3">
                    <div class="mb-4">
                        <label for="appUrlInput" class="form-label-custom">URL Base del Proyecto en Hostinger <span class="text-danger">*</span></label>
                        <input type="url" id="appUrlInput" class="form-control form-control-custom font-mono" value="<?= e($defaultAppUrl) ?>" required>
                        <div class="form-text text-muted" style="font-size: 0.70rem;">
                            Detectada automáticamente. Ejemplo: <code>https://devioz.com/mi_proyecto</code> o <code>http://localhost/nombre_proyecto</code>.
                        </div>
                    </div>

                    <div class="border-top pt-3 mb-3" style="border-color: rgba(255,255,255,0.08) !important;">
                        <h6 class="fw-bold text-white small mb-3 text-uppercase font-mono text-emerald">
                            <i class="fa-solid fa-user-gear me-1"></i> Cuenta de Administrador Principal
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="adminName" class="form-label-custom">Nombre del Administrador <span class="text-danger">*</span></label>
                                <input type="text" id="adminName" class="form-control form-control-custom" value="Elena Rostova" required>
                            </div>
                            <div class="col-md-6">
                                <label for="adminEmail" class="form-label-custom">Correo del Administrador <span class="text-danger">*</span></label>
                                <input type="email" id="adminEmail" class="form-control form-control-custom font-mono" value="admin@devioz.com" required>
                            </div>
                            <div class="col-12">
                                <label for="adminPass" class="form-label-custom">Contraseña de Administrador <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" id="adminPass" class="form-control form-control-custom font-mono" value="Devioz2026*" required minlength="6">
                                    <button type="button" class="btn btn-outline-secondary border-secondary border-opacity-50 text-white-50" id="btnToggleAdminPass">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted" style="font-size: 0.70rem;">
                                    Asegúrate de recordar o guardar esta clave para acceder al panel administrativo.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cuadro Informativo de Importación SQL -->
                    <div class="alert alert-dark border border-secondary border-opacity-25 p-3 rounded-3 small text-white-50 mb-3" style="background: rgba(15, 23, 42, 0.65);">
                        <div class="d-flex align-items-center gap-2 text-info fw-bold mb-1">
                            <i class="fa-solid fa-database"></i>
                            <span>Importación Automática de Datos y Proyectos</span>
                        </div>
                        El instalador creará las 12 tablas del sistema relacional e importará el portafolio completo con los 35 proyectos, practicantes asignados, hitos y auditorías técnicas QA.
                    </div>

                    <div id="step3Feedback" class="small mb-3"></div>

                    <!-- Barra de Progreso de Instalación (Inicialmente Oculta) -->
                    <div id="installProgressBarContainer" class="d-none mb-3">
                        <div class="d-flex justify-content-between small text-white-50 mb-1">
                            <span id="installProgressLabel">Instalando tablas y estructura...</span>
                            <span id="installProgressPercent">0%</span>
                        </div>
                        <div class="progress bg-dark" style="height: 8px;">
                            <div id="installProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-emerald" style="width: 20%;"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-ghost-install" id="btnBackToStep2">Atrás</button>
                        <button type="submit" class="btn btn-brand-install d-inline-flex align-items-center gap-2" id="btnExecuteInstall">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Iniciar Instalación en Hostinger</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- ============================================================= -->
            <!-- PASO 4: ÉXITO Y FINALIZACIÓN DE LA INSTALACIÓN                -->
            <!-- ============================================================= -->
            <div id="stepView4" class="d-none text-center py-4">
                <div class="mb-4">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.18); color: #10B981; border: 2px solid rgba(16, 185, 129, 0.4);">
                        <i class="fa-solid fa-circle-check fs-1"></i>
                    </span>
                </div>
                <span class="brand-badge mb-2 d-inline-block">DESPLIEGUE FINALIZADO</span>
                <h3 class="fw-bold text-white mb-2">¡Proyecto Instalado con Éxito!</h3>
                <p class="text-secondary small mx-auto mb-4" style="max-width: 520px;">
                    La base de datos MySQL, el portafolio de proyectos y la configuración del entorno <code class="text-info font-mono">.env</code> han sido generados exitosamente. El instalador ha sido bloqueado con seguridad.
                </p>

                <!-- Resumen de Credenciales de Acceso -->
                <div class="card bg-dark border border-secondary border-opacity-25 rounded-3 p-3 text-start small font-mono mx-auto mb-4" style="max-width: 480px;">
                    <div class="text-white-50 fw-bold mb-2 pb-1 border-bottom border-secondary border-opacity-25 text-uppercase" style="font-size: 0.70rem;">
                        Credenciales de Acceso del Administrador
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Correo:</span>
                        <span class="text-white fw-bold" id="finalAdminEmail">admin@devioz.com</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Contraseña:</span>
                        <span class="text-emerald fw-bold" id="finalAdminPass">••••••••</span>
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-2">
                    <a href="#" id="finalGoToSystemBtn" class="btn btn-brand-install d-inline-flex align-items-center gap-2 px-4 py-2">
                        <span>Ingresar a Devioz 24</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
    <?php endif; ?>

    <!-- Pie institucional -->
    <div class="text-center text-muted mt-3" style="font-size: 0.72rem;">
        DEVIOZ 24 &bull; SISTEMA DE GESTIÓN Y PORTAFOLIO DE PRÁCTICAS &bull; HOSTINGER COMPLIANT
    </div>
</div>

<!-- Scripts del Instalador en Vanilla JS -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Referencias a los pasos
    const stepView1 = document.getElementById('stepView1');
    const stepView2 = document.getElementById('stepView2');
    const stepView3 = document.getElementById('stepView3');
    const stepView4 = document.getElementById('stepView4');

    const stepPill1 = document.getElementById('stepPill1');
    const stepPill2 = document.getElementById('stepPill2');
    const stepPill3 = document.getElementById('stepPill3');
    const stepPill4 = document.getElementById('stepPill4');

    // 1. Mostrar/Ocultar contraseñas
    const setupToggle = (btnId, inputId) => {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        if (!btn || !input) return;
        btn.addEventListener('click', () => {
            const isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            btn.innerHTML = isPass ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
        });
    };
    setupToggle('btnToggleKeyVisibility', 'inputSecretKey');
    setupToggle('btnToggleDbPass', 'dbPass');
    setupToggle('btnToggleAdminPass', 'adminPass');

    // 2. Paso 1: Verificar Clave Privada
    const formStep1 = document.getElementById('formStep1');
    const step1Feedback = document.getElementById('step1Feedback');
    const btnVerifyKey = document.getElementById('btnVerifyKey');

    if (formStep1) {
        formStep1.addEventListener('submit', async (e) => {
            e.preventDefault();
            const key = document.getElementById('inputSecretKey').value.trim();
            if (!key) return;

            btnVerifyKey.disabled = true;
            btnVerifyKey.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Verificando...';
            step1Feedback.innerHTML = '';

            try {
                const formData = new FormData();
                formData.append('secret_key', key);

                const res = await fetch('instalar.php?action=verify_key', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    step1Feedback.innerHTML = `<div class="text-emerald"><i class="fa-solid fa-check me-1"></i> ${data.message}</div>`;
                    setTimeout(() => {
                        stepView1.classList.add('d-none');
                        stepView2.classList.remove('d-none');
                        stepPill1.classList.remove('active');
                        stepPill1.classList.add('completed');
                        stepPill2.classList.add('active');
                    }, 500);
                } else {
                    step1Feedback.innerHTML = `<div class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.message}</div>`;
                }
            } catch (err) {
                step1Feedback.innerHTML = `<div class="text-danger">Error de comunicación con el servidor.</div>`;
            } finally {
                btnVerifyKey.disabled = false;
                btnVerifyKey.innerHTML = '<span>Verificar Clave y Continuar</span> <i class="fa-solid fa-arrow-right"></i>';
            }
        });
    }

    // Navegación Atrás
    const btnBackToStep1 = document.getElementById('btnBackToStep1');
    if (btnBackToStep1) {
        btnBackToStep1.addEventListener('click', () => {
            stepView2.classList.add('d-none');
            stepView1.classList.remove('d-none');
            stepPill2.classList.remove('active');
            stepPill1.classList.add('active');
        });
    }

    // 3. Paso 2: Probar Conexión MySQL
    const btnTestDb = document.getElementById('btnTestDbConnection');
    const step2Feedback = document.getElementById('step2Feedback');

    const testDbConnection = async (showSuccessMsg = true) => {
        const host = document.getElementById('dbHost').value.trim();
        const port = document.getElementById('dbPort').value.trim();
        const name = document.getElementById('dbName').value.trim();
        const user = document.getElementById('dbUser').value.trim();
        const pass = document.getElementById('dbPass').value;

        if (!name || !user) {
            step2Feedback.innerHTML = '<div class="text-danger">Completa el nombre de base de datos y usuario.</div>';
            return false;
        }

        if (btnTestDb) {
            btnTestDb.disabled = true;
            btnTestDb.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Probando conexión...';
        }

        try {
            const formData = new FormData();
            formData.append('db_host', host);
            formData.append('db_port', port);
            formData.append('db_name', name);
            formData.append('db_user', user);
            formData.append('db_pass', pass);

            const res = await fetch('instalar.php?action=test_db', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                if (showSuccessMsg) {
                    step2Feedback.innerHTML = `<div class="text-emerald"><i class="fa-solid fa-circle-check me-1"></i> ${data.message} (${data.server_info})</div>`;
                }
                return true;
            } else {
                step2Feedback.innerHTML = `<div class="text-danger"><i class="fa-solid fa-circle-xmark me-1"></i> ${data.message}</div>`;
                return false;
            }
        } catch (err) {
            step2Feedback.innerHTML = `<div class="text-danger">Error de red al probar conexión MySQL.</div>`;
            return false;
        } finally {
            if (btnTestDb) {
                btnTestDb.disabled = false;
                btnTestDb.innerHTML = '<i class="fa-solid fa-plug me-1"></i> Probar Conexión';
            }
        }
    };

    if (btnTestDb) {
        btnTestDb.addEventListener('click', () => testDbConnection(true));
    }

    const formStep2 = document.getElementById('formStep2');
    if (formStep2) {
        formStep2.addEventListener('submit', async (e) => {
            e.preventDefault();
            const ok = await testDbConnection(false);
            if (!ok) return;

            stepView2.classList.add('d-none');
            stepView3.classList.remove('d-none');
            stepPill2.classList.remove('active');
            stepPill2.classList.add('completed');
            stepPill3.classList.add('active');
        });
    }

    const btnBackToStep2 = document.getElementById('btnBackToStep2');
    if (btnBackToStep2) {
        btnBackToStep2.addEventListener('click', () => {
            stepView3.classList.add('d-none');
            stepView2.classList.remove('d-none');
            stepPill3.classList.remove('active');
            stepPill2.classList.add('active');
        });
    }

    // 4. Paso 3: Ejecutar la Instalación Completa
    const formStep3 = document.getElementById('formStep3');
    const step3Feedback = document.getElementById('step3Feedback');
    const btnExecuteInstall = document.getElementById('btnExecuteInstall');
    const installProgressBarContainer = document.getElementById('installProgressBarContainer');
    const installProgressBar = document.getElementById('installProgressBar');
    const installProgressLabel = document.getElementById('installProgressLabel');
    const installProgressPercent = document.getElementById('installProgressPercent');

    if (formStep3) {
        formStep3.addEventListener('submit', async (e) => {
            e.preventDefault();

            btnExecuteInstall.disabled = true;
            btnBackToStep2.disabled = true;
            installProgressBarContainer.classList.remove('d-none');
            step3Feedback.innerHTML = '';

            // Animación de pasos
            installProgressLabel.textContent = 'Verificando y conectando a MySQL...';
            installProgressBar.style.width = '30%';
            installProgressPercent.textContent = '30%';

            const formData = new FormData();
            formData.append('db_host', document.getElementById('dbHost').value.trim());
            formData.append('db_port', document.getElementById('dbPort').value.trim());
            formData.append('db_name', document.getElementById('dbName').value.trim());
            formData.append('db_user', document.getElementById('dbUser').value.trim());
            formData.append('db_pass', document.getElementById('dbPass').value);

            formData.append('app_url', document.getElementById('appUrlInput').value.trim());
            formData.append('admin_name', document.getElementById('adminName').value.trim());
            formData.append('admin_email', document.getElementById('adminEmail').value.trim());
            formData.append('admin_pass', document.getElementById('adminPass').value);

            try {
                setTimeout(() => {
                    installProgressLabel.textContent = 'Creando tablas e importando portafolio...';
                    installProgressBar.style.width = '65%';
                    installProgressPercent.textContent = '65%';
                }, 800);

                const res = await fetch('instalar.php?action=run_install', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.success) {
                    installProgressLabel.textContent = '¡Completado! Configurando .env y bloqueo...';
                    installProgressBar.style.width = '100%';
                    installProgressPercent.textContent = '100%';

                    setTimeout(() => {
                        stepView3.classList.add('d-none');
                        stepView4.classList.remove('d-none');
                        stepPill3.classList.remove('active');
                        stepPill3.classList.add('completed');
                        stepPill4.classList.add('completed');

                        document.getElementById('finalAdminEmail').textContent = data.admin_email;
                        document.getElementById('finalAdminPass').textContent = document.getElementById('adminPass').value;
                        document.getElementById('finalGoToSystemBtn').href = data.redirect_url;
                    }, 600);
                } else {
                    installProgressBarContainer.classList.add('d-none');
                    btnExecuteInstall.disabled = false;
                    btnBackToStep2.disabled = false;
                    step3Feedback.innerHTML = `<div class="text-danger mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.message}</div>`;
                }
            } catch (err) {
                installProgressBarContainer.classList.add('d-none');
                btnExecuteInstall.disabled = false;
                btnBackToStep2.disabled = false;
                step3Feedback.innerHTML = `<div class="text-danger mt-2">Error inesperado en el servidor durante la ejecución.</div>`;
            }
        });
    }
});
</script>
</body>
</html>
