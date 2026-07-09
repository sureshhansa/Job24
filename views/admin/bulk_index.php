<?php
/** @var array $jobs @var array $results @var int $success @var int $failed */
$ran = !empty($results);
?>

<?php if ($ran): ?>
<!-- Results Screen -->
<div class="row">
    <div class="col-sm-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="bi bi-check-lg"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Submitted</span>
                <span class="info-box-number"><?= $success ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="bi bi-x-lg"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Failed</span>
                <span class="info-box-number"><?= $failed ?></span>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="bi bi-list"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total</span>
                <span class="info-box-number"><?= count($results) ?></span>
            </div>
        </div>
    </div>
</div>

<?php if ($failed > 0): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <strong><?= $failed ?> jobs fail hui.</strong>
    403 error = Search Console mein Service Account ko Owner permission do.
    Daily limit (200/day) exceed hone par bhi fail hota hai.
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Indexing Results</h3>
        <a href="<?= e(url('admin/bulk-index')) ?>" class="btn btn-sm btn-default">
            <i class="bi bi-arrow-left me-1"></i>Back — Select More Jobs
        </a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-sm mb-0">
            <thead>
                <tr>
                    <th style="width:40px"></th>
                    <th>Job Title</th>
                    <th>URL</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as [$type, $title, $jobUrl, $msg]): ?>
                <tr>
                    <td class="text-center">
                        <?= $type === 'ok'
                            ? '<i class="bi bi-check-circle-fill text-success"></i>'
                            : '<i class="bi bi-x-circle-fill text-danger"></i>' ?>
                    </td>
                    <td><?= e($title) ?></td>
                    <td><small class="text-muted"><?= e($jobUrl) ?></small></td>
                    <td>
                        <?= $type === 'ok'
                            ? '<span class="badge badge-success">Submitted</span>'
                            : '<span class="badge badge-danger">' . e($msg) . '</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- Selection Screen -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-google me-2"></i>Select Jobs to Index</h3>
        <div class="card-tools">
            <span class="badge badge-primary" id="selectedCount">0 selected</span>
        </div>
    </div>
    <div class="card-body">

        <div class="callout callout-info py-2 mb-3">
            <small>Google daily limit: <strong>200 URLs/day</strong>. Sirf jo jobs submit karni hain unhe select karo.</small>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="alert alert-warning">Koi job nahi mili.</div>
        <?php else: ?>

        <form method="post" action="<?= e(url('admin/bulk-index')) ?>" id="indexForm">
            <?= csrf_field() ?>
            <input type="hidden" name="confirm" value="yes">

            <!-- Toolbar -->
            <div class="row mb-3">
                <div class="col-sm-6">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-default" onclick="selectAll()">
                            <i class="bi bi-check-all me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-default" onclick="selectNone()">
                            <i class="bi bi-x-lg me-1"></i>Deselect All
                        </button>
                    </div>
                </div>
                <div class="col-sm-6">
                    <input type="text" id="jobSearch" class="form-control form-control-sm"
                           placeholder="Search job title..." oninput="filterJobs()">
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" id="jobsTable">
                    <thead>
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" id="checkAll" onchange="toggleAll(this)">
                            </th>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                        <tr class="job-row">
                            <td>
                                <input type="checkbox" name="job_ids[]"
                                       value="<?= $job['id'] ?>"
                                       class="job-checkbox"
                                       onchange="updateCount()">
                            </td>
                            <td class="job-title"><?= e($job['title']) ?></td>
                            <td><small class="text-muted"><?= e($job['company_name']) ?></small></td>
                            <td>
                                <?php if ((int)$job['status'] === 1): ?>
                                    <span class="badge badge-success">Published</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Submit bar -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                <span class="text-muted"><span id="selectedCount2">0</span> jobs selected</span>
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled
                        onclick="return confirm('Selected jobs Google ko submit karein?')">
                    <i class="bi bi-send me-2"></i>Submit Selected to Google
                </button>
            </div>
        </form>

        <?php endif; ?>
    </div>
</div>

<script>
function updateCount() {
    const total = document.querySelectorAll('.job-checkbox').length;
    const checked = document.querySelectorAll('.job-checkbox:checked').length;
    document.getElementById('selectedCount').textContent  = checked + ' selected';
    document.getElementById('selectedCount2').textContent = checked;
    document.getElementById('submitBtn').disabled = checked === 0;
    const ca = document.getElementById('checkAll');
    ca.indeterminate = checked > 0 && checked < total;
    ca.checked = checked === total && total > 0;
}
function toggleAll(master) {
    document.querySelectorAll('.job-row').forEach(row => {
        if (row.style.display !== 'none')
            row.querySelector('.job-checkbox').checked = master.checked;
    });
    updateCount();
}
function selectAll() {
    document.querySelectorAll('.job-row').forEach(row => {
        if (row.style.display !== 'none')
            row.querySelector('.job-checkbox').checked = true;
    });
    updateCount();
}
function selectNone() {
    document.querySelectorAll('.job-checkbox').forEach(cb => cb.checked = false);
    updateCount();
}
function filterJobs() {
    const q = document.getElementById('jobSearch').value.toLowerCase();
    document.querySelectorAll('.job-row').forEach(row => {
        row.style.display = row.querySelector('.job-title').textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
<?php endif; ?>
