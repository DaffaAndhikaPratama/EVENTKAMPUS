<?php
$page_title = "Lupa Password";
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/email_helper.php';

$msg = "";

if (isset($_POST['reset_request'])) {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
        $upd->bind_param("ssi", $token, $expires, $user['id']);

        if ($upd->execute()) {
            $link = BASE_URL . "/auth/reset_password.php?token=" . $token;
            $subject = "Reset Password - EventKampus";
            $body = "
            <h3>Halo, {$user['name']}</h3>
            <p>Anda meminta untuk mereset password akun EventKampus Anda.</p>
            <p>Silakan klik link berikut untuk membuat password baru:</p>
            <p><a href='$link' style='background:#0d6efd; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Reset Password</a></p>
            <p><small>Link ini berlaku selama 1 jam.</small></p>
            <p>Jika Anda tidak merasa meminta ini, abaikan saja email ini.</p>
            ";

            if (kirimEmail($email, $user['name'], $subject, $body)) {
                $msg = "<div class='alert alert-success'>Instruksi reset password telah dikirim ke email Anda. Cek Inbox/Spam.</div>";
            } else {
                $msg = "<div class='alert alert-warning'>Gagal mengirim email. Silakan coba lagi nanti.</div>";
            }
        }
    } else {
        $msg = "<div class='alert alert-success'>Jika email terdaftar, instruksi reset password akan dikirimkan.</div>";
    }
}
?>

<div class="container d-flex justify-content-center align-items-center py-5 my-4">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-1">Lupa Password?</h3>
                    <p class="text-muted small">Masukkan email Anda untuk reset password</p>
                </div>
                <?= $msg ?>
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Aamat Email</label>
                        <input type="email" name="email" class="form-control bg-light border-0 py-2"
                            placeholder="nama@email.com" required>
                    </div>
                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" name="reset_request"
                            class="btn btn-primary fw-bold py-2 rounded-pill shadow-sm">Kirim Link Reset</button>
                    </div>
                </form>
                <div class="text-center">
                    <a href="login.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i>
                        Kembali ke Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>