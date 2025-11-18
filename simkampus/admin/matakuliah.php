<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['admin']);

$page_title = 'Data Mata Kuliah';

// Proses Tambah Mata Kuliah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $kode_mk = clean($_POST['kode_mk']);
    $nama_mk = clean($_POST['nama_mk']);
    $sks = $_POST['sks'];
    $semester = $_POST['semester'];
    $dosen_nip = !empty($_POST['dosen_nip']) ? $_POST['dosen_nip'] : null;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester, dosen_nip)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$kode_mk, $nama_mk, $sks, $semester, $dosen_nip]);
        
        setAlert('success', 'Mata kuliah berhasil ditambahkan!');
        header("Location: matakuliah.php");
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menambahkan mata kuliah: ' . $e->getMessage());
    }
}

// Proses Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $kode_mk = $_POST['kode_mk'];
    $nama_mk = clean($_POST['nama_mk']);
    $sks = $_POST['sks'];
    $semester = $_POST['semester'];
    $dosen_nip = !empty($_POST['dosen_nip']) ? $_POST['dosen_nip'] : null;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE mata_kuliah 
            SET nama_mk = ?, sks = ?, semester = ?, dosen_nip = ?
            WHERE kode_mk = ?
        ");
        $stmt->execute([$nama_mk, $sks, $semester, $dosen_nip, $kode_mk]);
        
        setAlert('success', 'Mata kuliah berhasil diupdate!');
        header("Location: matakuliah.php");
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengupdate mata kuliah!');
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $kode_mk = $_GET['hapus'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE kode_mk = ?");
        $stmt->execute([$kode_mk]);
        setAlert('success', 'Mata kuliah berhasil dihapus!');
        header("Location: matakuliah.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menghapus mata kuliah!');
    }
}

// Get data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE kode_mk = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

include '../includes/header.php';
?>

<div class="card">
    <h2>📚 Data Mata Kuliah</h2>
    <p>Kelola mata kuliah kampus</p>
</div>

<!-- Form Tambah/Edit Mata Kuliah -->
<div class="card">
    <h3><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Mata Kuliah</h3>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Kode Mata Kuliah <span style="color: red;">*</span></label>
                <input type="text" name="kode_mk" 
                       value="<?php echo $edit_data ? $edit_data['kode_mk'] : ''; ?>"
                       <?php echo $edit_data ? 'readonly style="background: #f5f5f5;"' : ''; ?>
                       required>
            </div>
            <div class="form-group">
                <label>Nama Mata Kuliah <span style="color: red;">*</span></label>
                <input type="text" name="nama_mk" 
                       value="<?php echo $edit_data ? $edit_data['nama_mk'] : ''; ?>"
                       required>
            </div>
            <div class="form-group">
                <label>SKS <span style="color: red;">*</span></label>
                <input type="number" name="sks" min="1" max="6"
                       value="<?php echo $edit_data ? $edit_data['sks'] : ''; ?>"
                       required>
            </div>
            <div class="form-group">
                <label>Semester <span style="color: red;">*</span></label>
                <select name="semester" required>
                    <option value="">-- Pilih Semester --</option>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <option value="<?php echo $i; ?>" 
                                <?php echo ($edit_data && $edit_data['semester'] == $i) ? 'selected' : ''; ?>>
                            Semester <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Dosen Pengampu</label>
                <select name="dosen_nip">
                    <option value="">-- Pilih Dosen --</option>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM dosen ORDER BY nama");
                    while ($dosen = $stmt->fetch()) {
                        $selected = ($edit_data && $edit_data['dosen_nip'] == $dosen['nip']) ? 'selected' : '';
                        echo "<option value='{$dosen['nip']}' {$selected}>{$dosen['nama']} - {$dosen['jurusan']}</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <?php if ($edit_data): ?>
            <button type="submit" name="update" class="btn btn-primary">Update Mata Kuliah</button>
            <a href="matakuliah.php" class="btn btn-danger">Batal</a>
        <?php else: ?>
            <button type="submit" name="tambah" class="btn btn-primary">Tambah Mata Kuliah</button>
        <?php endif; ?>
    </form>
</div>

<!-- Statistik -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <?php
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mata_kuliah");
    $total_mk = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mata_kuliah WHERE dosen_nip IS NOT NULL");
    $mk_ada_dosen = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(sks) as total FROM mata_kuliah");
    $total_sks = $stmt->fetch()['total'] ?? 0;
    ?>
    
    <div class="stat-card">
        <h3>Total Mata Kuliah</h3>
        <div class="number"><?php echo $total_mk; ?></div>
    </div>
    <div class="stat-card">
        <h3>Sudah Ada Dosen</h3>
        <div class="number"><?php echo $mk_ada_dosen; ?></div>
    </div>
    <div class="stat-card">
        <h3>Belum Ada Dosen</h3>
        <div class="number"><?php echo $total_mk - $mk_ada_dosen; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total SKS</h3>
        <div class="number"><?php echo $total_sks; ?></div>
    </div>
</div>

<!-- Tabel Data Mata Kuliah -->
<div class="table-container">
    <h3>Daftar Mata Kuliah</h3>
    
    <!-- Filter -->
    <form method="GET" style="margin-bottom: 15px; display: flex; gap: 10px;">
        <select name="semester" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <option value="">Semua Semester</option>
            <?php for($i=1; $i<=8; $i++): ?>
                <option value="<?php echo $i; ?>" 
                        <?php echo (isset($_GET['semester']) && $_GET['semester'] == $i) ? 'selected' : ''; ?>>
                    Semester <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>
        <input type="text" name="search" placeholder="Cari mata kuliah..." 
               value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
               style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Semester</th>
                <th>Dosen Pengampu</th>
                <th>Jumlah Mahasiswa</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $where = "1=1";
            $params = [];
            
            if (isset($_GET['semester']) && !empty($_GET['semester'])) {
                $where .= " AND mk.semester = ?";
                $params[] = $_GET['semester'];
            }
            
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $where .= " AND (mk.kode_mk LIKE ? OR mk.nama_mk LIKE ?)";
                $params[] = $search;
                $params[] = $search;
            }
            
            $stmt = $pdo->prepare("
                SELECT mk.*, d.nama as nama_dosen, COUNT(DISTINCT k.nim) as jml_mhs
                FROM mata_kuliah mk
                LEFT JOIN dosen d ON mk.dosen_nip = d.nip
                LEFT JOIN kelas k ON mk.kode_mk = k.kode_mk
                WHERE {$where}
                GROUP BY mk.kode_mk
                ORDER BY mk.semester, mk.kode_mk
            ");
            $stmt->execute($params);
            
            $no = 1;
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td><strong>{$row['kode_mk']}</strong></td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['sks']}</td>";
                    echo "<td>Semester {$row['semester']}</td>";
                    echo "<td>" . ($row['nama_dosen'] ?? '<span style="color: red;">Belum ada</span>') . "</td>";
                    echo "<td><span class='badge badge-info'>{$row['jml_mhs']} Mhs</span></td>";
                    echo "<td>";
                    echo "<a href='?edit={$row['kode_mk']}' class='btn btn-warning' style='margin-right: 5px;'>Edit</a>";
                    echo "<a href='?hapus={$row['kode_mk']}' class='btn btn-danger' onclick='return confirm(\"Yakin hapus mata kuliah ini?\")'>Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Tidak ada data mata kuliah</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>