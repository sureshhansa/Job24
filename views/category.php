<?php
/** @var array $cat @var array $jobs @var array $pg */
?>
<section class="bg-light border-bottom py-4">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <div class="category-icon-lg"><i class="bi <?= e($cat['icon']) ?>"></i></div>
            <div>
                <h1 class="h3 fw-bold mb-0"><?= e($cat['name']) ?> Jobs</h1>
                <p class="text-muted mb-0"><?= number_format($pg['total']) ?> open position<?= $pg['total'] == 1 ? '' : 's' ?></p>
            </div>
        </div>
    </div>
</section>

<section class="container py-4">
    <?php if (!$jobs): ?>
        <div class="alert alert-light text-center py-5 border">No jobs in this category yet.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6 col-lg-4"><?php require BASE_PATH . '/views/partials/job_card.php'; ?></div>
            <?php endforeach; ?>
        </div>

        <?php if ($pg['pages'] > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $pg['hasPrev'] ? '' : 'disabled' ?>">
                        <a class="page-link" href="<?= e(url('category/' . $cat['slug']) . query_with(['page' => $pg['current'] - 1])) ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
                        <li class="page-item <?= $i === $pg['current'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= e(url('category/' . $cat['slug']) . query_with(['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $pg['hasNext'] ? '' : 'disabled' ?>">
                        <a class="page-link" href="<?= e(url('category/' . $cat['slug']) . query_with(['page' => $pg['current'] + 1])) ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
