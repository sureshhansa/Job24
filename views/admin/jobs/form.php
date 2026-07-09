<?php
/** @var array|null $job @var array $categories @var array $companies */
$isEdit = $job !== null;
$action = $isEdit ? url('admin/jobs/edit/' . $job['id']) : url('admin/jobs/create');
$val = function (string $key, $default = '') use ($job) {
    if (isset($_SESSION['_old'][$key])) return e((string) $_SESSION['_old'][$key]);
    return e((string) ($job[$key] ?? $default));
};
$types = ['full-time','part-time','contract','internship','remote'];
$applyType = $_SESSION['_old']['apply_type'] ?? ($job['apply_type'] ?? 'internal');

// Publish state (published | draft | scheduled)
$oldState = $_SESSION['_old']['publish_state'] ?? null;
if ($oldState) {
    $publishState = $oldState;
} elseif ($isEdit) {
    $st    = (int) ($job['status'] ?? 1);
    $pubTs = !empty($job['published_at']) ? strtotime($job['published_at']) : null;
    if ($st === 0)                    $publishState = 'draft';
    elseif ($pubTs && $pubTs > time()) $publishState = 'scheduled';
    else                              $publishState = 'published';
} else {
    $publishState = 'published';
}
// datetime-local value (Y-m-dTH:i), shown in APP_TIMEZONE (IST).
// Existing job: convert its stored UTC time to IST. New job: default to "now" in IST.
$pubRaw = $_SESSION['_old']['publish_at']
    ?? (!empty($job['published_at']) ? utc_to_local_input($job['published_at']) : now_local_input());
// Remote countries already selected
$selCountries = $_SESSION['_old']['remote_countries']
    ?? (isset($job['remote_countries']) ? remote_countries($job['remote_countries']) : []);
if (!is_array($selCountries)) $selCountries = [];
$workTypeVal = $_SESSION['_old']['work_type'] ?? ($job['work_type'] ?? 'on-site');
// New jobs default to INR; existing jobs keep their stored currency.
$selCurrency = $_SESSION['_old']['salary_currency'] ?? ($job['salary_currency'] ?? 'INR');
$selPeriod   = $_SESSION['_old']['salary_period'] ?? ($job['salary_period'] ?? 'year');
?>
<form action="<?= e($action) ?>" method="post">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0"><?= $isEdit ? 'Edit Job' : 'New Job' ?></h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Job Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="job_title" class="form-control" value="<?= $val('title') ?>" required>
                    </div>
                    <div class="form-group">
    <label>Custom URL Slug</label>
    <input type="text"
           name="slug"
           id="job_slug"
           class="form-control"
           value="<?= $val('slug') ?>"
           placeholder="seo-expert-delhi">
    <small class="text-muted">
        URL: /job/your-slug
    </small>
</div>
                    <div class="form-group">
                        <label>Description <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="7" class="form-control"><?= $val('description') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Requirements</label>
                        <textarea name="requirements" id="requirements" rows="5" class="form-control"><?= $val('requirements') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Settings</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Company <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-control" required>
                            <option value="">— Select —</option>
                            <?php foreach ($companies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (string)($job['company_id'] ?? ($_SESSION['_old']['company_id'] ?? '')) === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-control" required>
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (string)($job['category_id'] ?? ($_SESSION['_old']['category_id'] ?? '')) === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Work Type</label>
                        <select name="work_type" id="work_type" class="form-control">
                            <?php foreach (WORK_TYPES as $wt): ?>
                                <option value="<?= e($wt) ?>" <?= ($job['work_type'] ?? ($_SESSION['_old']['work_type'] ?? 'on-site')) === $wt ? 'selected' : '' ?>><?= e(ucfirst($wt)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div id="geo_fields">
                        <div class="form-group">
                            <label>Country <span class="text-danger geo-req">*</span></label>
                            <input type="text" name="country" class="form-control" value="<?= $val('country') ?>" placeholder="e.g. United States">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>State <span class="text-danger geo-req">*</span></label>
                                <input type="text" name="state" class="form-control" value="<?= $val('state') ?>" placeholder="e.g. Telangana">
                            </div>
                            <div class="form-group col-6">
                                <label>City <span class="text-danger geo-req">*</span></label>
                                <input type="text" name="city" class="form-control" value="<?= $val('city') ?>" placeholder="e.g. Hyderabad">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Street Address <small class="text-muted">(optional — improves Google schema)</small></label>
                                <input type="text" name="street_address" class="form-control" value="<?= $val('street_address') ?>" placeholder="e.g. 4th Floor, Cyber Towers">
                            </div>
                            <div class="form-group col-6">
                                <label>Postal Code <small class="text-muted">(optional — improves Google schema)</small></label>
                                <input type="text" name="postal_code" class="form-control" value="<?= $val('postal_code') ?>" placeholder="e.g. 500081">
                            </div>
                        </div>
                    </div>

                    <!-- Extra Locations (Multiple jobLocation for Google schema) -->
                    <div class="form-group" id="extra_locations_box">
                        <label class="fw-bold">Additional Locations <small class="text-muted">(optional — for jobs open in multiple cities)</small></label>
                        <div id="extra_locations_list">
                            <?php foreach ($extraLocations as $eli => $el): ?>
                            <div class="extra-location-row border rounded p-3 mb-2 bg-light">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <input type="text" name="el_city[]" class="form-control form-control-sm"
                                               placeholder="City" value="<?= e($el['city'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="el_state[]" class="form-control form-control-sm"
                                               placeholder="State" value="<?= e($el['state'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="el_country[]" class="form-control form-control-sm"
                                               placeholder="Country" value="<?= e($el['country'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="el_postal[]" class="form-control form-control-sm"
                                               placeholder="Postal Code" value="<?= e($el['postal_code'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-location-btn">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" id="add_location_btn" class="btn btn-sm btn-outline-primary mt-1">
                            <i class="bi bi-plus-circle"></i> Add Another Location
                        </button>
                        <small class="form-text text-muted d-block mt-1">
                            Primary location upar set hai. Yahan extra cities/states add karo.
                        </small>
                    </div>

                    <div class="form-group" id="remote_countries_box">
                        <label>Remote — open to (countries)</label>
                        <div class="row">
                            <?php foreach (REMOTE_COUNTRIES as $rc): ?>
                                <div class="col-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="remote_countries[]"
                                               id="rc_<?= e(slugify($rc)) ?>" value="<?= e($rc) ?>"
                                               <?= in_array($rc, $selCountries, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="rc_<?= e(slugify($rc)) ?>"><?= e($rc) ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted">Used for Google Job schema (applicant location). Leave all unchecked for "Worldwide".</small>
                    </div>
                    <div class="form-group">
                        <label>Job Type</label>
                        <select name="job_type" class="form-control">
                            <?php foreach ($types as $t): ?>
                                <option value="<?= e($t) ?>" <?= ($job['job_type'] ?? ($_SESSION['_old']['job_type'] ?? 'full-time')) === $t ? 'selected' : '' ?>><?= e(job_type_label($t)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-3">
                            <label>Currency</label>
                            <select name="salary_currency" class="form-control">
                                <?php foreach (CURRENCIES as $code => $c): ?>
                                <option value="<?= e($code) ?>" <?= $selCurrency === $code ? 'selected' : '' ?>><?= e($c['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-3">
                            <label>Salary Min</label>
                            <input type="number" name="salary_min" class="form-control" value="<?= $val('salary_min') ?>" min="0">
                        </div>
                        <div class="form-group col-3">
                            <label>Salary Max</label>
                            <input type="number" name="salary_max" class="form-control" value="<?= $val('salary_max') ?>" min="0">
                        </div>
                        <div class="form-group col-3">
                            <label>Per</label>
                            <select name="salary_period" class="form-control">
                                <option value="year"  <?= $selPeriod === 'year'  ? 'selected' : '' ?>>Per year</option>
                                <option value="month" <?= $selPeriod === 'month' ? 'selected' : '' ?>>Per month</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Application Deadline</label>
                        <input type="date" name="deadline" class="form-control" value="<?= $val('deadline') ?>">
                    </div>
                    <div class="form-group">
                        <label>Apply Type</label>
                        <select name="apply_type" id="apply_type" class="form-control">
                            <option value="internal" <?= $applyType === 'internal' ? 'selected' : '' ?>>Internal (login required)</option>
                            <option value="external" <?= $applyType === 'external' ? 'selected' : '' ?>>External (redirect)</option>
                        </select>
                    </div>
                    <div class="form-group" id="external_url_box">
                        <label>External URL</label>
                        <input type="url" name="external_url" class="form-control" value="<?= $val('external_url') ?>" placeholder="https://...">
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured"
                            <?= ($job['is_featured'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_featured">Featured job</label>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label>Publishing</label>
                        <select name="publish_state" id="publish_state" class="form-control">
                            <option value="published" <?= $publishState === 'published' ? 'selected' : '' ?>>Publish now</option>
                            <option value="draft"     <?= $publishState === 'draft' ? 'selected' : '' ?>>Save as draft</option>
                            <option value="scheduled" <?= $publishState === 'scheduled' ? 'selected' : '' ?>>Schedule for later</option>
                        </select>
                    </div>
                    <div class="form-group" id="publish_at_box">
                        <label id="publish_at_label">Publish date &amp; time (<?= e(APP_TZ_LABEL) ?>)</label>
                        <input type="datetime-local" name="publish_at" id="publish_at" class="form-control" value="<?= e($pubRaw) ?>">
                        <small class="form-text text-muted">
                            For <strong>Schedule</strong>: pick a future time — the job auto-goes-live then.
                            For <strong>Publish now</strong>: leave blank for current time, or set a past/new date to (re)publish with that date.
                        </small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?= $isEdit ? 'Update Job' : 'Create Job' ?></button>
                    <a href="<?= e(url('admin/jobs')) ?>" class="btn btn-default btn-block">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
<?php unset($_SESSION['_old']); ?>

<!-- Rich text editor (TinyMCE, self-hosted via jsDelivr — GPL, no API key) -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js"></script>
<script>
if (window.tinymce) {
    tinymce.init({
        selector: '#description, #requirements',
        license_key: 'gpl',
        promotion: false,
        branding: false,
        menubar: false,
        height: 300,
        plugins: 'lists link autolink',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link | removeformat',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Quote=blockquote',
        // Keep the editor's output limited to what the server allows.
        valid_elements: 'p,br,strong/b,em/i,u,s,ul,ol,li,h2,h3,h4,blockquote,pre,code,a[href]',
        content_style: 'body{font-family:system-ui,Segoe UI,Roboto,sans-serif;font-size:14px}'
    });
}
</script>

<script>
// Add Location button
document.getElementById('add_location_btn').addEventListener('click', function() {
    const row = document.createElement('div');
    row.className = 'extra-location-row border rounded p-3 mb-2 bg-light';
    row.innerHTML = `
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="el_city[]" class="form-control form-control-sm" placeholder="City">
            </div>
            <div class="col-md-3">
                <input type="text" name="el_state[]" class="form-control form-control-sm" placeholder="State">
            </div>
            <div class="col-md-3">
                <input type="text" name="el_country[]" class="form-control form-control-sm" placeholder="Country">
            </div>
            <div class="col-md-2">
                <input type="text" name="el_postal[]" class="form-control form-control-sm" placeholder="Postal Code">
            </div>
            <div class="col-md-1 d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-location-btn">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>`;
    document.getElementById('extra_locations_list').appendChild(row);
});

// Remove location row
document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-location-btn')) {
        e.target.closest('.extra-location-row').remove();
    }
});
</script>

<script>
// Slug auto-generation — only for NEW jobs
const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
const titleInput = document.getElementById('job_title');
const slugInput  = document.getElementById('job_slug');

function toSlug(str) {
    return str
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

if (!isEdit) {
    // New job — title se auto slug
    titleInput.addEventListener('input', function () {
        slugInput.value = toSlug(this.value);
    });
}

// Dono cases mein — slug field mein type karo to live slugify ho
slugInput.addEventListener('input', function () {
    this.value = toSlug(this.value);
});

// Slug field se focus hata to final clean karo
slugInput.addEventListener('blur', function () {
    this.value = toSlug(this.value);
});
</script>
