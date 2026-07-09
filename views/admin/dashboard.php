<?php
/** @var array $stats @var array $recentApps @var array $recentJobs */
$boxes = [
    ['Active Jobs', $stats['active_jobs'], 'bi-briefcase', 'bg-primary', url('admin/jobs')],
    ['Applications', $stats['applications'], 'bi-file-earmark-text', 'bg-success', url('admin/applications')],
    ['Candidates', $stats['users'], 'bi-people', 'bg-warning', url('admin/users')],
    ['Companies', $stats['companies'], 'bi-building', 'bg-info', url('admin/companies')],
];
?>
<div class="row">
    <?php foreach ($boxes as [$label, $value, $icon, $bg, $href]): ?>
        <div class="col-lg-3 col-6">
            <div class="small-box <?= $bg ?>">
                <div class="inner">
                    <h3><?= (int) $value ?></h3>
                    <p><?= e($label) ?></p>
                </div>
                <div class="icon"><i class="bi <?= $icon ?>"></i></div>
                <a href="<?= e($href) ?>" class="small-box-footer">More info <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="bi bi-file-earmark-text"></i> Recent Applications</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Candidate</th><th>Job</th><th>Status</th><th>When</th></tr></thead>
                    <tbody>
                        <?php if (!$recentApps): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No applications yet.</td></tr>
                        <?php else: foreach ($recentApps as $a): ?>
                            <tr>
                                <td><?= e($a['user_name']) ?><br><small class="text-muted"><?= e($a['user_email']) ?></small></td>
                                <td><?= e($a['title']) ?></td>
                                <td><span class="badge badge-<?= status_badge($a['status']) ?>"><?= e(ucfirst($a['status'])) ?></span></td>
                                <td class="text-muted small"><?= e(time_ago($a['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><i class="bi bi-briefcase"></i> Recent Jobs</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Title</th><th>Company</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php if (!$recentJobs): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No jobs yet.</td></tr>
                        <?php else: foreach ($recentJobs as $j): ?>
                            <tr>
                                <td><a href="<?= e(url('admin/jobs/edit/' . $j['id'])) ?>"><?= e($j['title']) ?></a></td>
                                <td><?= e($j['company_name']) ?></td>
                                <td>
                                    <?php if ((int)$j['status'] === 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Draft</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <a href="<?= e(url('admin/jobs/create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Post a Job</a>
        <a href="<?= e(url('admin/companies/create')) ?>" class="btn btn-outline-secondary"><i class="bi bi-plus-lg"></i> Add Company</a>
    </div>
</div>
