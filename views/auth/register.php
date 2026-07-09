<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <h1 class="h4 fw-bold text-center mb-1">Create your account</h1>
        <p class="text-muted text-center mb-4">Apply to jobs in one click</p>

        <form action="<?= e(url('register')) ?>" method="post" novalidate>
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" value="<?= old('name') ?>" class="form-control" required autofocus>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" value="<?= old('email') ?>" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" minlength="6" required>
                </div>
                <div class="form-text">At least 6 characters.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password_confirm" class="form-control" minlength="6" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-3">Create Account</button>
        </form>

        <p class="text-center text-muted small mb-0">
            Already have an account? <a href="<?= e(url('login')) ?>">Log in</a>
        </p>
    </div>
</div>
