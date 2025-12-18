<?php
$page_title = "Daftar Akun Baru";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/email_helper.php';

$client = require_once __DIR__ . '/../config/google_init.php';

?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/auth_style.css">

<?php
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location='" . BASE_URL . "/pages/dashboard.php';</script>";
    exit;
}

$msg = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $campus = trim($_POST['campus'] ?? '');

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();

    if ($check_stmt->get_result()->num_rows > 0) {
        $msg = "<div class='alert alert-danger small py-2'>Email sudah terdaftar.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $otp_code = rand(100000, 999999);
        $hashed_token = password_hash((string) $otp_code, PASSWORD_DEFAULT); 
        $is_verified = 0;

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, campus, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $name, $email, $hashed_password, $role, $campus, $is_verified, $hashed_token);

        if ($stmt->execute()) {

            $subject = "Kode Verifikasi Pendaftaran - EventKampus";
            $body = "Halo $name,<br><br>Terima kasih telah mendaftar. Kode verifikasi Anda adalah: <b>$otp_code</b><br><br>Masukkan kode ini di halaman verifikasi.";

            if (kirimEmail($email, $name, $subject, $body)) {
                echo "<script>window.location='verify.php?email=$email';</script>";
                exit;
            } else {
                $msg = "<div class='alert alert-warning small py-2'>Pendaftaran berhasil, tapi gagal mengirim email OTP. Hubungi Admin.</div>";
            }

            if ($role == 'event_organizer') {
                $admin_msg = "Akun EO baru ({$name}) telah mendaftar (Menunggu OTP).";
                $conn->query("INSERT INTO notifications (user_id, message) SELECT id, '{$admin_msg}' FROM users WHERE role='admin'");
            }

        } else {
            $msg = "<div class='alert alert-danger small py-2'>Gagal mendaftar. Silakan coba lagi.</div>";
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center py-5 my-4" style="background-color: #f8f9fa;">
    <div class="col-md-6 col-lg-5">

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-1">Buat Akun Baru</h3>
                    <p class="text-muted small">Bergabunglah dengan komunitas EventKampus</p>
                </div>

                <?= $msg ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control bg-light border-0 py-2"
                            placeholder="Nama Lengkap Anda" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Alamat Email</label>
                        <input type="email" name="email" class="form-control bg-light border-0 py-2"
                            placeholder="nama@email.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Kata Sandi</label>
                        <input type="password" name="password" class="form-control bg-light border-0 py-2"
                            placeholder="******" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Daftar Sebagai</label>
                        <select name="role" class="form-select bg-light border-0 py-2" required>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="event_organizer">Event Organizer</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Asal Kampus <small
                                class="fw-normal text-secondary">(Opsional)</small></label>
                        <input type="text" name="campus" class="form-control bg-light border-0 py-2"
                            placeholder="Contoh: UNSIKA">
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" name="register"
                            class="btn btn-primary fw-bold py-2 rounded-pill shadow-sm">
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="d-flex align-items-center mb-4">
                    <hr class="flex-grow-1 m-0 text-muted" style="opacity: 0.2;">
                    <span class="px-3 text-muted small" style="font-size: 0.75rem;">ATAU</span>
                    <hr class="flex-grow-1 m-0 text-muted" style="opacity: 0.2;">
                </div>

                <?php if (isset($client) && $client): ?>
                    <div class="d-grid gap-2 mb-4">
                        <a href="<?= $client->createAuthUrl() ?>"
                            class="btn btn-white border fw-bold py-2 rounded-pill d-flex align-items-center justify-content-center text-secondary hover-shadow">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"
                                width="20" class="me-2">
                            Daftar dengan Google
                        </a>
                    </div>
                <?php endif; ?>

                <div class="text-center">
                    <small class="text-muted">Sudah punya akun?</small>
                    <a href="login.php" class="text-primary fw-bold text-decoration-none ms-1">Masuk</a>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>