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
        require_once __DIR__ . '/../../views/send_message.php';
    }

    public function send()
    {
        $instanceId = $_POST['instance_id'];
        $phone = $_POST['phone'];
        $body = $_POST['body'];
        $userId = $_SESSION['user_id'];

        // Basic validation
        if (empty($phone) || empty($body)) {
            $error = "Phone and Body are required";
            require_once __DIR__ . '/../../views/send_message.php';
            return;
        }

        try {
            // Start transaction
            $this->db->beginTransaction();

            // Check user credits
            $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :user_id FOR UPDATE");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['message_credits'] < 1) {
                $this->db->rollBack();
                $error = "Insufficient credits. You have {$user['message_credits']} credits but need 1 to send this message.";
                require_once __DIR__ . '/../../views/send_message.php';
                return;
            }

            // Deduct credit
            $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits - 1 WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $mediaPath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $mediaPath = '/uploads/' . $fileName;
                }
            }

            $stmt = $this->db->prepare("INSERT INTO messages (user_id, instance_id, phone, body, media_path, status) VALUES (:user_id, :instance_id, :phone, :body, :media_path, 'pending')");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':instance_id', $instanceId);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':body', $body);
            $stmt->bindParam(':media_path', $mediaPath);
            $stmt->execute();

            $this->db->commit();

            $success = "Message queued successfully! Remaining credits: " . ($user['message_credits'] - 1);
            require_once __DIR__ . '/../../views/send_message.php';
        } catch (PDOException $e) {
            $this->db->rollBack();
            $error = "Failed to queue message: " . $e->getMessage();
            require_once __DIR__ . '/../../views/send_message.php';
        }
    }
}
