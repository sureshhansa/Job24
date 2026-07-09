<?php /** @var array $redirects */ ?>

<!-- Add New Redirect -->
<div class="card mb-4">
    <div class="card-header d-flex align-items-center">
        <i class="bi bi-plus-circle me-2"></i>
        <h5 class="mb-0">Add New Redirect</h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= e(url('admin/redirects')) ?>">
            <?= csrf_field() ?>
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-medium">From Path <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text text-muted small"><?= e(rtrim(BASE_URL, '/')) ?></span>
                        <input type="text" name="from_path" class="form-control"
                               placeholder="/old-job-slug" required>
                    </div>
                    <div class="form-text">The old URL path that no longer exists.</div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-medium">Redirect To <span class="text-danger">*</span></label>
                    <input type="text" name="to_url" class="form-control"
                           placeholder="https://job.guildhiring.com/jobs  OR  /jobs" required>
                    <div class="form-text">Full URL or path to redirect to.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-medium">Type</label>
                    <select name="code" class="form-select">
                        <option value="301" selected>301 — Permanent</option>
                        <option value="302">302 — Temporary</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Existing Redirects -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="bi bi-signpost-split me-2"></i>
            <h5 class="mb-0">Active Redirects</h5>
        </div>
        <span class="badge bg-primary"><?= count($redirects) ?> total</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($redirects)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-signpost-split fs-1 d-block mb-2 opacity-25"></i>
                No redirects added yet. Add your first one above.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>From Path</th>
                        <th>Redirects To</th>
                        <th class="text-center">Type</th>
                        <th>Added On</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($redirects as $r): ?>
                    <tr>
                        <td>
                            <code class="text-danger"><?= e($r['from_path']) ?></code>
                        </td>
                        <td>
                            <a href="<?= e($r['to_url']) ?>" target="_blank" class="text-break">
                                <?= e($r['to_url']) ?>
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <?php if ((int)$r['code'] === 301): ?>
                                <span class="badge bg-success">301 Permanent</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">302 Temporary</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        <td class="text-center">
                            <a href="<?= e(url('admin/redirects/delete/' . $r['id'])) ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Delete this redirect?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Help Box -->
<div class="alert alert-info mt-4 d-flex gap-3">
    <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
    <div>
        <strong>How it works:</strong>
        Jab koi visitor kisi deleted/old URL pe jaata hai, server automatically usse naye URL pe bhej deta hai.
        <br>
        <strong>301 (Permanent)</strong> — SEO ke liye best. Old URL ka juice naye URL ko milta hai. Job delete hui? Yeh use karo.<br>
        <strong>302 (Temporary)</strong> — Temporary change ke liye. SEO juice transfer nahi hota.
    </div>
</div>
