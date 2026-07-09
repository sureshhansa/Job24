<?php
/**
 * Site settings — key/value store with request-level caching.
 * Falls back to the SITE_* constants if the settings table is missing
 * (e.g. before upgrade.sql has been run).
 */

declare(strict_types=1);

/** Load all settings once per request. */
function settings_all(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;

    $cache = [];
    try {
        foreach (fetch_all("SELECT `key`, `value` FROM settings") as $row) {
            $cache[$row['key']] = $row['value'];
        }
    } catch (Throwable $e) {
        $cache = []; // table not created yet — use defaults
    }
    return $cache;
}

/** Get a single setting with a fallback default. */
function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    $val = $all[$key] ?? '';
    return ($val === '' || $val === null) ? $default : (string) $val;
}

/** Persist a setting (insert or update). */
function setting_set(string $key, ?string $value): void
{
    q("INSERT INTO settings (`key`, `value`) VALUES (?, ?)
       ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$key, $value]);
}

// ----- Convenience accessors used across the UI ------------------------

function site_name(): string        { return setting('site_name', defined('SITE_NAME') ? SITE_NAME : 'JobPortal'); }
function site_tagline(): string     { return setting('site_tagline', defined('SITE_TAGLINE') ? SITE_TAGLINE : 'Find your next opportunity'); }
function meta_title(): string       { return setting('meta_title', site_name() . ' — ' . site_tagline()); }
function meta_description(): string  { return setting('meta_description', site_tagline()); }

/** Public URL for the uploaded logo, or '' if none. */
function site_logo_url(): string
{
    $f = setting('site_logo');
    return $f !== '' ? UPLOAD_URL . '/branding/' . $f : '';
}

/** Public URL for the favicon, or '' if none. */
function favicon_url(): string
{
    $f = setting('favicon');
    return $f !== '' ? UPLOAD_URL . '/branding/' . $f : '';
}
