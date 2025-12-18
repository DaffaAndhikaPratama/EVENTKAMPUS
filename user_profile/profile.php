<?php
$page_title = "Profil Pengguna";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location='" . BASE_URL . "/auth/login.php';</script>";
    exit;
}

$viewer_id = $_SESSION['user_id'];
$viewer_role = $_SESSION['role'];

$target_id = isset($_GET['id']) ? (int) $_GET['id'] : $viewer_id;

$stmt = $conn->prepare("SELECT id, name, email, role, photo, description, campus, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "<div class='container py-5'><div class='alert alert-danger'>Pengguna tidak ditemukan.</div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

if ($user['role'] == 'admin' && $viewer_role != 'admin' && $target_id != $viewer_id) {
    echo "<div class='container py-5'><div class='card border-0 shadow-sm text-center p-5'>
            <div class='mb-3 text-danger'><i class='bi bi-shield-lock-fill fs-1'></i></div>
            <h3 class='fw-bold'>Akses Dibatasi</h3>
            <p class='text-muted'>Anda tidak memiliki izin untuk melihat profil Administrator.</p>
            <button onclick=\"goBack('" . BASE_URL . "/pages/dashboard.php')\" class='btn btn-outline-secondary mt-3'>Kembali</button>
          </div></div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$join_date = date('d F Y', strtotime($user['created_at']));
$is_own_profile = ($viewer_id == $user['id']);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-primary text-white p-4 text-center">
                    <h4 class="mb-0 fw-bold"><?= $is_own_profile ? "Profil Saya" : "Profil Pengguna" ?></h4>
                </div>

                <div class="card-body p-5">
                    <div class="row g-5 align-items-center">

                        <div class="col-md-4 text-center border-end">
                            <?php
                            $foto = !empty($user['photo']) ? BASE_URL . "/assets/images/profiles/" . $user['photo'] : "https://via.placeholder.com/180?text=U";
                            ?>
                            <img src="<?= $foto ?>" class="rounded-circle img-thumbnail shadow"
                                style="width: 180px; height: 180px; object-fit: cover;"
                                onerror="this.src='https://via.placeholder.com/180?text=U'">

                            <h5 class="fw-bold mb-1 mt-3"><?= htmlspecialchars($user['name']) ?></h5>

                            <?php
                            $role_badge = 'bg-secondary';
                            if ($user['role'] == 'admin')
                                $role_badge = 'bg-danger';
                            elseif ($user['role'] == 'event_organizer')
                                $role_badge = 'bg-info text-dark';
                            elseif ($user['role'] == 'mahasiswa')
                                $role_badge = 'bg-success';
                            ?>
                            <span
                                class="badge <?= $role_badge ?> mb-3"><?= strtoupper(str_replace('_', ' ', $user['role'])) ?></span>

                            <p class="text-muted small">
                                <i class="bi bi-calendar3"></i> Bergabung:<br>
                                <strong><?= $join_date ?></strong>
                            </p>
                        </div>

                        <div class="col-md-8">
                            <h5 class="fw-bold text-primary mb-3"><i class="bi bi-person-lines-fill"></i> Biodata</h5>

                            <?php if ($is_own_profile || $viewer_role == 'admin'): ?>
                                <div class="row mb-3">
                                    <div class="col-sm-4 text-muted fw-bold">Email</div>
                                    <div class="col-sm-8 text-break"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-sm-4 text-muted fw-bold">Asal Kampus</div>
                                <div class="col-sm-8">
                                    <?php if (!empty($user['campus'])): ?>
                                        <span class="text-dark"><i class="bi bi-mortarboard-fill text-warning me-1"></i>
                                            <?= htmlspecialchars($user['campus']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">- Belum diatur -</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted fw-bold">Tentang Saya</div>
                                <div class="col-sm-8">
                                    <?php if (!empty($user['description'])): ?>
                                        <div
                                            class="p-3 bg-light rounded text-secondary border-start border-4 border-primary">
                                            <?= nl2br(htmlspecialchars($user['description'])) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">- Belum ada deskripsi -</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                <?php if ($is_own_profile): ?>
                                    <a href="edit_profile.php" class="btn btn-primary fw-bold px-4">
                                        <i class="bi bi-pencil-square"></i> Edit Profil
                                    </a>
                                <?php endif; ?>

                                <button onclick="goBack('<?= BASE_URL ?>/pages/dashboard.php')"
                                    class="btn btn-outline-secondary px-4">
                                    Kembali
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>