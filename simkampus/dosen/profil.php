<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['dosen']);

$page_title = 'Profil Dosen';
$user_id = $_SESSION['user_id'];

// Ambil data dosen berdasarkan user_id
$stmt = $pdo->prepare("SELECT * FROM dosen WHERE user_id = ?");
$stmt->execute([$user_id]);
$dosen = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dosen) {
    die("Data dosen tidak ditemukan.");
}

$nip = $dosen['nip'];

include '../includes/header.php';

// =========================
// PROSES UPDATE PROFIL
// =========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $email = clean($_POST['email']);
    $no_hp = clean($_POST['no_hp']);

    try {
        $stmt = $pdo->prepare("UPDATE dosen SET email = ?, no_hp = ? WHERE nip = ?");
        $stmt->execute([$email, $no_hp, $nip]);

        setAlert('success', 'Profil berhasil diupdate!');
        header("Location: profil.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengupdate profil!');
    }
}

// =========================
// PROSES GANTI PASSWORD
// =========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = md5($_POST['password_lama']);
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    try {
        // Cek password lama
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['password'] !== $password_lama) {
            setAlert('danger', 'Password lama salah!');
        } elseif ($password_baru !== $konfirmasi_password) {
            setAlert('danger', 'Password baru dan konfirmasi tidak cocok!');
        } elseif (strlen($password_baru) < 6) {
            setAlert('danger', 'Password minimal 6 karakter!');
        } else {
            // Update password
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([md5($password_baru), $user_id]);

            setAlert('success', 'Password berhasil diubah!');
            header("Location: profil.php");
            exit();
        }
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengubah password!');
    }
}
?>

<div class="card">
    <h2>👨‍🏫 Profil Dosen</h2>
    <p>Informasi data pribadi Anda sebagai dosen.</p>
</div>

<div class="card mt-20">
    <table class="detail-table">
        <tr><th>NIP</th><td><?= htmlspecialchars($dosen['nip']) ?></td></tr>
        <tr><th>Nama Lengkap</th><td><?= htmlspecialchars($dosen['nama']) ?></td></tr>
        <tr><th>Jurusan</th><td><?= htmlspecialchars($dosen['jurusan']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($dosen['email']) ?></td></tr>
        <tr><th>No. HP</th><td><?= htmlspecialchars($dosen['no_hp']) ?></td></tr>
        <tr><th>User ID</th><td><?= htmlspecialchars($dosen['user_id']) ?></td></tr>
    </table>
</div>

<!-- FORM UPDATE PROFIL -->
<div class="card mt-20">
    <h3>✏️ Update Profil</h3>
    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($dosen['email']) ?>" required>

        <label>No. HP</label>
        <input type="text" name="no_hp" value="<?= htmlspecialchars($dosen['no_hp']) ?>">

        <button type="submit" name="update_profil" class="btn btn-primary mt-10">Update Profil</button>
    </form>
</div>

<!-- FORM GANTI PASSWORD -->
<div class="card mt-20">
    <h3>🔐 Ganti Password</h3>
    <form method="POST">
        <label>Password Lama</label>
        <input type="password" name="password_lama" required>

        <label>Password Baru</label>
        <input type="password" name="password_baru" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="konfirmasi_password" required>

        <button type="submit" name="ganti_password" class="btn btn-warning mt-10">Ubah Password</button>
    </form>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
