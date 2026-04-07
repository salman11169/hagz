<?php
require_once __DIR__ . '/includes/session.php';
if (is_logged_in()) logout();
// Already logged out, redirect to login
header('Location: /Hagz/auth/login.php');
exit();
