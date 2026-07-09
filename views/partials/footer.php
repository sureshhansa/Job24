</main>

<footer class="bg-dark text-light pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold"><i class="bi bi-briefcase-fill"></i> <?= e(site_name()) ?></h5>
                <p class="text-secondary mb-2"><?= e(site_tagline()) ?>.</p>
                <p class="text-secondary small">Get daily updates for new work-from-home, local jobs, data entry jobs, online jobs, and female jobs. Find your dream job today.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-semibold">For Candidates</h6>
                <ul class="list-unstyled small">
                    <li><a class="footer-link" href="<?= e(url('jobs')) ?>">Browse Jobs</a></li>
                    <li><a class="footer-link" href="<?= e(url('register')) ?>">Create Account</a></li>
                    <li><a class="footer-link" href="<?= e(url('dashboard')) ?>">Dashboard</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="fw-semibold">Company</h6>
                <ul class="list-unstyled small">
                    <li><a class="footer-link" href="<?= e(url('about')) ?>">About Us</a></li>
                    <li><a class="footer-link" href="<?= e(url('contact')) ?>">Contact Us</a></li>
                    <li><a class="footer-link" href="<?= e(url('companies')) ?>">Companies</a></li>
                    <li><a class="footer-link" href="<?= e(url('jobs')) ?>">All Jobs</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-semibold">Employers</h6>
                <p class="text-secondary small">Post jobs and manage applicants from the admin dashboard.</p>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 small text-secondary">
            <span>&copy; <?= date('Y') ?> <?= e(site_name()) ?>. All rights reserved.</span>
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a class="footer-link" href="<?= e(url('about')) ?>">About Us</a>
                <a class="footer-link" href="<?= e(url('contact')) ?>">Contact Us</a>
                <a class="footer-link" href="<?= e(url('privacy-policy')) ?>">Privacy Policy</a>
                <a class="footer-link" href="<?= e(url('terms')) ?>">Terms &amp; Conditions</a>
                <a class="footer-link" href="<?= e(url('disclaimer')) ?>">Disclaimer</a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(ASSET_URL) ?>/js/main.js"></script>
</body>
</html>
