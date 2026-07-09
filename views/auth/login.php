<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold text-center mb-1">Welcome back</h1>
        <p class="text-muted text-center mb-4">Log in to your candidate account</p>

        <form action="<?= e(url('login')) ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" value="<?= old('email') ?>" class="form-control" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Log In</button>
        </form>

        <p class="text-center text-muted small mb-0">
            Don't have an account? <a href="<?= e(url('register')) ?>">Sign up free</a>
        </p>
    </div>
</div>
