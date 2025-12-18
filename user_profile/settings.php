<?php
$page_title = "Pengaturan Akun";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='" . BASE_URL . "/auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

if (isset($_POST['save_settings'])) {
    $notif_email = isset($_POST['notify_email']) ? 1 : 0;
    $notif_web = isset($_POST['notify_web']) ? 1 : 0;

    $update = $conn->prepare("UPDATE users SET notify_email = ?, notify_web = ? WHERE id = ?");
    $update->bind_param("iii", $notif_email, $notif_web, $user_id);

    if ($update->execute()) {
        $msg = "<div class='alert alert-success alert-dismissible fade show'>Pengaturan berhasil disimpan! <button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $msg = "<div class='alert alert-danger'>Gagal menyimpan pengaturan.</div>";
    }
}

$q = $conn->query("SELECT notify_email, notify_web FROM users WHERE id = $user_id");
$pref = $q->fetch_assoc();
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0"><i class="bi bi-gear-fill text-secondary"></i> Pengaturan</h3>
                <button onclick="goBack('<?= BASE_URL ?>/pages/dashboard.php')"
                    class="btn btn-outline-secondary btn-sm">
                    Kembali
                </button>
            </div>

            <?= $msg ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Preferensi Notifikasi</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-envelope-paper me-2"></i> Notifikasi Email</h6>
                                <small class="text-muted">Terima email update pendaftaran & reminder event.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input fs-4" type="checkbox" name="notify_email" value="1"
                                    <?= $pref['notify_email'] ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-bell me-2"></i> Notifikasi Web (Pop-up)</h6>
                                <small class="text-muted">Munculkan notifikasi di layar saat membuka web.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input fs-4" type="checkbox" name="notify_web" value="1"
                                    <?= $pref['notify_web'] ? 'checked' : '' ?>>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h6 class="fw-bold mb-1"><i class="bi bi-calendar-event me-2"></i> Sinkronisasi Google
                                    Calendar</h6>
                                <small class="text-muted">Tambahkan event otomatis ke kalender Google Anda.</small>
                            </div>
                            <div>
                                <?php
                                $q_cal = $conn->query("SELECT google_refresh_token FROM users WHERE id=$user_id");
                                $has_token = ($r_cal = $q_cal->fetch_assoc()) && !empty($r_cal['google_refresh_token']);
                                ?>

                                <?php if ($has_token): ?>
                                    <span class="badge bg-success py-2 px-3"><i class="bi bi-check-lg me-1"></i>
                                        Terhubung</span>
                                    <div class="mt-1"><a href="../auth/google_connect.php"
                                            class="small text-muted text-decoration-none">Hubungkan Ulang</a></div>
                                <?php else: ?>
                                    <a href="../auth/google_connect.php" class="btn btn-outline-primary btn-sm fw-bold">
                                        <i class="bi bi-google me-1"></i> Hubungkan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-grid mt-5">
                            <button type="submit" name="save_settings" class="btn btn-primary fw-bold">Simpan
                                Perubahan</button>
                        </div>

                    </form>
                </div>
            </div>

            <div class="alert alert-light border mt-4 text-center">
                <small class="text-muted">
                    Untuk mengubah Password, Foto, atau Biodata, silakan buka menu
                    <a href="profile.php" class="fw-bold text-decoration-none">Profil Saya</a>.
                </small>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>