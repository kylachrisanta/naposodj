<?php
$file = 'admin/kegiatan_warta.php';
$content = file_get_contents($file);

// 1. Capture biaya
$find1 = "\$butuh_pendaftaran = isset(\$_POST['butuh_pendaftaran']) ? 1 : 0;";
$rep1 = "\$butuh_pendaftaran = isset(\$_POST['butuh_pendaftaran']) ? 1 : 0;\n    \$biaya = \$butuh_pendaftaran ? (int)\$_POST['biaya'] : 0;";
$content = str_replace($find1, $rep1, $content);

// 2. Update queries
$find_upd1 = "UPDATE warta SET judul='\$judul', isi_pengumuman='\$isi_pengumuman', butuh_pendaftaran=\$butuh_pendaftaran, gambar='\$gambar_filename' WHERE id=\$id";
$rep_upd1 = "UPDATE warta SET judul='\$judul', isi_pengumuman='\$isi_pengumuman', butuh_pendaftaran=\$butuh_pendaftaran, biaya=\$biaya, gambar='\$gambar_filename' WHERE id=\$id";
$content = str_replace($find_upd1, $rep_upd1, $content);

$find_upd2 = "UPDATE warta SET judul='\$judul', isi_pengumuman='\$isi_pengumuman', butuh_pendaftaran=\$butuh_pendaftaran, gambar=NULL WHERE id=\$id";
$rep_upd2 = "UPDATE warta SET judul='\$judul', isi_pengumuman='\$isi_pengumuman', butuh_pendaftaran=\$butuh_pendaftaran, biaya=\$biaya, gambar=NULL WHERE id=\$id";
$content = str_replace($find_upd2, $rep_upd2, $content);

$find_upd3 = "UPDATE warta SET judul='\$judul', isi_pengumuman='\$isi_pengumuman', butuh_pendaftaran=\$butuh_pendaftaran WHERE id=\$id";
$rep_upd3 = "UPDATE warta SET judul='\$judul', isi_pengumuman='\$isi_pengumuman', butuh_pendaftaran=\$butuh_pendaftaran, biaya=\$biaya WHERE id=\$id";
$content = str_replace($find_upd3, $rep_upd3, $content);

$find_ins = "INSERT INTO warta (judul, isi_pengumuman, butuh_pendaftaran, gambar) VALUES ('\$judul', '\$isi_pengumuman', \$butuh_pendaftaran, \$gambar_val)";
$rep_ins = "INSERT INTO warta (judul, isi_pengumuman, butuh_pendaftaran, biaya, gambar) VALUES ('\$judul', '\$isi_pengumuman', \$butuh_pendaftaran, \$biaya, \$gambar_val)";
$content = str_replace($find_ins, $rep_ins, $content);

// 3. Update edit_warta_data
$find_arr = "'id' => '', 'judul' => '', 'isi_pengumuman' => '', 'butuh_pendaftaran' => 0, 'gambar' => NULL";
$rep_arr = "'id' => '', 'judul' => '', 'isi_pengumuman' => '', 'butuh_pendaftaran' => 0, 'biaya' => 0, 'gambar' => NULL";
$content = str_replace($find_arr, $rep_arr, $content);

// 4. Update UI: add biaya input
$find_ui = "            <div style=\"margin-bottom: 20px; display: flex; align-items: center; gap: 10px;\">
                <input type=\"checkbox\" name=\"butuh_pendaftaran\" id=\"butuh_pendaftaran\" value=\"1\" <?= (\$edit_warta_data['butuh_pendaftaran'] ?? 0) == 1 ? 'checked' : '' ?> style=\"width: 18px; height: 18px; cursor: pointer;\">
                <label for=\"butuh_pendaftaran\" style=\"font-weight: 500; cursor: pointer; color: var(--text-main);\">Butuh Pendaftaran Peserta</label>
            </div>";
$rep_ui = "            <div style=\"margin-bottom: 10px; display: flex; align-items: center; gap: 10px;\">
                <input type=\"checkbox\" name=\"butuh_pendaftaran\" id=\"butuh_pendaftaran\" onchange=\"toggleBiayaField()\" value=\"1\" <?= (\$edit_warta_data['butuh_pendaftaran'] ?? 0) == 1 ? 'checked' : '' ?> style=\"width: 18px; height: 18px; cursor: pointer;\">
                <label for=\"butuh_pendaftaran\" style=\"font-weight: 500; cursor: pointer; color: var(--text-main);\">Butuh Pendaftaran Peserta</label>
            </div>
            
            <div id=\"biaya_field\" style=\"margin-bottom: 20px; display: <?= (\$edit_warta_data['butuh_pendaftaran'] ?? 0) == 1 ? 'block' : 'none' ?>; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid var(--border-color);\">
                <label style=\"display: block; margin-bottom: 8px; font-weight: 500;\">Biaya Pendaftaran (Rp)</label>
                <div style=\"display: flex; align-items: center; gap: 10px;\">
                    <span style=\"font-weight: 600; color: var(--text-muted);\">Rp</span>
                    <input type=\"number\" name=\"biaya\" value=\"<?= htmlspecialchars(\$edit_warta_data['biaya'] ?? 0) ?>\" min=\"0\" style=\"width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;\">
                </div>
                <p style=\"font-size: 0.75rem; color: var(--text-muted); margin-top: 6px;\">Biarkan 0 atau kosongkan jika kegiatan ini gratis.</p>
            </div>";
$content = str_replace($find_ui, $rep_ui, $content);

// 5. Update JS
$find_js = "</script>";
$rep_js = "function toggleBiayaField() {
    const isChecked = document.getElementById('butuh_pendaftaran').checked;
    document.getElementById('biaya_field').style.display = isChecked ? 'block' : 'none';
}
</script>";
$content = str_replace($find_js, $rep_js, $content);

file_put_contents($file, $content);
echo "done";
