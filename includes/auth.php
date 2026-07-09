<?php
/**
 * Authentication + authorization helpers (users and admins).
 */

declare(strict_types=1);

// ----- Candidate (user) -------------------------------------------------

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

/** Full user row (cached per request). */
function current_user(): ?array
{
    static $cache = false;
    if ($cache !== false) return $cache;
    $id = current_user_id();
    $cache = $id ? fetch_one('SELECT * FROM users WHERE id = ?', [$id]) : null;
    return $cache;
}

function logout_user(): void
{
    unset($_SESSION['user_id'], $_SESSION['user_name']);
}

/** Require a logged-in candidate or redirect to login. */
function require_user(): void
{
    if (!is_logged_in()) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? url('/');
        flash('warning', 'Please log in to continue.');
        redirect('login');
    }
}

// ----- Admin ------------------------------------------------------------

function login_admin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id']   = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
}

function current_admin_id(): ?int
{
    return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
}

function is_admin(): bool
{
    return current_admin_id() !== null;
}

function logout_admin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name']);
}

function require_admin(): void
{
    if (!is_admin()) {
        $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? url('/admin');
        flash('warning', 'Please log in as administrator.');
        redirect('admin/login');
    }
}

// ----- Secure file upload ----------------------------------------------

/**
 * Handle a single uploaded file.
 * Returns the stored filename on success, or null when no file was sent.
 * Throws RuntimeException on validation failure.
 *
 * @param array  $allowedExt  e.g. ['pdf','doc','docx']
 */
function handle_upload(array $file, string $destDir, array $allowedExt, int $maxSize, string $prefix = 'f'): ?string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed (code ' . $file['error'] . ').');
    }
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File is too large. Max ' . round($maxSize / 1048576, 1) . ' MB.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Invalid file type. Allowed: ' . implode(', ', $allowedExt) . '.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload source.');
    }

    if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
        throw new RuntimeException('Upload directory is not writable.');
    }

    $name = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $target = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $name;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    return $name;
}
