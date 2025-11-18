<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$page_title = 'Nilai Kuliah';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

include '../includes/header.php';
?>

<div class="card">
    <h2>📝 Daftar Nilai Mata Kuliah</h2>
    <p>Menampilkan semua nilai mata kuliah yang sudah diambil</p>
</div>

<!-- Filter Semester -->
<div class="card">
    <form method="GET" style="display: flex; gap: 15px; align-items: end;">
        <div class="form-group" style="margin: 0; flex: 1;">
            <label>Filter Semester</label>
            <select name="semester">
                <option value="">Semua Semester</option>
                <option value="Ganjil">Ganjil</option>
                <option value="Genap">Genap</option>
            </select>
        </div>
        <div class="form-group" style="margin: 0; flex: 1;">
            <label>Tahun Ajaran</label>
            <input type="text" name="tahun" placeholder="2021/2022">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<!-- Tabel Nilai -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode MK</th>
                <th>Mata Kuliah</th>
                <th>SKS</th>
                <th>Semester</th>
                <th>Tahun Ajaran</th>
                <th>Tugas</th>
                <th>UTS</th>
                <th>UAS</th>
                <th>Nilai Akhir</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $where = "WHERE k.nim = ?";
            $params = [$nim];
            
            if (isset($_GET['semester']) && !empty($_GET['semester'])) {
                $where .= " AND k.semester = ?";
                $params[] = $_GET['semester'];
            }
            
            if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
                $where .= " AND k.tahun_ajaran = ?";
                $params[] = $_GET['tahun'];
            }
            
            $stmt = $pdo->prepare("
                SELECT k.*, m.nama_mk, m.sks 
                FROM kelas k 
                JOIN mata_kuliah m ON k.kode_mk = m.kode_mk 
                {$where}
                ORDER BY k.tahun_ajaran DESC, k.semester
            ");
            $stmt->execute($params);
            
            $no = 1;
            $total_sks = 0;
            $total_nilai = 0;
            
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch()) {
                    $total_sks += $row['sks'];
                    $total_nilai += $row['nilai_akhir'] * $row['sks'];
                    
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td>{$row['kode_mk']}</td>";
                    echo "<td>{$row['nama_mk']}</td>";
                    echo "<td>{$row['sks']}</td>";
                    echo "<td>{$row['semester']}</td>";
                    echo "<td>{$row['tahun_ajaran']}</td>";
                    echo "<td>{$row['nilai_tugas']}</td>";
                    echo "<td>{$row['nilai_uts']}</td>";
                    echo "<td>{$row['nilai_uas']}</td>";
                    echo "<td><strong>{$row['nilai_akhir']}</strong></td>";
                    echo "<td><span class='badge badge-success'>{$row['grade']}</span></td>";
                    echo "</tr>";
                    $no++;
                }
                
                // Hitung IPK
                $ipk = $total_sks > 0 ? $total_nilai / $total_sks : 0;
                
                echo "<tr style='background: #f0f0f0; font-weight: bold;'>";
                echo "<td colspan='3'>TOTAL / IPK</td>";
                echo "<td>{$total_sks}</td>";
                echo "<td colspan='6'></td>";
                echo "<td>" . hitungIPK($ipk) . "</td>";
                echo "</tr>";
            } else {
                echo "<tr><td colspan='11' class='text-center'>Belum ada data nilai</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Keterangan Grade -->
<div class="card">
    <h3>Keterangan Grade</h3>
    <table style="width: 50%;">
        <tr><td><span class="badge badge-success">A</span></td><td>85 - 100</td></tr>
        <tr><td><span class="badge badge-success">A-</span></td><td>80 - 84</td></tr>
        <tr><td><span class="badge badge-success">B+</span></td><td>75 - 79</td></tr>
        <tr><td><span class="badge badge-success">B</span></td><td>70 - 74</td></tr>
        <tr><td><span class="badge badge-warning">B-</span></td><td>65 - 69</td></tr>
        <tr><td><span class="badge badge-warning">C+</span></td><td>60 - 64</td></tr>
        <tr><td><span class="badge badge-warning">C</span></td><td>55 - 59</td></tr>
        <tr><td><span class="badge badge-danger">D</span></td><td>50 - 54</td></tr>
        <tr><td><span class="badge badge-danger">E</span></td><td>< 50</td></tr>
    </table>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>