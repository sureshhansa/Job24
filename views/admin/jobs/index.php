<?php
/** @var array $jobs @var int $page @var int $totalPages @var int $total @var string $filter @var array $counts */
$search = trim($_GET['q'] ?? '');
$rowStart = ($page - 1) * 20 + 1; // assumes 20 per page; offset for numbering
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Jobs</h3>
        <div class="btn-group">
            <a href="<?= e(url('admin/jobs/import')) ?>" class="btn btn-default btn-sm">
                <i class="bi bi-upload"></i> Import
            </a>
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="bi bi-download"></i> Export
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="<?= e(url('admin/jobs/export?format=csv')) ?>">Export CSV</a>
                    <a class="dropdown-item" href="<?= e(url('admin/jobs/export?format=json')) ?>">Export JSON</a>
                </div>
            </div>
            <a href="<?= e(url('admin/jobs/trash')) ?>" class="btn btn-danger btn-sm">
                <i class="bi bi-trash"></i> Trash
                <?php
                $trashCount = (int)(fetch_one("SELECT COUNT(*) AS n FROM jobs WHERE deleted_at IS NOT NULL")['n'] ?? 0);
                if ($trashCount > 0): ?>
                    <span class="badge badge-light ml-1"><?= $trashCount ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= e(url('admin/jobs/create')) ?>" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> New Job
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card-header border-bottom py-2">
        <form method="GET" action="<?= e(url('admin/jobs')) ?>" class="d-flex gap-2">
            <input type="hidden" name="filter" value="<?= e($filter) ?>">
            <div class="input-group input-group-sm" style="max-width:400px;">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-left-0"
                       placeholder="Search jobs by title or company..."
                       value="<?= e($search) ?>" autofocus>
                <?php if ($search): ?>
                    <a href="<?= e(url('admin/jobs?filter=' . $filter)) ?>" class="btn btn-outline-secondary btn-sm" title="Clear">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
        </form>
    </div>

    <!-- Filter Tabs (WordPress style) -->
    <div class="card-header p-0 border-bottom-0" style="background:transparent;">
        <ul class="nav nav-pills nav-justified" style="gap:0; border-bottom: 1px solid #dee2e6;">
            <?php
            $tabs = [
                'all'       => ['All',       'secondary'],
                'published' => ['Published', 'success'],
                'draft'     => ['Draft',     'secondary'],
                'expired'   => ['Expired',   'danger'],
            ];
            foreach ($tabs as $key => [$label, $color]):
                $active = $filter === $key;
            ?>
            <li class="nav-item">
                <a href="<?= e(url('admin/jobs?filter=' . $key . ($search ? '&q=' . urlencode($search) : ''))) ?>"
                   class="nav-link rounded-0 <?= $active ? 'active' : 'text-muted' ?>"
                   style="<?= $active ? 'border-bottom: 3px solid #007bff; background: #f8f9fa;' : '' ?>">
                    <?= $label ?>
                    <span class="badge badge-<?= $active ? 'primary' : 'secondary' ?> ml-1">
                        <?= $counts[$key] ?>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <?php if ($search): ?>
    <div class="px-3 py-2 bg-light border-bottom small text-muted">
        <i class="bi bi-filter"></i>
        Showing results for <strong>"<?= e($search) ?>"</strong> &mdash; <?= $total ?> job(s) found.
    </div>
    <?php endif; ?>

    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:40px;" class="text-center text-muted">#</th>
                    <th>Title</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Apps</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$jobs): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <?php if ($search): ?>
                                <i class="bi bi-search fs-3 d-block mb-2 opacity-50"></i>
                                "<strong><?= e($search) ?></strong>" ke liye koi job nahi mili.
                                <br><a href="<?= e(url('admin/jobs?filter=' . $filter)) ?>">Search clear karein</a>
                            <?php elseif ($filter === 'draft'): ?>
                                <i class="bi bi-file-earmark-text fs-3 d-block mb-2 opacity-50"></i>
                                Koi draft job nahi hai.
                            <?php elseif ($filter === 'expired'): ?>
                                <i class="bi bi-check-circle text-success fs-3 d-block mb-2"></i>
                                Koi expired job nahi hai! 🎉
                            <?php else: ?>
                                <i class="bi bi-briefcase fs-3 d-block mb-2 opacity-50"></i>
                                Koi job nahi mili. <a href="<?= e(url('admin/jobs/create')) ?>">Create one</a>.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: foreach ($jobs as $i => $j): ?>
                    <?php
                        $isDraft     = (int)$j['status'] !== 1;
                        $pubTs       = !empty($j['published_at']) ? strtotime($j['published_at']) : null;
                        $isScheduled = !$isDraft && $pubTs && $pubTs > time();
                        $isExpired   = !$isDraft && !empty($j['deadline']) && strtotime($j['deadline']) < mktime(0,0,0);
                        $rowNum      = $rowStart + $i;
                    ?>
                    <tr>
                        <td class="text-center text-muted small align-middle" style="font-variant-numeric: tabular-nums;">
                            <?= $rowNum ?>
                        </td>
                        <td style="max-width:340px;">
                            <a href="<?= e(url('job/' . $j['slug'] . '/' . $j['id'])) ?>" target="_blank">
                                <?= e($j['title']) ?>
                            </a>
                            <?php if ((int)$j['is_featured']): ?>
                                <span class="badge badge-warning ml-1">Featured</span>
                            <?php endif; ?>
                            <div class="small text-muted">
                                <?= e($j['category_name']) ?> &middot; <?= e(job_type_label($j['job_type'])) ?>
                            </div>
                        </td>
                        <td><?= e($j['company_name']) ?></td>
                        <td>
                            <?php if ($isExpired): ?>
                                <span class="badge badge-danger">Expired</span>
                                <div class="small text-muted">Expires: <?= date('d M Y', strtotime($j['deadline'])) ?></div>
                                <?php if ($pubTs): ?>
                                    <div class="small text-muted">Published: <?= date('d M Y', $pubTs) ?></div>
                                <?php endif; ?>
                            <?php elseif ($isDraft): ?>
                                <span class="badge badge-secondary">Draft</span>
                            <?php elseif ($isScheduled): ?>
                                <span class="badge badge-warning">
                                    <i class="bi bi-clock"></i> Scheduled
                                </span>
                                <div class="small text-muted">Publishes: <?= date('M j, g:i A', $pubTs) ?></div>
                            <?php else: ?>
                                <span class="badge badge-success">Active</span>
                                <?php if ($pubTs): ?>
                                    <div class="small text-muted">Published: <?= date('d M Y', $pubTs) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($j['deadline'])): ?>
                                    <div class="small text-muted">Expires: <?= date('d M Y', strtotime($j['deadline'])) ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= e(url('admin/applications')) ?>"><?= (int)$j['app_count'] ?></a>
                        </td>
                        <td class="text-right text-nowrap">
                            <?php if ($isExpired): ?>
                                <a href="<?= e(url('admin/jobs/renew/' . $j['id'])) ?>"
                                   class="btn btn-xs btn-success"
                                   onclick="return confirm('Job ko 30 din ke liye renew karein?')"
                                   title="Renew +30 days">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </a>
                            <?php else: ?>
                                <a href="<?= e(url('admin/jobs/toggle/' . $j['id'])) ?>"
                                   class="btn btn-xs <?= $isDraft ? 'btn-outline-success' : 'btn-outline-secondary' ?>"
                                   title="<?= $isDraft ? 'Publish' : 'Unpublish' ?>">
                                    <i class="bi <?= $isDraft ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= e(url('admin/jobs/edit/' . $j['id'])) ?>"
                               class="btn btn-xs btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
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
                <a class="page-link" href="<?= e(url('admin/jobs?filter=' . $filter . '&page=' . ($page - 1) . ($search ? '&q=' . urlencode($search) : ''))) ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= e(url('admin/jobs?filter=' . $filter . '&page=1' . ($search ? '&q=' . urlencode($search) : ''))) ?>">1</a>
                </li>
                <?php if ($start > 2): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e(url('admin/jobs?filter=' . $filter . '&page=' . $i . ($search ? '&q=' . urlencode($search) : ''))) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="<?= e(url('admin/jobs?filter=' . $filter . '&page=' . $totalPages . ($search ? '&q=' . urlencode($search) : ''))) ?>"><?= $totalPages ?></a>
                </li>
            <?php endif; ?>
            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= e(url('admin/jobs?filter=' . $filter . '&page=' . ($page + 1) . ($search ? '&q=' . urlencode($search) : ''))) ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
</div>
