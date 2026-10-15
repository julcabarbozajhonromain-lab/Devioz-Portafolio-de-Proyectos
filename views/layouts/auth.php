<?php

declare(strict_types=1);

use App\Core\View;
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-url" content="<?= rtrim(url('/'), '/') ?>">
    <title><?= e($title ?? 'Iniciar Sesión — Devioz OS') ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/devioz-icon.png') ?>">

    <!-- Preload del Fondo Oficial Bitrix24 HD -->
    <link rel="preload" href="<?= asset('images/bitrix24-bg-hd.webp') ?>" as="image">

    <!-- Google Fonts: Open Sans Oficial Bitrix24 & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5.1 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= asset('css/custom.css') ?>">

    <style>
        :root {
            /* Variables Bitrix24 Design System */
            --b24-cyan: #00ACE3;
            --b24-cyan-hover: #0093c4;
            --b24-cyan-light: #EBF8FD;
            --b24-lime: #9DCF00;
            --b24-lime-hover: #88B500;
            --b24-navy: #172B4D;
            --b24-dark: #0F172A;
            --b24-border: #DFE3E6;
            --b24-gray-text: #535C69;
            --b24-charcoal: #202428;
        }

        /* Estructura Base con Imagen de Fondo Bitrix24 */
        body.b24net-auth-page {
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: #1E293B url('<?= asset("images/bitrix24-bg-hd.webp") ?>') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            color: var(--b24-charcoal);
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Overlay Gradiente Suave para Garantizar Contraste y Legibilidad */
        .b24net-overlay {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 75% 50%, rgba(15, 23, 42, 0.25) 0%, rgba(15, 23, 42, 0.55) 100%);
            backdrop-filter: blur(1.5px);
            -webkit-backdrop-filter: blur(1.5px);
            z-index: 1;
            pointer-events: none;
        }

        /* Contenedor Principal de la Capa de UI */
        .b24net-layout-container {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Topbar / Header Bitrix24 */
        .b24net-header {
            padding: 1.5rem 2rem 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .b24net-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: #FFFFFF;
            font-weight: 800;
            font-size: 1.35rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
            letter-spacing: -0.5px;
        }

        .b24net-brand-tag {
            font-size: 0.85rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.85);
            margin-left: 0.5rem;
            padding-left: 0.75rem;
            border-left: 1px solid rgba(255, 255, 255, 0.35);
        }

        .b24net-btn-home {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #FFFFFF;
            padding: 0.45rem 1rem;
            border-radius: 20px;
            font-size: 0.84rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
        }

        .b24net-btn-home:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        /* Área Central con Columna Split */
        .b24net-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1.25rem;
        }

        /* Columna Izquierda: Cover / Hero Institucional */
        .b24net-aside-cover {
            color: #FFFFFF;
            padding-right: 2.5rem;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.6);
        }

        .b24net-aside-badge {
            background: rgba(0, 172, 227, 0.25);
            border: 1px solid rgba(0, 172, 227, 0.6);
            color: #EBF8FD;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 1.2rem;
            backdrop-filter: blur(6px);
        }

        .b24net-aside-title {
            font-size: clamp(2.2rem, 3.8vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 1rem;
            color: #FFFFFF;
        }

        .b24net-aside-subtitle {
            font-size: 1.1rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            max-width: 500px;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.5);
        }

        .b24net-feature-pill {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 12px;
            padding: 0.65rem 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.88rem;
            font-weight: 600;
            max-width: 440px;
            color: #FFFFFF;
        }

        /* Tarjeta Isla de Autenticación (Estilo Bitrix24 Island Card) */
        .b24-island-card {
            background: #FFFFFF;
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 2.5rem 2.25rem 2.25rem;
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
            position: relative;
            animation: islandFadeIn 0.3s ease;
        }

        @keyframes islandFadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Estilización de Inputs Bitrix24 */
        .form-control-custom,
        .form-select-custom {
            background-color: #F8FAFC !important;
            border: 1px solid #CBD5E1 !important;
            color: #0F172A !important;
            border-radius: 8px;
            padding: 0.7rem 0.95rem;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .form-control-custom:focus,
        .form-select-custom:focus {
            background-color: #FFFFFF !important;
            border-color: var(--b24-cyan) !important;
            box-shadow: 0 0 0 3px rgba(0, 172, 227, 0.22) !important;
            outline: none;
        }

        .form-label-custom {
            font-size: 0.82rem;
            font-weight: 700;
            color: #475569;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        /* Botón Primario Bitrix24 Cyan */
        .btn-b24-auth-submit {
            background-color: var(--b24-cyan);
            border-color: var(--b24-cyan);
            color: #FFFFFF !important;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 24px;
            padding: 0.75rem 1.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(0, 172, 227, 0.35);
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            width: 100%;
        }

        .btn-b24-auth-submit:hover {
            background-color: var(--b24-cyan-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 172, 227, 0.5);
        }

        /* Botón de alternar contraseña ("ojito") */
        .auth-password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .btn-toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94A3B8;
            padding: 0.25rem 0.35rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.35rem;
            transition: color 0.15s ease;
            z-index: 5;
        }

        .btn-toggle-password:hover {
            color: var(--b24-cyan);
        }

        /* Acordeón de Credenciales Demo */
        .btn-demo-creds-toggle {
            background: transparent;
            border: none;
            color: #64748B;
            font-size: 0.8rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.2s ease;
        }

        .btn-demo-creds-toggle:hover {
            color: var(--b24-navy);
            background: #F1F5F9;
        }

        /* Botones de Proveedores OAuth (Google & GitHub) */
        .btn-oauth-provider {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid #CBD5E1;
            background: #FFFFFF;
            color: #1E293B !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            cursor: pointer;
        }

        .btn-oauth-provider:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .btn-oauth-google:hover {
            border-color: #4285F4;
            background: #F8FAFF;
            color: #1A73E8 !important;
        }

        .btn-oauth-github:hover {
            border-color: #0F172A;
            background: #F1F5F9;
            color: #0F172A !important;
        }

        /* Footer Bitrix24 Network */
        .b24net-footer {
            padding: 1rem 2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.85);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .b24net-footer-btn {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
            padding: 0.35rem 0.85rem;
            border-radius: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .b24net-footer a {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .b24net-footer a:hover {
            color: #FFFFFF;
            text-decoration: underline;
        }

        /* Responsividad */
        @media (max-width: 991.98px) {
            .b24net-header {
                padding: 1rem 1.25rem;
            }
            .b24net-aside-cover {
                display: none;
            }
            .b24-island-card {
                padding: 2rem 1.5rem;
            }
            .b24net-footer {
                justify-content: center;
                text-align: center;
                padding: 1rem 1.25rem;
            }
        }
    </style>
</head>
<body class="b24net-auth-page">

    <!-- Capa de Oscurecimiento Gradual -->
    <div class="b24net-overlay"></div>

    <div class="b24net-layout-container">
        
        <!-- Header Superior -->
        <header class="b24net-header">
            <a href="<?= url('/') ?>" class="b24net-brand">
                <img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz OS" width="32" height="32" class="rounded">
                <span>Devioz<span style="color: var(--b24-cyan);">OS</span></span>
                <span class="b24net-brand-tag d-none d-sm-inline">Su espacio de trabajo ideal</span>
            </a>

            <a href="<?= url('/') ?>" class="b24net-btn-home" title="Volver a la portada principal">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="d-none d-sm-inline">Volver al Inicio</span>
            </a>
        </header>

        <!-- Contenido Central Split (Cover Izquierdo + Isla Derecha) -->
        <main class="b24net-main">
            <div class="container-xl">
                <div class="row align-items-center g-4 justify-content-center">
                    
                    <!-- Columna Izquierda: Información de Portada (Visible en Desktop) -->
                    <div class="col-lg-6 col-xl-7 d-none d-lg-block">
                        <div class="b24net-aside-cover">
                            
                            <div class="b24net-aside-badge">
                                <i class="fa-solid fa-shield-halved text-info"></i>
                                <span>Estándar Enterprise • ISO 25010 • ISO 27001 • TMMi 3</span>
                            </div>

                            <h1 class="b24net-aside-title">
                                Su espacio de trabajo ideal
                            </h1>

                            <p class="b24net-aside-subtitle">
                                Gestión ágil de proyectos, evaluación de talento TI y aseguramiento métrico de calidad con asistencia de IA LABVA.
                            </p>

                            <!-- Píldoras de Valor Institucional -->
                            <div class="b24net-feature-pill">
                                <i class="fa-solid fa-circle-check text-success fs-5"></i>
                                <span>Gobernanza con QA Scorecard (0 a 100 pts) y Quality Gate.</span>
                            </div>

                            <div class="b24net-feature-pill">
                                <i class="fa-solid fa-lock text-info fs-5"></i>
                                <span>Seguridad Zero-Trust, sesiones blindadas y OWASP Top 10.</span>
                            </div>

                            <div class="b24net-feature-pill">
                                <i class="fa-solid fa-clock text-warning fs-5"></i>
                                <span>Workday con control biométrico y geocercas en hubs de Devioz.</span>
                            </div>

                        </div>
                    </div>

                    <!-- Columna Derecha: Tarjeta Isla del Formulario -->
                    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                        <div class="b24-island-card">
                            
                            <!-- Flash Messages -->
                            <?= View::partial('partials/flash') ?>

                            <!-- Contenido Dinámico (Login o Registro) -->
                            <?= $content ?>

                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Footer Inferior -->
        <footer class="b24net-footer">
            <div class="d-flex align-items-center gap-3">
                <div class="b24net-footer-btn">
                    <i class="fa-solid fa-globe"></i>
                    <span>Español</span>
                </div>
                <a href="<?= url('/#normativas') ?>">Normativas & ISOs</a>
                <a href="<?= url('/#sustento') ?>">Soporte TI</a>
            </div>

            <div>
                <span>© 2026 Devioz OS • Devioz Technology Solutions</span>
            </div>
        </footer>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>

    <!-- Script Global de Interacciones de Autenticación -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Mostrar/Ocultar Contraseña ("Ojito")
            document.querySelectorAll('.btn-toggle-password').forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('data-target');
                    const targetInput = document.getElementById(targetId);
                    if (!targetInput) return;

                    const isPassword = targetInput.getAttribute('type') === 'password';
                    targetInput.setAttribute('type', isPassword ? 'text' : 'password');

                    const eyeOpen = this.querySelector('.eye-open');
                    const eyeClosed = this.querySelector('.eye-closed');

                    if (eyeOpen && eyeClosed) {
                        if (isPassword) {
                            eyeOpen.classList.add('d-none');
                            eyeClosed.classList.remove('d-none');
                            this.setAttribute('aria-label', 'Ocultar contraseña');
                        } else {
                            eyeOpen.classList.remove('d-none');
                            eyeClosed.classList.add('d-none');
                            this.setAttribute('aria-label', 'Mostrar contraseña');
                        }
                    }
                });
            });

            // 2. Prevención de Doble Envío de Formularios con Spinner de Carga
            const authForm = document.querySelector('form');
            if (authForm) {
                authForm.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.disabled = true;
                        const btnText = submitBtn.querySelector('span');
                        if (btnText) {
                            btnText.textContent = 'Procesando...';
                        }
                        const icon = submitBtn.querySelector('i, svg');
                        if (icon) {
                            icon.outerHTML = '<span class="spinner-border spinner-border-sm ms-1" role="status" aria-hidden="true"></span>';
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>