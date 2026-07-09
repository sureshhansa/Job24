<?php
/** @var array $categories */
?>
<div class="row">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Add / Edit Category</h3></div>
            <div class="card-body">
                <form action="<?= e(url('admin/categories')) ?>" method="post" id="catForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="cat_id" value="">
                    <div class="form-group">
                        <label>Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="cat_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Icon (Bootstrap Icons class)</label>
                        <input type="text" name="icon" id="cat_icon" class="form-control" value="bi-briefcase" placeholder="bi-code-slash">
                        <small class="form-text text-muted">See icons at icons.getbootstrap.com</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-default" onclick="resetCat()">Clear</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3 class="card-title mb-0">Categories (<?= count($categories) ?>)</h3></div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Jobs</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                        <?php if (!$categories): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No categories yet.</td></tr>
                        <?php else: foreach ($categories as $c): ?>
                            <tr>
                                <td><i class="bi <?= e($c['icon']) ?>"></i></td>
                                <td><?= e($c['name']) ?></td>
                                <td class="text-muted small"><?= e($c['slug']) ?></td>
                                <td><?= (int)$c['job_count'] ?></td>
                                <td class="text-right">
                                    <button class="btn btn-xs btn-outline-primary"
                                        onclick="editCat(<?= (int)$c['id'] ?>, '<?= e(addslashes($c['name'])) ?>', '<?= e($c['icon']) ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="<?= e(url('admin/categories/delete/' . $c['id'])) ?>" class="btn btn-xs btn-outline-danger"
                                       data-confirm="Delete this category and its jobs?"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function editCat(id, name, icon){
    document.getElementById('cat_id').value = id;
    document.getElementById('cat_name').value = name;
    document.getElementById('cat_icon').value = icon;
    window.scrollTo({top:0, behavior:'smooth'});
}
function resetCat(){
    document.getElementById('catForm').reset();
    document.getElementById('cat_id').value = '';
}
</script>
