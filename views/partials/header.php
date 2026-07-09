<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? meta_title()) ?></title>
    <meta name="description" content="<?= e($pageDesc ?? meta_description()) ?>">
    <?php if (!empty($canonical)): ?>
        <link rel="canonical" href="<?= e($canonical) ?>">
    <?php endif; ?>
    <?php if (favicon_url() !== ''): ?>
        <link rel="icon" href="<?= e(favicon_url()) ?>">
    <?php endif; ?>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(ASSET_URL) ?>/css/style.css" rel="stylesheet">
    <?= $headExtra ?? '' ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-JRJCN2E8B7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-JRJCN2E8B7');
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="<?= e(url('/')) ?>">
            <?php if (site_logo_url() !== ''): ?>
                <img src="<?= e(site_logo_url()) ?>" alt="<?= e(site_name()) ?>" style="height:32px;width:auto">
            <?php else: ?>
                <i class="bi bi-briefcase-fill"></i> <?= e(site_name()) ?>
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= e(url('jobs')) ?>">Find Jobs</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= e(url('companies')) ?>">Companies</a></li>
            </ul>
            <ul class="navbar-nav align-items-lg-center">
                <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= e($_SESSION['user_name'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= e(url('dashboard')) ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('applications')) ?>"><i class="bi bi-file-earmark-text"></i> My Applications</a></li>
                            <li><a class="dropdown-item" href="<?= e(url('profile')) ?>"><i class="bi bi-person"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= e(url('logout')) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('login')) ?>">Log In</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary" href="<?= e(url('register')) ?>">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php $flash = flash_render(); ?>
<?php if ($flash !== ''): ?>
    <div class="container mt-3"><?= $flash ?></div>
<?php endif; ?>

<main>
