<?php
/** @var array $stats @var array $recent */
$cards = [
    ['Total Applications', $stats['total'], 'bi-send', 'primary'],
    ['Pending', $stats['pending'], 'bi-hourglass-split', 'secondary'],
    ['Shortlisted', $stats['shortlisted'], 'bi-star', 'info'],
    ['Hired', $stats['hired'], 'bi-trophy', 'success'],
];
?>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3"><?php require BASE_PATH . '/views/candidate/_nav.php'; ?></div>
        <div class="col-lg-9">
            <h1 class="h4 fw-bold mb-3">Dashboard</h1>

            <div class="row g-3 mb-4">
                <?php foreach ($cards as [$label, $value, $icon, $color]): ?>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm stat-card">
                            <div class="card-body">
                                <div class="stat-icon text-<?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
                                <div class="h3 fw-bold mb-0"><?= (int) $value ?></div>
                                <div class="text-muted small"><?= e($label) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h2 class="h6 fw-bold mb-0">Recent applications</h2>
                    <a href="<?= e(url('applications')) ?>" class="small">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Job</th><th>Company</th><th>Status</th><th>Applied</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$recent): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">
                                    No applications yet. <a href="<?= e(url('jobs')) ?>">Browse jobs</a>.
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($recent as $a): ?>
                                    <tr>
                                        <td><a href="<?= e(url('job/' . $a['slug'] . '/' . $a['job_id'])) ?>" class="text-decoration-none fw-semibold"><?= e($a['title']) ?></a></td>
                                        <td class="text-muted"><?= e($a['company_name']) ?></td>
                                        <td><span class="badge text-bg-<?= status_badge($a['status']) ?>"><?= e(ucfirst($a['status'])) ?></span></td>
                                        <td class="text-muted small"><?= e(time_ago($a['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
