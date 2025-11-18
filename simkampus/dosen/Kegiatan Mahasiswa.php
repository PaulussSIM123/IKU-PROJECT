<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['dosen']);

$page_title = 'Kegiatan Mahasiswa';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'dosen');

include '../includes/header.php';
?>

<div class="card">
    <h2>🎯 Kegiatan Mahasiswa</h2>
    <p>Lihat dan monitoring kegiatan mahasiswa</p>
</div>

<!-- Filter -->
<div class="card">
    <form method="GET" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
        <div class="form-group" style="margin: 0;">
            <label>Status</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="menunggu" <?php echo (isset($_GET['status']) && $_GET['status'] == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                <option value="disetujui" <?php echo (isset($_GET['status']) && $_GET['status'] == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                <option value="ditolak" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label>Jenis Kegiatan</label>
            <select name="jenis">
                <option value="">Semua Jenis</option>
                <option value="organisasi" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'organisasi') ? 'selected' : ''; ?>>Organisasi</option>
                <option value="lomba" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'lomba') ? 'selected' : ''; ?>>Lomba</option>
                <option value="seminar" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'seminar') ? 'selected' : ''; ?>>Seminar</option>
                <option value="workshop" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                <option value="penelitian" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'penelitian') ? 'selected' : ''; ?>>Penelitian</option>
                <option value="pengabdian" <?php echo (isset($_GET['jenis']) && $_GET['jenis'] == 'pengabdian') ? 'selected' : ''; ?>>Pengabdian</option>
            </select>
        </div>
        
        <div style="display: flex; align-items: end; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">Filter</button>
            <a href="kegiatan-mahasiswa.php" class="btn btn-danger">Reset</a>
            <a href="export-kegiatan.php?<?php echo http_build_query($_GET); ?>" class="btn btn-success" title="Export data ke CSV">
                📥 Export
            </a>
        </div>
    </form>
</div>

<!-- Statistik Kegiatan -->
<div class="stats-grid">
    <?php
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan");
    $total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan WHERE status = 'disetujui'");
    $disetujui = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan WHERE status = 'menunggu'");
    $menunggu = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(poin) as total FROM kegiatan WHERE status = 'disetujui'");
    $total_poin = $stmt->fetch()['total'] ?? 0;
    ?>
    
    <div class="stat-card">
        <h3>Total Kegiatan</h3>
        <div class="number"><?php echo $total; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #28a745;">
        <h3>Disetujui</h3>
        <div class="number" style="color: #28a745;"><?php echo $disetujui; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #ffc107;">
        <h3>Menunggu</h3>
        <div class="number" style="color: #ffc107;"><?php echo $menunggu; ?></div>
    </div>
    <div class="stat-card" style="border-left-color: #667eea;">
        <h3>Total Poin</h3>
        <div class="number"><?php echo $total_poin; ?></div>
    </div>
</div>

<!-- Chart Kegiatan Per Jenis -->
<div class="card">
    <h3>Statistik Kegiatan Per Jenis</h3>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px;">
        <?php
        $stmt = $pdo->query("
            SELECT jenis_kegiatan, COUNT(*) as jumlah
            FROM kegiatan
            WHERE status = 'disetujui'
            GROUP BY jenis_kegiatan
            ORDER BY jumlah DESC
        ");
        
        $colors = ['#667eea', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#fd7e14'];
        $i = 0;
        
        while ($row = $stmt->fetch()) {
            $color = $colors[$i % count($colors)];
            echo "<div style='padding: 15px; border-left: 4px solid {$color}; background: #f9f9f9; border-radius: 5px;'>";
            echo "<div style='font-size: 12px; color: #666; text-transform: uppercase;'>" . ucfirst($row['jenis_kegiatan']) . "</div>";
            echo "<div style='font-size: 24px; font-weight: bold; color: {$color}; margin-top: 5px;'>{$row['jumlah']}</div>";
            echo "</div>";
            $i++;
        }
        ?>
    </div>
</div>

<!-- Top 10 Mahasiswa Aktif -->
<div class="table-container">
    <h3>🏆 Top 10 Mahasiswa Paling Aktif</h3>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Jumlah Kegiatan</th>
                <th>Total Poin</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("
                SELECT 
                    m.nim,
                    m.nama,
                    m.jurusan,
                    COUNT(k.id) as jumlah_kegiatan,
                    SUM(CASE WHEN k.status = 'disetujui' THEN k.poin ELSE 0 END) as total_poin
                FROM mahasiswa m
                LEFT JOIN kegiatan k ON m.nim = k.nim
                GROUP BY m.nim, m.nama, m.jurusan
                HAVING jumlah_kegiatan > 0
                ORDER BY total_poin DESC, jumlah_kegiatan DESC
                LIMIT 10
            ");
            
            $rank = 1;
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $medal = $rank <= 3 ? ['🥇', '🥈', '🥉'][$rank - 1] : $rank;
                    
                    echo "<tr>";
                    echo "<td style='text-align: center; font-size: 20px;'>{$medal}</td>";
                    echo "<td>{$row['nim']}</td>";
                    echo "<td><strong>{$row['nama']}</strong></td>";
                    echo "<td>{$row['jurusan']}</td>";
                    echo "<td><span class='badge badge-info'>{$row['jumlah_kegiatan']} kegiatan</span></td>";
                    echo "<td><strong style='color: #667eea;'>{$row['total_poin']} poin</strong></td>";
                    echo "<td><a href='?detail={$row['nim']}' class='btn btn-primary'>Detail</a></td>";
                    echo "</tr>";
                    $rank++;
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>Belum ada data kegiatan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Detail Kegiatan Mahasiswa -->
<?php if (isset($_GET['detail'])): ?>
    <?php
    $nim_detail = $_GET['detail'];
    
    $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->execute([$nim_detail]);
    $mhs_data = $stmt->fetch();
    ?>
    
    <div class="card" style="background: #f0f0ff; border: 2px solid #667eea;">
        <h3>Detail Kegiatan: <?php echo $mhs_data['nama']; ?></h3>
        <p>NIM: <?php echo $mhs_data['nim']; ?> | Jurusan: <?php echo $mhs_data['jurusan']; ?></p>
        
        <div style="margin-top: 20px;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Kegiatan</th>
                        <th>Jenis</th>
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
                    $stmt->execute([$nim_detail]);
                    
                    $no = 1;
                    while ($row = $stmt->fetch()) {
                        $badge_class = $row['status'] == 'disetujui' ? 'badge-success' : 
                                      ($row['status'] == 'ditolak' ? 'badge-danger' : 'badge-warning');
                        
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['tanggal_mulai'])) . "</td>";
                        echo "<td>{$row['nama_kegiatan']}</td>";
                        echo "<td>" . ucfirst($row['jenis_kegiatan']) . "</td>";
                        echo "<td><strong>{$row['poin']}</strong></td>";
                        echo "<td><span class='badge {$badge_class}'>" . ucfirst($row['status']) . "</span></td>";
                        echo "</tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-20">
            <a href="kegiatan-mahasiswa.php" class="btn btn-primary">← Kembali</a>
        </div>
    </div>
<?php else: ?>
    <!-- Semua Kegiatan -->
    <div class="table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>Semua Kegiatan Mahasiswa</h3>
            <div style="display: flex; gap: 10px;">
                <a href="export-kegiatan.php?<?php echo http_build_query($_GET); ?>" 
                   class="btn btn-success"
                   title="Download data kegiatan dalam format CSV (Excel)">
                    📥 Export ke Excel/CSV
                </a>
                <button onclick="window.print()" class="btn btn-primary" title="Print halaman ini">
                    🖨️ Print
                </button>
            </div>
        </div>
        
        <!-- Search -->
        <form method="GET" style="margin-bottom: 15px;">
            <div style="display: flex; gap: 10px;">
                <input type="text" name="search" placeholder="Cari mahasiswa atau kegiatan..." 
                       value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Kegiatan</th>
                    <th>Jenis</th>
                    <th>Poin</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = "1=1";
                $params = [];
                
                if (isset($_GET['status']) && !empty($_GET['status'])) {
                    $where .= " AND k.status = ?";
                    $params[] = $_GET['status'];
                }
                
                if (isset($_GET['jenis']) && !empty($_GET['jenis'])) {
                    $where .= " AND k.jenis_kegiatan = ?";
                    $params[] = $_GET['jenis'];
                }
                
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $search = '%' . $_GET['search'] . '%';
                    $where .= " AND (m.nama LIKE ? OR m.nim LIKE ? OR k.nama_kegiatan LIKE ?)";
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                }
                
                $stmt = $pdo->prepare("
                    SELECT k.*, m.nama, m.nim
                    FROM kegiatan k
                    JOIN mahasiswa m ON k.nim = m.nim
                    WHERE {$where}
                    ORDER BY k.tanggal_mulai DESC
                    LIMIT 50
                ");
                $stmt->execute($params);
                
                $no = 1;
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch()) {
                        $badge_class = $row['status'] == 'disetujui' ? 'badge-success' : 
                                      ($row['status'] == 'ditolak' ? 'badge-danger' : 'badge-warning');
                        
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['tanggal_mulai'])) . "</td>";
                        echo "<td>{$row['nim']}</td>";
                        echo "<td>{$row['nama']}</td>";
                        echo "<td>{$row['nama_kegiatan']}</td>";
                        echo "<td>" . ucfirst($row['jenis_kegiatan']) . "</td>";
                        echo "<td><strong>{$row['poin']}</strong></td>";
                        echo "<td><span class='badge {$badge_class}'>" . ucfirst($row['status']) . "</span></td>";
                        echo "</tr>";
                        
                        // Deskripsi
                        if (!empty($row['deskripsi'])) {
                            echo "<tr style='background: #f9f9f9;'>";
                            echo "<td></td>";
                            echo "<td colspan='7' style='font-size: 12px; color: #666;'>";
                            echo "<strong>Deskripsi:</strong> " . nl2br($row['deskripsi']);
                            echo "</td>";
                            echo "</tr>";
                        }
                        
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Tidak ada data kegiatan</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<!-- Info Export -->
<div class="card" style="background: #d1ecf1; border-left: 4px solid #17a2b8; margin-top: 20px;">
    <h3 style="color: #0c5460; margin-bottom: 10px;">ℹ️ Informasi Export Data</h3>
    <ul style="margin-left: 20px; color: #0c5460;">
        <li><strong>Format File:</strong> CSV (Comma Separated Values) - Bisa dibuka dengan Microsoft Excel, Google Sheets, atau LibreOffice</li>
        <li><strong>Filter Data:</strong> Data yang di-export sesuai dengan filter yang Anda pilih (Status & Jenis Kegiatan)</li>
        <li><strong>Cara Export:</strong> Klik tombol "📥 Export ke Excel/CSV" di atas tabel</li>
        <li><strong>Nama File:</strong> kegiatan_mahasiswa_YYYY-MM-DD.csv</li>
        <li><strong>Isi Data:</strong> No, Tanggal, NIM, Nama, Jurusan, Kegiatan, Jenis, Deskripsi, Poin, Status</li>
    </ul>
</div>

<?php include '../includes/footer.php'; ?>