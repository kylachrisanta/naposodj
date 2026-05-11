<?php
require 'config/database.php';
$sql = "ALTER TABLE users ADD COLUMN wa_notification ENUM('aktif', 'nonaktif') DEFAULT 'aktif' AFTER whatsapp";
if ($conn->query($sql)) {
    echo "Successfully added wa_notification column";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
