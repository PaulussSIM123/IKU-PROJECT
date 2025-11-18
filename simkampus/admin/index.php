<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['admin']);

$page_title = 'Dashboard Admin';

// Get statistics
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
    $total_mhs = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM dosen");
    $total_dosen = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM mata_kuliah");
    $total_mk = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan WHERE status = 'menunggu'");
    $pending_kegiatan = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $total_mhs = $total_dosen = $total_mk = $pending_kegiatan = 0;
}

include '../includes/header.php';
?>

<div class="welcome-card">
    <h2>Selamat Datang, Administrator! 👨‍💼</h2>
    <p>Dashboard Admin - Sistem Informasi Manajemen Kampus</p>
</div>

<!-- Statistics -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Mahasiswa</h3>
        <div class="number"><?php echo $total_mhs; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Dosen</h3>
        <div class="number"><?php echo $total_dosen; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Mata Kuliah</h3>
        <div class="number"><?php echo $total_mk; ?></div>
    </div>
    <div class="stat-card">
        <h3>Kegiatan Pending</h3>
        <div class="number"><?php echo $pending_kegiatan; ?></div>
    </div>
</div>

<!-- Menu -->
<h3 style="margin-bottom: 15px;">Menu Administrator</h3>
<div class="menu-grid">
    <a href="mahasiswa.php" class="menu-item">
        <div class="icon">👨‍🎓</div>
        <h3>Data Mahasiswa</h3>
        <p>Kelola data mahasiswa</p>
    </a>
    <a href="dosen.php" class="menu-item">
        <div class="icon">👨‍🏫</div>
        <h3>Data Dosen</h3>
        <p>Kelola data dosen</p>
    </a>
    <a href="matakuliah.php" class="menu-item">
        <div class="icon">📚</div>
        <h3>Mata Kuliah</h3>
        <p>Kelola mata kuliah</p>
    </a>
    <a href="pengumuman.php" class="menu-item">
        <div class="icon">📢</div>
        <h3>Pengumuman</h3>
        <p>Kelola pengumuman</p>
    </a>

</div>

<!-- Data Mahasiswa Terbaru -->
<div class="table-container">
    <h3>Mahasiswa Terbaru</h3>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Angkatan</th>
                <th>Email</th>
                <th>No HP</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM mahasiswa ORDER BY nim DESC LIMIT 5");
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['nim']}</td>";
                    echo "<td>{$row['nama']}</td>";
                    echo "<td>{$row['jurusan']}</td>";
                    echo "<td>{$row['angkatan']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['no_hp']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Belum ada data mahasiswa</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="mt-20">
        <a href="mahasiswa.php" class="btn btn-primary">Lihat Semua →</a>
    </div>
</div>

<!-- Kegiatan Pending -->
<div class="table-container">
    <h3>Kegiatan Menunggu Validasi</h3>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Kegiatan</th>
                <th>Jenis</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("
                SELECT k.*, m.nama 
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
                    echo "<td><span class='badge badge-warning'>" . ucfirst($row['status']) . "</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Tidak ada kegiatan yang menunggu validasi</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>