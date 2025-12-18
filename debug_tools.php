<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/email_helper.php';

$action = $_GET['action'] ?? 'home';
$msg = "";

if ($action == 'check_db') {
    try {
        if ($conn->query("SELECT 1")) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'unknown';
            $msg = "<div class='alert alert-success'>✅ Koneksi Database BERHASIL! (Host: " . $host . ", DB: " . $dbname . ")</div>";
        } else {
            $msg = "<div class='alert alert-danger'>❌ Koneksi Database GAGAL: " . $conn->error . "</div>";
        }
    } catch (Exception $e) {
        $msg = "<div class='alert alert-danger'>❌ Error: " . $e->getMessage() . "</div>";
    }
} elseif ($action == 'test_email') {
    $to = "test@example.com";
    $to_name = "Debug User";
    $subject = "Test Email Debugging";
    $body = "<p>Ini adalah email percobaan dari Debug Tool.</p>";

    if (kirimEmail($to, $to_name, $subject, $body)) {
        $msg = "<div class='alert alert-success'>✅ Email 'kirimEmail' function return TRUE. Cek inbox (atau Mailtrap).</div>";
    } else {
        $msg = "<div class='alert alert-danger'>❌ Gagal mengirim email. Cek konfigurasi SMTP di .env</div>";
    }
} elseif ($action == 'run_scheduler') {
    ob_start();
    include __DIR__ . '/scripts/scheduler.php';
    $output = ob_get_clean();
    $msg = "<div class='alert alert-info'>Output Scheduler:<br><pre>" . htmlspecialchars($output) . "</pre></div>";
} elseif ($action == 'phpinfo') {
    phpinfo();
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Tools - EventKampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">🛠️ Debug Tools</h1>

        <?= $msg ?>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Database</h5>
                        <p class="card-text">Cek status koneksi ke database MySQL.</p>
                        <a href="?action=check_db" class="btn btn-outline-primary w-100">Cek Koneksi</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Email System</h5>
                        <p class="card-text">Test fungsi `kirimEmail` (PHPMailer).</p>
                        <a href="?action=test_email" class="btn btn-outline-success w-100">Test Kirim Email</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Scheduler</h5>
                        <p class="card-text">Jalankan script `scheduler.php` manual.</p>
                        <a href="?action=run_scheduler" class="btn btn-outline-warning w-100">Run Scheduler</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">PHP Info</h5>
                        <p class="card-text">Lihat konfigurasi PHP server.</p>
                        <a href="?action=phpinfo" target="_blank" class="btn btn-outline-secondary w-100">View
                            phpinfo()</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center">
            <a href="index.php" class="text-decoration-none">&larr; Kembali ke Home</a>
        </div>
    </div>
</body>

</html>