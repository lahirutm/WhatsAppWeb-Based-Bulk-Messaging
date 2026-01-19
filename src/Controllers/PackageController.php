<?php

class PackageController
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

    private function checkPermission()
    {
        $role = $_SESSION['role'];
        if ($role !== 'super_user' && $role !== 'administrator') {
            header("Location: /dashboard");
            exit;
        }
    }

    public function index()
    {
        $this->checkPermission();
        $stmt = $this->db->prepare("SELECT * FROM packages ORDER BY created_at DESC");
        $stmt->execute();
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/packages/index.php';
    }

    public function create()
    {
        $this->checkPermission();
        require_once __DIR__ . '/../../views/packages/create.php';
    }

    public function store()
    {
        $this->checkPermission();
        $name = $_POST['name'];
        $credits = (int) $_POST['credits'];

        $stmt = $this->db->prepare("INSERT INTO packages (name, credits) VALUES (:name, :credits)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':credits', $credits);
        $stmt->execute();

        header("Location: /packages");
        exit;
    }

    public function delete($id)
    {
        $this->checkPermission();
        $stmt = $this->db->prepare("DELETE FROM packages WHERE id = :id AND is_default = 0");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: /packages");
        exit;
    }

    public function setDefault($id)
    {
        $this->checkPermission();
        try {
            $this->db->beginTransaction();

            // Reset all defaults
            $this->db->exec("UPDATE packages SET is_default = 0");

            // Set new default
            $stmt = $this->db->prepare("UPDATE packages SET is_default = 1 WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
        }

        header("Location: /packages");
        exit;
    }
}
