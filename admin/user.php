<?php
require_once '../includes/admin_header.php';
require_once '../includes/admin_sidebar.php';

$message = "";
if (isset($_SESSION['admin_flash'])) {
    $message = $_SESSION['admin_flash'];
    unset($_SESSION['admin_flash']);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cegah hapus diri sendiri
    if($id == $_SESSION['admin_id']) {
        $_SESSION['admin_flash'] = "<div class='alert alert-danger'>Anda tidak bisa menghapus akun Anda sendiri!</div>";
    } else {
        if($conn->query("DELETE FROM users WHERE id=$id")) {
            $_SESSION['admin_flash'] = "<div class='alert alert-success'>Akun berhasil dihapus.</div>";
        }
    }
    header("Location: user.php");
    exit();
}

// Handle Update Role & Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $role = $conn->real_escape_string($_POST['role']);
    $new_password = $_POST['new_password'];
    
    if($new_password != "") {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET role='$role', password='$hashed' WHERE id=$id";
    } else {
        $sql = "UPDATE users SET role='$role' WHERE id=$id";
    }
    
    if($conn->query($sql)) {
        $_SESSION['admin_flash'] = "<div class='alert alert-success'>Data akun berhasil diperbarui.</div>";
    }
    header("Location: user.php");
    exit();
}

// Edit Mode
$edit_mode = false;
$edit_data = ['id' => '', 'nama' => '', 'email' => '', 'role' => 'pengunjung'];
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT id, nama, email, role FROM users WHERE id=$id");
    if ($res->num_rows > 0) {
        $edit_mode = true;
        $edit_data = $res->fetch_assoc();
    }
}
?>

<div style="margin-bottom: 30px;">
    <h2>Manajemen Akun</h2>
    <p style="color: var(--text-muted);">Kelola data anggota dan hak akses admin.</p>
</div>

<?= $message ?>

<?php if($edit_mode): ?>
<div style="background: white; padding: 25px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px;">Ubah Akun: <?= htmlspecialchars($edit_data['nama']) ?></h3>
    <form action="user.php" method="POST">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label>Email</label>
                <input type="text" value="<?= $edit_data['email'] ?>" disabled class="form-control" style="background: #f8fafc;">
            </div>
            <div>
                <label>Peran (Role)</label>
                <select name="role" class="form-control">
                    <option value="pengunjung" <?= $edit_data['role'] == 'pengunjung' ? 'selected' : '' ?>>Pengunjung (Member)</option>
                    <option value="admin" <?= $edit_data['role'] == 'admin' ? 'selected' : '' ?>>Administrator</option>
                </select>
            </div>
        </div>
        <div style="margin-bottom: 20px;">
            <label>Ganti Password (Kosongkan jika tidak ingin ganti)</label>
            <input type="password" name="new_password" class="form-control" placeholder="Masukkan password baru...">
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
            <a href="user.php" class="btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div style="background: white; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead style="background: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
            <tr>
                <th style="padding: 15px 20px;">Nama</th>
                <th style="padding: 15px 20px;">Email</th>
                <th style="padding: 15px 20px;">WhatsApp</th>
                <th style="padding: 15px 20px; text-align: center;">Wijk</th>
                <th style="padding: 15px 20px; text-align: center;">SIDI</th>
                <th style="padding: 15px 20px;">Peran</th>
                <th style="padding: 15px 20px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM users ORDER BY role ASC, nama ASC");
            $user_details = [];
            while($row = $result->fetch_assoc()):
                $user_details[$row['id']] = $row;
            ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 15px 20px; font-weight: 500;"><?= htmlspecialchars($row['nama']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row['email']) ?></td>
                <td style="padding: 15px 20px; color: var(--text-muted);"><?= htmlspecialchars($row['whatsapp'] ?? '-') ?></td>
                <td style="padding: 15px 20px; text-align: center; color: var(--text-muted);"><?= htmlspecialchars($row['wijk']) ?></td>
                <td style="padding: 15px 20px; text-align: center; color: var(--text-muted);"><?= htmlspecialchars($row['angkatan_sidi']) ?></td>
                <td style="padding: 15px 20px;">
                    <span class="badge" style="background: <?= ($row['role'] == 'admin' ? '#dcfce7' : '#f1f5f9') ?>; color: <?= ($row['role'] == 'admin' ? '#15803d' : '#475569') ?>;">
                        <?= strtoupper($row['role']) ?>
                    </span>
                </td>
                <td style="padding: 15px 20px;">
                    <button onclick='showDetail(<?= json_encode($row) ?>)' class="text-primary" style="margin-right: 15px; background: none; border: none; cursor: pointer; font-size: 1.1rem;" title="Lihat Detail Profil"><i class="fa-solid fa-address-card"></i></button>
                    <a href="user.php?edit=<?= $row['id'] ?>" class="text-primary" style="margin-right: 15px;" title="Edit Akun"><i class="fa-solid fa-user-gear"></i></a>
                    <?php if($row['id'] != $_SESSION['admin_id']): ?>
                        <a href="user.php?delete=<?= $row['id'] ?>" class="text-danger" onclick="return confirm('Hapus akun ini secara permanen?')" title="Hapus Akun"><i class="fa-solid fa-user-minus"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; backdrop-filter: blur(4px);">
    <div style="background: white; max-width: 500px; width: 90%; margin: 100px auto; border-radius: var(--radius-md); padding: 30px; position: relative; box-shadow: var(--shadow-lg);">
        <span onclick="closeModal()" style="position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</span>
        <h3 style="margin-bottom: 25px; border-bottom: 2px solid var(--bg-subtle); padding-bottom: 10px; color: var(--primary);">Detail Profil Naposo</h3>
        <div id="detailContent"></div>
    </div>
</div>

<script>
function showDetail(user) {
    const content = `
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Nama Lengkap:</strong>
                <span>${user.nama}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Tempat, Tgl Lahir:</strong>
                <span>${user.tempat_lahir}, ${user.tanggal_lahir}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Alamat:</strong>
                <span>${user.alamat}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Wijk / Sektor:</strong>
                <span>${user.wijk}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Angkatan SIDI:</strong>
                <span>${user.angkatan_sidi}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Email:</strong>
                <span>${user.email}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">WhatsApp:</strong>
                <span>${user.whatsapp || '-'}</span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Notifikasi WA:</strong>
                <span class="badge" style="background: ${user.wa_notification === 'aktif' ? '#dcfce7' : '#fee2e2'}; color: ${user.wa_notification === 'aktif' ? '#15803d' : '#b91c1c'};">
                    ${user.wa_notification === 'aktif' ? 'AKTIF' : 'NONAKTIF'}
                </span>
            </div>
            <div style="display: grid; grid-template-columns: 140px 1fr;">
                <strong style="color: var(--text-muted);">Status:</strong>
                <span style="text-transform: uppercase; font-weight: 600; color: var(--primary);">${user.role}</span>
            </div>
        </div>
        <div style="margin-top: 30px; text-align: center;">
            <button onclick="closeModal()" class="btn-primary" style="background: var(--text-muted); border: none;">Tutup</button>
        </div>
    `;
    document.getElementById('detailContent').innerHTML = content;
    document.getElementById('detailModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

// Close on escape
window.onkeydown = function(event) {
    if (event.key === "Escape") closeModal();
}
</script>

<?php require_once '../includes/admin_footer.php'; ?>
