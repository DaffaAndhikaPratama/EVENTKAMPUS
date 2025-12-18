<div class="p-4">
    <?php if ($calendar_connected): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <h4 class="fw-bold mb-0 text-dark"><?= date("F Y", mktime(0, 0, 0, $selected_month, 1, $selected_year)) ?>
                </h4>
                <div class="btn-group">
                    <a href="?tab=calendar&month=<?= $prev_month ?>&year=<?= $prev_year ?>"
                        class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
                    <a href="?tab=calendar&month=<?= $next_month ?>&year=<?= $next_year ?>"
                        class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
                </div>
            </div>
            <a href="https://calendar.google.com" target="_blank"
                class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"><i class="bi bi-plus-lg me-1"></i> Buka Google
                Calendar</a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div
                    style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background-color: #dee2e6; border: 1px solid #dee2e6;">

                    <?php
                    $days_of_week = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                    foreach ($days_of_week as $dow) {
                        echo "<div class='bg-light text-secondary fw-bold text-center py-2'>$dow</div>";
                    }
                    ?>

                    <?php
                    $first_day_timestamp = mktime(0, 0, 0, $selected_month, 1, $selected_year);
                    $days_in_month = date('t', $first_day_timestamp);
                    $starting_day = date('w', $first_day_timestamp); 
                
                    $events_by_date = [];
                    foreach ($calendar_events as $event) {
                        $start = $event->start->dateTime;
                        if (empty($start))
                            $start = $event->start->date;
                        $d_key = date('Y-m-d', strtotime($start));
                        $events_by_date[$d_key][] = $event;
                    }

                    for ($i = 0; $i < $starting_day; $i++) {
                        echo "<div class='bg-white' style='min-height: 100px;'></div>";
                    }

                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $current_date = sprintf('%04d-%02d-%02d', $selected_year, $selected_month, $day);
                        $is_today = ($current_date == date('Y-m-d'));
                        $day_events = isset($events_by_date[$current_date]) ? $events_by_date[$current_date] : [];

                        $bg_color = $is_today ? 'background-color: #ebf8ff;' : 'background-color: #fff;';
                        $text_class = $is_today ? 'text-primary fw-bold' : 'text-secondary';

                        echo "<div class='p-2' style='$bg_color min-height: 110px;'>";

                        echo "<div class='d-flex justify-content-end mb-2'>";
                        echo "<span class='$text_class' style='font-size: 0.9rem;'>$day</span>";
                        echo "</div>";

                        usort($day_events, function ($a, $b) {
                            $t1 = !empty($a->start->dateTime) ? strtotime($a->start->dateTime) : strtotime($a->start->date);
                            $t2 = !empty($b->start->dateTime) ? strtotime($b->start->dateTime) : strtotime($b->start->date);
                            return $t1 - $t2;
                        });

                        foreach ($day_events as $evt) {
                            $title = htmlspecialchars($evt->getSummary());
                            $link = $evt->htmlLink;
                            $time_str = "";
                            $is_all_day = empty($evt->start->dateTime);

                            if (!$is_all_day) {
                                $time_str = date('H:i', strtotime($evt->start->dateTime));
                            }

                            echo "<a href='$link' target='_blank' class='d-block badge bg-primary text-white text-decoration-none text-start text-truncate mb-1 rounded-1' style='font-weight:normal; font-size:0.75rem; padding: 4px 6px;' title='$title'>";
                            if ($time_str) {
                                echo "<span class='fw-bold me-1'>$time_str</span>";
                            }
                            echo "$title";
                            echo "</a>";
                        }
                        echo "</div>";
                    }

                    $total_cells = $days_in_month + $starting_day;
                    $remaining_cells = (7 - ($total_cells % 7)) % 7;
                    for ($i = 0; $i < $remaining_cells; $i++) {
                        echo "<div class='bg-white' style='min-height: 100px;'></div>";
                    }
                    ?>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-google fs-1 text-danger d-block mb-3"></i>
            <h5 class="fw-bold">Google Calendar Belum Terhubung</h5>
            <p class="text-muted mb-4">Hubungkan akun Anda untuk melihat jadwal event langsung di sini.</p>
            <a href="<?= BASE_URL ?>/auth/google_connect.php" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                <i class="bi bi-link-45deg me-2"></i> Hubungkan Sekarang
            </a>
        </div>
    <?php endif; ?>
</div>