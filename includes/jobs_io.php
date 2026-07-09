<?php
/**
 * Jobs Import / Export — shared logic.
 *
 * Used by the admin Jobs controllers (admin.php) to:
 *   - export all jobs to CSV / JSON (category & company by NAME, not id, so the
 *     file is portable across installs),
 *   - import a CSV / JSON file, auto-filling every job field, auto-creating any
 *     missing category/company, and upserting by slug.
 *
 * Reuses helpers from helpers.php / index.php: slugify(), unique_slug(),
 * slug_clean(), clean_html(), q(), fetch_one().
 */

declare(strict_types=1);

/**
 * Ordered list of import/export field names. `category` and `company` are the
 * human-readable names (resolved to ids on import). `id`/`views`/timestamps are
 * intentionally omitted — they are install-specific.
 */
function job_io_columns(): array
{
    return [
        'title', 'slug', 'category', 'company', 'job_type', 'work_type',
        'country', 'state', 'city', 'remote_countries', 'location',
        'salary_min', 'salary_max', 'salary_currency', 'salary_period',
        'description', 'requirements', 'apply_type', 'external_url',
        'is_featured', 'status', 'deadline', 'published_at',
    ];
}

/**
 * Map a jobs row (joined with category_name + company_name) to a flat assoc
 * array keyed by job_io_columns(), ready for CSV/JSON output.
 */
function job_to_export_row(array $job): array
{
    return [
        'title'            => (string) ($job['title'] ?? ''),
        'slug'             => (string) ($job['slug'] ?? ''),
        'category'         => (string) ($job['category_name'] ?? ''),
        'company'          => (string) ($job['company_name'] ?? ''),
        'job_type'         => (string) ($job['job_type'] ?? ''),
        'work_type'        => (string) ($job['work_type'] ?? ''),
        'country'          => (string) ($job['country'] ?? ''),
        'state'            => (string) ($job['state'] ?? ''),
        'city'             => (string) ($job['city'] ?? ''),
        'remote_countries' => (string) ($job['remote_countries'] ?? ''),
        'location'         => (string) ($job['location'] ?? ''),
        'salary_min'       => $job['salary_min'] !== null ? (string) $job['salary_min'] : '',
        'salary_max'       => $job['salary_max'] !== null ? (string) $job['salary_max'] : '',
        'salary_currency'  => (string) ($job['salary_currency'] ?? ''),
        'salary_period'    => (string) ($job['salary_period'] ?? ''),
        'description'      => (string) ($job['description'] ?? ''),
        'requirements'     => (string) ($job['requirements'] ?? ''),
        'apply_type'       => (string) ($job['apply_type'] ?? ''),
        'external_url'     => (string) ($job['external_url'] ?? ''),
        'is_featured'      => (string) (int) ($job['is_featured'] ?? 0),
        'status'           => (string) (int) ($job['status'] ?? 0),
        'deadline'         => (string) ($job['deadline'] ?? ''),
        'published_at'     => (string) ($job['published_at'] ?? ''),
    ];
}

/** Find a category by (case-insensitive) name, creating it if absent. */
function find_or_create_category(string $name): ?int
{
    $name = trim($name);
    if ($name === '') return null;
    $row = fetch_one("SELECT id FROM categories WHERE name = ? LIMIT 1", [$name]);
    if ($row) return (int) $row['id'];
    $slug = unique_slug(slugify($name), 'categories');
    q("INSERT INTO categories (name, slug, icon) VALUES (?, ?, 'bi-briefcase')", [$name, $slug]);
    return (int) db()->lastInsertId();
}

/** Find a company by (case-insensitive) name, creating it if absent. */
function find_or_create_company(string $name): ?int
{
    $name = trim($name);
    if ($name === '') return null;
    $row = fetch_one("SELECT id FROM companies WHERE name = ? LIMIT 1", [$name]);
    if ($row) return (int) $row['id'];
    $slug = unique_slug(slugify($name), 'companies');
    q("INSERT INTO companies (name, slug) VALUES (?, ?)", [$name, $slug]);
    return (int) db()->lastInsertId();
}

/**
 * Parse an uploaded import file into a uniform array of associative rows.
 * Supports CSV (first row = header) and JSON (array of objects, or {jobs:[...]}).
 *
 * @throws RuntimeException on unreadable / malformed input.
 */
function parse_import_file(string $tmpPath, string $ext): array
{
    $ext = strtolower($ext);

    if ($ext === 'json') {
        $raw = file_get_contents($tmpPath);
        if ($raw === false) throw new RuntimeException('Could not read the uploaded file.');
        // Strip a UTF-8 BOM if present.
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;
        $data = json_decode($raw, true);
        if (!is_array($data)) throw new RuntimeException('Invalid JSON file.');
        if (isset($data['jobs']) && is_array($data['jobs'])) $data = $data['jobs'];
        $rows = [];
        foreach ($data as $item) {
            if (is_array($item)) $rows[] = $item;
        }
        return $rows;
    }

    if ($ext === 'csv') {
        $fh = fopen($tmpPath, 'r');
        if ($fh === false) throw new RuntimeException('Could not read the uploaded file.');
        $header = null;
        $rows = [];
        while (($cells = fgetcsv($fh, 0, ',', '"', '\\')) !== false) {
            if ($header === null) {
                // Drop a UTF-8 BOM from the first header cell if Excel added one.
                if (isset($cells[0])) {
                    $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $cells[0]) ?? $cells[0];
                }
                $header = array_map(static fn($h) => trim((string) $h), $cells);
                continue;
            }
            // Skip fully-empty lines.
            if (count($cells) === 1 && trim((string) ($cells[0] ?? '')) === '') continue;
            $row = [];
            foreach ($header as $i => $key) {
                if ($key === '') continue;
                $row[$key] = $cells[$i] ?? '';
            }
            $rows[] = $row;
        }
        fclose($fh);
        return $rows;
    }

    throw new RuntimeException('Unsupported file type. Upload a .csv or .json file.');
}

/** Truthy parse for is_featured / status / boolean-ish CSV cells. */
function job_io_bool(mixed $v): int
{
    if (is_bool($v)) return $v ? 1 : 0;
    $s = strtolower(trim((string) $v));
    return in_array($s, ['1', 'yes', 'y', 'true', 'published', 'active', 'featured'], true) ? 1 : 0;
}

/**
 * Insert or update a job. $params must contain every column below. When $id is
 * given the row is updated (slug included); otherwise a new row is inserted.
 * Shared by the admin job form and the importer so the SQL lives in one place.
 *
 * @return int the job id.
 */
function job_save(array $params, ?int $id): int
{
    if ($id) {
        $params['id'] = $id;
        q("UPDATE jobs SET title=:title, slug=:slug, category_id=:category_id, company_id=:company_id,
               location=:location, job_type=:job_type, work_type=:work_type,
               country=:country, state=:state, city=:city, street_address=:street_address, postal_code=:postal_code, remote_countries=:remote_countries,
               salary_min=:salary_min, salary_max=:salary_max, salary_currency=:salary_currency, salary_period=:salary_period,
               description=:description, requirements=:requirements, apply_type=:apply_type,
               external_url=:external_url, is_featured=:is_featured, status=:status,
               published_at=:published_at, deadline=:deadline,
               updated_at=NOW()
           WHERE id=:id", $params);

        // Notify Google: published → index, draft → deindex
        if (file_exists(BASE_PATH . '/includes/google_indexing.php')) {
            require_once BASE_PATH . '/includes/google_indexing.php';
            $jobUrl = url('job/' . $params['slug']);
            if ((int)$params['status'] === 1) {
                google_index_url($jobUrl);
            } else {
                google_deindex_url($jobUrl);
            }
        }

        return $id;
    }

    q("INSERT INTO jobs (title, slug, category_id, company_id, location, job_type, work_type,
           country, state, city, street_address, postal_code, remote_countries, salary_min, salary_max, salary_currency, salary_period,
           description, requirements, apply_type, external_url, is_featured, status, published_at, deadline)
       VALUES (:title, :slug, :category_id, :company_id, :location, :job_type, :work_type,
           :country, :state, :city, :street_address, :postal_code, :remote_countries, :salary_min, :salary_max, :salary_currency, :salary_period,
           :description, :requirements, :apply_type, :external_url, :is_featured, :status, :published_at, :deadline)", $params);
    $newId = (int) db()->lastInsertId();

    // Notify Google for newly published jobs
    if ((int)$params['status'] === 1 && file_exists(BASE_PATH . '/includes/google_indexing.php')) {
        require_once BASE_PATH . '/includes/google_indexing.php';
        google_index_url(url('job/' . $params['slug']));
    }

    return $newId;
}

/**
 * Normalize one import row, resolve category/company, and upsert by slug.
 * Mutates $stats: ['inserted','updated','skipped','errors'=>[]].
 */
function import_job_row(array $row, array &$stats, int $lineNo): void
{
    // Case-insensitive key access for the incoming row.
    $lower = [];
    foreach ($row as $k => $v) {
        $lower[strtolower(trim((string) $k))] = is_string($v) ? trim($v) : $v;
    }
    $get = static fn(string $k, string $d = ''): string => isset($lower[$k]) ? (string) $lower[$k] : $d;

    $title = $get('title');
    if ($title === '') {
        $stats['skipped']++;
        $stats['errors'][] = "Row $lineNo: missing title — skipped.";
        return;
    }

    $catName = $get('category');
    $coName  = $get('company');
    if ($catName === '' || $coName === '') {
        $stats['skipped']++;
        $stats['errors'][] = "Row $lineNo ($title): category and company are required — skipped.";
        return;
    }
    $categoryId = find_or_create_category($catName);
    $companyId  = find_or_create_company($coName);
    if (!$categoryId || !$companyId) {
        $stats['skipped']++;
        $stats['errors'][] = "Row $lineNo ($title): could not resolve category/company — skipped.";
        return;
    }

    $validJobTypes = ['full-time', 'part-time', 'contract', 'internship', 'remote'];
    $jobType = strtolower($get('job_type'));
    if (!in_array($jobType, $validJobTypes, true)) $jobType = 'full-time';

    $workType = strtolower($get('work_type'));
    if (!in_array($workType, WORK_TYPES, true)) $workType = 'on-site';

    $currency = strtoupper($get('salary_currency'));
    if (!isset(CURRENCIES[$currency])) $currency = DEFAULT_CURRENCY;

    $period = strtolower($get('salary_period'));
    if (!isset(SALARY_PERIODS[$period])) $period = DEFAULT_SALARY_PERIOD;

    $salaryMin = $get('salary_min');
    $salaryMax = $get('salary_max');
    $salaryMin = ($salaryMin !== '' && is_numeric($salaryMin)) ? (int) $salaryMin : null;
    $salaryMax = ($salaryMax !== '' && is_numeric($salaryMax)) ? (int) $salaryMax : null;

    $description = clean_html($get('description'));
    if (trim(strip_tags($description)) === '') {
        $stats['skipped']++;
        $stats['errors'][] = "Row $lineNo ($title): missing description — skipped.";
        return;
    }
    $requirements = clean_html($get('requirements'));
    $requirements = trim(strip_tags($requirements)) === '' ? null : $requirements;

    $applyType = strtolower($get('apply_type')) === 'external' ? 'external' : 'internal';
    $externalUrl = $get('external_url');
    $externalUrl = ($applyType === 'external' && filter_var($externalUrl, FILTER_VALIDATE_URL)) ? $externalUrl : null;

    $featured = job_io_bool($lower['is_featured'] ?? '');
    $status   = job_io_bool($lower['status'] ?? '1');

    // Deadline: accept Y-m-d (or anything strtotime understands) -> Y-m-d, else null.
    $deadline = $get('deadline');
    $deadlineTs = $deadline !== '' ? strtotime($deadline) : false;
    $deadline = $deadlineTs !== false ? date('Y-m-d', $deadlineTs) : null;

    // published_at: exported values are already UTC. Parse to UTC 'Y-m-d H:i:s'.
    // Blank + published -> now (UTC). Blank + draft -> null.
    $publishedRaw = $get('published_at');
    if ($publishedRaw !== '' && ($pts = strtotime($publishedRaw)) !== false) {
        $publishedAt = gmdate('Y-m-d H:i:s', $pts);
    } else {
        $publishedAt = $status === 1 ? gmdate('Y-m-d H:i:s') : null;
    }

    // Geo / remote handling (mirrors admin_job_form()).
    if ($workType === 'remote') {
        $country = $state = $city = '';
        $countriesIn = array_filter(array_map('trim', explode(',', $get('remote_countries'))));
        $countriesIn = array_values(array_intersect($countriesIn, REMOTE_COUNTRIES));
        $location = $countriesIn ? ('Remote · ' . implode(', ', $countriesIn)) : 'Remote';
        $remoteCountries = $countriesIn ? implode(', ', $countriesIn) : null;
    } else {
        $country = $get('country');
        $state   = $get('state');
        $city    = $get('city');
        $location = $get('location');
        if ($location === '') {
            $location = trim(implode(', ', array_filter([$city, $state, $country])));
        }
        $remoteCountries = null;
    }

    // Resolve slug -> decide insert vs update (upsert). slugify() is idempotent
    // on already-clean exported slugs, and avoids depending on index.php helpers.
    $rawSlug = $get('slug');
    $baseSlug = $rawSlug !== '' ? slugify($rawSlug) : slugify($title);
    if ($baseSlug === '' || $baseSlug === 'n-a') $baseSlug = slugify($title);

    $existing = fetch_one("SELECT id FROM jobs WHERE slug = ?", [$baseSlug]);
    $id   = $existing ? (int) $existing['id'] : null;
    $slug = $existing ? $baseSlug : unique_slug($baseSlug, 'jobs');

    $params = [
        'title'            => $title,
        'slug'             => $slug,
        'category_id'      => $categoryId,
        'company_id'       => $companyId,
        'location'         => $location !== '' ? $location : null,
        'job_type'         => $jobType,
        'work_type'        => $workType,
        'country'          => $country !== '' ? $country : null,
        'state'            => $state !== '' ? $state : null,
        'city'             => $city !== '' ? $city : null,
        'street_address'   => null,
        'postal_code'      => null,
        'remote_countries' => $remoteCountries,
        'salary_min'       => $salaryMin,
        'salary_max'       => $salaryMax,
        'salary_currency'  => $currency,
        'salary_period'    => $period,
        'description'      => $description,
        'requirements'     => $requirements,
        'apply_type'       => $applyType,
        'external_url'     => $externalUrl,
        'is_featured'      => $featured,
        'status'           => $status,
        'published_at'     => $publishedAt,
        'deadline'         => $deadline,
    ];

    job_save($params, $id);
    if ($id) $stats['updated']++; else $stats['inserted']++;
}
