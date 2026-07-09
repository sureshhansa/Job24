<?php
/** @var int $code @var string $message */
?>
<section class="container py-5">
    <div class="text-center py-5">
        <div class="display-1 fw-bold text-primary"><?= (int) $code ?></div>
        <h1 class="h4 fw-bold"><?= e($message) ?></h1>
        <p class="text-muted">The page you're looking for isn't here.</p>
        <a href="<?= e(url('/')) ?>" class="btn btn-primary mt-2"><i class="bi bi-house"></i> Back to Home</a>
    </div>
</section>
