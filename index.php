<?php
/**
 * Front controller / router.
 * All non-file requests are routed here by .htaccess.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

// ---------------------------------------------------------------------
// Resolve the route path relative to BASE_URL
// ---------------------------------------------------------------------
$uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$uri  = rawurldecode($uri);
$base = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
if ($base && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}
$route = trim($uri, '/');
$segments = $route === '' ? [] : explode('/', $route);

// CSRF guard runs for every POST.
csrf_verify();

// ---------------------------------------------------------------------
// Dispatch
// ---------------------------------------------------------------------
$seg0 = $segments[0] ?? '';

try {
    switch ($seg0) {
        case '':            home();                                   break;
        case 'jobs':        jobs_list();                              break;
        case 'job':         job_single($segments[1] ?? '', (int)($segments[2] ?? 0)); break;
        case 'category':    category_page($segments[1] ?? '');        break;
        case 'companies':   companies_list();                         break;
        case 'company':     company_page($segments[1] ?? '');         break;

        case 'register':    auth_register();                          break;
        case 'login':       auth_login();                             break;
        case 'logout':      auth_logout();                            break;

        case 'apply':       job_apply((int) ($segments[1] ?? 0));     break;
        case 'go':          job_external((int) ($segments[1] ?? 0));  break;

        case 'dashboard':   candidate_dashboard();                    break;
        case 'profile':     candidate_profile();                      break;
        case 'resume':      candidate_resume();                       break;
        case 'applications':candidate_applications();                 break;

        case 'admin':       admin_router($segments);                  break;

        case 'sitemap.xml': sitemap();                                break;
        case 'sitemap':     sitemap();                                break;
        case 'robots.txt':  robots();                                 break;
        case 'robots':      robots();                                 break;

        case 'contact':         page_contact();                       break;
        case 'about':
        case 'about-us':        page_about();                         break;
        case 'privacy-policy':
        case 'privacy':         page_privacy();                       break;
        case 'disclaimer':      page_disclaimer();                    break;
        case 'terms':
        case 'terms-and-conditions': page_terms();                    break;

        default:
            // Check redirects table before showing 404
            $currentPath = '/' . $route;
            $redirect = fetch_one(
                "SELECT to_url, code FROM redirects WHERE from_path = ? LIMIT 1",
                [$currentPath]
            );
            if ($redirect) {
                http_response_code((int) $redirect['code']);
                header('Location: ' . $redirect['to_url']);
                exit;
            }
            not_found();
    }
} catch (Throwable $ex) {
    if (APP_ENV === 'development') {
        http_response_code(500);
        echo '<pre style="padding:2rem;font:14px monospace;color:#b00">'
           . e($ex->getMessage()) . "\n\n" . e($ex->getTraceAsString()) . '</pre>';
    } else {
        server_error();
    }
}

// =====================================================================
//  PUBLIC CONTROLLERS
// =====================================================================

function home(): void
{
    $selectCols =
        "j.*, c.name AS company_name, c.logo AS company_logo, c.slug AS company_slug,
         cat.name AS category_name, cat.slug AS category_slug";
    $joins =
        "FROM jobs j
         JOIN companies c    ON c.id = j.company_id
         JOIN categories cat ON cat.id = j.category_id";

    $live = live_sql('j');

    $featured = fetch_all(
        "SELECT $selectCols $joins
         WHERE $live AND j.is_featured = 1
         ORDER BY j.published_at DESC LIMIT 6"
    );
    $latest = fetch_all(
        "SELECT $selectCols $joins
         WHERE $live
         ORDER BY j.published_at DESC LIMIT 6"
    );
    $remote = fetch_all(
        "SELECT $selectCols $joins
         WHERE $live AND j.work_type = 'remote'
         ORDER BY j.published_at DESC LIMIT 6"
    );
    $categories = fetch_all(
        "SELECT cat.*, COUNT(j.id) AS job_count
         FROM categories cat
         LEFT JOIN jobs j ON j.category_id = cat.id AND $live
         GROUP BY cat.id ORDER BY cat.name"
    );
    $stats = [
        'jobs'      => (int) fetch_col("SELECT COUNT(*) FROM jobs j WHERE $live"),
        'companies' => (int) fetch_col("SELECT COUNT(*) FROM companies"),
        'users'     => (int) fetch_col("SELECT COUNT(*) FROM users"),
    ];
    render('home', compact('featured', 'latest', 'remote', 'categories', 'stats'), [
        'title' => meta_title(),
    ]);
}

function jobs_list(): void
{
    $keyword  = get_param('q');
    $location = get_param('location');
    $type     = get_param('type');
    $workType = get_param('work_type');
    $catId    = (int) get_param('category', '0');
    $page     = max(1, (int) get_param('page', '1'));

    if (!in_array($workType, WORK_TYPES, true)) $workType = '';

    $where  = [live_sql('j')];
    $params = [];

    if ($keyword !== '') {
        $where[] = '(j.title LIKE ? OR j.description LIKE ? OR c.name LIKE ?)';
        $like = '%' . $keyword . '%';
        array_push($params, $like, $like, $like);
    }
    if ($location !== '') {
        $where[] = 'j.location LIKE ?';
        $params[] = '%' . $location . '%';
    }
    if ($type !== '') {
        $where[] = 'j.job_type = ?';
        $params[] = $type;
    }
    if ($workType !== '') {
        $where[] = 'j.work_type = ?';
        $params[] = $workType;
    }
    if ($catId > 0) {
        $where[] = 'j.category_id = ?';
        $params[] = $catId;
    }
    $whereSql = implode(' AND ', $where);

    $total = (int) fetch_col(
        "SELECT COUNT(*) FROM jobs j JOIN companies c ON c.id = j.company_id WHERE $whereSql",
        $params
    );
    $pg = paginate($total, PER_PAGE, $page);

    $jobs = fetch_all(
        "SELECT j.*, c.name AS company_name, c.logo AS company_logo, c.slug AS company_slug,
                cat.name AS category_name, cat.slug AS category_slug
         FROM jobs j
         JOIN companies c    ON c.id = j.company_id
         JOIN categories cat ON cat.id = j.category_id
         WHERE $whereSql
         ORDER BY j.is_featured DESC, j.published_at DESC
         LIMIT {$pg['perPage']} OFFSET {$pg['offset']}",
        $params
    );

    $categories = fetch_all("SELECT * FROM categories ORDER BY name");

    render('jobs', compact('jobs', 'categories', 'pg', 'keyword', 'location', 'type', 'workType', 'catId'), [
        'title' => 'Browse Jobs — ' . site_name(),
    ]);
}

function job_single(string $slug, int $id = 0): void
{
    $slug = slug_clean($slug);

    // Support both URL formats:
    // Old: /job/slug
    // New: /job/slug/id
    if ($id > 0) {
        // New format — find by id, verify slug matches
        $job = fetch_one(
            "SELECT j.*, c.name AS company_name, c.logo AS company_logo, c.slug AS company_slug,
                    c.website AS company_website, c.location AS company_location, c.about AS company_about,
                    cat.name AS category_name, cat.slug AS category_slug
             FROM jobs j
             JOIN companies c    ON c.id = j.company_id
             JOIN categories cat ON cat.id = j.category_id
             WHERE j.id = ? AND " . live_sql('j'),
            [$id]
        );
        // If slug doesn't match, redirect to correct URL
        if ($job && $job['slug'] !== $slug) {
            redirect('job/' . $job['slug'] . '/' . $id);
            return;
        }
    } else {
        // Old format — find by slug only
        $job = fetch_one(
            "SELECT j.*, c.name AS company_name, c.logo AS company_logo, c.slug AS company_slug,
                    c.website AS company_website, c.location AS company_location, c.about AS company_about,
                    cat.name AS category_name, cat.slug AS category_slug
             FROM jobs j
             JOIN companies c    ON c.id = j.company_id
             JOIN categories cat ON cat.id = j.category_id
             WHERE j.slug = ? AND " . live_sql('j'),
            [$slug]
        );
    }

    if (!$job) {
        // Check redirects table before showing 404
        $currentPath = $id > 0 ? '/job/' . $slug . '/' . $id : '/job/' . $slug;
        $redirect = fetch_one(
            "SELECT to_url, code FROM redirects WHERE from_path = ? LIMIT 1",
            [$currentPath]
        );
        if (!$redirect) {
            // Also try without id
            $redirect = fetch_one(
                "SELECT to_url, code FROM redirects WHERE from_path = ? LIMIT 1",
                ['/job/' . $slug]
            );
        }
        if ($redirect) {
            http_response_code((int) $redirect['code']);
            header('Location: ' . $redirect['to_url']);
            exit;
        }
        not_found(); return;
    }

    q("UPDATE jobs SET views = views + 1 WHERE id = ?", [$job['id']]);

    $related = fetch_all(
        "SELECT j.*, c.name AS company_name, c.logo AS company_logo
         FROM jobs j JOIN companies c ON c.id = j.company_id
         WHERE j.category_id = ? AND j.id <> ? AND " . live_sql('j') . "
         ORDER BY j.published_at DESC LIMIT 4",
        [$job['category_id'], $job['id']]
    );

    $hasApplied = false;
    if (is_logged_in()) {
        $hasApplied = (bool) fetch_one(
            "SELECT id FROM applications WHERE job_id = ? AND user_id = ?",
            [$job['id'], current_user_id()]
        );
    }

    render('job_single', compact('job', 'related', 'hasApplied'), [
        'title' => $job['title'] . ' at ' . $job['company_name'] . ' — ' . site_name(),
        'description' => excerpt($job['description'], 30),
        'headExtra' => job_posting_schema($job),
    ]);
}

/**
 * Build Google JobPosting JSON-LD for a job row (joined with company fields).
 */
function job_posting_schema(array $job): string
{
    $jobUrl = url('job/' . $job['slug'] . '/' . $job['id']);
    $schema = [
        '@context'    => 'https://schema.org/',
        '@type'       => 'JobPosting',
        'title'       => $job['title'],
        'description' => $job['description'],
        'datePosted'  => date('Y-m-d', strtotime($job['published_at'] ?? $job['created_at'])),
        'employmentType' => strtoupper(str_replace('-', '_', $job['job_type'])),
        // Canonical URL of this posting — the page Google Jobs links to.
        'url'         => $jobUrl,
        // Stable per-posting id Google uses to de-duplicate listings.
        'identifier'  => [
            '@type' => 'PropertyValue',
            'name'  => $job['company_name'],
            'value' => (string) $job['id'],
        ],
        // true = candidates apply on THIS site (internal); false = external redirect.
        'directApply' => ($job['apply_type'] ?? 'internal') !== 'external',
        'hiringOrganization' => [
            '@type' => 'Organization',
            'name'  => $job['company_name'],
        ],
    ];

    if (!empty($job['company_website'])) {
        $schema['hiringOrganization']['sameAs'] = $job['company_website'];
    }
    if (!empty($job['company_logo'])) {
        $schema['hiringOrganization']['logo'] = UPLOAD_URL . '/logos/' . $job['company_logo'];
    }
    if (!empty($job['deadline'])) {
        $schema['validThrough'] = date('Y-m-d', strtotime($job['deadline']));
    }

    // Salary
    if (!empty($job['salary_min']) || !empty($job['salary_max'])) {
        $value = [];
        if (!empty($job['salary_min'])) $value['minValue'] = (int) $job['salary_min'];
        if (!empty($job['salary_max'])) $value['maxValue'] = (int) $job['salary_max'];
        $value['@type']    = 'QuantitativeValue';
        $value['unitText'] = SALARY_PERIODS[$job['salary_period'] ?? DEFAULT_SALARY_PERIOD]['schema']
                             ?? SALARY_PERIODS[DEFAULT_SALARY_PERIOD]['schema'];
        $schema['baseSalary'] = [
            '@type'    => 'MonetaryAmount',
            'currency' => strtoupper($job['salary_currency'] ?? DEFAULT_CURRENCY),
            'value'    => $value,
        ];
    }

    // Location / remote handling
    $workType = $job['work_type'] ?? 'on-site';
    if ($workType === 'remote') {
        $schema['jobLocationType'] = 'TELECOMMUTE';
        $countries = remote_countries($job['remote_countries'] ?? null);
        // "Worldwide" (or no selection) => no specific country requirement.
        $countries = array_values(array_filter($countries, fn($c) => strcasecmp($c, 'Worldwide') !== 0));

        // Build Country entries using full country NAMES, not ISO codes.
        // Google resolves applicantLocationRequirements names against its entity
        // graph: ambiguous 2-letter codes like "CA" (=> California, a State) or
        // "IN" (=> Indiana) get recognised as sub-country regions, which clashes
        // with the declared @type "Country" and fails validation. Full names
        // ("India", "Canada", "United States") are unambiguous and match
        // Google's own documented example ("USA").
        $reqs = [];
        foreach ($countries as $c) {
            $name = schema_country_name($c);
            if ($name !== null) {
                $reqs[] = ['@type' => 'Country', 'name' => $name];
            }
        }
        if ($reqs) {
            // Single country => object; multiple => array. Both are valid Google shapes.
            $schema['applicantLocationRequirements'] = count($reqs) === 1 ? $reqs[0] : $reqs;
        }
    } else {
        $address = ['@type' => 'PostalAddress'];
        if (!empty($job['street_address'])) $address['streetAddress']   = $job['street_address'];
        if (!empty($job['city']))           $address['addressLocality'] = $job['city'];
        if (!empty($job['state']))          $address['addressRegion']   = $job['state'];
        if (!empty($job['postal_code']))    $address['postalCode']      = $job['postal_code'];
        if (!empty($job['country'])) {
            $address['addressCountry'] = country_code($job['country']) ?? $job['country'];
        }
        $schema['jobLocation'] = [
            '@type'   => 'Place',
            'address' => $address,
        ];
    }

    // Keep slashes escaped so a literal "</script>" in any field (e.g. the HTML
    // description) cannot break out of the <script type="application/ld+json"> block.
    $json = json_encode($schema, JSON_UNESCAPED_UNICODE);
    return '<script type="application/ld+json">' . $json . '</script>';
}

function category_page(string $slug): void
{
    $slug = slug_clean($slug);
    $cat = fetch_one("SELECT * FROM categories WHERE slug = ?", [$slug]);
    if (!$cat) { not_found(); return; }

    $page = max(1, (int) get_param('page', '1'));
    $total = (int) fetch_col("SELECT COUNT(*) FROM jobs j WHERE j.category_id = ? AND " . live_sql('j'), [$cat['id']]);
    $pg = paginate($total, PER_PAGE, $page);

    $jobs = fetch_all(
        "SELECT j.*, c.name AS company_name, c.logo AS company_logo, c.slug AS company_slug,
                cat.name AS category_name, cat.slug AS category_slug
         FROM jobs j
         JOIN companies c    ON c.id = j.company_id
         JOIN categories cat ON cat.id = j.category_id
         WHERE j.category_id = ? AND " . live_sql('j') . "
         ORDER BY j.is_featured DESC, j.published_at DESC
         LIMIT {$pg['perPage']} OFFSET {$pg['offset']}",
        [$cat['id']]
    );

    render('category', compact('cat', 'jobs', 'pg'), [
        'title' => $cat['name'] . ' Jobs — ' . site_name(),
    ]);
}

function companies_list(): void
{
    $companies = fetch_all(
        "SELECT c.*, COUNT(j.id) AS job_count
         FROM companies c
         LEFT JOIN jobs j ON j.company_id = c.id AND " . live_sql('j') . "
         GROUP BY c.id ORDER BY c.name"
    );
    render('companies', compact('companies'), ['title' => 'Companies — ' . site_name()]);
}

function company_page(string $slug): void
{
    $slug = slug_clean($slug);
    $company = fetch_one("SELECT * FROM companies WHERE slug = ?", [$slug]);
    if (!$company) { not_found(); return; }

    $jobs = fetch_all(
        "SELECT j.*, cat.name AS category_name, cat.slug AS category_slug
         FROM jobs j JOIN categories cat ON cat.id = j.category_id
         WHERE j.company_id = ? AND " . live_sql('j') . "
         ORDER BY j.published_at DESC",
        [$company['id']]
    );
    render('company', compact('company', 'jobs'), [
        'title' => $company['name'] . ' — ' . site_name(),
    ]);
}


// =====================================================================
//  STATIC / INFO PAGES
// =====================================================================

function page_contact(): void
{
    $sent = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = trim($_POST['name']    ?? '');
        $email   = trim($_POST['email']   ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name && $email && $subject && $message) {
            // Store message in DB if table exists, otherwise just mark sent
            try {
                q(
                    "INSERT INTO contact_messages (name, email, subject, message, created_at)
                     VALUES (?, ?, ?, ?, NOW())",
                    [$name, $email, $subject, $message]
                );
            } catch (Throwable $e) {
                // Table may not exist yet — silently ignore, still show success
            }
            $sent = true;
        }
    }

    render('contact', compact('sent'), [
        'title'       => 'Contact Us — ' . site_name(),
        'description' => 'Get in touch with ' . site_name() . '. We are happy to help.',
        'canonical'   => url('contact'),
    ]);
}

function page_about(): void
{
    $stats = [
        'jobs'         => fetch_one("SELECT COUNT(*) AS n FROM jobs WHERE status='active'"   )['n'] ?? 0,
        'companies'    => fetch_one("SELECT COUNT(*) AS n FROM companies"                    )['n'] ?? 0,
        'candidates'   => fetch_one("SELECT COUNT(*) AS n FROM users")['n'] ?? 0,
        'applications' => fetch_one("SELECT COUNT(*) AS n FROM applications"                 )['n'] ?? 0,
    ];

    render('about', compact('stats'), [
        'title'       => 'About Us — ' . site_name(),
        'description' => 'Learn about ' . site_name() . ' — our mission, vision, and team.',
        'canonical'   => url('about'),
    ]);
}

function page_privacy(): void
{
    render('privacy', [], [
        'title'       => 'Privacy Policy — ' . site_name(),
        'description' => 'Read the Privacy Policy for ' . site_name() . '.',
        'canonical'   => url('privacy-policy'),
    ]);
}

function page_disclaimer(): void
{
    render('disclaimer', [], [
        'title'       => 'Disclaimer — ' . site_name(),
        'description' => 'Read the Disclaimer for ' . site_name() . '.',
        'canonical'   => url('disclaimer'),
    ]);
}

function page_terms(): void
{
    render('terms', [], [
        'title'       => 'Terms & Conditions — ' . site_name(),
        'description' => 'Read the Terms and Conditions of ' . site_name() . '.',
        'canonical'   => url('terms'),
    ]);
}

// =====================================================================

/**
 * Dynamic sitemap.xml — always reflects current jobs/categories/companies.
 * Updates automatically whenever rows are added/edited/deleted.
 */
function sitemap(): void
{
    $urls = [];
    $urls[] = ['loc' => url('/'),              'changefreq' => 'daily',   'priority' => '1.0'];
    $urls[] = ['loc' => url('jobs'),           'changefreq' => 'daily',   'priority' => '0.9'];
    $urls[] = ['loc' => url('companies'),      'changefreq' => 'weekly',  'priority' => '0.7'];
    $urls[] = ['loc' => url('about'),          'changefreq' => 'monthly', 'priority' => '0.6'];
    $urls[] = ['loc' => url('contact'),        'changefreq' => 'monthly', 'priority' => '0.5'];
    $urls[] = ['loc' => url('privacy-policy'), 'changefreq' => 'monthly', 'priority' => '0.4'];
    $urls[] = ['loc' => url('terms'),          'changefreq' => 'monthly', 'priority' => '0.4'];
    $urls[] = ['loc' => url('disclaimer'),     'changefreq' => 'monthly', 'priority' => '0.4'];

    foreach (fetch_all("SELECT id, slug, updated_at FROM jobs j WHERE " . live_sql('j') . " ORDER BY updated_at DESC") as $j) {
        $urls[] = [
            'loc'        => url('job/' . $j['slug'] . '/' . $j['id']),
            'lastmod'    => date('Y-m-d', strtotime($j['updated_at'])),
            'changefreq' => 'daily',
            'priority'   => '1.0',
        ];
    }
    foreach (fetch_all("SELECT slug FROM categories ORDER BY name") as $c) {
        $urls[] = ['loc' => url('category/' . $c['slug']), 'changefreq' => 'weekly', 'priority' => '0.6'];
    }
    foreach (fetch_all("SELECT slug FROM companies ORDER BY name") as $c) {
        $urls[] = ['loc' => url('company/' . $c['slug']), 'changefreq' => 'weekly', 'priority' => '0.6'];
    }

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        echo "  <url>\n";
        echo "    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
        if (!empty($u['lastmod']))    echo "    <lastmod>{$u['lastmod']}</lastmod>\n";
        if (!empty($u['changefreq'])) echo "    <changefreq>{$u['changefreq']}</changefreq>\n";
        if (!empty($u['priority']))   echo "    <priority>{$u['priority']}</priority>\n";
        echo "  </url>\n";
    }
    echo '</urlset>';
}

/**
 * Dynamic robots.txt — points crawlers at the sitemap and blocks
 * private/admin areas. Sitemap URL is absolute and matches the install.
 */
function robots(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /dashboard',
        'Disallow: /profile',
        'Disallow: /applications',
        'Disallow: /apply',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /logout',
        '',
        'Sitemap: ' . url('sitemap.xml'),
    ];
    echo implode("\n", $lines) . "\n";
}

// =====================================================================
//  AUTH CONTROLLERS
// =====================================================================

function auth_register(): void
{
    if (is_logged_in()) redirect('dashboard');

    if (is_post()) {
        $name  = post_param('name');
        $email = strtolower(post_param('email'));
        $pass  = (string) ($_POST['password'] ?? '');
        $pass2 = (string) ($_POST['password_confirm'] ?? '');
        $errors = [];

        if ($name === '' || mb_strlen($name) < 2)         $errors[] = 'Please enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'Please enter a valid email address.';
        if (strlen($pass) < 6)                            $errors[] = 'Password must be at least 6 characters.';
        if ($pass !== $pass2)                             $errors[] = 'Passwords do not match.';

        if (!$errors && fetch_one("SELECT id FROM users WHERE email = ?", [$email])) {
            $errors[] = 'An account with that email already exists.';
        }

        if ($errors) {
            foreach ($errors as $err) flash('error', $err);
            set_old($_POST);
            redirect('register');
        }

        q("INSERT INTO users (name, email, password) VALUES (?, ?, ?)",
            [$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);

        clear_old();
        login_user(['id' => last_id(), 'name' => $name]);
        flash('success', 'Welcome aboard, ' . $name . '!');
        redirect('dashboard');
    }

    render('auth/register', [], ['title' => 'Create Account — ' . site_name(), 'layout' => 'plain']);
}

function auth_login(): void
{
    if (is_logged_in()) redirect('dashboard');

    if (is_post()) {
        $email = strtolower(post_param('email'));
        $pass  = (string) ($_POST['password'] ?? '');

        $user = fetch_one("SELECT * FROM users WHERE email = ?", [$email]);
        if (!$user || !password_verify($pass, $user['password'])) {
            flash('error', 'Invalid email or password.');
            set_old($_POST);
            redirect('login');
        }
        if ((int) $user['status'] !== 1) {
            flash('error', 'Your account has been disabled.');
            redirect('login');
        }

        clear_old();
        login_user($user);
        flash('success', 'Welcome back, ' . $user['name'] . '!');
        $intended = $_SESSION['_intended'] ?? null;
        unset($_SESSION['_intended']);
        redirect($intended ?? url('dashboard'));
    }

    render('auth/login', [], ['title' => 'Log In — ' . site_name(), 'layout' => 'plain']);
}

function auth_logout(): void
{
    logout_user();
    flash('info', 'You have been logged out.');
    redirect('/');
}

// =====================================================================
//  APPLY CONTROLLERS
// =====================================================================

function job_apply(int $jobId): void
{
    require_user();
    $job = fetch_one("SELECT * FROM jobs j WHERE j.id = ? AND " . live_sql('j'), [$jobId]);
    if (!$job) { not_found(); return; }

    if ($job['apply_type'] === 'external') {
        redirect('go/' . $jobId);
    }

    if (!is_post()) {
        redirect('job/' . $job['slug'] . '/' . $job['id']);
    }

    $user = current_user();
    if (fetch_one("SELECT id FROM applications WHERE job_id = ? AND user_id = ?", [$jobId, $user['id']])) {
        flash('warning', 'You have already applied to this job.');
        redirect('job/' . $job['slug'] . '/' . $job['id']);
    }

    $resume = $user['resume_file'];
    try {
        $uploaded = handle_upload($_FILES['resume'] ?? [], RESUME_PATH, ALLOWED_RESUME_EXT, MAX_RESUME_SIZE, 'resume');
        if ($uploaded) {
            $resume = $uploaded;
            q("UPDATE users SET resume_file = ? WHERE id = ?", [$uploaded, $user['id']]);
        }
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
        redirect('job/' . $job['slug'] . '/' . $job['id']);
    }

    if (!$resume) {
        flash('error', 'Please upload your resume before applying.');
        redirect('job/' . $job['slug'] . '/' . $job['id']);
    }

    q("INSERT INTO applications (job_id, user_id, cover_letter, resume_file) VALUES (?, ?, ?, ?)",
        [$jobId, $user['id'], post_param('cover_letter') ?: null, $resume]);

    flash('success', 'Your application has been submitted!');
    redirect('job/' . $job['slug'] . '/' . $job['id']);
}

function job_external(int $jobId): void
{
    $job = fetch_one("SELECT j.external_url, j.apply_type FROM jobs j WHERE j.id = ? AND " . live_sql('j'), [$jobId]);
    if (!$job || $job['apply_type'] !== 'external' || empty($job['external_url'])) {
        not_found();
        return;
    }
    redirect($job['external_url']);
}

// =====================================================================
//  CANDIDATE CONTROLLERS
// =====================================================================

function candidate_dashboard(): void
{
    require_user();
    $uid = current_user_id();
    $stats = [
        'total'       => (int) fetch_col("SELECT COUNT(*) FROM applications WHERE user_id = ?", [$uid]),
        'pending'     => (int) fetch_col("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status='pending'", [$uid]),
        'shortlisted' => (int) fetch_col("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status='shortlisted'", [$uid]),
        'hired'       => (int) fetch_col("SELECT COUNT(*) FROM applications WHERE user_id = ? AND status='hired'", [$uid]),
    ];
    $recent = fetch_all(
        "SELECT a.*, j.title, j.slug, c.name AS company_name
         FROM applications a
         JOIN jobs j ON j.id = a.job_id
         JOIN companies c ON c.id = j.company_id
         WHERE a.user_id = ? ORDER BY a.created_at DESC LIMIT 5",
        [$uid]
    );
    render('candidate/dashboard', compact('stats', 'recent'), [
        'title' => 'Dashboard — ' . site_name(), 'active' => 'dashboard',
    ]);
}

function candidate_profile(): void
{
    require_user();
    $user = current_user();

    if (is_post()) {
        $name     = post_param('name');
        $phone    = post_param('phone');
        $headline = post_param('headline');
        $location = post_param('location');
        $bio      = post_param('bio');

        if ($name === '') {
            flash('error', 'Name is required.');
            redirect('profile');
        }
        q("UPDATE users SET name=?, phone=?, headline=?, location=?, bio=? WHERE id=?",
            [$name, $phone ?: null, $headline ?: null, $location ?: null, $bio ?: null, $user['id']]);
        $_SESSION['user_name'] = $name;
        flash('success', 'Profile updated.');
        redirect('profile');
    }

    render('candidate/profile', compact('user'), [
        'title' => 'My Profile — ' . site_name(), 'active' => 'profile',
    ]);
}

function candidate_resume(): void
{
    require_user();
    if (!is_post()) redirect('profile');
    $user = current_user();
    try {
        $file = handle_upload($_FILES['resume'] ?? [], RESUME_PATH, ALLOWED_RESUME_EXT, MAX_RESUME_SIZE, 'resume');
        if (!$file) {
            flash('error', 'Please choose a file to upload.');
            redirect('profile');
        }
        // remove old
        if (!empty($user['resume_file'])) {
            @unlink(RESUME_PATH . '/' . $user['resume_file']);
        }
        q("UPDATE users SET resume_file = ? WHERE id = ?", [$file, $user['id']]);
        flash('success', 'Resume uploaded successfully.');
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
    }
    redirect('profile');
}

function candidate_applications(): void
{
    require_user();
    $apps = fetch_all(
        "SELECT a.*, j.title, j.slug, j.location, c.name AS company_name, c.logo AS company_logo
         FROM applications a
         JOIN jobs j ON j.id = a.job_id
         JOIN companies c ON c.id = j.company_id
         WHERE a.user_id = ? ORDER BY a.created_at DESC",
        [current_user_id()]
    );
    render('candidate/applications', compact('apps'), [
        'title' => 'My Applications — ' . site_name(), 'active' => 'applications',
    ]);
}

// =====================================================================
//  ADMIN ROUTER  (delegates to admin.php)
// =====================================================================

function admin_router(array $segments): void
{
    require_once BASE_PATH . '/admin.php';
    admin_dispatch(array_slice($segments, 1));
}

// =====================================================================
//  HELPERS / RENDERING
// =====================================================================

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function slug_clean(string $slug): string
{
    return preg_replace('/[^a-z0-9\-]/i', '', $slug) ?? '';
}

/**
 * Clean canonical URL for the current request: the path without any
 * query string. Filter/search/pagination variants of /jobs therefore
 * all canonicalise to the same base page, avoiding duplicate content.
 */
function canonical_url(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $base = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    if ($base && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }
    return url(trim($path, '/'));
}

/**
 * Render a public-facing page wrapped in the site layout.
 * $meta keys: title, description, layout ('site'|'plain'), active, canonical
 */
function render(string $view, array $data = [], array $meta = []): void
{
    $layout = $meta['layout'] ?? 'site';
    $pageTitle   = $meta['title'] ?? meta_title();
    $pageDesc    = $meta['description'] ?? meta_description();
    $activeNav   = $meta['active'] ?? '';
    $headExtra   = $meta['headExtra'] ?? '';
    $canonical   = $meta['canonical'] ?? canonical_url();

    extract($data, EXTR_SKIP);

    ob_start();
    require BASE_PATH . '/views/' . $view . '.php';
    $content = ob_get_clean();

    if ($layout === 'plain') {
        require BASE_PATH . '/views/partials/plain.php';
    } else {
        require BASE_PATH . '/views/partials/header.php';
        echo $content;
        require BASE_PATH . '/views/partials/footer.php';
    }
}

function not_found(): void
{
    http_response_code(404);
    render('errors', ['code' => 404, 'message' => 'Page not found'], ['title' => 'Not Found']);
}

function server_error(): void
{
    http_response_code(500);
    render('errors', ['code' => 500, 'message' => 'Something went wrong'], ['title' => 'Error']);
}

