<?php
/** Jobs import / export screen. */
$cols = job_io_columns();
?>
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="bi bi-upload"></i> Import Jobs</h3>
            </div>
            <form method="post" action="<?= e(url('admin/jobs/import')) ?>" enctype="multipart/form-data">
                <div class="card-body">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="file">Choose a CSV or JSON file</label>
                        <div class="custom-file">
                            <input type="file" name="file" id="file" accept=".csv,.json"
                                   class="custom-file-input" required>
                            <label class="custom-file-label" for="file">No file chosen…</label>
                        </div>
                        <small class="form-text text-muted">
                            Accepted: <code>.csv</code> (Excel/Sheets) or <code>.json</code>.
                            Rows are matched to existing jobs by <code>slug</code> — matches are
                            <strong>updated</strong>, new rows are <strong>added</strong>. Missing
                            categories &amp; companies are created automatically.
                        </small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <a href="<?= e(url('admin/jobs')) ?>" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0"><i class="bi bi-download"></i> Export Jobs</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Download every job. Use the CSV as a template for bulk editing,
                   then re-import it.</p>
                <a href="<?= e(url('admin/jobs/export?format=csv')) ?>" class="btn btn-outline-primary mb-2">
                    <i class="bi bi-filetype-csv"></i> Export CSV
                </a>
                <a href="<?= e(url('admin/jobs/export?format=json')) ?>" class="btn btn-outline-secondary mb-2">
                    <i class="bi bi-filetype-json"></i> Export JSON
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Expected columns</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    <code>title</code>, <code>category</code> &amp; <code>description</code> are
                    required on every row. The rest are optional and fall back to sensible defaults.
                </p>
                <div class="d-flex flex-wrap" style="gap:.35rem">
                    <?php foreach ($cols as $c): ?>
                        <span class="badge badge-light border"><?= e($c) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show the chosen filename in the Bootstrap custom-file label.
document.getElementById('file')?.addEventListener('change', function () {
    var label = this.nextElementSibling;
    if (label) label.textContent = this.files.length ? this.files[0].name : 'No file chosen…';
});
</script>
