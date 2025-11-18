<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$page_title = 'Ambil Mata Kuliah (KRS)';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

// Proses Ambil Mata Kuliah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ambil_mk'])) {
    $kode_mk = $_POST['kode_mk'];
    $semester = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    
    try {
        // Cek apakah sudah mengambil mata kuliah ini
        $stmt = $pdo->prepare("
            SELECT * FROM kelas 
            WHERE nim = ? AND kode_mk = ? AND semester = ? AND tahun_ajaran = ?
        ");
        $stmt->execute([$nim, $kode_mk, $semester, $tahun_ajaran]);
        
        if ($stmt->rowCount() > 0) {
            setAlert('danger', 'Anda sudah mengambil mata kuliah ini!');
        } else {
            // Insert ke tabel kelas
            $stmt = $pdo->prepare("
                INSERT INTO kelas (kode_mk, nim, semester, tahun_ajaran, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir, grade)
                VALUES (?, ?, ?, ?, 0, 0, 0, 0, '-')
            ");
            $stmt->execute([$kode_mk, $nim, $semester, $tahun_ajaran]);
            
            setAlert('success', 'Mata kuliah berhasil ditambahkan ke KRS!');
        }
        header("Location: krs.php");
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengambil mata kuliah!');
    }
}

// Proses Hapus Mata Kuliah dari KRS
if (isset($_GET['hapus'])) {
    $kelas_id = $_GET['hapus'];
    
    try {
        // Cek apakah sudah ada nilai
        $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ? AND nim = ?");
        $stmt->execute([$kelas_id, $nim]);
        $kelas = $stmt->fetch();
        
        if ($kelas['nilai_akhir'] > 0) {
            setAlert('danger', 'Tidak dapat menghapus! Mata kuliah ini sudah ada nilai.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ? AND nim = ?");
            $stmt->execute([$kelas_id, $nim]);
            setAlert('success', 'Mata kuliah berhasil dihapus dari KRS!');
        }
        header("Location: krs.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menghapus mata kuliah!');
    }
}

// Get semester dan tahun ajaran aktif (bisa dari setting, untuk contoh hardcode)
$semester_aktif = isset($_GET['semester']) ? $_GET['semester'] : 'Ganjil';
$tahun_ajaran_aktif = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y') . '/' . (date('Y') + 1);

include '../includes/header.php';
?>

<div class="card">
    <h2>📚 Kartu Rencana Studi (KRS)</h2>
    <p>Pilih mata kuliah yang akan diambil semester ini</p>
</div>

<!-- Info Periode KRS -->
<div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="color: white; margin-bottom: 10px;">Periode KRS Aktif</h3>
            <p style="margin: 5px 0;"><strong>Semester:</strong> <?php echo $semester_aktif; ?></p>
            <p style="margin: 5px 0;"><strong>Tahun Ajaran:</strong> <?php echo $tahun_ajaran_aktif; ?></p>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 48px;">📝</div>
        </div>
    </div>
</div>

<!-- Statistik KRS -->
<?php
// Hitung total SKS yang sudah diambil
$stmt = $pdo->prepare("
    SELECT SUM(mk.sks) as total_sks, COUNT(*) as total_mk
    FROM kelas k
    JOIN mata_kuliah mk ON k.kode_mk = mk.kode_mk
    WHERE k.nim = ? AND k.semester = ? AND k.tahun_ajaran = ?
");
$stmt->execute([$nim, $semester_aktif, $tahun_ajaran_aktif]);
$krs_stats = $stmt->fetch();
$total_sks = $krs_stats['total_sks'] ?? 0;
$total_mk = $krs_stats['total_mk'] ?? 0;

$max_sks = 24; // Batas maksimal SKS
$sisa_sks = $max_sks - $total_sks;
?>

<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
    <div class="stat-card">
        <h3>Total SKS Diambil</h3>
        <div class="number"><?php echo $total_sks; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #ffc107;">
        <h3>Sisa SKS</h3>
        <div class="number" style="color: #ffc107;"><?php echo $sisa_sks; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #28a745;">
        <h3>Total Mata Kuliah</h3>
        <div class="number" style="color: #28a745;"><?php echo $total_mk; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #dc3545;">
        <h3>Batas Maksimal</h3>
        <div class="number" style="color: #dc3545;"><?php echo $max_sks; ?> SKS</div>
    </div>
</div>

<!-- Progress Bar SKS -->
<div class="card">
    <h3>Progress Pengambilan SKS</h3>
    <div style="display: flex; align-items: center; gap: 20px; margin-top: 15px;">
        <div style="flex: 1; background: #f5f5f5; border-radius: 10px; height: 30px; overflow: hidden;">
            <div style="background: <?php echo $total_sks >= $max_sks ? '#dc3545' : '#667eea'; ?>; 
                        height: 100%; width: <?php echo ($total_sks / $max_sks) * 100; ?>%; 
                        transition: width 0.5s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                <?php if ($total_sks > 0): ?>
                    <?php echo $total_sks; ?> / <?php echo $max_sks; ?> SKS
                <?php endif; ?>
            </div>
        </div>
        <div style="font-size: 20px; font-weight: bold; color: <?php echo $total_sks >= $max_sks ? '#dc3545' : '#667eea'; ?>;">
            <?php echo number_format(($total_sks / $max_sks) * 100, 1); ?>%
        </div>
    </div>
</div>

<!-- Mata Kuliah yang Sudah Diambil -->
<div class="table-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3>Mata Kuliah yang Sudah Diambil</h3>
        <button onclick="document.getElementById('modalTambahMK').style.display='block'" 
                class="btn btn-primary" 
                <?php echo ($total_sks >= $max_sks) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>
            ➕ Tambah Mata Kuliah
        </button>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Semester MK</th>
                <th>Dosen</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT k.*, mk.nama_mk, mk.sks, mk.semester as semester_mk, d.nama as nama_dosen
                FROM kelas k
                JOIN mata_kuliah mk ON k.kode_mk = mk.kode_mk
                LEFT JOIN dosen d ON mk.dosen_nip = d.nip
                WHERE k.nim = ? AND k.semester = ? AND k.tahun_ajaran = ?
                ORDER BY mk.semester, mk.kode_mk
            ");
            $stmt->execute([$nim, $semester_aktif, $tahun_ajaran_aktif]);
            
            $no = 1;
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $has_nilai = $row['nilai_akhir'] > 0;
                    $status = $has_nilai ? 
                        '<span class="badge badge-info">Sudah Ada Nilai</span>' : 
                        '<span class="badge badge-warning">Belum Ada Nilai</span>';
                    
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td><strong>{$row['kode_mk']}</strong></td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['sks']}</td>";
                    echo "<td>Semester {$row['semester_mk']}</td>";
                    echo "<td>" . ($row['nama_dosen'] ?? '-') . "</td>";
                    echo "<td>{$status}</td>";
                    echo "<td>";
                    
                    if (!$has_nilai) {
                        echo "<a href='?hapus={$row['id']}' class='btn btn-danger' onclick='return confirm(\"Yakin hapus mata kuliah ini dari KRS?\")'>Hapus</a>";
                    } else {
                        echo "<span style='color: #999; font-size: 12px;'>Tidak dapat dihapus</span>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Belum ada mata kuliah yang diambil</td></tr>";
            }
            ?>
        </tbody>
        <?php if ($total_mk > 0): ?>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3">TOTAL</td>
                <td><?php echo $total_sks; ?> SKS</td>
                <td colspan="4"><?php echo $total_mk; ?> Mata Kuliah</td>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

<!-- Modal Tambah Mata Kuliah -->
<div id="modalTambahMK" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); overflow-y: auto;">
    <div style="background: white; margin: 3% auto; padding: 30px; width: 90%; max-width: 900px; border-radius: 10px; max-height: 85vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Pilih Mata Kuliah</h3>
            <button onclick="document.getElementById('modalTambahMK').style.display='none'" 
                    style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">
                ✕
            </button>
        </div>
        
        <!-- Filter Semester -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Filter Semester Mata Kuliah:</label>
            <select id="filterSemester" onchange="filterMataKuliah()" style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 200px;">
                <option value="">Semua Semester</option>
                <?php for($i=1; $i<=8; $i++): ?>
                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <table id="tableMataKuliah">
            <thead>
                <tr>
                    <th>Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Semester</th>
                    <th>Dosen</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get mata kuliah yang belum diambil
                $stmt = $pdo->prepare("
                    SELECT mk.*, d.nama as nama_dosen,
                           CASE 
                               WHEN k.kode_mk IS NOT NULL THEN 1 
                               ELSE 0 
                           END as sudah_diambil
                    FROM mata_kuliah mk
                    LEFT JOIN dosen d ON mk.dosen_nip = d.nip
                    LEFT JOIN kelas k ON mk.kode_mk = k.kode_mk 
                        AND k.nim = ? 
                        AND k.semester = ? 
                        AND k.tahun_ajaran = ?
                    ORDER BY mk.semester, mk.kode_mk
                ");
                $stmt->execute([$nim, $semester_aktif, $tahun_ajaran_aktif]);
                
                while ($row = $stmt->fetch()) {
                    $sudah_diambil = $row['sudah_diambil'];
                    $disabled = ($sudah_diambil || $total_sks + $row['sks'] > $max_sks) ? 'disabled' : '';
                    $button_text = $sudah_diambil ? 'Sudah Diambil' : 'Ambil';
                    
                    if ($total_sks + $row['sks'] > $max_sks && !$sudah_diambil) {
                        $button_text = 'Melebihi Batas';
                    }
                    
                    $status_badge = $sudah_diambil ? 
                        '<span class="badge badge-success">Terdaftar</span>' : 
                        '<span class="badge badge-info">Tersedia</span>';
                    
                    echo "<tr data-semester='{$row['semester']}'>";
                    echo "<td><strong>{$row['kode_mk']}</strong></td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['sks']}</td>";
                    echo "<td>Semester {$row['semester']}</td>";
                    echo "<td>" . ($row['nama_dosen'] ?? '-') . "</td>";
                    echo "<td>{$status_badge}</td>";
                    echo "<td>";
                    
                    if (!$sudah_diambil && $total_sks + $row['sks'] <= $max_sks) {
                        echo "<form method='POST' style='display: inline;'>";
                        echo "<input type='hidden' name='kode_mk' value='{$row['kode_mk']}'>";
                        echo "<input type='hidden' name='semester' value='{$semester_aktif}'>";
                        echo "<input type='hidden' name='tahun_ajaran' value='{$tahun_ajaran_aktif}'>";
                        echo "<button type='submit' name='ambil_mk' class='btn btn-primary'>{$button_text}</button>";
                        echo "</form>";
                    } else {
                        echo "<button class='btn btn-danger' disabled style='opacity: 0.5; cursor: not-allowed;'>{$button_text}</button>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterMataKuliah() {
    const filter = document.getElementById('filterSemester').value;
    const rows = document.querySelectorAll('#tableMataKuliah tbody tr');
    
    rows.forEach(row => {
        const semester = row.getAttribute('data-semester');
        if (filter === '' || semester === filter) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('modalTambahMK');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<!-- Informasi Penting -->
<div class="card" style="background: #fff3cd; border-left: 4px solid #ffc107;">
    <h3 style="color: #856404; margin-bottom: 10px;">⚠️ Ketentuan Pengambilan Mata Kuliah</h3>
    <ul style="margin-left: 20px; color: #856404;">
        <li>Batas maksimal pengambilan SKS: <strong><?php echo $max_sks; ?> SKS</strong> per semester</li>
        <li>Mata kuliah yang sudah memiliki nilai tidak dapat dihapus dari KRS</li>
        <li>Pastikan memilih mata kuliah sesuai dengan semester yang sedang berjalan</li>
        <li>Konsultasikan dengan dosen pembimbing akademik jika ada kendala</li>
        <li>Periode KRS dibuka setiap awal semester (2 minggu pertama)</li>
    </ul>
</div>

<!-- Cetak KRS -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3>Cetak Kartu Rencana Studi</h3>
            <p style="color: #666; margin-top: 5px;">Download KRS dalam format PDF</p>
        </div>
        <?php if ($total_mk > 0): ?>
            <a href="cetak-krs.php?semester=<?php echo $semester_aktif; ?>&tahun=<?php echo $tahun_ajaran_aktif; ?>" 
               target="_blank" class="btn btn-success">
                🖨️ Cetak KRS
            </a>
        <?php else: ?>
            <button class="btn btn-success" disabled style="opacity: 0.5; cursor: not-allowed;">
                🖨️ Cetak KRS (Belum ada MK)
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>