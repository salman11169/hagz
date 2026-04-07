<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}

define('ROLE_ADMIN', 1);
define('ROLE_DOCTOR', 2);
define('ROLE_PATIENT', 3);
define('ROLE_RECEPTIONIST', 4);


function is_logged_in(): bool
{

    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin(): bool
{

    return is_logged_in() && ($_SESSION['role_id'] ?? 0) === ROLE_ADMIN;
}

function is_doctor(): bool
{

    return is_logged_in() && ($_SESSION['role_id'] ?? 0) === ROLE_DOCTOR;
}

function is_patient(): bool
{

    return is_logged_in() && ($_SESSION['role_id'] ?? 0) === ROLE_PATIENT;
}

function require_role(int $role): void
{

    if (!is_logged_in()) {
        header('Location: /Hagz/auth/login.php?error=يرجى_تسجيل_الدخول_أولاً');

        exit();
    }


    if (($_SESSION['role_id'] ?? 0) !== $role) {

        header('Location: /Hagz/public/403.html');
        exit();
    }
}


function logout(): void
{
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }

    session_destroy();

    header('Location: /Hagz/auth/login.php');
    exit();
}


function get_unread_notifications_count(): int
{
    if (!is_logged_in())
        return 0;

    try {
        require_once __DIR__ . '/../config/database.php';
        $pdo = getDB();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE user_id = ? AND is_read = 0");

        $stmt->execute([$_SESSION['user_id']]);

        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
