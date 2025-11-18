<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['admin']);

$page_title = 'Pengumuman';

// Proses Tambah Pengumuman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $judul = clean($_POST['judul']);
    $isi = clean($_POST['isi']);
    $tanggal = $_POST['tanggal'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO pengumuman (judul, isi, tanggal, user_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$judul, $isi, $tanggal, $user_id]);
        
        setAlert('success', 'Pengumuman berhasil ditambahkan!');
        header("Location: pengumuman.php");
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menambahkan pengumuman!');
    }
}

// Proses Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $judul = clean($_POST['judul']);
    $isi = clean($_POST['isi']);
    $tanggal = $_POST['tanggal'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE pengumuman 
            SET judul = ?, isi = ?, tanggal = ?
            WHERE id = ?
        ");
        $stmt->execute([$judul, $isi, $tanggal, $id]);
        
        setAlert('success', 'Pengumuman berhasil diupdate!');
        header("Location: pengumuman.php");
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengupdate pengumuman!');
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM pengumuman WHERE id = ?");
        $stmt->execute([$id]);
        setAlert('success', 'Pengumuman berhasil dihapus!');
        header("Location: pengumuman.php");
        exit();
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal menghapus pengumuman!');
    }
}

// Get data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pengumuman WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

include '../includes/header.php';
?>

<div class="card">
    <h2>📢 Pengumuman Kampus</h2>
    <p>Kelola pengumuman untuk seluruh aktivitas akademika</p>
</div>

<!-- Form Tambah/Edit Pengumuman -->
<div class="card">
    <h3><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Pengumuman</h3>
    <form method="POST">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label>Judul Pengumuman <span style="color: red;">*</span></label>
            <input type="text" name="judul" 
                   value="<?php echo $edit_data ? $edit_data['judul'] : ''; ?>"
                   placeholder="Masukkan judul pengumuman"
                   required>
        </div>
        
        <div class="form-group">
            <label>Tanggal <span style="color: red;">*</span></label>
            <input type="date" name="tanggal" 
                   value="<?php echo $edit_data ? $edit_data['tanggal'] : date('Y-m-d'); ?>"
                   required>
        </div>
        
        <div class="form-group">
            <label>Isi Pengumuman <span style="color: red;">*</span></label>
            <textarea name="isi" rows="8" 
                      placeholder="Tulis isi pengumuman di sini..."
                      required><?php echo $edit_data ? $edit_data['isi'] : ''; ?></textarea>
        </div>
        
        <?php if ($edit_data): ?>
            <button type="submit" name="update" class="btn btn-primary">Update Pengumuman</button>
            <a href="pengumuman.php" class="btn btn-danger">Batal</a>
        <?php else: ?>
            <button type="submit" name="tambah" class="btn btn-primary">Tambahkan Pengumuman</button>
        <?php endif; ?>
    </form>
</div>

<!-- Statistik -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <?php
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengumuman");
    $total = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengumuman WHERE tanggal >= CURDATE()");
    $aktif = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pengumuman WHERE tanggal < CURDATE()");
    $expired = $stmt->fetch()['total'];
    ?>
    
    <div class="stat-card">
        <h3>Total Pengumuman</h3>
        <div class="number"><?php echo $total; ?></div>
    </div>
    <div class="stat-card">
        <h3>Pengumuman Aktif</h3>
        <div class="number"><?php echo $aktif; ?></div>
    </div>
    <div class="stat-card">
        <h3>Pengumuman Lalu</h3>
        <div class="number"><?php echo $expired; ?></div>
    </div>
</div>

<!-- Tabel Pengumuman -->
<div class="table-container">
    <h3>Daftar Pengumuman</h3>
    
    <!-- Search -->
    <form method="GET" style="margin-bottom: 15px;">
        <div style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Cari pengumuman..." 
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
                <th>Judul</th>
                <th>Isi Pengumuman</th>
                <th>Dibuat Oleh</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $where = "1=1";
            $params = [];
            
            if (isset($_GET['search']) && !empty($_GET['search'])) {
                $search = '%' . $_GET['search'] . '%';
                $where = "(judul LIKE ? OR isi LIKE ?)";
                $params = [$search, $search];
            }
            
            $stmt = $pdo->prepare("
                SELECT p.*, u.username
                FROM pengumuman p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE {$where}
                ORDER BY p.tanggal DESC, p.created_at DESC
            ");
            $stmt->execute($params);
            
            $no = 1;
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $is_active = strtotime($row['tanggal']) >= strtotime(date('Y-m-d'));
                    $status = $is_active ? 
                        '<span class="badge badge-success">Aktif</span>' : 
                        '<span class="badge badge-danger">Expired</span>';
                    
                    // Truncate isi jika terlalu panjang
                    $isi = strlen($row['isi']) > 100 ? 
                           substr($row['isi'], 0, 100) . '...' : 
                           $row['isi'];
                    
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                    echo "<td><strong>{$row['judul']}</strong></td>";
                    echo "<td>" . nl2br($isi) . "</td>";
                    echo "<td>{$row['username']}</td>";
                    echo "<td>{$status}</td>";
                    echo "<td>";
                    echo "<a href='?edit={$row['id']}' class='btn btn-warning' style='margin-right: 5px;'>Edit</a>";
                    echo "<a href='?hapus={$row['id']}' class='btn btn-danger' onclick='return confirm(\"Yakin hapus pengumuman ini?\")'>Hapus</a>";
                    echo "</td>";
                    echo "</tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='7' class='text-center'>Belum ada pengumuman</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Preview Pengumuman untuk User -->
<div class="card" style="background: #f9f9f9;">
    <h3>Preview Pengumuman Terbaru (Untuk User)</h3>
    <div style="display: grid; gap: 15px; margin-top: 15px;">
        <?php
        $stmt = $pdo->query("
            SELECT * FROM pengumuman 
            WHERE tanggal >= CURDATE() 
            ORDER BY tanggal DESC 
            LIMIT 3
        ");
        
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch()) {
                echo "<div style='background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;'>";
                echo "<h4 style='color: #667eea; margin-bottom: 10px;'>{$row['judul']}</h4>";
                echo "<p style='color: #666; font-size: 12px; margin-bottom: 10px;'>";
                echo "📅 " . formatTanggal($row['tanggal']);
                echo "</p>";
                echo "<p style='color: #333;'>" . nl2br($row['isi']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p style='text-align: center; color: #666;'>Tidak ada pengumuman aktif</p>";
        }
        ?>
    </div>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>