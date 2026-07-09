<?php
/**
 * Google Indexing API — Instant Indexing Helper
 *
 * Automatically notifies Google when a job is published or removed.
 * Requires: config/google_indexing.json (Service Account JSON key)
 *
 * Docs: https://developers.google.com/search/apis/indexing-api/v3/quickstart
 */

define('GOOGLE_INDEXING_KEY_FILE', BASE_PATH . '/config/google_indexing.json');
define('GOOGLE_INDEXING_ENDPOINT', 'https://indexing.googleapis.com/v3/urlNotifications:publish');
define('GOOGLE_TOKEN_ENDPOINT',    'https://oauth2.googleapis.com/token');
define('GOOGLE_INDEXING_SCOPE',    'https://www.googleapis.com/auth/indexing');

// ─────────────────────────────────────────────
//  PUBLIC API
// ─────────────────────────────────────────────

/**
 * Notify Google that a job URL was PUBLISHED / UPDATED.
 * Call after INSERT or UPDATE when status = 1.
 */
function google_index_url(string $jobUrl): void
{
    google_indexing_notify($jobUrl, 'URL_UPDATED');
}

/**
 * Notify Google that a job URL was DELETED / UNPUBLISHED.
 * Call before or after DELETE, or when status flips to 0.
 */
function google_deindex_url(string $jobUrl): void
{
    google_indexing_notify($jobUrl, 'URL_DELETED');
}

// ─────────────────────────────────────────────
//  INTERNALS
// ─────────────────────────────────────────────

function google_indexing_notify(string $pageUrl, string $type): void
{
    // Silently skip if key file missing — never crash the site
    if (!file_exists(GOOGLE_INDEXING_KEY_FILE)) {
        error_log('[GoogleIndexing] Key file not found: ' . GOOGLE_INDEXING_KEY_FILE);
        return;
    }

    try {
        $token = google_indexing_get_token();
        if (!$token) {
            error_log('[GoogleIndexing] Could not obtain access token.');
            return;
        }

        $payload = json_encode(['url' => $pageUrl, 'type' => $type]);

        $ch = curl_init(GOOGLE_INDEXING_ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("[GoogleIndexing] $type failed ($httpCode): $response — URL: $pageUrl");
        } else {
            error_log("[GoogleIndexing] $type success: $pageUrl");
        }

    } catch (Throwable $e) {
        error_log('[GoogleIndexing] Exception: ' . $e->getMessage());
    }
}

function google_indexing_get_token(): ?string
{
    // Cache token in session for up to 55 minutes (token lasts 60 min)
    if (!isset($_SESSION)) session_start();
    $cacheKey = 'g_index_token';
    $expKey   = 'g_index_token_exp';

    if (!empty($_SESSION[$cacheKey]) && !empty($_SESSION[$expKey]) && time() < $_SESSION[$expKey]) {
        return $_SESSION[$cacheKey];
    }

    $key = json_decode(file_get_contents(GOOGLE_INDEXING_KEY_FILE), true);
    if (!$key) return null;

    $now = time();
    $header    = base64_url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $claimset  = base64_url_encode(json_encode([
        'iss'   => $key['client_email'],
        'scope' => GOOGLE_INDEXING_SCOPE,
        'aud'   => GOOGLE_TOKEN_ENDPOINT,
        'exp'   => $now + 3600,
        'iat'   => $now,
    ]));

    $sigInput = $header . '.' . $claimset;

    // Load private key and sign
    $privateKey = openssl_pkey_get_private($key['private_key']);
    if (!$privateKey) return null;

    $signature = '';
    openssl_sign($sigInput, $signature, $privateKey, 'SHA256');
    $jwt = $sigInput . '.' . base64_url_encode($signature);

    // Exchange JWT for access token
    $ch = curl_init(GOOGLE_TOKEN_ENDPOINT);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT    => 10,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    if (empty($data['access_token'])) return null;

    // Cache token
    $_SESSION[$cacheKey] = $data['access_token'];
    $_SESSION[$expKey]   = $now + 3300; // 55 min

    return $data['access_token'];
}

function base64_url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
