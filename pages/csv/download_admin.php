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

$user_total = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
outputCSV(['Statistik User', 'Jumlah Total User', 'Semua Akun', $user_total, '-']);

$user_active = $conn->query("SELECT COUNT(*) as t FROM users WHERE is_verified=1")->fetch_assoc()['t'];
outputCSV(['Statistik User', 'Jumlah User Aktif', 'Terverifikasi', $user_active, '-']);

$q = $conn->query("SELECT role, COUNT(*) as t FROM users GROUP BY role");
while ($r = $q->fetch_assoc()) {
    $rName = ucfirst($r['role'] == 'event_organizer' ? 'Event Organizer' : $r['role']);
    outputCSV(['Komposisi Role', 'Jumlah User', $rName, $r['t'], '-']);
}

$event_active = $conn->query("SELECT COUNT(*) as t FROM events WHERE event_date >= CURRENT_DATE")->fetch_assoc()['t'];
outputCSV(['Statistik Event', 'Event Aktif', 'Belum Terlaksana', $event_active, '-']);

$event_past = $conn->query("SELECT COUNT(*) as t FROM events WHERE event_date < CURRENT_DATE")->fetch_assoc()['t'];
outputCSV(['Statistik Event', 'Event Selesai', 'Lewat Tanggal', $event_past, '-']);

$event_month_count = $conn->query("SELECT COUNT(*) as t FROM events WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'")->fetch_assoc()['t'];
outputCSV(['Statistik Event', 'Event Baru', "Dibuat Bulan Ini ($monthName)", $event_month_count, '-']);

$q_ev_month = $conn->query("SELECT title, event_date, participants FROM events WHERE MONTH(event_date) = '$month' AND YEAR(event_date) = '$year'");
$event_month_exec_count = $q_ev_month->num_rows;
outputCSV(['Statistik Event', 'Event Pelaksanaan Bulan Ini', $monthName, $event_month_exec_count, '-']);

$q_cat = $conn->query("SELECT category, COUNT(*) as t FROM events GROUP BY category");
while ($c = $q_cat->fetch_assoc()) {
    outputCSV(['Statistik Kategori', 'Jumlah Event', $c['category'], $c['t'], '-']);
}

$q_new = $conn->query("SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10");
while ($u = $q_new->fetch_assoc()) {
    outputCSV([
        'Daftar User Terbaru',
        $u['name'],
        ucfirst($u['role']),
        '-',
        'Join: ' . date('d-m-Y', strtotime($u['created_at']))
    ]);
}

if ($event_month_exec_count > 0) {
    $q_ev_month_list = $conn->query("SELECT e.title, u.name as organizer, e.event_date, e.category, e.participants 
                                    FROM events e JOIN users u ON e.user_id = u.id 
                                    WHERE MONTH(e.event_date) = '$month' AND YEAR(e.event_date) = '$year'
                                    ORDER BY e.event_date ASC");
    while ($row = $q_ev_month_list->fetch_assoc()) {
        outputCSV([
            'Rincian Event Bulan Ini',
            $row['title'],
            date('d-m-Y', strtotime($row['event_date'])),
            $row['participants'] . ' Peserta',
            'Oleh: ' . $row['organizer'] . ' | Kat: ' . $row['category']
        ]);
    }
} else {
    outputCSV(['Rincian Event Bulan Ini', 'Tidak ada event bulan ini', '-', '-', '-']);
}

$q_all = $conn->query("SELECT e.title, e.event_date, e.category, e.participants FROM events e ORDER BY e.event_date DESC");
while ($row = $q_all->fetch_assoc()) {
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