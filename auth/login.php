<?php
$page_title = "Masuk ke EventKampus";
require_once __DIR__ . '/../includes/header.php';
$client = require_once __DIR__ . '/../config/google_init.php';


if (isset($_SESSION['user_id'])) {
    echo "<script>window.location='" . BASE_URL . "/pages/index.php';</script>";
    exit;
}

$msg = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, role, password, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                $msg = "<div class='alert alert-warning small py-2'>Akun belum diverifikasi admin.</div>";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                echo "<script>window.location='" . BASE_URL . "/pages/index.php';</script>";
                exit;
            }
        } else {
            $msg = "<div class='alert alert-danger small py-2'>Password salah.</div>";
        }
    } else {
        $msg = "<div class='alert alert-danger small py-2'>Email tidak ditemukan.</div>";
    }
}
?>

<div class="container d-flex justify-content-center align-items-center py-5 my-4" style="background-color: #f8f9fa;">
    <div class="col-md-5 col-lg-4">

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-1">Selamat Datang</h3>
                    <p class="text-muted small">Masuk untuk mengelola event Anda</p>
                </div>

                <?= $msg ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Alamat Email</label>
                        <input type="email" name="email" class="form-control bg-light border-0 py-2"
                            placeholder="nama@email.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">Kata Sandi</label>
                        <input type="password" name="password" class="form-control bg-light border-0 py-2"
                            placeholder="******" required>
                        <div class="text-end mt-1">
                            <a href="forgot_password.php" class="small text-decoration-none text-muted">Lupa
                                Password?</a>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" name="login" class="btn btn-primary fw-bold py-2 rounded-pill shadow-sm">
                            Masuk Sekarang
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
                        <?php
                        $client->setAccessType('offline');
                        $client->setPrompt('select_account consent');
                        ?>
                        <a href="<?= $client->createAuthUrl() ?>"
                            class="btn btn-white border fw-bold py-2 rounded-pill d-flex align-items-center justify-content-center text-secondary hover-shadow">
                            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google"
                                width="20" class="me-2">
                            Masuk dengan Google
                        </a>
                    </div>
                <?php endif; ?>

                <div class="text-center">
                    <small class="text-muted">Belum punya akun?</small>
                    <a href="register.php" class="text-primary fw-bold text-decoration-none ms-1">Daftar</a>
                </div>

            </div>
        </div>

    </div>
</div>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/auth_style.css">

<?php require_once __DIR__ . '/../includes/footer.php'; ?>