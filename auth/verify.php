<?php
$page_title = "Verifikasi Akun";
require_once __DIR__ . '/../includes/header.php';

$email_input = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

if (isset($_POST['verify'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];

    $stmt = $conn->prepare("SELECT id, name, role, verification_token FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($token, $user['verification_token'])) {
            $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
            $update->bind_param("i", $user['id']);

            if ($update->execute()) {

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                echo "<script>
                        alert('Verifikasi Berhasil! Selamat datang.');
                        window.location='../index.php';
                      </script>";
                exit;
            }
        } else {
            $error = "Kode verifikasi salah.";
        }
    } else {
        $error = "Email tidak ditemukan atau kode kadaluarsa.";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-3">
                <div class="card-body p-4 text-center">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-envelope-check-fill fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Verifikasi Email</h3>
                    <p class="text-muted small mb-4">Masukkan 6 digit kode yang telah kami kirimkan ke
                        <b><?= $email_input ?></b>.
                    </p>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger py-2"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3 text-start">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= $email_input ?>" readonly>
                        </div>
                        <div class="mb-4 text-start">
                            <label class="form-label fw-bold">Kode OTP</label>
                            <input type="text" name="token" class="form-control text-center fs-4 letter-spacing-2"
                                placeholder="000000" maxlength="6" required autofocus>
                        </div>
                        <button type="submit" name="verify" class="btn btn-primary w-100 fw-bold py-2">Verifikasi &
                            Masuk</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>