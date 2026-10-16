<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CheckIn;
use App\Models\Project;
use App\Models\User;

/**
 * Devioz OS — Controlador de Portada y Experiencias Inmersivas
 * Gestiona la Landing Page institucional y el feed de video Devioz Reels (TikTok-style)
 */
class HomeController extends BaseController
{
    /**
     * Landing Page de Alto Impacto
     */
    public function index(): string
    {
        $stats = [
            'total_projects'       => Project::count(),
            'approved_projects'    => Project::count("status = 'approved' OR status = 'completed'"),
            'in_review_projects'   => Project::count("status = 'in_review' OR status = 'pending_review'"),
            'total_developers'     => User::count("role = 'developer' AND active = 1"),
            'total_checkins'       => CheckIn::count(),
            'sedes_count'          => 3,
            'uptime_sla'           => '99.9%',
            'qa_avg_score'         => '9.6',
            'zero_vulnerabilities' => '0 Críticas',
        ];

        // Proyectos destacados para el showcase corporativo (ordenados por calidad y relevancia)
        $featuredProjects = Project::whereRaw('1=1', [], 'final_score DESC, views_count DESC, id DESC', 9);

        // Proyectos con demostración de video para la sección inmersiva de Reels
        $reelProjects = Project::whereRaw(
            "video_url IS NOT NULL AND TRIM(video_url) != '' AND is_public_in_gallery = 1 AND status IN ('approved', 'completed') AND deleted_at IS NULL",
            [],
            'final_score DESC, id DESC',
            8
        );

        return $this->view('home/index', [
            'title'            => 'Devioz Technology Solutions — Fábrica de Software, Cloud & Aseguramiento de Calidad',
            'stats'            => $stats,
            'featuredProjects' => $featuredProjects,
            'reelProjects'     => $reelProjects,
        ], null); // Renderiza como landing autónoma de alto impacto con estilos integrados
    }

    /**
     * Devioz Reels (Shorts) — Feed vertical snap-scrolling estilo TikTok
     */
    public function reels(): string
    {
        // Buscar proyectos con video disponible
        $reels = Project::whereRaw(
            "video_url IS NOT NULL AND TRIM(video_url) != '' AND is_public_in_gallery = 1 AND status IN ('approved', 'completed') AND deleted_at IS NULL",
            [],
            'updated_at DESC',
            50
        );

        return $this->view('gallery/reels', [
            'title' => 'Devioz Reels — Demostraciones Técnicas 16:9',
            'reels' => $reels,
        ], 'app');
    }
}
