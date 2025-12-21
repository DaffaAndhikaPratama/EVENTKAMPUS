<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Event Kampus' ?></title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/node_modules/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand text-primary fw-bold" href="<?= BASE_URL ?>/index.php">
                <i class="bi bi-calendar-event"></i> EVENT<span class="text-dark">KAMPUS</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/index.php">Beranda</a>
                    </li>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/index.php#events">Jelajahi</a>
                    </li>
                    <li class="nav-item"><a class="nav-link px-3" href="<?= BASE_URL ?>/pages/info_tips.php">Info &
                            Tips</a></li>
                </ul>

                <ul class="navbar-nav gap-2 align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>

                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link fw-bold text-primary" href="<?= BASE_URL ?>/admin/manage_events.php">
                                    <i class="bi bi-calendar-check-fill"></i> Kelola Event
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bold text-success" href="<?= BASE_URL ?>/admin/manage_articles.php">
                                    <i class="bi bi-newspaper"></i> Kelola Artikel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link fw-bold text-danger" href="<?= BASE_URL ?>/admin/manage_users.php">
                                    <i class="bi bi-shield-lock-fill"></i> Kelola User
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $uid = $_SESSION['user_id'];
                        $notif_count = 0;
                        if (isset($conn)) {
                            $q = $conn->query("SELECT COUNT(*) as u FROM notifications WHERE user_id=$uid AND is_read=0");
                            if ($q)
                                $notif_count = $q->fetch_assoc()['u'];
                        }
                        ?>
                        <li class="nav-item me-2 position-relative">
                            <a class="nav-link text-secondary" href="<?= BASE_URL ?>/pages/notifications.php">
                                <i class="bi bi-bell-fill fs-5"></i>
                                <?php if ($notif_count > 0): ?>
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        style="font-size:0.6rem">
                                        <?= $notif_count > 9 ? '9+' : $notif_count ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-outline-light text-dark border-0" href="#"
                                id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5 align-middle me-1"></i>
                                <span class="fw-bold align-middle"><?= explode(' ', $_SESSION['name'])[0] ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/pages/dashboard.php"><i
                                            class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/user_profile/profile.php"><i
                                            class="bi bi-person-badge me-2 text-info"></i> Profil Saya</a></li>
                                <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/user_profile/settings.php"><i
                                            class="bi bi-gear me-2 text-secondary"></i> Pengaturan</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item py-2 text-danger fw-bold"
                                        href="<?= BASE_URL ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>
                                        Keluar</a></li>
                            </ul>
                        </li>

                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-outline-secondary"
                                href="<?= BASE_URL ?>/auth/login.php">Masuk</a></li>
                        <li class="nav-item"><a class="btn btn-primary" href="<?= BASE_URL ?>/auth/register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
                Notification.requestPermission();
            }

            function checkNewNotifications() {
                <?php if (isset($_SESSION['user_id'])): ?>
                    fetch('<?= BASE_URL ?>/api/check_notif.php')
                        .then(r => r.json()).then(d => {
                            if (d.status === 'found') {
                                if (Notification.permission === "granted") {
                                    const n = new Notification("Event Kampus", {
                                        body: d.message,
                                        icon: "<?= BASE_URL ?>/assets/images/logo.png"
                                    });
                                    n.onclick = function () {
                                        window.focus();
                                        if (d.link) window.location.href = d.link;
                                        n.close();
                                    };
                                }
                            }
                        }).catch(e => console.error(e));
                <?php endif; ?>
            }

            setInterval(checkNewNotifications, 5000);
        });
    </script>