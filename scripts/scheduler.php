<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../config/database.php';
}
if (!function_exists('kirimEmail')) {
    require_once __DIR__ . '/../config/email_helper.php';
}

echo "--- Scheduler Start: " . date('Y-m-d H:i:s') . " ---\n";

$three_hours_later = date('Y-m-d H:i:s', strtotime('+3 hours'));
$now = date('Y-m-d H:i:s');

$sql = "SELECT * FROM events WHERE event_date <= '$three_hours_later' AND event_date > '$now' AND reminder_sent = 0";
$events = $conn->query($sql);

if ($events->num_rows > 0) {
    while ($ev = $events->fetch_assoc()) {
        echo "Processing Event ID: " . $ev['id'] . " - " . $ev['title'] . "\n";

        $parts = $conn->query("SELECT u.name, u.email FROM event_registrations r JOIN users u ON r.user_id = u.id WHERE r.event_id = {$ev['id']} AND r.status = 'confirmed'");
        while ($p = $parts->fetch_assoc()) {
            $sub = "REMINDER: Event {$ev['title']} Segera Dimulai!";
            $bdy = "<h3>Halo, {$p['name']}</h3>
                    <p>Event <b>{$ev['title']}</b> akan dimulai dalam waktu kurang dari 3 jam.</p>
                    <p>Waktu: " . date('d M Y, H:i', strtotime($ev['event_date'])) . "</p>";

            if ($ev['event_type'] == 'online' && !empty($ev['zoom_link'])) {
                $bdy .= "<p><strong>Link Zoom:</strong> <a href='{$ev['zoom_link']}'>{$ev['zoom_link']}</a></p>";
            } elseif ($ev['event_type'] == 'offline') {
                $bdy .= "<p><strong>Lokasi:</strong> {$ev['location']}</p>";
            }

            $bdy .= "<p>Siapkan diri Anda!</p>";

            kirimEmail($p['email'], $p['name'], $sub, $bdy);
            echo " -> Sent to {$p['email']}\n";
        }

        $conn->query("UPDATE events SET reminder_sent = 1 WHERE id = {$ev['id']}");
    }
} else {
    echo "No events need reminder.\n";
}

$sql_eo = "SELECT e.*, u.email as eo_email, u.name as eo_name 
           FROM events e JOIN users u ON e.user_id = u.id 
           WHERE e.event_type = 'online' AND (e.zoom_link IS NULL OR e.zoom_link = '') 
           AND e.event_date <= '$three_hours_later' AND e.event_date > '$now'";

$events_eo = $conn->query($sql_eo);
if ($events_eo->num_rows > 0) {
    while ($ev = $events_eo->fetch_assoc()) {
        echo "ALERT EO: Event ID {$ev['id']} missing zoom link!\n";

        $sub = "URGENT: Masukkan Link Zoom untuk {$ev['title']}";
        $bdy = "<h3>Halo, {$ev['eo_name']}</h3>
                <p>Event Anda <b>{$ev['title']}</b> akan dimulai dalam 3 jam, tetapi <b>Link Zoom belum diisi!</b></p>
                <p>Segera login dan edit event Anda untuk memasukkan Link Zoom agar peserta dapat bergabung.</p>
                <p><a href='" . BASE_URL . "/event_management/edit.php?id={$ev['id']}'>Edit Event Sekarang</a></p>";

        kirimEmail($ev['eo_email'], $ev['eo_name'], $sub, $bdy);
    }
}

$one_month_ago = date('Y-m-d H:i:s', strtotime('-1 month'));
$sql_del = "SELECT id, title, poster, certificate_link FROM events WHERE event_date < '$one_month_ago'";
$old_events = $conn->query($sql_del);

if ($old_events->num_rows > 0) {
    while ($oe = $old_events->fetch_assoc()) {
        echo "Deleting Old Event ID: " . $oe['id'] . " - " . $oe['title'] . "\n";

        $conn->query("DELETE FROM event_registrations WHERE event_id = {$oe['id']}");

        if (!empty($oe['poster']) && file_exists(__DIR__ . "/../assets/images/poster/" . $oe['poster'])) {
            unlink(__DIR__ . "/../assets/images/poster/" . $oe['poster']);
        }

        if ($conn->query("DELETE FROM events WHERE id = {$oe['id']}")) {
            echo " -> DELETED successfully.\n";
        } else {
            echo " -> FAILED to delete: " . $conn->error . "\n";
        }
    }
} else {
    echo "No old events to delete.\n";
}

echo "--- Scheduler Finish ---\n";
?>