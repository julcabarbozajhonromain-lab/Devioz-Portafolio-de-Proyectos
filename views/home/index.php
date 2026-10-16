<?php

declare(strict_types=1);

/**
 * Devioz Technology Solutions — Portal Corporativo Institucional & Showcase TI
 *
 * Arquitectura:
 *   - End-to-End Corporate Storytelling (La historia de innovación, calidad y entrega)
 *   - Zero-Leak Public Isolation (La landing es 100% autónoma para clientes y observadores)
 *   - Autonomous Modals: Devioz Reels 16:9 Lightbox & Dossier de Ficha Técnica
 *   - Estándares Internacionales: ISO/IEC 25010, ISO/IEC 27001, OWASP Top 10, Pruebas de software
 *   - Identidad visual Devioz: esmeralda, menta y pizarra
 *
 * @var array<\App\Models\Project> $featuredProjects
 * @var array<\App\Models\Project> $reelProjects
 * @var string|null $title
 */

// Helper para parsear URLs de video a reproductor embebido seguro y miniaturas dinámicas
if (!function_exists('devioz_parse_video')) {
    function devioz_parse_video(?string $url): array {
        if (!$url) {
            return ['embed' => '', 'thumb' => '', 'platform' => 'none'];
        }
        // YouTube (watch, shorts, embed, youtu.be)
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return [
                'embed'    => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0&modestbranding=1',
                'thumb'    => 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg',
                'platform' => 'youtube',
            ];
        }
        // Vimeo
        if (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/', $url, $m)) {
            return [
                'embed'    => 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1',
                'thumb'    => '',
                'platform' => 'vimeo',
            ];
        }
        return [
            'embed'    => $url,
            'thumb'    => '',
            'platform' => 'direct',
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <meta name="app-url" content="<?= rtrim(url('/'), '/') ?>">
    <meta name="description" content="Devioz: consultor&iacute;a tecnol&oacute;gica, desarrollo de software e inteligencia artificial aplicada.">
    <meta name="keywords" content="Devioz, consultoría tecnológica, desarrollo de software, inteligencia artificial, automatización, cloud, DevOps, seguridad y calidad de software">
    <meta name="author" content="Devioz Technology Solutions">

    <title><?= e($title ?? 'Devioz Technology Solutions — Fábrica de Software, Cloud & Aseguramiento de Calidad') ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/devioz-icon.png') ?>">

    <!-- Google Fonts: Open Sans & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6.5.1 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        :root {
            /* Paleta visual de la plataforma */
            --ui-primary: #10B981;
            --ui-primary-hover: #059669;
            --ui-primary-dark: #047857;
            --ui-primary-subtle: #ECFDF5;
            --ui-accent: #34D399;
            --ui-accent-hover: #15803D;
            --ui-navy: #1F2937;
            --ui-navy-light: #253858;
            --ui-dark: #0F172A;
            --ui-darker: #0B1120;
            --ui-gray-bg: #F8FAFC;
            --ui-gray-border: #E2E8F0;
            --ui-gray-text: #475569;
            --ui-charcoal: #1E293B;
            --ui-white: #FFFFFF;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--ui-charcoal);
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        code, pre, .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ------------------------------------------------------------- */
        /* TOPBAR & NAVEGACIÓN                                           */
        /* ------------------------------------------------------------- */
        .ui-topbar {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--ui-gray-border);
            position: sticky;
            top: 0;
            z-index: 1040;
            transition: all 0.25s ease-in-out;
        }

        .ui-topbar.scrolled {
            box-shadow: 0 4px 20px rgba(31, 41, 55, 0.08);
            background: rgba(255, 255, 255, 0.98);
        }

        .ui-brand {
            font-weight: 800;
            font-size: 1.45rem;
            color: var(--ui-navy);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            letter-spacing: -0.5px;
        }

        .ui-brand:hover {
            color: var(--ui-navy);
        }

        .ui-brand-badge {
            background: linear-gradient(135deg, var(--ui-primary), #059669);
            color: #fff;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .ui-nav-link {
            color: var(--ui-gray-text);
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            padding: 0.5rem 0.85rem;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .ui-nav-link:hover {
            color: var(--ui-primary);
            background: var(--ui-primary-subtle);
        }

        /* Botones de Acción */
        .btn-ui-accent {
            background-color: var(--ui-accent);
            color: #1F2937 !important;
            font-weight: 700;
            font-size: 0.88rem;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            padding: 0.65rem 1.45rem;
            border-radius: 28px;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            box-shadow: 0 4px 14px rgba(52, 211, 153, 0.35);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-ui-accent:hover {
            background-color: var(--ui-accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(52, 211, 153, 0.45);
            color: #0F172A !important;
        }

        .btn-ui-primary {
            background-color: var(--ui-primary);
            color: #FFFFFF !important;
            font-weight: 700;
            font-size: 0.88rem;
            padding: 0.65rem 1.45rem;
            border-radius: 28px;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.32);
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-ui-primary:hover {
            background-color: var(--ui-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }

        .btn-ui-outline {
            background: transparent;
            color: var(--ui-navy) !important;
            border: 1.5px solid var(--ui-gray-border);
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.6rem 1.35rem;
            border-radius: 28px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .btn-ui-outline:hover {
            border-color: var(--ui-primary);
            color: var(--ui-primary) !important;
            background: var(--ui-primary-subtle);
        }

        .btn-portal-client {
            color: var(--ui-navy);
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            padding: 0.5rem 0.95rem;
            border-radius: 20px;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            cursor: pointer;
        }

        .btn-portal-client:hover {
            color: var(--ui-primary);
            background: #FFFFFF;
            border-color: var(--ui-primary);
        }

        /* ------------------------------------------------------------- */
        /* HERO SECTION                                                  */
        /* ------------------------------------------------------------- */
        .ui-hero-section {
            background: radial-gradient(90% 75% at 85% 28%, #EAF7F3 0%, rgba(234, 247, 243, 0) 62%), #FFFFFF;
            padding: 6.5rem 0 5.5rem;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--ui-gray-border);
        }

        .ui-hero-badge {
            background: #F0F8F5;
            color: #087A62;
            border: 1px solid #D7ECE4;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.38rem 1rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.4rem;
        }

        .ui-hero-title {
            font-size: clamp(2.75rem, 4.4vw, 4.2rem);
            font-weight: 700;
            color: #142B40;
            line-height: 1.08;
            letter-spacing: -1.8px;
            margin-bottom: 1.3rem;
        }

        .ui-hero-subtitle {
            font-size: 1.08rem;
            color: #596B7C;
            line-height: 1.75;
            margin-bottom: 1.7rem;
            max-width: 540px;
        }

        .ui-hero-services { display:flex; flex-wrap:wrap; gap:.55rem; margin-bottom:1.8rem; }
        .ui-hero-services span { display:inline-flex; align-items:center; gap:.45rem; color:#385366; font-size:.82rem; font-weight:600; background:#fff; border:1px solid #E0E9EC; border-radius:999px; padding:.48rem .76rem; }
        .ui-hero-services i { color:#0F9B77; font-size:.78rem; }
        .ui-hero-visual { animation: heroReveal .9s cubic-bezier(.2,.7,.25,1) both; }
        .ui-hero-visual img { width:100%; height:auto; display:block; filter:drop-shadow(0 22px 35px rgba(13,39,56,.15)); animation: heroFloat 7s ease-in-out 1s infinite; }
        .ui-hero-note { font-size:.82rem; color:#667989; }
        @keyframes heroReveal { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
        @keyframes heroFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-7px); } }

        .ui-hero-checklist {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem;
        }

        .ui-hero-checklist li {
            font-size: 0.93rem;
            color: #334155;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
        }

        .ui-hero-checklist .check-icon {
            color: var(--ui-accent);
            background: #F4FBE8;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
            margin-top: 2px;
            border: 1px solid #D4EDB2;
        }

        /* Mockup Interactivo Terminal / Pipeline */
        .ui-mockup-wrapper {
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            position: relative;
        }

        .ui-mockup-header {
            background: #F8FAFC;
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--ui-gray-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ui-window-dots {
            display: flex;
            gap: 6px;
        }

        .ui-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .ui-dot-red { background: #EF4444; }
        .ui-dot-yellow { background: #F59E0B; }
        .ui-dot-green { background: #10B981; }

        .ui-floating-badge {
            position: absolute;
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 2;
            animation: floatUpDown 4s ease-in-out infinite;
        }

        .badge-pos-1 {
            top: -20px;
            right: 20px;
            animation-delay: 0s;
        }

        .badge-pos-2 {
            bottom: -20px;
            left: 20px;
            animation-delay: 2s;
        }

        @keyframes floatUpDown {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        /* ------------------------------------------------------------- */
        /* CINTA DE MÉTRICAS & RESUMEN DE CONFIANZA                      */
        /* ------------------------------------------------------------- */
        .ui-metrics-ribbon {
            background: #142B40;
            color: #FFFFFF;
            padding: 1.25rem 0;
            border-top: 1px solid #1E293B;
            border-bottom: 1px solid #1E293B;
        }

        .ui-metric-card {
            text-align: center;
        }

        .ui-metric-num {
            font-size: 1rem;
            font-weight: 650;
            color: #FFFFFF;
            letter-spacing: 0;
            line-height: 1.35;
        }

        .ui-metric-label {
            font-size: 0.85rem;
            color: #B9C8D3;
            margin-top: 0.2rem;
            font-weight: 400;
        }

        /* Trust Badges */
        .ui-trust-section {
            background: #FFFFFF;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--ui-gray-border);
        }

        .ui-trust-badge-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #F8FAFC;
            border: 1px solid var(--ui-gray-border);
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
        }

        /* ------------------------------------------------------------- */
        /* TITULOS DE SECCIÓN ESTANDARIZADOS                             */
        /* ------------------------------------------------------------- */
        .ui-section-tag {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 0.85rem;
        }

        .ui-section-title {
            font-size: 2.35rem;
            font-weight: 800;
            color: var(--ui-navy);
            letter-spacing: -0.5px;
            margin-bottom: 1rem;
        }

        .ui-section-desc {
            font-size: 1.05rem;
            color: #64748B;
            max-width: 720px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }

        /* ------------------------------------------------------------- */
        /* SERVICIOS CORE                                                */
        /* ------------------------------------------------------------- */
        .ui-service-card {
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            border-radius: 16px;
            padding: 2.25rem 2rem;
            height: 100%;
            transition: all 0.25s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .ui-service-card:hover {
            border-color: var(--ui-primary);
            box-shadow: 0 15px 35px rgba(31, 41, 55, 0.08);
            transform: translateY(-4px);
        }

        .ui-service-icon {
            width: 54px;
            height: 54px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            margin-bottom: 1.25rem;
        }

        /* ------------------------------------------------------------- */
        /* METODOLOGÍA CRONOLÓGICA (TIMELINE END-TO-END)                */
        /* ------------------------------------------------------------- */
        .ui-timeline-wrapper {
            position: relative;
            padding: 1.5rem 0;
        }

        .ui-timeline-wrapper::before {
            content: '';
            position: absolute;
            top: 40px;
            bottom: 40px;
            left: 50%;
            width: 3px;
            background: #E2E8F0;
            transform: translateX(-50%);
        }

        @media (max-width: 991.98px) {
            .ui-timeline-wrapper::before {
                left: 30px;
            }
        }

        .ui-timeline-item {
            position: relative;
            margin-bottom: 3rem;
        }

        .ui-timeline-dot {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #FFFFFF;
            border: 4px solid var(--ui-primary);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            color: var(--ui-navy);
            font-size: 1.1rem;
            z-index: 3;
        }

        @media (max-width: 991.98px) {
            .ui-timeline-dot {
                left: 30px;
            }
        }

        .ui-timeline-content {
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: all 0.25s ease;
        }

        .ui-timeline-content:hover {
            border-color: var(--ui-primary);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        /* ------------------------------------------------------------- */
        /* LOS 4 PILARES DE CALIDAD Y NORMATIVAS                         */
        /* ------------------------------------------------------------- */
        .ui-pillar-card {
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            border-radius: 16px;
            padding: 2rem 1.75rem;
            height: 100%;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }

        .ui-pillar-card:hover {
            border-color: var(--ui-primary);
            box-shadow: 0 15px 35px rgba(31, 41, 55, 0.08);
            transform: translateY(-4px);
        }

        .ui-pillar-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .pillar-iso25010::before { background: #10B981; }
        .pillar-iso27001::before { background: #059669; }
        .pillar-owasp::before { background: #8B5CF6; }
        .pillar-tmmi::before { background: #F59E0B; }

        /* ------------------------------------------------------------- */
        /* DEVIOZ REELS (DEMOS 16:9 VERTICALES AUTÓNOMAS)                */
        /* ------------------------------------------------------------- */
        .ui-reels-section {
            background: linear-gradient(180deg, #0F172A 0%, #1F2937 100%);
            color: #FFFFFF;
            padding: 5rem 0;
            position: relative;
        }

        .ui-reel-card {
            background: rgba(14, 22, 45, 0.65);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 8px 8px 12px 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .ui-reel-card:hover {
            transform: translateY(-5px);
            background: rgba(18, 30, 60, 0.82);
            border-color: rgba(16, 185, 129, 0.55);
            box-shadow: 0 16px 36px rgba(16, 185, 129, 0.25), 0 0 20px rgba(16, 185, 129, 0.15);
        }

        .ui-reel-poster-box {
            position: relative;
            width: 100%;
            aspect-ratio: 3 / 4;
            border-radius: 14px;
            overflow: hidden;
            background: #0B1120;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ui-reel-card:hover .ui-reel-poster-box {
            border-color: rgba(16, 185, 129, 0.75);
            box-shadow: 0 16px 36px rgba(16, 185, 129, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.4);
        }

        .ui-reel-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .ui-reel-card:hover .ui-reel-bg {
            transform: scale(1.06);
        }

        /* Hover Overlay Liquid Glass Estilo Bilibili */
        .ui-reel-hover-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(8, 14, 30, 0.12) 0%, rgba(8, 14, 30, 0.75) 45%, rgba(6, 11, 26, 0.96) 100%);
            backdrop-filter: blur(14px) saturate(180%);
            -webkit-backdrop-filter: blur(14px) saturate(180%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 14px 12px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 3;
        }

        .ui-reel-card:hover .ui-reel-hover-overlay {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .ui-reel-play-btn-blue {
            width: 100%;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: #FFFFFF !important;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            letter-spacing: 0.02em;
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.5);
            transition: all 0.2s ease;
        }

        .ui-reel-card:hover .ui-reel-play-btn-blue {
            background: linear-gradient(135deg, #34D399 0%, #10B981 100%);
            box-shadow: 0 6px 22px rgba(16, 185, 129, 0.75);
            transform: scale(1.02);
        }

        /* ------------------------------------------------------------- */
        /* SHOWCASE DE PROYECTOS CERTIFICADOS                            */
        /* ------------------------------------------------------------- */
        .ui-portfolio-filter-btn {
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            color: var(--ui-gray-text);
            font-weight: 600;
            font-size: 0.88rem;
            padding: 0.5rem 1.15rem;
            border-radius: 24px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .ui-portfolio-filter-btn:hover {
            border-color: var(--ui-primary);
            color: var(--ui-primary);
            background: var(--ui-primary-subtle);
        }

        .ui-portfolio-filter-btn.active {
            background: var(--ui-navy);
            color: #FFFFFF;
            border-color: var(--ui-navy);
            box-shadow: 0 4px 12px rgba(31, 41, 55, 0.15);
        }

        .ui-project-card {
            background: #FFFFFF;
            border: 1px solid var(--ui-gray-border);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .ui-project-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(31, 41, 55, 0.08);
            border-color: var(--ui-primary);
        }

        .ui-project-cover {
            height: 190px;
            position: relative;
            background-color: #E2E8F0;
            overflow: hidden;
        }

        .ui-project-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .ui-project-card:hover .ui-project-cover img {
            transform: scale(1.05);
        }

        .ui-tech-tag {
            background: #F1F5F9;
            color: #334155;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        /* ------------------------------------------------------------- */
        /* MODAL AUTÓNOMO: REEL LIGHTBOX 16:9                            */
        /* ------------------------------------------------------------- */
        .ui-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(10px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }

        .ui-modal-overlay.active {
            display: flex;
            animation: fadeInModal 0.25s ease;
        }

        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .ui-reel-dialog {
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            width: 100%;
            max-width: 880px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75), 0 0 40px rgba(16, 185, 129, 0.25);
            overflow: hidden;
            position: relative;
            animation: slideUpModal 0.25s ease;
        }

        @keyframes slideUpModal {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .ui-reel-player-box {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 9;
            height: auto;
            background: #000;
        }

        .ui-reel-player-box iframe,
        .ui-reel-player-box video {
            width: 100%;
            height: 100%;
            border: none;
        }

        .ui-close-reel-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.6);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 10;
            transition: all 0.2s ease;
        }

        .ui-close-reel-btn:hover {
            background: #EF4444;
            border-color: #EF4444;
        }

        /* ------------------------------------------------------------- */
        /* MODAL AUTÓNOMO: FICHA TÉCNICA DEL PROYECTO (PUBLIC DOSSIER)   */
        /* ------------------------------------------------------------- */
        .ui-dossier-dialog {
            background: #FFFFFF;
            border-radius: 20px;
            width: 100%;
            max-width: 780px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            position: relative;
            animation: slideUpModal 0.25s ease;
        }

        /* ------------------------------------------------------------- */
        /* FOOTER CORPORATIVO DEVIOZ                                   */
        /* ------------------------------------------------------------- */
        .ui-footer {
            background: #0F172A;
            color: #94A3B8;
            padding: 5rem 0 2.5rem;
            border-top: 1px solid #1E293B;
            font-size: 0.88rem;
        }

        .ui-footer-title {
            color: #F8FAFC;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ui-footer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ui-footer-list li {
            margin-bottom: 0.65rem;
        }

        .ui-footer-list a {
            color: #94A3B8;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .ui-footer-list a:hover {
            color: var(--ui-primary);
        }

        @media (max-width: 991.98px) {
            .ui-hero-section {
                padding: 3.5rem 0 2.5rem;
                text-align: center;
            }
            .ui-hero-title {
                font-size: 2.15rem;
            }
            .ui-hero-subtitle {
                margin: 0 auto 1.5rem;
            }
            .ui-hero-checklist {
                display: inline-block;
                text-align: left;
            }
            .badge-pos-1, .badge-pos-2 {
                display: none;
            }
        }
        @media (max-width: 575.98px) {
            .ui-hero-section { padding: 2.75rem 0 2rem; }
            .ui-hero-title { font-size: clamp(2rem, 9vw, 2.65rem); letter-spacing: -1px; }
            .ui-hero-services { justify-content: center; }
            .ui-hero-services span { font-size: .76rem; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>

    <!-- ===================================================================== -->
    <!-- 1. TOPBAR DE NAVEGACIÓN INSTITUCIONAL (AUTÓNOMA)                      -->
    <!-- ===================================================================== -->
    <header class="ui-topbar" id="mainHeader">
        <div class="container-xl d-flex align-items-center justify-content-between py-2 py-lg-3">

            <!-- Logo Institucional -->
            <div class="d-flex align-items-center gap-3">
                <a href="#inicio" class="ui-brand">
                    <img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz Technology Solutions" width="34" height="34" class="rounded">
                    <span>Devioz</span>
                    <span class="ui-brand-badge">Tecnolog&iacute;a y consultor&iacute;a</span>
                </a>
            </div>

            <!-- Menú de Navegación Suave (In-Page Anchors — Cero saltos internos) -->
            <nav class="d-none d-lg-flex align-items-center gap-1">
                <a href="#servicios" class="ui-nav-link">Capacidades</a><a href="#metodologia" class="ui-nav-link">C&oacute;mo trabajamos</a><a href="#calidad" class="ui-nav-link">Calidad</a><a href="#reels" class="ui-nav-link">Demos</a><a href="#portafolio" class="ui-nav-link">Proyectos</a><a href="#contacto" class="ui-nav-link">Contacto</a>
            </nav>

            <!-- Acciones Derecha: Portal Clientes & Botón CTA -->
            <div class="d-flex align-items-center gap-2 gap-sm-3">

                <!-- Selector de Idioma Simbólico -->
                <div class="d-none d-md-flex align-items-center text-muted small fw-bold px-2.5 py-1 rounded" style="background: #F1F5F9; font-size: 0.78rem;">
                    <i class="fa-solid fa-globe me-1"></i> ES
                </div>

                <!-- Botón de Acceso Exclusivo al Portal de Clientes/Consultores -->
                <button type="button" class="btn-portal-client" id="btnOpenAuthModal" title="Acceso al Portal de Clientes y Consultores">
                    <i class="fa-regular fa-circle-user" style="font-size: 1.05rem; color: var(--ui-primary);"></i>
                    <span class="d-none d-sm-inline">Acceso al equipo</span>
                </button>

                <!-- Botón CTA Primario -->
                <a href="#contacto" class="btn-ui-accent">
                    <span>Hablemos</span>
                    <i class="fa-solid fa-arrow-right" style="font-size: 0.75rem;"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- ===================================================================== -->
    <!-- 2. ACTO 1: HERO & PROPUESTA DE VALOR CORPORATIVA                      -->
    <!-- ===================================================================== -->
    <section class="ui-hero-section" id="inicio">
        <div class="container-xl"><div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <div class="ui-hero-badge"><i class="fa-solid fa-sparkles text-success"></i><span>Ingenier&iacute;a de software &middot; IA aplicada &middot; Cloud y seguridad</span></div>
                <h1 class="ui-hero-title">Tecnolog&iacute;a que resuelve problemas reales.</h1>
                <p class="ui-hero-subtitle">Dise&ntilde;amos software a medida y soluciones de inteligencia artificial para ayudar a las organizaciones a trabajar mejor. Unimos ingenier&iacute;a, nube y seguridad desde el diagn&oacute;stico hasta la evoluci&oacute;n.</p>
                <div class="ui-hero-services" aria-label="Capacidades Devioz">
                    <span><i class="fa-solid fa-code"></i> Software a medida</span><span><i class="fa-solid fa-wand-magic-sparkles"></i> IA y automatizaci&oacute;n</span><span><i class="fa-solid fa-cloud"></i> Cloud, DevOps y seguridad</span>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 pt-2"><a href="#contacto" class="btn-ui-accent py-3 px-4"><span>Conversemos sobre tu proyecto</span><i class="fa-solid fa-arrow-right"></i></a><a href="#portafolio" class="btn-ui-outline py-2.5 px-3"><span>Explorar proyectos</span></a></div>
                <p class="ui-hero-note mt-4 mb-0"><i class="fa-solid fa-user-check text-success me-1"></i> Personas expertas, IA con supervisi&oacute;n y entregas con evidencia.</p>
            </div>
            <div class="col-lg-6"><div class="ui-hero-visual"><img src="<?= asset('images/landing-engineering.svg') ?>" alt="Ilustraci&oacute;n del proceso Devioz: producto, IA aplicada y verificaci&oacute;n de calidad" width="960" height="720" fetchpriority="high"></div></div>
        </div></div>
    </section>

    <!-- ===================================================================== -->
    <!-- 3. CAPACIDADES                                                       -->
    <!-- ===================================================================== -->
    <section class="ui-metrics-ribbon" aria-label="Capacidades de Devioz"><div class="container-xl"><div class="row g-3 align-items-center">
        <div class="col-6 col-md-3"><div class="ui-metric-card"><div class="ui-metric-num">01</div><div class="ui-metric-label">Software a medida</div></div></div>
        <div class="col-6 col-md-3"><div class="ui-metric-card"><div class="ui-metric-num">02</div><div class="ui-metric-label">Inteligencia artificial</div></div></div>
        <div class="col-6 col-md-3"><div class="ui-metric-card"><div class="ui-metric-num">03</div><div class="ui-metric-label">Cloud y DevOps</div></div></div>
        <div class="col-6 col-md-3"><div class="ui-metric-card"><div class="ui-metric-num">04</div><div class="ui-metric-label">Seguridad y calidad</div></div></div>
    </div></div></section>
    <!-- 4. ACTO 2: SERVICIOS TECNOLÓGICOS CORE                                -->
    <!-- ===================================================================== -->
    <section class="py-5" style="background:#FAFCFE" id="servicios"><div class="container-xl py-4">
        <div class="text-center"><span class="ui-section-tag bg-primary-subtle text-primary">Lo que hacemos</span><h2 class="ui-section-title">Capacidades para llevar ideas a producto</h2><p class="ui-section-desc">Acompa&ntilde;amos a los equipos a resolver necesidades concretas con tecnolog&iacute;a, colaboraci&oacute;n y decisiones transparentes.</p></div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><div class="ui-service-icon bg-primary-subtle text-primary"><i class="fa-solid fa-code"></i></div><h3 class="h5 fw-bold text-navy">Software a medida</h3><p class="text-secondary small">Aplicaciones web, plataformas internas e integraciones dise&ntilde;adas seg&uacute;n los procesos de cada organizaci&oacute;n.</p><a href="#contacto" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-bold mt-auto">Conversemos</a></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><div class="ui-service-icon bg-info-subtle text-info"><i class="fa-solid fa-brain"></i></div><h3 class="h5 fw-bold text-navy">IA y automatizaci&oacute;n</h3><p class="text-secondary small">Identificamos oportunidades para aplicar IA y automatizar tareas con controles, supervisi&oacute;n humana y objetivos claros.</p><a href="#contacto" class="btn btn-sm btn-outline-info rounded-pill w-100 fw-bold mt-auto">Conversemos</a></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><div class="ui-service-icon bg-warning-subtle text-warning"><i class="fa-solid fa-cloud"></i></div><h3 class="h5 fw-bold text-navy">Cloud y DevOps</h3><p class="text-secondary small">Apoyamos la arquitectura, los flujos de entrega y la operaci&oacute;n de soluciones en entornos cloud.</p><a href="#contacto" class="btn btn-sm btn-outline-warning rounded-pill w-100 fw-bold mt-auto">Conversemos</a></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><div class="ui-service-icon bg-success-subtle text-success"><i class="fa-solid fa-shield-halved"></i></div><h3 class="h5 fw-bold text-navy">Seguridad y calidad</h3><p class="text-secondary small">Integramos revisiones, pruebas y controles adecuados al riesgo y a los criterios de aceptaci&oacute;n acordados.</p><a href="#contacto" class="btn btn-sm btn-outline-success rounded-pill w-100 fw-bold mt-auto">Conversemos</a></article></div>
        </div>
    </div></section>
    <!-- 5. ACTO 3: METODOLOGÍA CRONOLÓGICA ("CÓMO LO HACEMOS Y TIEMPOS")       -->
    <!-- ===================================================================== -->
    <section class="py-5 bg-white" id="metodologia"><div class="container-xl py-4">
        <div class="text-center"><span class="ui-section-tag bg-success-subtle text-success">Un proceso colaborativo</span><h2 class="ui-section-title">De la idea a una soluci&oacute;n que evoluciona</h2><p class="ui-section-desc">Acordamos alcance, prioridades y entregables seg&uacute;n el contexto de cada proyecto. El equipo comparte avances y decisiones durante todo el proceso.</p></div>
        <div class="row g-4 mt-2">
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><span class="badge bg-primary-subtle text-primary mb-3">01 &middot; Alinear</span><h3 class="h5 fw-bold text-navy">Entender el problema</h3><p class="text-secondary small mb-0">Conversamos con las personas involucradas, identificamos necesidades, restricciones y criterios de &eacute;xito.</p></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><span class="badge bg-info-subtle text-info mb-3">02 &middot; Dise&ntilde;ar</span><h3 class="h5 fw-bold text-navy">Definir una soluci&oacute;n</h3><p class="text-secondary small mb-0">Priorizamos el alcance y acordamos arquitectura, experiencia, riesgos y plan de trabajo.</p></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><span class="badge bg-warning-subtle text-warning mb-3">03 &middot; Construir</span><h3 class="h5 fw-bold text-navy">Entregar por etapas</h3><p class="text-secondary small mb-0">Construimos incrementos revisables, compartimos demostraciones y ajustamos con retroalimentaci&oacute;n.</p></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-service-card h-100"><span class="badge bg-success-subtle text-success mb-3">04 &middot; Evolucionar</span><h3 class="h5 fw-bold text-navy">Verificar y mejorar</h3><p class="text-secondary small mb-0">Validamos los criterios acordados, preparamos la puesta en marcha y planificamos el mantenimiento.</p></article></div>
        </div>
    </div></section>
    <!-- 6. ACTO 4: LOS 4 PILARES DE CALIDAD Y NORMATIVAS INTERNACIONALES      -->
    <!-- ===================================================================== -->
    <section class="py-5" style="background:#F8FAFC" id="calidad"><div class="container-xl py-4">
        <div class="text-center"><span class="ui-section-tag bg-info-subtle text-info">Calidad proporcional al riesgo</span><h2 class="ui-section-title">Calidad que se puede explicar y comprobar</h2><p class="ui-section-desc">Usamos modelos y buenas pr&aacute;cticas como referencia. Para cada iniciativa acordamos controles pertinentes y presentamos evidencias; esto no implica una certificaci&oacute;n externa.</p></div>
        <div class="row g-4 mt-2">
            <div class="col-md-6 col-lg-3"><article class="ui-pillar-card h-100"><h3 class="h5 fw-bold text-navy">Calidad del producto</h3><span class="badge bg-success-subtle text-success mb-3">ISO/IEC 25010 como referencia</span><p class="text-secondary small">Revisamos atributos relevantes como funcionalidad, rendimiento, usabilidad, fiabilidad, mantenibilidad y compatibilidad.</p><p class="small text-muted mb-0">Evidencia: criterios de aceptaci&oacute;n, revisiones y resultados de pruebas acordadas.</p></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-pillar-card h-100"><h3 class="h5 fw-bold text-navy">Seguridad desde el dise&ntilde;o</h3><span class="badge bg-primary-subtle text-primary mb-3">ISO/IEC 27001 como referencia</span><p class="text-secondary small">Identificamos activos y riesgos, y definimos controles de acceso, protecci&oacute;n de datos y registro seg&uacute;n el contexto.</p><p class="small text-muted mb-0">Evidencia: decisiones, riesgos y controles implementados en el alcance.</p></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-pillar-card h-100"><h3 class="h5 fw-bold text-navy">Riesgos de aplicaciones</h3><span class="badge bg-light text-dark mb-3">OWASP como gu&iacute;a</span><p class="text-secondary small">Consideramos riesgos comunes de aplicaciones web y seleccionamos revisiones o pruebas adecuadas al producto.</p><p class="small text-muted mb-0">Evidencia: hallazgos, prioridad, mitigaci&oacute;n y pendientes aceptados.</p></article></div>
            <div class="col-md-6 col-lg-3"><article class="ui-pillar-card h-100"><h3 class="h5 fw-bold text-navy">Pruebas y aceptaci&oacute;n</h3><span class="badge bg-warning-subtle text-warning mb-3">Plan seg&uacute;n alcance</span><p class="text-secondary small">Combinamos validaci&oacute;n funcional, pruebas automatizadas cuando corresponda y revisi&oacute;n con usuarios.</p><p class="small text-muted mb-0">Evidencia: casos ejecutados, resultados y dictamen de entrega.</p></article></div>
        </div>
    </div></section>
    <!-- 7. ACTO 5: DEVIOZ REELS — DEMOSTRACIONES TÉCNICAS 16:9 EN VIVO         -->
    <!-- ===================================================================== -->
    <section class="ui-reels-section" id="reels">
        <div class="container-xl">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
                <div>
                    <span class="badge bg-danger px-3 py-1.5 rounded-pill mb-2 text-uppercase fw-bold" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-video me-1"></i> Demostraciones de proyectos
                    </span>
                    <h2 class="display-6 fw-bold mb-1 text-white">Proyectos en acci&oacute;n</h2>
                    <p class="text-white-50 mb-0 small">Videos compartidos para mostrar avances y funcionalidades de proyectos.</p>
                </div>
                <div class="mt-3 mt-md-0 text-white-50 small">
                    <i class="fa-solid fa-circle-info text-info me-1"></i> Haz clic en cualquier reel para reproducir la demostración en pantalla completa sin salir de la página.
                </div>
            </div>

            <div class="row g-4">
                <?php if (!empty($reelProjects)): ?>
                    <?php
                    $reelIndex = 0;
                    foreach ($reelProjects as $reel):
                        $videoData = devioz_parse_video((string)$reel->video_url);
                        $reelCover = $videoData['thumb'] ?: $reel->coverUrl();
                        $dev = $reel->developer();
                        $leadName = $dev ? $dev->name : 'Tech Lead Devioz';
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="ui-reel-card js-reel-trigger"
                                 data-reel-index="<?= $reelIndex ?>"
                                 data-title="<?= e($reel->name) ?>"
                                 data-video-url="<?= e($videoData['embed']) ?>"
                                 data-lead="<?= e($leadName) ?>"

                                 data-client="<?= e($reel->clientName()) ?>">

                                <!-- Caja de Poster Vertical Bilibili -->
                                <div class="ui-reel-poster-box">
                                    <div class="ui-reel-bg" style="background-image: url('<?= e($reelCover) ?>');"></div>

                                    <!-- Badges en las esquinas -->
                                    <span class="bili-badge-pink">
                                        <i class="fa-regular fa-clock" style="font-size: 0.65rem;"></i> Free
                                    </span>
                                    <span class="bili-badge-glass">
                                        <i class="fa-solid fa-code me-1"></i><?= e($reel->code ?: 'DEV') ?>
                                    </span>

                                    <!-- Overlay Liquid Glass al pasar el ratón (Hover) -->
                                    <div class="ui-reel-hover-overlay">
                                        <div class="bili-overlay-meta">
                                            <span class="bili-tag-pill"><?= !empty($reel->tech_stack) ? e($reel->tech_stack[0]) : 'Software TI' ?></span>

                                        </div>
                                        <!-- Botón Azul de Play de Bilibili -->
                                        <button type="button" class="ui-reel-play-btn-blue">
                                            <i class="fa-solid fa-play"></i> Play
                                        </button>
                                    </div>
                                </div>

                                <!-- Textos e Información Debajo del Poster -->
                                <div class="bili-info-box">
                                    <h6 class="bili-card-title text-white" title="<?= e($reel->name) ?>" style="color: #FFFFFF !important; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.95) !important;">
                                        <?= e($reel->name) ?>
                                    </h6>
                                    <div class="bili-dev-line" style="color: #FFFFFF !important;">
                                        <img src="<?= e($dev ? $dev->avatarUrl() : '') ?>" alt="Dev" class="bili-dev-avatar" loading="lazy">
                                        <span class="text-truncate" style="color: rgba(255, 255, 255, 0.95) !important;"><?= e($leadName) ?></span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php
                        $reelIndex++;
                    endforeach;
                    ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <p class="text-white-50">Próximamente más demostraciones en Devioz Reels.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <!-- 8. ACTO 6: CASOS DE ÉXITO & PORTAFOLIO CERTIFICADO                   -->
    <!-- ===================================================================== -->
    <section class="py-5 bg-white" id="portafolio">
        <div class="container-xl py-4">
            <div class="text-center">
                <span class="ui-section-tag bg-primary-subtle text-primary">Proyectos publicados</span>
                <h2 class="ui-section-title">Proyectos seleccionados</h2>
                <p class="ui-section-desc">
                    Explora una selección de nuestras soluciones en producción. Cada proyecto cuenta con su respectiva ficha técnica, arquitectura auditada y conformidad ISO.
                </p>
            </div>

            <!-- Filtros por Categoría -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4" id="portfolioFilterContainer">
                <button type="button" class="ui-portfolio-filter-btn active" data-filter="all">Todos los Proyectos</button>
                <button type="button" class="ui-portfolio-filter-btn" data-filter="web">Web Enterprise</button>
                <button type="button" class="ui-portfolio-filter-btn" data-filter="cloud">Cloud & DevOps</button>
                <button type="button" class="ui-portfolio-filter-btn" data-filter="fintech">FinTech & Pagos</button>
                <button type="button" class="ui-portfolio-filter-btn" data-filter="mobile">Móvil & Logística</button>
            </div>

            <!-- Grid de Proyectos -->
            <div class="row g-4" id="portfolioProjectsGrid">
                <?php if (!empty($featuredProjects)): ?>
                    <?php foreach ($featuredProjects as $proj):
                        // Preparar categorías para filtrado
                        $cat = 'web';
                        $lowName = strtolower($proj->name . ' ' . $proj->description);
                        if (str_contains($lowName, 'cloud') || str_contains($lowName, 'iot') || str_contains($lowName, 'telemetría')) {
                            $cat = 'cloud';
                        } elseif (str_contains($lowName, 'factur') || str_contains($lowName, 'banc') || str_contains($lowName, 'pago') || str_contains($lowName, 'fintech')) {
                            $cat = 'fintech';
                        } elseif (str_contains($lowName, 'móvil') || str_contains($lowName, 'app') || str_contains($lowName, 'distribución')) {
                            $cat = 'mobile';
                        }

                        $dev = $proj->developer();
                        $leadDev = $dev ? $dev->name : 'Tech Lead Devioz';
                        $projCode = $proj->code ?: ('DEV-2026-' . sprintf('%03d', $proj->id));
                        $projScore = null;
                        $stack = is_array($proj->tech_stack) ? $proj->tech_stack : (json_decode((string)$proj->tech_stack, true) ?: ['PHP 8.2', 'MySQL', 'Docker', 'Bootstrap 5']);

                        // Datos para el modal público
                        $projectDataJson = htmlspecialchars(json_encode([
                            'id'          => $proj->id,
                            'code'        => $projCode,
                            'name'        => $proj->name,
                            'client'      => $proj->clientName(),
                            'status'      => $proj->statusLabel(),
                            'category'    => $proj->categoryLabel(),
                            'score'       => $projScore,
                            'cover'       => $proj->coverUrl(),
                            'lead'        => $leadDev,
                            'description' => $proj->description ?: 'Conoce el alcance y las capacidades de este proyecto.',
                            'techStack'   => $stack,
                            'progress'    => $proj->progressPercentage(),
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="col-md-6 col-lg-4 js-portfolio-item" data-category="<?= $cat ?>">
                            <div class="ui-project-card">

                                <div class="ui-project-cover">
                                    <img src="<?= e($proj->coverUrl()) ?>" alt="<?= e($proj->name) ?>" loading="lazy">



                                    <div class="position-absolute bottom-0 start-0 m-2">
                                        <span class="badge bg-success text-white text-nowrap"><?= e($proj->statusLabel()) ?></span>
                                    </div>
                                </div>

                                <div class="p-4 d-flex flex-column justify-content-between flex-grow-1">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="font-mono text-muted small"><?= e($projCode) ?></span>
                                            <span class="badge bg-light text-secondary border small"><?= e($proj->categoryLabel()) ?></span>
                                        </div>

                                        <h5 class="fw-bold text-navy mb-2 text-truncate" title="<?= e($proj->name) ?>">
                                            <?= e($proj->name) ?>
                                        </h5>

                                        <p class="text-secondary small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?= e($proj->description ?: 'Solución tecnológica de alto impacto implementada para optimización de procesos de negocio.') ?>
                                        </p>

                                        <!-- Tech Stack Chips -->
                                        <div class="d-flex flex-wrap gap-1 mb-3">
                                            <?php foreach (array_slice($stack, 0, 3) as $tech): ?>
                                                <span class="ui-tech-tag"><?= e($tech) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($stack) > 3): ?>
                                                <span class="ui-tech-tag">+<?= count($stack) - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <span class="text-muted small">
                                                <i class="fa-solid fa-building me-1 text-primary"></i> <?= e($proj->clientName()) ?>
                                            </span>
                                            <!-- Botón Autónomo para abrir la Ficha Técnica (NO navega al sistema interno) -->
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 js-open-dossier" data-project='<?= $projectDataJson ?>'>
                                                <span>Ficha Técnica</span>
                                                <i class="fa-solid fa-chevron-right ms-1" style="font-size: 0.68rem;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h5 class="text-muted">No se registran proyectos en este momento.</h5>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===================================================================== -->
    <section class="py-5" style="background:#FAFCFE"><div class="container-xl py-4">
        <div class="text-center"><span class="ui-section-tag bg-success-subtle text-success">Nuestra forma de colaborar</span><h2 class="ui-section-title">Una relaci&oacute;n de trabajo clara y cercana</h2><p class="ui-section-desc">La confianza se construye compartiendo contexto, decisiones y avances durante el proyecto.</p></div>
        <div class="row g-4 mt-2">
            <div class="col-md-4"><article class="ui-pillar-card h-100"><div class="ui-service-icon bg-primary-subtle text-primary"><i class="fa-solid fa-comments"></i></div><h3 class="h5 fw-bold text-navy">Comunicaci&oacute;n abierta</h3><p class="text-secondary small mb-0">Acordamos objetivos y mantenemos visibles el progreso, los riesgos y las decisiones pendientes.</p></article></div>
            <div class="col-md-4"><article class="ui-pillar-card h-100"><div class="ui-service-icon bg-success-subtle text-success"><i class="fa-solid fa-user-check"></i></div><h3 class="h5 fw-bold text-navy">IA con criterio humano</h3><p class="text-secondary small mb-0">La IA puede acelerar tareas, mientras las personas revisan resultados y conservan la responsabilidad de las decisiones.</p></article></div>
            <div class="col-md-4"><article class="ui-pillar-card h-100"><div class="ui-service-icon bg-warning-subtle text-warning"><i class="fa-solid fa-clipboard-check"></i></div><h3 class="h5 fw-bold text-navy">Calidad con evidencia</h3><p class="text-secondary small mb-0">Relacionamos cada entrega con el alcance acordado, sus criterios de aceptaci&oacute;n y las verificaciones realizadas.</p></article></div>
        </div>
    </div></section>
    <!-- 10. ACTO 8: CONTACTO DIRECTO & SOLICITUD DE PROPUESTA TÉCNICA         -->
    <!-- ===================================================================== -->
    <section class="py-5" style="background:linear-gradient(135deg,#172334 0%,#0F172A 100%);color:#fff" id="contacto"><div class="container-xl py-4"><div class="row g-5 align-items-center">
        <div class="col-lg-7"><span class="badge bg-success-subtle text-success fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">Hablemos de tu proyecto</span><h2 class="display-6 fw-bold mb-3 text-white">Construyamos algo que le sirva a tu equipo.</h2><p class="text-light opacity-75 mb-0" style="line-height:1.7">Cu&eacute;ntanos qu&eacute; quieres mejorar, qu&eacute; obst&aacute;culos enfrenta tu organizaci&oacute;n y qu&eacute; resultado esperas. Podemos explorar juntos el siguiente paso.</p></div>
        <div class="col-lg-5"><div class="bg-white rounded-4 p-4 p-md-5 text-dark shadow-lg"><div class="ui-service-icon bg-success-subtle text-success mb-3"><i class="fa-brands fa-whatsapp"></i></div><h3 class="h4 fw-bold text-navy">Inicia una conversaci&oacute;n</h3><p class="text-secondary">Escr&iacute;benos y coordinaremos una primera conversaci&oacute;n sobre el contexto y alcance.</p><a class="btn-ui-accent w-100 justify-content-center py-3" href="https://wa.me/51969072449" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i><span>Conversar por WhatsApp</span></a><p class="small text-muted mt-3 mb-0">El canal de contacto se abre en una nueva pesta&ntilde;a.</p></div></div>
    </div></div></section>
    <!-- 11. MEGA FOOTER INSTITUCIONAL (AUTÓNOMO)                              -->
    <!-- ===================================================================== -->
    <footer class="ui-footer"><div class="container-xl py-4"><div class="row g-4 align-items-start">
        <div class="col-lg-5"><div class="d-flex align-items-center gap-2 mb-3"><img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz" width="30" height="30" class="rounded"><span class="fw-bold fs-5 text-white">Devioz</span></div><p class="text-secondary small mb-0">Consultor&iacute;a tecnol&oacute;gica, desarrollo de software e inteligencia artificial aplicada.</p></div>
        <div class="col-lg-4"><h2 class="ui-footer-title">Explora</h2><div class="d-flex flex-wrap gap-3"><a href="#servicios">Capacidades</a><a href="#metodologia">Proceso</a><a href="#calidad">Calidad</a><a href="#portafolio">Proyectos</a></div></div>
        <div class="col-lg-3"><h2 class="ui-footer-title">Enlaces</h2><div class="d-flex flex-wrap gap-3"><a href="https://pe.linkedin.com/company/devioz-ti" target="_blank" rel="noopener noreferrer">LinkedIn</a><a href="https://www.devioz.com/" target="_blank" rel="noopener noreferrer">Sitio Devioz</a><button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btnFooterOpenAuthModal">Acceso al equipo</button></div></div>
    </div><div class="pt-4 mt-4 border-top border-secondary border-opacity-25 text-muted small">&copy; <?= date('Y') ?> Devioz</div></div></footer>
    <!-- ===================================================================== -->
    <!-- 12. MODAL AUTÓNOMO: REPRODUCTOR DE REELS (16:9 LIGHTBOX)             -->
    <!-- ===================================================================== -->
    <div class="ui-modal-overlay" id="reelPlayerModal" role="dialog" aria-modal="true" aria-labelledby="reelModalTitle">
        <div class="ui-reel-dialog">

            <!-- Botón de Cierre del Reel -->
            <button type="button" class="ui-close-reel-btn" id="btnCloseReelModal" aria-label="Cerrar reproductor">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Caja de Reproducción (Vertical 16:9) -->
            <div class="ui-reel-player-box">
                <iframe id="reelPlayerIframe"
                        src=""
                        title="Devioz Reel Demo"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
            </div>

            <!-- Información del Proyecto en el Reel -->
            <div class="p-3 bg-dark text-white border-top border-secondary border-opacity-25">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-primary" id="reelModalClient">Cliente Corporativo</span>
                    <span class="badge bg-secondary text-white font-mono fw-bold d-none" id="reelModalScore"></span>
                </div>
                <h6 class="fw-bold mb-1 text-white text-truncate" id="reelModalTitle">Demostración Técnica Devioz</h6>
                <div class="text-white-50 small d-flex justify-content-between align-items-center">
                    <span id="reelModalLead"><i class="fa-solid fa-user-tie text-info me-1"></i> Tech Lead Devioz</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnPrevReel" title="Reel Anterior">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light py-0 px-2" id="btnNextReel" title="Siguiente Reel">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 13. MODAL AUTÓNOMO: FICHA TÉCNICA DEL PROYECTO (PUBLIC DOSSIER)      -->
    <!-- ===================================================================== -->
    <div class="ui-modal-overlay" id="publicProjectModal" role="dialog" aria-modal="true" aria-labelledby="dossierModalTitle">
        <div class="ui-dossier-dialog">

            <div class="d-flex justify-content-between align-items-center p-3 p-md-4 border-bottom bg-light">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary font-mono" id="dossierCode">DEV-2026-001</span>
                    <h5 class="fw-bold text-navy mb-0" id="dossierModalTitle">Ficha Técnica de Proyecto</h5>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" id="btnCloseDossierModal" style="width: 34px; height: 34px;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-3 p-md-4" style="max-height: 75vh; overflow-y: auto;">
                <div class="row g-4">

                    <!-- Columna Izquierda: Portada y Metadata -->
                    <div class="col-md-5">
                        <div class="rounded-3 overflow-hidden border mb-3" style="height: 180px;">
                            <img src="" alt="Portada" id="dossierCoverImg" class="w-100 h-100 object-fit-cover">
                        </div>

                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="small text-muted mb-1">EMPRESA / CLIENTE:</div>
                            <div class="fw-bold text-navy mb-2" id="dossierClient">Cliente Corporativo</div>

                            <div class="small text-muted mb-1">TECH LEAD ASIGNADO:</div>
                            <div class="fw-bold text-navy mb-2" id="dossierLead">Ingeniero Senior Devioz</div>

                            <div class="small text-muted mb-1">ESTADO DEL PROYECTO:</div>
                            <div id="dossierStatusBadge"><span class="badge bg-success">En Producción</span></div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border mb-3"><div class="small fw-bold text-navy mb-1">Informaci&oacute;n del proyecto</div><p class="small text-muted mb-0">La ficha resume los datos y tecnolog&iacute;as registrados para este proyecto.</p></div>
                    </div>
                    <!-- Columna Derecha: Descripción, Stack y Métricas -->
                    <div class="col-md-7">
                        <h6 class="fw-bold text-navy mb-2"><i class="fa-solid fa-align-left text-primary me-2"></i>Objetivo y Descripción de la Solución</h6>
                        <p class="text-secondary small mb-4" id="dossierDescription" style="line-height: 1.7;">
                            Descripción detallada del proyecto empresarial...
                        </p>

                        <h6 class="fw-bold text-navy mb-2"><i class="fa-solid fa-layer-group text-primary me-2"></i>Stack Tecnológico Implementado</h6>
                        <div class="d-flex flex-wrap gap-1.5 mb-4" id="dossierTechStackContainer">
                            <!-- Inyectado dinámicamente -->
                        </div>

                        <div class="pt-3 border-top d-flex gap-2">
                            <a href="#contacto" class="btn btn-primary rounded-pill w-100 fw-bold py-2 js-quote-similar-btn" style="background: var(--ui-primary); border-color: var(--ui-primary);">
                                <i class="fa-solid fa-paper-plane me-1"></i> Cotizar Solución Similar
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 14. MODAL AUTÓNOMO: ACCESO AL PORTAL DE CLIENTES Y CONSULTORES        -->
    <!-- ===================================================================== -->
    <div class="ui-modal-overlay" id="portalAuthModal" role="dialog" aria-modal="true" aria-labelledby="modalAuthTitle">
        <div class="ui-modal-card" style="background: #fff; border-radius: 18px; width: 100%; max-width: 440px; box-shadow: 0 25px 60px rgba(0,0,0,0.3); overflow: hidden; position: relative;">

            <div class="ui-modal-header p-3 px-4 bg-light border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?= asset('images/devioz-icon.png') ?>" alt="Devioz OS" width="24" height="24" class="rounded">
                    <h6 class="fw-bold mb-0 text-navy" id="modalAuthTitle">Acceso al equipo Devioz</h6>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" id="btnCloseAuthModal" style="width: 30px; height: 30px;" aria-label="Cerrar ventana">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-4">
                <div class="alert alert-info py-2 px-3 small mb-3 border-0" style="background: #ECFDF5; color: #047857;">
                    <i class="fa-solid fa-lock me-1"></i> Espacio reservado para empresas con proyectos activos y consultores autorizados.
                </div>

                <form action="<?= url('/login') ?>" method="POST" id="portalLoginForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="authModalEmail" class="form-label small fw-bold text-secondary">Correo Profesional / Corporativo</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control border-start-0" id="authModalEmail" name="email" placeholder="usuario@empresa.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="authModalPassword" class="form-label small fw-bold text-secondary mb-0">Contraseña</label>
                            <a href="<?= url('/login') ?>" class="small text-decoration-none text-muted">¿Olvidó su clave?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" class="form-control border-start-0" id="authModalPassword" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="authModalRemember" checked>
                        <label class="form-check-label small text-muted" for="authModalRemember">
                            Mantener sesión activa en este equipo
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold rounded-3 shadow-sm mb-3" style="background: var(--ui-primary); border-color: var(--ui-primary);">
                        <span>Ingresar</span>
                        <i class="fa-solid fa-right-to-bracket ms-1"></i>
                    </button>
                </form>

                <!-- Métodos Alternativos OAuth -->
                <div class="pt-3 border-top">
                    <div class="position-relative my-2 text-center">
                        <hr class="text-muted opacity-25 m-0">
                        <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted small fw-semibold" style="font-size: 0.72rem; text-transform: uppercase;">
                            O ingresar con
                        </span>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-6">
                            <a href="<?= url('/auth/google') ?>" class="btn btn-sm btn-outline-secondary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center" title="Iniciar sesión con Google Workspace">
                                <svg width="16" height="16" viewBox="0 0 24 24" class="me-2" aria-hidden="true">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                                </svg>
                                <span>Google</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?= url('/auth/github') ?>" class="btn btn-sm btn-outline-secondary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center" title="Iniciar sesión con GitHub Enterprise">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="me-2 text-dark" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                </svg>
                                <span>GitHub</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3 pt-2">
                    <span class="small text-muted">¿Deseas solicitar una cuenta corporativa?</span>
                    <a href="#contacto" class="small fw-bold text-primary text-decoration-none ms-1" onclick="closeAuthModal();">Contáctanos</a>
                </div>
            </div>

        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- 15. SCRIPTS VANILLA JS AUTÓNOMOS (CERO FUGAS DE NAVEGACIÓN)           -->
    <!-- ===================================================================== -->
    <script>
        (function() {
            'use strict';

            // 1. Efecto Scroll en Barra Superior
            const header = document.getElementById('mainHeader');
            if (header) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 30) {
                        header.classList.add('scrolled');
                    } else {
                        header.classList.remove('scrolled');
                    }
                }, { passive: true });
            }

            // 2. Control de Modales Autónomos (Cierre y Apertura)
            const reelModal = document.getElementById('reelPlayerModal');
            const reelIframe = document.getElementById('reelPlayerIframe');
            const reelTitle = document.getElementById('reelModalTitle');
            const reelClient = document.getElementById('reelModalClient');
                        const reelLead = document.getElementById('reelModalLead');
            const btnCloseReel = document.getElementById('btnCloseReelModal');
            const btnPrevReel = document.getElementById('btnPrevReel');
            const btnNextReel = document.getElementById('btnNextReel');

            // Lista de todos los reels en la página para navegación interactiva
            const reelCards = Array.from(document.querySelectorAll('.js-reel-trigger'));
            let currentReelIndex = 0;

            function openReelAtIndex(index) {
                if (index < 0) index = reelCards.length - 1;
                if (index >= reelCards.length) index = 0;
                currentReelIndex = index;

                const card = reelCards[currentReelIndex];
                if (!card || !reelModal) return;

                const videoUrl = card.getAttribute('data-video-url') || '';
                const title = card.getAttribute('data-title') || 'Demostración Técnica';
                const client = card.getAttribute('data-client') || 'Cliente Corporativo';
                                const lead = card.getAttribute('data-lead') || 'Tech Lead Devioz';

                if (reelTitle) reelTitle.textContent = title;
                if (reelClient) reelClient.textContent = client;
                                if (reelLead) reelLead.innerHTML = '<i class="fa-solid fa-user-tie text-info me-1"></i> ' + lead;
                if (reelIframe) reelIframe.src = videoUrl;

                reelModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeReel() {
                if (reelModal) {
                    reelModal.classList.remove('active');
                    document.body.style.overflow = '';
                    if (reelIframe) {
                        reelIframe.src = ''; // Detiene inmediatamente la reproducción de audio/video
                    }
                }
            }

            reelCards.forEach((card, idx) => {
                card.addEventListener('click', () => openReelAtIndex(idx));
            });

            if (btnCloseReel) btnCloseReel.addEventListener('click', closeReel);
            if (btnPrevReel) btnPrevReel.addEventListener('click', () => openReelAtIndex(currentReelIndex - 1));
            if (btnNextReel) btnNextReel.addEventListener('click', () => openReelAtIndex(currentReelIndex + 1));

            // 3. Control de Ficha Técnica del Proyecto (Dossier Modal)
            const dossierModal = document.getElementById('publicProjectModal');
            const btnCloseDossier = document.getElementById('btnCloseDossierModal');
            const dossierBtns = document.querySelectorAll('.js-open-dossier');

            function openDossier(data) {
                if (!dossierModal || !data) return;

                const codeEl = document.getElementById('dossierCode');
                const titleEl = document.getElementById('dossierModalTitle');
                const coverEl = document.getElementById('dossierCoverImg');
                const clientEl = document.getElementById('dossierClient');
                const leadEl = document.getElementById('dossierLead');
                const descEl = document.getElementById('dossierDescription');
                const stackContainer = document.getElementById('dossierTechStackContainer');
                const statusBadgeEl = document.getElementById('dossierStatusBadge');

                if (codeEl) codeEl.textContent = data.code || 'DEV-2026';
                if (titleEl) titleEl.textContent = data.name || 'Ficha Técnica';
                if (coverEl) coverEl.src = data.cover || '';
                if (clientEl) clientEl.textContent = data.client || 'Cliente Corporativo';
                if (leadEl) leadEl.textContent = data.lead || 'Tech Lead Devioz';
                if (descEl) descEl.textContent = data.description || '';
                if (statusBadgeEl) statusBadgeEl.innerHTML = '<span class="badge bg-success text-white">' + (data.status || 'En Producción') + '</span>';

                if (stackContainer) {
                    stackContainer.innerHTML = '';
                    const stack = Array.isArray(data.techStack) ? data.techStack : [];
                    stack.forEach(tech => {
                        const span = document.createElement('span');
                        span.className = 'ui-tech-tag';
                        span.textContent = tech;
                        stackContainer.appendChild(span);
                    });
                }

                // Ajustar botón "Cotizar Solución Similar"
                const quoteBtn = dossierModal.querySelector('.js-quote-similar-btn');
                if (quoteBtn) {
                    quoteBtn.onclick = function() {
                        closeDossier();
                        const message = 'Hola Devioz, me interesa conversar sobre una solucion similar a ' + (data.name || 'este proyecto') + ' (' + (data.code || '') + ').';
                        window.open('https://wa.me/51969072449?text=' + encodeURIComponent(message), '_blank', 'noopener,noreferrer');
                    };
                }

                dossierModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeDossier() {
                if (dossierModal) {
                    dossierModal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }

            dossierBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    try {
                        const data = JSON.parse(btn.getAttribute('data-project') || '{}');
                        openDossier(data);
                    } catch (err) {
                        console.error('Error parseando datos del proyecto:', err);
                    }
                });
            });

            if (btnCloseDossier) btnCloseDossier.addEventListener('click', closeDossier);

            // 4. Control de Modal de Autenticación de Clientes/Consultores
            const authModal = document.getElementById('portalAuthModal');
            const openAuthBtns = [
                document.getElementById('btnOpenAuthModal'),
                document.getElementById('btnFooterOpenAuthModal')
            ];
            const closeAuthBtn = document.getElementById('btnCloseAuthModal');

            window.closeAuthModal = function() {
                if (authModal) {
                    authModal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            };

            function openAuth() {
                if (authModal) {
                    authModal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    const emailInput = document.getElementById('authModalEmail');
                    if (emailInput) setTimeout(() => emailInput.focus(), 150);
                }
            }

            openAuthBtns.forEach(btn => {
                if (btn) btn.addEventListener('click', openAuth);
            });

            if (closeAuthBtn) closeAuthBtn.addEventListener('click', window.closeAuthModal);

            // Cierre general por clic en backdrop oscuro
            [reelModal, dossierModal, authModal].forEach(modal => {
                if (!modal) return;
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeReel();
                        closeDossier();
                        window.closeAuthModal();
                    }
                });
            });

            // Cierre con tecla Escape
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeReel();
                    closeDossier();
                    window.closeAuthModal();
                }
            });

            // 5. Filtrado Dinámico de Proyectos en Portafolio
            const filterBtns = document.querySelectorAll('.ui-portfolio-filter-btn');
            const projectCards = document.querySelectorAll('.js-portfolio-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    const filterValue = btn.getAttribute('data-filter') || 'all';

                    projectCards.forEach(card => {
                        const cardCategory = card.getAttribute('data-category') || '';
                        if (filterValue === 'all' || cardCategory === filterValue) {
                            card.style.display = 'block';
                            card.style.animation = 'fadeInModal 0.3s ease';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });

        })();
    </script>
</body>
</html>
