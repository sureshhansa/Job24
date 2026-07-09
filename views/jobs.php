<?php
/** @var array $jobs @var array $categories @var array $pg
 *  @var string $keyword @var string $location @var string $type @var string $workType @var int $catId */
$types = ['full-time','part-time','contract','internship','remote'];
?>
<section class="bg-light border-bottom py-4">
    <div class="container">
        <form action="<?= e(url('jobs')) ?>" method="get" class="row g-2">
            <?php if ($workType !== ''): ?><input type="hidden" name="work_type" value="<?= e($workType) ?>"><?php endif; ?>
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="<?= e($keyword) ?>" class="form-control" placeholder="Job title or keyword">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-geo-alt"></i></span>
                    <input type="text" name="location" value="<?= e($location) ?>" class="form-control" placeholder="Location">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">All job types</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= e($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= e(job_type_label($t)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
    </div>
</section>

<section class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">Work Type</h2>
                    <ul class="list-unstyled mb-0 filter-list">
                        <li class="<?= $workType === '' ? 'active' : '' ?>">
                            <a href="<?= e(url('jobs') . query_with(['work_type' => null, 'page' => null])) ?>">All work types</a>
                        </li>
                        <?php foreach (WORK_TYPES as $wt): ?>
                            <li class="<?= $workType === $wt ? 'active' : '' ?>">
                                <a href="<?= e(url('jobs') . query_with(['work_type' => $wt, 'page' => null])) ?>">
                                    <?= e(work_type_label($wt)) ?> Jobs
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">Categories</h2>
                    <ul class="list-unstyled mb-0 filter-list">
                        <li class="<?= $catId === 0 ? 'active' : '' ?>">
                            <a href="<?= e(url('jobs') . query_with(['category' => null, 'page' => null])) ?>">All categories</a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li class="<?= $catId === (int)$cat['id'] ? 'active' : '' ?>">
                                <a href="<?= e(url('jobs') . query_with(['category' => $cat['id'], 'page' => null])) ?>">
                                    <i class="bi <?= e($cat['icon']) ?>"></i> <?= e($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Results -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h5 fw-bold mb-0"><?= number_format($pg['total']) ?> job<?= $pg['total'] == 1 ? '' : 's' ?> found</h1>
                <span class="text-muted small">Page <?= $pg['current'] ?> of <?= $pg['pages'] ?></span>
            </div>

            <?php if (!$jobs): ?>
                <div class="alert alert-light text-center py-5 border">
                    <i class="bi bi-search display-6 text-muted d-block mb-2"></i>
                    No jobs match your search. Try different keywords.
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($jobs as $job): ?>
                        <div class="col-md-6">
                            <?php require BASE_PATH . '/views/partials/job_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($pg['pages'] > 1): ?>
                    <nav class="mt-4" aria-label="Jobs pagination">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $pg['hasPrev'] ? '' : 'disabled' ?>">
                                <a class="page-link" href="<?= e(url('jobs') . query_with(['page' => $pg['current'] - 1])) ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
                                <li class="page-item <?= $i === $pg['current'] ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= e(url('jobs') . query_with(['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $pg['hasNext'] ? '' : 'disabled' ?>">
                                <a class="page-link" href="<?= e(url('jobs') . query_with(['page' => $pg['current'] + 1])) ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
