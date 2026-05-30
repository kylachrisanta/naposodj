<?php
$file = 'warta.php';
$content = file_get_contents($file);

// 1. Remove the POST processing block
$start_str = "// Handle Registration Submission\nif (\$_SERVER['REQUEST_METHOD'] == 'POST' && isset(\$_POST['register_event'])) {";
$end_str = "    header(\"Location: warta.php\");\n    exit();\n}";
$start_pos = strpos($content, $start_str);
if ($start_pos !== false) {
    $end_pos = strpos($content, $end_str, $start_pos);
    if ($end_pos !== false) {
        $content = substr($content, 0, $start_pos) . substr($content, $end_pos + strlen($end_str));
    }
}

// 2. Change the Daftar Kegiatan button
$old_btn = "<button onclick='openRegisterModal(<?= json_encode(\$row_warta) ?>)' class=\"btn-primary\" style=\"padding: 8px 20px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; text-decoration: none;\">\n                                <i class=\"fa-solid fa-user-plus\"></i> Daftar Kegiatan\n                            </button>";
$new_btn = "<a href=\"daftar_kegiatan.php?id=<?= \$row_warta['id'] ?>\" class=\"btn-primary\" style=\"padding: 8px 20px; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; text-decoration: none;\">\n                                <i class=\"fa-solid fa-user-plus\"></i> Daftar Kegiatan\n                            </a>";
$content = str_replace($old_btn, $new_btn, $content);

// 3. Remove the Modal Registration HTML
$modal_start = "<!-- Modal Registration Form -->";
$modal_end = "<!-- Global Emoji Picker -->";
$m_start_pos = strpos($content, $modal_start);
if ($m_start_pos !== false) {
    $m_end_pos = strpos($content, $modal_end, $m_start_pos);
    if ($m_end_pos !== false) {
        $content = substr($content, 0, $m_start_pos) . substr($content, $m_end_pos);
    }
}

// 4. Remove JS functions for modal
$js_start = "function openRegisterModal(warta) {";
$js_end = "function openLightbox(imageSrc) {";
$j_start_pos = strpos($content, $js_start);
if ($j_start_pos !== false) {
    $j_end_pos = strpos($content, $js_end, $j_start_pos);
    if ($j_end_pos !== false) {
        $content = substr($content, 0, $j_start_pos) . substr($content, $j_end_pos);
    }
}

// 5. Remove closeRegisterModal from Escape key event
$esc_str = "closeRegisterModal();\n        closeLightbox();";
$new_esc = "closeLightbox();";
$content = str_replace($esc_str, $new_esc, $content);

file_put_contents($file, $content);
echo "warta.php updated successfully.\n";
