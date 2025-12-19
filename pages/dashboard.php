<?php
$page_title = "Dashboard";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='" . BASE_URL . "/auth/login.php';</script>";
    exit;
}

$uid = $_SESSION['user_id'];
$role = $_SESSION['role'];
$year = date('Y');

require_once __DIR__ . '/../includes/dashboard_logic.php';

require_once __DIR__ . '/../config/google_init.php';
require_once __DIR__ . '/../config/calendar_helper.php';

$calendar_connected = false;
$calendar_events = [];

require_once __DIR__ . '/calendar/logic.php';
?>

<div class="container py-5">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold">Halo, <?= htmlspecialchars($_SESSION['name']) ?>! </h2>
            <p class="text-muted">
                <?= ($role == 'admin') ? "Laporan statistik sistem." : (($role == 'event_organizer') ? "Ringkasan performa event Anda." : "Aktivitas kemahasiswaan Anda.") ?>
            </p>
        </div>
        <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end mt-3 mt-md-0">
            <a href="<?= BASE_URL ?>/user_profile/profile.php" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-person-circle me-1"></i> Profil Saya
            </a>
            <?php if ($role != 'mahasiswa'): ?>
                <a href="<?= BASE_URL ?>/pages/csv/<?= ($role == 'admin') ? 'download_admin.php' : 'download_eo.php' ?>" class="btn btn-outline-success shadow-sm fw-bold">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Unduh Tabel CSV
                </a>
                <a href="<?= BASE_URL ?>/event_management/create.php" class="btn btn-success shadow-sm fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Buat Event
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php 
    if ($role == 'admin') {
        include __DIR__ . '/../includes/dashboard_views/admin.php';
    } elseif ($role == 'event_organizer') {
        include __DIR__ . '/../includes/dashboard_views/eo.php';
    } else {
        include __DIR__ . '/../includes/dashboard_views/mahasiswa.php';
    }
    ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active fw-bold text-primary" data-bs-toggle="tab"
                        data-bs-target="#t1"><i class="bi bi-ticket-perforated-fill me-2"></i> Event Diikuti <span
                            class="badge bg-primary ms-2"><?= $joined ? $joined->num_rows : 0 ?></span></button></li>
                <?php if ($role != 'mahasiswa'): ?>
                    <li class="nav-item"><button class="nav-link fw-bold text-secondary" data-bs-toggle="tab"
                            data-bs-target="#t2"><i class="bi bi-calendar-check-fill me-2"></i> <?= ($role == 'admin') ? 'Event-event' : 'Event Saya' ?> <span
                                class="badge bg-secondary ms-2"><?= $created ? $created->num_rows : 0 ?></span></button>
                    </li><?php endif; ?>
                <li class="nav-item"><button class="nav-link fw-bold text-success" data-bs-toggle="tab"
                        data-bs-target="#t3"><i class="bi bi-google me-2"></i> Kalender</button></li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="t1">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Event</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($joined && $joined->num_rows > 0):
                                    while ($j = $joined->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold"><?= htmlspecialchars($j['title']) ?></div><small
                                                    class="text-muted"><?= date('d M Y', strtotime($j['event_date'])) ?></small>
                                            </td>
                                            <td><span
                                                    class="badge bg-<?= ($j['status'] == 'confirmed' ? 'success' : ($j['status'] == 'pending' ? 'warning' : 'danger')) ?>"><?= $j['status'] ?></span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex gap-2 justify-content-end">
                                                    <a href="../event_management/detail_event.php?id=<?= $j['id'] ?>"
                                                        class="btn btn-sm btn-outline-primary rounded-pill px-3">Detail</a>
                                                    <?php if ($j['status'] == 'confirmed' && !empty($j['certificate_link'])): ?>
                                                        <a href="<?= htmlspecialchars($j['certificate_link']) ?>" target="_blank"class="btn btn-sm btn-success rounded-pill px-3"
                                                            title="Lihat Sertifikat"><i class="bi bi-cloud-arrow-down-fill"></i> Sertifikat</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; else:
                                    echo "<tr><td colspan='3' class='text-center py-5 text-muted'>Belum ada event yang diikuti.</td></tr>";
                                endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($role != 'mahasiswa'): ?>
                    <div class="tab-pane fade" id="t2">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Event</th>
                                        <th>Tgl</th>
                                        <th>Peserta</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($created && $created->num_rows > 0):
                                        while ($ev = $created->fetch_assoc()): ?>
                                            <tr>
                                                <td class="ps-4 fw-bold">
                                                    <?= htmlspecialchars($ev['title']) ?>
                                                    <?php 
                                                        $ev_time = strtotime($ev['event_date']);
                                                        if (time() > $ev_time): 
                                                            $del_date = date('d M Y', strtotime('+1 month', $ev_time));
                                                    ?>
                                                        <div class="mt-1">
                                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:0.75rem">
                                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Akan dihapus: <?= $del_date ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d M Y', $ev_time) ?></td>
                                                <td class="fw-bold text-primary"><?= number_format($ev['participants']) ?></td>
                                                <td class="text-end pe-4">
                                                    <div class="btn-group">
                                                        <a href="../event_management/verifikasi_peserta.php?id=<?= $ev['id'] ?>"
                                                            class="btn btn-sm btn-light border text-success"
                                                            title="Verifikasi Peserta"><i class="bi bi-person-check-fill"></i></a>
                                                        <a href="../event_management/edit.php?id=<?= $ev['id'] ?>"
                                                            class="btn btn-sm btn-light border text-primary" title="Edit"><i
                                                                class="bi bi-pencil-square"></i></a>
                                                        <a href="../event_management/delete.php?id=<?= $ev['id'] ?>"
                                                            class="btn btn-sm btn-light border text-danger"
                                                            onclick="return confirm('Hapus event ini?')" title="Hapus"><i
                                                                class="bi bi-trash-fill"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; else:
                                        echo "<tr><td colspan='4' class='text-center py-5 text-muted'>Belum ada event yang dibuat.</td></tr>";
                                    endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="tab-pane fade" id="t3">
                    <?php require_once __DIR__ . '/calendar/view.php'; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'calendar') {
            const triggerEl = document.querySelector('button[data-bs-target="#t3"]');
            if (triggerEl) {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>