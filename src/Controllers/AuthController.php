<?php

class AuthController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function showLogin()
    {
        require_once __DIR__ . '/../../views/login.php';
    }

    public function showRegister()
    {
        require_once __DIR__ . '/../../views/register.php';
    }

    public function login()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $this->db->prepare("SELECT id, password_hash, role, is_enabled FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['is_enabled'] == 0) {
                $error = "Your account has been disabled. Please contact the administrator.";
                require_once __DIR__ . '/../../views/login.php';
                return;
            }

            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];
                header("Location: /dashboard");
                exit;
            }
        }

        $error = "Invalid username or password";
        require_once __DIR__ . '/../../views/login.php';
    }

    public function register()
    {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->execute();

            header("Location: /login");
            exit;
        } catch (PDOException $e) {
            $error = "Username already exists";
            require_once __DIR__ . '/../../views/register.php';
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: /login");
        exit;
    }
}
