<?php
$page_title = "Kelola Event (Admin)";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='../pages/dashboard.php';</script>";
    exit;
}

$where = "1=1";
$params = [];
$types = "";

$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_cat = isset($_GET['cat']) ? trim($_GET['cat']) : '';

if (!empty($search_q)) {
    $where .= " AND e.title LIKE ?";
    $like_q = "%$search_q%";
    $params[] = $like_q;
    $types .= "s";
}

if (!empty($filter_cat)) {
    $where .= " AND e.category = ?";
    $params[] = $filter_cat;
    $types .= "s";
}

$query = "
    SELECT e.id, e.title, e.event_date, e.category, u.name as organizer_name 
    FROM events e 
    JOIN users u ON e.user_id = u.id 
    WHERE $where
    ORDER BY e.event_date DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-calendar-event"></i> Kelola Event</h2>
            <p class="text-muted">Total Event: <strong><?= $result->num_rows ?></strong></p>
        </div>
        <a href="../pages/dashboard.php" class="btn btn-outline-secondary">Kembali</a>
    </div>

    <!-- Search & Filter Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Cari judul event..." value="<?= htmlspecialchars($search_q) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="cat" class="form-select" onchange="this.form.submit()">
                        <option value="">- Semua Kategori -</option>
                        <option value="Seminar" <?= $filter_cat == 'Seminar' ? 'selected' : '' ?>>Seminar</option>
                        <option value="Workshop" <?= $filter_cat == 'Workshop' ? 'selected' : '' ?>>Workshop</option>
                        <option value="Webinar" <?= $filter_cat == 'Webinar' ? 'selected' : '' ?>>Webinar</option>
                        <option value="Kompetisi" <?= $filter_cat == 'Kompetisi' ? 'selected' : '' ?>>Kompetisi</option>
                        <option value="Pentas Seni" <?= $filter_cat == 'Pentas Seni' ? 'selected' : '' ?>>Pentas Seni</option>
                        <option value="Lainnya" <?= $filter_cat == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                    <?php if(!empty($search_q) || !empty($filter_cat)): ?>
                        <a href="manage_events.php" class="btn btn-light border" title="Reset"><i class="bi bi-x-lg"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'];
            unset($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'];
            unset($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Event</th>
                            <th>Kategori</th>
                            <th>Penyelenggara</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        while ($ev = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $no++ ?></td>
                                <td>
                                    <a href="../event_management/detail_event.php?id=<?= $ev['id'] ?>"
                                        class="fw-bold text-decoration-none text-dark">
                                        <?= htmlspecialchars($ev['title']) ?>
                                    </a>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($ev['category'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($ev['organizer_name']) ?></td>
                                <td><?= date('d M Y', strtotime($ev['event_date'])) ?></td>
                                <td>
                                    <a href="../event_management/delete.php?id=<?= $ev['id'] ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Hapus event ini? Semua data terkait (registrasi, sertifikat) akan ikut terhapus!')"
                                        title="Hapus Event">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>