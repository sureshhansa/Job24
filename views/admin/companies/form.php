<?php
/** @var array|null $company */
$isEdit = $company !== null;
$action = $isEdit ? url('admin/companies/edit/' . $company['id']) : url('admin/companies/create');
$val = function (string $key) use ($company) {
    if (isset($_SESSION['_old'][$key])) return e((string) $_SESSION['_old'][$key]);
    return e((string) ($company[$key] ?? ''));
};
?>
<form action="<?= e($action) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0"><?= $isEdit ? 'Edit Company' : 'New Company' ?></h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= $val('name') ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Website</label>
                            <input type="url" name="website" class="form-control" value="<?= $val('website') ?>" placeholder="https://...">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Location</label>
                            <input type="text" name="location" class="form-control" value="<?= $val('location') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>About</label>
                        <textarea name="about" rows="6" class="form-control"><?= $val('about') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Logo</h3></div>
                <div class="card-body">
                    <?php if ($isEdit && !empty($company['logo'])): ?>
                        <div class="mb-3 text-center">
                            <img src="<?= e(UPLOAD_URL . '/logos/' . $company['logo']) ?>" alt="" class="img-fluid" style="max-height:120px">
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <input type="file" name="logo" class="form-control-file" accept=".png,.jpg,.jpeg,.webp">
                        <small class="form-text text-muted">PNG, JPG or WEBP. Max 2 MB.</small>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><?= $isEdit ? 'Update' : 'Create' ?></button>
                    <a href="<?= e(url('admin/companies')) ?>" class="btn btn-default btn-block">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
<?php unset($_SESSION['_old']); ?>
