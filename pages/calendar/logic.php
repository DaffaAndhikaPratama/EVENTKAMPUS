<?php

$calendar_connected = false;
$calendar_events = [];

$selected_month = isset($_GET['month']) ? (int) $_GET['month'] : date('n');
$selected_year = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');

$prev_month = $selected_month - 1;
$prev_year = $selected_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $selected_month + 1;
$next_year = $selected_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$q_cal = $conn->query("SELECT google_refresh_token FROM users WHERE id = $uid");
if ($r_cal = $q_cal->fetch_assoc()) {
    if (!empty($r_cal['google_refresh_token'])) {
        $calendar_connected = true;

        try {
            $client->fetchAccessTokenWithRefreshToken($r_cal['google_refresh_token']);
            $calendarHelper = new CalendarHelper($client);
            $calendar_events = $calendarHelper->listUpcomingEvents(50);
        } catch (Exception $e) {
            $calendar_connected = false;
        }
    }
}
?>