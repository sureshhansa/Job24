<?php
/** Site Settings page. Uses setting() accessors. */
$logoUrl = site_logo_url();
$favUrl  = favicon_url();
?>
<form action="<?= e(url('admin/settings')) ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">General</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Site Name <span class="text-danger">*</span></label>
                        <input type="text" name="site_name" class="form-control" required
                               value="<?= e(setting('site_name', 'JobPortal')) ?>">
                    </div>
                    <div class="form-group">
                        <label>Tagline</label>
                        <input type="text" name="site_tagline" class="form-control"
                               value="<?= e(setting('site_tagline')) ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">SEO Meta</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" name="meta_title" class="form-control"
                               value="<?= e(setting('meta_title')) ?>">
                        <small class="form-text text-muted">Shown in the browser tab and search results.</small>
                    </div>
                    <div class="form-group">
                        <label>Meta Description</label>
                        <textarea name="meta_description" rows="3" class="form-control"><?= e(setting('meta_description')) ?></textarea>
                        <small class="form-text text-muted">Recommended length: 150–160 characters.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Logo</h3></div>
                <div class="card-body">
                    <?php if ($logoUrl !== ''): ?>
                        <div class="mb-2 p-2 bg-light rounded text-center">
                            <img src="<?= e($logoUrl) ?>" alt="Logo" style="max-height:60px;width:auto">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="remove_logo" value="1" class="form-check-input" id="remove_logo">
                            <label class="form-check-label" for="remove_logo">Remove current logo</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="site_logo" class="form-control-file" accept=".png,.jpg,.jpeg,.webp">
                    <small class="form-text text-muted">PNG, JPG or WEBP. Max 2 MB.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Favicon</h3></div>
                <div class="card-body">
                    <?php if ($favUrl !== ''): ?>
                        <div class="mb-2 p-2 bg-light rounded text-center">
                            <img src="<?= e($favUrl) ?>" alt="Favicon" style="height:32px;width:32px">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="remove_favicon" value="1" class="form-check-input" id="remove_favicon">
                            <label class="form-check-label" for="remove_favicon">Remove current favicon</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="favicon" class="form-control-file" accept=".png,.ico,.svg">
                    <small class="form-text text-muted">PNG, ICO or SVG. Max 512 KB.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-save"></i> Save Settings</button>
        </div>
    </div>
</form>
