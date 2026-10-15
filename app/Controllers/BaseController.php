<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Core\View;
use App\Models\User;

/**
 * Devioz OS — Controlador Base
 */
abstract class BaseController
{
    protected Auth $auth;

    public function __construct()
    {
        $this->auth = Auth::getInstance();
    }

    /**
     * Renderiza una vista dentro del layout estándar.
     */
    protected function view(string $view, array $data = [], ?string $layout = 'app'): string
    {
        // Compartir usuario actual y rol con todas las vistas
        $globalData = [
            'currentUser' => $this->user(),
            'isAdmin'     => $this->auth->isAdmin(),
            'isDeveloper' => $this->auth->isDeveloper(),
        ];

        return View::render($view, array_merge($globalData, $data), $layout);
    }

    /**
     * Emite una respuesta en formato JSON.
     */
    protected function json(mixed $data, int $status = 200): never
    {
        json_response($data, $status);
    }

    /**
     * Ejecuta validación y persiste errores si falla.
     */
    protected function validate(array $data, array $rules): Validator
    {
        $validator = Validator::validate($data, $rules);
        if ($validator->fails()) {
            $validator->flashErrorsAndInput();
        }
        return $validator;
    }

    protected function user(): ?User
    {
        return $this->auth->user();
    }
}
