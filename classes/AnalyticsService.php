<?php
require_once __DIR__ . '/Database.php';

class AnalyticsService
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function getTotalUsers()
    {
        return $this->conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
    }

    public function getActiveUsers()
    {
        return $this->conn->query("SELECT COUNT(*) as t FROM users WHERE is_verified=1")->fetch_assoc()['t'];
    }

    public function getUserRoleComposition()
    {
        $data = [];
        $q = $this->conn->query("SELECT role, COUNT(*) as t FROM users GROUP BY role");
        while ($r = $q->fetch_assoc()) {
            $data[] = $r;
        }
        return $data;
    }

    public function getActiveEvents()
    {
        return $this->conn->query("SELECT COUNT(*) as t FROM events WHERE event_date >= CURRENT_DATE")->fetch_assoc()['t'];
    }

    public function getPastEvents()
    {
        return $this->conn->query("SELECT COUNT(*) as t FROM events WHERE event_date < CURRENT_DATE")->fetch_assoc()['t'];
    }

    public function getNewEventsThisMonth()
    {
        $month = date('m');
        $year = date('Y');
        return $this->conn->query("SELECT COUNT(*) as t FROM events WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'")->fetch_assoc()['t'];
    }

    public function getCategoryDistribution()
    {
        $data = [];
        $q = $this->conn->query("SELECT category, COUNT(*) as t FROM events GROUP BY category");
        while ($c = $q->fetch_assoc()) {
            $data[] = $c;
        }
        return $data;
    }

    public function getEventsExecutedThisMonth()
    {
        $month = date('m');
        $year = date('Y');
        $q = $this->conn->query("SELECT title, event_date, participants FROM events WHERE MONTH(event_date) = '$month' AND YEAR(event_date) = '$year'");
        $data = [];
        while ($r = $q->fetch_assoc()) {
            $data[] = $r;
        }
        return $data;
    }

    public function getMostPopularEvents($limit = 5)
    {
        $q = $this->conn->query("SELECT title, participants, category FROM events ORDER BY participants DESC LIMIT $limit");
        $data = [];
        while ($r = $q->fetch_assoc()) {
            $data[] = $r;
        }
        return $data;
    }

    public function getRecentUsers($limit = 10)
    {
        $q = $this->conn->query("SELECT name, role, created_at FROM users ORDER BY created_at DESC LIMIT $limit");
        $data = [];
        while ($r = $q->fetch_assoc()) {
            $data[] = $r;
        }
        return $data;
    }

    public function getAllEvents()
    {
        $q = $this->conn->query("SELECT title, event_date, category, participants FROM events ORDER BY event_date DESC");
        $data = [];
        while ($r = $q->fetch_assoc()) {
            $data[] = $r;
        }
        return $data;
    }

    public function getMonthlyUserGrowth($year)
    {
        $stmt = $this->conn->prepare("SELECT MONTH(created_at) as b, COUNT(*) as t FROM users WHERE YEAR(created_at)=? GROUP BY MONTH(created_at)");
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = array_fill(1, 12, 0);
        while ($r = $res->fetch_assoc()) {
            $data[$r['b']] = $r['t'];
        }
        return $data;
    }

    public function getEventParticipantTrends($userId)
    {
        $stmt = $this->conn->prepare("SELECT title, participants FROM events WHERE user_id=? ORDER BY event_date DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        $labels = [];
        $data = [];
        $total = 0;

        while ($r = $res->fetch_assoc()) {
            $raw_title = $r['title'];
            $judul = mb_strlen($raw_title, 'UTF-8') > 15 ? mb_substr($raw_title, 0, 15, 'UTF-8') . '...' : $raw_title;

            $labels[] = $judul;
            $data[] = (int) $r['participants'];
            $total += $r['participants'];
        }

        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data),
            'total_participants' => $total
        ];
    }

    public function getCategoryDistributionByOrganizer($userId)
    {
        $stmt = $this->conn->prepare("SELECT category, COUNT(*) as t FROM events WHERE user_id=? GROUP BY category");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        $labels = [];
        $data = [];
        while ($r = $res->fetch_assoc()) {
            $labels[] = $r['category'];
            $data[] = $r['t'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    public function getTotalEventsByOrganizer($userId)
    {
        $q = $this->conn->query("SELECT COUNT(*) as t FROM events WHERE user_id=$userId");
        return $q->fetch_assoc()['t'];
    }

    public function getMonthlyEventRegistrations($year, $userId)
    {
        $stmt = $this->conn->prepare("SELECT MONTH(registered_at) as b, COUNT(*) as t FROM event_registrations WHERE user_id=? AND YEAR(registered_at)=? GROUP BY MONTH(registered_at)");
        $stmt->bind_param("ii", $userId, $year);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = array_fill(1, 12, 0);
        while ($r = $res->fetch_assoc()) {
            $data[$r['b']] = $r['t'];
        }
        return $data;
    }

    public function getRegistrationStatusStats($userId)
    {
        $stmt = $this->conn->prepare("SELECT status, COUNT(*) as t FROM event_registrations WHERE user_id=? GROUP BY status");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();

        $stats = ['confirmed' => 0, 'pending' => 0, 'rejected' => 0];
        while ($r = $res->fetch_assoc()) {
            $stats[$r['status']] = $r['t'];
        }
        return $stats;
    }
}
