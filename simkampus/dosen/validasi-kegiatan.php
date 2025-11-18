<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['dosen']);

$page_title = 'Validasi Kegiatan';

// Proses Validasi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validasi'])) {
    $kegiatan_id = $_POST['kegiatan_id'];
    $status = $_POST['status'];
    $poin = $_POST['poin'];
    
    try {
        $stmt = $pdo->prepare("UPDATE kegiatan SET status = ?, poin = ? WHERE id = ?");
        $stmt->execute([$status, $poin, $kegiatan_id]);
        
        $msg = $status == 'disetujui' ? 'disetujui' : 'ditolak';
        setAlert('success', "Kegiatan berhasil {$msg}!");
        header("Location: validasi-kegiatan.php");
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal memvalidasi kegiatan!');
    }
}

include '../includes/header.php';
?>

<div class="card">
    <h2>🎯 Validasi Kegiatan Mahasiswa</h2>
    <p>Validasi dan berikan poin untuk kegiatan mahasiswa</p>
</div>

<!-- Filter -->
<div class="card">
    <form method="GET" style="display: flex; gap: 15px; align-items: end;">
        <div class="form-group" style="margin: 0; flex: 1;">
            <label>Filter Status</label>
            <select name="status">
                <option value="">Semua Status</option>
                <option value="menunggu" <?php echo (isset($_GET['status']) && $_GET['status'] == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                <option value="disetujui" <?php echo (isset($_GET['status']) && $_GET['status'] == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                <option value="ditolak" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<!-- Statistik -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <?php
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan WHERE status = 'menunggu'");
    $menunggu = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan WHERE status = 'disetujui'");
    $disetujui = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan WHERE status = 'ditolak'");
    $ditolak = $stmt->fetch()['total'];
    ?>
    
    <div class="stat-card">
        <h3>Menunggu Validasi</h3>
        <div class="number"><?php echo $menunggu; ?></div>
    </div>
    <div class="stat-card">
        <h3>Disetujui</h3>
        <div class="number"><?php echo $disetujui; ?></div>
    </div>
    <div class="stat-card">
        <h3>Ditolak</h3>
        <div class="number"><?php echo $ditolak; ?></div>
    </div>
</div>

<!-- Tabel Kegiatan -->
<div class="table-container">
    <h3>Daftar Kegiatan Mahasiswa</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Kegiatan</th>
                <th>Jenis</th>
                <th>Poin</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $where = "1=1";
            $params = [];
            
            if (isset($_GET['status']) && !empty($_GET['status'])) {
                $where = "k.status = ?";
                $params[] = $_GET['status'];
            }
            
            $stmt = $pdo->prepare("
                SELECT k.*, m.nama, m.jurusan
                FROM kegiatan k
                JOIN mahasiswa m ON k.nim = m.nim
                WHERE {$where}
                ORDER BY k.tanggal_mulai DESC
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
                    echo "<td>{$row['nama']}<br><small style='color: #666;'>{$row['jurusan']}</small></td>";
                    echo "<td>{$row['nama_kegiatan']}</td>";
                    echo "<td>" . ucfirst($row['jenis_kegiatan']) . "</td>";
                    echo "<td><strong>{$row['poin']}</strong></td>";
                    echo "<td><span class='badge {$badge_class}'>" . ucfirst($row['status']) . "</span></td>";
                    echo "<td>";
                    
                    if ($row['status'] == 'menunggu') {
                        echo "<button onclick='validasi({$row['id']}, \"{$row['nama_kegiatan']}\", \"{$row['jenis_kegiatan']}\")' class='btn btn-primary'>Validasi</button>";
                    } else {
                        echo "<button onclick='lihatDetail({$row['id']})' class='btn btn-warning'>Detail</button>";
                    }
                    
                    echo "</td>";
                    echo "</tr>";
                    
                    // Tampilkan deskripsi
                    if (!empty($row['deskripsi'])) {
                        echo "<tr style='background: #f9f9f9;'>";
                        echo "<td></td>";
                        echo "<td colspan='8' style='font-size: 12px; color: #666;'>";
                        echo "<strong>Deskripsi:</strong> " . nl2br($row['deskripsi']);
                        echo "</td>";
                        echo "</tr>";
                    }
                    
                    $no++;
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>Tidak ada data kegiatan</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Modal Validasi -->
<div id="modalValidasi" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5);">
    <div style="background: white; margin: 5% auto; padding: 30px; width: 90%; max-width: 500px; border-radius: 10px;">
        <h3 style="margin-bottom: 20px;">Validasi Kegiatan</h3>
        <form method="POST">
            <input type="hidden" name="kegiatan_id" id="kegiatan_id">
            
            <div class="form-group">
                <label>Nama Kegiatan</label>
                <input type="text" id="nama_kegiatan" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Jenis Kegiatan</label>
                <input type="text" id="jenis_kegiatan" readonly style="background: #f5f5f5;">
            </div>
            
            <div class="form-group">
                <label>Status <span style="color: red;">*</span></label>
                <select name="status" id="status_validasi" required onchange="updatePoinSuggestion()">
                    <option value="">-- Pilih Status --</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Poin Kegiatan <span style="color: red;">*</span></label>
                <input type="number" name="poin" id="poin" min="0" max="100" required>
                <small id="poin_suggestion" style="color: #666; display: block; margin-top: 5px;"></small>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" name="validasi" class="btn btn-primary">Simpan</button>
                <button type="button" onclick="closeModalValidasi()" class="btn btn-danger">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
const poinSuggestion = {
    'organisasi': 20,
    'lomba': 50,
    'seminar': 10,
    'workshop': 15,
    'penelitian': 40,
    'pengabdian': 30,
    'lainnya': 10
};

function validasi(id, nama, jenis) {
    document.getElementById('kegiatan_id').value = id;
    document.getElementById('nama_kegiatan').value = nama;
    document.getElementById('jenis_kegiatan').value = jenis.charAt(0).toUpperCase() + jenis.slice(1);
    document.getElementById('status_validasi').value = '';
    document.getElementById('poin').value = poinSuggestion[jenis] || 10;
    document.getElementById('poin_suggestion').textContent = 'Saran poin: ' + (poinSuggestion[jenis] || 10);
    document.getElementById('modalValidasi').style.display = 'block';
}

function updatePoinSuggestion() {
    const status = document.getElementById('status_validasi').value;
    const poinInput = document.getElementById('poin');
    
    if (status === 'ditolak') {
        poinInput.value = 0;
        poinInput.readOnly = true;
    } else {
        poinInput.readOnly = false;
    }
}

function closeModalValidasi() {
    document.getElementById('modalValidasi').style.display = 'none';
}

function lihatDetail(id) {
    alert('Fitur detail kegiatan akan segera hadir!');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('modalValidasi');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>