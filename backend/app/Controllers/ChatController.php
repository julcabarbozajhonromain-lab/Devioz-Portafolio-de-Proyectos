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
    private function canCommunicate(User $sender, User $receiver): bool
    {
        // Administrators supervise every site; developers and leads stay within their site.
        return $sender->isAdmin()
            || $receiver->isAdmin()
            || ($sender->sede !== null && $sender->sede === $receiver->sede);
    }

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
            'title' => 'Chats & Messenger — Devioz 24',
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
            $stmt = $db->getConnection()->prepare(
                "SELECT id, sender_id, type, body, attachment_url, attachment_type, attachment_name, attachment_size, created_at, project_id 
                 FROM messages 
                 WHERE receiver_id = :uid AND read_at IS NULL AND id > :lastId 
                 ORDER BY id ASC LIMIT 5"
            );
            $stmt->execute(['uid' => $user->id, 'lastId' => $lastCheckedId]);
            $newMessages = $stmt->fetchAll();

            if (!empty($newMessages)) {
                $lastCheckedId = end($newMessages)['id'];
                $countStmt = $db->getConnection()->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = :uid AND read_at IS NULL");
                $countStmt->execute(['uid' => $user->id]);
                $unreadCount = (int)$countStmt->fetchColumn();

                $formattedMessages = array_map(function($m) {
                    $m['date_label'] = date('H:i', strtotime($m['created_at']));
                    return $m;
                }, $newMessages);

                $payload = json_encode(['messages' => $formattedMessages, 'unread_count' => $unreadCount]);
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
        if ($contactId > 0 && (!$contact || !$contact->active || $contact->deleted_at !== null)) {
            $this->json(['success' => false, 'message' => 'El contacto no está disponible.'], 404);
        }
        if (!$contact && $user->isDeveloper()) {
            if ($project && $project->assignedBy()) {
                $contact = $project->assignedBy();
            } else {
                $admins = User::where('role', 'admin');
                $contact = !empty($admins) ? $admins[0] : null;
            }
        }

        if ($contact && !$this->canCommunicate($user, $contact)) {
            $this->json(['error' => 'Violacion de seguridad 007: Inter-sede bloqueada.'], 403);
        }

        $messages = [];
        $stmt = $db->getConnection()->prepare(
            "SELECT m.*, u.name AS sender_name
             FROM messages m
             LEFT JOIN users u ON m.sender_id = u.id
             WHERE (m.sender_id = :sender_a AND m.receiver_id = :receiver_a)
                OR (m.sender_id = :sender_b AND m.receiver_id = :receiver_b)
             ORDER BY m.created_at ASC, m.id ASC
             LIMIT 100"
        );
        $contactIdForQuery = $contact ? (int)$contact->id : 0;
        $stmt->execute([
            'sender_a' => $user->id,
            'receiver_a' => $contactIdForQuery,
            'sender_b' => $contactIdForQuery,
            'receiver_b' => $user->id,
        ]);
        $messages = $stmt->fetchAll();

        // mark read
        if ($contact) {
            $db->query(
                "UPDATE messages SET read_at = NOW()
                 WHERE receiver_id = :uid AND sender_id = :cid AND read_at IS NULL",
                ['uid' => $user->id, 'cid' => $contactIdForQuery]
            );
        }

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
            'project' => $project ? [
                'id' => (int)$project->id,
                'code' => $project->code,
                'name' => $project->name,
            ] : null,
            'messages' => array_map(function($m) use ($user) {
                return [
                    'id' => $m['id'],
                    'body' => $m['body'],
                    'type' => $m['type'] ?? 'general',
                    'attachment_url' => $m['attachment_url'] ?? null,
                    'attachment_type' => $m['attachment_type'] ?? null,
                    'attachment_name' => $m['attachment_name'] ?? null,
                    'attachment_size' => $m['attachment_size'] ?? null,
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
        try {
            $user = $this->user();
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true) ?: $_POST;
            $receiverId = (int)($input['receiver_id'] ?? ($_POST['receiver_id'] ?? 0));
            $body = trim((string)($input['body'] ?? ($_POST['body'] ?? '')));
            $attachmentUrl = $input['attachment_url'] ?? ($_POST['attachment_url'] ?? null);
            $attachmentType = $input['attachment_type'] ?? ($_POST['attachment_type'] ?? null);
            $attachmentName = $input['attachment_name'] ?? ($_POST['attachment_name'] ?? null);
            $attachmentSize = isset($input['attachment_size']) ? (int)$input['attachment_size'] : (isset($_POST['attachment_size']) ? (int)$_POST['attachment_size'] : null);

            // Procesar archivo o audio adjunto vía multipart/form-data
            $uploadedFile = $_FILES['attachment'] ?? ($_FILES['file'] ?? ($_FILES['audio'] ?? null));
            if ($uploadedFile && !empty($uploadedFile['name']) && ($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $origName = (string)$uploadedFile['name'];
                $isVoice = ($attachmentType === 'audio' 
                    || str_starts_with($uploadedFile['type'] ?? '', 'audio') 
                    || preg_match('/\.(webm|ogg|wav|mp3|m4a)$/i', $origName));

                $subDir = $isVoice ? 'chat/audio' : 'chat';
                $meta = \App\Core\Uploader::upload($uploadedFile, $subDir, 50 * 1024 * 1024);
                $attachmentUrl = url($meta['path']);
                $attachmentName = $meta['original_name'];
                $attachmentSize = (int)$meta['size'];

                if (!$attachmentType) {
                    if ($isVoice) {
                        $attachmentType = 'audio';
                    } elseif (str_starts_with($meta['mime_type'], 'image/')) {
                        $attachmentType = 'image';
                    } elseif (str_starts_with($meta['mime_type'], 'video/')) {
                        $attachmentType = 'video';
                    } else {
                        $attachmentType = 'file';
                    }
                }
            }

            if ($receiverId <= 0) {
                $this->json(['success' => false, 'message' => 'Falta indicar el destinatario del mensaje.'], 422);
            }
            if ($body === '' && empty($attachmentUrl)) {
                $this->json(['success' => false, 'message' => 'El mensaje debe contener texto o un archivo adjunto.'], 422);
            }

            $receiver = User::find($receiverId);
            if (!$receiver || !$receiver->active || $receiver->deleted_at !== null) {
                $this->json(['success' => false, 'message' => 'El destinatario no existe o no está activo.'], 404);
            }
            if (!$this->canCommunicate($user, $receiver)) {
                $this->json(['success' => false, 'message' => 'Violación de seguridad: Comunicación inter-sede restringida.'], 403);
            }

            $db = Database::getInstance();
            $db->query(
                "INSERT INTO messages (sender_id, receiver_id, body, attachment_url, attachment_type, attachment_name, attachment_size, type, created_at)
                 VALUES (:sid, :rid, :b, :att_url, :att_type, :att_name, :att_size, 'general', NOW())",
                [
                    'sid' => $user->id,
                    'rid' => $receiverId,
                    'b' => $body,
                    'att_url' => $attachmentUrl,
                    'att_type' => $attachmentType,
                    'att_name' => $attachmentName,
                    'att_size' => $attachmentSize,
                ]
            );
            $msgId = (int)$db->lastInsertId();

            $this->json([
                'success' => true,
                'message_id' => $msgId,
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'body' => $body,
                'attachment_url' => $attachmentUrl,
                'attachment_type' => $attachmentType,
                'attachment_name' => $attachmentName,
                'attachment_size' => $attachmentSize,
                'is_mine' => true,
                'is_read' => false,
                'date_label' => date('H:i')
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al guardar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    public function requestCall(): never
    {
        try {
            $user = $this->user();
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true) ?: $_POST;
            $receiverId = (int)($input['receiver_id'] ?? 0);
            $reason = trim((string)($input['reason'] ?? 'Reunión técnica'));

            if ($receiverId <= 0) {
                $this->json(['success' => false, 'message' => 'Selecciona a la persona que recibirá la invitación.'], 422);
            }

            $receiver = User::find($receiverId);
            if (!$receiver || !$receiver->active || $receiver->deleted_at !== null) {
                $this->json(['success' => false, 'message' => 'El destinatario no existe o no está activo.'], 404);
            }
            if (!$this->canCommunicate($user, $receiver)) {
                $this->json(['success' => false, 'message' => 'Violación de seguridad: Comunicación inter-sede restringida.'], 403);
            }

            $roomUrl = 'https://meet.jit.si/Devioz-Meet-' . bin2hex(random_bytes(16));
            $body = "Solicitud de llamada urgente: {$reason}. Entrar aquí: {$roomUrl}";
            Database::getInstance()->query(
                "INSERT INTO messages (sender_id, receiver_id, body, type, priority, created_at) VALUES (:sid, :rid, :b, 'alert', 'urgent', NOW())",
                ['sid' => $user->id, 'rid' => $receiverId, 'b' => $body]
            );

            $this->json(['success' => true, 'room_url' => $roomUrl]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Error al crear la sala de videollamada: ' . $e->getMessage()
            ], 500);
        }
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

            // Requerimiento "Solo la hora": Eliminar fecha completa y dejar unicamente la hora (estilo Devioz)
            $time = date('H:i', strtotime($row['created_at']));

            $avatarUrl = !empty($row['sender_avatar'])
                ? (str_starts_with($row['sender_avatar'], 'http') ? $row['sender_avatar'] : '/'.$row['sender_avatar'])
                : 'https://ui-avatars.com/api/?name='.urlencode($row['sender_name'] ?? 'Sys').'&background=00ACE3&color=fff';

            $roomUrl = preg_match('~https://meet\.jit\.si/[A-Za-z0-9_-]+~', (string)$row['body'], $roomMatch)
                ? $roomMatch[0]
                : '';

            $notifications[] = [
                'id' => $row['id'],
                'contact_id' => $row['sender_id'],
                'avatar' => $avatarUrl,
                'title' => $row['sender_name'] ?? 'Notificación del Sistema',
                'time' => $time,
                'body' => $row['body'],
                'is_urgent' => ($row['priority'] === 'urgent'),
                'action_url' => $roomUrl,
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
