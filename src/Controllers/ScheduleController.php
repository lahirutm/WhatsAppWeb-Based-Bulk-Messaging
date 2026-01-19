<?php

class ScheduleController
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

    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Get all scheduled messages for the user
        $stmt = $this->db->prepare("
            SELECT m.*, i.session_name, i.phone as instance_phone, b.name as batch_name
            FROM messages m
            LEFT JOIN instances i ON m.instance_id = i.id
            LEFT JOIN batches b ON m.batch_id = b.id
            WHERE m.user_id = :user_id 
            AND m.status = 'scheduled'
            ORDER BY m.scheduled_at ASC
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $scheduledMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/scheduled_messages.php';
    }

    public function cancel($id)
    {
        $userId = $_SESSION['user_id'];

        try {
            $this->db->beginTransaction();

            // Verify ownership and get message details
            $stmt = $this->db->prepare("SELECT * FROM messages WHERE id = :id AND user_id = :user_id AND status = 'scheduled' FOR UPDATE");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $message = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$message) {
                $this->db->rollBack();
                $_SESSION['error'] = "Scheduled message not found or already processed";
                header("Location: /scheduled-messages");
                exit;
            }

            // Cancel the message
            $stmt = $this->db->prepare("UPDATE messages SET status = 'cancelled', is_scheduled = 0 WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Refund credit
            $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits + 1 WHERE id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $this->db->commit();

            $_SESSION['success'] = "Scheduled message cancelled successfully. Credit refunded.";
        } catch (PDOException $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Failed to cancel message: " . $e->getMessage();
        }

        header("Location: /scheduled-messages");
        exit;
    }

    public function reschedule($id)
    {
        $userId = $_SESSION['user_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newScheduledAt = $_POST['scheduled_at'] ?? null;

            if (empty($newScheduledAt)) {
                $_SESSION['error'] = "Scheduled time is required";
                header("Location: /scheduled-messages");
                exit;
            }

            $scheduledTime = strtotime($newScheduledAt);
            if ($scheduledTime === false || $scheduledTime <= time()) {
                $_SESSION['error'] = "Invalid scheduled time. Must be in the future.";
                header("Location: /scheduled-messages");
                exit;
            }

            try {
                // Verify ownership
                $stmt = $this->db->prepare("SELECT id FROM messages WHERE id = :id AND user_id = :user_id AND status = 'scheduled'");
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':user_id', $userId);
                $stmt->execute();

                if ($stmt->rowCount() === 0) {
                    $_SESSION['error'] = "Scheduled message not found or already processed";
                    header("Location: /scheduled-messages");
                    exit;
                }

                // Update scheduled time
                $newScheduledAt = date('Y-m-d H:i:s', $scheduledTime);
                $stmt = $this->db->prepare("UPDATE messages SET scheduled_at = :scheduled_at WHERE id = :id");
                $stmt->bindParam(':scheduled_at', $newScheduledAt);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                $_SESSION['success'] = "Message rescheduled successfully to " . $newScheduledAt;
            } catch (PDOException $e) {
                $_SESSION['error'] = "Failed to reschedule message: " . $e->getMessage();
            }

            header("Location: /scheduled-messages");
            exit;
        }

        // GET request - show reschedule form
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE id = :id AND user_id = :user_id AND status = 'scheduled'");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$message) {
            $_SESSION['error'] = "Scheduled message not found";
            header("Location: /scheduled-messages");
            exit;
        }

        require_once __DIR__ . '/../../views/reschedule_message.php';
    }
}
