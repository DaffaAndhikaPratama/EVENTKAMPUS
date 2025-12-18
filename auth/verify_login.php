<?php
$page_title = "Verifikasi Login";
require_once __DIR__ . '/../includes/header.php';

$email_input = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';

if (isset($_POST['verify_login'])) {
    $email = $_POST['email'];
    $token = $_POST['token'];

    $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE email = ? AND verification_token = ?");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        $clear = $conn->prepare("UPDATE users SET verification_token = NULL WHERE id = ?");
        $clear->bind_param("i", $user['id']);
        $clear->execute();

        echo "<script>
                alert('Login Berhasil!');
                window.location='".BASE_URL."/pages/dashboard.php';
              </script>";
        exit;
    } else {
        $error = "Kode OTP salah atau kadaluarsa.";
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-3">
                <div class="card-body p-4 text-center">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-shield-lock-fill fs-1"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Keamanan Login</h3>
                    <p class="text-muted small mb-4">Kami telah mengirim kode OTP Login ke <b><?= $email_input ?></b>. Masukkan kode tersebut untuk masuk.</p>

                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger py-2"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="email" value="<?= $email_input ?>">
                        <div class="mb-4">
                            <input type="text" name="token" class="form-control text-center fs-3 letter-spacing-2" placeholder="000000" maxlength="6" required autofocus>
                        </div>
                        <button type="submit" name="verify_login" class="btn btn-warning w-100 fw-bold py-2 text-dark">Verifikasi & Masuk</button>
                    </form>
                    
                    <div class="mt-3">
                        <a href="login.php" class="text-decoration-none small text-muted">Kembali ke Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>