<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Project;

/** Landing publica y experiencias de demostracion de Devioz. */
class HomeController extends BaseController
{
    public function index(): string
    {
        $featuredProjects = Project::whereRaw(
            "is_public_in_gallery = 1 AND status IN ('approved', 'completed') AND deleted_at IS NULL",
            [],
            'final_score DESC, views_count DESC, id DESC',
            9
        );

        $reelProjects = Project::whereRaw(
            "video_url IS NOT NULL AND TRIM(video_url) != '' AND is_public_in_gallery = 1 AND status IN ('approved', 'completed') AND deleted_at IS NULL",
            [],
            'final_score DESC, id DESC',
            8
        );

        return $this->view('home/index', [
            'title' => 'Devioz | Software, IA aplicada y servicios tecnologicos',
            'featuredProjects' => $featuredProjects,
            'reelProjects' => $reelProjects,
        ], null);
    }

    public function reels(): string
    {
        $reels = Project::whereRaw(
            "video_url IS NOT NULL AND TRIM(video_url) != '' AND is_public_in_gallery = 1 AND status IN ('approved', 'completed') AND deleted_at IS NULL",
            [],
            'updated_at DESC',
            50
        );

        return $this->view('gallery/reels', [
            'title' => 'Devioz Reels | Demostraciones tecnicas',
            'reels' => $reels,
        ], 'app');
    }
}