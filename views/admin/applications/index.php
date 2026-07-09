<?php
/** @var array $apps @var string $filter */
$statuses = ['pending','reviewed','shortlisted','rejected','hired'];
?>
<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <h3 class="card-title mb-0">Applications (<?= count($apps) ?>)</h3>
        <div class="btn-group btn-group-sm">
            <a href="<?= e(url('admin/applications')) ?>" class="btn btn-<?= $filter === '' ? 'primary' : 'default' ?>">All</a>
            <?php foreach ($statuses as $s): ?>
                <a href="<?= e(url('admin/applications') . '?status=' . $s) ?>"
                   class="btn btn-<?= $filter === $s ? 'primary' : 'default' ?>"><?= e(ucfirst($s)) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Candidate</th><th>Job</th><th>Company</th><th>Resume</th><th>Applied</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if (!$apps): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No applications found.</td></tr>
                <?php else: foreach ($apps as $a): ?>
                    <tr>
                        <td>
                            <strong><?= e($a['user_name']) ?></strong><br>
                            <small class="text-muted"><?= e($a['user_email']) ?><?= $a['user_phone'] ? ' · ' . e($a['user_phone']) : '' ?></small>
                            <?php if (!empty($a['cover_letter'])): ?>
                                <a href="#" class="d-block small" data-toggle="collapse" data-target="#cl<?= (int)$a['id'] ?>">View cover letter</a>
                                <div class="collapse small text-muted border rounded p-2 mt-1" id="cl<?= (int)$a['id'] ?>"><?= nl2br(e($a['cover_letter'])) ?></div>
                            <?php endif; ?>
                        </td>
                        <td><a href="<?= e(url('job/' . $a['slug'])) ?>" target="_blank"><?= e($a['title']) ?></a></td>
                        <td><?= e($a['company_name']) ?></td>
                        <td>
                            <?php if (!empty($a['resume_file'])): ?>
                                <a href="<?= e(UPLOAD_URL . '/resumes/' . $a['resume_file']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary">
                                    <i class="bi bi-file-earmark-arrow-down"></i> View
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
                        <td>
                            <form action="<?= e(url('admin/applications/status')) ?>" method="post" class="form-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                    <?php foreach ($statuses as $s): ?>
                                        <option value="<?= e($s) ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
