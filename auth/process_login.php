<?php
require_once '../includes/auth_middleware.php';
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Cari user berdasarkan email
    $sql = "SELECT id, nama, password, role FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verifikasi password hash
        if (password_verify($password, $row['password'])) {
            // Password benar, regenerasi session ID untuk keamanan
            session_regenerate_id(true);
            
            // Set session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = $row['role'];

            // Cek role untuk redirect
            if ($row['role'] == 'admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../index.php");
            }
            exit();
        } else {
            $_SESSION['flash_error'] = "Password yang Anda masukkan salah.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['flash_error'] = "Email tidak ditemukan. Silakan daftar terlebih dahulu.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
