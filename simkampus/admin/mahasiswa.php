<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['admin']);

$page_title = 'Data Mahasiswa';

/* ============================================================
   ✅ PROSES TAMBAH MAHASISWA
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $nim = clean($_POST['nim']);
    $nama = clean($_POST['nama']);
    $jurusan = clean($_POST['jurusan']);
    $angkatan = clean($_POST['angkatan']);
    $email = clean($_POST['email']);
    $no_hp = clean($_POST['no_hp']);
    $username = clean($_POST['username']);
    $password = md5($_POST['password']);

    try {
        $pdo->beginTransaction();

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'mahasiswa')");
        $stmt->execute([$username, $password]);
        $user_id = $pdo->lastInsertId();

        // Insert mahasiswa
        $stmt = $pdo->prepare("
            INSERT INTO mahasiswa (nim, nama, jurusan, angkatan, email, no_hp, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nim, $nama, $jurusan, $angkatan, $email, $no_hp, $user_id]);

        $pdo->commit();
        setAlert('success', 'Data mahasiswa berhasil ditambahkan!');
        header("Location: mahasiswa.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        setAlert('danger', 'Gagal menambahkan data: ' . $e->getMessage());
    }
}

/* ============================================================
   ✅ PROSES EDIT — AMBIL DATA
   ============================================================ */
$edit_mode = false;
$edit_data = null;

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $nim = $_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT m.*, u.username, u.id AS uid
        FROM mahasiswa m
        LEFT JOIN users u ON m.user_id = u.id
        WHERE m.nim = ?
    ");
    $stmt->execute([$nim]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$edit_data) {
        setAlert('danger', 'Data mahasiswa tidak ditemukan.');
        header("Location: mahasiswa.php");
        exit();
    }

    // Jika user_id kosong → buat otomatis
    if (empty($edit_data['user_id']) || $edit_data['uid'] == null) {
        $username_auto = strtolower("mhs_" . $edit_data['nim']);
        $pass_auto = md5($edit_data['nim']);

        $stmtU = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'mahasiswa')");
        $stmtU->execute([$username_auto, $pass_auto]);

        $new_uid = $pdo->lastInsertId();

        $stmtUp = $pdo->prepare("UPDATE mahasiswa SET user_id = ? WHERE nim = ?");
        $stmtUp->execute([$new_uid, $nim]);

        header("Location: mahasiswa.php?edit=$nim");
        exit();
    }
}

/* ============================================================
   ✅ PROSES UPDATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {

    try {
        $stmt = $pdo->prepare("
            UPDATE mahasiswa 
            SET nama=?, jurusan=?, angkatan=?, email=?, no_hp=? 
            WHERE nim=?
        ");
        $stmt->execute([
            $_POST['nama'],
            $_POST['jurusan'],
            $_POST['angkatan'],
            $_POST['email'],
            $_POST['no_hp'],
            $_POST['nim_old']
        ]);

        setAlert('success', 'Data mahasiswa berhasil diperbarui!');
        header("Location: mahasiswa.php");
        exit();

    } catch (PDOException $e) {
        setAlert('danger', 'Gagal update data!');
    }
}

/* ============================================================
   ✅ PROSES HAPUS
   ============================================================ */
if (isset($_GET['hapus'])) {
    $nim = $_GET['hapus'];

    try {
        // hapus mahasiswa saja (tidak menghapus user)
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE nim = ?");
        $stmt->execute([$nim]);

        setAlert('success', 'Data mahasiswa berhasil dihapus!');
        header("Location: mahasiswa.php");
        exit();

    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menghapus data!');
    }
}

include '../includes/header.php';
?>


<!-- =========================== HEADER PAGE ============================== -->
<div class="card">
    <h2>👨‍🎓 Data Mahasiswa</h2>
    <p>Kelola data mahasiswa kampus</p>
</div>


<!-- =========================== FORM EDIT ============================== -->
<?php if ($edit_mode): ?>
<div class="card">
    <h3>✏ Edit Data Mahasiswa</h3>

    <form method="POST">
        <input type="hidden" name="nim_old" value="<?= $edit_data['nim'] ?>">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">

            <div>
                <label>NIM</label>
                <input type="text" value="<?= $edit_data['nim'] ?>" disabled>
            </div>

            <div>
                <label>Nama</label>
                <input type="text" name="nama" value="<?= $edit_data['nama'] ?>" required>
            </div>

            <div>
                <label>Jurusan</label>
                <input type="text" name="jurusan" value="<?= $edit_data['jurusan'] ?>" required>
            </div>

            <div>
                <label>Angkatan</label>
                <input type="number" name="angkatan" value="<?= $edit_data['angkatan'] ?>" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" value="<?= $edit_data['email'] ?>">
            </div>

            <div>
                <label>No HP</label>
                <input type="text" name="no_hp" value="<?= $edit_data['no_hp'] ?>">
            </div>

        </div>

        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
        <a href="mahasiswa.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
<?php endif; ?>


<!-- =========================== FORM TAMBAH ============================== -->
<?php if (!$edit_mode): ?>
<div class="card">
    <h3>Tambah Mahasiswa Baru</h3>

    <form method="POST">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            <div><label>NIM *</label><input type="text" name="nim" required></div>
            <div><label>Nama *</label><input type="text" name="nama" required></div>
            <div><label>Jurusan *</label><input type="text" name="jurusan" required></div>
            <div><label>Angkatan *</label><input type="number" name="angkatan" min="2000" max="2099" required></div>
            <div><label>Email</label><input type="email" name="email"></div>
            <div><label>No HP</label><input type="text" name="no_hp"></div>
            <div><label>Username *</label><input type="text" name="username" required></div>
            <div><label>Password *</label><input type="password" name="password" required></div>
        </div>

        <button type="submit" name="tambah" class="btn btn-primary">Tambah Mahasiswa</button>
    </form>
</div>
<?php endif; ?>


<!-- =========================== TABEL MAHASISWA ============================== -->
<div class="table-container">
    <h3>Daftar Mahasiswa</h3>

    <!-- Search -->
    <form method="GET" style="margin-bottom:15px;">
        <input type="text" name="search" placeholder="Cari NIM atau Nama..." 
               value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>"
               style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Angkatan</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $where = "1=1";
            $params = [];

            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $where = "(nim LIKE ? OR nama LIKE ?)";
                $params = [$search, $search];
            }

            $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE $where ORDER BY nim DESC");
            $stmt->execute($params);

            $no = 1;

            while ($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nim'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['jurusan'] ?></td>
                <td><?= $row['angkatan'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['no_hp'] ?></td>
                <td>
                    <a href="?edit=<?= $row['nim'] ?>" class="btn btn-warning">Edit</a>
                    <a href="?hapus=<?= $row['nim'] ?>" class="btn btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<br>
<a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>

<?php include '../includes/footer.php'; ?>
