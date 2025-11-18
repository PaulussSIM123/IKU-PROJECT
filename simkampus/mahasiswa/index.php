<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';


// Cek login dan role
checkRole(['mahasiswa']);

$page_title = 'Dashboard Mahasiswa';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

// ==========================
// Statistik Mahasiswa
// ==========================
try {
    // Total mata kuliah
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kelas WHERE nim = ?");
    $stmt->execute([$nim]);
    $total_mk = $stmt->fetch()['total'];

    // Total kegiatan
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM kegiatan WHERE nim = ?");
    $stmt->execute([$nim]);
    $total_kegiatan = $stmt->fetch()['total'];

    // Total poin
    $stmt = $pdo->prepare("SELECT SUM(poin) as total FROM kegiatan WHERE nim = ? AND status = 'disetujui'");
    $stmt->execute([$nim]);
    $total_poin = $stmt->fetch()['total'] ?? 0;

    // IPK
    $stmt = $pdo->prepare("SELECT AVG(nilai_akhir) as ipk FROM kelas WHERE nim = ?");
    $stmt->execute([$nim]);
    $ipk = $stmt->fetch()['ipk'] ?? 0;

} catch (PDOException $e) {
    $total_mk = $total_kegiatan = $total_poin = $ipk = 0;
}

include '../includes/header.php';
?>

<div class="welcome-card">
    <h2>Selamat Datang, <?php echo $user_data['nama']; ?>! 👋</h2>
    <p>NIM: <?php echo $nim; ?> | Jurusan: <?php echo $user_data['jurusan']; ?> | Angkatan: <?php echo $user_data['angkatan']; ?></p>
</div>

<!-- ========================== -->
<!--     STATISTIK MAHASISWA   -->
<!-- ========================== -->

<div class="stats-grid">
    <div class="stat-card">
        <h3>Mata Kuliah Diambil</h3>
        <div class="number"><?php echo $total_mk; ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Kegiatan</h3>
        <div class="number"><?php echo $total_kegiatan; ?></div>
    </div>
    <div class="stat-card">
        <h3>Poin Kegiatan</h3>
        <div class="number"><?php echo $total_poin; ?></div>
    </div>
    <div class="stat-card">
        <h3>IPK</h3>
        <div class="number"><?php echo hitungIPK($ipk); ?></div>
    </div>
</div>

<!-- MENU -->
<h3 style="margin-bottom: 15px;">Menu Mahasiswa</h3>
<div class="menu-grid">
    <a href="krs.php" class="menu-item">
        <div class="icon">📚</div>
        <h3>Ambil Mata Kuliah</h3>
        <p>Kartu Rencana Studi (KRS)</p>
    </a>
    <a href="nilai.php" class="menu-item">
        <div class="icon">📝</div>
        <h3>Nilai Kuliah</h3>
        <p>Lihat nilai mata kuliah</p>
    </a>
    <a href="kegiatan.php" class="menu-item">
        <div class="icon">🎯</div>
        <h3>Kegiatan</h3>
        <p>Input kegiatan mahasiswa</p>
    </a>
    <a href="absensi.php" class="menu-item">
        <div class="icon">📊</div>
        <h3>Absensi</h3>
        <p>Rekap absensi kuliah</p>
    </a>
    </a>
    <a href="pengumuman.php" class="menu-item">
        <div class="icon">📢</div>
        <h3>Pengumuman</h3>
        <p>Lihat Pengumuman</p>
    </a>
</div>

<!-- ========================== -->
<!--   TABEL NILAI TERBARU     -->
<!-- ========================== -->
<div class="table-container">
    <h3>Nilai Mata Kuliah Semester Ini</h3>
    <table>
        <thead>
            <tr>
                <th>Kode MK</th>
                <th>Mata Kuliah</th>
                <th>SKS</th>
                <th>Nilai Akhir</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT k.*, m.nama_mk, m.sks 
                FROM kelas k 
                JOIN mata_kuliah m ON k.kode_mk = m.kode_mk 
                WHERE k.nim = ?
                ORDER BY k.id DESC
                LIMIT 5
            ");
            $stmt->execute([$nim]);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    echo "<tr>";
                    echo "<td>{$row['kode_mk']}</td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['sks']}</td>";
                    echo "<td>{$row['nilai_akhir']}</td>";
                    echo "<td><span class='badge badge-success'>{$row['grade']}</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>Belum ada data nilai</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <div class="mt-20">
        <a href="nilai.php" class="btn btn-primary">Lihat Semua Nilai →</a>
    </div>
</div>

<!-- ========================== -->
<!--   TABEL KEGIATAN TERBARU  -->
<!-- ========================== -->

<div class="table-container">
    <h3>Kegiatan Terbaru</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Kegiatan</th>
                <th>Jenis</th>
                <th>Tanggal</th>
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
                LIMIT 5
            ");
            $stmt->execute([$nim]);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $badge_class = $row['status'] == 'disetujui' ? 'badge-success' :
                                  ($row['status'] == 'ditolak' ? 'badge-danger' : 'badge-warning');

                    echo "<tr>";
                    echo "<td>{$row['nama_kegiatan']}</td>";
                    echo "<td>" . ucfirst($row['jenis_kegiatan']) . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_mulai'])) . "</td>";
                    echo "<td>{$row['poin']}</td>";
                    echo "<td><span class='badge {$badge_class}'>" . ucfirst($row['status']) . "</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>Belum ada data kegiatan</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="mt-20">
        <a href="kegiatan.php" class="btn btn-primary">Lihat Semua Kegiatan →</a>
    </div>
</div>

<!-- ========================== -->
<!--   REKAP ABSENSI TERBARU   -->
<!-- ========================== -->

<div class="table-container">
    <h3>📊 Rekap Absensi Kuliah</h3>

    <?php
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha,
            COUNT(*) as total
        FROM absensi
        WHERE nim = ?
    ");
    $stmt->execute([$nim]);
    $absen_stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $absen_stats['hadir'] = $absen_stats['hadir'] ?? 0;
    $absen_stats['izin'] = $absen_stats['izin'] ?? 0;
    $absen_stats['sakit'] = $absen_stats['sakit'] ?? 0;
    $absen_stats['alpha'] = $absen_stats['alpha'] ?? 0;
    $absen_stats['total'] = $absen_stats['total'] ?? 0;

    $persentase_hadir = $absen_stats['total'] > 0 ? 
                        ($absen_stats['hadir'] / $absen_stats['total']) * 100 : 0;
    ?>

    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px;">
        <div style="padding: 15px; background: #d4edda; border-radius: 8px; text-align: center;">
            <div style="font-size: 12px; color: #155724;">Hadir</div>
            <div style="font-size: 24px; font-weight: bold; color: #155724;"><?php echo $absen_stats['hadir']; ?></div>
        </div>
        <div style="padding: 15px; background: #fff3cd; border-radius: 8px; text-align: center;">
            <div style="font-size: 12px; color: #856404;">Izin</div>
            <div style="font-size: 24px; font-weight: bold; color: #856404;"><?php echo $absen_stats['izin']; ?></div>
        </div>
        <div style="padding: 15px; background: #d1ecf1; border-radius: 8px; text-align: center;">
            <div style="font-size: 12px; color: #0c5460;">Sakit</div>
            <div style="font-size: 24px; font-weight: bold; color: #0c5460;"><?php echo $absen_stats['sakit']; ?></div>
        </div>
        <div style="padding: 15px; background: #f8d7da; border-radius: 8px; text-align: center;">
            <div style="font-size: 12px; color: #721c24;">Alpha</div>
            <div style="font-size: 24px; font-weight: bold; color: #721c24;"><?php echo $absen_stats['alpha']; ?></div>
        </div>
    </div>

    <div style="margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
            <h4 style="margin: 0;">Persentase Kehadiran</h4>
            <span style="font-size: 24px; font-weight: bold; color: <?php echo $persentase_hadir >= 75 ? '#28a745' : ($persentase_hadir >= 50 ? '#ffc107' : '#dc3545'); ?>;">
                <?php echo number_format($persentase_hadir, 1); ?>%
            </span>
        </div>

        <div style="background: #f5f5f5; border-radius: 10px; height: 30px; overflow: hidden;">
            <div style="background: <?php echo $persentase_hadir >= 75 ? '#28a745' : ($persentase_hadir >= 50 ? '#ffc107' : '#dc3545'); ?>; 
                        height: 100%; width: <?php echo $persentase_hadir; ?>%; 
                        transition: width 0.5s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                <?php if ($persentase_hadir > 10): ?>
                    <?php echo number_format($persentase_hadir, 1); ?>%
                <?php endif; ?>
            </div>
        </div>

        <p style="margin-top: 10px; color: #666; font-size: 14px; text-align: center;">
            <?php if ($persentase_hadir >= 75): ?>
                ✅ Kehadiran Anda sangat baik! Pertahankan!
            <?php elseif ($persentase_hadir >= 50): ?>
                ⚠️ Kehadiran Anda cukup, tingkatkan lagi!
            <?php else: ?>
                ❌ Kehadiran Anda kurang, mohon lebih rajin hadir!
            <?php endif; ?>
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mata Kuliah</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>Total</th>
                <th>%</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT 
                    mk.kode_mk,
                    mk.nama_mk,
                    COALESCE(SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END), 0) as hadir,
                    COALESCE(SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END), 0) as izin,
                    COALESCE(SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END), 0) as sakit,
                    COALESCE(SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END), 0) as alpha,
                    COALESCE(COUNT(a.id), 0) as total
                FROM kelas k
                JOIN mata_kuliah mk ON k.kode_mk = mk.kode_mk
                LEFT JOIN absensi a ON k.kode_mk = a.kode_mk AND k.nim = a.nim
                WHERE k.nim = ?
                GROUP BY mk.kode_mk, mk.nama_mk
                ORDER BY mk.nama_mk
                LIMIT 5
            ");
            $stmt->execute([$nim]);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $persen = $row['total'] > 0 ? ($row['hadir'] / $row['total']) * 100 : 0;

                    if ($persen >= 75) {
                        $badge = 'badge-success';
                        $status = 'Aman';
                    } elseif ($persen >= 50) {
                        $badge = 'badge-warning';
                        $status = 'Perhatian';
                    } else {
                        $badge = 'badge-danger';
                        $status = $row['total'] > 0 ? 'Bahaya' : 'Belum Ada Data';
                    }

                    echo "<tr>";
                    echo "<td><strong>{$row['nama_mk']}</strong><br><small style='color: #666;'>{$row['kode_mk']}</small></td>";
                    echo "<td>{$row['hadir']}</td>";
                    echo "<td>{$row['izin']}</td>";
                    echo "<td>{$row['sakit']}</td>";
                    echo "<td>{$row['alpha']}</td>";
                    echo "<td><strong>{$row['total']}</strong></td>";
                    echo "<td><strong>" . number_format($persen, 1) . "%</strong></td>";
                    echo "<td><span class='badge {$badge}'>{$status}</span></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Belum ada data mata kuliah</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="mt-20">
        <a href="absensi.php" class="btn btn-primary">Lihat Detail Absensi →</a>
    </div>
</div>

<!-- INFO KETENTUAN KEHADIRAN -->
<div class="card" style="background: #fff3cd; border-left: 4px solid #ffc107;">
    <h3 style="color: #856404; margin-bottom: 10px;">ℹ️ Ketentuan Kehadiran</h3>
    <ul style="margin-left: 20px; color: #856404;">
        <li>Kehadiran minimal <strong>75%</strong> untuk dapat mengikuti UAS</li>
        <li>Kehadiran <strong>50-74%</strong> harus melengkapi tugas tambahan</li>
        <li>Kehadiran <strong>< 50%</strong> tidak diperkenankan mengikuti UAS</li>
    </ul>
</div>

<?php include '../includes/footer.php'; ?>
