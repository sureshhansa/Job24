<?php
/** @var array $featured @var array $latest @var array $remote @var array $categories @var array $stats */
?>
<section class="hero text-white">
    <div class="container py-5">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3">Apply Smart. Get Hired Fast
</h1>
                <p class="lead mb-4 opacity-75">Explore the latest Work From Home & On-Site openings from trusted employers, updated daily.</p>

                <form class="search-box bg-white rounded-3 shadow p-2" action="<?= e(url('jobs')) ?>" method="get">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control border-0" placeholder="Job title or keyword">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-0"><i class="bi bi-geo-alt text-muted"></i></span>
                                <input type="text" name="location" class="form-control border-0" placeholder="Location">
                            </div>
                        </div>
                        <div class="col-md-3 d-grid">
                            <button class="btn btn-primary btn-lg" type="submit">Search Jobs</button>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-center gap-4 mt-4 small">
                    <span><strong><?= number_format($stats['jobs']) ?></strong> Jobs</span>
                    <span><strong><?= number_format($stats['companies']) ?></strong> Companies</span>
                    <span><strong><?= number_format($stats['users']) ?></strong> Candidates</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="h4 fw-bold mb-0">Browse by category</h2>
            <p class="text-muted mb-0">Explore roles across industries</p>
        </div>
    </div>
    <div class="row g-3">
    <?php foreach ($categories as $cat): ?>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="<?= e(url('category/' . $cat['slug'])) ?>" class="text-decoration-none">
                <div class="card category-card h-100 border-0 shadow-sm text-center p-3">
                    <div class="category-icon mb-2">
                        <i class="bi <?= e($cat['icon']) ?>"></i>
                    </div>
                    <div class="fw-semibold small text-dark">
                        <?= e($cat['name']) ?>
                    </div>
                    <div class="text-muted small">
                        <?= (int) $cat['job_count'] ?> jobs
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
        </div>
</section>

<?php if ($featured): ?>
<section class="bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="h4 fw-bold mb-0">Featured jobs</h2>
                <p class="text-muted mb-0">Hand-picked roles from great companies</p>
            </div>
            <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            <?php foreach ($featured as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require BASE_PATH . '/views/partials/job_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="h4 fw-bold mb-0">Latest jobs</h2>
            <p class="text-muted mb-0">Freshly posted opportunities</p>
        </div>
        <a href="<?= e(url('jobs')) ?>" class="btn btn-outline-primary btn-sm">View all <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-3">
        <?php if (!$latest): ?>
            <div class="col-12"><div class="alert alert-light text-center">No jobs posted yet.</div></div>
        <?php else: ?>
            <?php foreach ($latest as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require BASE_PATH . '/views/partials/job_card.php'; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<?php if ($remote): ?>
<section class="bg-light py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="h4 fw-bold mb-0"><i class="bi bi-globe text-success"></i> Remote jobs</h2>
                <p class="text-muted mb-0">Work from anywhere</p>
            </div>
            <a href="<?= e(url('jobs') . '?work_type=remote') ?>" class="btn btn-outline-primary btn-sm">View all <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-3">
            <?php foreach ($remote as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <?php require BASE_PATH . '/views/partials/job_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="container py-5">
    <div class="cta-banner rounded-4 p-5 text-center text-white">
        <h2 class="fw-bold mb-2">Ready to take the next step?</h2>
        <p class="mb-4 opacity-75">Create a free account, upload your resume, and apply in one click.</p>
        <a href="<?= e(url('register')) ?>" class="btn btn-light btn-lg px-4">Get Started</a>
    </div>
</section>
