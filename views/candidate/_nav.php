<?php
/** @var string $activeNav  Shared candidate sidebar. */
$items = [
    'dashboard'    => ['Dashboard', 'bi-speedometer2', url('dashboard')],
    'applications' => ['My Applications', 'bi-file-earmark-text', url('applications')],
    'profile'      => ['Profile & Resume', 'bi-person', url('profile')],
];
?>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="text-center mb-3">
            <div class="avatar-circle mx-auto mb-2"><?= e(strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1))) ?></div>
            <div class="fw-semibold"><?= e($_SESSION['user_name'] ?? 'Candidate') ?></div>
        </div>
        <ul class="nav flex-column candidate-nav">
            <?php foreach ($items as $key => [$label, $icon, $href]): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($activeNav ?? '') === $key ? 'active' : '' ?>" href="<?= e($href) ?>">
                        <i class="bi <?= e($icon) ?>"></i> <?= e($label) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= e(url('logout')) ?>"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </li>
        </ul>
    </div>
</div>
