<?php /** @var array $stats */ ?>

<div class="container py-5">

    <!-- Hero Section -->
    <div class="row align-items-center g-5 mb-5">
        <div class="col-lg-6">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= e(url('/')) ?>">Home</a></li>
                    <li class="breadcrumb-item active">About Us</li>
                </ol>
            </nav>
            <h1 class="fw-bold mb-3">About <?= e(site_name()) ?></h1>
            <p class="lead text-muted mb-4">
                <?= e(site_tagline()) ?>. We are dedicated to connecting talented professionals with great opportunities across India.
            </p>
            <p class="text-muted">
                <?= e(site_name()) ?> is a modern job portal built to make hiring and job searching simple, fast, and transparent.
                Whether you're a fresher looking for your first break or an experienced professional seeking your next big move —
                we're here to help.
            </p>
            <a href="<?= e(url('jobs')) ?>" class="btn btn-primary mt-2">
                <i class="bi bi-briefcase me-2"></i>Browse Jobs
            </a>
        </div>
        <div class="col-lg-6">
            <div class="row g-3 text-center">
                <div class="col-6">
                    <div class="card border-0 bg-primary bg-opacity-10 rounded-4 p-4">
                        <div class="display-5 fw-bold text-primary"><?= number_format($stats['jobs'] ?? 0) ?>+</div>
                        <div class="text-muted small mt-1">Active Jobs</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 bg-success bg-opacity-10 rounded-4 p-4">
                        <div class="display-5 fw-bold text-success"><?= number_format($stats['companies'] ?? 0) ?>+</div>
                        <div class="text-muted small mt-1">Companies</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 bg-warning bg-opacity-10 rounded-4 p-4">
                        <div class="display-5 fw-bold text-warning"><?= number_format($stats['candidates'] ?? 0) ?>+</div>
                        <div class="text-muted small mt-1">Candidates</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 bg-info bg-opacity-10 rounded-4 p-4">
                        <div class="display-5 fw-bold text-info"><?= number_format($stats['applications'] ?? 0) ?>+</div>
                        <div class="text-muted small mt-1">Applications</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <!-- Mission & Vision -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="text-center p-4">
                <div class="text-primary fs-1 mb-3"><i class="bi bi-bullseye"></i></div>
                <h4 class="fw-semibold mb-3">Our Mission</h4>
                <p class="text-muted">
                    To bridge the gap between job seekers and employers by providing a seamless, 
                    transparent, and accessible recruitment platform for everyone.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4">
                <div class="text-primary fs-1 mb-3"><i class="bi bi-eye-fill"></i></div>
                <h4 class="fw-semibold mb-3">Our Vision</h4>
                <p class="text-muted">
                    To become the most trusted job portal in the country, empowering millions 
                    of professionals to build meaningful careers.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center p-4">
                <div class="text-primary fs-1 mb-3"><i class="bi bi-heart-fill"></i></div>
                <h4 class="fw-semibold mb-3">Our Values</h4>
                <p class="text-muted">
                    Transparency, fairness, and trust are at the core of everything we do. 
                    Every candidate and employer deserves equal opportunity.
                </p>
            </div>
        </div>
    </div>

    <hr class="my-5">

    <!-- Why Choose Us -->
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center mb-4">
            <h2 class="fw-bold">Why Choose <?= e(site_name()) ?>?</h2>
            <p class="text-muted">We make the hiring process easier for both candidates and employers.</p>
        </div>
        <div class="col-12">
            <div class="row g-3">
                <?php
                $features = [
                    ['icon' => 'bi-shield-check', 'title' => 'Verified Listings', 'desc' => 'All job postings are reviewed before going live to maintain quality.'],
                    ['icon' => 'bi-lightning-charge', 'title' => 'Quick Apply', 'desc' => 'Apply to jobs in seconds with your saved profile and resume.'],
                    ['icon' => 'bi-bell', 'title' => 'Smart Alerts', 'desc' => 'Get notified about jobs that match your skills and preferences.'],
                    ['icon' => 'bi-people', 'title' => 'For All Levels', 'desc' => 'Freshers, mid-level, and senior professionals all find a home here.'],
                    ['icon' => 'bi-geo-alt', 'title' => 'All India Coverage', 'desc' => 'Jobs from cities and towns across India, including remote options.'],
                    ['icon' => 'bi-lock', 'title' => 'Privacy First', 'desc' => 'Your data stays safe. We never sell your personal information.'],
                ];
                foreach ($features as $f): ?>
                <div class="col-md-4">
                    <div class="d-flex gap-3 p-3 rounded-3 border bg-light">
                        <div class="text-primary fs-4 mt-1"><i class="bi <?= $f['icon'] ?>"></i></div>
                        <div>
                            <h6 class="fw-semibold mb-1"><?= $f['title'] ?></h6>
                            <p class="text-muted small mb-0"><?= $f['desc'] ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="card border-0 bg-primary text-white rounded-4 text-center p-5">
        <h3 class="fw-bold mb-3">Ready to get started?</h3>
        <p class="opacity-75 mb-4">Join thousands of job seekers and employers already using <?= e(site_name()) ?>.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= e(url('register')) ?>" class="btn btn-light text-primary fw-semibold">
                <i class="bi bi-person-plus me-2"></i>Create Free Account
            </a>
            <a href="<?= e(url('contact')) ?>" class="btn btn-outline-light fw-semibold">
                <i class="bi bi-envelope me-2"></i>Contact Us
            </a>
        </div>
    </div>

</div>
