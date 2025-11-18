<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$page_title = 'Rekap Absensi';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

include '../includes/header.php';
?>

<div class="card">
    <h2>📊 Rekap Absensi Kuliah</h2>
    <p>Lihat rekap kehadiran kuliah Anda</p>
</div>

<!-- Statistik Kehadiran -->
<div class="stats-grid">
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
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set default 0 jika null atau tidak ada data
    if (!$stats || $stats['total'] == 0) {
        $stats = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'total' => 0
        ];
    }
    
    $persentase = $stats['total'] > 0 ? 
                  ($stats['hadir'] / $stats['total']) * 100 : 0;
    ?>
    
    <div class="stat-card">
        <h3>Total Pertemuan</h3>
        <div class="number"><?php echo $stats['total']; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #28a745;">
        <h3>Hadir</h3>
        <div class="number" style="color: #28a745;"><?php echo $stats['hadir']; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #ffc107;">
        <h3>Izin/Sakit</h3>
        <div class="number" style="color: #ffc107;">
            <?php echo $stats['izin'] + $stats['sakit']; ?>
        </div>
    </div>
    <div class="stat-card" style="border-left-color: #dc3545;">
        <h3>Alpha</h3>
        <div class="number" style="color: #dc3545;"><?php echo $stats['alpha']; ?></div>
    </div>
</div>

<!-- Persentase Kehadiran -->
<div class="card">
    <h3>Persentase Kehadiran Keseluruhan</h3>
    <div style="display: flex; align-items: center; gap: 20px; margin-top: 15px;">
        <div style="flex: 1; background: #f5f5f5; border-radius: 10px; height: 30px; overflow: hidden;">
            <div style="background: <?php echo $persentase >= 75 ? '#28a745' : ($persentase >= 50 ? '#ffc107' : '#dc3545'); ?>; 
                        height: 100%; width: <?php echo $persentase; ?>%; 
                        transition: width 0.5s;">
            </div>
        </div>
        <div style="font-size: 24px; font-weight: bold; color: <?php echo $persentase >= 75 ? '#28a745' : ($persentase >= 50 ? '#ffc107' : '#dc3545'); ?>;">
            <?php echo number_format($persentase, 1); ?>%
        </div>
    </div>
    <p style="margin-top: 10px; color: #666; font-size: 14px;">
        <?php if ($stats['total'] == 0): ?>
            ℹ️ Belum ada data absensi. Data akan muncul setelah dosen input absensi.
        <?php elseif ($persentase >= 75): ?>
            ✅ Kehadiran Anda sangat baik! Pertahankan!
        <?php elseif ($persentase >= 50): ?>
            ⚠️ Kehadiran Anda cukup, tingkatkan lagi!
        <?php else: ?>
            ❌ Kehadiran Anda kurang, mohon lebih rajin hadir!
        <?php endif; ?>
    </p>
</div>

<!-- Rekap Per Mata Kuliah -->
<div class="table-container">
    <h3>Rekap Absensi Per Mata Kuliah</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MK</th>
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
            ");
            $stmt->execute([$nim]);
            
            $no = 1;
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $persen = $row['total'] > 0 ? 
                             ($row['hadir'] / $row['total']) * 100 : 0;
                    
                    if ($row['total'] == 0) {
                        $badge = 'badge badge-info';
                        $status = 'Belum Ada Data';
                    } elseif ($persen >= 75) {
                        $badge = 'badge-success';
                        $status = 'Aman';
                    } elseif ($persen >= 50) {
                        $badge = 'badge-warning';
                        $status = 'Perhatian';
                    } else {
                        $badge = 'badge-danger';
                        $status = 'Bahaya';
                    }
                    
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>{$row['kode_mk']}</td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['hadir']}</td>";
                    echo "<td>{$row['izin']}</td>";
                    echo "<td>{$row['sakit']}</td>";
                    echo "<td>{$row['alpha']}</td>";
                    echo "<td><strong>{$row['total']}</strong></td>";
                    echo "<td><strong>" . number_format($persen, 1) . "%</strong></td>";
                    echo "<td><span class='{$badge}'>{$status}</span></td>";
                    echo "</tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='10' class='text-center'>Anda belum mengambil mata kuliah. Silakan ambil mata kuliah di menu KRS.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Detail Absensi -->
<?php if (isset($_GET['kode_mk'])): ?>
    <?php
    $kode_mk = $_GET['kode_mk'];
    
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE kode_mk = ?");
    $stmt->execute([$kode_mk]);
    $mk_info = $stmt->fetch();
    ?>
    
    <div class="table-container">
        <h3>Detail Absensi: <?php echo $mk_info['nama_mk']; ?></h3>
        <table>
            <thead>
                <tr>
                    <th>Pertemuan</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("
                    SELECT * FROM absensi 
                    WHERE nim = ? AND kode_mk = ?
                    ORDER BY pertemuan_ke
                ");
                $stmt->execute([$nim, $kode_mk]);
                
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch()) {
                        $badge = $row['status'] == 'hadir' ? 'badge-success' :
                                ($row['status'] == 'alpha' ? 'badge-danger' : 'badge-warning');
                        
                        echo "<tr>";
                        echo "<td>Pertemuan {$row['pertemuan_ke']}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                        echo "<td><span class='badge {$badge}'>" . ucfirst($row['status']) . "</span></td>";
                        echo "<td>" . ($row['keterangan'] ?? '-') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>Belum ada data absensi</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="mt-20">
            <a href="absensi.php" class="btn btn-primary">← Kembali</a>
        </div>
    </div>
<?php else: ?>
    <!-- Pilih Mata Kuliah untuk Detail -->
    <div class="card">
        <h3>Lihat Detail Absensi Per Mata Kuliah</h3>
        <form method="GET">
            <div style="display: flex; gap: 15px; align-items: end;">
                <div class="form-group" style="flex: 1; margin: 0;">
                    <label>Pilih Mata Kuliah</label>
                    <select name="kode_mk" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT DISTINCT mk.kode_mk, mk.nama_mk 
                            FROM kelas k
                            JOIN mata_kuliah mk ON k.kode_mk = mk.kode_mk
                            WHERE k.nim = ?
                            ORDER BY mk.nama_mk
                        ");
                        $stmt->execute([$nim]);
                        while ($mk = $stmt->fetch()) {
                            echo "<option value='{$mk['kode_mk']}'>{$mk['kode_mk']} - {$mk['nama_mk']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Lihat Detail</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Info Ketentuan -->
<div class="card" style="background: #fff3cd; border: 1px solid #ffc107;">
    <h3 style="color: #856404;">ℹ️ Ketentuan Kehadiran</h3>
    <ul style="margin-left: 20px; color: #856404;">
        <li>Kehadiran minimal <strong>75%</strong> untuk dapat mengikuti UAS</li>
        <li>Kehadiran <strong>50-74%</strong> harus melengkapi tugas tambahan</li>
        <li>Kehadiran <strong>< 50%</strong> tidak diperkenankan mengikuti UAS</li>
        <li>Izin dan Sakit harus disertai surat keterangan</li>
    </ul>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>