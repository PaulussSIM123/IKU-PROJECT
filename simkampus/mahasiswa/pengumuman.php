<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$page_title = 'Pengumuman Mahasiswa';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');

include '../includes/header.php';
?>

<div class="card">
    <h2>📢 Pengumuman Mahasiswa</h2>
    <p>Daftar pengumuman terbaru dari pihak kampus.</p>
</div>

<!-- Tabel Pengumuman -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Isi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->prepare("
                SELECT id, judul, isi, tanggal
                FROM pengumuman
                ORDER BY tanggal DESC, id DESC
            ");
            $stmt->execute();

            $no = 1;

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>{$no}</td>";
                    echo "<td><strong>" . htmlspecialchars($row['judul']) . "</strong></td>";
                    echo "<td>" . date('d/m/Y', strtotime($row['tanggal'])) . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($row['isi'])) . "</td>";
                    echo "</tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>Belum ada pengumuman</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>
