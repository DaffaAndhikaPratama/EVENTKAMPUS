<?php
$page_title = "Edit Event";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/email_helper.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'event_organizer' && $_SESSION['role'] !== 'admin') || !isset($_GET['id'])) {
    echo "<script>window.location='" . BASE_URL . "/pages/dashboard.php';</script>";
    exit;
}

$event_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM events WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $event_id, $user_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    echo "<script>alert('Akses ditolak atau event tidak ditemukan.'); window.location='" . BASE_URL . "/pages/dashboard.php';</script>";
    exit;
}

$page_title .= ": " . $event['title'];
$msg = "";

if (isset($_POST['update'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $date = $_POST['event_date'];
    $location = $_POST['location'];
    $price = (int) $_POST['price'];
    $desc = $_POST['description'];

    $bank_info = trim($_POST['payment_info_bank']);
    $ewallet_info = trim($_POST['payment_info_ewallet']);

    $poster_name = $event['poster'];

    if (!empty($_FILES['poster']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = time() . "_" . $_FILES['poster']['name'];
            $target = __DIR__ . "/../assets/images/poster/" . $new_name;

            if (move_uploaded_file($_FILES['poster']['tmp_name'], $target)) {
                if ($event['poster'] && file_exists(__DIR__ . "/../assets/images/poster/" . $event['poster'])) {
                    unlink(__DIR__ . "/../assets/images/poster/" . $event['poster']);
                }
                $poster_name = $new_name;
            }
        }
    }

    $certificate_link = trim($_POST['certificate_link'] ?? '');
    $broadcast_cert = isset($_POST['broadcast_cert']);

    $event_type = $_POST['event_type'];
    $zoom_link = ($event_type == 'online') ? trim($_POST['zoom_link']) : NULL;
    $location = ($event_type == 'offline') ? trim($_POST['location']) : 'Online Event';
    $broadcast = isset($_POST['broadcast_zoom']);

    require_once __DIR__ . '/../classes/Event.php';
    $eventObj = new Event();

    $data = [
        'title' => $title,
        'category' => $category,
        'event_type' => $event_type,
        'event_date' => $date,
        'location' => $location,
        'zoom_link' => $zoom_link,
        'price' => $price,
        'description' => $desc,
        'poster' => $poster_name,
        'bank_info' => $bank_info,
        'ewallet_info' => $ewallet_info,
        
    ];

    $updateSuccess = $eventObj->update($event_id, $data);

    if ($updateSuccess) {
        $manual = $conn->prepare("UPDATE events SET certificate_link=? WHERE id=?");
        $manual->bind_param("si", $certificate_link, $event_id);
        $manual->execute();
    }

    if ($updateSuccess) {
        $alert_message = "Event berhasil diperbarui!";
        $count_sent_zoom = 0;
        $count_sent_cert = 0;

        if ($broadcast && !empty($zoom_link)) {
            $parts = $conn->query("SELECT u.name, u.email FROM event_registrations r JOIN users u ON r.user_id = u.id WHERE r.event_id = $event_id AND r.status = 'confirmed'");
            if ($parts->num_rows > 0) {
                while ($p = $parts->fetch_assoc()) {
                    $sub = "Update Link Zoom: " . $title;
                    $bdy = "<h3>Halo, {$p['name']}</h3>
                            <p>Event Organizer telah memperbarui Link Zoom untuk event <b>$title</b>.</p>
                            <p><strong>Link Zoom:</strong> <a href='$zoom_link'>$zoom_link</a></p>";
                    kirimEmail($p['email'], $p['name'], $sub, $bdy);
                    $conn->query("INSERT INTO notifications (user_id, message) VALUES ((SELECT id FROM users WHERE email='{$p['email']}'), 'Link Zoom diperbarui: $title')");
                    $count_sent_zoom++;
                }
                $alert_message .= "\\nLink Zoom dikirim ke $count_sent_zoom peserta!";
            }
        }

        if ($broadcast_cert && !empty($certificate_link)) {
            $parts = $conn->query("SELECT u.name, u.email FROM event_registrations r JOIN users u ON r.user_id = u.id WHERE r.event_id = $event_id AND r.status = 'confirmed'");
            if ($parts->num_rows > 0) {
                while ($p = $parts->fetch_assoc()) {
                    $sub = "Sertifikat Tersedia: " . $title;
                    $bdy = "<h3>Halo, {$p['name']}</h3>
                            <p>Terima kasih telah mengikuti event <b>$title</b>.</p>
                            <p>Sertifikat Anda sudah dapat diakses melalui link berikut:</p>
                            <p><a href='$certificate_link' style='background:#0d6efd;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Lihat Sertifikat</a></p>
                            <p>Atau copy link ini: $certificate_link</p>";

                    kirimEmail($p['email'], $p['name'], $sub, $bdy);

                    $msg_notif = "Sertifikat untuk event $title telah tersedia.";
                    $desc_notif = "Selamat! Sertifikat kepesertaan Anda untuk event $title telah diterbitkan. Silakan cek detailnya di Dashboard.";
                    $link_notif = BASE_URL . '/pages/dashboard.php';

                    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, message, description, link) VALUES ((SELECT id FROM users WHERE email=?), ?, ?, ?)");
                    $stmt_notif->bind_param("ssss", $p['email'], $msg_notif, $desc_notif, $link_notif);
                    $stmt_notif->execute();
                    $count_sent_cert++;
                }
                $alert_message .= "\\nSertifikat dibagikan ke $count_sent_cert peserta!";
            }
        }
        echo "<script>alert('$alert_message'); window.location='" . BASE_URL . "/pages/dashboard.php';</script>";

    } else {
        $msg = "<div class='alert alert-danger'>Gagal update event.</div>";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Event</h5>
                </div>
                <div class="card-body p-4">
                    <?= $msg ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3"><label class="fw-bold">Judul Event</label><input type="text" name="title"
                                class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required></div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="fw-bold">Kategori</label>
                                <select name="category" class="form-select">
                                    <?php $cats = ['Seminar', 'Workshop', 'Hiburan', 'Lomba', 'Bursa Kerja', 'Seni & Budaya'];
                                    foreach ($cats as $c): ?>
                                        <option value="<?= $c ?>" <?= $event['category'] == $c ? 'selected' : '' ?>><?= $c ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label class="fw-bold">Tanggal</label><input
                                    type="datetime-local" name="event_date" class="form-control"
                                    value="<?= date('Y-m-d\TH:i', strtotime($event['event_date'])) ?>" required></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Tipe Event</label>
                                <select name="event_type" id="event_type" class="form-select"
                                    onchange="toggleLocationInput()">
                                    <option value="offline" <?= ($event['event_type'] ?? 'offline') == 'offline' ? 'selected' : '' ?>>Offline (Tatap
                                        Muka)</option>
                                    <option value="online" <?= ($event['event_type'] ?? 'offline') == 'online' ? 'selected' : '' ?>>Online (Zoom/Meet)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div id="location_field">
                                    <label class="fw-bold">Lokasi Gedung / Alamat</label>
                                    <input type="text" name="location" class="form-control"
                                        value="<?= htmlspecialchars($event['location']) ?>">
                                </div>
                                <div id="zoom_field" style="display:none;">
                                    <label class="fw-bold">Link Zoom / Meeting</label>
                                    <input type="text" name="zoom_link" class="form-control"
                                        placeholder="https://zoom.us/j/..."
                                        value="<?= htmlspecialchars($event['zoom_link'] ?? '') ?>">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="broadcast_zoom"
                                            id="broadcast_zoom">
                                        <label class="form-check-label small text-primary fw-bold" for="broadcast_zoom">
                                            Kirim Notifikasi & Email Link Zoom ke Peserta Terdaftar
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            function toggleLocationInput() {
                                const type = document.getElementById('event_type').value;
                                if (type === 'online') {
                                    document.getElementById('location_field').style.display = 'none';
                                    document.getElementById('zoom_field').style.display = 'block';
                                } else {
                                    document.getElementById('location_field').style.display = 'block';
                                    document.getElementById('zoom_field').style.display = 'none';
                                }
                            }
                            window.addEventListener('DOMContentLoaded', toggleLocationInput);
                        </script>

                        <div class="mb-3"><label class="fw-bold">Harga Tiket (Rp 0 = Gratis)</label><input type="number"
                                name="price" class="form-control" value="<?= $event['price'] ?>" required></div>

                        <div class="card card-body bg-light mb-4">
                            <h6 class="fw-bold mb-2">Detail Pembayaran (Hanya diisi jika Berbayar)</h6>
                            <div class="mb-3">
                                <label class="form-label small text-muted"><i class="bi bi-bank2"></i> No. Rekening
                                    Bank</label>
                                <input type="text" name="payment_info_bank" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($event['payment_info_bank'] ?? '') ?>"
                                    placeholder="Contoh: BCA 1234-5678-90 a.n [NAMA EO]">
                            </div>
                            <div>
                                <label class="form-label small text-muted"><i class="bi bi-wallet"></i> E-Wallet
                                    (DANA/GOPAY/OVO)</label>
                                <input type="text" name="payment_info_ewallet" class="form-control form-control-sm"
                                    value="<?= htmlspecialchars($event['payment_info_ewallet'] ?? '') ?>"
                                    placeholder="Contoh: DANA 0812-xxxx-xxxx a.n [NAMA EO]">
                            </div>
                        </div>

                        <div class="mb-3"><label class="fw-bold">Deskripsi</label><textarea name="description"
                                class="form-control" rows="5"
                                required><?= htmlspecialchars($event['description']) ?></textarea></div>

                        <div class="mb-4">
                            <label class="fw-bold d-block">Poster Saat Ini</label>
                            <img src="<?= BASE_URL ?>/assets/images/poster/<?= $event['poster'] ?>" width="100"
                                class="mb-2 rounded border">
                            <input type="file" name="poster" class="form-control">
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengganti poster.</small>
                        </div>

                        <div class="mb-4">
                            <label class="fw-bold d-block">Link Sertifikat (Google Drive / Lainnya)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                <input type="text" name="certificate_link" class="form-control"
                                    placeholder="https://drive.google.com/..."
                                    value="<?= htmlspecialchars($event['certificate_link'] ?? '') ?>">
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="broadcast_cert"
                                    id="broadcast_cert">
                                <label class="form-check-label small text-success fw-bold" for="broadcast_cert">
                                    Kirim Notifikasi & Email Sertifikat ke Peserta
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" onclick="goBack('<?= BASE_URL ?>/pages/dashboard.php')"
                                class="btn btn-secondary">Batal</button>
                            <button type="submit" name="update" class="btn btn-primary fw-bold">Simpan
                                Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>