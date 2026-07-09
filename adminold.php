<?php
/**
 * Admin controllers. Dispatched from index.php for any /admin/* route.
 * AdminLTE-based dashboard.
 */

declare(strict_types=1);

function admin_dispatch(array $seg): void
{
    $action = $seg[0] ?? '';

    // Public admin routes (no auth)
    if ($action === 'login')  { admin_login();  return; }
    if ($action === 'logout') { admin_logout(); return; }

    // Everything else requires an authenticated admin
    require_admin();

    switch ($action) {
        case '':           admin_dashboard();                        break;
        case 'jobs':       admin_jobs($seg);                         break;
        case 'bulk-index': admin_bulk_index();                       break;
        case 'categories': admin_categories($seg);                   break;
        case 'companies':  admin_companies($seg);                    break;
        case 'applications': admin_applications($seg);               break;
        case 'users':      admin_users($seg);                        break;
        case 'settings':   admin_settings();                         break;
        case 'redirects':  admin_redirects($seg);                    break;
        default:           admin_404();
    }
}

// =====================================================================
//  AUTH
// =====================================================================

function admin_login(): void
{
    if (is_admin()) redirect('admin');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $email = strtolower(post_param('email'));
        $pass  = (string) ($_POST['password'] ?? '');
        $admin = fetch_one("SELECT * FROM admins WHERE email = ?", [$email]);
        if (!$admin || !password_verify($pass, $admin['password'])) {
            flash('error', 'Invalid administrator credentials.');
            redirect('admin/login');
        }
        login_admin($admin);
        flash('success', 'Welcome back, ' . $admin['name'] . '.');
        redirect('admin');
    }

    admin_view('login', [], ['title' => 'Admin Login', 'layout' => 'plain']);
}

function admin_logout(): void
{
    logout_admin();
    flash('info', 'Logged out of admin.');
    redirect('admin/login');
}

// =====================================================================
//  DASHBOARD
// =====================================================================

function admin_dashboard(): void
{
    $stats = [
        'jobs'         => (int) fetch_col("SELECT COUNT(*) FROM jobs"),
        'active_jobs'  => (int) fetch_col("SELECT COUNT(*) FROM jobs WHERE status = 1"),
        'applications' => (int) fetch_col("SELECT COUNT(*) FROM applications"),
        'users'        => (int) fetch_col("SELECT COUNT(*) FROM users"),
        'companies'    => (int) fetch_col("SELECT COUNT(*) FROM companies"),
        'categories'   => (int) fetch_col("SELECT COUNT(*) FROM categories"),
    ];
    $recentApps = fetch_all(
        "SELECT a.*, j.title, u.name AS user_name, u.email AS user_email
         FROM applications a
         JOIN jobs j ON j.id = a.job_id
         JOIN users u ON u.id = a.user_id
         ORDER BY a.created_at DESC LIMIT 6"
    );
    $recentJobs = fetch_all(
        "SELECT j.*, c.name AS company_name FROM jobs j
         JOIN companies c ON c.id = j.company_id
         ORDER BY j.created_at DESC LIMIT 6"
    );
    admin_view('dashboard', compact('stats', 'recentApps', 'recentJobs'),
        ['title' => 'Dashboard', 'active' => 'dashboard']);
}

// =====================================================================
//  JOBS CRUD
// =====================================================================

function admin_jobs(array $seg): void
{
    $sub = $seg[1] ?? '';

    if ($sub === 'create' || $sub === 'edit') {
        admin_job_form($sub === 'edit' ? (int) ($seg[2] ?? 0) : null);
        return;
    }
    if ($sub === 'delete') {
        admin_job_delete((int) ($seg[2] ?? 0));
        return;
    }
    if ($sub === 'toggle') {
        admin_job_toggle((int) ($seg[2] ?? 0));
        return;
    }
    if ($sub === 'export') {
        admin_jobs_export();
        return;
    }
    if ($sub === 'import') {
        admin_jobs_import();
        return;
    }
    if ($sub === 'expired') {
        admin_expired_jobs();
        return;
    }
    if ($sub === 'trash') {
        admin_jobs_trash();
        return;
    }
    if ($sub === 'restore') {
        admin_job_restore((int) ($seg[2] ?? 0));
        return;
    }
    if ($sub === 'force-delete') {
        admin_job_force_delete((int) ($seg[2] ?? 0));
        return;
    }
    if ($sub === 'renew') {
        admin_job_renew((int)($seg[2] ?? 0));
        return;
    }

    // list
    $filter  = $_GET['filter'] ?? 'all';
    $perPage = 20;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $offset  = ($page - 1) * $perPage;

    // Search query
    $search = trim($_GET['q'] ?? '');

    $where = match($filter) {
        'published' => "j.status = 1 AND j.deleted_at IS NULL AND (j.deadline IS NULL OR j.deadline >= CURDATE())",
        'draft'     => "j.status = 0 AND j.deleted_at IS NULL",
        'expired'   => "j.status = 1 AND j.deleted_at IS NULL AND j.deadline IS NOT NULL AND j.deadline < CURDATE()",
        default     => "1=1",
    };

    // Append search filter
    if ($search !== '') {
        $searchLike = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
        $where .= " AND (j.title LIKE ? OR c.name LIKE ?)";
        $searchParams = [$searchLike, $searchLike];
    } else {
        $searchParams = [];
    }

    $counts = [
        'all'       => (int)(fetch_one("SELECT COUNT(*) AS n FROM jobs j WHERE j.deleted_at IS NULL")['n'] ?? 0),
        'published' => (int)(fetch_one("SELECT COUNT(*) AS n FROM jobs j WHERE j.status = 1 AND j.deleted_at IS NULL AND (j.deadline IS NULL OR j.deadline >= CURDATE())")['n'] ?? 0),
        'draft'     => (int)(fetch_one("SELECT COUNT(*) AS n FROM jobs j WHERE j.status = 0 AND j.deleted_at IS NULL")['n'] ?? 0),
        'expired'   => (int)(fetch_one("SELECT COUNT(*) AS n FROM jobs j WHERE j.status = 1 AND j.deleted_at IS NULL AND j.deadline IS NOT NULL AND j.deadline < CURDATE()")['n'] ?? 0),
    ];

    // Total with search applied
    $totalRow = fetch_one(
        "SELECT COUNT(*) AS n FROM jobs j
          JOIN companies c ON c.id = j.company_id
          WHERE $where",
        $searchParams
    );
    $total = (int)($totalRow['n'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset  = ($page - 1) * $perPage;

    $jobs = fetch_all(
        "SELECT j.id, j.title, j.slug, j.status, j.job_type, j.apply_type,
                j.is_featured, j.published_at, j.created_at, j.deadline,
                c.name AS company_name, cat.name AS category_name,
                (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
         FROM jobs j
         JOIN companies c ON c.id = j.company_id
         JOIN categories cat ON cat.id = j.category_id
         WHERE $where
         ORDER BY j.published_at DESC
         LIMIT $perPage OFFSET $offset",
        $searchParams
    );
    admin_view('jobs/index', compact('jobs', 'page', 'totalPages', 'total', 'filter', 'counts'), ['title' => 'Jobs', 'active' => 'jobs']);
}

function admin_job_form(?int $id): void
{
    $job = null;
    if ($id) {
        $job = fetch_one("SELECT * FROM jobs WHERE id = ?", [$id]);
        if (!$job) { admin_404(); return; }
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $title       = post_param('title');
        $categoryId  = (int) post_param('category_id');
        $companyId   = (int) post_param('company_id');
        $jobType     = post_param('job_type');
        $workType    = post_param('work_type');
        $country        = post_param('country');
        $state          = post_param('state');
        $city           = post_param('city');
        $street_address = post_param('street_address');
        $postal_code    = post_param('postal_code');
        $salaryMin   = post_param('salary_min');
        $salaryMax   = post_param('salary_max');
        $salaryCurrency = strtoupper(post_param('salary_currency'));
        if (!isset(CURRENCIES[$salaryCurrency])) $salaryCurrency = DEFAULT_CURRENCY;
        $salaryPeriod = post_param('salary_period');
        if (!isset(SALARY_PERIODS[$salaryPeriod])) $salaryPeriod = DEFAULT_SALARY_PERIOD;
        $description = clean_html(post_param('description'));
        $requirements= clean_html(post_param('requirements'));
        $applyType   = post_param('apply_type') === 'external' ? 'external' : 'internal';
        $externalUrl = post_param('external_url');
        $deadline    = post_param('deadline');
        $featured    = isset($_POST['is_featured']) ? 1 : 0;

        // Publish state: published | draft | scheduled
        $publishState = post_param('publish_state');
        if (!in_array($publishState, ['published', 'draft', 'scheduled'], true)) {
            $publishState = 'published';
        }
        $publishAtRaw = post_param('publish_at'); // datetime-local "Y-m-dTH:i"

        // Remote country targeting (only kept for remote jobs)
        $countriesIn = $_POST['remote_countries'] ?? [];
        if (!is_array($countriesIn)) $countriesIn = [];
        $countriesIn = array_values(array_intersect($countriesIn, REMOTE_COUNTRIES));

        $validTypes = ['full-time','part-time','contract','internship','remote'];
        if (!in_array($workType, WORK_TYPES, true)) $workType = 'on-site';

        $errors = [];
        if ($title === '')                       $errors[] = 'Title is required.';
        if (!$categoryId)                         $errors[] = 'Please choose a category.';
        if (!$companyId)                          $errors[] = 'Please choose a company.';
        if (!in_array($jobType, $validTypes, true)) $errors[] = 'Invalid job type.';
        if (trim(strip_tags($description)) === '') $errors[] = 'Description is required.';
        if ($applyType === 'external' && !filter_var($externalUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'A valid external URL is required for external apply.';
        }
        // Geo required for Hybrid and On-site; ignored for Remote.
        if ($workType !== 'remote') {
            if ($country === '') $errors[] = 'Country is required for ' . $workType . ' jobs.';
            if ($state === '')   $errors[] = 'State is required for ' . $workType . ' jobs.';
            if ($city === '')    $errors[] = 'City is required for ' . $workType . ' jobs.';
        }
        // Scheduled needs a valid future date. Input is in APP_TIMEZONE (IST) and
        // converted to UTC for storage/comparison (DB NOW() is UTC).
        $publishAtUtc = local_input_to_utc($publishAtRaw);
        $scheduleTs   = $publishAtUtc ? $publishAtUtc->getTimestamp() : false;
        if ($publishState === 'scheduled') {
            if ($scheduleTs === false) {
                $errors[] = 'Please pick a date/time to schedule this job.';
            } elseif ($scheduleTs <= time()) {
                $errors[] = 'Scheduled date/time must be in the future.';
            }
        }

        if ($errors) {
            foreach ($errors as $err) flash('error', $err);
            set_old($_POST);
            redirect($id ? "admin/jobs/edit/$id" : 'admin/jobs/create');
        }

        // Resolve status + published_at from the chosen publish state.
        //   draft     -> status 0; keep any existing published_at
        //   published -> status 1; use chosen date if given, else now (allows re/back-dating)
        //   scheduled -> status 1; future date (hidden until then by live_sql())
        $status = ($publishState === 'draft') ? 0 : 1;
        if ($publishState === 'draft') {
            $publishedAt = $job['published_at'] ?? null;
        } elseif ($publishState === 'scheduled') {
            $publishedAt = $publishAtUtc->format('Y-m-d H:i:s'); // UTC
        } else { // published
            $publishedAt = $publishAtUtc ? $publishAtUtc->format('Y-m-d H:i:s') : gmdate('Y-m-d H:i:s'); // UTC
        }

        // Remote => clear geo. Build a display location string for listings/search.
        if ($workType === 'remote') {
            $country = $state = $city = '';
            $location = $countriesIn ? ('Remote · ' . implode(', ', $countriesIn)) : 'Remote';
            $remoteCountries = $countriesIn ? implode(', ', $countriesIn) : null;
        } else {
            $location = trim(implode(', ', array_filter([$city, $state, $country])));
            $remoteCountries = null;
        }

        $params = [
            'title'         => $title,
            'category_id'   => $categoryId,
            'company_id'    => $companyId,
            'location'      => $location ?: null,
            'job_type'      => $jobType,
            'work_type'     => $workType,
            'country'        => $country ?: null,
            'state'          => $state ?: null,
            'city'           => $city ?: null,
            'street_address' => $street_address ?: null,
            'postal_code'    => $postal_code ?: null,
            'remote_countries' => $remoteCountries,
            'salary_min'    => $salaryMin !== '' ? (int) $salaryMin : null,
            'salary_max'    => $salaryMax !== '' ? (int) $salaryMax : null,
            'salary_currency' => $salaryCurrency,
            'salary_period'   => $salaryPeriod,
            'description'   => $description,
            'requirements'  => $requirements ?: null,
            'apply_type'    => $applyType,
            'external_url'  => $applyType === 'external' ? $externalUrl : null,
            'is_featured'   => $featured,
            'status'        => $status,
            'published_at'  => $publishedAt,
            'deadline'      => $deadline ?: null,
        ];

        // SLUG: Only generate new slug for NEW jobs.
        if ($id && !empty($job['slug'])) {
            $params['slug'] = $job['slug'];
        } else {
            $params['slug'] = unique_slug(slugify($title), 'jobs', $id);
        }

        // Extra locations (multiple jobLocation support)
        $extraLocations = [];
        $elCities    = $_POST['el_city']    ?? [];
        $elStates    = $_POST['el_state']   ?? [];
        $elCountries = $_POST['el_country'] ?? [];
        $elPostals   = $_POST['el_postal']  ?? [];
        foreach ($elCities as $i => $elCity) {
            $elCity    = trim((string)$elCity);
            $elState   = trim((string)($elStates[$i]   ?? ''));
            $elCountry = trim((string)($elCountries[$i] ?? ''));
            $elPostal  = trim((string)($elPostals[$i]  ?? ''));
            if ($elCity !== '' || $elState !== '' || $elCountry !== '') {
                $extraLocations[] = [
                    'city'        => $elCity    ?: null,
                    'state'       => $elState   ?: null,
                    'country'     => $elCountry ?: null,
                    'postal_code' => $elPostal  ?: null,
                ];
            }
        }

        // AUTO MOVE TO TOP: Jab bhi published job edit ho, published_at = NOW()
        if ($id && $publishState === 'published') {
            $params['published_at'] = gmdate('Y-m-d H:i:s');
        }

        $savedId = job_save($params, $id);

        // Save extra locations
        q("DELETE FROM job_locations WHERE job_id = ?", [$savedId]);
        foreach ($extraLocations as $loc) {
            q("INSERT INTO job_locations (job_id, city, state, country, postal_code) VALUES (?, ?, ?, ?, ?)",
              [$savedId, $loc['city'], $loc['state'], $loc['country'], $loc['postal_code']]);
        }

        // Direct DB update as safety net
        if ($id && $publishState === 'published') {
            q("UPDATE jobs SET published_at = NOW() WHERE id = ?", [$id]);
        }
        flash('success', publish_message($publishState, $publishedAt));
        clear_old();
        redirect('admin/jobs');
    }

    $categories = fetch_all("SELECT id, name FROM categories ORDER BY name");
    $companies  = fetch_all("SELECT id, name FROM companies ORDER BY name");
    admin_view('jobs/form', array_merge(compact('job', 'categories', 'companies'), ['extraLocations' => $extraLocations ?? []]),
        ['title' => $id ? 'Edit Job' : 'New Job', 'active' => 'jobs']);
}

function admin_job_delete(int $id): void
{
    // Soft delete — move to trash
    $job = fetch_one("SELECT slug FROM jobs WHERE id = ?", [$id]);
    if (!$job) { redirect('admin/jobs'); return; }

    // Deindex from Google
    if (file_exists(BASE_PATH . '/includes/google_indexing.php')) {
        require_once BASE_PATH . '/includes/google_indexing.php';
        google_deindex_url(url('job/' . $job['slug']));
    }

    q("UPDATE jobs SET deleted_at = NOW() WHERE id = ?", [$id]);
    flash('success', 'Job trash mein chali gayi. <a href="' . url('admin/jobs/trash') . '">Trash dekhein</a>');
    redirect('admin/jobs');
}

function admin_jobs_trash(): void
{
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 20;
    $offset  = ($page - 1) * $perPage;

    $total = (int)(fetch_one(
        "SELECT COUNT(*) AS n FROM jobs WHERE deleted_at IS NOT NULL"
    )['n'] ?? 0);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $jobs = fetch_all(
        "SELECT j.id, j.title, j.slug, j.status, j.deleted_at,
                c.name AS company_name, cat.name AS category_name
         FROM jobs j
         JOIN companies c    ON c.id = j.company_id
         JOIN categories cat ON cat.id = j.category_id
         WHERE j.deleted_at IS NOT NULL
         ORDER BY j.deleted_at DESC
         LIMIT $perPage OFFSET $offset"
    );

    admin_view('jobs/trash', compact('jobs', 'page', 'totalPages', 'total'),
        ['title' => 'Trash', 'active' => 'jobs']);
}

function admin_job_restore(int $id): void
{
    q("UPDATE jobs SET deleted_at = NULL WHERE id = ?", [$id]);
    flash('success', 'Job restore ho gayi.');
    redirect('admin/jobs/trash');
}

function admin_job_force_delete(int $id): void
{
    $job = fetch_one("SELECT slug FROM jobs WHERE id = ? AND deleted_at IS NOT NULL", [$id]);
    if (!$job) { redirect('admin/jobs/trash'); return; }
    q("DELETE FROM jobs WHERE id = ?", [$id]);
    flash('success', 'Job permanently delete ho gayi.');
    redirect('admin/jobs/trash');
}

/** Friendly flash message describing the publish outcome. */
function publish_message(string $state, ?string $publishedAt): string
{
    return match ($state) {
        'draft'     => 'Job saved as draft.',
        'scheduled' => 'Job scheduled to publish on ' . date('M j, Y g:i A', strtotime((string) $publishedAt)) . ' (UTC).',
        default     => 'Job published.',
    };
}

function admin_job_toggle(int $id): void
{
    // Flip status. When activating a job that was never published,
    // stamp published_at = now so it actually becomes visible.
    q("UPDATE jobs
          SET status = 1 - status,
              published_at = CASE
                  WHEN status = 0 AND published_at IS NULL THEN NOW()
                  ELSE published_at
              END
        WHERE id = ?", [$id]);

    // Notify Google based on new status
    $job = fetch_one("SELECT slug, status FROM jobs WHERE id = ?", [$id]);
    if ($job && file_exists(BASE_PATH . '/includes/google_indexing.php')) {
        require_once BASE_PATH . '/includes/google_indexing.php';
        $jobUrl = url('job/' . $job['slug']);
        if ((int)$job['status'] === 1) {
            google_index_url($jobUrl);
        } else {
            google_deindex_url($jobUrl);
        }
    }

    flash('success', 'Job status updated.');
    redirect('admin/jobs');
}

// =====================================================================
//  JOBS IMPORT / EXPORT
// =====================================================================

/**
 * Download all jobs as CSV or JSON (?format=csv|json). Category & company are
 * emitted by name so the file imports cleanly on any install.
 */
function admin_jobs_export(): void
{
    $format = strtolower(get_param('format')) === 'json' ? 'json' : 'csv';

    $jobs = fetch_all(
        "SELECT j.*, c.name AS company_name, cat.name AS category_name
         FROM jobs j
         JOIN companies c ON c.id = j.company_id
         JOIN categories cat ON cat.id = j.category_id
         ORDER BY j.id"
    );
    $rows = array_map('job_to_export_row', $jobs);
    $stamp = gmdate('Ymd');

    // Discard any buffered output so the download is clean.
    while (ob_get_level() > 0) ob_end_clean();

    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="jobs-' . $stamp . '.json"');
        echo json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="jobs-' . $stamp . '.csv"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel reads unicode correctly
    $out = fopen('php://output', 'w');
    fputcsv($out, job_io_columns(), ',', '"', '\\');
    foreach ($rows as $row) {
        fputcsv($out, $row, ',', '"', '\\');
    }
    fclose($out);
    exit;
}

/**
 * Import jobs from an uploaded CSV/JSON file. Auto-creates missing
 * categories/companies and upserts each job by slug.
 */
function admin_jobs_import(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $file = $_FILES['file'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            flash('error', 'Please choose a valid .csv or .json file to upload.');
            redirect('admin/jobs/import');
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'json'], true)) {
            flash('error', 'Unsupported file type. Upload a .csv or .json file.');
            redirect('admin/jobs/import');
        }

        try {
            $rows = parse_import_file($file['tmp_name'], $ext);
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect('admin/jobs/import');
        }

        if (!$rows) {
            flash('error', 'No rows found in the uploaded file.');
            redirect('admin/jobs/import');
        }

        $stats = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        $lineNo = 1; // header is line 1; first data row is line 2
        foreach ($rows as $row) {
            $lineNo++;
            import_job_row($row, $stats, $lineNo);
        }

        flash('success', sprintf(
            'Import complete — %d added, %d updated, %d skipped.',
            $stats['inserted'], $stats['updated'], $stats['skipped']
        ));
        foreach (array_slice($stats['errors'], 0, 20) as $err) {
            flash('error', $err);
        }
        if (count($stats['errors']) > 20) {
            flash('error', '…and ' . (count($stats['errors']) - 20) . ' more skipped rows.');
        }
        redirect('admin/jobs/import');
    }

    admin_view('jobs/import', [], ['title' => 'Import Jobs', 'active' => 'jobs']);
}

// =====================================================================
//  CATEGORIES CRUD
// =====================================================================

function admin_categories(array $seg): void
{
    $sub = $seg[1] ?? '';

    if ($sub === 'delete') {
        q("DELETE FROM categories WHERE id = ?", [(int) ($seg[2] ?? 0)]);
        flash('success', 'Category deleted.');
        redirect('admin/categories');
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $id   = (int) post_param('id');
        $name = post_param('name');
        $icon = post_param('icon') ?: 'bi-briefcase';
        if ($name === '') {
            flash('error', 'Category name is required.');
            redirect('admin/categories');
        }
        if ($id) {
            $slug = unique_slug(slugify($name), 'categories', $id);
            q("UPDATE categories SET name=?, slug=?, icon=? WHERE id=?", [$name, $slug, $icon, $id]);
            flash('success', 'Category updated.');
        } else {
            $slug = unique_slug(slugify($name), 'categories');
            q("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)", [$name, $slug, $icon]);
            flash('success', 'Category added.');
        }
        redirect('admin/categories');
    }

    $categories = fetch_all(
        "SELECT cat.*, COUNT(j.id) AS job_count
         FROM categories cat LEFT JOIN jobs j ON j.category_id = cat.id
         GROUP BY cat.id ORDER BY cat.name"
    );
    admin_view('categories/index', compact('categories'),
        ['title' => 'Categories', 'active' => 'categories']);
}

// =====================================================================
//  COMPANIES CRUD
// =====================================================================

function admin_companies(array $seg): void
{
    $sub = $seg[1] ?? '';

    if ($sub === 'create' || $sub === 'edit') {
        admin_company_form($sub === 'edit' ? (int) ($seg[2] ?? 0) : null);
        return;
    }
    if ($sub === 'delete') {
        $c = fetch_one("SELECT logo FROM companies WHERE id = ?", [(int) ($seg[2] ?? 0)]);
        if ($c && !empty($c['logo'])) @unlink(LOGO_PATH . '/' . $c['logo']);
        q("DELETE FROM companies WHERE id = ?", [(int) ($seg[2] ?? 0)]);
        flash('success', 'Company deleted.');
        redirect('admin/companies');
    }

    $companies = fetch_all(
        "SELECT c.*, COUNT(j.id) AS job_count
         FROM companies c LEFT JOIN jobs j ON j.company_id = c.id
         GROUP BY c.id ORDER BY c.name"
    );
    admin_view('companies/index', compact('companies'),
        ['title' => 'Companies', 'active' => 'companies']);
}

function admin_company_form(?int $id): void
{
    $company = null;
    if ($id) {
        $company = fetch_one("SELECT * FROM companies WHERE id = ?", [$id]);
        if (!$company) { admin_404(); return; }
    }

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $name     = post_param('name');
        $website  = post_param('website');
        $location = post_param('location');
        $about    = post_param('about');

        if ($name === '') {
            flash('error', 'Company name is required.');
            redirect($id ? "admin/companies/edit/$id" : 'admin/companies/create');
        }
        if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
            flash('error', 'Website must be a valid URL.');
            set_old($_POST);
            redirect($id ? "admin/companies/edit/$id" : 'admin/companies/create');
        }

        $logo = $company['logo'] ?? null;
        try {
            $uploaded = handle_upload($_FILES['logo'] ?? [], LOGO_PATH, ALLOWED_LOGO_EXT, MAX_LOGO_SIZE, 'logo');
            if ($uploaded) {
                if ($logo) @unlink(LOGO_PATH . '/' . $logo);
                $logo = $uploaded;
            }
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
            redirect($id ? "admin/companies/edit/$id" : 'admin/companies/create');
        }

        if ($id) {
            $slug = unique_slug(slugify($name), 'companies', $id);
            q("UPDATE companies SET name=?, slug=?, website=?, location=?, about=?, logo=? WHERE id=?",
                [$name, $slug, $website ?: null, $location ?: null, $about ?: null, $logo, $id]);
            flash('success', 'Company updated.');
        } else {
            $slug = unique_slug(slugify($name), 'companies');
            q("INSERT INTO companies (name, slug, website, location, about, logo) VALUES (?, ?, ?, ?, ?, ?)",
                [$name, $slug, $website ?: null, $location ?: null, $about ?: null, $logo]);
            flash('success', 'Company added.');
        }
        clear_old();
        redirect('admin/companies');
    }

    admin_view('companies/form', compact('company'),
        ['title' => $id ? 'Edit Company' : 'New Company', 'active' => 'companies']);
}

// =====================================================================
//  APPLICATIONS
// =====================================================================

function admin_applications(array $seg): void
{
    $sub = $seg[1] ?? '';

    if ($sub === 'status' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $id     = (int) post_param('id');
        $status = post_param('status');
        $valid  = ['pending','reviewed','shortlisted','rejected','hired'];
        if (in_array($status, $valid, true)) {
            q("UPDATE applications SET status = ? WHERE id = ?", [$status, $id]);
            flash('success', 'Application status updated.');
        }
        redirect('admin/applications');
    }

    $filter = get_param('status');
    $where  = '';
    $params = [];
    if (in_array($filter, ['pending','reviewed','shortlisted','rejected','hired'], true)) {
        $where = 'WHERE a.status = ?';
        $params[] = $filter;
    }

    $apps = fetch_all(
        "SELECT a.*, j.title, j.slug, c.name AS company_name,
                u.name AS user_name, u.email AS user_email, u.phone AS user_phone
         FROM applications a
         JOIN jobs j ON j.id = a.job_id
         JOIN companies c ON c.id = j.company_id
         JOIN users u ON u.id = a.user_id
         $where
         ORDER BY a.created_at DESC",
        $params
    );
    admin_view('applications/index', compact('apps', 'filter'),
        ['title' => 'Applications', 'active' => 'applications']);
}

// =====================================================================
//  USERS
// =====================================================================

function admin_users(array $seg): void
{
    $sub = $seg[1] ?? '';

    if ($sub === 'toggle') {
        q("UPDATE users SET status = 1 - status WHERE id = ?", [(int) ($seg[2] ?? 0)]);
        flash('success', 'User status updated.');
        redirect('admin/users');
    }

    $users = fetch_all(
        "SELECT u.*, (SELECT COUNT(*) FROM applications a WHERE a.user_id = u.id) AS app_count
         FROM users u ORDER BY u.created_at DESC"
    );
    admin_view('users/index', compact('users'), ['title' => 'Candidates', 'active' => 'users']);
}

// =====================================================================
//  SITE SETTINGS
// =====================================================================

function admin_settings(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $siteName    = post_param('site_name');
        $tagline     = post_param('site_tagline');
        $metaTitle   = post_param('meta_title');
        $metaDesc    = post_param('meta_description');

        if ($siteName === '') {
            flash('error', 'Site name is required.');
            redirect('admin/settings');
        }

        // Logo upload
        try {
            $logo = handle_upload($_FILES['site_logo'] ?? [], BRANDING_PATH, ALLOWED_LOGO_EXT, MAX_LOGO_SIZE, 'logo');
            if ($logo) {
                $old = setting('site_logo');
                if ($old !== '') @unlink(BRANDING_PATH . '/' . $old);
                setting_set('site_logo', $logo);
            }
        } catch (RuntimeException $e) {
            flash('error', 'Logo: ' . $e->getMessage());
            redirect('admin/settings');
        }

        // Favicon upload
        try {
            $fav = handle_upload($_FILES['favicon'] ?? [], BRANDING_PATH, ALLOWED_FAVICON_EXT, MAX_FAVICON_SIZE, 'favicon');
            if ($fav) {
                $old = setting('favicon');
                if ($old !== '') @unlink(BRANDING_PATH . '/' . $old);
                setting_set('favicon', $fav);
            }
        } catch (RuntimeException $e) {
            flash('error', 'Favicon: ' . $e->getMessage());
            redirect('admin/settings');
        }

        // Remove logo / favicon if requested
        if (isset($_POST['remove_logo']) && setting('site_logo') !== '') {
            @unlink(BRANDING_PATH . '/' . setting('site_logo'));
            setting_set('site_logo', '');
        }
        if (isset($_POST['remove_favicon']) && setting('favicon') !== '') {
            @unlink(BRANDING_PATH . '/' . setting('favicon'));
            setting_set('favicon', '');
        }

        setting_set('site_name', $siteName);
        setting_set('site_tagline', $tagline);
        setting_set('meta_title', $metaTitle ?: $siteName);
        setting_set('meta_description', $metaDesc);

        flash('success', 'Settings saved.');
        redirect('admin/settings');
    }

    admin_view('settings/index', [], ['title' => 'Site Settings', 'active' => 'settings']);
}

// =====================================================================
//  RENDERING
// =====================================================================

// =====================================================================
//  BULK GOOGLE INDEXING
// =====================================================================

function admin_bulk_index(): void
{
    require_once BASE_PATH . '/includes/google_indexing.php';

    $allJobs = fetch_all(
        "SELECT j.id, j.slug, j.title, j.status, c.name AS company_name
         FROM jobs j
         JOIN companies c ON c.id = j.company_id
         ORDER BY j.created_at DESC"
    );

    $results = [];
    $success = 0;
    $failed  = 0;

    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $selectedIds = array_map('intval', $_POST['job_ids'] ?? []);

        // Filter only selected jobs
        $jobs = array_filter($allJobs, fn($j) => in_array((int)$j['id'], $selectedIds));

        foreach ($jobs as $job) {
            $jobUrl = url('job/' . $job['slug']);
            try {
                $token = google_indexing_get_token();
                if (!$token) {
                    $results[] = ['error', $job['title'], $jobUrl, 'Token nahi mila'];
                    $failed++;
                    continue;
                }

                $payload = json_encode(['url' => $jobUrl, 'type' => 'URL_UPDATED']);
                $ch = curl_init('https://indexing.googleapis.com/v3/urlNotifications:publish');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $payload,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $token,
                    ],
                    CURLOPT_TIMEOUT => 10,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200) {
                    $results[] = ['ok', $job['title'], $jobUrl, 'Submitted'];
                    $success++;
                } else {
                    $resp = json_decode($response, true);
                    $msg  = $resp['error']['message'] ?? "HTTP $httpCode";
                    $results[] = ['error', $job['title'], $jobUrl, $msg];
                    $failed++;
                }

                usleep(200000); // 0.2 sec delay
            } catch (Throwable $e) {
                $results[] = ['error', $job['title'], $jobUrl, $e->getMessage()];
                $failed++;
            }
        }
    }

    $jobs = $allJobs; // pass all jobs to view for selection screen

    admin_view('bulk_index', compact('jobs', 'results', 'success', 'failed'), [
        'title'  => 'Bulk Google Indexing',
        'active' => 'jobs',
    ]);
}

// =====================================================================
//  EXPIRED JOBS
// =====================================================================

function admin_expired_jobs(): void
{
    $perPage = 20;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $offset  = ($page - 1) * $perPage;

    $total = (int)(fetch_one(
        "SELECT COUNT(*) AS n FROM jobs j
         JOIN companies c ON c.id = j.company_id
         WHERE j.status = 1 AND j.deadline IS NOT NULL AND j.deadline < CURDATE()"
    )['n'] ?? 0);

    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);

    $jobs = fetch_all(
        "SELECT j.id, j.title, j.slug, j.deadline, j.created_at,
                c.name AS company_name,
                (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS app_count
         FROM jobs j
         JOIN companies c ON c.id = j.company_id
         WHERE j.status = 1 AND j.deadline IS NOT NULL AND j.deadline < CURDATE()
         ORDER BY j.deadline DESC
         LIMIT $perPage OFFSET $offset"
    );

    admin_view('jobs/expired', compact('jobs', 'page', 'totalPages', 'total'), [
        'title'  => 'Expired Jobs',
        'active' => 'jobs',
    ]);
}

function admin_job_renew(int $id): void
{
    $job = fetch_one("SELECT slug, deadline FROM jobs WHERE id = ?", [$id]);
    if (!$job) { admin_404(); return; }

    // Extend deadline by 30 days from today
    $newDeadline = date('Y-m-d', strtotime('+30 days'));
    q("UPDATE jobs SET deadline = ? WHERE id = ?", [$newDeadline, $id]);

    // Notify Google — job is live again
    if (file_exists(BASE_PATH . '/includes/google_indexing.php')) {
        require_once BASE_PATH . '/includes/google_indexing.php';
        google_index_url(url('job/' . $job['slug']));
    }

    flash('success', 'Job renewed — new deadline: ' . date('d M Y', strtotime($newDeadline)));
    redirect('admin/jobs/expired');
}

// =====================================================================
//  REDIRECT MANAGER
// =====================================================================

function admin_redirects(array $seg): void
{
    $action = $seg[1] ?? '';

    // DELETE
    if ($action === 'delete' && isset($seg[2])) {
        q("DELETE FROM redirects WHERE id = ?", [(int)$seg[2]]);
        flash('success', 'Redirect deleted.');
        redirect('admin/redirects');
    }

    // ADD
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        $from = '/' . ltrim(trim($_POST['from_path'] ?? ''), '/');
        $to   = trim($_POST['to_url'] ?? '');
        $code = in_array((int)($_POST['code'] ?? 301), [301, 302]) ? (int)$_POST['code'] : 301;

        if ($from && $to) {
            q("INSERT INTO redirects (from_path, to_url, code, created_at)
               VALUES (?, ?, ?, NOW())
               ON DUPLICATE KEY UPDATE to_url = VALUES(to_url), code = VALUES(code)",
               [$from, $to, $code]);
            flash('success', 'Redirect saved.');
        } else {
            flash('error', 'Both From and To fields are required.');
        }
        redirect('admin/redirects');
    }

    $redirects = fetch_all("SELECT * FROM redirects ORDER BY created_at DESC");
    admin_view('redirects/index', compact('redirects'), [
        'title'  => 'Redirect Manager',
        'active' => 'redirects',
    ]);
}

// =====================================================================

function admin_view(string $view, array $data = [], array $meta = []): void
{
    $layout    = $meta['layout'] ?? 'admin';
    $pageTitle = ($meta['title'] ?? 'Admin') . ' — ' . site_name() . ' Admin';
    $heading   = $meta['title'] ?? '';
    $active    = $meta['active'] ?? '';

    extract($data, EXTR_SKIP);

    ob_start();
    require BASE_PATH . '/views/admin/' . $view . '.php';
    $content = ob_get_clean();

    if ($layout === 'plain') {
        require BASE_PATH . '/views/admin/partials/auth.php';
    } else {
        require BASE_PATH . '/views/admin/partials/header.php';
        require BASE_PATH . '/views/admin/partials/sidebar.php';
        echo $content;
        require BASE_PATH . '/views/admin/partials/footer.php';
    }
}

function admin_404(): void
{
    http_response_code(404);
    admin_view('dashboard', [
        'stats' => ['jobs'=>0,'active_jobs'=>0,'applications'=>0,'users'=>0,'companies'=>0,'categories'=>0],
        'recentApps' => [], 'recentJobs' => [],
    ], ['title' => 'Not Found', 'active' => '']);
}
