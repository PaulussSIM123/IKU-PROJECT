<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['admin']);

$page_title = 'Data Dosen';

// 🟢 Tambah Dosen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $nip = clean($_POST['nip']);
    $nama = clean($_POST['nama']);
    $jurusan = clean($_POST['jurusan']);
    $email = clean($_POST['email']);
    $no_hp = clean($_POST['no_hp']);
    $username = clean($_POST['username']);
    $password = md5($_POST['password']);

    try {
        $pdo->beginTransaction();

        // Insert ke tabel users
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'dosen')");
        $stmt->execute([$username, $password]);
        $user_id = $pdo->lastInsertId();

        // Insert ke tabel dosen
        $stmt = $pdo->prepare("
            INSERT INTO dosen (nip, nama, jurusan, email, no_hp, user_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nip, $nama, $jurusan, $email, $no_hp, $user_id]);

        $pdo->commit();
        setAlert('success', 'Data dosen berhasil ditambahkan!');
        header("Location: dosen.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        setAlert('danger', 'Gagal menambahkan data: ' . $e->getMessage());
    }
}

// 🟠 Edit Dosen
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $nip = clean($_POST['nip']);
    $nama = clean($_POST['nama']);
    $jurusan = clean($_POST['jurusan']);
    $email = clean($_POST['email']);
    $no_hp = clean($_POST['no_hp']);

    try {
        $stmt = $pdo->prepare("UPDATE dosen SET nama=?, jurusan=?, email=?, no_hp=? WHERE nip=?");
        $stmt->execute([$nama, $jurusan, $email, $no_hp, $nip]);
        setAlert('success', 'Data dosen berhasil diperbarui!');
        header("Location: dosen.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal memperbarui data: ' . $e->getMessage());
    }
}

// 🔴 Hapus Dosen
if (isset($_GET['hapus'])) {
    $nip = $_GET['hapus'];

    try {
        $stmt = $pdo->prepare("DELETE FROM dosen WHERE nip = ?");
        $stmt->execute([$nip]);
        setAlert('success', 'Data dosen berhasil dihapus!');
        header("Location: dosen.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menghapus data!');
    }
}

include '../includes/header.php';
?>

<div class="card">
    <h2>👨‍🏫 Data Dosen</h2>
    <p>Kelola data dosen kampus</p>
</div>

<?php
// Jika ada parameter edit, tampilkan form edit
if (isset($_GET['edit'])) {
    $nip = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM dosen WHERE nip = ?");
    $stmt->execute([$nip]);
    $dosen = $stmt->fetch();
    if ($dosen):
?>
<div class="card">
    <h3>Edit Data Dosen</h3>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>NIP</label>
                <input type="text" name="nip" value="<?php echo htmlspecialchars($dosen['nip']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($dosen['nama']); ?>" required>
            </div>
            <div class="form-group">
                <label>Jurusan</label>
                <select name="jurusan" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <?php
                    $jurusanList = [
                        'Teknik Informatika', 'Sistem Informasi', 'Teknik Elektro',
                        'Teknik Mesin', 'Teknik Sipil', 'Manajemen', 'Akuntansi'
                    ];
                    foreach ($jurusanList as $j) {
                        $sel = ($j == $dosen['jurusan']) ? 'selected' : '';
                        echo "<option value='$j' $sel>$j</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($dosen['email']); ?>">
            </div>
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="no_hp" value="<?php echo htmlspecialchars($dosen['no_hp']); ?>">
            </div>
        </div>
        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
        <a href="dosen.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php
    else:
        echo "<div class='alert alert-warning'>Data dosen tidak ditemukan.</div>";
    endif;
}
?>

<!-- Form Tambah Dosen Baru -->
<?php if (!isset($_GET['edit'])): ?>
<div class="card">
    <h3>Tambah Dosen Baru</h3>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>NIP</label>
                <input type="text" name="nip" required>
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Jurusan</label>
                <select name="jurusan" required>
                    <option value="">-- Pilih Jurusan --</option>
                    <option value="Teknik Informatika">Teknik Informatika</option>
                    <option value="Sistem Informasi">Sistem Informasi</option>
                    <option value="Teknik Elektro">Teknik Elektro</option>
                    <option value="Teknik Mesin">Teknik Mesin</option>
                    <option value="Teknik Sipil">Teknik Sipil</option>
                    <option value="Manajemen">Manajemen</option>
                    <option value="Akuntansi">Akuntansi</option>
                </select>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="form-group">
                <label>No HP</label>
                <input type="text" name="no_hp">
            </div>
            <div class="form-group">
                <label>Username (Login)</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
        </div>
        <button type="submit" name="tambah" class="btn btn-primary">Tambah Dosen</button>
    </form>
</div>
<?php endif; ?>

<!-- Daftar Dosen -->
<div class="table-container">
    <h3>Daftar Dosen</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $stmt = $pdo->query("SELECT * FROM dosen ORDER BY nama");
        $no = 1;
        while ($row = $stmt->fetch()) {
            echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['nip']}</td>
                    <td>{$row['nama']}</td>
                    <td>{$row['jurusan']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['no_hp']}</td>
                    <td>
                        <a href='?edit={$row['nip']}' class='btn btn-warning btn-sm'>Edit</a>
                        <a href='?hapus={$row['nip']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin hapus data ini?\")'>Hapus</a>
                    </td>
                  </tr>";
            $no++;
        }
        ?>
        </tbody>
    </table>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
