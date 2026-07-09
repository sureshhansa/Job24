<?php
/** @var array $job @var array $related @var bool $hasApplied */
$mainJob = $job; // preserve: the job_card partial below reuses $job
$logo = !empty($job['company_logo']) ? UPLOAD_URL . '/logos/' . $job['company_logo'] : null;
$user = is_logged_in() ? current_user() : null;

// Schema.org JobPosting JSON-LD for Google (uses published_at so Google sees update time)
$jobUrl      = url('job/' . $job['slug'] . '/' . $job['id']);
$publishedAt = date('c'); // Schema mein hamesha aaj ki date — DB ki published_at change nahi hogi
$deadlineAt  = !empty($job['deadline'])     ? date('c', strtotime($job['deadline']))     : null;

// Build PostalAddress — fill every field we have, skip empty ones
// Google prefers ISO 2-letter country code for addressCountry
$countryRaw = trim($job['country'] ?? '');
$countryMap = [
    'india'=>'IN','united states'=>'US','usa'=>'US','us'=>'US','united kingdom'=>'GB','uk'=>'GB',
    'canada'=>'CA','australia'=>'AU','germany'=>'DE','france'=>'FR','singapore'=>'SG',
    'uae'=>'AE','united arab emirates'=>'AE','pakistan'=>'PK','bangladesh'=>'BD',
    'nepal'=>'NP','sri lanka'=>'LK','philippines'=>'PH','indonesia'=>'ID','malaysia'=>'MY',
];
$countryCode = strlen($countryRaw) === 2
    ? strtoupper($countryRaw)
    : ($countryMap[strtolower($countryRaw)] ?? $countryRaw);

// Remote jobs: Google wants applicantLocationRequirements instead of a fixed address
$isRemoteJob = ($job['work_type'] ?? '') === 'remote';

if ($isRemoteJob) {
    // For remote jobs, no fixed address needed
    $postalAddress = [];
} else {
    $postalAddress = array_filter([
        '@type'           => 'PostalAddress',
        'addressLocality' => $job['city']     ?? ($job['location'] ?? ''),
        'addressRegion'   => $job['state']    ?? '',
        'addressCountry'  => $countryCode,
        'streetAddress'   => $job['street_address'] ?? '',
        'postalCode'      => $job['postal_code']    ?? '',
    ], fn($v) => $v !== '' && $v !== null);
}

// Fetch extra locations for schema
$extraLocations = fetch_all("SELECT * FROM job_locations WHERE job_id = ? ORDER BY id", [$job['id']]);

$schemaOrg = [
    '@context'          => 'https://schema.org',
    '@type'             => 'JobPosting',
    'title'             => $job['title'],
    'description'       => $job['description'],
    'datePosted'        => $publishedAt,
    'url'               => $jobUrl,
    'hiringOrganization'=> array_filter([
        '@type' => 'Organization',
        'name'  => $job['company_name'] ?? '',
        'logo'  => $logo ?? '',   // company logo URL if available
    ], fn($v) => $v !== '' && $v !== null),
    'jobLocation'       => (function() use ($postalAddress, $extraLocations, $countryMap) {
        $locations = [['@type' => 'Place', 'address' => $postalAddress]];
        foreach ($extraLocations as $el) {
            $cr = trim($el['country'] ?? '');
            $cc = strlen($cr) === 2 ? strtoupper($cr) : ($countryMap[strtolower($cr)] ?? $cr);
            $addr = array_filter([
                '@type'           => 'PostalAddress',
                'addressLocality' => $el['city']        ?? '',
                'addressRegion'   => $el['state']       ?? '',
                'addressCountry'  => $cc,
                'postalCode'      => $el['postal_code'] ?? '',
            ], fn($v) => $v !== '' && $v !== null);
            if (count($addr) > 1) {
                $locations[] = ['@type' => 'Place', 'address' => $addr];
            }
        }
        return count($locations) === 1 ? $locations[0] : $locations;
    })(),
    'employmentType'    => strtoupper(str_replace('-', '_', $job['job_type'] ?? 'FULL_TIME')),
    'directApply'       => ($job['apply_type'] ?? '') !== 'external',
    'identifier'        => [
        '@type' => 'PropertyValue',
        'name'  => 'GuildHiring',
        'value' => 'guildhiring-' . $job['id'],
    ],
];
if ($deadlineAt)                $schemaOrg['validThrough'] = $deadlineAt;
if ($isRemoteJob) {
    $schemaOrg['jobLocationType'] = 'TELECOMMUTE';
    // Remove jobLocation for remote jobs
    unset($schemaOrg['jobLocation']);
    // applicantLocationRequirements from the "Remote — open to" checkboxes.
    // Use full country NAMES (not ISO codes) — Google's entity graph treats
    // 2-letter codes like "CA"/"IN" as States/regions rather than Countries,
    // which fails validation for the "Country" type.
    $storedCountries = array_values(array_filter(
        remote_countries($job['remote_countries'] ?? null),
        fn($c) => strcasecmp($c, 'Worldwide') !== 0
    ));
    $reqs = [];
    foreach ($storedCountries as $rc) {
        $mapped = schema_country_name($rc);
        if ($mapped) $reqs[] = ['@type' => 'Country', 'name' => $mapped];
    }
    if ($reqs) {
        $schemaOrg['applicantLocationRequirements'] = count($reqs) === 1 ? $reqs[0] : $reqs;
    }
}
if (!empty($job['salary_min'])) $schemaOrg['baseSalary']  = [
    '@type'    => 'MonetaryAmount',
    'currency' => $job['salary_currency'] ?? 'INR',
    'value'    => ['@type' => 'QuantitativeValue', 'minValue' => (int)$job['salary_min'], 'maxValue' => (int)($job['salary_max'] ?? $job['salary_min']), 'unitText' => strtoupper($job['salary_period'] ?? 'MONTH')],
];
$headExtra = '<script type="application/ld+json">' . json_encode($schemaOrg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
?>
<section class="bg-light border-bottom py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= e(url('jobs')) ?>">Jobs</a></li>
                <li class="breadcrumb-item"><a href="<?= e(url('category/' . $job['category_slug'])) ?>"><?= e($job['category_name']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($job['title']) ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex gap-3 mb-3">
                        <div class="company-logo company-logo-lg flex-shrink-0">
                            <?php if ($logo): ?>
                                <img src="<?= e($logo) ?>" alt="<?= e($job['company_name']) ?>" class="img-fluid">
                            <?php else: ?>
                                <span class="logo-fallback"><?= e(strtoupper(substr($job['company_name'], 0, 1))) ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h1 class="h3 fw-bold mb-1"><?= e($job['title']) ?></h1>
                            <a href="<?= e(url('company/' . $job['company_slug'])) ?>" class="text-decoration-none text-muted">
                                <?= e($job['company_name']) ?>
                            </a>
                            <?php if (!empty($job['is_featured'])): ?>
                                <span class="badge text-bg-warning ms-1">Featured</span>
                            <?php endif; ?>
                            <?php if (!empty($job['work_type'])): ?>
                                <?= work_type_badge($job['work_type']) ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-3 text-muted small border-top border-bottom py-3 mb-3">
                        <?php $rcList = remote_countries($job['remote_countries'] ?? null); ?>
                        <span><i class="bi bi-geo-alt"></i>
                            <?php if ($job['work_type'] === 'remote'): ?>
                                Remote<?= $rcList ? ' (' . e(implode(', ', $rcList)) . ')' : '' ?>
                            <?php else: ?>
                                <?= e($job['location'] ?: 'Anywhere') ?>
                            <?php endif; ?>
                        </span>
                        <span><i class="bi bi-clock"></i> <?= e(job_type_label($job['job_type'])) ?></span>
                        <span><i class="bi bi-cash-coin"></i> <?= e(format_salary($job['salary_min'] ? (int)$job['salary_min'] : null, $job['salary_max'] ? (int)$job['salary_max'] : null, $job['salary_currency'] ?? null, $job['salary_period'] ?? null)) ?></span>
                        <span><i class="bi bi-tag"></i> <?= e($job['category_name']) ?></span>
                        <?php if (!empty($job['deadline'])): ?>
                            <span><i class="bi bi-calendar-event"></i> Apply by <?= e(date('M j, Y', strtotime($job['deadline']))) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Top Apply Button — mobile pe ekdum upar -->
                    <div class="d-lg-none mb-3">
                        <?php if ($mainJob['apply_type'] === 'external'): ?>
                            <a href="<?= e(url('go/' . $mainJob['id'])) ?>" target="_blank" rel="noopener"
                               class="btn btn-primary w-100 fw-semibold">
                                Apply Now <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php elseif ($hasApplied): ?>
                            <a href="<?= e(url('applications')) ?>" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Already Applied
                            </a>
                        <?php elseif (!is_logged_in()): ?>
                            <a href="<?= e(url('login')) ?>" class="btn btn-primary w-100 fw-semibold">Apply Now</a>
                        <?php else: ?>
                            <a href="#mobile-apply-form" class="btn btn-primary w-100 fw-semibold">Apply Now</a>
                        <?php endif; ?>
                    </div>

                    <h2 class="h5 fw-bold">Job Description</h2>
                    <div class="job-content mb-4"><?= display_richtext($job['description']) ?></div>

                    <?php if (!empty($job['requirements'])): ?>
                        <h2 class="h5 fw-bold">Requirements</h2>
                        <div class="job-content"><?= display_richtext($job['requirements']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Apply Form — job content ke baad -->
            <div id="mobile-apply-form" class="d-lg-none mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <?php if ($mainJob['apply_type'] === 'external'): ?>
                            <h2 class="h6 fw-bold">Apply on company site</h2>
                            <p class="text-muted small">This employer accepts applications on their own website.</p>
                            <a href="<?= e(url('go/' . $mainJob['id'])) ?>" target="_blank" rel="noopener"
                               class="btn btn-primary w-100">
                                Apply Now <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        <?php elseif ($hasApplied): ?>
                            <div class="text-center py-2">
                                <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                                <p class="fw-semibold mb-1">Application submitted</p>
                                <a href="<?= e(url('applications')) ?>" class="btn btn-outline-primary w-100">View my applications</a>
                            </div>
                        <?php elseif (!is_logged_in()): ?>
                            <h2 class="h6 fw-bold">Easy Apply</h2>
                            <p class="text-muted small">Log in to apply with your saved resume.</p>
                            <a href="<?= e(url('login')) ?>" class="btn btn-primary w-100 mb-2">Log in to apply</a>
                            <a href="<?= e(url('register')) ?>" class="btn btn-outline-secondary w-100">Create an account</a>
                        <?php else: ?>
                            <h2 class="h6 fw-bold">Easy Apply</h2>
                            <form action="<?= e(url('apply/' . $mainJob['id'])) ?>" method="post" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Cover letter <span class="text-muted">(optional)</span></label>
                                    <textarea name="cover_letter" rows="3" class="form-control" placeholder="Tell the employer why you're a great fit..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Resume</label>
                                    <?php if (!empty($user['resume_file'])): ?>
                                        <div class="small text-success mb-1"><i class="bi bi-check-circle"></i> Using your saved resume</div>
                                        <input type="file" name="resume" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                                        <div class="form-text">Upload a new file to replace it (optional).</div>
                                    <?php else: ?>
                                        <input type="file" name="resume" class="form-control form-control-sm" accept=".pdf,.doc,.docx" required>
                                        <div class="form-text">PDF, DOC or DOCX. Max 3 MB.</div>
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                            </form>
                        <?php endif; ?>
                        <hr>
                        <div class="small text-muted">
                            <div class="mb-1"><i class="bi bi-eye"></i> <?= (int) $mainJob['views'] ?> views</div>
                            <div><i class="bi bi-clock-history"></i> Posted <?= e(time_ago($mainJob['published_at'] ?? $mainJob['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($related): ?>
                <h2 class="h5 fw-bold mb-3">Similar jobs</h2>
                <div class="row g-3">
                    <?php foreach ($related as $job): // reuse card ?>
                        <div class="col-md-6"><?php require BASE_PATH . '/views/partials/job_card.php'; ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Apply sidebar -->
        <?php $job = $mainJob; // restore after related-jobs loop ?>
        <div class="col-lg-4 d-none d-lg-block">
            <div class="card border-0 shadow-sm sticky-lg-top apply-card">
                <div class="card-body p-4">
                    <?php if ($job['apply_type'] === 'external'): ?>
                        <h2 class="h6 fw-bold">Apply on company site</h2>
                        <p class="text-muted small">This employer accepts applications on their own website.</p>
                        <a href="<?= e(url('go/' . $job['id'])) ?>" target="_blank" rel="noopener"
                           class="btn btn-primary w-100">
                            Apply Now <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    <?php elseif ($hasApplied): ?>
                        <div class="text-center py-3">
                            <i class="bi bi-check-circle-fill text-success display-6"></i>
                            <p class="fw-semibold mb-1 mt-2">Application submitted</p>
                            <p class="text-muted small mb-3">You've already applied to this job.</p>
                            <a href="<?= e(url('applications')) ?>" class="btn btn-outline-primary w-100">View my applications</a>
                        </div>
                    <?php elseif (!is_logged_in()): ?>
                        <h2 class="h6 fw-bold">Easy Apply</h2>
                        <p class="text-muted small">Log in to apply with your saved resume.</p>
                        <a href="<?= e(url('login')) ?>" class="btn btn-primary w-100 mb-2">Log in to apply</a>
                        <a href="<?= e(url('register')) ?>" class="btn btn-outline-secondary w-100">Create an account</a>
                    <?php else: ?>
                        <h2 class="h6 fw-bold">Easy Apply</h2>
                        <form action="<?= e(url('apply/' . $job['id'])) ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Cover letter <span class="text-muted">(optional)</span></label>
                                <textarea name="cover_letter" rows="4" class="form-control" placeholder="Tell the employer why you're a great fit..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Resume</label>
                                <?php if (!empty($user['resume_file'])): ?>
                                    <div class="small text-success mb-1"><i class="bi bi-check-circle"></i> Using your saved resume</div>
                                    <input type="file" name="resume" class="form-control form-control-sm" accept=".pdf,.doc,.docx">
                                    <div class="form-text">Upload a new file to replace it (optional).</div>
                                <?php else: ?>
                                    <input type="file" name="resume" class="form-control form-control-sm" accept=".pdf,.doc,.docx" required>
                                    <div class="form-text">PDF, DOC or DOCX. Max 3 MB.</div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                        </form>
                    <?php endif; ?>

                    <hr>
                    <div class="small text-muted">
                        <div class="mb-1"><i class="bi bi-eye"></i> <?= (int) $job['views'] ?> views</div>
                        <div><i class="bi bi-clock-history"></i> Posted <?= e(time_ago($job['published_at'] ?? $job['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
