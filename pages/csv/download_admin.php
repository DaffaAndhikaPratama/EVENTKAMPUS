<?php
ob_start();
session_start();
require_once __DIR__ . '/../../config/database.php';

if (isset($conn))
    $conn->set_charset('utf8mb4');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Akses ditolak. Khusus Admin.");
}

$year = date('Y');
$month = date('m');
$monthName = date('F Y');

if (ob_get_length())
    ob_end_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Laporan_Admin_' . date('Y-m-d') . '.csv"');

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

outputCSV(["KELOMPOK DATA", "METRIK / ITEM", "DETAIL (STATUS/INFO)", "JUMLAH / NILAI", "KETERANGAN TAMBAHAN"]);

require_once __DIR__ . '/../../classes/AnalyticsService.php';
$analytics = new AnalyticsService();

$user_total = $analytics->getTotalUsers();
outputCSV(['Statistik User', 'Jumlah Total User', 'Semua Akun', $user_total, '-']);

$user_active = $analytics->getActiveUsers();
outputCSV(['Statistik User', 'Jumlah User Aktif', 'Terverifikasi', $user_active, '-']);

$roles = $analytics->getUserRoleComposition();
foreach ($roles as $r) {
    $rName = ucfirst($r['role'] == 'event_organizer' ? 'Event Organizer' : $r['role']);
    outputCSV(['Komposisi Role', 'Jumlah User', $rName, $r['t'], '-']);
}

$event_active = $analytics->getActiveEvents();
outputCSV(['Statistik Event', 'Event Aktif', 'Belum Terlaksana', $event_active, '-']);

$event_past = $analytics->getPastEvents();
outputCSV(['Statistik Event', 'Event Selesai', 'Lewat Tanggal', $event_past, '-']);

$event_month_count = $analytics->getNewEventsThisMonth();
outputCSV(['Statistik Event', 'Event Baru', "Dibuat Bulan Ini ($monthName)", $event_month_count, '-']);

$events_exec_month = $analytics->getEventsExecutedThisMonth();
$event_month_exec_count = count($events_exec_month);
outputCSV(['Statistik Event', 'Event Pelaksanaan Bulan Ini', $monthName, $event_month_exec_count, '-']);

$cats = $analytics->getCategoryDistribution();
foreach ($cats as $c) {
    outputCSV(['Statistik Kategori', 'Jumlah Event', $c['category'], $c['t'], '-']);
}

$popular = $analytics->getMostPopularEvents();
foreach ($popular as $p) {
    outputCSV(['Statistik Populer', 'Event Diminati', $p['title'], $p['participants'] . ' Peserta', 'Kat: ' . $p['category']]);
}

$new_users = $analytics->getRecentUsers();
foreach ($new_users as $u) {
    outputCSV([
        'Daftar User Terbaru',
        $u['name'],
        ucfirst($u['role']),
        '-',
        'Join: ' . date('d-m-Y', strtotime($u['created_at']))
    ]);
}

$all_events = $analytics->getAllEvents();
foreach ($all_events as $row) {
    $status = (strtotime($row['event_date']) >= time()) ? 'Aktif' : 'Lewat';
    outputCSV([
        'Data Semua Event',
        $row['title'],
        $status,
        $row['participants'],
        'Tgl: ' . date('d-m-Y', strtotime($row['event_date'])) . ' | Kat: ' . $row['category']
    ]);
}
?>