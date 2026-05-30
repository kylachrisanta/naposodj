<?php
require 'config/database.php';
$res = $conn->query("DESCRIBE pendaftaran_warta");
echo "PENDAFTARAN WARTA:\n";
while($row = $res->fetch_assoc()) print_r($row);

$res2 = $conn->query("DESCRIBE warta");
echo "\nWARTA:\n";
while($row = $res2->fetch_assoc()) print_r($row);
