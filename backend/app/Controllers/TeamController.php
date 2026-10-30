<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Uploader;
use App\Models\AuditLog;
use App\Models\User;

/**
 * Devioz 24 — Directorio y CRUD de Talento Técnico / Consultores
 */
class TeamController extends BaseController
{
    /**
     * Listado principal con métricas y filtros
     */
    public function index(): string
    {
        $db = Database::getInstance();

        $search = trim((string)($_GET['q'] ?? ''));
        $sede = trim((string)($_GET['sede'] ?? ''));
        $role = trim((string)($_GET['role'] ?? ''));
        $status = trim((string)($_GET['status'] ?? 'active'));

        $sql = "SELECT u.*, 
                       p.id as project_id, p.code as project_code, p.name as project_name, p.status as project_status 
                FROM `users` u 
                LEFT JOIN `projects` p ON p.developer_id = u.id AND p.status NOT IN ('completed', 'archived', 'rejected') AND p.deleted_at IS NULL
                WHERE 1=1";
        $params = [];

        if ($status === 'active') {
            $sql .= " AND u.active = 1 AND u.deleted_at IS NULL";
        } elseif ($status === 'inactive') {
            $sql .= " AND (u.active = 0 OR u.deleted_at IS NOT NULL)";
        }
        // Si es 'all', muestra todos los registros

        if ($search !== '') {
            $searchTerm = "%{$search}%";
            $sql .= " AND (u.name LIKE :q1 OR u.email LIKE :q2 OR u.headline LIKE :q3)";
            $params['q1'] = $searchTerm;
            $params['q2'] = $searchTerm;
            $params['q3'] = $searchTerm;
        }

        if ($sede !== '') {
            $sql .= " AND u.sede = :sede";
            $params['sede'] = $sede;
        }

        if ($role !== '') {
            $sql .= " AND u.role = :role";
            $params['role'] = $role;
        }

        $sql .= " ORDER BY (u.role = 'admin') DESC, (u.role = 'lider') DESC, u.active DESC, u.sede, u.name ASC";

        $members = $db->fetchAll($sql, $params);

        // Estadísticas para KPIs superiores
        $stats = [
            'total'       => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL"),
            'developers'  => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'developer' AND active = 1 AND deleted_at IS NULL"),
            'leads_admin' => (int)$db->fetchColumn("SELECT COUNT(*) FROM users WHERE role IN ('admin', 'lider') AND active = 1 AND deleted_at IS NULL"),
            'available'   => (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM users u 
                 WHERE u.role = 'developer' AND u.active = 1 AND u.deleted_at IS NULL 
                   AND u.id NOT IN (
                       SELECT developer_id FROM projects 
                       WHERE status NOT IN ('completed', 'archived', 'rejected') 
                         AND deleted_at IS NULL AND developer_id IS NOT NULL
                   )"
            ),
        ];

        return $this->view('team/index', [
            'title'   => 'Directorio de Talento & Equipo — Devioz 24',
            'members' => $members,
            'search'  => $search,
            'sede'    => $sede,
            'role'    => $role,
            'status'  => $status,
            'stats'   => $stats,
            'isAdmin' => $this->auth->isAdmin(),
        ]);
    }

    /**
     * Retorna datos de un usuario en JSON para pre-cargar el modal de edición
     */
    public function showJson(string $id): never
    {
        $this->ensureAdmin();
        $member = User::find((int)$id);
        if (!$member) {
            $this->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
        }

        $this->json([
            'success' => true,
            'member' => [
                'id'         => (int)$member->id,
                'name'       => $member->name,
                'email'      => $member->email,
                'role'       => $member->role,
                'sede'       => $member->sede,
                'headline'   => $member->headline ?? '',
                'bio'        => $member->bio ?? '',
                'active'     => (int)$member->active,
                'avatar_url' => $member->avatarUrl(),
            ]
        ]);
    }

    /**
     * Crear un nuevo programador / miembro del equipo
     */
    public function store(): void
    {
        $this->ensureAdmin();

        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $role = trim((string)($_POST['role'] ?? 'developer'));
        $sede = trim((string)($_POST['sede'] ?? 'Cercado'));
        $password = (string)($_POST['password'] ?? '');
        $headline = trim((string)($_POST['headline'] ?? ''));
        $bio = trim((string)($_POST['bio'] ?? ''));

        $isAjax = $this->isAjax();

        // Validaciones
        if ($name === '' || strlen($name) < 3) {
            $this->failValidation('El nombre completo debe tener al menos 3 caracteres.', $isAjax);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->failValidation('El formato de correo electrónico no es válido.', $isAjax);
        }

        $existing = User::findBy('email', $email);
        if ($existing) {
            $this->failValidation('El correo electrónico ya se encuentra registrado por otro usuario.', $isAjax);
        }

        if (!in_array($role, ['admin', 'developer', 'lider'], true)) {
            $this->failValidation('El rol seleccionado no es válido.', $isAjax);
        }

        if (!in_array($sede, ['Cercado', 'Lima Norte', 'Villa'], true)) {
            $this->failValidation('La sede seleccionada no es válida.', $isAjax);
        }

        if (strlen($password) < 6) {
            $this->failValidation('La contraseña debe tener al menos 6 caracteres.', $isAjax);
        }

        // Subida opcional de avatar
        $avatarPath = null;
        if (!empty($_FILES['avatar']['name']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $uploadMeta = Uploader::upload($_FILES['avatar'], 'avatars', 5 * 1024 * 1024);
                $avatarPath = $uploadMeta['path'];
            } catch (\Throwable $e) {
                // Continuar sin avatar personalizado
            }
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Auth::hashPassword($password),
            'role'     => $role,
            'sede'     => $sede,
            'headline' => $headline ?: null,
            'bio'      => $bio ?: null,
            'avatar'   => $avatarPath,
            'active'   => 1,
        ]);

        AuditLog::record('team.member_created', 'User', (int)$user->id, [
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'sede'       => $user->sede,
            'created_by' => $this->auth->id(),
        ]);

        if ($isAjax) {
            $this->json([
                'success' => true,
                'message' => "¡Miembro {$user->name} registrado exitosamente!",
                'user_id' => (int)$user->id,
            ]);
        }

        Session::flash('success', "¡El profesional {$user->name} ha sido registrado exitosamente con el rol de " . $user->roleLabel() . "!");
        redirect('/team');
    }

    /**
     * Modificar un programador / miembro existente
     */
    public function update(string $id): void
    {
        $this->ensureAdmin();

        $member = User::find((int)$id);
        if (!$member) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
            }
            Session::flash('error', 'Miembro no encontrado.');
            redirect('/team');
        }

        $name = trim((string)($_POST['name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $role = trim((string)($_POST['role'] ?? $member->role));
        $sede = trim((string)($_POST['sede'] ?? $member->sede));
        $password = (string)($_POST['password'] ?? '');
        $headline = trim((string)($_POST['headline'] ?? ''));
        $bio = trim((string)($_POST['bio'] ?? ''));
        $active = isset($_POST['active']) ? (int)$_POST['active'] : (int)$member->active;

        $isAjax = $this->isAjax();

        if ($name === '' || strlen($name) < 3) {
            $this->failValidation('El nombre completo debe tener al menos 3 caracteres.', $isAjax);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->failValidation('El formato de correo electrónico no es válido.', $isAjax);
        }

        if ($email !== strtolower($member->email)) {
            $existing = User::findBy('email', $email);
            if ($existing && (int)$existing->id !== (int)$member->id) {
                $this->failValidation('El correo electrónico ya está en uso por otra cuenta.', $isAjax);
            }
        }

        if (!in_array($role, ['admin', 'developer', 'lider'], true)) {
            $this->failValidation('El rol seleccionado no es válido.', $isAjax);
        }

        if (!in_array($sede, ['Cercado', 'Lima Norte', 'Villa'], true)) {
            $this->failValidation('La sede seleccionada no es válida.', $isAjax);
        }

        $updateData = [
            'name'     => $name,
            'email'    => $email,
            'role'     => $role,
            'sede'     => $sede,
            'headline' => $headline ?: null,
            'bio'      => $bio ?: null,
            'active'   => $active,
        ];

        // Actualizar contraseña si se ingresó una nueva
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $this->failValidation('La nueva contraseña debe contener al menos 6 caracteres.', $isAjax);
            }
            $updateData['password'] = Auth::hashPassword($password);
        }

        // Subida de nuevo avatar si se adjuntó
        if (!empty($_FILES['avatar']['name']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $uploadMeta = Uploader::upload($_FILES['avatar'], 'avatars', 5 * 1024 * 1024);
                $updateData['avatar'] = $uploadMeta['path'];
            } catch (\Throwable $e) {}
        }

        $member->update($updateData);

        AuditLog::record('team.member_updated', 'User', (int)$member->id, [
            'name'       => $name,
            'email'      => $email,
            'role'       => $role,
            'sede'       => $sede,
            'active'     => $active,
            'updated_by' => $this->auth->id(),
        ]);

        if ($isAjax) {
            $this->json([
                'success' => true,
                'message' => "Datos de {$member->name} actualizados exitosamente.",
            ]);
        }

        Session::flash('success', "Datos de {$member->name} actualizados exitosamente.");
        redirect('/team');
    }

    /**
     * Asignación rápida de rol
     */
    public function updateRole(string $id): void
    {
        $this->ensureAdmin();

        $member = User::find((int)$id);
        if (!$member) {
            $this->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
        }

        $newRole = trim((string)($_POST['role'] ?? ''));
        if (!in_array($newRole, ['admin', 'developer', 'lider'], true)) {
            $this->json(['success' => false, 'message' => 'Rol inválido.'], 422);
        }

        $oldRole = $member->role;
        $member->update(['role' => $newRole]);

        AuditLog::record('team.role_changed', 'User', (int)$member->id, [
            'old_role'   => $oldRole,
            'new_role'   => $newRole,
            'changed_by' => $this->auth->id(),
        ]);

        if ($this->isAjax()) {
            $this->json([
                'success'     => true,
                'message'     => "Rol de {$member->name} modificado a " . $member->roleLabel() . ".",
                'role'        => $newRole,
                'role_label'  => $member->roleLabel(),
                'badge_class' => $member->roleBadgeClass(),
            ]);
        }

        Session::flash('success', "Rol de {$member->name} modificado a " . $member->roleLabel() . ".");
        redirect('/team');
    }

    /**
     * Dar de baja / Desactivar miembro (Soft Delete)
     */
    public function destroy(string $id): void
    {
        $this->ensureAdmin();

        $targetId = (int)$id;
        if ($targetId === (int)$this->auth->id()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Por seguridad, no puedes darte de baja a ti mismo.'], 422);
            }
            Session::flash('error', 'Por seguridad, no puedes darte de baja a ti mismo.');
            redirect('/team');
        }

        $member = User::find($targetId);
        if (!$member) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
            }
            Session::flash('error', 'Miembro no encontrado.');
            redirect('/team');
        }

        $member->update([
            'active'     => 0,
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::record('team.member_deactivated', 'User', (int)$member->id, [
            'name'       => $member->name,
            'email'      => $member->email,
            'deleted_by' => $this->auth->id(),
        ]);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "El miembro {$member->name} ha sido dado de baja.",
            ]);
        }

        Session::flash('success', "El miembro {$member->name} ha sido dado de baja.");
        redirect('/team');
    }

    /**
     * Reactivar un miembro dado de baja
     */
    public function restore(string $id): void
    {
        $this->ensureAdmin();

        $member = User::find((int)$id);
        if (!$member) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Miembro no encontrado.'], 404);
            }
            Session::flash('error', 'Miembro no encontrado.');
            redirect('/team');
        }

        $member->update([
            'active'     => 1,
            'deleted_at' => null,
        ]);

        AuditLog::record('team.member_restored', 'User', (int)$member->id, [
            'name'        => $member->name,
            'restored_by' => $this->auth->id(),
        ]);

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "¡Cuenta de {$member->name} reactivada con éxito!",
            ]);
        }

        Session::flash('success', "¡Cuenta de {$member->name} reactivada con éxito!");
        redirect('/team');
    }

    private function ensureAdmin(): void
    {
        if (!$this->auth->isAdmin()) {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Acceso denegado. Se requieren privilegios de Administrador.'], 403);
            }
            echo \App\Core\View::renderError(403, "Acceso denegado. Se requieren privilegios de Administrador.");
            exit;
        }
    }

    private function failValidation(string $message, bool $isAjax): never
    {
        if ($isAjax) {
            $this->json(['success' => false, 'message' => $message], 422);
        }
        Session::flash('error', $message);
        redirect('/team');
        exit;
    }

    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/');
    }
}
