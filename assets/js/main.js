/* Job Portal — frontend scripts */
(function () {
    'use strict';

    // Auto-dismiss flash alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            try {
                var alert = bootstrap.Alert.getOrCreateInstance(el);
                alert.close();
            } catch (e) { el.style.display = 'none'; }
        }, 5000);
    });

    // Client-side guard: confirm before leaving an external apply link
    document.querySelectorAll('[data-external-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm('You are leaving to apply on the company website. Continue?')) {
                e.preventDefault();
            }
        });
    });
})();
