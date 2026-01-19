<?php

class ApiSettingsController
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
        $stmt = $this->db->prepare("SELECT * FROM instances WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $instances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/api_settings.php';
    }

    public function generateKey()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $instanceId = $_POST['instance_id'];
            $userId = $_SESSION['user_id'];

            // Verify ownership
            $stmt = $this->db->prepare("SELECT id FROM instances WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $instanceId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Generate secure random key
                $apiKey = bin2hex(random_bytes(32));

                $updateStmt = $this->db->prepare("UPDATE instances SET api_key = :api_key, api_key_created_at = NOW() WHERE id = :id");
                $updateStmt->bindParam(':api_key', $apiKey);
                $updateStmt->bindParam(':id', $instanceId);
                $updateStmt->execute();
            }
        }

        header("Location: /api-settings");
        exit;
    }
}
