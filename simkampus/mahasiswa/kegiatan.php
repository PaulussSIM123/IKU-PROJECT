<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$page_title = 'Kegiatan Mahasiswa';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

// Proses tambah kegiatan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_kegiatan'])) {
    $nama_kegiatan = clean($_POST['nama_kegiatan']);
    $jenis_kegiatan = $_POST['jenis_kegiatan'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $deskripsi = clean($_POST['deskripsi']);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO kegiatan (nim, nama_kegiatan, jenis_kegiatan, tanggal_mulai, tanggal_selesai, deskripsi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nim, $nama_kegiatan, $jenis_kegiatan, $tanggal_mulai, $tanggal_selesai, $deskripsi]);
        
        setAlert('success', 'Kegiatan berhasil ditambahkan! Menunggu validasi.');
        header("Location: kegiatan.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menambahkan kegiatan!');
    }
}

include '../includes/header.php';
?>

<div class="card">
    <h2>🎯 Kegiatan Mahasiswa</h2>
    <p>Input dan monitoring kegiatan mahasiswa</p>
</div>

<!-- Form Tambah Kegiatan -->
<div class="card">
    <h3>Tambah Kegiatan Baru</h3>
    <form method="POST">
        <div class="form-group">
            <label>Nama Kegiatan <span style="color: red;">*</span></label>
            <input type="text" name="nama_kegiatan" required>
        </div>
        
        <div class="form-group">
            <label>Jenis Kegiatan <span style="color: red;">*</span></label>
            <select name="jenis_kegiatan" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="organisasi">Organisasi</option>
                <option value="lomba">Lomba</option>
                <option value="seminar">Seminar</option>
                <option value="workshop">Workshop</option>
                <option value="penelitian">Penelitian</option>
                <option value="pengabdian">Pengabdian Masyarakat</option>
                <option value="lainnya">Lainnya</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Tanggal Mulai <span style="color: red;">*</span></label>
                <input type="date" name="tanggal_mulai" required>
            </div>
            
            <div class="form-group">
                <label>Tanggal Selesai</label>
                <input type="date" name="tanggal_selesai">
            </div>
        </div>
        
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="4" placeholder="Jelaskan detail kegiatan..."></textarea>
        </div>
        
        <button type="submit" name="tambah_kegiatan" class="btn btn-primary">
            Tambah Kegiatan
        </button>
    </form>
</div>

<!-- Statistik Kegiatan -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <?php
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kegiatan WHERE nim = ? AND status = 'disetujui'");
    $stmt->execute([$nim]);
    $disetujui = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kegiatan WHERE nim = ? AND status = 'menunggu'");
    $stmt->execute([$nim]);
    $menunggu = $stmt->fetch()['total'];
    
    $stmt = $pdo->prepare("SELECT SUM(poin) as total FROM kegiatan WHERE nim = ? AND status = 'disetujui'");
    $stmt->execute([$nim]);
    $total_poin = $stmt->fetch()['total'] ?? 0;
    ?>
    
    <div class="stat-card">
        <h3>Kegiatan Disetujui</h3>
        <div class="number"><?php echo $disetujui; ?></div>
    </div>
    <div class="stat-card">
        <h3>Menunggu Validasi</h3>
        <div class="number"><?php echo $menunggu; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Poin</h3>
        <div class="number"><?php echo $total_poin; ?></div>
    </div>
</div>

<!-- Tabel Riwayat Kegiatan -->
<div class="table-container">
    <h3>Riwayat Kegiatan</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Kegiatan</th>
                <th>Jenis</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Poin</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT * FROM kegiatan 
                WHERE nim = ? 
                ORDER BY tanggal_mulai DESC
            ");
            $stmt->execute([$nim]);
            
            $no = 1;
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $badge_class = $row['status'] == 'disetujui' ? 'badge-success' : 
                                  ($row['status'] == 'ditolak' ? 'badge-danger' : 'badge-warning');
                    
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>{$row['nama_kegiatan']}</td>";
                    echo "<td>" . ucfirst($row['jenis_kegiatan']) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_mulai'])) . "</td>";
                    echo "<td>" . ($row['tanggal_selesai'] ? date('d/m/Y', strtotime($row['tanggal_selesai'])) : '-') . "</td>";
                    echo "<td><strong>{$row['poin']}</strong></td>";
                    echo "<td><span class='badge {$badge_class}'>" . ucfirst($row['status']) . "</span></td>";
                    echo "</tr>";
                    
                    // Tampilkan deskripsi jika ada
                    if (!empty($row['deskripsi'])) {
                        echo "<tr style='background: #f9f9f9;'>";
                        echo "<td></td>";
                        echo "<td colspan='6' style='font-size: 12px; color: #666;'>";
                        echo "<strong>Deskripsi:</strong> " . nl2br($row['deskripsi']);
                        echo "</td>";
                        echo "</tr>";
                    }
                    
                    $no++;
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>Belum ada data kegiatan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>