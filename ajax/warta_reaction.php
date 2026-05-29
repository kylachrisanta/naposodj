<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Cek autentikasi
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$user_id = (int)$_SESSION['user_id'];

// Ambil input JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['warta_id']) || !isset($input['emoticon'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$warta_id = (int)$input['warta_id'];
$emoticon = $input['emoticon'];

// Validasi sederhana: panjang karakter tidak boleh terlalu panjang (VARCHAR 10)
// mb_strlen di PHP menghitung karakter (bukan byte). Emoji biasa 1-2 karakter.
if (mb_strlen($emoticon, 'UTF-8') > 10) {
    echo json_encode(['success' => false, 'message' => 'Emoticon too long']);
    exit();
}

// Cek apakah warta ada
$warta_check = $conn->query("SELECT id FROM warta WHERE id = $warta_id");
if ($warta_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Warta not found']);
    exit();
}

// Cek reaksi yang sudah ada
$stmt = $conn->prepare("SELECT emoticon FROM warta_reaksi WHERE warta_id = ? AND user_id = ?");
$stmt->bind_param("ii", $warta_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$user_current_reaction = null;

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    if ($existing['emoticon'] === $emoticon) {
        // Toggle (hapus reaksi jika diklik ikon yang sama)
        $del_stmt = $conn->prepare("DELETE FROM warta_reaksi WHERE warta_id = ? AND user_id = ?");
        $del_stmt->bind_param("ii", $warta_id, $user_id);
        $del_stmt->execute();
        $user_current_reaction = null;
    } else {
        // Update reaksi
        $upd_stmt = $conn->prepare("UPDATE warta_reaksi SET emoticon = ? WHERE warta_id = ? AND user_id = ?");
        $upd_stmt->bind_param("sii", $emoticon, $warta_id, $user_id);
        $upd_stmt->execute();
        $user_current_reaction = $emoticon;
    }
} else {
    // Insert reaksi baru
    $ins_stmt = $conn->prepare("INSERT INTO warta_reaksi (warta_id, user_id, emoticon) VALUES (?, ?, ?)");
    $ins_stmt->bind_param("iis", $warta_id, $user_id, $emoticon);
    $ins_stmt->execute();
    $user_current_reaction = $emoticon;
}

// Ambil statistik reaksi terbaru
$stats_stmt = $conn->prepare("SELECT emoticon, COUNT(*) as total FROM warta_reaksi WHERE warta_id = ? GROUP BY emoticon");
$stats_stmt->bind_param("i", $warta_id);
$stats_stmt->execute();
$stats_res = $stats_stmt->get_result();

$stats = [];
while ($row = $stats_res->fetch_assoc()) {
    $stats[$row['emoticon']] = (int)$row['total'];
}

echo json_encode([
    'success' => true,
    'user_reaction' => $user_current_reaction,
    'stats' => $stats
]);
?>
