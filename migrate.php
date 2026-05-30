<?php
require 'config/database.php';
$conn->query("ALTER TABLE warta ADD COLUMN biaya INT DEFAULT 0");
$conn->query("ALTER TABLE pendaftaran_warta MODIFY COLUMN status_pembayaran ENUM('Menunggu Verifikasi','Lunas','Ditolak','Bayar di Tempat','Terdaftar') DEFAULT 'Menunggu Verifikasi'");
echo "Migration done\n";
