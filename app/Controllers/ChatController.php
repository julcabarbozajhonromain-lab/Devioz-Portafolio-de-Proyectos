<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Database;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use PDO;

class ChatController extends BaseController
{
    public function index(): string
    {
        $user = $this->user();
        $db = Database::getInstance();

        // Contactos disponibles para chat directo
        $sedeFiltro = $user->isLider() ? " AND `sede` = :sede" : "";
        $params = $user->isLider() ? ['sede' => $user->sede, 'my_id' => $user->id] : ['my_id' => $user->id];

        $contacts = User::whereRaw("`id` != :my_id AND `active` = 1 AND `deleted_at` IS NULL {$sedeFiltro}", $params, 'name ASC');

        // Contacto preseleccionado por query param ?contact_id=X
        $selectedContactId = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : 0;
        $allowedChannels = ['copilot', 'news', 'general', 'notes', 'direct'];
        $requestedChannel = isset($_GET['channel']) ? (string)$_GET['channel'] : '';
        $selectedChannel = $selectedContactId > 0
            ? 'direct'
            : (in_array($requestedChannel, $allowedChannels, true) ? $requestedChannel : '');

        // Noticias corporativas (de lounge_posts)
        $companyNews = \App\Models\LoungePost::whereRaw("1=1", [], 'created_at DESC', 10);

        // Mensajes del canal general del equipo
        $generalMessages = $db->fetchAll(
            "SELECT m.*, u.name as sender_name, u.avatar as sender_avatar, u.role as sender_role 
             FROM messages m 
             LEFT JOIN users u ON m.sender_id = u.id 
             WHERE m.type = 'general_channel' OR (m.receiver_id = 0 AND m.type = 'general')
             ORDER BY m.created_at ASC LIMIT 50"
        );

        // Notas personales del usuario activo
        $personalNotes = $db->fetchAll(
            "SELECT * FROM messages 
             WHERE sender_id = :uid AND receiver_id = :ruid 
             ORDER BY created_at ASC LIMIT 50",
            ['uid' => $user->id, 'ruid' => $user->id]
        );

        return $this->view('chat/index', [
            'title' => 'Chats & Messenger — Devioz OS',
            'currentUser' => $user,
            'contacts' => $contacts,
            'selectedContactId' => $selectedContactId,
            'selectedChannel' => $selectedChannel,
            'companyNews' => $companyNews,
            'generalMessages' => $generalMessages,
            'personalNotes' => $personalNotes,
        ]);
    }

    public function stream(): never
    {
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $user = $this->user();
        $db = Database::getInstance();
        
        // MUY IMPORTANTE: Cerrar la sesión para no bloquear otras peticiones concurrentes del usuario (ej. navegación)
        session_write_close();
        
        $lastCheckedId = isset($_GET['last_msg_id']) ? (int)$_GET['last_msg_id'] : 0;
        $maxIterations = 50; 
        
        for ($i = 0; $i < $maxIterations; $i++) {
            $stmt = $db->getConnection()->prepare("SELECT id, sender_id, type, body, created_at, project_id FROM messages WHERE receiver_id = :uid AND read_at IS NULL AND id > :lastId ORDER BY id ASC LIMIT 5");
            $stmt->execute(['uid' => $user->id, 'lastId' => $lastCheckedId]);
            $newMessages = $stmt->fetchAll();
            
            if (!empty($newMessages)) {
                $lastCheckedId = end($newMessages)['id'];
                $countStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = :uid AND read_at IS NULL");
                $countStmt->execute(['uid' => $user->id]);
                $unreadCount = (int)$countStmt->fetchColumn();
                $payload = json_encode(['messages' => $newMessages, 'unread_count' => $unreadCount]);
                echo "data: {$payload}\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            }
            echo ": heartbeat\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
            sleep(2);
        }
        echo "event: close\ndata: refresh\n\n";
        exit;
    }

    public function conversation(): never
    {
        $user = $this->user();
        $db = Database::getInstance();
        $contactId = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : 0;
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        
        $project = null;
        if ($projectId > 0) {
            $project = Project::find($projectId);
        } elseif ($user->isDeveloper()) {
            $project = $user->activeProject();
        }

        $contact = null;
        if ($contactId > 0) {
            $contact = User::find($contactId);
        }
        if (!$contact && $user->isDeveloper()) {
            if ($project && $project->assignedBy()) {
                $contact = $project->assignedBy();
            } else {
                $contact = User::where('role', 'admin')->first();
            }
        }

        if ($contact && $user->sede !== $contact->sede) {
            $this->json(['error' => 'Violacion de seguridad 007: Inter-sede bloqueada.'], 403);
        }

        // Logic omitted for brevity, assuming standard chat load
        $messages = []; // we should actually fetch messages
        $stmt = $db->getConnection()->prepare("SELECT m.*, u.name as sender_name FROM messages m LEFT JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = :uid AND m.receiver_id = :cid) OR (m.sender_id = :cid AND m.receiver_id = :uid) ORDER BY m.created_at ASC LIMIT 50");
        $stmt->execute(['uid' => $user->id, 'cid' => $contact ? $contact->id : 0]);
        $messages = $stmt->fetchAll();

        // mark read
        $db->query("UPDATE messages SET read_at = NOW() WHERE receiver_id = :uid AND sender_id = :cid AND read_at IS NULL", ['uid' => $user->id, 'cid' => $contact ? $contact->id : 0]);

        $unreadStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = :uid AND read_at IS NULL");
        $unreadStmt->execute(['uid' => $user->id]);

        $this->json([
            'success' => true,
            'contact' => $contact ? [
                'id' => (int)$contact->id,
                'name' => $contact->name,
                'role_label' => $contact->role === 'admin' ? 'Lead Técnico & Auditor QA' : 'Desarrollador',
                'avatar' => $contact->avatarUrl(),
            ] : null,
            'messages' => array_map(function($m) use ($user) {
                return [
                    'id' => $m['id'],
                    'body' => $m['body'],
                    'type' => $m['type'] ?? 'general',
                    'is_mine' => (int)$m['sender_id'] === (int)$user->id,
                    'is_read' => !empty($m['read_at']),
                    'date_label' => date('H:i', strtotime($m['created_at']))
                ];
            }, $messages),
            'unread_total' => (int)$unreadStmt->fetchColumn(),
        ]);
    }

    public function send(): never
    {
        $user = $this->user();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $receiverId = (int)($input['receiver_id'] ?? 0);
        $body = trim((string)($input['body'] ?? ''));
        
        if ($receiverId <= 0 || $body === '') {
            $this->json(['success' => false, 'message' => 'Faltan datos'], 422);
        }
        $receiver = User::find($receiverId);
        if ($receiver && $user->sede !== $receiver->sede) {
            $this->json(['success' => false, 'message' => 'Violacion de seguridad 007: Inter-sede bloqueada.'], 403);
        }
        
        Database::getInstance()->query(
            "INSERT INTO messages (sender_id, receiver_id, body, type, created_at) VALUES (:sid, :rid, :b, 'general', NOW())",
            ['sid' => $user->id, 'rid' => $receiverId, 'b' => $body]
        );
        $this->json(['success' => true]);
    }

    public function requestCall(): never
    {
        $user = $this->user();
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $receiverId = (int)($input['receiver_id'] ?? 0);
        $reason = trim((string)($input['reason'] ?? 'Reunion'));

        $receiver = User::find($receiverId);
        if ($receiver && $user->sede !== $receiver->sede) {
            $this->json(['success' => false, 'message' => 'Violacion de seguridad 007: Inter-sede bloqueada.'], 403);
        }

        $roomUrl = "https://meet.jit.si/Devioz-Bitrix-Meet-" . md5(uniqid());
        $body = "Solicitud de llamada urgente: {$reason}. Entrar aqui: {$roomUrl}";
        Database::getInstance()->query(
            "INSERT INTO messages (sender_id, receiver_id, body, type, priority, created_at) VALUES (:sid, :rid, :b, 'alert', 'urgent', NOW())",
            ['sid' => $user->id, 'rid' => $receiverId, 'b' => $body]
        );

        $this->json(['success' => true, 'room_url' => $roomUrl]);
    }

    public function notifications(): never
    {
        $user = $this->user();
        $db = Database::getInstance();
        $stmt = $db->getConnection()->prepare("
            SELECT m.*, u.name as sender_name, u.avatar as sender_avatar 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.id 
            WHERE m.receiver_id = :uid AND m.type = 'alert' 
            ORDER BY m.created_at DESC LIMIT 10
        ");
        $stmt->execute(['uid' => $user->id]);
        
        $notifications = [];
        $unreadCount = 0;
        foreach ($stmt->fetchAll() as $row) {
            if (empty($row['read_at'])) $unreadCount++;
            
            // Requerimiento "Solo la hora": Eliminar fecha completa y dejar unicamente la hora (estilo Bitrix)
            $time = date('H:i', strtotime($row['created_at']));
            
            $avatarUrl = !empty($row['sender_avatar']) 
                ? (str_starts_with($row['sender_avatar'], 'http') ? $row['sender_avatar'] : '/'.$row['sender_avatar']) 
                : 'https://ui-avatars.com/api/?name='.urlencode($row['sender_name'] ?? 'Sys').'&background=00ACE3&color=fff';
            
            $notifications[] = [
                'id' => $row['id'],
                'contact_id' => $row['sender_id'],
                'avatar' => $avatarUrl,
                'title' => $row['sender_name'] ?? 'Notificación del Sistema',
                'time' => $time,
                'body' => $row['body'],
                'is_urgent' => ($row['priority'] === 'urgent'),
                'action_url' => '',
            ];
        }

        $this->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    public function markRead(): never
    {
        $user = $this->user();
        Database::getInstance()->query("UPDATE messages SET read_at = NOW() WHERE receiver_id = :uid AND read_at IS NULL", ['uid' => $user->id]);
        $this->json(['success' => true]);
    }
}
