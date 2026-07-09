<?php
/**
 * One-time flash messages stored in the session.
 */

declare(strict_types=1);

/** Set a flash message. Types: success | error | warning | info */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][$type][] = $message;
}

/** Pull and clear all flash messages. */
function flash_take(): array
{
    $messages = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $messages;
}

/** Render flash messages as Bootstrap alerts. */
function flash_render(): string
{
    $map = [
        'success' => 'success',
        'error'   => 'danger',
        'warning' => 'warning',
        'info'    => 'info',
    ];
    $html = '';
    foreach (flash_take() as $type => $messages) {
        $cls = $map[$type] ?? 'secondary';
        foreach ($messages as $msg) {
            $html .= '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
                  .  e($msg)
                  .  '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                  .  '</div>';
        }
    }
    return $html;
}
