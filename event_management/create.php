<?php
$page_title = "Buat Event Baru";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'event_organizer' && $_SESSION['role'] !== 'admin')) {
    echo "<script>alert('Hanya Event Organizer dan Admin yang dapat membuat event!'); window.location='" . BASE_URL . "/pages/dashboard.php';</script>";
    exit;
}

$errors = [];
$title = $description = $category = $event_date = $location = $bank_info = $ewallet_info = '';
$price = '';
$event_type = 'offline';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $event_date = $_POST['event_date'];
    $price = (int) $_POST['price'];
    $bank_info = trim($_POST['payment_info_bank']);
    $ewallet_info = trim($_POST['payment_info_ewallet']);
    $user_id = $_SESSION['user_id'];

    $event_type = $_POST['event_type'];
    $zoom_link = ($event_type == 'online') ? trim($_POST['zoom_link']) : NULL;
    $location = ($event_type == 'offline') ? trim($_POST['location']) : 'Online Event';

    $poster_name = NULL;
    if (!empty($_FILES['poster']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $poster_name = time() . "_" . $_FILES['poster']['name'];
            $target_dir = __DIR__ . "/../assets/images/poster/";
            if (!is_dir($target_dir))
                mkdir($target_dir, 0777, true);
            $target = $target_dir . $poster_name;

            if (!move_uploaded_file($_FILES['poster']['tmp_name'], $target)) {
                $errors['poster'] = "Gagal upload poster ke server.";
            }
        } else {
            $errors['poster'] = "Format poster harus JPG/PNG.";
        }
    } else {
        $errors['poster'] = "Poster event wajib diunggah.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO events (user_id, title, category, event_type, event_date, location, zoom_link, price, description, poster, payment_info_bank, payment_info_ewallet, participants, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");

        $stmt->bind_param(
            "issssssissss",
            $user_id,
            $title,
            $category,
            $event_type,
            $event_date,
            $location,
            $zoom_link,
            $price,
            $description,
            $poster_name,
            $bank_info,
            $ewallet_info
        );

        if ($stmt->execute()) {

            require_once __DIR__ . '/../config/google_init.php';
            require_once __DIR__ . '/../config/calendar_helper.php';

            $q_tok = $conn->query("SELECT google_refresh_token FROM users WHERE id = $user_id");
            if ($r_tok = $q_tok->fetch_assoc()) {
                if (!empty($r_tok['google_refresh_token'])) {
                    try {
                        $client->fetchAccessTokenWithRefreshToken($r_tok['google_refresh_token']);
                        $calendar = new CalendarHelper($client);

                        $startTime = date('c', strtotime($event_date));
                        $endTime = date('c', strtotime($event_date . ' +2 hours'));

                        $desc_cal = strip_tags($description) . "\n\nLokasi: " . $location;

                        $calendar->createEvent($title, $desc_cal, $startTime, $endTime);
                    } catch (Exception $e) {
                        error_log("Gagal create event calendar: " . $e->getMessage());
                    }
                }
            }

            echo "<script>alert('Event berhasil dibuat!'); window.location='" . BASE_URL . "/pages/dashboard.php';</script>";
            exit;
        } else {
            $errors['db'] = "Gagal menyimpan data: " . $conn->error;
        }
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h4 class="fw-bold mb-0 text-primary"><i class="bi bi-calendar-plus me-2"></i>Buat Event Baru</h4>
                </div>
                <div class="card-body p-4">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $err)
                                    echo "<li>$err</li>"; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Judul Event</label>
                            <input type="text" name="title" class="form-control"
                                placeholder="Contoh: Seminar Nasional Teknologi 2025"
                                value="<?= htmlspecialchars($title) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Kategori</label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <?php
                                    $cats = ['Seminar', 'Workshop', 'Hiburan', 'Lomba', 'Bursa Kerja', 'Seni & Budaya'];
                                    foreach ($cats as $c):
                                        ?>
                                        <option value="<?= $c ?>" <?= $category == $c ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tanggal & Waktu</label>
                                <input type="datetime-local" name="event_date" class="form-control"
                                    value="<?= htmlspecialchars($event_date) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipe Event</label>
                                <select name="event_type" id="event_type" class="form-select"
                                    onchange="toggleLocationInput()">
                                    <option value="offline" <?= ($event_type == 'offline') ? 'selected' : '' ?>>Offline
                                        (Tatap Muka)</option>
                                    <option value="online" <?= ($event_type == 'online') ? 'selected' : '' ?>>Online
                                        (Zoom/Meet)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div id="location_field">
                                    <label class="form-label fw-bold">Lokasi Gedung / Alamat</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                                        <input type="text" name="location" class="form-control"
                                            placeholder="Nama Gedung / Tempat"
                                            value="<?= htmlspecialchars($location) ?>">
                                    </div>
                                </div>
                                <div id="zoom_field" style="display:none;">
                                    <label class="form-label fw-bold">Link Zoom / Meeting</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i
                                                class="bi bi-camera-video"></i></span>
                                        <input type="text" name="zoom_link" class="form-control"
                                            placeholder="https://zoom.us/j/..." value="">
                                    </div>
                                    <small class="text-danger fst-italic">*Dapat diisi nanti (H-3 jam)</small>
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
                        </script>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Harga Tiket</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="price" class="form-control" placeholder="0 = Gratis"
                                        value="<?= htmlspecialchars($price) ?>" required>
                                </div>
                                <small class="text-muted">Isi 0 jika gratis.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Upload Poster</label>
                                <input type="file" name="poster" class="form-control" accept="image/*" required>
                                <small class="text-muted">Format: JPG/PNG, Maks 2MB.</small>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 me-1"></i> Info Pembayaran
                                    (Opsional)</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Bank Transfer</label>
                                        <input type="text" name="payment_info_bank" class="form-control form-control-sm"
                                            value="<?= htmlspecialchars($bank_info) ?>"
                                            placeholder="Bank - No.Rek - A.n">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">E-Wallet</label>
                                        <input type="text" name="payment_info_ewallet"
                                            class="form-control form-control-sm"
                                            value="<?= htmlspecialchars($ewallet_info) ?>"
                                            placeholder="Dana/OVO - No.HP - A.n">
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block fst-italic">*Hanya diisi jika event
                                    berbayar.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Deskripsi Lengkap</label>
                            <textarea name="description" class="form-control" rows="6"
                                placeholder="Jelaskan detail acara, narasumber, benefit, dll..."
                                required><?= htmlspecialchars($description) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?= BASE_URL ?>/pages/dashboard.php"
                                class="btn btn-light border px-4 fw-bold">Batal</a>
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                                <i class="bi bi-send-check me-1"></i> Terbitkan Event
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>