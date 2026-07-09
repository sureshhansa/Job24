<?php $site = site_name(); $host = parse_url(BASE_URL, PHP_URL_HOST); ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>">Home</a></li>
                    <li class="breadcrumb-item active">Privacy Policy</li>
                </ol>
            </nav>

            <h1 class="fw-bold mb-2">Privacy Policy</h1>
            <p class="text-muted mb-5">
                Last updated: <strong><?= date('d F Y') ?></strong>
            </p>

            <div class="prose-content">

                <p>
                    Welcome to <strong><?= e($site) ?></strong> ("we", "our", or "us"). We are committed to protecting your
                    personal information and your right to privacy. This Privacy Policy explains how we collect,
                    use, and safeguard your information when you visit our website
                    <strong><?= e($host) ?></strong>.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">1. Information We Collect</h4>
                <p>We collect information that you provide directly to us, including:</p>
                <ul class="text-muted">
                    <li><strong>Account Information:</strong> Name, email address, and password when you register.</li>
                    <li><strong>Profile Information:</strong> Resume, skills, work experience, education, and phone number.</li>
                    <li><strong>Application Data:</strong> Jobs you apply for and related correspondence.</li>
                    <li><strong>Contact Messages:</strong> Any messages you send via our Contact Us form.</li>
                </ul>
                <p>We also automatically collect certain technical information such as IP address, browser type, pages visited, and time spent on the site.</p>

                <h4 class="fw-semibold mt-5 mb-3">2. How We Use Your Information</h4>
                <p>We use the information we collect to:</p>
                <ul class="text-muted">
                    <li>Create and manage your account.</li>
                    <li>Match you with relevant job opportunities.</li>
                    <li>Allow employers to view your profile and resume when you apply.</li>
                    <li>Send you notifications about your applications and relevant jobs.</li>
                    <li>Respond to your queries and provide customer support.</li>
                    <li>Improve and maintain our platform.</li>
                    <li>Comply with legal obligations.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">3. How We Share Your Information</h4>
                <p>We do <strong>not</strong> sell your personal information to third parties. We may share information:</p>
                <ul class="text-muted">
                    <li><strong>With Employers:</strong> When you apply for a job, your profile and resume are shared with that employer.</li>
                    <li><strong>Service Providers:</strong> With trusted third parties who help us operate our platform (e.g., hosting, email services), under confidentiality agreements.</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to protect our rights.</li>
                </ul>

                <h4 class="fw-semibold mt-5 mb-3">4. Data Retention</h4>
                <p>
                    We retain your personal data for as long as your account is active or as needed to provide services.
                    You may delete your account at any time by contacting us, and we will remove your personal data
                    within 30 days, except where retention is required by law.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">5. Cookies</h4>
                <p>
                    We use cookies and similar technologies to maintain your session, remember your preferences,
                    and analyze site traffic. You can control cookie settings through your browser. Disabling cookies
                    may affect certain features of the site (such as staying logged in).
                </p>

                <h4 class="fw-semibold mt-5 mb-3">6. Security</h4>
                <p>
                    We take reasonable technical and organizational measures to protect your data from unauthorized
                    access, alteration, or disclosure. However, no method of transmission over the internet is
                    100% secure, and we cannot guarantee absolute security.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">7. Your Rights</h4>
                <p>You have the right to:</p>
                <ul class="text-muted">
                    <li>Access the personal data we hold about you.</li>
                    <li>Request correction of inaccurate data.</li>
                    <li>Request deletion of your data.</li>
                    <li>Withdraw consent at any time.</li>
                </ul>
                <p>To exercise any of these rights, please <a href="<?= e(url('contact')) ?>">contact us</a>.</p>

                <h4 class="fw-semibold mt-5 mb-3">8. Third-Party Links</h4>
                <p>
                    Our website may contain links to third-party websites. We are not responsible for the privacy
                    practices or content of those sites. We encourage you to review their privacy policies separately.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">9. Children's Privacy</h4>
                <p>
                    Our services are not directed to individuals under the age of 18. We do not knowingly collect
                    personal information from minors. If you believe we have inadvertently collected such data,
                    please contact us immediately.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">10. Changes to This Policy</h4>
                <p>
                    We may update this Privacy Policy from time to time. We will notify you of significant changes
                    by posting the new policy on this page with an updated date. Continued use of our platform
                    after changes constitutes your acceptance of the updated policy.
                </p>

                <h4 class="fw-semibold mt-5 mb-3">11. Contact Us</h4>
                <p>
                    If you have questions about this Privacy Policy, please reach out to us via our
                    <a href="<?= e(url('contact')) ?>">Contact Us</a> page or email us at
                    <strong><?= e(setting('contact_email', 'support@' . $host)) ?></strong>.
                </p>

            </div>

        </div>
    </div>
</div>
