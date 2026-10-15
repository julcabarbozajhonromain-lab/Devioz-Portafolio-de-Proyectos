<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Session;
use App\Core\Validator;
use App\Models\AuditLog;
use App\Models\User;

/**
 * Devioz OS — Controlador de Autenticación y Registro
 */
class AuthController extends BaseController
{
    public function showLogin(): string
    {
        if ($this->auth->check()) {
            redirect('/dashboard');
        }

        return $this->view('auth/login', [
            'title' => 'Iniciar Sesión — Devioz OS',
        ], 'auth');
    }

    public function login(): void
    {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $remember = isset($_POST['remember']);

        $validator = $this->validate($_POST, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            redirect('/login');
        }

        if ($this->auth->attempt($email, $password, $remember)) {
            $user = $this->user();
            Session::flash('success', "Bienvenido de nuevo, {$user->name}.");
            redirect('/dashboard');
        }

        // Por seguridad, mensaje genérico para evitar enumeración de usuarios
        Session::flash('error', 'Credenciales incorrectas. Verifica tu correo y contraseña.');
        Session::flash('old_email', $email);
        redirect('/login');
    }

    public function showRegister(): string
    {
        if ($this->auth->check()) {
            redirect('/dashboard');
        }

        return $this->view('auth/register', [
            'title' => 'Registro de Desarrollador — Devioz OS',
        ], 'auth');
    }

    public function register(): void
    {
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirmation = (string)($_POST['password_confirmation'] ?? '');

        $validator = $this->validate($_POST, [
            'name'     => 'required|min:3|max:255',
            'email'    => 'required|email|unique:users,email',
            'sede'     => 'required|in:Cercado,Lima Norte,Villa',
            'password' => 'required|min:8',
        ]);

        if ($password !== $passwordConfirmation) {
            Session::flash('error', 'Las contraseñas ingresadas no coinciden.');
            Session::flash('old_name', (string)($_POST['name'] ?? ''));
            Session::flash('old_email', (string)($_POST['email'] ?? ''));
            Session::flash('old_sede', (string)($_POST['sede'] ?? ''));
            redirect('/register');
        }

        if ($validator->fails()) {
            redirect('/register');
        }

        $user = User::create([
            'name'     => trim((string)$_POST['name']),
            'email'    => strtolower(trim((string)$_POST['email'])),
            'sede'     => (string)$_POST['sede'],
            'role'     => 'developer',
            'active'   => 1,
            'password' => Auth::hashPassword($password),
        ]);

        AuditLog::record('user.registered', 'User', (int)$user->id, [
            'name'  => $user->name,
            'email' => $user->email,
            'sede'  => $user->sede,
        ], (int)$user->id);

        // Iniciar sesión automáticamente tras el registro
        $this->auth->attempt($user->email, $password);

        Session::flash('success', 'Tu cuenta de desarrollador ha sido creada con éxito.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->auth->logout();
        Session::flash('info', 'Has cerrado sesión satisfactoriamente.');
        redirect('/');
    }

    /**
     * Inicio de sesión OAuth con Google Workspace
     */
    public function oauthGoogle(): void
    {
        $clientId = config('services.google.client_id', getenv('GOOGLE_CLIENT_ID') ?: null);
        if (empty($clientId)) {
            Session::flash('info', 'El acceso con Google Workspace está preparado para producción. Para habilitarlo en vivo, configure las credenciales GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en su entorno.');
            redirect('/login');
        }
        
        // Redirección OAuth estándar de Google
        $redirectUri = url('/auth/google/callback');
        $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
            'client_id'     => $clientId,
            'redirect_uri'  => $redirectUri,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
        ]);
        redirect($authUrl);
    }

    /**
     * Inicio de sesión OAuth con GitHub Enterprise
     */
    public function oauthGithub(): void
    {
        $clientId = config('services.github.client_id', getenv('GITHUB_CLIENT_ID') ?: null);
        if (empty($clientId)) {
            Session::flash('info', 'El acceso con GitHub Enterprise está preparado para producción. Para habilitarlo en vivo, configure las credenciales GITHUB_CLIENT_ID y GITHUB_CLIENT_SECRET en su entorno.');
            redirect('/login');
        }

        // Redirección OAuth estándar de GitHub
        $redirectUri = url('/auth/github/callback');
        $authUrl = "https://github.com/login/oauth/authorize?" . http_build_query([
            'client_id'    => $clientId,
            'redirect_uri' => $redirectUri,
            'scope'        => 'user:email read:user',
        ]);
        redirect($authUrl);
    }
}
