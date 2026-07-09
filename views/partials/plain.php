<?php /** @var string $content */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? meta_title()) ?></title>
    <meta name="description" content="<?= e($pageDesc ?? meta_description()) ?>">
    <?php if (favicon_url() !== ''): ?><link rel="icon" href="<?= e(favicon_url()) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(ASSET_URL) ?>/css/style.css" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <a class="navbar-brand fw-bold text-primary fs-3 text-decoration-none" href="<?= e(url('/')) ?>">
                        <?php if (site_logo_url() !== ''): ?>
                            <img src="<?= e(site_logo_url()) ?>" alt="<?= e(site_name()) ?>" style="height:40px;width:auto">
                        <?php else: ?>
                            <i class="bi bi-briefcase-fill"></i> <?= e(site_name()) ?>
                        <?php endif; ?>
                    </a>
                </div>
                <?php $flash = flash_render(); ?>
                <?= $flash ?>
                <?= $content ?>
                <p class="text-center text-muted small mt-4">
                    <a class="text-muted" href="<?= e(url('/')) ?>"><i class="bi bi-arrow-left"></i> Back to home</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
