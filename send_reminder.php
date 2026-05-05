<?php
require_once __DIR__ . '/config/database.php';

// Konfigurasi API WhatsApp (Contoh menggunakan Fonnte, bisa disesuaikan dengan API lain seperti Whapi, WAAPI, dll)
// Anda perlu mendaftar dan mendapatkan token dari provider API WhatsApp tersebut.
$api_token = 'YOUR_API_TOKEN_HERE'; 

// Waktu saat ini
$now = new DateTime();
// Waktu 24 jam ke depan (H-1)
$target_time = new DateTime();
$target_time->modify('+24 hours');

$now_str = $now->format('Y-m-d H:i:s');
$target_str = $target_time->format('Y-m-d H:i:s');

// Cari kegiatan yang waktunya mendekati H-1 (antara sekarang sampai 24 jam ke depan)
// dan status reminder belum terkirim (0) serta nomor WA tersedia
$sql = "SELECT * FROM kegiatan WHERE tanggal BETWEEN ? AND ? AND status_reminder = 0 AND nomor_wa IS NOT NULL AND nomor_wa != ''";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $now_str, $target_str);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_kegiatan = $row['id'];
        $nama_kegiatan = $row['nama_kegiatan'];
        $tanggal = date('d-m-Y H:i', strtotime($row['tanggal']));
        $tempat = $row['tempat'];
        $nomor_wa = $row['nomor_wa'];

        // Format pesan pengingat
        $pesan = "Shalom! \n\nIni adalah pengingat otomatis dari Naposo HKBP Duren Jaya.\n\nJangan lupa menghadiri kegiatan:\n";
        $pesan .= "*$nama_kegiatan*\n";
        $pesan .= "📅 Tanggal & Waktu: $tanggal\n";
        $pesan .= "📍 Tempat: $tempat\n\n";
        $pesan .= "Mari kita persiapkan hati dan diri untuk hadir tepat waktu. Tuhan Yesus memberkati!";

        // Kirim pesan via API WhatsApp (Contoh CURL ke Fonnte)
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $nomor_wa,
                'message' => $pesan,
                'countryCode' => '62', // Kode negara Indonesia
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $api_token" // Header untuk autentikasi API
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err . "\n";
        } else {
            // Jika tidak ada error (asumsi pengiriman sukses), update status_reminder jadi 1
            $update_sql = "UPDATE kegiatan SET status_reminder = 1 WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id_kegiatan);
            $update_stmt->execute();
            
            echo "Reminder berhasil terkirim ke $nomor_wa untuk kegiatan: $nama_kegiatan\n";
        }
    }
} else {
    echo "Tidak ada jadwal kegiatan yang perlu dikirim reminder saat ini.\n";
}
?>
