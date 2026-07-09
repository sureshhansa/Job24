<?php /** AdminLTE 3 layout — top of page. @var string $content @var string $active @var string $heading */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Admin') ?></title>
    <?php if (favicon_url() !== ''): ?><link rel="icon" href="<?= e(favicon_url()) ?>"><?php endif; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .brand-link .brand-text{font-weight:700}
        .small-box .icon i{font-size:60px}
        .content-header h1{font-size:1.5rem}
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="bi bi-list"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="<?= e(url('/')) ?>" target="_blank" class="nav-link">View Site <i class="bi bi-box-arrow-up-right small"></i></a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="bi bi-person-circle"></i> <?= e($_SESSION['admin_name'] ?? 'Admin') ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?= e(url('admin/logout')) ?>" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </li>
        </ul>
    </nav>
