<?php
session_start();

if (isset($_GET['role']) && $_GET['role'] == 'admin') {
    // Logout admin
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_nama']);
    unset($_SESSION['admin_role']);
    header("Location: ../auth/login.php");
} else {
    // Logout user biasa
    unset($_SESSION['user_id']);
    unset($_SESSION['user_nama']);
    unset($_SESSION['user_role']);
    header("Location: ../index.php");
}

// Jika setelah salah satu logout ternyata session benar-benar kosong (keduanya logout), hancurkan session
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

exit();
?>
