<?php
/**
 * CSRF protection helpers.
 */

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Hidden input field for forms. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Validate a submitted token (constant-time). */
function csrf_check(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token)
        && !empty($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

/**
 * Guard for POST handlers: aborts on invalid token.
 */
function csrf_verify(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_check()) {
        http_response_code(419);
        flash('error', 'Your session expired. Please try again.');
        $ref = $_SERVER['HTTP_REFERER'] ?? url('/');
        redirect($ref);
    }
}
