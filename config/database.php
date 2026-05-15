<?php
// Konfigurasi Database
$host = "localhost";        // Host server, default XAMPP adalah localhost
$user = "root";             // Username database, default XAMPP adalah root
$pass = "";                 // Password database, default XAMPP adalah kosong
$db   = "naposodj_db";      // Nama database yang kita buat di phpMyAdmin

// Membuat koneksi menggunakan objek mysqli
$conn = new mysqli($host, $user, $pass, $db);

// Memeriksa apakah koneksi berhasil atau error
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}

// Set zona waktu default ke Waktu Indonesia Barat (WIB)
date_default_timezone_set("Asia/Jakarta");

// Mengatur charset utf8mb4 agar mendukung karakter khusus
$conn->set_charset("utf8mb4");

// Mengatur timezone MySQL ke WIB agar fungsi date/time di SQL sinkron dengan PHP
$conn->query("SET time_zone = '+07:00'");

// Jika kamu butuh mengecek koneksi, bisa un-comment baris di bawah ini:
// echo "Koneksi Berhasil!";
?>
