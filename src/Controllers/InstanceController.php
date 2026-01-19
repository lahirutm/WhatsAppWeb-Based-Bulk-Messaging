<?php

class InstanceController
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

    public function showLink()
    {
        $instanceId = $_GET['instance_id'] ?? null;
        $sessionName = '';

        if ($instanceId) {
            // Re-linking existing instance
            $stmt = $this->db->prepare("SELECT session_name FROM instances WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $instanceId);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $instance = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($instance) {
                $sessionName = $instance['session_name'];
            } else {
                // Invalid instance, redirect or error
                header("Location: /dashboard");
                exit;
            }
        } else {
            // New instance
            $sessionName = 'user_' . $_SESSION['user_id'] . '_' . time();

            // Create the instance record first
            $stmt = $this->db->prepare("INSERT INTO instances (user_id, session_name) VALUES (:user_id, :session_name)");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':session_name', $sessionName);
            $stmt->execute();

            $instanceId = $this->db->lastInsertId();
        }

        require_once __DIR__ . '/../../views/link_account.php';
    }

    public function disconnect()
    {
        $instanceId = $_POST['instance_id'];

        // Call Node.js service to logout
        $ch = curl_init('http://localhost:3000/logout');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['instanceId' => $instanceId]));
        curl_exec($ch);
        curl_close($ch);

        header("Location: /dashboard");
        exit;
    }

    // Note: Actual linking happens via Socket.io on the client side, 
    // communicating with the Node.js service.
}
