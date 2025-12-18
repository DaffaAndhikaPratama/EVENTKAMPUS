<?php
require_once __DIR__ . '/../classes/AnalyticsService.php';
$analytics = new AnalyticsService();

$adm_tot = 0;
$adm_act = 0;
$adm_roles = ['admin' => 0, 'event_organizer' => 0, 'mahasiswa' => 0];
$adm_trend = array_fill(1, 12, 0);

if ($role == 'admin') {
    $adm_tot = $analytics->getTotalUsers();
    $adm_act = $analytics->getActiveUsers();

    $roleStats = $analytics->getUserRoleComposition();
    foreach ($roleStats as $r) {
        if (isset($adm_roles[$r['role']]))
            $adm_roles[$r['role']] = $r['t'];
    }

    $adm_trend = $analytics->getMonthlyUserGrowth($year);
}

$eo_tot_ev = 0;
$eo_tot_part = 0;
$eo_chart_event = ['labels' => [], 'data' => []];
$eo_chart_cat = ['labels' => [], 'data' => []];

if ($role == 'event_organizer') {
    $eventStats = $analytics->getEventParticipantTrends($uid);
    $eo_chart_event['labels'] = $eventStats['labels'];
    $eo_chart_event['data'] = $eventStats['data'];
    $eo_tot_part = $eventStats['total_participants'];

    $catStats = $analytics->getCategoryDistributionByOrganizer($uid);
    $eo_chart_cat['labels'] = $catStats['labels'];
    $eo_chart_cat['data'] = $catStats['data'];

    $eo_tot_ev = $analytics->getTotalEventsByOrganizer($uid);
}

$mhs_trend = array_fill(1, 12, 0);
$mhs_stat = ['confirmed' => 0, 'pending' => 0, 'rejected' => 0];

if ($role == 'mahasiswa') {
    $mhs_trend = $analytics->getMonthlyEventRegistrations($year, $uid);
    $mhs_stat = $analytics->getRegistrationStatusStats($uid);
}

$created = null;
$joined = null; 

if ($role == 'mahasiswa') {
    $stmt = $conn->prepare("SELECT e.*, r.registered_at, r.status, e.certificate_link FROM event_registrations r JOIN events e ON r.event_id=e.id WHERE r.user_id=? ORDER BY r.registered_at DESC");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $joined = $stmt->get_result();
}

if ($role != 'mahasiswa') {
    if ($role == 'admin') {
        $stmt = $conn->prepare("SELECT * FROM events ORDER BY created_at DESC");
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("SELECT * FROM events WHERE user_id=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
    }
    $created = $stmt->get_result();
}
?>