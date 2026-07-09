        </div><!-- /.container-fluid -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

    <footer class="main-footer text-sm">
        <strong>&copy; <?= date('Y') ?> <?= e(site_name()) ?>.</strong> Admin Panel.
        <div class="float-right d-none d-sm-inline">PHP <?= PHP_VERSION ?></div>
    </footer>
</div><!-- /.wrapper -->

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
// Confirm destructive actions
document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('click', function (e) {
        if (!confirm(el.getAttribute('data-confirm'))) e.preventDefault();
    });
});
// Toggle external URL field on apply type change
var applySel = document.getElementById('apply_type');
if (applySel) {
    var toggle = function () {
        var box = document.getElementById('external_url_box');
        if (box) box.style.display = applySel.value === 'external' ? 'block' : 'none';
    };
    applySel.addEventListener('change', toggle); toggle();
}
// Toggle Country/State/City + Remote-countries based on Work Type
var workSel = document.getElementById('work_type');
if (workSel) {
    var geo = document.getElementById('geo_fields');
    var rcBox = document.getElementById('remote_countries_box');
    var toggleGeo = function () {
        var remote = workSel.value === 'remote';
        if (geo)   geo.style.display = remote ? 'none' : 'block';
        if (rcBox) rcBox.style.display = remote ? 'block' : 'none';
        geo && geo.querySelectorAll('input').forEach(function (i) { i.required = !remote; });
    };
    workSel.addEventListener('change', toggleGeo); toggleGeo();
}
// Show publish date/time only for Publish now & Schedule (hide for Draft)
var pubSel = document.getElementById('publish_state');
if (pubSel) {
    var pubBox = document.getElementById('publish_at_box');
    var pubInput = document.getElementById('publish_at');
    var togglePub = function () {
        if (!pubBox) return;
        pubBox.style.display = pubSel.value === 'draft' ? 'none' : 'block';
        if (pubInput) pubInput.required = pubSel.value === 'scheduled';
    };
    pubSel.addEventListener('change', togglePub); togglePub();
}
</script>
</body>
</html>
