<?php
/** @var array $companies */
?>
<section class="bg-light border-bottom py-4">
    <div class="container">
        <h1 class="h3 fw-bold mb-0">Companies</h1>
        <p class="text-muted mb-0">Discover great places to work</p>
    </div>
</section>

<section class="container py-4">
    <div class="row g-3">
        <?php if (!$companies): ?>
            <div class="col-12"><div class="alert alert-light text-center border">No companies yet.</div></div>
        <?php else: ?>
            <?php foreach ($companies as $c): ?>
                <?php $logo = !empty($c['logo']) ? UPLOAD_URL . '/logos/' . $c['logo'] : null; ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex gap-3 align-items-center mb-2">
                                <div class="company-logo flex-shrink-0">
                                    <?php if ($logo): ?>
                                        <img src="<?= e($logo) ?>" alt="<?= e($c['name']) ?>" class="img-fluid">
                                    <?php else: ?>
                                        <span class="logo-fallback"><?= e(strtoupper(substr($c['name'], 0, 1))) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <h2 class="h6 mb-0 text-truncate">
                                        <a class="text-decoration-none text-dark stretched-link" href="<?= e(url('company/' . $c['slug'])) ?>"><?= e($c['name']) ?></a>
                                    </h2>
                                    <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($c['location'] ?: 'Remote') ?></small>
                                </div>
                            </div>
                            <p class="text-muted small mb-2"><?= e(excerpt((string)$c['about'], 18)) ?></p>
                            <span class="badge text-bg-light border"><?= (int) $c['job_count'] ?> open job<?= (int)$c['job_count'] == 1 ? '' : 's' ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
