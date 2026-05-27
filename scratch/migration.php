<?php
require_once dirname(__FILE__) . '/../config/database.php';

echo "=== MIGRATION START ===\n";

// 1. Alter table warta
$check_col = $conn->query("SHOW COLUMNS FROM warta LIKE 'butuh_pendaftaran'");
if ($check_col->num_rows == 0) {
    $alter_sql = "ALTER TABLE warta ADD COLUMN butuh_pendaftaran TINYINT(1) DEFAULT 0";
    if ($conn->query($alter_sql)) {
        echo "Successfully added 'butuh_pendaftaran' column to 'warta' table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'butuh_pendaftaran' already exists in 'warta' table.\n";
}

// 2. Create table pendaftaran_warta
$create_table_sql = "
CREATE TABLE IF NOT EXISTS pendaftaran_warta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warta_id INT NOT NULL,
    user_id INT NOT NULL,
    nama_peserta VARCHAR(100) NOT NULL,
    email_peserta VARCHAR(100) NOT NULL,
    whatsapp_peserta VARCHAR(20) NOT NULL,
    metode_pembayaran ENUM('Tunai', 'Non Tunai') NOT NULL,
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    catatan TEXT DEFAULT NULL,
    status_pembayaran ENUM('Menunggu Verifikasi', 'Lunas', 'Ditolak', 'Bayar di Tempat') NOT NULL DEFAULT 'Menunggu Verifikasi',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warta_id) REFERENCES warta(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($create_table_sql)) {
    echo "Successfully created or verified 'pendaftaran_warta' table.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

echo "=== MIGRATION END ===\n";
?>
