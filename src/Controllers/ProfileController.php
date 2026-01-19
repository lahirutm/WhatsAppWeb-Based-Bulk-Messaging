<?php

class ProfileController
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

    public function showChangePassword()
    {
        require_once __DIR__ . '/../../views/profile/password.php';
    }

    public function updatePassword()
    {
        $userId = $_SESSION['user_id'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match";
            require_once __DIR__ . '/../../views/profile/password.php';
            return;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $success = "Password updated successfully!";
        require_once __DIR__ . '/../../views/profile/password.php';
    }
}
