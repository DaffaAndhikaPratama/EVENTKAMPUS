<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/email_helper.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location='" . BASE_URL . "/index.php';</script>";
    exit;
}
$event_id = (int) $_GET['id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$msg = "";

$query = "SELECT e.*, u.name AS creator_name, u.photo AS creator_photo 
          FROM events e 
          JOIN users u ON e.user_id = u.id 
          WHERE e.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "Event tidak ditemukan.";
    exit;
}

$q_count = $conn->query("SELECT COUNT(*) as total FROM event_registrations WHERE event_id = $event_id AND status = 'confirmed'");
$real_participants = $q_count->fetch_assoc()['total'];

if (isset($_POST['daftar_event']) && $user_id) {

    $check_stmt = $conn->prepare("SELECT id FROM event_registrations WHERE user_id = ? AND event_id = ?");
    $check_stmt->bind_param("ii", $user_id, $event_id);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $msg = "<div class='alert alert-warning'>Anda sudah terdaftar di event ini.</div>";
    } else {
        $status = 'confirmed';
        $proof_name = NULL;

        if ($event['price'] > 0) {
            $status = 'pending';
            if (!empty($_FILES['payment_proof']['name'])) {
                $proof_name = time() . '_' . $_FILES['payment_proof']['name'];
                $target = __DIR__ . '/../assets/images/payments/' . $proof_name;
                if (!is_dir(__DIR__ . '/../assets/images/payments/'))
                    mkdir(__DIR__ . '/../assets/images/payments/', 0777, true);
                move_uploaded_file($_FILES['payment_proof']['tmp_name'], $target);
            } else {
                $msg = "<div class='alert alert-danger'>Wajib upload bukti pembayaran!</div>";
            }
        }

        if (empty($msg)) {
            $insert = $conn->prepare("INSERT INTO event_registrations (user_id, event_id, status, payment_proof) VALUES (?, ?, ?, ?)");
            $insert->bind_param("iiss", $user_id, $event_id, $status, $proof_name);

            if ($insert->execute()) {
                if ($status == 'confirmed') {
                    $conn->query("UPDATE events SET participants = participants + 1 WHERE id = $event_id");
                }

                $pesan_mhs = ($status == 'confirmed') ? "Berhasil Terdaftar di " . $event['title'] : "Pembayaran diverifikasi untuk " . $event['title'];
                $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$pesan_mhs')");

                $eo_id = $event['user_id'];
                $pesan_eo = "Peserta Baru! Mendaftar di: " . $event['title'];
                $link_target = BASE_URL . "/event_management/verifikasi_peserta.php?id=" . $event_id;
                $conn->query("INSERT INTO notifications (user_id, message, link, is_pushed) VALUES ($eo_id, '$pesan_eo', '$link_target', 0)");
                $msg = "<div class='alert alert-success'>Pendaftaran berhasil! Status: <b>" . strtoupper($status) . "</b></div>";
                if ($status == 'confirmed')
                    $real_participants++;

                $subject = "Pendaftaran Event: " . $event['title'];
                $nama_peserta = $_SESSION['name'];
                $email_peserta = $_SESSION['email'] ?? '';

                if (empty($email_peserta)) {
                    $u_q = $conn->query("SELECT email FROM users WHERE id = $user_id");
                    $email_peserta = $u_q->fetch_assoc()['email'];
                }

                $body = "
                <h3>Halo, $nama_peserta!</h3>
                <p>Terima kasih telah mendaftar di event <b>{$event['title']}</b>.</p>
                <p><strong>Detail Event:</strong><br>
                Tanggal: " . date('d M Y, H:i', strtotime($event['event_date'])) . "<br>
                Lokasi: {$event['location']}<br>
                Status Tiket: <b>" . strtoupper($status) . "</b>
                </p>";

                if ($status == 'confirmed') {
                    $body .= "<div style='border:1px dashed #333; padding:15px; background:#f9f9f9; margin-top:20px;'>
                        <h4 style='margin-top:0;'><i class='bi bi-ticket-detailed'></i> E-TICKET</h4>
                        <p>Tunjukkan email ini saat registrasi ulang di lokasi.</p>
                        <p><strong>ID Registrasi:</strong> #{$insert->insert_id}</p>
                    </div>";

                    require_once __DIR__ . '/../config/google_init.php';
                    require_once __DIR__ . '/../config/calendar_helper.php';

                    $q_tok = $conn->query("SELECT google_refresh_token FROM users WHERE id = $user_id");
                    if ($r_tok = $q_tok->fetch_assoc()) {
                        if (!empty($r_tok['google_refresh_token'])) {
                            try {
                                $client->fetchAccessTokenWithRefreshToken($r_tok['google_refresh_token']);
                                $calendar = new CalendarHelper($client);

                                $startTime = date('c', strtotime($event['event_date']));
                                $endTime = date('c', strtotime($event['event_date'] . ' +2 hours'));
                                $desc_cal = strip_tags($event['description']) . "\n\nLokasi: " . $event['location'];

                                $calendar->createEvent($event['title'], $desc_cal, $startTime, $endTime);
                            } catch (Exception $e) {
                                error_log("Gagal create participant calendar: " . $e->getMessage());
                            }
                        }
                    }
                } else {
                    $body .= "<p>Pembayaran Anda sedang diverifikasi oleh panitia. Anda akan menerima email lanjutan setelah dikonfirmasi.</p>";
                }

                kirimEmail($email_peserta, $nama_peserta, $subject, $body);
            }
        }
    }
}

$is_registered = false;
$reg_status = '';
if ($user_id) {
    $q_stat = $conn->prepare("SELECT status FROM event_registrations WHERE user_id = ? AND event_id = ? ORDER BY id DESC LIMIT 1");
    $q_stat->bind_param("ii", $user_id, $event_id);
    $q_stat->execute();
    $res = $q_stat->get_result();
    if ($row = $res->fetch_assoc()) {
        $is_registered = true;
        $reg_status = $row['status'];
    }
}
?>

<div class="container py-5" style="margin-top: 20px;">
    <?= $msg ?>
    <div class="row g-5">

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 90px; z-index: 1;">
                <?php $poster = !empty($event['poster']) ? BASE_URL . "/assets/images/poster/" . $event['poster'] : "https://via.placeholder.com/400?text=Event"; ?>
                <img src="<?= $poster ?>" class="card-img-top"
                    style="width: 100%; height: auto; object-fit: contain; background-color: #f8f9fa;">

                <div class="card-body text-center p-4">
                    <h3 class="fw-bold text-success mb-3">
                        <?= ($event['price'] == 0) ? 'GRATIS' : 'Rp ' . number_format($event['price']) ?>
                    </h3>

                    <div class="d-grid gap-2">
                        <?php if ($user_id): ?>

                            <?php if ($is_registered && $reg_status == 'confirmed' && $event['event_type'] == 'online'): ?>
                                <?php if (!empty($event['zoom_link'])): ?>
                                    <div class="alert alert-success py-2 mb-2 small text-start">
                                        <i class="bi bi-camera-video-fill me-1"></i> Link Zoom Tersedia:<br>
                                        <a href="<?= $event['zoom_link'] ?>" target="_blank"
                                            class="fw-bold text-decoration-none"><?= $event['zoom_link'] ?></a>
                                    </div>
                                    <a href="<?= $event['zoom_link'] ?>" target="_blank"
                                        class="btn btn-primary fw-bold shadow-sm mb-2">
                                        <i class="bi bi-camera-video me-1"></i> GABUNG ZOOM
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning py-2 mb-2 small text-start">
                                        <i class="bi bi-hourglass-split me-1"></i> Link Zoom akan muncul di sini menjalang event
                                        dimulai.
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($user_id == $event['user_id'] && $event['event_type'] == 'online' && empty($event['zoom_link'])): ?>
                                <div class="alert alert-danger py-2 mb-2 small text-start fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> ANDA BELUM MENGISI LINK ZOOM!
                                    <a href="edit.php?id=<?= $event['id'] ?>" class="text-danger text-decoration-underline">Isi
                                        Sekarang</a>
                                </div>
                            <?php endif; ?>

                            <?php if ($is_registered): ?>
                                <button
                                    class="btn btn-<?= $reg_status == 'confirmed' ? 'success' : ($reg_status == 'pending' ? 'warning' : 'danger') ?> fw-bold"
                                    disabled>
                                    <?= $reg_status == 'confirmed' ? 'Sudah Terdaftar' : ($reg_status == 'pending' ? 'Menunggu Verifikasi' : 'Ditolak') ?>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-primary btn-lg fw-bold shadow-sm" data-bs-toggle="modal"
                                    data-bs-target="#daftarModal">Daftar Sekarang</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-primary fw-bold">Login untuk Daftar</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <span class="badge bg-primary mb-3"><?= $event['category'] ?></span>

            <h1 class="fw-bold mb-2"><?= htmlspecialchars($event['title']) ?></h1>

            <div class="d-flex align-items-center mb-4 text-muted">
                <?php $fotoEO = !empty($event['creator_photo']) ? BASE_URL . "/assets/images/profiles/" . $event['creator_photo'] : "https://via.placeholder.com/40?text=" . strtoupper(substr($event['creator_name'], 0, 1)); ?>
                <a href="<?= BASE_URL ?>/user_profile/profile.php?id=<?= $event['user_id'] ?>"
                    class="d-flex align-items-center text-decoration-none text-muted">
                    <img src="<?= $fotoEO ?>" class="rounded-circle me-2 border" width="30" height="30"
                        style="object-fit: cover;">
                    <small>Diselenggarakan oleh <strong
                            class="text-primary"><?= htmlspecialchars($event['creator_name']) ?></strong></small>
                </a>
            </div>

            <div class="row g-3 mb-4 text-muted">
                <div class="col-md-6"><i class="bi bi-calendar-event me-2"></i>
                    <?= date('d M Y, H:i', strtotime($event['event_date'])) ?></div>
                <div class="col-md-6"><i class="bi bi-geo-alt me-2"></i> <?= htmlspecialchars($event['location']) ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="fw-bold text-warning mb-3 fs-5"><i class="bi bi-people-fill me-2"></i>
                        <?= number_format($real_participants) ?> Orang telah mendaftar</div>
                    <hr>
                    <h5 class="fw-bold">Deskripsi</h5>
                    <div class="text-secondary" style="line-height: 1.8; text-align: justify;">
                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($user_id && !$is_registered): ?>
    <div class="modal fade" id="daftarModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Konfirmasi Pendaftaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <p>Mendaftar ke: <b><?= htmlspecialchars($event['title']) ?></b></p>

                        <?php if ($event['price'] > 0): ?>

                            <div class="alert alert-info small">
                                <strong>Info Pembayaran:</strong><br>
                                Silakan transfer <b>Rp <?= number_format($event['price']) ?></b> ke salah satu rekening/e-wallet
                                berikut:
                                <ul class="list-unstyled mt-2 mb-0 small">

                                    <?php if (!empty($event['payment_info_bank'])): ?>
                                        <li class="mb-1"><i class="bi bi-bank2 me-1"></i>
                                            <?= htmlspecialchars($event['payment_info_bank']) ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($event['payment_info_ewallet'])): ?>
                                        <li><i class="bi bi-wallet me-1"></i>
                                            <?= htmlspecialchars($event['payment_info_ewallet']) ?>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (empty($event['payment_info_bank']) && empty($event['payment_info_ewallet'])): ?>
                                        <li class="text-danger fw-bold">!! Nomor tujuan belum diatur oleh EO. Hubungi kontak EO.
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload Bukti Transfer</label>
                                <input type="file" name="payment_proof" class="form-control" required accept="image/*">
                            </div>
                        <?php else: ?>
                            <p class="text-success fw-bold">Event ini GRATIS! Lanjutkan?</p>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="daftar_event" class="btn btn-primary fw-bold">Ya, Daftar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>