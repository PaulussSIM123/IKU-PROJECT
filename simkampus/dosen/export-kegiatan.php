<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['dosen', 'admin']);

// Set header untuk download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=kegiatan_mahasiswa_' . date('Y-m-d') . '.csv');

// Output file
$output = fopen('php://output', 'w');

// BOM untuk UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header CSV
fputcsv($output, [
    'No',
    'Tanggal',
    'NIM',
    'Nama Mahasiswa',
    'Jurusan',
    'Nama Kegiatan',
    'Jenis Kegiatan',
    'Deskripsi',
    'Poin',
    'Status'
]);

// Get data
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

$stmt = $pdo->prepare("
    SELECT 
        k.*,
        m.nama,
        m.nim,
        m.jurusan
    FROM kegiatan k
    JOIN mahasiswa m ON k.nim = m.nim
    WHERE {$where}
    ORDER BY k.tanggal_mulai DESC
");
$stmt->execute($params);

$no = 1;
while ($row = $stmt->fetch()) {
    fputcsv($output, [
        $no,
        date('d/m/Y', strtotime($row['tanggal_mulai'])),
        $row['nim'],
        $row['nama'],
        $row['jurusan'],
        $row['nama_kegiatan'],
        ucfirst($row['jenis_kegiatan']),
        $row['deskripsi'],
        $row['poin'],
        ucfirst($row['status'])
    ]);
    $no++;
}

fclose($output);
exit();
?>