<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/whatsapp.php';

// Fungsi untuk mengirim pesan WhatsApp via Fonnte
function sendWhatsApp($target, $message) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => FONNTE_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $target,
            'message' => $message,
            'countryCode' => '62', // Default Indonesia
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: " . FONNTE_TOKEN
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    
    return $response;
}

// 1. Cari kegiatan H-1 (24 jam dari sekarang)
// Kita gunakan rentang waktu agar tidak terlewat jika cron job tidak tepat waktu
$now = date('Y-m-d H:i:s');
$h1_start = date('Y-m-d H:i:s', strtotime('+23 hours'));
$h1_end = date('Y-m-d H:i:s', strtotime('+25 hours'));

// Jika $target_id diset (dari panel admin), ambil kegiatan spesifik tersebut
// Jika tidak, jalankan pengecekan otomatis H-1
if (isset($target_id)) {
    $sql_kegiatan = "SELECT * FROM kegiatan WHERE id = $target_id";
} else {
    $sql_kegiatan = "SELECT * FROM kegiatan WHERE tanggal BETWEEN '$h1_start' AND '$h1_end'";
}

$res_kegiatan = $conn->query($sql_kegiatan);

if ($res_kegiatan->num_rows > 0) {
    while ($kegiatan = $res_kegiatan->fetch_assoc()) {
        $id_kegiatan = $kegiatan['id'];
        $nama_kegiatan = $kegiatan['nama_kegiatan'];
        $tgl_formatted = date('d-m-Y', strtotime($kegiatan['tanggal']));
        $jam_formatted = date('H:i', strtotime($kegiatan['tanggal']));
        $tempat = $kegiatan['tempat'];

        // 2. Ambil seluruh user yang mengaktifkan notifikasi
        $sql_users = "SELECT id, nama, whatsapp FROM users WHERE wa_notification = 'aktif' AND whatsapp IS NOT NULL AND whatsapp != ''";
        $res_users = $conn->query($sql_users);

        while ($user = $res_users->fetch_assoc()) {
            $id_user = $user['id'];
            $nomor_wa = $user['whatsapp'];
            $nama_user = $user['nama'];

            // 3. Cek apakah sudah pernah dikirim (log_notifikasi)
            $sql_check = "SELECT id_log FROM log_notifikasi WHERE id_user = $id_user AND id_kegiatan = $id_kegiatan";
            $res_check = $conn->query($sql_check);

            if ($res_check->num_rows == 0) {
                // Ambil pesan dasar (custom atau default)
                if (isset($custom_message) && !empty($custom_message)) {
                    $pesan = $custom_message;
                } else {
                    // Format Pesan Default
                    $pesan = "Halo *{nama}*,\n\nAda kegiatan seru nih di Naposo HKBP Duren Jaya!\n\n";
                    $pesan .= "📌 *Kegiatan:* $nama_kegiatan\n";
                    $pesan .= "📅 *Tanggal:* $tgl_formatted\n";
                    $pesan .= "⏰ *Waktu:* $jam_formatted WIB\n";
                    $pesan .= "📍 *Tempat:* $tempat\n\n";
                    $pesan .= "Jangan lupa hadir ya! Mari kita berkumpul dan bertumbuh bersama. Tuhan Yesus memberkati! 🙏✨";
                }

                // Ganti placeholder {nama} dengan nama user sebenarnya
                $pesan = str_replace('{nama}', $nama_user, $pesan);

                // 4. Kirim WA
                $response = sendWhatsApp($nomor_wa, $pesan);
                $res_json = json_decode($response, true);

                $status = ($res_json && isset($res_json['status']) && $res_json['status'] == true) ? 'sukses' : 'gagal';

                // 5. Simpan Log
                $stmt_log = $conn->prepare("INSERT INTO log_notifikasi (id_user, id_kegiatan, status) VALUES (?, ?, ?)");
                $stmt_log->bind_param("iis", $id_user, $id_kegiatan, $status);
                $stmt_log->execute();
                
                echo "Sent to $nomor_wa ($nama_user) for activity $nama_kegiatan. Status: $status\n";
            }
        }
    }
} else {
    echo "Tidak ada kegiatan H-1 saat ini.\n";
}
?>
