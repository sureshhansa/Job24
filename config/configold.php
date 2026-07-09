<?php
/**
 * Global configuration & bootstrap.
 * Adjust DB_* and BASE_URL for your environment.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------
// Environment / errors
// ---------------------------------------------------------------------
define('APP_ENV', 'development');           // 'development' | 'production'

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ---------------------------------------------------------------------
// Database credentials
// ---------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'jobupdate_guildnew');
define('DB_USER', 'jobupdate_guildnew');
define('DB_PASS', 'Hansa@983392');
define('DB_CHARSET', 'utf8mb4');

// ---------------------------------------------------------------------
// Paths & URLs
// ---------------------------------------------------------------------
// BASE_PATH = filesystem root of the project (no trailing slash)
define('BASE_PATH', dirname(__DIR__));

// BASE_URL is auto-detected so it works in a subfolder on shared hosting.
// You can hard-code it instead, e.g. define('BASE_URL', 'https://example.com');
if (!defined('BASE_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    // Folder the front controller lives in (handles subdirectory installs)
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = rtrim($dir, '/');
    define('BASE_URL', $scheme . '://' . $host . $dir);
}

define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('RESUME_PATH', UPLOAD_PATH . '/resumes');
define('LOGO_PATH',   UPLOAD_PATH . '/logos');
define('BRANDING_PATH', UPLOAD_PATH . '/branding');

define('UPLOAD_URL',  BASE_URL . '/uploads');
define('ASSET_URL',   BASE_URL . '/assets');

// ---------------------------------------------------------------------
// Site meta
// ---------------------------------------------------------------------
define('SITE_NAME', 'JobPortal');
define('SITE_TAGLINE', 'Find your next opportunity');
define('ADMIN_EMAIL', 'admin@jobportal.test');

// Upload rules
define('MAX_RESUME_SIZE', 3 * 1024 * 1024); // 3 MB
define('ALLOWED_RESUME_EXT', ['pdf', 'doc', 'docx']);
define('MAX_LOGO_SIZE', 2 * 1024 * 1024);   // 2 MB
define('ALLOWED_LOGO_EXT', ['png', 'jpg', 'jpeg', 'webp']);
define('MAX_FAVICON_SIZE', 512 * 1024);     // 512 KB
define('ALLOWED_FAVICON_EXT', ['png', 'ico', 'svg']);

// Work type (job location type)
define('WORK_TYPES', ['remote', 'hybrid', 'on-site']);

// Countries selectable for remote jobs (applicant location requirements).
define('REMOTE_COUNTRIES', [
    'India', 'United States', 'United Kingdom', 'Canada',
    'Australia', 'Germany', 'United Arab Emirates', 'Singapore', 'Worldwide',
]);

// Salary currencies. 'grouping' => 'indian' uses lakh/crore digit groups.
// Keys are ISO 4217 codes (also emitted in the JobPosting schema baseSalary).
define('CURRENCIES', [
    'USD' => ['symbol' => '$', 'label' => 'US Dollar ($)',     'grouping' => 'western'],
    'INR' => ['symbol' => '₹', 'label' => 'Indian Rupee (₹)', 'grouping' => 'indian'],
]);
define('DEFAULT_CURRENCY', 'USD'); // existing/unspecified jobs fall back to this

// Salary period: how the salary range is expressed.
//   'label'  => short suffix shown after the amount (e.g. "₹50,000 /month")
//   'schema' => schema.org QuantitativeValue.unitText for JobPosting baseSalary
define('SALARY_PERIODS', [
    'year'  => ['label' => '/year',  'schema' => 'YEAR'],
    'month' => ['label' => '/month', 'schema' => 'MONTH'],
]);
define('DEFAULT_SALARY_PERIOD', 'year');

// Admin-facing timezone. Datetimes are STORED in UTC (DB NOW() is UTC), but the
// admin publish date/time field is shown and entered in this zone.
define('APP_TIMEZONE', 'Asia/Kolkata');
define('APP_TZ_LABEL', 'IST');

define('PER_PAGE', 8); // jobs per page

// ---------------------------------------------------------------------
// Session (start once, with safe params)
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_name('JOBPORTALSESS');
    session_start();
}

date_default_timezone_set('UTC');

// ---------------------------------------------------------------------
// Load core includes
// ---------------------------------------------------------------------
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/includes/helpers.php';
require_once BASE_PATH . '/includes/csrf.php';
require_once BASE_PATH . '/includes/flash.php';
require_once BASE_PATH . '/includes/auth.php';
require_once BASE_PATH . '/includes/settings.php';
require_once BASE_PATH . '/includes/jobs_io.php';
