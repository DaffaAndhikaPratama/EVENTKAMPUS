<?php
ob_start();
session_start();
require_once __DIR__ . '/../../config/database.php';

if (isset($conn))
    $conn->set_charset('utf8mb4');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'event_organizer') {
    die("Akses ditolak. Khusus Event Organizer.");
}

$uid = $_SESSION['user_id'];
$userName = $_SESSION['name'];

if (ob_get_length())
    ob_end_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_EO_' . date('Y-m-d') . '.csv"');

echo "\xEF\xBB\xBF";
echo "sep=;\n";

function cleanStr($str)
{
    if ($str === null)
        return '';
    $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
    return preg_replace('/[^\x20-\x7E]/', '', $clean);
}

function outputCSV($data)
{
    $line = array_map(function ($field) {
        $field = cleanStr((string) $field);
        $field = str_replace('"', '""', $field);
        if (preg_match('/[;"\n\r]/', $field) || $field === '') {
            $field = '"' . $field . '"';
        }
        return $field;
    }, $data);
    echo implode(';', $line) . "\n";
}

outputCSV(["KELOMPOK DATA", "METRIK / NAMA", "DETAIL (STATUS/INFO)", "KATEGORI", "JUMLAH / NILAI"]);

$q_ev = $conn->query("SELECT COUNT(*) as t FROM events WHERE user_id=$uid");
$tot_ev = $q_ev->fetch_assoc()['t'];
$q_part = $conn->query("SELECT SUM(participants) as t FROM events WHERE user_id=$uid");
$tot_part = $q_part->fetch_assoc()['t'] ?? 0;

outputCSV(['Ringkasan Performa', 'Total Event Dibuat', 'Sejak Bergabung', '-', $tot_ev]);
outputCSV(['Ringkasan Performa', 'Total Akumulasi Peserta', 'Semua Event', '-', $tot_part]);

$query = "SELECT 
            e.title, 
            e.event_date, 
            e.category,
            COUNT(r.id) as total_reg,
            SUM(CASE WHEN r.status = 'confirmed' THEN 1 ELSE 0 END) as total_confirmed,
            SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as total_pending,
            SUM(CASE WHEN r.status = 'rejected' THEN 1 ELSE 0 END) as total_rejected
          FROM events e
          LEFT JOIN event_registrations r ON e.id = r.event_id
          WHERE e.user_id = ?
          GROUP BY e.id
          ORDER BY e.event_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $dateFormatted = date('d-m-Y', strtotime($r['event_date']));
        $eventName = $r['title'];
        $cat = $r['category'];

        outputCSV(['Rincian Event', $eventName, $dateFormatted, 'Total Pendaftar', $r['total_reg']]);
        outputCSV(['Rincian Event', $eventName, $dateFormatted, 'Peserta Resmi (Confirmed)', $r['total_confirmed']]);

        if ($r['total_pending'] > 0)
            outputCSV(['Rincian Event', $eventName, $dateFormatted, 'Menunggu Verifikasi', $r['total_pending']]);

        if ($r['total_rejected'] > 0)
            outputCSV(['Rincian Event', $eventName, $dateFormatted, 'Ditolak', $r['total_rejected']]);
    }
} else {
    outputCSV(['Rincian Event', 'Belum ada event', '-', '-', '-']);
}

$stmt = $conn->prepare("SELECT category, COUNT(*) as t FROM events WHERE user_id=? GROUP BY category");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) {
    outputCSV(['Distribusi Kategori', $r['category'], '-', 'Jumlah Event', $r['t']]);
}
?>