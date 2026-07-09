<?php
/** @var array $jobs @var int $page @var int $totalPages @var int $total */
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-trash text-danger"></i>
            <h3 class="card-title mb-0">Trash</h3>
            <span class="badge badge-danger ml-2"><?= $total ?> jobs</span>
        </div>
        <a href="<?= e(url('admin/jobs')) ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Jobs par wapas jao
        </a>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-trash fs-1 d-block mb-3 opacity-25"></i>
            <p class="mb-0">Trash khali hai.</p>
        </div>
    <?php else: ?>
        <div class="card-body p-0">
            <div class="px-3 py-2 bg-light border-bottom small text-muted">
                <i class="bi bi-info-circle"></i>
                Trash mein jobs automatically permanently delete nahi hoti. Aapko manually karna hoga.
            </div>
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Trash mein gaya</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $i => $j): ?>
                    <tr>
                        <td class="text-muted small align-middle"><?= ($page - 1) * 20 + $i + 1 ?></td>
                        <td>
                            <span class="text-muted"><?= e($j['title']) ?></span>
                            <div class="small text-muted"><?= e($j['category_name']) ?></div>
                        </td>
                        <td><?= e($j['company_name']) ?></td>
                        <td><?= e($j['category_name']) ?></td>
                        <td class="small text-muted">
                            <?= date('d M Y, g:i A', strtotime($j['deleted_at'])) ?>
                        </td>
                        <td class="text-right text-nowrap">
                            <a href="<?= e(url('admin/jobs/restore/' . $j['id'])) ?>"
                               class="btn btn-xs btn-success"
                               title="Restore — wapas jobs mein le jao">
                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                            </a>
                            <a href="<?= e(url('admin/jobs/force-delete/' . $j['id'])) ?>"
                               class="btn btn-xs btn-danger"
                               title="Permanently delete"
                               data-confirm="Yeh job permanently delete ho jayegi. Wapas nahi aayegi. Confirm?">
                                <i class="bi bi-x-circle"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?> &middot; <?= $total ?> total</small>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e(url('admin/jobs/trash?page=' . ($page - 1))) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e(url('admin/jobs/trash?page=' . $p)) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e(url('admin/jobs/trash?page=' . ($page + 1))) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
