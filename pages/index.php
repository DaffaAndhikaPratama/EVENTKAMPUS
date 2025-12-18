<?php
require_once __DIR__ . '/../includes/header.php';
$page_title = "Beranda - EventKampus"; 
$today = date('Y-m-d H:i:s');
$search_q = isset($_GET['q']) ? trim($_GET['q']) : '';
?>

<div class="container mt-4 mb-5">
    <div class="bg-primary text-white p-5 text-center shadow-sm rounded-5 position-relative overflow-hidden">
        
        <div class="position-relative" style="z-index: 1;">
            <h1 class="fw-bold display-5 mb-3">Event Kampus Terkini</h1>
            <p class="lead mb-4 opacity-75">Temukan seminar, workshop, dan kompetisi terbaik untukmu.</p>
            
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <form action="#events" method="GET" class="d-flex gap-2 p-1 bg-white rounded-pill shadow-sm">
                        <input type="text" name="q" class="form-control border-0 rounded-pill ps-4 form-control-lg" 
                               placeholder="Cari event (co: Seminar AI...)" 
                               value="<?= htmlspecialchars($search_q) ?>">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Cari</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">

    <?php
    $sql_pop = "SELECT * FROM events WHERE event_date >= '$today' ORDER BY participants DESC LIMIT 3";
    $q_pop = $conn->query($sql_pop);
    
    if (empty($search_q) && $q_pop && $q_pop->num_rows > 0): 
    ?>
    <div class="d-flex align-items-center mb-4 ps-2">
        <div class="bg-danger text-white rounded p-1 me-2 shadow-sm"><i class="bi bi-fire"></i></div>
        <h4 class="fw-bold mb-0 text-danger">Sedang Populer</h4>
    </div>

    <div class="row g-4 mb-5">
        <?php while ($pop = $q_pop->fetch_assoc()): 
            $poster = !empty($pop['poster']) ? BASE_URL."/assets/images/poster/".$pop['poster'] : "https://via.placeholder.com/400x220?text=Event";
        ?>
        <div class="col-md-4">
            <div class="card card-event h-100 shadow-sm rounded-4">
                <div class="position-relative">
                    <img src="<?= $poster ?>" class="card-img-top" alt="<?= htmlspecialchars($pop['title']) ?>">
                    <span class="position-absolute top-0 end-0 bg-warning text-dark badge m-2 shadow-sm rounded-pill">
                        <?= htmlspecialchars($pop['category']) ?>
                    </span>
                </div>
                <div class="card-body">
                    <h5 class="fw-bold text-dark text-truncate mb-1"><?= htmlspecialchars($pop['title']) ?></h5>
                    <p class="small text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($pop['location']) ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <small class="fw-bold text-primary"><i class="bi bi-people-fill"></i> <?= number_format($pop['participants']) ?> Peserta</small>
                        <a href="<?= BASE_URL ?>/event_management/detail_event.php?id=<?= $pop['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-4">Detail</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>


    <div id="events" class="d-flex justify-content-between align-items-center mb-4 ps-2">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded p-1 me-2 shadow-sm"><i class="bi bi-calendar-event"></i></div>
            <h4 class="fw-bold mb-0">Event Terbaru</h4>
        </div>
        <?php if(!empty($search_q)): ?>
            <a href="index.php" class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-x-lg"></i> Reset</a>
        <?php endif; ?>
    </div>
    
    <div class="row g-4">
        <?php
        $sql_new = "SELECT * FROM events WHERE event_date >= '$today'";
        if (!empty($search_q)) {
            $safe_q = $conn->real_escape_string($search_q);
            $sql_new .= " AND (title LIKE '%$safe_q%' OR category LIKE '%$safe_q%')";
        }
        $sql_new .= " ORDER BY event_date ASC LIMIT 9";

        $q_events = $conn->query($sql_new);

        if ($q_events && $q_events->num_rows > 0) {
            while ($event = $q_events->fetch_assoc()):
                $poster = !empty($event['poster']) ? BASE_URL."/assets/images/poster/".$event['poster'] : "https://via.placeholder.com/400x220?text=No+Image";
                $dateObj = strtotime($event['event_date']);
        ?>
        <div class="col-md-4">
            <div class="card card-event h-100 shadow-sm rounded-4">
                <div class="position-relative">
                    <img src="<?= $poster ?>" class="card-img-top" alt="<?= htmlspecialchars($event['title']) ?>">
                    <span class="position-absolute top-0 end-0 bg-primary text-white badge m-2 shadow-sm rounded-pill">
                        <?= htmlspecialchars($event['category']) ?>
                    </span>
                </div>

                <div class="card-body d-flex flex-column">
                    <small class="text-muted mb-1">
                        <i class="bi bi-clock me-1"></i> <?= date('d M Y, H:i', $dateObj) ?> WIB
                    </small>
                    
                    <h5 class="fw-bold text-dark text-truncate mb-2">
                        <?= htmlspecialchars($event['title']) ?>
                    </h5>

                    <p class="text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= strip_tags($event['description']) ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div class="fw-bold">
                            <?php if($event['price'] == 0): ?>
                                <span class="text-success">GRATIS</span>
                            <?php else: ?>
                                <span class="text-primary">Rp <?= number_format($event['price'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="<?= BASE_URL ?>/event_management/detail_event.php?id=<?= $event['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-4 stretched-link">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; } else { ?>
            <div class="col-12">
                <div class="alert alert-light text-center py-5 border shadow-sm rounded-4">
                    <i class="bi bi-search fs-1 text-muted"></i>
                    <h5 class="fw-bold mt-3">Event tidak ditemukan.</h5>
                    <a href="index.php" class="btn btn-primary rounded-pill px-4 mt-2">Lihat Semua</a>
                </div>
            </div>
        <?php } ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>