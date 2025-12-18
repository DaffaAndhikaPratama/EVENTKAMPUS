<?php
$page_title = "Reset Password";
require_once __DIR__ . '/../includes/header.php';

$msg = "";
$token = $_GET['token'] ?? '';
$valid_token = false;
$user_id = null;

if (empty($token)) {
    $msg = "<div class='alert alert-danger'>Token tidak valid atau tidak ditemukan.</div>";
} else {
    $current_time = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires_at > ?");
    $stmt->bind_param("ss", $token, $current_time);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $valid_token = true;
        $user_id = $res->fetch_assoc()['id'];
    } else {
        $msg = "<div class='alert alert-danger'>Link reset password sudah kadaluarsa atau tidak valid. Silakan request ulang.</div>";
    }
}

if (isset($_POST['do_reset']) && $valid_token) {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];

    if (strlen($pass1) < 6) {
        $msg = "<div class='alert alert-danger'>Password minimal 6 karakter.</div>";
    } elseif ($pass1 !== $pass2) {
        $msg = "<div class='alert alert-danger'>Konfirmasi password tidak cocok.</div>";
    } else {
        $hash = password_hash($pass1, PASSWORD_DEFAULT);

        $upd = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?");
        $upd->bind_param("si", $hash, $user_id);

        if ($upd->execute()) {
            echo "<script>alert('Password berhasil diubah! Silakan login.'); window.location='login.php';</script>";
            exit;
        } else {
            $msg = "<div class='alert alert-danger'>Gagal mengupdate password. Silakan coba lagi.</div>";
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center py-5 my-4">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-1">Reset Password</h3>
                    <p class="text-muted small">Buat kata sandi baru untuk akun Anda</p>
                </div>

                <?= $msg ?>

                <?php if ($valid_token): ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Password Baru</label>
                            <input type="password" name="pass1" class="form-control bg-light border-0 py-2"
                                placeholder="******" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">Konfirmasi Password</label>
                            <input type="password" name="pass2" class="form-control bg-light border-0 py-2"
                                placeholder="******" required>
                        </div>
                        <div class="d-grid gap-2 mb-4">
                            <button type="submit" name="do_reset"
                                class="btn btn-success fw-bold py-2 rounded-pill shadow-sm">Simpan Password Baru</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center">
                        <a href="forgot_password.php" class="btn btn-outline-primary rounded-pill">Request Ulang Link</a>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="login.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i>
                        Kembali ke Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>