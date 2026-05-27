<?php
require_once dirname(__FILE__) . '/../config/database.php';

echo "=== MIGRATION START ===\n";

// Alter table warta to add 'gambar' column
$check_col = $conn->query("SHOW COLUMNS FROM warta LIKE 'gambar'");
if ($check_col->num_rows == 0) {
    $alter_sql = "ALTER TABLE warta ADD COLUMN gambar VARCHAR(255) DEFAULT NULL";
    if ($conn->query($alter_sql)) {
        echo "Successfully added 'gambar' column to 'warta' table.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'gambar' already exists in 'warta' table.\n";
}

echo "=== MIGRATION END ===\n";
?>
