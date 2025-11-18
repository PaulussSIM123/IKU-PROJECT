<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['mahasiswa']);

$user_data = getUserData($pdo, $_SESSION['user_id'], 'mahasiswa');
$nim = $user_data['nim'];

$semester_aktif = isset($_GET['semester']) ? $_GET['semester'] : 'Ganjil';
$tahun_ajaran_aktif = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y') . '/' . (date('Y') + 1);

// Get KRS data
$stmt = $pdo->prepare("
    SELECT k.*, mk.nama_mk, mk.sks, mk.semester as semester_mk, d.nama as nama_dosen
    FROM kelas k
    JOIN mata_kuliah mk ON k.kode_mk = mk.kode_mk
    LEFT JOIN dosen d ON mk.dosen_nip = d.nip
    WHERE k.nim = ? AND k.semester = ? AND k.tahun_ajaran = ?
    ORDER BY mk.semester, mk.kode_mk
");
$stmt->execute([$nim, $semester_aktif, $tahun_ajaran_aktif]);
$krs_data = $stmt->fetchAll();

// Hitung total SKS
$total_sks = 0;
foreach ($krs_data as $row) {
    $total_sks += $row['sks'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak KRS - <?php echo $nim; ?></title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        
        .header h2 {
            margin: 5px 0;
            font-size: 24px;
            color: #667eea;
        }
        
        .info-mahasiswa {
            margin: 20px 0;
            border: 2px solid #667eea;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-row {
            display: flex;
            margin: 5px 0;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        
        .info-value {
            flex: 1;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        table th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
            border: 1px solid #333;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #333;
        }
        
        table tfoot td {
            font-weight: bold;
            background: #f0f0f0;
        }
        
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            margin-top: 80px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        
        .btn-print {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        
        .btn-print:hover {
            background: #5568d3;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        
        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" class="btn-print">🖨️ Cetak KRS</button>
    <a href="krs.php" class="btn-back">← Kembali</a>
</div>

<div class="header">
    <h1>Universitas / Institut / Politeknik</h1>
    <h2>KARTU RENCANA STUDI (KRS)</h2>
    <p>Semester <?php echo $semester_aktif; ?> Tahun Ajaran <?php echo $tahun_ajaran_aktif; ?></p>
</div>

<div class="info-mahasiswa">
    <div class="info-row">
        <div class="info-label">NIM</div>
        <div class="info-value">: <?php echo $user_data['nim']; ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Nama</div>
        <div class="info-value">: <?php echo $user_data['nama']; ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Jurusan</div>
        <div class="info-value">: <?php echo $user_data['jurusan']; ?></div>
    </div>
    <div class="info-row">
        <div class="info-label">Angkatan</div>
        <div class="info-value">: <?php echo $user_data['angkatan']; ?></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 100px;">Kode MK</th>
            <th>Nama Mata Kuliah</th>
            <th style="width: 60px;">SKS</th>
            <th style="width: 80px;">Semester</th>
            <th>Dosen Pengampu</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        if (count($krs_data) > 0) {
            foreach ($krs_data as $row) {
                echo "<tr>";
                echo "<td style='text-align: center;'>{$no}</td>";
                echo "<td>{$row['kode_mk']}</td>";
                echo "<td>{$row['nama_mk']}</td>";
                echo "<td style='text-align: center;'>{$row['sks']}</td>";
                echo "<td style='text-align: center;'>{$row['semester_mk']}</td>";
                echo "<td>" . ($row['nama_dosen'] ?? '-') . "</td>";
                echo "</tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='6' style='text-align: center;'>Belum ada mata kuliah yang diambil</td></tr>";
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align: right;">TOTAL SKS:</td>
            <td style="text-align: center;"><?php echo $total_sks; ?></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<div style="margin: 20px 0; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107;">
    <strong>Catatan:</strong>
    <ul style="margin: 10px 0 0 20px;">
        <li>Mahasiswa wajib mengikuti seluruh mata kuliah yang tercantum dalam KRS ini</li>
        <li>Perubahan KRS hanya dapat dilakukan pada periode yang telah ditentukan</li>
        <li>KRS yang telah dicetak harus disimpan sebagai bukti pengambilan mata kuliah</li>
    </ul>
</div>

<div class="footer">
    <div class="signature">
        <p>Mengetahui,</p>
        <p><strong>Dosen Pembimbing Akademik</strong></p>
        <div class="signature-line">
            (..................................)
        </div>
    </div>
    
    <div class="signature">
        <p>Pekanbaru, <?php echo date('d F Y'); ?></p>
        <p><strong>Mahasiswa</strong></p>
        <div class="signature-line">
            <?php echo $user_data['nama']; ?>
        </div>
    </div>
</div>

<div class="no-print" style="text-align: center; margin-top: 30px;">
    <button onclick="window.print()" class="btn-print">🖨️ Cetak KRS</button>
    <a href="krs.php" class="btn-back">← Kembali</a>
</div>

</body>
</html>