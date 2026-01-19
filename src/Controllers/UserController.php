<?php

class UserController
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
        $role = $_SESSION['role'];

        // Clients cannot access user management
        if ($role === 'client') {
            header("Location: /dashboard");
            exit;
        }

        $query = "SELECT * FROM users";
        $params = [];

        if ($role !== 'super_user') {
            $query .= " WHERE created_by = :created_by";
            $params[':created_by'] = $_SESSION['user_id'];
        }

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/users/index.php';
    }

    public function create()
    {
        $role = $_SESSION['role'];
        if ($role === 'client') {
            header("Location: /dashboard");
            exit;
        }
        require_once __DIR__ . '/../../views/users/create.php';
    }

    public function store()
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Permission Checks
        $allowed = false;
        if ($currentUserRole === 'super_user') {
            $allowed = true;
        } elseif ($currentUserRole === 'administrator') {
            if ($role === 'client' || $role === 'reseller') {
                $allowed = true;
            }
        } elseif ($currentUserRole === 'reseller') {
            if ($role === 'client') {
                $allowed = true;
            }
        }

        if (!$allowed) {
            $error = "You do not have permission to create a user with role: $role";
            require_once __DIR__ . '/../../views/users/create.php';
            return;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, role, created_by) VALUES (:username, :password_hash, :role, :created_by)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':created_by', $_SESSION['user_id']);
            $stmt->execute();

            header("Location: /users");
            exit;
        } catch (PDOException $e) {
            $error = "Username already exists";
            require_once __DIR__ . '/../../views/users/create.php';
        }
    }
    public function toggleStatus($id)
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        // Fetch user to check permissions
        $stmt = $this->db->prepare("SELECT role, created_by, is_enabled FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users");
            exit;
        }

        // Permission Logic (Same as create/store)
        $allowed = false;
        if ($currentUserRole === 'super_user') {
            $allowed = true;
        } elseif ($currentUserRole === 'administrator') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        } elseif ($currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            // Flash message could be added here
            header("Location: /users");
            exit;
        }

        $newStatus = $user['is_enabled'] ? 0 : 1;
        $stmt = $this->db->prepare("UPDATE users SET is_enabled = :is_enabled WHERE id = :id");
        $stmt->bindParam(':is_enabled', $newStatus);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: /users");
        exit;
    }

    public function editPassword($id)
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        // Fetch user to check permissions
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users");
            exit;
        }

        // Permission Logic
        $allowed = false;
        if ($currentUserRole === 'super_user') {
            $allowed = true;
        } elseif ($currentUserRole === 'administrator') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        } elseif ($currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            header("Location: /users");
            exit;
        }

        require_once __DIR__ . '/../../views/users/password.php';
    }

    public function updatePassword($id)
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Fetch user to check permissions
        $stmt = $this->db->prepare("SELECT role, created_by FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users");
            exit;
        }

        // Permission Logic
        $allowed = false;
        if ($currentUserRole === 'super_user') {
            $allowed = true;
        } elseif ($currentUserRole === 'administrator') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        } elseif ($currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            header("Location: /users");
            exit;
        }

        $stmt = $this->db->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: /users");
        exit;
    }

    public function editCredits($id)
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        // Fetch user to check permissions
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users");
            exit;
        }

        // Permission Logic
        $allowed = false;
        if ($currentUserRole === 'super_user') {
            $allowed = true;
        } elseif ($currentUserRole === 'administrator') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        } elseif ($currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            header("Location: /users");
            exit;
        }

        require_once __DIR__ . '/../../views/users/credits.php';
    }

    public function updateCredits($id)
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        $action = $_POST['action']; // 'add' or 'set'
        $amount = (int) $_POST['amount'];

        // Fetch user to check permissions
        $stmt = $this->db->prepare("SELECT role, created_by, message_credits FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header("Location: /users");
            exit;
        }

        // Permission Logic
        $allowed = false;
        if ($currentUserRole === 'super_user') {
            $allowed = true;
        } elseif ($currentUserRole === 'administrator') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        } elseif ($currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            header("Location: /users");
            exit;
        }

        if ($action === 'add') {
            $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits + :amount WHERE id = :id");
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } elseif ($action === 'set') {
            $stmt = $this->db->prepare("UPDATE users SET message_credits = :amount WHERE id = :id");
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }

        header("Location: /users");
        exit;
    }
}
