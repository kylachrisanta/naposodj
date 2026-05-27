<?php
require_once dirname(__FILE__) . '/../config/database.php';

echo "=== MIGRATION START ===\n";

// 1. Create table renungan
$create_renungan_sql = "
CREATE TABLE IF NOT EXISTS renungan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    ayat_alkitab VARCHAR(255) NOT NULL,
    isi_renungan TEXT NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    tanggal_posting DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    gambar VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($create_renungan_sql)) {
    echo "Successfully created or verified 'renungan' table.\n";
} else {
    echo "Error creating table 'renungan': " . $conn->error . "\n";
}

// 2. Create table komentar_renungan
$create_komentar_sql = "
CREATE TABLE IF NOT EXISTS komentar_renungan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    renungan_id INT NOT NULL,
    user_id INT NOT NULL,
    nama_komentator VARCHAR(100) NOT NULL,
    isi_komentar TEXT NOT NULL,
    status_moderasi ENUM('Disetujui', 'Menunggu', 'Ditolak') NOT NULL DEFAULT 'Disetujui',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (renungan_id) REFERENCES renungan(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($create_komentar_sql)) {
    echo "Successfully created or verified 'komentar_renungan' table.\n";
} else {
    echo "Error creating table 'komentar_renungan': " . $conn->error . "\n";
}

echo "=== MIGRATION END ===\n";
?>
