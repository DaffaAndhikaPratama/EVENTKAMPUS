<?php

$adm_tot = 0;
$adm_act = 0;
$adm_roles = ['admin' => 0, 'event_organizer' => 0, 'mahasiswa' => 0];
$adm_trend = array_fill(1, 12, 0);

if ($role == 'admin') {
    $adm_tot = $conn->query("SELECT COUNT(*) as t FROM users")->fetch_assoc()['t'];
    $adm_act = $conn->query("SELECT COUNT(*) as t FROM users WHERE is_verified=1")->fetch_assoc()['t'];
    $q = $conn->query("SELECT role, COUNT(*) as t FROM users GROUP BY role");
    while ($r = $q->fetch_assoc()) {
        if (isset($adm_roles[$r['role']]))
            $adm_roles[$r['role']] = $r['t'];
    }
    $stmt = $conn->prepare("SELECT MONTH(created_at) as b, COUNT(*) as t FROM users WHERE YEAR(created_at)=? GROUP BY MONTH(created_at)");
    $stmt->bind_param("i", $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc())
        $adm_trend[$r['b']] = $r['t'];
}

$eo_tot_ev = 0;
$eo_tot_part = 0;
$eo_chart_event = ['labels' => [], 'data' => []];
$eo_chart_cat = ['labels' => [], 'data' => []];

if ($role == 'event_organizer') {
    $stmt = $conn->prepare("SELECT title, participants FROM events WHERE user_id=? ORDER BY event_date DESC");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($r = $res->fetch_assoc()) {
        $raw_title = $r['title'];
        $judul = mb_strlen($raw_title, 'UTF-8') > 15 ? mb_substr($raw_title, 0, 15, 'UTF-8') . '...' : $raw_title;

        $eo_chart_event['labels'][] = $judul;
        $eo_chart_event['data'][] = (int) $r['participants'];
        $eo_tot_part += $r['participants'];
    }

    $eo_chart_event['labels'] = array_reverse($eo_chart_event['labels']);
    $eo_chart_event['data'] = array_reverse($eo_chart_event['data']);


    $stmt = $conn->prepare("SELECT category, COUNT(*) as t FROM events WHERE user_id=? GROUP BY category");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $eo_chart_cat['labels'][] = $r['category'];
        $eo_chart_cat['data'][] = $r['t'];
    }

    $q_ev = $conn->query("SELECT COUNT(*) as t FROM events WHERE user_id=$uid");
    $eo_tot_ev = $q_ev->fetch_assoc()['t'];
}

$mhs_trend = array_fill(1, 12, 0);
$mhs_stat = ['confirmed' => 0, 'pending' => 0, 'rejected' => 0];

if ($role == 'mahasiswa') {
    $stmt = $conn->prepare("SELECT MONTH(registered_at) as b, COUNT(*) as t FROM event_registrations WHERE user_id=? AND YEAR(registered_at)=? GROUP BY MONTH(registered_at)");
    $stmt->bind_param("ii", $uid, $year);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc())
        $mhs_trend[$r['b']] = $r['t'];
    $stmt = $conn->prepare("SELECT status, COUNT(*) as t FROM event_registrations WHERE user_id=? GROUP BY status");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc())
        $mhs_stat[$r['status']] = $r['t'];
}

$stmt = $conn->prepare("SELECT e.*, r.registered_at, r.status, e.certificate_link FROM event_registrations r JOIN events e ON r.event_id=e.id WHERE r.user_id=? ORDER BY r.registered_at DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$joined = $stmt->get_result();

$created = null;
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