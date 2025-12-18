<?php
$page_title = "Verifikasi Peserta";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/email_helper.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'event_organizer' || !isset($_GET['id'])) {
    echo "<script>window.location='".BASE_URL."/pages/dashboard.php';</script>"; 
    exit;
}

$event_id = (int)$_GET['id'];
$uid = $_SESSION['user_id'];
$msg = '';

$stmt = $conn->prepare("SELECT * FROM events WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $event_id, $uid);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "<script>alert('Akses ditolak atau event tidak ditemukan.'); window.location='".BASE_URL."/pages/dashboard.php';</script>"; 
    exit;
}

$page_title .= ": " . $event['title'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_id']) && isset($_POST['action'])) {
    $reg_id = (int)$_POST['registration_id'];
    $action = $_POST['action'];
    $status_baru = ($action == 'accept') ? 'confirmed' : 'rejected';

    $update = $conn->prepare("UPDATE event_registrations SET status = ? WHERE id = ? AND event_id = ?");
    $update->bind_param("sii", $status_baru, $reg_id, $event_id);
    
    if ($update->execute()) {
        
        $q_target = $conn->query("SELECT user_id FROM event_registrations WHERE id = $reg_id");
        $target_user_id = $q_target->fetch_assoc()['user_id'];

        if ($status_baru == 'confirmed') {
            $conn->query("UPDATE events SET participants = participants + 1 WHERE id = $event_id");
            $notif_msg = "Selamat! Pendaftaran Anda pada {$event['title']} telah **DIKONFIRMASI**.";

            $u_t = $conn->query("SELECT name, email FROM users WHERE id = $target_user_id")->fetch_assoc();
            $target_email = $u_t['email'];
            $target_name = $u_t['name'];

            $subject = "Tiket Event: " . $event['title'];
            $body = "
            <h3>Halo, $target_name!</h3>
            <p>Selamat! Pendaftaran Anda untuk event <b>{$event['title']}</b> telah disetujui.</p>
            <p><strong>Detail Event:</strong><br>
            Tanggal: ".date('d M Y, H:i', strtotime($event['event_date']))."<br>
            Lokasi: {$event['location']}
            </p>
            <div style='border:1px dashed #333; padding:15px; background:#f9f9f9; margin-top:20px;'>
                <h4 style='margin-top:0;'>🎫 E-TICKET</h4>
                <p>Tunjukkan email ini saat registrasi ulang di lokasi.</p>
                <p><strong>ID Registrasi:</strong> #{$reg_id}</p>
                <p><strong>Status:</strong> CONFIRMED</p>
            </div>";
            
            kirimEmail($target_email, $target_name, $subject, $body);

        } else {
            $notif_msg = "Maaf, Pendaftaran Anda pada {$event['title']} telah **DITOLAK**.";
            
            $u_t = $conn->query("SELECT name, email FROM users WHERE id = $target_user_id")->fetch_assoc();
            kirimEmail($u_t['email'], $u_t['name'], "Update Status Pendaftaran", 
                "Mohon maaf, pendaftaran Anda untuk event <b>{$event['title']}</b> belum dapat kami setujui saat ini.");
        }
        
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ($target_user_id, '$notif_msg')");

        $msg = "<div class='alert alert-success'>Status peserta berhasil diperbarui menjadi " . strtoupper($status_baru) . ".</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Gagal memperbarui status.</div>";
    }
}

$registrations = $conn->query("
    SELECT r.*, u.name AS user_name, u.email AS user_email, u.campus AS user_campus, u.photo, u.id AS user_target_id
    FROM event_registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = $event_id
    ORDER BY r.registered_at DESC
");

?>
<div class="container py-5">
    <h2 class="fw-bold mb-2">Verifikasi Peserta</h2>
    <p class="text-muted">Event: <strong><?= htmlspecialchars($event['title']) ?></strong></p>
    <?= $msg ?>
    
    <div class="d-flex justify-content-end mb-3">
        <button onclick="goBack('<?= BASE_URL ?>/pages/dashboard.php')" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </button>
    </div>
    
    <div class="card shadow-sm border-0"><div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Peserta</th>
                        <th>Asal Kampus</th>
                        <th>Bukti Bayar</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
            <tbody>
            <?php if($registrations->num_rows > 0): while($reg = $registrations->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4 d-flex align-items-center">
                        <?php $foto_path = !empty($reg['photo']) ? BASE_URL . "/assets/images/profiles/" . $reg['photo'] : "https://via.placeholder.com/40"; ?>
                        <img src="<?= $foto_path ?>" onerror="this.src='https://via.placeholder.com/40'" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                        <div>
                            <a href="<?= BASE_URL ?>/user_profile/profile.php?id=<?= $reg['user_target_id'] ?>" class="fw-bold text-decoration-none text-dark"><?= htmlspecialchars($reg['user_name']) ?></a>
                            <div class="small text-muted"><?= htmlspecialchars($reg['user_email']) ?></div>
                        </div>
                    </td>
                    <td><?= !empty($reg['user_campus']) ? htmlspecialchars($reg['user_campus']) : '-' ?></td>
                    <td>
                        <?php if ($reg['payment_proof']): ?>
                            <a href="<?= BASE_URL ?>/assets/images/payments/<?= $reg['payment_proof'] ?>" target="_blank" class="btn btn-sm btn-info text-white rounded-pill">Lihat</a>
                        <?php else: ?>
                            <span class="text-success">GRATIS</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= ($reg['status'] === 'confirmed' ? 'success' : ($reg['status'] === 'pending' ? 'warning' : 'danger')) ?>"><?= ucfirst($reg['status']) ?></span>
                    </td>
                    <td class="text-end pe-4">
                        <?php if($reg['status'] === 'pending'): ?>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="registration_id" value="<?= $reg['id'] ?>">
                            <input type="hidden" name="action" value="accept">
                            <button type="submit" class="btn btn-sm btn-success me-1" title="Konfirmasi" onclick="return confirm('Yakin ingin menerima pendaftar ini?')"><i class="bi bi-check-lg"></i></button>
                        </form>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="registration_id" value="<?= $reg['id'] ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-sm btn-danger" title="Tolak" onclick="return confirm('Yakin ingin menolak pendaftar ini?')"><i class="bi bi-x-lg"></i></button>
                        </form>
                        <?php else: ?>
                            <button class="btn btn-sm btn-light border" disabled>Selesai</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada peserta yang mendaftar.</td></tr>
            <?php endif; ?>
            </tbody>
        </table></div>
    </div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>