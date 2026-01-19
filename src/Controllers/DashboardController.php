<?php

class DashboardController
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

        // Fetch user data including credits
        $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("
            SELECT i.*, 
            (SELECT COUNT(*) FROM messages WHERE instance_id = i.id AND status = 'sent') as sent_count 
            FROM instances i 
            WHERE i.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $instances = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/dashboard.php';
    }
}
