<?php
/** @var array $jobs @var int $page @var int $totalPages @var int $total */
?>

<div class="alert alert-warning d-flex gap-2 align-items-start">
    <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
    <div>
        <strong><?= $total ?> expired job<?= $total !== 1 ? 's' : '' ?></strong> — yeh jobs frontend pe
        <strong>hidden hain</strong> (visitors inhe nahi dekh sakte). Renew karo ya delete karo.
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            Expired Jobs
            <span class="badge badge-warning ml-2"><?= $total ?></span>
        </h3>
        <a href="<?= e(url('admin/jobs')) ?>" class="btn btn-sm btn-default">
            <i class="bi bi-arrow-left me-1"></i> All Jobs
        </a>
    </div>

    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Expired On</th>
                    <th>Apps</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$jobs): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                            Koi expired job nahi hai! 🎉
                        </td>
                    </tr>
                <?php else: foreach ($jobs as $j): ?>
                    <?php
                        $daysAgo = (int)floor((time() - strtotime($j['deadline'])) / 86400);
                    ?>
                    <tr>
                        <td style="max-width:340px;">
                            <a href="<?= e(url('admin/jobs/edit/' . $j['id'])) ?>">
                                <?= e($j['title']) ?>
                            </a>
                            <div class="small text-muted">
                                Added: <?= date('d M Y', strtotime($j['created_at'])) ?>
                            </div>
                        </td>
                        <td><?= e($j['company_name']) ?></td>
                        <td>
                            <span class="text-danger fw-semibold">
                                <?= date('d M Y', strtotime($j['deadline'])) ?>
                            </span>
                            <div class="small text-muted"><?= $daysAgo ?> day<?= $daysAgo !== 1 ? 's' : '' ?> ago</div>
                        </td>
                        <td>
                            <a href="<?= e(url('admin/applications')) ?>"><?= (int)$j['app_count'] ?></a>
                        </td>
                        <td class="text-right text-nowrap">
                            <!-- Renew +30 days -->
                            <a href="<?= e(url('admin/jobs/renew/' . $j['id'])) ?>"
                               class="btn btn-xs btn-success"
                               title="Renew — deadline +30 days from today"
                               onclick="return confirm('Job ko 30 din ke liye renew karein?')">
                                <i class="bi bi-arrow-clockwise"></i> Renew
                            </a>
                            <!-- Edit to set custom deadline -->
                            <a href="<?= e(url('admin/jobs/edit/' . $j['id'])) ?>"
                               class="btn btn-xs btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <!-- Delete -->
                            <a href="<?= e(url('admin/jobs/delete/' . $j['id'])) ?>"
                               class="btn btn-xs btn-outline-danger" title="Delete"
                               data-confirm="Delete this job and all its applications?">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Page <?= $page ?> of <?= $totalPages ?> &middot; <?= $total ?> total
        </small>
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(url('admin/jobs/expired?page=' . ($page - 1))) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e(url('admin/jobs/expired?page=' . $i)) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(url('admin/jobs/expired?page=' . ($page + 1))) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
</div>
