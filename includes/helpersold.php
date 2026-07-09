<?php
/**
 * General helper functions.
 */

declare(strict_types=1);

/** Escape for HTML output. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Build a site URL from a path. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Redirect helper. */
function redirect(string $path): never
{
    $location = preg_match('#^https?://#i', $path) ? $path : url($path);
    header('Location: ' . $location);
    exit;
}

/** Old input value (for repopulating forms after validation error). */
function old(string $key, string $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function set_old(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['csrf_token']);
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

/** Create a URL-safe slug. */
function slugify(string $text): string
{
    $text = trim($text);
    $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? '';
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text) ?? '';
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text) ?? '';
    return $text === '' ? 'n-a' : $text;
}

/**
 * Ensure a slug is unique within a table; appends -2, -3, ... if needed.
 */
function unique_slug(string $base, string $table, ?int $ignoreId = null): string
{
    $allowed = ['jobs', 'categories', 'companies'];
    if (!in_array($table, $allowed, true)) {
        return $base;
    }
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?";
        $params = [$slug];
        if ($ignoreId !== null) {
            $sql .= " AND id <> ?";
            $params[] = $ignoreId;
        }
        if (fetch_one($sql, $params) === null) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

/** Group an integer Indian-style (e.g. 1200000 => "12,00,000"). */
function indian_number_format(int $n): string
{
    $neg = $n < 0;
    $s = (string) abs($n);
    if (strlen($s) > 3) {
        $last3 = substr($s, -3);
        $rest  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', substr($s, 0, -3)) ?? '';
        $s = $rest . ',' . $last3;
    }
    return ($neg ? '-' : '') . $s;
}

/** ISO-4217 code => its config entry, falling back to the default currency. */
function currency_meta(?string $code): array
{
    $code = strtoupper(trim((string) $code));
    return CURRENCIES[$code] ?? CURRENCIES[DEFAULT_CURRENCY];
}

/** Format a salary range in the job's currency, with an optional period suffix. */
function format_salary(?int $min, ?int $max, ?string $currency = null, ?string $period = null): string
{
    $cur = currency_meta($currency);
    $fmt = fn(int $n) => $cur['symbol'] . (
        ($cur['grouping'] ?? 'western') === 'indian'
            ? indian_number_format($n)
            : number_format($n)
    );
    if ($min && $max)      $amount = $fmt($min) . ' - ' . $fmt($max);
    elseif ($min)          $amount = 'From ' . $fmt($min);
    elseif ($max)          $amount = 'Up to ' . $fmt($max);
    else                   return 'Negotiable';

    $p = SALARY_PERIODS[$period] ?? SALARY_PERIODS[DEFAULT_SALARY_PERIOD] ?? null;
    return $p ? $amount . ' ' . $p['label'] : $amount;
}

/**
 * Parse an admin datetime-local input ("Y-m-dTH:i", interpreted in APP_TIMEZONE)
 * into a UTC DateTimeImmutable. Returns null for empty/invalid input.
 */
function local_input_to_utc(string $local): ?DateTimeImmutable
{
    $local = trim($local);
    if ($local === '') return null;
    try {
        return (new DateTimeImmutable($local, new DateTimeZone(APP_TIMEZONE)))
            ->setTimezone(new DateTimeZone('UTC'));
    } catch (Exception $e) {
        return null;
    }
}

/** Convert a stored UTC datetime to an APP_TIMEZONE datetime-local value ("Y-m-dTH:i"). */
function utc_to_local_input(?string $utc): string
{
    if ($utc === null || trim($utc) === '') return '';
    try {
        return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone(APP_TIMEZONE))
            ->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        return '';
    }
}

/** Current time as an APP_TIMEZONE datetime-local value (form default). */
function now_local_input(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('Y-m-d\TH:i');
}

/** Human friendly "time ago". */
function time_ago(string $datetime): string
{
    $ts = strtotime($datetime);
    if ($ts === false) return $datetime;
    $diff = time() - $ts;
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60) . 'm ago';
    if ($diff < 86400)   return floor($diff / 3600) . 'h ago';
    if ($diff < 2592000) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $ts);
}

/** Pretty label for a job type enum. */
function job_type_label(string $type): string
{
    return ucwords(str_replace('-', ' ', $type));
}

/** Human label for a work type (remote/hybrid/on-site). */
function work_type_label(string $workType): string
{
    return [
        'remote'  => 'Remote',
        'hybrid'  => 'Hybrid',
        'on-site' => 'On-site',
    ][$workType] ?? ucfirst($workType);
}

/** Bootstrap badge HTML for a work type. Returns '' for empty/unknown. */
function work_type_badge(?string $workType): string
{
    $map = [
        'remote'  => ['Remote',  'success', 'bi-globe'],
        'hybrid'  => ['Hybrid',  'info',    'bi-shuffle'],
        'on-site' => ['On-site', 'secondary', 'bi-building'],
    ];
    if (!$workType || !isset($map[$workType])) return '';
    [$label, $color, $icon] = $map[$workType];
    return '<span class="badge text-bg-' . $color . '"><i class="bi ' . $icon . '"></i> ' . $label . '</span>';
}

/** Map work_type to schema.org jobLocationType (TELECOMMUTE for remote). */
function schema_location_type(?string $workType): ?string
{
    return $workType === 'remote' ? 'TELECOMMUTE' : null;
}

/**
 * SQL fragment: a job is publicly visible only when published and its
 * scheduled publish time has arrived. Pass the table alias used in the query.
 * Example:  "WHERE " . live_sql('j')
 */
function live_sql(string $alias = 'jobs'): string
{
    return "$alias.status = 1
        AND $alias.published_at IS NOT NULL
        AND $alias.published_at <= NOW()
        AND ($alias.deadline IS NULL OR $alias.deadline >= CURDATE())";
}

/** Decode the stored remote_countries field into an array. */
function remote_countries(?string $stored): array
{
    if ($stored === null || trim($stored) === '') return [];
    return array_values(array_filter(array_map('trim', explode(',', $stored))));
}

/**
 * Map a display country name to its ISO 3166-1 alpha-2 code.
 * Google's JobPosting applicantLocationRequirements validates reliably
 * against ISO codes, so we emit codes in the schema (not free-text names).
 * Returns null for "Worldwide" or anything unmapped.
 */
function country_code(string $name): ?string
{
    static $map = [
        'india'                => 'IN',
        'united states'        => 'US',
        'usa'                  => 'US',
        'united kingdom'       => 'GB',
        'uk'                   => 'GB',
        'canada'               => 'CA',
        'australia'            => 'AU',
        'germany'              => 'DE',
        'united arab emirates' => 'AE',
        'uae'                  => 'AE',
        'singapore'            => 'SG',
    ];
    return $map[strtolower(trim($name))] ?? null;
}

/**
 * Canonical, unambiguous country name for JobPosting
 * applicantLocationRequirements (a Country node's `name`).
 *
 * Unlike addressCountry — which wants the ISO alpha-2 code — Google resolves
 * applicantLocationRequirements names against its entity graph, where 2-letter
 * codes are ambiguous ("CA" => California, "IN" => Indiana) and break the
 * "Country" type. So we emit full names. Returns null for "Worldwide" or any
 * value we don't recognise (so it's skipped rather than emitted unvalidated).
 */
function schema_country_name(string $name): ?string
{
    static $map = [
        'india'                => 'India',
        'united states'        => 'United States',
        'usa'                  => 'United States',
        'united kingdom'       => 'United Kingdom',
        'uk'                   => 'United Kingdom',
        'canada'               => 'Canada',
        'australia'            => 'Australia',
        'germany'              => 'Germany',
        'united arab emirates' => 'United Arab Emirates',
        'uae'                  => 'United Arab Emirates',
        'singapore'            => 'Singapore',
    ];
    return $map[strtolower(trim($name))] ?? null;
}

/** Badge class for application status. */
function status_badge(string $status): string
{
    return [
        'pending'     => 'secondary',
        'reviewed'    => 'info',
        'shortlisted' => 'primary',
        'rejected'    => 'danger',
        'hired'       => 'success',
    ][$status] ?? 'secondary';
}

/** Truncate plain text. */
function excerpt(string $text, int $words = 28): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    $parts = explode(' ', $text);
    if (count($parts) <= $words) return $text;
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

/**
 * Sanitize rich-text HTML (from the admin WYSIWYG editor) down to a safe
 * allowlist of tags/attributes. Dependency-free (DOMDocument). Strips scripts,
 * event handlers, styles, iframes, and any javascript: URLs. Use on SAVE so the
 * DB only ever holds clean markup.
 */
function clean_html(string $html): string
{
    $html = trim($html);
    if ($html === '') return '';

    // tag => list of allowed attributes
    $allowed = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [],
        'u' => [], 's' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'h2' => [], 'h3' => [], 'h4' => [], 'blockquote' => [], 'pre' => [], 'code' => [],
        'a' => ['href'],
    ];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<?xml encoding="UTF-8"><div id="__rt_root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $root = $doc->getElementById('__rt_root');
    if (!$root) return '';

    // Tags removed together with their contents (never unwrapped to text).
    $dropWithContent = ['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template', 'svg'];

    foreach (iterator_to_array($root->getElementsByTagName('*')) as $node) {
        if ($node->parentNode === null) continue; // already detached
        $tag = strtolower($node->nodeName);
        if (in_array($tag, $dropWithContent, true)) {
            $node->parentNode->removeChild($node);
            continue;
        }
        if (!isset($allowed[$tag])) {
            // Disallowed tag: unwrap to its text content (drops any markup inside).
            $node->parentNode->replaceChild($doc->createTextNode($node->textContent), $node);
            continue;
        }
        if ($node->hasAttributes()) {
            foreach (iterator_to_array($node->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (!in_array($name, $allowed[$tag], true)) {
                    $node->removeAttribute($attr->name);
                } elseif ($name === 'href'
                    && !preg_match('#^(https?:|mailto:|/)#i', trim($attr->value))) {
                    $node->removeAttribute($attr->name); // block javascript:, data:, etc.
                }
            }
        }
    }

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

/**
 * Render stored job text. New jobs hold sanitized HTML from the editor; old jobs
 * hold plain text — for those, escape + nl2br so line breaks survive.
 */
function display_richtext(?string $content): string
{
    $content = (string) $content;
    if ($content === '') return '';
    // Contains real markup? Sanitize and emit as HTML; else treat as plain text.
    if ($content !== strip_tags($content)) {
        return clean_html($content);
    }
    return nl2br(e($content));
}

/** Read a GET param safely as trimmed string. */
function get_param(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

/** Read a POST param safely as trimmed string. */
function post_param(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

/** Current absolute URL path segment (for active nav highlighting). */
function current_route(): string
{
    return trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
}

/**
 * Render a view with layout-agnostic data.
 * Extracts $data into scope and includes the view file.
 */
function view(string $file, array $data = []): void
{
    $path = BASE_PATH . '/views/' . ltrim($file, '/') . '.php';
    if (!is_file($path)) {
        http_response_code(500);
        exit('View not found: ' . e($file));
    }
    extract($data, EXTR_SKIP);
    require $path;
}

/**
 * Build pagination metadata.
 */
function paginate(int $total, int $perPage, int $current): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $current = max(1, min($current, $pages));
    return [
        'total'   => $total,
        'perPage' => $perPage,
        'pages'   => $pages,
        'current' => $current,
        'offset'  => ($current - 1) * $perPage,
        'hasPrev' => $current > 1,
        'hasNext' => $current < $pages,
    ];
}

/** Build a query string preserving current filters, overriding given keys. */
function query_with(array $overrides): string
{
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    return $params ? '?' . http_build_query($params) : '';
}
