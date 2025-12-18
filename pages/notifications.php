<?php
$page_title = "Notifikasi";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='" . BASE_URL . "/auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['mark_read_all'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    echo "<script>window.location='notifications.php';</script>";
    exit;
}

$result = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 50");

?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0"><i class="bi bi-bell-fill text-primary"></i> Notifikasi Anda</h2>
        <?php if ($result->num_rows > 0): ?>
            <a href="?mark_read_all=1" class="btn btn-sm btn-outline-secondary"
                onclick="return confirm('Tandai semua sebagai sudah dibaca?')">
                <i class="bi bi-check-all"></i> Tandai Semua
            </a>
        <?php endif; ?>
    </div>

    <div class="card border-0 shadow-sm">
        <style>
            .notif-item {
                transition: all 0.2s ease-in-out;
                border-left: 4px solid transparent;
                border-radius: 8px;
                margin-bottom: 8px;
                background-color: #fff;
                border: 1px solid #e9ecef;
            }

            .notif-item:hover {
                background-color: #f8f9fa;
                transform: translateY(-2px);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            }

            .notif-unread {
                background-color: #f0f7ff;
                border-left-color: #0d6efd;
            }

            .notif-icon {
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background-color: #e9ecef;
                color: #6c757d;
                flex-shrink: 0;
            }

            .notif-unread .notif-icon {
                background-color: #cfe2ff;
                color: #0d6efd;
            }

            .notif-time {
                font-size: 0.8rem;
                color: #adb5bd;
            }
        </style>

        <div class="accordion" id="notifAccordion">
            <?php if ($result->num_rows > 0):
                $i = 0;
                while ($notif = $result->fetch_assoc()):
                    $i++;
                    $is_unread = $notif['is_read'] == 0;
                    $collapseId = "collapseNotif" . $notif['id'];
                    $headingId = "headingNotif" . $notif['id'];

                    $icon = 'bi-bell-fill';
                    if (stripos($notif['message'], 'diterima') !== false || stripos($notif['message'], 'konfirmasi') !== false)
                        $icon = 'bi-check-circle-fill';
                    elseif (stripos($notif['message'], 'ditolak') !== false)
                        $icon = 'bi-x-circle-fill';
                    elseif (stripos($notif['message'], 'pembayaran') !== false)
                        $icon = 'bi-wallet2';
                    ?>
                    <div class="accordion-item mb-2 border rounded overflow-hidden shadow-sm" style="border:none;">
                        <h2 class="accordion-header" id="<?= $headingId ?>">
                            <button
                                class="accordion-button <?= $is_unread ? 'bg-light text-dark fw-bold' : 'collapsed bg-white text-secondary' ?>"
                                type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>"
                                aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                <div class="d-flex align-items-center gap-3 w-100">
                                    <div class="notif-icon-small text-primary">
                                        <i class="bi <?= $icon ?>"></i>
                                    </div>
                                    <div class="flex-grow-1 text-start">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span
                                                class="small text-muted"><?= date('d M Y, H:i', strtotime($notif['created_at'])) ?></span>
                                            <?php if ($is_unread): ?><span
                                                    class="badge bg-danger rounded-pill flex-shrink-0 ms-2"
                                                    style="font-size:0.6rem">Baru</span><?php endif; ?>
                                        </div>
                                        <div class="<?= $is_unread ? 'text-dark' : 'text-muted' ?>"
                                            style="font-size:0.95rem; line-height:1.4">
                                            <?= htmlspecialchars($notif['message']) ?>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>"
                            data-bs-parent="#notifAccordion" data-notif-id="<?= $notif['id'] ?>">
                            <div class="accordion-body bg-light">
                                <p class="mb-3 text-dark">
                                    <?= !empty($notif['description']) ? nl2br(htmlspecialchars($notif['description'])) : '<em class="text-muted">Tidak ada deskripsi detail untuk notifikasi ini.</em>' ?>
                                </p>
                                <?php if (!empty($notif['link'])): ?>
                                    <a href="<?= htmlspecialchars($notif['link']) ?>"
                                        class="btn btn-sm btn-primary rounded-pill px-3">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Detail
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; else: ?>
                <div class="p-5 text-center text-muted bg-white border rounded-3 shadow-sm">
                    <i class="bi bi-bell-slash fs-1 text-secondary opacity-25 mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary">Belum ada notifikasi</h5>
                    <p class="mb-0 small text-muted">Aktivitas terbaru Anda akan muncul di sini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="dashboard.php" class="btn btn-outline-primary">Kembali ke Dashboard</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const accordions = document.querySelectorAll('.accordion-collapse');
        accordions.forEach(acc => {
            acc.addEventListener('shown.bs.collapse', function () {
                const notifId = this.getAttribute('data-notif-id');
                const button = document.querySelector(`button[data-bs-target="#${this.id}"]`);
                const badge = button.querySelector('.badge.bg-danger');

                if (notifId && badge) {
                    fetch('mark_notification_read.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + notifId
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                badge.remove(); 
                                button.classList.remove('fw-bold', 'bg-light'); 
                                button.classList.add('bg-white', 'text-secondary'); 
                            }
                        })
                        .catch(err => console.error('Error marking read:', err));
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>