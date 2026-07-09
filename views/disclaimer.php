<?php $site = site_name(); $host = parse_url(BASE_URL, PHP_URL_HOST); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>">Home</a></li>
                    <li class="breadcrumb-item active">Disclaimer</li>
                </ol>
            </nav>

            <h1 class="fw-bold mb-2">Disclaimer</h1>
            <p class="text-muted mb-5">Last updated: <strong><?= date('d F Y') ?></strong></p>

            <div class="alert alert-warning border-0 d-flex gap-3 mb-5">
                <div class="fs-4"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                <div>
                    Please read this disclaimer carefully before using <?= e($site) ?>. By using our platform,
                    you agree to the terms outlined below.
                </div>
            </div>

            <div class="prose-content">

                <h4 class="fw-semibold mt-4 mb-3">1. General Information Only</h4>
                <p>
                    The information provided on <?= e($site) ?> (<strong><?= e($host) ?></strong>) is for general
                    informational purposes only. While we strive to keep job listings and company information
                    accurate and up to date, we make no warranties — express or implied — about the completeness,
                    accuracy, reliability, or suitability of the content on this platform.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">2. Job Listings</h4>
                <p>
                    <?= e($site) ?> acts as a platform to display job listings submitted by employers and companies.
                    We do <strong>not</strong> guarantee:
                </p>
                <ul class="text-muted">
                    <li>The accuracy or legitimacy of any job posting.</li>
                    <li>That any vacancy listed is still open at the time of your application.</li>
                    <li>That you will be hired for any position you apply to.</li>
                    <li>The terms, salary, or conditions described in any listing.</li>
                </ul>
                <p>
                    Candidates are advised to conduct their own due diligence before applying or sharing personal
                    information with any employer.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">3. No Employment Relationship</h4>
                <p>
                    Use of this platform does not create any employment relationship between <?= e($site) ?>
                    and any candidate or employer. <?= e($site) ?> is purely an intermediary connecting job seekers
                    with potential employers.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">4. External Links</h4>
                <p>
                    Our platform may contain links to external websites. These links are provided for convenience only.
                    <?= e($site) ?> has no control over, and takes no responsibility for, the content or availability
                    of those sites.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">5. No Liability for Losses</h4>
                <p>
                    To the fullest extent permitted by applicable law, <?= e($site) ?> shall not be liable for any
                    direct, indirect, incidental, or consequential losses arising from:
                </p>
                <ul class="text-muted">
                    <li>Use or inability to use our platform.</li>
                    <li>Any decision made based on information found on this platform.</li>
                    <li>Fraudulent job postings submitted by third parties.</li>
                    <li>Data loss or breaches beyond our reasonable control.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">6. Fraud Warning</h4>
                <div class="alert alert-danger border-0">
                    <strong><i class="bi bi-shield-exclamation me-2"></i>Important:</strong>
                    Legitimate employers will <strong>never</strong> ask you to pay any fee to apply for a job or
                    during any part of the hiring process. If any employer or recruiter asks you for money,
                    please report it to us immediately via our <a href="<?= e(url('contact')) ?>">Contact Us</a> page.
                </div>

                <h4 class="fw-semibold mt-5 mb-3">7. Changes to This Disclaimer</h4>
                <p>
                    We reserve the right to update or modify this disclaimer at any time. Continued use of the
                    platform after changes are posted constitutes acceptance of the revised disclaimer.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">8. Contact</h4>
                <p>
                    For questions or concerns regarding this disclaimer, please
                    <a href="<?= e(url('contact')) ?>">contact us</a>.
                </p>

            </div>

        </div>
    </div>
</div>
