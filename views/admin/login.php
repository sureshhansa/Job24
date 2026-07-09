<p class="login-box-msg">Sign in to manage the job portal</p>
<form action="<?= e(url('admin/login')) ?>" method="post">
    <?= csrf_field() ?>
    <div class="input-group mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required autofocus
               value="">
        <div class="input-group-append"><div class="input-group-text"><span class="bi bi-envelope"></span></div></div>
    </div>
    <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <div class="input-group-append"><div class="input-group-text"><span class="bi bi-lock"></span></div></div>
    </div>
    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </div>
    </div>
</form>
