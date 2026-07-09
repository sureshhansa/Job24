<?php
/** @var string $active */
$nav = [
    'dashboard'    => ['Dashboard', 'bi-speedometer2', url('admin')],
    'jobs'         => ['Jobs', 'bi-briefcase', url('admin/jobs')],
    'expired-jobs' => ['Expired Jobs', 'bi-calendar-x', url('admin/jobs/expired')],
    'bulk-index'   => ['Bulk Index (Google)', 'bi-google', url('admin/bulk-index')],
    'categories'   => ['Categories', 'bi-tags', url('admin/categories')],
    'companies'    => ['Companies', 'bi-building', url('admin/companies')],
    'applications' => ['Applications', 'bi-file-earmark-text', url('admin/applications')],
    'users'        => ['Candidates', 'bi-people', url('admin/users')],
    'redirects'    => ['Redirects', 'bi-signpost-split', url('admin/redirects')],
    'settings'     => ['Site Settings', 'bi-gear', url('admin/settings')],
];
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= e(url('admin')) ?>" class="brand-link text-center">
        <i class="bi bi-briefcase-fill"></i>
        <span class="brand-text"><?= e(site_name()) ?> Admin</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                <?php foreach ($nav as $key => [$label, $icon, $href]): ?>
                    <li class="nav-item">
                        <a href="<?= e($href) ?>" class="nav-link <?= $active === $key ? 'active' : '' ?>">
                            <i class="nav-icon bi <?= e($icon) ?>"></i>
                            <p><?= e($label) ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="nav-header">SESSION</li>
                <li class="nav-item">
                    <a href="<?= e(url('admin/logout')) ?>" class="nav-link">
                        <i class="nav-icon bi bi-box-arrow-right"></i><p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="m-0"><?= e($heading ?? '') ?></h1>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?= flash_render() ?>
