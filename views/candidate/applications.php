<?php
/** @var array $apps */
?>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3"><?php require BASE_PATH . '/views/candidate/_nav.php'; ?></div>
        <div class="col-lg-9">
            <h1 class="h4 fw-bold mb-3">My Applications</h1>

            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Job</th><th>Company</th><th>Location</th><th>Status</th><th>Applied</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php if (!$apps): ?>
                                <tr><td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    You haven't applied to any jobs yet.<br>
                                    <a href="<?= e(url('jobs')) ?>" class="btn btn-primary btn-sm mt-2">Browse Jobs</a>
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($apps as $a): ?>
                                    <tr>
                                        <td><a href="<?= e(url('job/' . $a['slug'] . '/' . $a['job_id'])) ?>" class="text-decoration-none fw-semibold"><?= e($a['title']) ?></a></td>
                                        <td class="text-muted"><?= e($a['company_name']) ?></td>
                                        <td class="text-muted small"><?= e($a['location'] ?: '—') ?></td>
                                        <td><span class="badge text-bg-<?= status_badge($a['status']) ?>"><?= e(ucfirst($a['status'])) ?></span></td>
                                        <td class="text-muted small"><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
                                        <td class="text-end">
                                            <?php if (!empty($a['resume_file'])): ?>
                                                <a href="<?= e(UPLOAD_URL . '/resumes/' . $a['resume_file']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View resume"><i class="bi bi-file-earmark"></i></a>
                                            <?php endif; ?>
                                        </td>
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
