<?php
?>
<div class="row mb-5 g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 small text-muted">STATUS PENDAFTARAN</h5>
                <div style="height:250px">
                    <canvas id="c1" data-labels='["Diterima","Menunggu","Ditolak"]' data-tooltip-unit="Event"
                        data-data='<?= htmlspecialchars(json_encode(array_values($mhs_stat)), ENT_QUOTES, 'UTF-8') ?>'
                        data-colors='["#198754","#ffc107","#dc3545"]'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 small text-muted">KEAKTIFAN TAHUN <?= $year ?></h5>
                <div style="height:250px;width:100%">
                    <canvas id="c2" data-chart-type="line"
                        data-labels='["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]'
                        data-data='<?= htmlspecialchars(json_encode(array_values($mhs_trend)), ENT_QUOTES, 'UTF-8') ?>'
                        data-colors='["rgba(13,110,253,0.1)"]'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>