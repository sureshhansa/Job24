<?php
/** @var array $users */
?>
<div class="card">
    <div class="card-header"><h3 class="card-title mb-0">Candidates (<?= count($users) ?>)</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Headline</th><th>Resume</th><th>Apps</th><th>Joined</th><th>Status</th><th class="text-right">Action</th></tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No candidates yet.</td></tr>
                <?php else: foreach ($users as $u): ?>
                    <tr>
                        <td><strong><?= e($u['name']) ?></strong><?= $u['location'] ? '<br><small class="text-muted">' . e($u['location']) . '</small>' : '' ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td class="text-muted small"><?= e($u['headline'] ?: '—') ?></td>
                        <td>
                            <?php if (!empty($u['resume_file'])): ?>
                                <a href="<?= e(UPLOAD_URL . '/resumes/' . $u['resume_file']) ?>" target="_blank"><i class="bi bi-file-earmark-text"></i></a>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><?= (int)$u['app_count'] ?></td>
                        <td class="text-muted small"><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                        <td>
                            <span class="badge badge-<?= (int)$u['status'] === 1 ? 'success' : 'secondary' ?>">
                                <?= (int)$u['status'] === 1 ? 'Active' : 'Blocked' ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="<?= e(url('admin/users/toggle/' . $u['id'])) ?>" class="btn btn-xs btn-outline-<?= (int)$u['status'] === 1 ? 'danger' : 'success' ?>"
                               data-confirm="<?= (int)$u['status'] === 1 ? 'Block' : 'Unblock' ?> this candidate?">
                                <?= (int)$u['status'] === 1 ? 'Block' : 'Unblock' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
