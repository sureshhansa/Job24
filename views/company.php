<?php
/** @var array $company @var array $jobs */
$logo = !empty($company['logo']) ? UPLOAD_URL . '/logos/' . $company['logo'] : null;
?>
<section class="bg-light border-bottom py-4">
    <div class="container">
        <div class="d-flex gap-3 align-items-center">
            <div class="company-logo company-logo-lg flex-shrink-0">
                <?php if ($logo): ?>
                    <img src="<?= e($logo) ?>" alt="<?= e($company['name']) ?>" class="img-fluid">
                <?php else: ?>
                    <span class="logo-fallback"><?= e(strtoupper(substr($company['name'], 0, 1))) ?></span>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="h3 fw-bold mb-1"><?= e($company['name']) ?></h1>
                <div class="text-muted small d-flex flex-wrap gap-3">
                    <span><i class="bi bi-geo-alt"></i> <?= e($company['location'] ?: 'Remote') ?></span>
                    <?php if (!empty($company['website'])): ?>
                        <a href="<?= e($company['website']) ?>" target="_blank" rel="noopener" class="text-decoration-none">
                            <i class="bi bi-globe"></i> Website
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-bold">About</h2>
                    <p class="text-muted small mb-0"><?= nl2br(e((string)$company['about'])) ?: 'No description provided.' ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <h2 class="h5 fw-bold mb-3">Open positions (<?= count($jobs) ?>)</h2>
            <?php if (!$jobs): ?>
                <div class="alert alert-light border">No open positions right now.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($jobs as $job): $job['company_name'] = $company['name']; $job['company_logo'] = $company['logo']; ?>
                        <div class="col-md-6"><?php require BASE_PATH . '/views/partials/job_card.php'; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
