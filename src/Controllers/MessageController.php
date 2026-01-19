<?php

class MessageController
{
    private $db;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function showSend()
    {
        $instanceId = $_GET['instance_id'] ?? null;
        if (!$instanceId) {
            header("Location: /dashboard");
            exit;
        }
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT id, name FROM templates WHERE user_id = :user_id ORDER BY name ASC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/send_message.php';
    }

    public function send()
    {
        $instanceId = $_POST['instance_id'];
        $phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
        $body = $_POST['body'];
        $userId = $_SESSION['user_id'];
        $scheduledAt = $_POST['scheduled_at'] ?? null;
        $replyToId = $_POST['reply_to_id'] ?? null;

        // Basic validation
        if (empty($phone) || empty($body)) {
            $error = "Phone and Body are required";
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'error' => $error]);
            } else {
                require_once __DIR__ . '/../../views/send_message.php';
            }
            return;
        }

        // Check instance status
        $stmt = $this->db->prepare("SELECT status FROM instances WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $instanceId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $instance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$instance || $instance['status'] !== 'connected') {
            $error = "WhatsApp instance is not connected. Please connect it from the dashboard.";
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => false, 'error' => $error]);
            } else {
                require_once __DIR__ . '/../../views/send_message.php';
            }
            return;
        }

        // Validate scheduled time if provided
        $isScheduled = false;
        $status = 'pending';
        if (!empty($scheduledAt)) {
            $scheduledTime = strtotime($scheduledAt);
            if ($scheduledTime === false) {
                $error = "Invalid scheduled date/time format";
                require_once __DIR__ . '/../../views/send_message.php';
                return;
            }
            if ($scheduledTime <= time()) {
                $error = "Scheduled time must be in the future";
                require_once __DIR__ . '/../../views/send_message.php';
                return;
            }
            $isScheduled = true;
            $status = 'scheduled';
            $scheduledAt = date('Y-m-d H:i:s', $scheduledTime);
        }

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :user_id FOR UPDATE");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['message_credits'] < 1) {
                $this->db->rollBack();
                $error = "Insufficient credits.";
                require_once __DIR__ . '/../../views/send_message.php';
                return;
            }

            $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits - 1 WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $mediaPath = $_POST['existing_media_path'] ?? null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $mediaPath = '/uploads/' . $fileName;
                }
            }

            $stmt = $this->db->prepare("INSERT INTO messages (user_id, instance_id, phone, body, media_path, status, is_scheduled, scheduled_at, reply_to_id) VALUES (:user_id, :instance_id, :phone, :body, :media_path, :status, :is_scheduled, :scheduled_at, :reply_to_id)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':instance_id', $instanceId);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':body', $body);
            $stmt->bindParam(':media_path', $mediaPath);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':is_scheduled', $isScheduled, PDO::PARAM_BOOL);
            $stmt->bindParam(':scheduled_at', $scheduledAt);
            $stmt->bindParam(':reply_to_id', $replyToId);
            $stmt->execute();

            $this->db->commit();

            if ($isScheduled) {
                $success = "Message scheduled successfully!";
            } else {
                $success = "Message queued successfully!";
            }

            // If it's an AJAX request (from chat), return JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $success]);
                exit;
            }

            require_once __DIR__ . '/../../views/send_message.php';
        } catch (PDOException $e) {
            $this->db->rollBack();
            $error = "Failed to queue message: " . $e->getMessage();
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $error]);
                exit;
            }
            require_once __DIR__ . '/../../views/send_message.php';
        }
    }

    public function showChat()
    {
        $instanceId = $_GET['instance_id'] ?? null;
        if (!$instanceId) {
            header("Location: /dashboard");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT id, name FROM templates WHERE user_id = :user_id ORDER BY name ASC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/chat.php';
    }

    public function getRecentChats()
    {
        try {
            $userId = $_SESSION['user_id'];
            $instanceId = $_GET['instance_id'];

            $sql = "SELECT m1.phone, m1.body as last_message, m1.created_at, m1.direction
                    FROM messages m1
                    JOIN (
                        SELECT MAX(id) as max_id
                        FROM messages
                        WHERE user_id = :user_id AND instance_id = :instance_id
                        GROUP BY phone
                    ) m2 ON m1.id = m2.max_id
                    ORDER BY m1.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':instance_id', $instanceId);
            $stmt->execute();
            $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get instance status
            $stmt = $this->db->prepare("SELECT status FROM instances WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $instanceId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $instance = $stmt->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode([
                'chats' => $chats,
                'instance_status' => $instance['status'] ?? 'disconnected'
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getChatHistory()
    {
        try {
            $userId = $_SESSION['user_id'];
            $instanceId = $_GET['instance_id'];
            $phone = preg_replace('/[^0-9]/', '', $_GET['phone']);

            $sql = "SELECT m1.*, m2.body as reply_body, m2.direction as reply_direction 
                    FROM messages m1 
                    LEFT JOIN messages m2 ON m1.reply_to_id = m2.message_id 
                    WHERE m1.user_id = :user_id AND m1.instance_id = :instance_id AND m1.phone = :phone 
                    ORDER BY m1.created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':instance_id', $instanceId);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($messages);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
