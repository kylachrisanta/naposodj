<?php
require_once dirname(__FILE__) . '/../config/database.php';

echo "=== MIGRATION USER DEMOGRAPHICS START ===\n";

// 1. Alter table users - add nama_panggilan
$check_col_panggilan = $conn->query("SHOW COLUMNS FROM users LIKE 'nama_panggilan'");
if ($check_col_panggilan->num_rows == 0) {
    $alter_sql = "ALTER TABLE users ADD COLUMN nama_panggilan VARCHAR(50) NULL AFTER nama";
    if ($conn->query($alter_sql)) {
        echo "Successfully added 'nama_panggilan' column to 'users' table.\n";
    } else {
        echo "Error adding 'nama_panggilan' column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'nama_panggilan' already exists in 'users' table.\n";
}

// 2. Alter table users - add jenis_kelamin
$check_col_kelamin = $conn->query("SHOW COLUMNS FROM users LIKE 'jenis_kelamin'");
if ($check_col_kelamin->num_rows == 0) {
    $alter_sql = "ALTER TABLE users ADD COLUMN jenis_kelamin ENUM('Laki-laki', 'Perempuan') NULL AFTER nama_panggilan";
    if ($conn->query($alter_sql)) {
        echo "Successfully added 'jenis_kelamin' column to 'users' table.\n";
    } else {
        echo "Error adding 'jenis_kelamin' column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'jenis_kelamin' already exists in 'users' table.\n";
}

echo "=== MIGRATION USER DEMOGRAPHICS END ===\n";
?>
