<?php
?>
<div class="row mb-4 g-3">
    <div class="col-md-6">
        <div class="card bg-primary text-white border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center"><i class="bi bi-people-fill fs-1 me-3 opacity-50"></i>
                <div>
                    <h6 class="mb-0 text-white-50">Total User</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($adm_tot) ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-success text-white border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center"><i
                    class="bi bi-person-check-fill fs-1 me-3 opacity-50"></i>
                <div>
                    <h6 class="mb-0 text-white-50">Akun Aktif</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($adm_act) ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb-5 g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 small text-muted">KOMPOSISI ROLE</h5>
                <div style="height:250px">
                    <canvas id="c1" data-labels='["Admin","EO","Mahasiswa"]'
                        data-data='[<?= $adm_roles['admin'] ?>,<?= $adm_roles['event_organizer'] ?>,<?= $adm_roles['mahasiswa'] ?>]'
                        data-colors='["#dc3545","#0d6efd","#6c757d"]'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 small text-muted">TREN USER BARU (<?= $year ?>)</h5>
                <div style="height:250px;width:100%">
                    <canvas id="c2" data-chart-type="line"
                        data-labels='["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]'
                        data-data='<?= htmlspecialchars(json_encode(array_values($adm_trend)), ENT_QUOTES, 'UTF-8') ?>'
                        data-colors='["rgba(13,110,253,0.1)"]'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>