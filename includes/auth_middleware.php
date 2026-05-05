<?php
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

/**
 * Check if user is logged in
 */
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /naposodj/auth/login.php");
        exit();
    }
}

/**
 * Check if user is an admin
 */
function check_admin() {
    check_auth();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /naposodj/index.php");
        exit();
    }
}

/**
 * CSRF Protection
 */
function get_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }
}

/**
 * Sanitize input
 */
function sanitize($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
