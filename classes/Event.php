<?php
class Event
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO events (user_id, title, category, event_type, event_date, location, zoom_link, price, description, poster, payment_info_bank, payment_info_ewallet, participants, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

        $stmt->bind_param(
            "issssssissss",
            $data['user_id'],
            $data['title'],
            $data['category'],
            $data['event_type'],
            $data['event_date'],
            $data['location'],
            $data['zoom_link'],
            $data['price'],
            $data['description'],
            $data['poster'],
            $data['bank_info'],
            $data['ewallet_info']
        );

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }

        return false;
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE events SET title=?, category=?, event_type=?, event_date=?, location=?, zoom_link=?, price=?, description=?, poster=?, payment_info_bank=?, payment_info_ewallet=? WHERE id=?");

        $stmt->bind_param(
            "ssssssissssi",
            $data['title'],
            $data['category'],
            $data['event_type'],
            $data['event_date'],
            $data['location'],
            $data['zoom_link'],
            $data['price'],
            $data['description'],
            $data['poster'],
            $data['bank_info'],
            $data['ewallet_info'],
            $id
        );

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function updateParticipantStatus($registrationId, $status)
    {
        $stmt = $this->conn->prepare("UPDATE event_registrations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $registrationId);
        return $stmt->execute();
    }
}
