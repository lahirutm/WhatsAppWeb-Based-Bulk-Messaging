<?php

class TemplateController
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
        $stmt = $this->db->prepare("SELECT * FROM templates WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/templates/index.php';
    }

    public function create()
    {
        require_once __DIR__ . '/../../views/templates/create.php';
    }

    public function store()
    {
        $userId = $_SESSION['user_id'];
        $name = $_POST['name'] ?? '';
        $body = $_POST['body'] ?? '';

        if (empty($name) || empty($body)) {
            $error = "Name and Body are required";
            require_once __DIR__ . '/../../views/templates/create.php';
            return;
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO templates (user_id, name, body) VALUES (:user_id, :name, :body)");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':body', $body);
            $stmt->execute();

            $_SESSION['success'] = "Template created successfully";
            header("Location: /templates");
            exit;
        } catch (PDOException $e) {
            $error = "Failed to create template: " . $e->getMessage();
            require_once __DIR__ . '/../../views/templates/create.php';
        }
    }

    public function edit($id)
    {
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT * FROM templates WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$template) {
            $_SESSION['error'] = "Template not found";
            header("Location: /templates");
            exit;
        }

        require_once __DIR__ . '/../../views/templates/edit.php';
    }

    public function update($id)
    {
        $userId = $_SESSION['user_id'];
        $name = $_POST['name'] ?? '';
        $body = $_POST['body'] ?? '';

        if (empty($name) || empty($body)) {
            $error = "Name and Body are required";
            $template = ['id' => $id, 'name' => $name, 'body' => $body];
            require_once __DIR__ . '/../../views/templates/edit.php';
            return;
        }

        try {
            $stmt = $this->db->prepare("UPDATE templates SET name = :name, body = :body WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':body', $body);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $_SESSION['success'] = "Template updated successfully";
            header("Location: /templates");
            exit;
        } catch (PDOException $e) {
            $error = "Failed to update template: " . $e->getMessage();
            $template = ['id' => $id, 'name' => $name, 'body' => $body];
            require_once __DIR__ . '/../../views/templates/edit.php';
        }
    }

    public function delete($id)
    {
        $userId = $_SESSION['user_id'];
        try {
            $stmt = $this->db->prepare("DELETE FROM templates WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $_SESSION['success'] = "Template deleted successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to delete template: " . $e->getMessage();
        }

        header("Location: /templates");
        exit;
    }

    public function getJson($id)
    {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        $stmt = $this->db->prepare("SELECT body FROM templates WHERE id = :id AND user_id = :user_id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $template = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($template) {
            echo json_encode(['status' => 'success', 'body' => $template['body']]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Template not found']);
        }
        exit;
    }
}
