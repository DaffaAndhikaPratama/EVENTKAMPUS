<?php
$page_title = "Kelola Info & Tips (Admin)";
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='../pages/dashboard.php';</script>";
    exit;
}

$articles = $conn->query("SELECT * FROM articles ORDER BY created_at DESC");
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-newspaper"></i> Kelola Info & Tips</h2>
            <p class="text-muted">Total Artikel: <strong><?= $articles->num_rows ?></strong></p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Info
        </button>
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

    <div class="row g-4">
        <?php while ($art = $articles->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($art['category']) ?></span>
                            <div class="dropdown">
                                <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li>
                                        <a class="dropdown-item text-danger"
                                            href="article_process.php?action=delete&id=<?= $art['id'] ?>"
                                            onclick="return confirm('Hapus artikel ini?')">
                                            <i class="bi bi-trash me-2"></i> Hapus
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <h5 class="fw-bold mb-2"><?= htmlspecialchars($art['title']) ?></h5>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-person me-1"></i> <?= htmlspecialchars($art['author']) ?> &bull;
                            <?= date('d M Y', strtotime($art['created_at'])) ?>
                        </p>
                        <p class="card-text text-secondary small"><?= mb_strimwidth($art['summary'], 0, 100, "...") ?></p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal Tambah Artikel -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="article_process.php" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Tambah Info & Tips</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="action" value="add">

                <div class="mb-3">
                    <label class="form-label fw-bold">Judul Artikel</label>
                    <input type="text" name="title" class="form-control" required
                        placeholder="Contoh: Tips Sukses Magang">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="category" class="form-select" required>
                            <option value="Akademik">Akademik</option>
                            <option value="Karir">Karir</option>
                            <option value="Beasiswa">Beasiswa</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Penulis</label>
                        <input type="text" name="author" class="form-control" value="Admin" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ringkasan (Summary)</label>
                    <textarea name="summary" class="form-control" rows="2" maxlength="200" required
                        placeholder="Ringkasan singkat untuk ditampilkan di kartu..."></textarea>
                    <div class="form-text">Maksimal 200 karakter.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Konten Lengkap</label>
                    <textarea name="content" class="form-control" rows="8" required
                        placeholder="Tulis konten lengkap di sini..."></textarea>
                    <div class="form-text">Mendukung format paragraf sederhana.</div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-link text-secondary text-decoration-none"
                    data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Artikel</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>