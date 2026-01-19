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
        $name = $_POST['name'] ?? null;
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
            // Get default package
            $stmt = $this->db->prepare("SELECT id, credits FROM packages WHERE is_default = 1 LIMIT 1");
            $stmt->execute();
            $package = $stmt->fetch(PDO::FETCH_ASSOC);
            $initialCredits = $package ? $package['credits'] : 0;
            $packageId = $package ? $package['id'] : null;

            $this->db->beginTransaction();

            // If reseller, check and deduct credits for the default package
            if ($currentUserRole === 'reseller' && $initialCredits > 0) {
                $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :id");
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
                $reseller = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$reseller || $reseller['message_credits'] < $initialCredits) {
                    $this->db->rollBack();
                    $error = "Insufficient credits to create user with default package ($initialCredits credits required)";
                    require_once __DIR__ . '/../../views/users/create.php';
                    return;
                }

                // Deduct from reseller
                $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits - :credits WHERE id = :id");
                $stmt->bindParam(':credits', $initialCredits);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
            }

            $stmt = $this->db->prepare("INSERT INTO users (username, name, password_hash, role, created_by, message_credits) VALUES (:username, :name, :password_hash, :role, :created_by, :credits)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':created_by', $_SESSION['user_id']);
            $stmt->bindParam(':credits', $initialCredits);
            $stmt->execute();
            $newUserId = $this->db->lastInsertId();

            // Log assignment
            if ($packageId) {
                $stmt = $this->db->prepare("INSERT INTO user_packages (user_id, package_id, assigned_by) VALUES (:user_id, :package_id, :assigned_by)");
                $stmt->bindParam(':user_id', $newUserId);
                $stmt->bindParam(':package_id', $packageId);
                $stmt->bindParam(':assigned_by', $_SESSION['user_id']);
                $stmt->execute();
            }

            $this->db->commit();

            header("Location: /users");
            exit;
        } catch (PDOException $e) {
            $this->db->rollBack();
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

    public function showAssignPackage($id)
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
        } elseif ($currentUserRole === 'administrator' || $currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            header("Location: /users");
            exit;
        }

        // Fetch reseller balance if current user is reseller
        $resellerBalance = null;
        if ($currentUserRole === 'reseller') {
            $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :id");
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            $reseller = $stmt->fetch(PDO::FETCH_ASSOC);
            $resellerBalance = $reseller ? $reseller['message_credits'] : 0;
        }

        // Fetch all packages
        $stmt = $this->db->prepare("SELECT * FROM packages ORDER BY name ASC");
        $stmt->execute();
        $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch assignment history
        $stmt = $this->db->prepare("
            SELECT up.*, p.name as package_name, p.credits as package_credits, u.username as admin_name 
            FROM user_packages up 
            JOIN packages p ON up.package_id = p.id 
            JOIN users u ON up.assigned_by = u.id 
            WHERE up.user_id = :user_id 
            ORDER BY up.assigned_at DESC
        ");
        $stmt->bindParam(':user_id', $id);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/users/assign_package.php';
    }

    public function assignPackage($id)
    {
        $currentUserRole = $_SESSION['role'];
        if ($currentUserRole === 'client') {
            header("Location: /dashboard");
            exit;
        }

        $packageId = $_POST['package_id'];

        // Fetch user to check permissions
        $stmt = $this->db->prepare("SELECT created_by FROM users WHERE id = :id");
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
        } elseif ($currentUserRole === 'administrator' || $currentUserRole === 'reseller') {
            if ($user['created_by'] == $_SESSION['user_id']) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            header("Location: /users");
            exit;
        }

        // Fetch package credits
        $stmt = $this->db->prepare("SELECT credits FROM packages WHERE id = :id");
        $stmt->bindParam(':id', $packageId);
        $stmt->execute();
        $package = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($package) {
            try {
                $this->db->beginTransaction();

                // If reseller, check and deduct credits
                if ($currentUserRole === 'reseller') {
                    $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $_SESSION['user_id']);
                    $stmt->execute();
                    $reseller = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$reseller || $reseller['message_credits'] < $package['credits']) {
                        $this->db->rollBack();
                        header("Location: /users/assign-package/$id?error=insufficient_credits");
                        exit;
                    }

                    // Deduct from reseller
                    $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits - :credits WHERE id = :id");
                    $stmt->bindParam(':credits', $package['credits']);
                    $stmt->bindParam(':id', $_SESSION['user_id']);
                    $stmt->execute();
                }

                // Update client credits
                $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits + :credits WHERE id = :id");
                $stmt->bindParam(':credits', $package['credits']);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                // Log assignment
                $stmt = $this->db->prepare("INSERT INTO user_packages (user_id, package_id, assigned_by) VALUES (:user_id, :package_id, :assigned_by)");
                $stmt->bindParam(':user_id', $id);
                $stmt->bindParam(':package_id', $packageId);
                $stmt->bindParam(':assigned_by', $_SESSION['user_id']);
                $stmt->execute();

                $this->db->commit();
                header("Location: /users/assign-package/$id?success=1");
                exit;
            } catch (Exception $e) {
                $this->db->rollBack();
            }
        }

        header("Location: /users/assign-package/$id");
        exit;
    }
}
