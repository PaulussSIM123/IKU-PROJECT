<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';


checkRole(['dosen']);

$page_title = 'Input Absensi';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'dosen');
$nip = $user_data['nip'];

// Proses Input Absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_absensi'])) {
    $kode_mk = $_POST['kode_mk'];
    $tanggal = $_POST['tanggal'];
    $pertemuan = $_POST['pertemuan_ke'];
    $status = $_POST['status'];
    
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM absensi WHERE kode_mk = ? AND pertemuan_ke = ?");
        $stmt->execute([$kode_mk, $pertemuan]);

        $stmt = $pdo->prepare("
            INSERT INTO absensi (kode_mk, nim, tanggal, status, pertemuan_ke)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($status as $nim => $stat) {
            $stmt->execute([$kode_mk, $nim, $tanggal, $stat, $pertemuan]);
        }

        $pdo->commit();
        setAlert('success', 'Absensi berhasil disimpan!');
        header("Location: absensi.php?kode_mk={$kode_mk}");
        exit();
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        setAlert('danger', 'Gagal menyimpan absensi!');
    }
}

include '../includes/header.php';
?>

<style>
    .card-simkampus {
        background: #fff;
        padding: 25px;
        border-radius: 15px;
        margin-top: 25px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h2.title {
        font-size: 26px;
        font-weight: bold;
        color: #3d4db7;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    h3.section-title {
        color: #3d4db7;
        font-weight: 600;
        margin-bottom: 10px;
        font-size: 20px;
    }
    table.sim-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        background: #fff;
    }
    table.sim-table th, table.sim-table td {
        border: 1px solid #ddd;
        padding: 10px;
    }
    table.sim-table th {
        background: #f5f6ff;
        font-weight: bold;
    }
    .badge-success {
        background: #28a745;
        padding: 5px 10px;
        color: white;
        border-radius: 8px;
    }
    .badge-warning {
        background: #ffc107;
        padding: 5px 10px;
        color: black;
        border-radius: 8px;
    }
    .badge-danger {
        background: #dc3545;
        padding: 5px 10px;
        color: white;
        border-radius: 8px;
    }
</style>

<div class="card-simkampus">
    <h2 class="title">📘 Input Absensi Mahasiswa</h2>
    <p>Input dan rekap absensi mahasiswa per pertemuan</p>
</div>

<!-- Pilih Mata Kuliah -->
<div class="card-simkampus">
    <h3 class="section-title">Pilih Mata Kuliah</h3>

    <form method="GET" style="display:flex; gap:20px; align-items:flex-end;">
        <div style="flex:1;">
            <label>Mata Kuliah</label>
            <select name="kode_mk" class="form-control" required>
                <option value="">-- Pilih Mata Kuliah --</option>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE dosen_nip = ?");
                $stmt->execute([$nip]);
                while ($mk = $stmt->fetch()) {
                    $sel = (isset($_GET['kode_mk']) && $_GET['kode_mk'] == $mk['kode_mk']) ? 'selected' : '';
                    echo "<option value='{$mk['kode_mk']}' $sel>{$mk['kode_mk']} - {$mk['nama_mk']}</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>
</div>

<?php if (isset($_GET['kode_mk'])): ?>
<?php
$kode_mk = $_GET['kode_mk'];

$stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE kode_mk = ?");
$stmt->execute([$kode_mk]);
$mk_info = $stmt->fetch();

$stmt = $pdo->prepare("SELECT MAX(pertemuan_ke) AS max FROM absensi WHERE kode_mk = ?");
$stmt->execute([$kode_mk]);
$max_pertemuan = $stmt->fetch()['max'] ?? 0;
?>

<div class="card-simkampus">
    <h3 class="section-title"><?= $mk_info['nama_mk'] ?></h3>
    <p>Kode: <?= $mk_info['kode_mk'] ?> | SKS: <?= $mk_info['sks'] ?></p>
</div>

<!-- Form Input Absensi -->
<div class="card-simkampus">
    <h3 class="section-title">Input Absensi Pertemuan</h3>

    <form method="POST">
        <input type="hidden" name="kode_mk" value="<?= $kode_mk ?>">

        <div class="row">
            <div class="col-md-6">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
            </div>

            <div class="col-md-6">
                <label>Pertemuan Ke-</label>
                <input type="number" name="pertemuan_ke" class="form-control" value="<?= $max_pertemuan + 1; ?>" required>
            </div>
        </div>

        <table class="sim-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th style="text-align:center;">Status Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("
                    SELECT k.nim, m.nama
                    FROM kelas k
                    JOIN mahasiswa m ON k.nim = m.nim
                    WHERE k.kode_mk = ?
                    ORDER BY m.nama
                ");
                $stmt->execute([$kode_mk]);
                $no = 1;
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $row['nim'] ?></td>
                    <td><?= $row['nama'] ?></td>
                    <td style="text-align:center;">
                        <label><input type="radio" name="status[<?= $row['nim'] ?>]" value="hadir" required> Hadir</label>
                        <label><input type="radio" name="status[<?= $row['nim'] ?>]" value="izin"> Izin</label>
                        <label><input type="radio" name="status[<?= $row['nim'] ?>]" value="sakit"> Sakit</label>
                        <label><input type="radio" name="status[<?= $row['nim'] ?>]" value="alpha"> Alpha</label>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <button type="submit" name="simpan_absensi" class="btn btn-primary mt-3">✅ Simpan Absensi</button>
    </form>
</div>

<div class="card-simkampus">
    <h3 class="section-title">Rekap Absensi</h3>

    <table class="sim-table">
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>Total</th>
                <th>Persentase</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT 
                    m.nim, m.nama,
                    SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) AS hadir,
                    SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) AS izin,
                    SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) AS sakit,
                    SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) AS alpha,
                    COUNT(a.status) AS total
                FROM kelas k
                JOIN mahasiswa m ON k.nim = m.nim
                LEFT JOIN absensi a ON k.nim = a.nim AND a.kode_mk = ?
                WHERE k.kode_mk = ?
                GROUP BY m.nim, m.nama
                ORDER BY m.nama
            ");
            $stmt->execute([$kode_mk, $kode_mk]);

            $no = 1;
            while ($row = $stmt->fetch()):
                $persen = ($row['total'] > 0) ? ($row['hadir'] / $row['total']) * 100 : 0;
                $badge = $persen >= 75 ? 'badge-success' : ($persen >= 50 ? 'badge-warning' : 'badge-danger');
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $row['nim'] ?></td>
                <td><?= $row['nama'] ?></td>
                <td><?= $row['hadir'] ?></td>
                <td><?= $row['izin'] ?></td>
                <td><?= $row['sakit'] ?></td>
                <td><?= $row['alpha'] ?></td>
                <td><?= $row['total'] ?></td>
                <td><span class="<?= $badge ?>"><?= number_format($persen,1) ?>%</span></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php endif; ?>

<a href="index.php" class="btn btn-primary mt-3">← Kembali ke Dashboard</a>

<?php include '../includes/footer.php'; ?>
