<?php
?>
<div class="row mb-4 g-3">
    <div class="col-md-6">
        <div class="card bg-info text-white border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center"><i class="bi bi-calendar-event fs-1 me-3 opacity-50"></i>
                <div>
                    <h6 class="mb-0 text-white-50">Total Event</h6>
                    <h2 class="fw-bold mb-0"><?= $eo_tot_ev ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-primary text-white border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center"><i class="bi bi-people fs-1 me-3 opacity-50"></i>
                <div>
                    <h6 class="mb-0 text-white-50">Total Peserta</h6>
                    <h2 class="fw-bold mb-0"><?= number_format($eo_tot_part) ?></h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb-5 g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 small text-muted">SEBARAN KATEGORI</h5>
                <div style="height:250px">
                    <?php if (empty($eo_chart_cat['data'])): ?>
                        <p class="text-center text-muted mt-5">Belum ada data event.</p>
                    <?php else: ?>
                        <canvas id="c1" data-tooltip-unit="Event"
                            data-labels='<?= htmlspecialchars(json_encode($eo_chart_cat['labels']), ENT_QUOTES, 'UTF-8') ?>'
                            data-data='<?= htmlspecialchars(json_encode($eo_chart_cat['data']), ENT_QUOTES, 'UTF-8') ?>'
                            data-colors='["#0d6efd","#6610f2","#6f42c1","#d63384","#dc3545","#fd7e14","#ffc107","#198754"]'>
                        </canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold mb-3 small text-muted">PESERTA PER EVENT</h5>
                <div style="height:250px;width:100%">
                    <?php if (empty($eo_chart_event['data'])): ?>
                        <p class="text-center text-muted mt-5">Belum ada data peserta/event.</p>
                    <?php else: ?>
                        <canvas id="c2" data-chart-type="bar"
                            data-labels='<?= htmlspecialchars(json_encode($eo_chart_event['labels']), ENT_QUOTES, 'UTF-8') ?>'
                            data-data='<?= htmlspecialchars(json_encode($eo_chart_event['data']), ENT_QUOTES, 'UTF-8') ?>'
                            data-colors='["#0d6efd"]'>
                        </canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>