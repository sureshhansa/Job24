<?php $site = site_name(); $host = parse_url(BASE_URL, PHP_URL_HOST); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>">Home</a></li>
                    <li class="breadcrumb-item active">Terms &amp; Conditions</li>
                </ol>
            </nav>

            <h1 class="fw-bold mb-2">Terms &amp; Conditions</h1>
            <p class="text-muted mb-5">Last updated: <strong><?= date('d F Y') ?></strong></p>

            <div class="prose-content">

                <p>
                    These Terms &amp; Conditions ("Terms") govern your use of <?= e($site) ?> (the "Platform"),
                    accessible at <strong><?= e($host) ?></strong>. By accessing or using our platform, you agree
                    to be bound by these Terms. If you do not agree, please do not use the Platform.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">1. Eligibility</h4>
                <p>
                    You must be at least 18 years of age to use this Platform. By using <?= e($site) ?>, you
                    confirm that you meet this requirement and are legally capable of entering into a binding agreement.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">2. User Accounts</h4>
                <ul class="text-muted">
                    <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                    <li>You agree to provide accurate and truthful information during registration and in your profile.</li>
                    <li>You are responsible for all activities that occur under your account.</li>
                    <li>Notify us immediately if you suspect unauthorized use of your account.</li>
                    <li>We reserve the right to suspend or terminate accounts that violate these Terms.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">3. For Job Seekers (Candidates)</h4>
                <ul class="text-muted">
                    <li>You may browse, search, and apply for jobs listed on the Platform.</li>
                    <li>You must not submit false, misleading, or plagiarized resumes or profiles.</li>
                    <li>By applying for a job, you consent to sharing your profile and resume with the respective employer.</li>
                    <li>We do not guarantee interviews, offers, or employment outcomes.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">4. For Employers</h4>
                <ul class="text-muted">
                    <li>All job listings must be for genuine, legal positions within India.</li>
                    <li>Employers must not post misleading, fraudulent, or discriminatory listings.</li>
                    <li>Employers must not charge candidates any fee as part of the hiring process.</li>
                    <li>Candidate data accessed through the Platform may only be used for recruitment purposes.</li>
                    <li>We reserve the right to remove any listing that violates these Terms without notice.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">5. Prohibited Conduct</h4>
                <p>You agree not to:</p>
                <ul class="text-muted">
                    <li>Use the Platform for any unlawful purpose.</li>
                    <li>Post spam, fraudulent, or misleading content.</li>
                    <li>Harvest data, scrape, or use automated tools to access the Platform.</li>
                    <li>Impersonate another person or entity.</li>
                    <li>Attempt to gain unauthorized access to any part of the Platform.</li>
                    <li>Interfere with the security or integrity of the Platform.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">6. Intellectual Property</h4>
                <p>
                    All content on this Platform — including logos, text, graphics, and software — is the
                    property of <?= e($site) ?> or its content providers and is protected by applicable intellectual
                    property laws. You may not reproduce or distribute any content without our prior written consent.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">7. Limitation of Liability</h4>
                <p>
                    <?= e($site) ?> shall not be liable for any direct, indirect, incidental, special, or
                    consequential damages resulting from your use of — or inability to use — the Platform.
                    This includes damages arising from errors in job listings, loss of data, or unauthorized
                    access to your account.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">8. Privacy</h4>
                <p>
                    Your use of the Platform is also governed by our
                    <a href="<?= e(url('privacy-policy')) ?>">Privacy Policy</a>, which is incorporated into
                    these Terms by reference.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">9. Termination</h4>
                <p>
                    We reserve the right to suspend or terminate your access to the Platform at any time,
                    with or without cause, and without prior notice, if we believe you have violated these Terms.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">10. Governing Law</h4>
                <p>
                    These Terms shall be governed by and construed in accordance with the laws of India.
                    Any disputes arising from these Terms shall be subject to the exclusive jurisdiction
                    of the courts of India.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">11. Changes to These Terms</h4>
                <p>
                    We may update these Terms at any time. We will post the updated Terms on this page with
                    a revised date. Your continued use of the Platform after any changes constitutes your
                    acceptance of the new Terms.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">12. Contact Us</h4>
                <p>
                    If you have any questions about these Terms, please
                    <a href="<?= e(url('contact')) ?>">contact us</a>.
                </p>

            </div>

        </div>
    </div>
</div>
