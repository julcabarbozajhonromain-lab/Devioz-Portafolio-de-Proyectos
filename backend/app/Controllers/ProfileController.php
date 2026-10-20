<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\Uploader;
use App\Models\AuditLog;
use App\Models\User;

/**
 * Devioz OS — Controlador de Perfil y Configuración de Cuenta
 * Permite la actualización de datos personales, modificación de contraseña y subida de avatar.
 */
class ProfileController extends BaseController
{
    public function index(): string
    {
        $user = $this->user();
        if (!$user) {
            redirect('/login');
        }

        return $this->view('profile/index', [
            'title' => 'Configuración de Cuenta y Perfil — Devioz OS',
            'user'  => $user,
        ]);
    }

    public function update(): void
    {
        $user = $this->user();
        if (!$user) {
            redirect('/login');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $sede = trim((string)($_POST['sede'] ?? ''));
        $headline = trim((string)($_POST['headline'] ?? ''));
        $bio = trim((string)($_POST['bio'] ?? ''));

        $validator = $this->validate($_POST, [
            'name'  => 'required|min:3',
            'email' => 'required|email',
            'sede'  => 'required|in:Cercado,Lima Norte,Villa',
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'Por favor selecciona obligatoriamente tu Sede Académica (Cercado, Lima Norte o Villa).');
            redirect('/profile');
        }

        // Validar unicidad del correo electrónico si cambió
        if ($email !== $user->email) {
            $existing = User::findBy('email', $email);
            if ($existing && (int)$existing->id !== (int)$user->id) {
                Session::flash('error', 'El correo electrónico ingresado ya está registrado por otra cuenta.');
                redirect('/profile');
            }
        }

        // Sedes corporativas autorizadas
        $validSedes = ['Cercado', 'Lima Norte', 'Villa'];
        if (!in_array($sede, $validSedes, true)) {
            Session::flash('error', 'La sede académica seleccionada no es válida.');
            redirect('/profile');
        }

        $user->update([
            'name'  => $name,
            'email' => $email,
            'sede'  => $sede,
            'bio'   => $bio ?: null,
        ]);

        AuditLog::record('profile_updated', 'User', (int)$user->id, [
            'name'  => $name,
            'email' => $email,
            'sede'  => $sede,
        ]);

        Session::flash('success', 'Tu información personal ha sido actualizada correctamente.');
        redirect('/profile');
    }

    public function updatePassword(): void
    {
        $user = $this->user();
        if (!$user) {
            redirect('/login');
        }

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $newPasswordConfirmation = (string)($_POST['new_password_confirmation'] ?? '');

        if (!password_verify($currentPassword, (string)$user->password)) {
            Session::flash('error', 'La contraseña actual ingresada es incorrecta.');
            redirect('/profile#seguridad');
        }

        if (strlen($newPassword) < 6) {
            Session::flash('error', 'La nueva contraseña debe contener al menos 6 caracteres.');
            redirect('/profile#seguridad');
        }

        if ($newPassword !== $newPasswordConfirmation) {
            Session::flash('error', 'La confirmación de la nueva contraseña no coincide.');
            redirect('/profile#seguridad');
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->update([
            'password' => $hashed,
        ]);

        AuditLog::record('password_changed', 'User', (int)$user->id);
        Session::flash('success', '¡Tu contraseña ha sido modificada con éxito!');
        redirect('/profile#seguridad');
    }

    public function updateAvatar(): void
    {
        $user = $this->user();
        if (!$user) {
            redirect('/login');
        }

        if (empty($_FILES['avatar']['name'])) {
            Session::flash('error', 'Por favor selecciona una imagen para tu foto de perfil.');
            redirect('/profile');
        }

        try {
            $result = Uploader::upload($_FILES['avatar'], 'avatars', 5 * 1024 * 1024);

            // Eliminar imagen anterior si era un archivo subido en uploads/avatars/
            if (!empty($user->avatar) && str_starts_with($user->avatar, '/uploads/avatars/')) {
                $oldPath = DEVIOZ_PUBLIC . $user->avatar;
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $user->update([
                'avatar' => $result['path'],
            ]);

            AuditLog::record('avatar_updated', 'User', (int)$user->id, ['avatar' => $result['path']]);
            Session::flash('success', '¡Foto de perfil actualizada exitosamente!');
        } catch (\Throwable $e) {
            Session::flash('error', 'Error al procesar la imagen: ' . $e->getMessage());
        }

        redirect('/profile');
    }

    public function removeAvatar(): void
    {
        $user = $this->user();
        if (!$user) {
            redirect('/login');
        }

        if (!empty($user->avatar) && str_starts_with($user->avatar, '/uploads/avatars/')) {
            $oldPath = DEVIOZ_PUBLIC . $user->avatar;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $user->update([
            'avatar' => null,
        ]);

        AuditLog::record('avatar_removed', 'User', (int)$user->id);
        Session::flash('success', 'Se restableció el avatar dinámico vectorial predeterminado.');
        redirect('/profile');
    }
}
