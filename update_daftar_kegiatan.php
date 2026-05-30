<?php
$file = 'daftar_kegiatan.php';
$content = file_get_contents($file);

// Update POST logic
$find_post = "\$metode_pembayaran = \$conn->real_escape_string(\$_POST['metode_pembayaran']);\n    \$catatan = \$conn->real_escape_string(\$_POST['catatan']);";
$rep_post = "\$biaya_kegiatan = (int)\$warta_info['biaya'];\n    \$metode_pembayaran = (\$biaya_kegiatan == 0) ? 'Tunai' : \$conn->real_escape_string(\$_POST['metode_pembayaran'] ?? 'Tunai');\n    \$catatan = \$conn->real_escape_string(\$_POST['catatan']);";
$content = str_replace($find_post, $rep_post, $content);

$find_db = "\$status_pembayaran = (\$metode_pembayaran == 'Tunai') ? 'Bayar di Tempat' : 'Menunggu Verifikasi';";
$rep_db = "\$status_pembayaran = (\$biaya_kegiatan == 0) ? 'Terdaftar' : ((\$metode_pembayaran == 'Tunai') ? 'Bayar di Tempat' : 'Menunggu Verifikasi');";
$content = str_replace($find_db, $rep_db, $content);

// Update header UI to show fee
$find_header = "<h3 style=\"font-size: 1.1rem; font-weight: 500; opacity: 0.9; margin: 0;\"><?= htmlspecialchars(\$warta_info['judul']) ?></h3>\n            </div>";
$rep_header = "<h3 style=\"font-size: 1.1rem; font-weight: 500; opacity: 0.9; margin: 0; margin-bottom: 15px;\"><?= htmlspecialchars(\$warta_info['judul']) ?></h3>\n                <div style=\"background: rgba(255,255,255,0.15); padding: 10px 15px; border-radius: 6px; display: inline-flex; align-items: center; gap: 10px;\">\n                    <i class=\"fa-solid fa-rupiah-sign\" style=\"font-size: 1.2rem;\"></i>\n                    <span style=\"font-weight: 600; font-size: 1rem;\">\n                        <?= (\$warta_info['biaya'] > 0) ? 'Biaya Pendaftaran: Rp ' . number_format(\$warta_info['biaya'], 0, ',', '.') : 'Gratis / Tidak Dipungut Biaya' ?>\n                    </span>\n                </div>\n            </div>";
$content = str_replace($find_header, $rep_header, $content);

// Wrap payment method in conditional
$find_payment = "<!-- Payment Method Selector -->\n                <div style=\"margin-bottom: 20px;\">\n                    <label style=\"display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);\">Metode Pembayaran</label>";
$rep_payment = "<?php if (\$warta_info['biaya'] > 0): ?>\n                <!-- Payment Method Selector -->\n                <div style=\"margin-bottom: 20px;\">\n                    <label style=\"display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-main);\">Metode Pembayaran</label>";
$content = str_replace($find_payment, $rep_payment, $content);

$find_qris_end = "</div>\n                </div>\n                \n                <!-- Additional Notes (Optional) -->";
$rep_qris_end = "</div>\n                </div>\n                <?php endif; ?>\n                \n                <!-- Additional Notes (Optional) -->";
$content = str_replace($find_qris_end, $rep_qris_end, $content);

file_put_contents($file, $content);
echo "done";
