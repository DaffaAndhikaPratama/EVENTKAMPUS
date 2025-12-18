<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email_helper.php';

date_default_timezone_set('Asia/Jakarta');

echo "<h3><i class='bi bi-arrow-repeat'></i> Memulai Proses Reminder...</h3><hr>";


$sql = "SELECT id, title, event_date, location, poster 
        FROM events 
        WHERE event_date > NOW() 
        AND event_date <= DATE_ADD(NOW(), INTERVAL 1 DAY) 
        AND is_reminder_sent = 0";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($event = $result->fetch_assoc()) {
        $event_id = $event['id'];
        $tgl_event = date('d F Y, Pukul H:i', strtotime($event['event_date']));

        echo "<b>Memproses Event:</b> {$event['title']} (ID: $event_id)<br>";

        $sql_peserta = "SELECT u.name, u.email 
                        FROM event_registrations r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.event_id = $event_id AND r.status = 'confirmed'";

        $res_peserta = $conn->query($sql_peserta);

        if ($res_peserta->num_rows > 0) {
            $count_sent = 0;
            while ($p = $res_peserta->fetch_assoc()) {

                $subject = "<i class='bi bi-bell-fill'></i> Reminder: Besok! {$event['title']}";
                $body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd;'>
                        <h2 style='color: #0d6efd;'>Halo, {$p['name']}! </h2>
                        <p>Kami ingin mengingatkan bahwa event yang kamu nantikan akan dimulai besok.</p>
                        
                        <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0;'>
                            <h3 style='margin-top:0;'>{$event['title']}</h3>
                            <p>
                                <i class='bi bi-calendar-event'></i> <b>Waktu:</b> $tgl_event WIB<br>
                                <i class='bi bi-geo-alt-fill'></i> <b>Lokasi:</b> {$event['location']}
                            </p>
                        </div>

                        <p>Pastikan kamu hadir tepat waktu ya! Sampai jumpa di lokasi.</p>
                        <br>
                        <small style='color: #888;'>Email ini dikirim otomatis oleh sistem EventKampus.</small>
                    </div>
                ";

                if (kirimEmail($p['email'], $p['name'], $subject, $body)) {
                    echo "<i class='bi bi-check-circle-fill'></i> Email terkirim ke: {$p['email']}<br>";
                    $count_sent++;
                } else {
                    echo "<i class='bi bi-x-circle-fill'></i> GAGAL kirim ke: {$p['email']}<br>";
                }
            }
            echo "Total terkirim: $count_sent email.<br>";
        } else {
            echo "Belum ada peserta terdaftar/confirmed.<br>";
        }

        $conn->query("UPDATE events SET is_reminder_sent = 1 WHERE id = $event_id");
        echo "<hr>";
    }
} else {
    echo "Tidak ada event H-1 yang perlu diingatkan saat ini.<br>";
}

echo "Done.";
?>