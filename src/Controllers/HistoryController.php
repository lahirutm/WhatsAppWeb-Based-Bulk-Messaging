<?php

class HistoryController
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

    public function showIndividual()
    {
        $userId = $_SESSION['user_id'];
        $where = "user_id = :user_id AND batch_id IS NULL";
        $params = [':user_id' => $userId];

        // Filters
        if (!empty($_GET['phone'])) {
            $where .= " AND phone LIKE :phone";
            $params[':phone'] = '%' . $_GET['phone'] . '%';
        }
        if (!empty($_GET['date_from'])) {
            $where .= " AND DATE(created_at) >= :date_from";
            $params[':date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where .= " AND DATE(created_at) <= :date_to";
            $params[':date_to'] = $_GET['date_to'];
        }

        $sql = "SELECT * FROM messages WHERE $where ORDER BY created_at DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/history_individual.php';
    }

    public function showBulk()
    {
        $userId = $_SESSION['user_id'];
        $where = "user_id = :user_id";
        $params = [':user_id' => $userId];

        // Filters
        if (!empty($_GET['date_from'])) {
            $where .= " AND DATE(created_at) >= :date_from";
            $params[':date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where .= " AND DATE(created_at) <= :date_to";
            $params[':date_to'] = $_GET['date_to'];
        }

        $sql = "SELECT * FROM batches WHERE $where ORDER BY created_at DESC LIMIT 50";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/history_bulk.php';
    }
}
