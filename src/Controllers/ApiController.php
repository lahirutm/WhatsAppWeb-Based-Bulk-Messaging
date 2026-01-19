<?php

class ApiController
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function send()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
            return;
        }

        $apiKey = $input['api_key'] ?? '';
        $sender = $input['sender'] ?? '';
        $number = $input['number'] ?? '';
        $message = $input['message'] ?? '';

        if (empty($apiKey) || empty($sender) || empty($number) || empty($message)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            return;
        }

        // Validate API Key and Sender
        $stmt = $this->db->prepare("SELECT id, user_id FROM instances WHERE api_key = :api_key AND phone = :phone AND status = 'connected'");
        $stmt->bindParam(':api_key', $apiKey);
        $stmt->bindParam(':phone', $sender);
        $stmt->execute();

        $instance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$instance) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid API Key, Sender Number, or Instance not connected']);
            return;
        }

        try {
            // Start transaction
            $this->db->beginTransaction();

            // Check user credits
            $stmt = $this->db->prepare("SELECT message_credits FROM users WHERE id = :user_id FOR UPDATE");
            $stmt->bindParam(':user_id', $instance['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user['message_credits'] < 1) {
                $this->db->rollBack();
                http_response_code(402);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Insufficient credits',
                    'credits_available' => $user['message_credits'],
                    'credits_required' => 1
                ]);
                return;
            }

            // Deduct credit
            $stmt = $this->db->prepare("UPDATE users SET message_credits = message_credits - 1 WHERE id = :user_id");
            $stmt->bindParam(':user_id', $instance['user_id']);
            $stmt->execute();

            $stmt = $this->db->prepare("INSERT INTO messages (user_id, instance_id, phone, body, status, is_api) VALUES (:user_id, :instance_id, :phone, :body, 'pending', 1)");
            $stmt->bindParam(':user_id', $instance['user_id']);
            $stmt->bindParam(':instance_id', $instance['id']);
            $stmt->bindParam(':phone', $number);
            $stmt->bindParam(':body', $message);
            $stmt->execute();

            $this->db->commit();

            echo json_encode([
                'status' => 'success',
                'message' => 'Message queued successfully',
                'message_id' => $this->db->lastInsertId(),
                'credits_remaining' => $user['message_credits'] - 1
            ]);
        } catch (PDOException $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
