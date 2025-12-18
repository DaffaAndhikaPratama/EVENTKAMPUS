<?php
$page_title = "Info & Tips";
require_once __DIR__ . '/../includes/header.php';

$articles = $conn->query("SELECT * FROM articles ORDER BY created_at DESC");
?>

<div class="bg-primary text-white py-5 mb-5 text-center">
    <h1 class="fw-bold">Pusat Informasi</h1>
    <p>Tips karir dan perkuliahan untukmu.</p>
</div>

<div class="container pb-5">
    
    <div class="row g-4 mb-5">
        <?php while($art = $articles->fetch_assoc()): ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <span class="badge bg-info text-dark mb-2"><?= $art['category'] ?></span>
                    <h4 class="fw-bold">
                        <a href="detail_article.php?id=<?= $art['id'] ?>" class="text-dark text-decoration-none">
                            <?= htmlspecialchars($art['title']) ?>
                        </a>
                    </h4>
                    <p class="text-muted"><?= $art['summary'] ?></p>
                    <a href="detail_article.php?id=<?= $art['id'] ?>" class="text-decoration-none fw-bold">
                        Baca Selengkapnya &rarr;
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="card border-0 shadow-sm bg-white overflow-hidden">
        <div class="card-body p-5 text-center">
            <div class="mb-3 text-success">
                <i class="bi bi-whatsapp fs-1"></i>
            </div>
            <h3 class="fw-bold mb-3">Butuh Bantuan?</h3>
            <p class="text-muted mb-4">
                Jika ada kendala pendaftaran atau pertanyaan seputar event,<br>
                silakan hubungi Admin kami langsung via WhatsApp.
            </p>
            
            <a href="https://wa.me/62895403345875?text=Halo%20Admin,%20saya%20butuh%20bantuan." target="_blank" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow-sm">
                <i class="bi bi-chat-dots-fill me-2"></i> Chat Admin Sekarang
            </a>
            
            <div class="mt-4 pt-4 border-top">
                <small class="text-muted">Online: Senin - Jumat (09.00 - 17.00 WIB)</small>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>