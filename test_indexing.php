<?php
/**
 * Google Indexing API — Test Script
 * Upload this file to your ROOT folder, open it in browser, then DELETE it.
 * URL: yoursite.com/test_indexing.php
 */

// ── Load your site config so BASE_PATH & url() are available ──
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/google_indexing.php';

$keyFile  = BASE_PATH . '/config/google_indexing.json';
$results  = [];

// ── Test 1: Key file exists? ──
if (file_exists($keyFile)) {
    $results[] = ['ok', '✅ JSON key file mila: config/google_indexing.json'];
} else {
    $results[] = ['fail', '❌ JSON key file nahi mila! config/google_indexing.json check karo.'];
}

// ── Test 2: JSON valid hai? ──
$key = json_decode(file_get_contents($keyFile), true);
if ($key && isset($key['client_email'], $key['private_key'])) {
    $results[] = ['ok', '✅ JSON valid hai. Client email: <strong>' . htmlspecialchars($key['client_email']) . '</strong>'];
} else {
    $results[] = ['fail', '❌ JSON invalid hai ya fields missing hain.'];
}

// ── Test 3: Access Token mil raha hai? ──
$token = google_indexing_get_token();
if ($token) {
    $results[] = ['ok', '✅ Google se Access Token mila! Authentication sahi hai.'];
} else {
    $results[] = ['fail', '❌ Access Token nahi mila. Service Account permissions check karo — Search Console mein Owner add kiya?'];
}

// ── Test 4: Actual URL notify karo ──
$testUrl = url('jobs'); // jobs page ko test ke liye use karein
if ($token) {
    // Send actual URL_UPDATED notification
    $payload  = json_encode(['url' => $testUrl, 'type' => 'URL_UPDATED']);
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

    $resp = json_decode($response, true);

    if ($httpCode === 200) {
        $results[] = ['ok', '✅ Google Indexing API ne request accept ki! URL: <strong>' . htmlspecialchars($testUrl) . '</strong>'];
    } elseif ($httpCode === 403) {
        $results[] = ['fail', '❌ 403 Forbidden — Service Account ko Search Console mein <strong>Owner</strong> permission do.'];
    } elseif ($httpCode === 400) {
        $results[] = ['warn', '⚠️ 400 Bad Request — URL format check karo: <strong>' . htmlspecialchars($testUrl) . '</strong><br><small>' . htmlspecialchars($response) . '</small>'];
    } else {
        $results[] = ['fail', "❌ HTTP $httpCode — Response: <small>" . htmlspecialchars($response) . '</small>'];
    }
} else {
    $results[] = ['warn', '⚠️ Token nahi mila isliye API test skip kiya.'];
}

$allOk = !in_array('fail', array_column($results, 0));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Google Indexing API Test</title>
    <style>
        body { font-family: sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; }
        h2 { margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .result { padding: 12px 16px; border-radius: 8px; margin-bottom: 12px; font-size: 15px; }
        .ok   { background: #d1fae5; border-left: 4px solid #10b981; }
        .fail { background: #fee2e2; border-left: 4px solid #ef4444; }
        .warn { background: #fef3c7; border-left: 4px solid #f59e0b; }
        .banner { padding: 16px 20px; border-radius: 10px; font-size: 17px; font-weight: bold; margin-bottom: 28px; }
        .banner.ok   { background: #d1fae5; color: #065f46; }
        .banner.fail { background: #fee2e2; color: #991b1b; }
        .delete-note { background: #fff3cd; border: 1px solid #ffc107; padding: 14px; border-radius: 8px; margin-top: 24px; font-size: 14px; }
    </style>
</head>
<body>

<h2>🔍 Google Indexing API — Test</h2>
<p class="subtitle">Yeh page check karta hai ki aapka setup sahi hai ya nahi.</p>

<div class="banner <?= $allOk ? 'ok' : 'fail' ?>">
    <?= $allOk ? '🎉 Sab kuch sahi hai! Indexing API kaam kar rahi hai.' : '⚠️ Kuch issues hain — neeche dekho.' ?>
</div>

<?php foreach ($results as [$type, $msg]): ?>
    <div class="result <?= $type ?>"><?= $msg ?></div>
<?php endforeach; ?>

<div class="delete-note">
    ⚠️ <strong>Important:</strong> Yeh test file check karne ke baad <strong>turant delete karo</strong> server se (<code>test_indexing.php</code>).
    Isko live rehne dena security risk hai.
</div>

</body>
</html>
