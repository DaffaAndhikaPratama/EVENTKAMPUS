<?php
class User
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function verifyLogin($email, $token)
    {
        $stmt = $this->conn->prepare("SELECT id, name, role FROM users WHERE email = ? AND verification_token = ?");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            $clear = $this->conn->prepare("UPDATE users SET verification_token = NULL WHERE id = ?");
            $clear->bind_param("i", $user['id']);
            $clear->execute();

            return true;
        }

        return false;
    }
}
