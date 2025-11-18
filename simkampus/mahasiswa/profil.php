<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$page_title = 'Profil Saya';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

// Proses Update Profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $email = clean($_POST['email']);
    $no_hp = clean($_POST['no_hp']);
    
    try {
        $stmt = $pdo->prepare("UPDATE mahasiswa SET email = ?, no_hp = ? WHERE nim = ?");
        $stmt->execute([$email, $no_hp, $nim]);
        
        setAlert('success', 'Profil berhasil diupdate!');
        header("Location: profil.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengupdate profil!');
    }
}

// Proses Ganti Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = md5($_POST['password_lama']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    try {
        // Cek password lama
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user['password'] !== $password_lama) {
            setAlert('danger', 'Password lama salah!');
        } elseif ($password_baru !== $konfirmasi_password) {
            setAlert('danger', 'Password baru dan konfirmasi tidak sama!');
        } elseif (strlen($password_baru) < 6) {
            setAlert('danger', 'Password minimal 6 karakter!');
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([md5($password_baru), $_SESSION['user_id']]);
            
            setAlert('success', 'Password berhasil diubah!');
            header("Location: profil.php");
            exit();
        }
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengubah password!');
    }
}

// Get statistik
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kelas WHERE nim = ?");
$stmt->execute([$nim]);
$total_mk = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kegiatan WHERE nim = ? AND status = 'disetujui'");
$stmt->execute([$nim]);
$total_kegiatan = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(poin) as total FROM kegiatan WHERE nim = ? AND status = 'disetujui'");
$stmt->execute([$nim]);
$total_poin = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT AVG(nilai_akhir) as ipk FROM kelas WHERE nim = ?");
$stmt->execute([$nim]);
$ipk = $stmt->fetch()['ipk'] ?? 0;

include '../includes/header.php';
?>

<div class="card">
    <h2>👤 Profil Mahasiswa</h2>
    <p>Kelola informasi profil Anda</p>
</div>

<!-- Profil Card -->
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="display: flex; align-items: center; gap: 30px;">
        <div style="width: 120px; height: 120px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 48px;">
            👨‍🎓
        </div>
        <div style="flex: 1;">
            <h2 style="color: white; margin-bottom: 10px;"><?php echo $user_data['nama']; ?></h2>
            <p style="margin-bottom: 5px;"><strong>NIM:</strong> <?php echo $user_data['nim']; ?></p>
            <p style="margin-bottom: 5px;"><strong>Jurusan:</strong> <?php echo $user_data['jurusan']; ?></p>
            <p style="margin-bottom: 5px;"><strong>Angkatan:</strong> <?php echo $user_data['angkatan']; ?></p>
        </div>
    </div>
</div>

<!-- Statistik Profil -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>IPK</h3>
        <div class="number"><?php echo hitungIPK($ipk); ?></div>
    </div>
    <div class="stat-card">
        <h3>Mata Kuliah</h3>
        <div class="number"><?php echo $total_mk; ?></div>
    </div>
    <div class="stat-card">
        <h3>Kegiatan</h3>
        <div class="number"><?php echo $total_kegiatan; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Poin</h3>
        <div class="number"><?php echo $total_poin; ?></div>
    </div>
</div>

<!-- Form Update Profil -->
<div class="card">
    <h3>Edit Profil</h3>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>NIM</label>
                <input type="text" value="<?php echo $user_data['nim']; ?>" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" value="<?php echo $user_data['nama']; ?>" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Jurusan</label>
                <input type="text" value="<?php echo $user_data['jurusan']; ?>" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Angkatan</label>
                <input type="text" value="<?php echo $user_data['angkatan']; ?>" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $user_data['email']; ?>">
            </div>
            
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="no_hp" value="<?php echo $user_data['no_hp']; ?>">
            </div>
        </div>
        
        <button type="submit" name="update_profil" class="btn btn-primary">
            Simpan Perubahan
        </button>
    </form>
</div>

<!-- Form Ganti Password -->
<div class="card">
    <h3>🔒 Ganti Password</h3>
    <form method="POST">
        <div class="form-group">
            <label>Password Lama <span style="color: red;">*</span></label>
            <input type="password" name="password_lama" required>
        </div>
        
        <div class="form-group">
            <label>Password Baru <span style="color: red;">*</span></label>
            <input type="password" name="password_baru" required>
            <small style="color: #666;">Minimal 6 karakter</small>
        </div>
        
        <div class="form-group">
            <label>Konfirmasi Password Baru <span style="color: red;">*</span></label>
            <input type="password" name="konfirmasi_password" required>
        </div>
        
        <button type="submit" name="ganti_password" class="btn btn-warning">
            Ganti Password
        </button>
    </form>
</div>

<!-- Riwayat Akademik -->
<div class="card">
    <h3>📊 Ringkasan Akademik</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
        <!-- Nilai Terbaik -->
        <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #28a745;">
            <h4 style="color: #28a745; margin-bottom: 10px;">🌟 Nilai Terbaik</h4>
            <?php
            $stmt = $pdo->prepare("
                SELECT mk.nama_mk, k.nilai_akhir, k.grade
                FROM kelas k
                JOIN mata_kuliah mk ON k.kode_mk = mk.kode_mk
                WHERE k.nim = ?
                ORDER BY k.nilai_akhir DESC
                LIMIT 3
            ");
            $stmt->execute([$nim]);
            
            while ($row = $stmt->fetch()) {
                echo "<p style='margin-bottom: 5px;'>";
                echo "<strong>{$row['nama_mk']}</strong>: {$row['nilai_akhir']} ({$row['grade']})";
                echo "</p>";
            }
            ?>
        </div>
        
        <!-- Kegiatan Terakhir -->
        <div style="padding: 20px; background: #f9f9f9; border-radius: 8px; border-left: 4px solid #667eea;">
            <h4 style="color: #667eea; margin-bottom: 10px;">🎯 Kegiatan Terakhir</h4>
            <?php
            $stmt = $pdo->prepare("
                SELECT nama_kegiatan, tanggal_mulai, poin, status
                FROM kegiatan
                WHERE nim = ?
                ORDER BY tanggal_mulai DESC
                LIMIT 3
            ");
            $stmt->execute([$nim]);
            
            while ($row = $stmt->fetch()) {
                $status_icon = $row['status'] == 'disetujui' ? '✅' : 
                              ($row['status'] == 'ditolak' ? '❌' : '⏳');
                echo "<p style='margin-bottom: 5px;'>";
                echo "{$status_icon} <strong>{$row['nama_kegiatan']}</strong> ({$row['poin']} poin)";
                echo "</p>";
            }
            ?>
        </div>
    </div>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>