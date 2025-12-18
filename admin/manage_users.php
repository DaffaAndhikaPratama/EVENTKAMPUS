<?php
$page_title = "Kelola User (Admin)";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { 
    echo "<script>alert('Akses Ditolak!'); window.location='../pages/dashboard.php';</script>"; 
    exit; 
}

$msg = "";

if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id == $_SESSION['user_id']) { 
        $msg = "<div class='alert alert-warning'>Gagal! Tidak bisa hapus akun Anda sendiri.</div>"; 
    } else {
        $q_photo = $conn->query("SELECT photo FROM users WHERE id = $del_id")->fetch_assoc();
        if ($q_photo['photo']) {
            $path = __DIR__ . "/../assets/images/profiles/" . $q_photo['photo'];
            if (file_exists($path)) unlink($path);
        }
        
        $conn->query("DELETE FROM users WHERE id = $del_id");
        $msg = "<div class='alert alert-success'>User berhasil dihapus.</div>";
    }
}

if (isset($_GET['verify_id'])) {
    $ver_id = (int)$_GET['verify_id'];
    $conn->query("UPDATE users SET is_verified = 1 WHERE id = $ver_id");
    $msg = "<div class='alert alert-success'>Akun berhasil diaktifkan.</div>";
}

$where = "1=1";
$params = [];
$types = "";

$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_role = isset($_GET['role']) ? trim($_GET['role']) : '';

if (!empty($search_q)) {
    $where .= " AND (name LIKE ? OR email LIKE ?)";
    $like_q = "%$search_q%";
    $params[] = $like_q;
    $params[] = $like_q;
    $types .= "ss";
}

if (!empty($filter_role)) {
    $where .= " AND role = ?";
    $params[] = $filter_role;
    $types .= "s";
}

$stmt = $conn->prepare("SELECT id, name, email, role, is_verified, campus, photo FROM users WHERE $where ORDER BY is_verified ASC, role ASC, name ASC");

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="fw-bold mb-1"><i class="bi bi-people-fill"></i> Kelola User</h2><p class="text-muted">Total User: <strong><?= $result->num_rows ?></strong></p></div>
        <a href="../pages/dashboard.php" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <!-- Search & Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari nama atau email..." value="<?= htmlspecialchars($search_q) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">- Semua Role -</option>
                        <option value="admin" <?= $filter_role == 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="event_organizer" <?= $filter_role == 'event_organizer' ? 'selected' : '' ?>>Event Organizer</option>
                        <option value="mahasiswa" <?= $filter_role == 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                    <?php if(!empty($search_q) || !empty($filter_role)): ?>
                        <a href="manage_users.php" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?= $msg ?>

    <div class="card border-0 shadow-sm"><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th class="ps-4">No</th><th>User</th><th>Kampus</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                <?php $no=1; while($u = $result->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4 text-muted"><?= $no++ ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php $foto = !empty($u['photo']) ? "../../assets/images/profiles/".$u['photo'] : "https://via.placeholder.com/40"; ?>
                            <img src="<?= $foto ?>" class="rounded-circle border" width="35" height="35" style="object-fit:cover" onerror="this.src='https://via.placeholder.com/40'">
                            <a href="../user_profile/profile.php?id=<?= $u['id'] ?>" class="fw-bold text-decoration-none text-dark"><?= htmlspecialchars($u['name']) ?></a>
                        </div>
                    </td>
                    <td><?= !empty($u['campus']) ? htmlspecialchars($u['campus']) : '-' ?></td>
                    <td><span class="badge bg-<?= $u['role']=='admin'?'danger':($u['role']=='event_organizer'?'primary':'secondary') ?>"><?= strtoupper($u['role']) ?></span></td>
                    <td>
                        <?php if ($u['is_verified']): ?>
                            <span class="badge bg-success bg-opacity-10 text-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <?php if (!$u['is_verified']): ?>
                                <a href="?verify_id=<?= $u['id'] ?>" class="btn btn-sm btn-success me-1" title="Aktifkan Akun"><i class="bi bi-check-lg"></i></a>
                            <?php endif; ?>
                            <a href="?delete_id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus user ini? Ini akan menghapus semua event/registrasinya!')" title="Hapus Permanen"><i class="bi bi-trash"></i></a>
                        <?php else: ?>
                             <span class="text-muted small">Anda</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div></div></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>