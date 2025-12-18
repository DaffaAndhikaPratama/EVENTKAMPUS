<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'event_organizer' && $_SESSION['role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit;
}

$event_id = (int)$_GET['id'];
$user_id  = $_SESSION['user_id']; 

if ($_SESSION['role'] == 'admin') {
    $stmt = $conn->prepare("SELECT poster FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
} else {
    $stmt = $conn->prepare("SELECT poster FROM events WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
}

$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if ($event) {
    if (!empty($event['poster'])) {
        $path = __DIR__ . '/../assets/images/poster/' . $event['poster']; 
        
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $conn->query("DELETE FROM event_registrations WHERE event_id = $event_id");
    
    $del = $conn->prepare("DELETE FROM events WHERE id = ?");
    $del->bind_param("i", $event_id);
    
    if ($del->execute()) {
        $_SESSION['success_message'] = "Event berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus event dari database.";
    }

} else {
    $_SESSION['error_message'] = "Akses ditolak atau event tidak ditemukan.";
}

header("Location: " . BASE_URL . "/pages/dashboard.php");
exit;
?>