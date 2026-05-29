<?php
require_once 'c:/xampp/htdocs/naposodj/config/database.php';

$sql = "CREATE TABLE IF NOT EXISTS warta_reaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warta_id INT NOT NULL,
    user_id INT NOT NULL,
    emoticon VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (warta_id, user_id),
    FOREIGN KEY (warta_id) REFERENCES warta(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Tabel warta_reaksi berhasil dibuat.";
} else {
    echo "Error membuat tabel: " . $conn->error;
}
?>
