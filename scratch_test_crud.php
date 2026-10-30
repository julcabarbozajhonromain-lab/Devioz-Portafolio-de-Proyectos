<?php
require_once __DIR__ . '/backend/bootstrap/app.php';

use App\Models\User;
use App\Core\Auth;

$u = User::findBy('email', 'test_crud_dev@devioz.com');
if ($u) {
    $u->delete();
}

$dev = User::create([
    'name' => 'Test CRUD Dev',
    'email' => 'test_crud_dev@devioz.com',
    'password' => Auth::hashPassword('Devioz2026*'),
    'role' => 'developer',
    'sede' => 'Cercado',
    'active' => 1,
    'headline' => 'Full Stack Cloud Dev'
]);

echo "Created ID: " . $dev->id . "\n";
echo "Role: " . $dev->role . " -> " . $dev->roleLabel() . "\n";

$dev->update(['role' => 'lider']);
echo "Updated Role: " . $dev->role . " -> " . $dev->roleLabel() . "\n";

$dev->update(['active' => 0, 'deleted_at' => date('Y-m-d H:i:s')]);
echo "Deactivated: active=" . $dev->active . "\n";

$dev->update(['active' => 1, 'deleted_at' => null]);
echo "Restored: active=" . $dev->active . "\n";

$dev->delete();
echo "Cleaned up test user successfully!\n";
