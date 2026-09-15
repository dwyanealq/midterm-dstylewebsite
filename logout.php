<?php
require_once __DIR__ . '/includes/auth.php';

/* Remove every value stored in the current session. */
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

/* Destroy the server-side session. */
session_destroy();
header('Location: index.php');
exit;
