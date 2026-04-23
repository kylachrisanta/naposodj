<?php
session_start();

// Hancurkan semua data session
$_SESSION = array();

// Hancurkan cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Terakhir, hancurkan session
session_destroy();

// Arahkan kembali ke halaman index pengunjung
header("Location: ../index.php");
exit();
?>
