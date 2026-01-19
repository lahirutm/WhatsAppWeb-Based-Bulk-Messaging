<?php

class BulkController
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

    public function showBulkSend()
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

        // Get instance status
        $stmt = $this->db->prepare("SELECT status FROM instances WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $instanceId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $instance = $stmt->fetch(PDO::FETCH_ASSOC);
        $instanceStatus = $instance['status'] ?? 'disconnected';

        require_once __DIR__ . '/../../views/bulk_send.php';
    }

    public function processBulkSend()
    {
        $instanceId = $_POST['instance_id'];
        $body = $_POST['body'];
        $userId = $_SESSION['user_id'];
        $scheduledAt = $_POST['scheduled_at'] ?? null;
        $numbers = [];

        // Handle CSV Upload
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            while (($line = fgetcsv($file)) !== FALSE) {
                if (!empty($line[0])) {
                    $numbers[] = trim($line[0]);
                }
            }
            fclose($file);
        }

        // Handle Text Input
        if (!empty($_POST['numbers_text'])) {
            $textNumbers = explode("\n", $_POST['numbers_text']);
            foreach ($textNumbers as $num) {
                $num = trim($num);
                if (!empty($num)) {
                    $numbers[] = $num;
                }
            }
        }

        $numbers = array_unique($numbers);
        $totalCount = count($numbers);

        if ($totalCount == 0) {
            $error = "No valid numbers found.";
            require_once __DIR__ . '/../../views/bulk_send.php';
            return;
        }

        // Validate scheduled time if provided
        $isScheduled = false;
        $status = 'pending';
        if (!empty($scheduledAt)) {
            $scheduledTime = strtotime($scheduledAt);
            if ($scheduledTime === false) {
                $error = "Invalid scheduled date/time format";
                require_once __DIR__ . '/../../views/bulk_send.php';
                return;
            }
            if ($scheduledTime <= time()) {
                $error = "Scheduled time must be in the future";
                require_once __DIR__ . '/../../views/bulk_send.php';
                return;
            }
            $isScheduled = true;
            $status = 'scheduled';
            // Convert to MySQL datetime format
            $scheduledAt = date('Y-m-d H:i:s', $scheduledTime);
        }

        // Check instance status
        $stmt = $this->db->prepare("SELECT status FROM instances WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $instanceId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $instance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$instance || $instance['status'] !== 'connected') {
            $error = "WhatsApp instance is not connected. Please connect it from the dashboard.";
            $stmt = $this->db->prepare("SELECT id, name FROM templates WHERE user_id = :user_id ORDER BY name ASC");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            require_once __DIR__ . '/../../views/bulk_send.php';
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

            if ($user['message_credits'] < $totalCount) {
                $this->db->rollBack();
                $error = "Insufficient credits. You have {$user['message_credits']} credits but need {$totalCount} to send these messages.";
                require_once __DIR__ . '/../../views/bulk_send.php';
                return;
            }

            // Deduct credits
            $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits - :count WHERE id = :user_id");
            $stmt->bindParam(':count', $totalCount);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            // Handle Image Upload
            $mediaPath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $mediaPath = '/uploads/' . $fileName;
                }
            }

            // Create Batch
            $batchName = $isScheduled ? "Scheduled Batch " . date("Y-m-d H:i:s") : "Batch " . date("Y-m-d H:i:s");
            $stmt = $this->db->prepare("INSERT INTO batches (user_id, instance_id, name, total_count) VALUES (:user_id, :instance_id, :name, :total_count)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':instance_id', $instanceId);
            $stmt->bindParam(':name', $batchName);
            $stmt->bindParam(':total_count', $totalCount);
            $stmt->execute();
            $batchId = $this->db->lastInsertId();

            // Insert Messages
            $stmt = $this->db->prepare("INSERT INTO messages (user_id, instance_id, batch_id, phone, body, media_path, status, is_scheduled, scheduled_at) VALUES (:user_id, :instance_id, :batch_id, :phone, :body, :media_path, :status, :is_scheduled, :scheduled_at)");
            foreach ($numbers as $phone) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':instance_id', $instanceId);
                $stmt->bindParam(':batch_id', $batchId);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':body', $body);
                $stmt->bindParam(':media_path', $mediaPath);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':is_scheduled', $isScheduled, PDO::PARAM_BOOL);
                $stmt->bindParam(':scheduled_at', $scheduledAt);
                $stmt->execute();
            }

            $this->db->commit();

            header("Location: /batch-status?batch_id=" . $batchId);
            exit;

        } catch (PDOException $e) {
            $this->db->rollBack();
            $error = "Failed to create batch: " . $e->getMessage();
            require_once __DIR__ . '/../../views/bulk_send.php';
        }
    }

    public function showBatchStatus()
    {
        $batchId = $_GET['batch_id'] ?? null;
        if (!$batchId) {
            header("Location: /dashboard");
            exit;
        }

        // Get Batch Info
        $stmt = $this->db->prepare("SELECT * FROM batches WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $batchId);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $batch = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$batch) {
            echo "Batch not found";
            exit;
        }

        // Get Message Stats
        $stmt = $this->db->prepare("SELECT status, COUNT(*) as count FROM messages WHERE batch_id = :batch_id GROUP BY status");
        $stmt->bindParam(':batch_id', $batchId);
        $stmt->execute();
        $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // ['pending' => 10, 'sent' => 5]

        $pending = $stats['pending'] ?? 0;
        $sent = $stats['sent'] ?? 0;
        $failed = $stats['failed'] ?? 0;

        // Get detailed messages
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE batch_id = :batch_id ORDER BY id ASC");
        $stmt->bindParam(':batch_id', $batchId);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/batch_status.php';
    }
}
