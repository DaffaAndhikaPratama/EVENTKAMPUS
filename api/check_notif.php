<?php
session_start();
require_once __DIR__ . '/../config/database.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$q_pref = $conn->query("SELECT notify_web FROM users WHERE id = $user_id");
$pref = $q_pref->fetch_assoc();

if ($pref && $pref['notify_web'] == 0) {
    echo json_encode(['status' => 'disabled']); 
    exit;
}

$query = "SELECT id, message, link FROM notifications 
          WHERE user_id = ? AND is_pushed = 0 AND is_read = 0 
          LIMIT 1"; 

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $notif = $result->fetch_assoc();
    
    $conn->query("UPDATE notifications SET is_pushed = 1 WHERE id = " . $notif['id']);
    
    $full_link = null;
    if ($notif['link']) {
        $full_link = $notif['link']; 
    }
    
    echo json_encode([
        'status' => 'found',
        'message' => $notif['message'],
        'link' => $full_link
    ]);
} else {
    echo json_encode(['status' => 'empty']);
}
?>