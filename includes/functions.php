<?php
/**
 * General Helper Functions — Hagz Clinic System
 */

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific page
 */
function redirect(string $url): void {
    header("Location: $url");
    exit();
}

/**
 * Set a flash message in the session (displayed once)
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear the flash message from the session
 */
function get_flash(): ?array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Return a JSON response and exit (used by AJAX endpoints)
 */
function json_response(bool $success, string $message, array $data = []): void {
    // Clear any buffered output (notices, warnings) that would break JSON
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Validate Saudi/GCC phone number format.
 * Accepts: 0512345678 / 05 123 45678 / +966512345678 / 00966512345678
 * Normalizes before checking.
 */
function validate_phone(string $phone): bool {
    // Remove spaces, dashes, parentheses
    $clean = preg_replace('/[\s\-()]+/', '', $phone);
    // Convert +966 or 00966 prefix to leading 0
    $clean = preg_replace('/^(\+966|00966)/', '0', $clean);
    return (bool) preg_match('/^05[0-9]{8}$/', $clean);
}

/**
 * Validate email format
 */
function validate_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
