<?php

/**
 * ============================================================
 *  JOB REFRESH SYSTEM (FINAL VERSION)
 * ============================================================
 * - Random published_at (last 12 hours)
 * - Safe for cron
 * - Uses your q() helper
 * ============================================================
 */

define('CRON_SECRET_KEY', 'guildhiring-refresh-2026-x9k7m2');

$isCli = php_sapi_name() === 'cli';
$isWeb = isset($_GET['cron_key']) && $_GET['cron_key'] === CRON_SECRET_KEY;

if (!$isCli && !$isWeb) {
    http_response_code(403);
    exit('403 Forbidden');
}

// Bootstrap
require_once __DIR__ . '/config/config.php';

try {

    // 🔥 Random last 12 hours (0–720 minutes)
    q("
        UPDATE jobs
        SET published_at = DATE_SUB(
            NOW(),
            INTERVAL FLOOR(RAND() * 720) MINUTE
        )
        WHERE status = 1
    ");

    // Count updated rows
    $count = q("SELECT ROW_COUNT() AS cnt")->fetch()['cnt'] ?? 0;

    echo date('Y-m-d H:i:s') . " [OK] Randomized {$count} job times (0-12 hours)\n";

    file_put_contents(
        __DIR__ . '/refresh_jobs.log',
        date('Y-m-d H:i:s') . " [OK] refreshed\n",
        FILE_APPEND
    );

} catch (Throwable $e) {

    echo "[ERROR] " . $e->getMessage();

    file_put_contents(
        __DIR__ . '/refresh_jobs.log',
        date('Y-m-d H:i:s') . " [ERROR] " . $e->getMessage() . "\n",
        FILE_APPEND
    );
}