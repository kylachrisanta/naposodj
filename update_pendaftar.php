<?php
$file = 'admin/pendaftar.php';
$content = file_get_contents($file);

// 1. Valid statuses
$find1 = "\$valid_statuses = ['Menunggu Verifikasi', 'Lunas', 'Ditolak', 'Bayar di Tempat'];";
$rep1 = "\$valid_statuses = ['Menunggu Verifikasi', 'Lunas', 'Ditolak', 'Bayar di Tempat', 'Terdaftar'];";
$content = str_replace($find1, $rep1, $content);

// 2. Add Stats
$find2 = "'bayar_di_tempat' => 0,\n    'ditolak' => 0\n];";
$rep2 = "'bayar_di_tempat' => 0,\n    'ditolak' => 0,\n    'terdaftar' => 0\n];";
$content = str_replace($find2, $rep2, $content);

$find3 = "elseif (\$status == 'Ditolak') \$stats['ditolak'] = \$count;";
$rep3 = "elseif (\$status == 'Ditolak') \$stats['ditolak'] = \$count;\n    elseif (\$status == 'Terdaftar') \$stats['terdaftar'] = \$count;";
$content = str_replace($find3, $rep3, $content);

// 3. Stats UI
$find4 = "<div style=\"background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;\">\n        <span style=\"font-size: 1.8rem; font-weight: 700; color: #2563eb;\"><?= \$stats['bayar_di_tempat'] ?></span>\n        <span style=\"font-size: 0.85rem; color: #2563eb; font-weight: 600; margin-top: 5px; background: #dbeafe; padding: 2px 8px; border-radius: 9999px;\">Bayar di Tempat</span>\n    </div>\n</div>";
$rep4 = "<div style=\"background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;\">\n        <span style=\"font-size: 1.8rem; font-weight: 700; color: #2563eb;\"><?= \$stats['bayar_di_tempat'] ?></span>\n        <span style=\"font-size: 0.85rem; color: #2563eb; font-weight: 600; margin-top: 5px; background: #dbeafe; padding: 2px 8px; border-radius: 9999px;\">Bayar di Tempat</span>\n    </div>\n    <div style=\"background: white; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;\">\n        <span style=\"font-size: 1.8rem; font-weight: 700; color: #4f46e5;\"><?= \$stats['terdaftar'] ?></span>\n        <span style=\"font-size: 0.85rem; color: #4f46e5; font-weight: 600; margin-top: 5px; background: #e0e7ff; padding: 2px 8px; border-radius: 9999px;\">Gratis (Terdaftar)</span>\n    </div>\n</div>";
$content = str_replace($find4, $rep4, $content);

// 4. Badge colors
$find5 = "} elseif (\$row['status_pembayaran'] == 'Ditolak') {\n                                \$badge_color = '#dc2626'; \$badge_bg = '#fee2e2';\n                            }";
$rep5 = "} elseif (\$row['status_pembayaran'] == 'Ditolak') {\n                                \$badge_color = '#dc2626'; \$badge_bg = '#fee2e2';\n                            } elseif (\$row['status_pembayaran'] == 'Terdaftar') {\n                                \$badge_color = '#4f46e5'; \$badge_bg = '#e0e7ff';\n                            }";
$content = str_replace($find5, $rep5, $content);

// 5. Select options
$find6 = "<option value=\"Ditolak\" style=\"color: #dc2626; background: white;\" <?= \$row['status_pembayaran'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>\n                            </select>";
$rep6 = "<option value=\"Ditolak\" style=\"color: #dc2626; background: white;\" <?= \$row['status_pembayaran'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>\n                                <option value=\"Terdaftar\" style=\"color: #4f46e5; background: white;\" <?= \$row['status_pembayaran'] == 'Terdaftar' ? 'selected' : '' ?>>Terdaftar (Gratis)</option>\n                            </select>";
$content = str_replace($find6, $rep6, $content);

file_put_contents($file, $content);
echo "done";
