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

        $stmt = $this->db->prepare("SELECT id, name, password_hash, role, is_enabled FROM users WHERE username = :username");
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
                $_SESSION['name'] = $row['name'];
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
        $name = $_POST['name'] ?? null;
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Get default package
            $stmt = $this->db->prepare("SELECT id, credits FROM packages WHERE is_default = 1 LIMIT 1");
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
            $initialCredits = $package ? $package['credits'] : 0;
            $packageId = $package ? $package['id'] : null;

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("INSERT INTO users (username, name, password_hash, message_credits) VALUES (:username, :name, :password_hash, :credits)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':credits', $initialCredits);
            $stmt->execute();
            $newUserId = $this->db->lastInsertId();

            // Log initial assignment if package exists
            if ($packageId) {
                $stmt = $this->db->prepare("INSERT INTO user_packages (user_id, package_id, assigned_by) VALUES (:user_id, :package_id, :assigned_by)");
                $stmt->bindParam(':user_id', $newUserId);
                $stmt->bindParam(':package_id', $packageId);
                // For self-registration, we'll use the new user's ID as assigned_by or a system ID. 
                // Since super_user is usually ID 1, let's check if we can find a super_user or just use the new user ID.
                $stmt->bindParam(':assigned_by', $newUserId);
                $stmt->execute();
            }

            $this->db->commit();

            header("Location: /login");
            exit;
        } catch (PDOException $e) {
            $this->db->rollBack();
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
