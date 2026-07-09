<?php
/** @var array $companies */
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Companies (<?= count($companies) ?>)</h3>
        <a href="<?= e(url('admin/companies/create')) ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Company</a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Logo</th><th>Name</th><th>Location</th><th>Jobs</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                <?php if (!$companies): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No companies yet.</td></tr>
                <?php else: foreach ($companies as $c): ?>
                    <tr>
                        <td>
                            <?php if (!empty($c['logo'])): ?>
                                <img src="<?= e(UPLOAD_URL . '/logos/' . $c['logo']) ?>" alt="" width="40" height="40" style="object-fit:contain">
                            <?php else: ?>
                                <span class="badge badge-light"><?= e(strtoupper(substr($c['name'],0,2))) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= e(url('company/' . $c['slug'])) ?>" target="_blank"><?= e($c['name']) ?></a>
                        </td>
                        <td class="text-muted"><?= e($c['location'] ?: '—') ?></td>
                        <td><?= (int)$c['job_count'] ?></td>
                        <td class="text-right">
                            <a href="<?= e(url('admin/companies/edit/' . $c['id'])) ?>" class="btn btn-xs btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="<?= e(url('admin/companies/delete/' . $c['id'])) ?>" class="btn btn-xs btn-outline-danger"
                               data-confirm="Delete this company and its jobs?"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
