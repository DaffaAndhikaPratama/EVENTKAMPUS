<?php
require_once __DIR__ . '/Database.php';

class Notification
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Create a new notification log in database
     * @param int $userId ID User
     * @param string $message Pesan Notifikasi
     * @param string $type 'email', 'push', 'web'
     * @param string $status 'sent', 'failed'
     * @param string|null $link Optional Link
     */
    public function log($userId, $message, $type, $status, $link = null)
    {
        $stmt = $this->conn->prepare("INSERT INTO notifications (user_id, message, type, status, link, created_at, is_read, is_pushed) VALUES (?, ?, ?, ?, ?, NOW(), 0, 0)");

        $linkVal = $link ?? '';

        $stmt->bind_param("issss", $userId, $message, $type, $status, $linkVal);
        return $stmt->execute();
    }

    public function getUserIdByEmail($email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return $row['id'];
        }
        return null; 
    }
}
