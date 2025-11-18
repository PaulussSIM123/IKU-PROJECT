<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['dosen']);

$page_title = 'Dashboard Dosen';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'dosen');
$nip = $user_data['nip'];

// Get Statistics
try {
    // Total mata kuliah diampu
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM mata_kuliah WHERE dosen_nip = ?");
    $stmt->execute([$nip]);
    $total_mk = $stmt->fetch()['total'];
    
    // Total mahasiswa
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT k.nim) as total 
        FROM kelas k 
        JOIN mata_kuliah m ON k.kode_mk = m.kode_mk 
        WHERE m.dosen_nip = ?
    ");
    $stmt->execute([$nip]);
    $total_mhs = $stmt->fetch()['total'];
    
    // Kegiatan pending validasi
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total 
        FROM kegiatan 
        WHERE status = 'menunggu'
    ");
    $stmt->execute();
    $pending_kegiatan = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $total_mk = $total_mhs = $pending_kegiatan = 0;
}

include '../includes/header.php';
?>

<div class="welcome-card">
    <h2>Selamat Datang, <?php echo $user_data['nama']; ?>! 👨‍🏫</h2>
    <p>NIP: <?php echo $nip; ?> | Jurusan: <?php echo $user_data['jurusan']; ?></p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Mata Kuliah Diampu</h3>
        <div class="number"><?php echo $total_mk; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Mahasiswa</h3>
        <div class="number"><?php echo $total_mhs; ?></div>
    </div>
    <div class="stat-card">
        <h3>Kegiatan Pending</h3>
        <div class="number"><?php echo $pending_kegiatan; ?></div>
    </div>
</div>

<!-- Menu -->
<h3 style="margin-bottom: 15px;">Menu Dosen</h3>
<div class="menu-grid">
    <a href="nilai.php" class="menu-item">
        <div class="icon">📝</div>
        <h3>Input Nilai</h3>
        <p>Input nilai mahasiswa</p>
    </a>
    <a href="absensi.php" class="menu-item">
        <div class="icon">✅</div>
        <h3>Absensi</h3>
        <p>Input absensi mahasiswa</p>
    </a>
    <a href="validasi-kegiatan.php" class="menu-item">
        <div class="icon">🎯</div>
        <h3>Validasi Kegiatan</h3>
        <p>Validasi kegiatan mahasiswa</p>
    </a>
    <a href="Kegiatan Mahasiswa.php" class="menu-item">
        <div class="icon">📊</div>
        <h3>Kegiatan Mahasiswa</h3>
        <p>Lihat & monitoring semua kegiatan</p>
    </a>
</div>

<!-- Quick Export Section -->
<div class="card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="color: white; margin-bottom: 10px;">📥 Export Data Kegiatan</h3>
            <p style="margin: 0;">Download laporan kegiatan mahasiswa dalam format Excel/CSV</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="export-kegiatan.php" class="btn" style="background: white; color: #28a745; font-weight: bold;">
                📥 Export Semua
            </a>
            <a href="Kegiatan Mahasiswa.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; border: 2px solid white;">
                📊 Lihat Detail
            </a>
        </div>
    </div>
</div>

<!-- Mata Kuliah yang Diampu -->
<div class="table-container">
    <h3>Mata Kuliah yang Diampu</h3>
    <table>
        <thead>
            <tr>
                <th>Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Semester</th>
                <th>Jumlah Mahasiswa</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT mk.*, 
                       COUNT(DISTINCT k.nim) as jml_mhs
                FROM mata_kuliah mk
                LEFT JOIN kelas k ON mk.kode_mk = k.kode_mk
                WHERE mk.dosen_nip = ?
                GROUP BY mk.kode_mk
            ");
            $stmt->execute([$nip]);
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['kode_mk']}</td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['sks']}</td>";
                    echo "<td>{$row['semester']}</td>";
                    echo "<td>{$row['jml_mhs']}</td>";
                    echo "<td>";
                    echo "<a href='nilai.php?kode_mk={$row['kode_mk']}' class='btn btn-primary' style='margin-right: 5px;'>Input Nilai</a>";
                    echo "<a href='absensi.php?kode_mk={$row['kode_mk']}' class='btn btn-success'>Absensi</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Belum ada mata kuliah yang diampu</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Kegiatan Mahasiswa Pending -->
<div class="table-container">
    <h3>Kegiatan Mahasiswa Menunggu Validasi</h3>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Kegiatan</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("
                SELECT k.*, m.nama, m.jurusan
                FROM kegiatan k
                JOIN mahasiswa m ON k.nim = m.nim
                WHERE k.status = 'menunggu'
                ORDER BY k.tanggal_mulai DESC
                LIMIT 10
            ");
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['nim']}</td>";
                    echo "<td>{$row['nama']}</td>";
                    echo "<td>{$row['nama_kegiatan']}</td>";
                    echo "<td>" . ucfirst($row['jenis_kegiatan']) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_mulai'])) . "</td>";
                    echo "<td>";
                    echo "<a href='validasi-kegiatan.php?id={$row['id']}' class='btn btn-warning'>Validasi</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Tidak ada kegiatan yang menunggu validasi</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="mt-20">
        <a href="validasi-kegiatan.php" class="btn btn-primary">Lihat Semua →</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>