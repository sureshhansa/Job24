<?php
/**
 * Reusable job card. Expects $job (assoc array with joined company/category fields).
 */
/** @var array $job */
$logo = !empty($job['company_logo'])
    ? UPLOAD_URL . '/logos/' . $job['company_logo']
    : null;
?>
<div class="card job-card h-100 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex gap-3">
            <div class="company-logo flex-shrink-0">
                <?php if ($logo): ?>
                    <img src="<?= e($logo) ?>" alt="<?= e($job['company_name'] ?? '') ?>" class="img-fluid">
                <?php else: ?>
                    <span class="logo-fallback"><?= e(strtoupper(substr($job['company_name'] ?? 'J', 0, 1))) ?></span>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start">
                    <h3 class="h6 mb-1 text-truncate">
                        <a class="stretched-link text-dark text-decoration-none fw-semibold"
                           href="<?= e(url('job/' . $job['slug'] . '/' . $job['id'])) ?>"><?= e($job['title']) ?></a>
                    </h3>
                    <?php if (!empty($job['is_featured'])): ?>
                        <span class="badge text-bg-warning ms-2">Featured</span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small mb-2"><?= e($job['company_name'] ?? '') ?></div>
                <?php if (!empty($job['work_type'])): ?>
                    <div class="mb-2"><?= work_type_badge($job['work_type']) ?></div>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2 small text-muted">
                    <span><i class="bi bi-geo-alt"></i> <?= e($job['location'] ?: 'Anywhere') ?></span>
                    <span><i class="bi bi-clock"></i> <?= e(job_type_label($job['job_type'])) ?></span>
                    <span><i class="bi bi-cash"></i> <?= e(format_salary($job['salary_min'] ? (int)$job['salary_min'] : null, $job['salary_max'] ? (int)$job['salary_max'] : null, $job['salary_currency'] ?? null, $job['salary_period'] ?? null)) ?></span>
                </div>
            </div>
        </div>
        <p class="text-muted small mt-3 mb-2"><?= e(excerpt($job['description'], 20)) ?></p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="badge rounded-pill text-bg-light border"><?= e($job['apply_type'] === 'external' ? 'External' : 'Easy Apply') ?></span>
            <small class="text-muted"><?= e(time_ago($job['published_at'] ?? $job['created_at'])) ?></small>
        </div>
    </div>
</div>
