<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/Event.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'event_organizer' && $_SESSION['role'] !== 'admin') || !isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit;
}

$event_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$eventObj = new Event();
$event = $eventObj->getById($event_id);

if ($event) {
    if ($_SESSION['role'] !== 'admin' && $event['user_id'] != $user_id) {
        $_SESSION['error_message'] = "Anda tidak memiliki akses menghapus event ini.";
        header("Location: " . BASE_URL . "/pages/dashboard.php");
        exit;
    }

    if (!empty($event['poster'])) {
        $path = __DIR__ . '/../assets/images/poster/' . $event['poster'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    if ($eventObj->delete($event_id)) {
        $_SESSION['success_message'] = "Event berhasil dihapus.";
    } else {
        $_SESSION['error_message'] = "Gagal menghapus event.";
    }

} else {
    $_SESSION['error_message'] = "Event tidak ditemukan.";
}

header("Location: " . BASE_URL . "/pages/dashboard.php");
exit;
?>