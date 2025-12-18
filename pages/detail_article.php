<?php
require_once __DIR__ . '/../includes/header.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>window.location='info_tips.php';</script>";
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();

if (!$article) {
    echo "<div class='container py-5 text-center'>
            <h3 class='text-muted'>Artikel tidak ditemukan.</h3>
            <a href='info_tips.php' class='btn btn-primary mt-3'>Kembali</a>
          </div>";
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$page_title = $article['title'];
?>

<div class="bg-light py-4 border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="info_tips.php" class="text-decoration-none">Info & Tips</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Artikel</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="mb-4 text-center">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-3"><?= htmlspecialchars($article['category']) ?></span>
                <h1 class="fw-bold display-6 mb-3"><?= htmlspecialchars($article['title']) ?></h1>
                <div class="text-muted small">
                    <span class="me-3"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($article['author']) ?></span>
                    <span><i class="bi bi-calendar-check"></i> <?= date('d F Y', strtotime($article['created_at'])) ?></span>
                </div>
            </div>

            <div class="mb-5">
                <img src="https://source.unsplash.com/1200x600/?<?= urlencode($article['category']) ?>,technology" class="img-fluid rounded-3 shadow-sm w-100" alt="Gambar Artikel">
            </div>

            <div class="article-content fs-5 text-dark" style="line-height: 1.8; text-align: justify;">
                <?= nl2br(htmlspecialchars_decode($article['content'])) ?>
            </div>

            <hr class="my-5">

            <div class="d-flex justify-content-between">
                <a href="info_tips.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Daftar</a>
                <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Simpan Artikel</button>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>