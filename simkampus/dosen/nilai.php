<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

checkRole(['dosen']);

$page_title = 'Input Nilai';
$user_data = getUserData($pdo, $_SESSION['user_id'], 'dosen');
$nip = $user_data['nip'];

// Proses Update Nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_nilai'])) {
    $kelas_id = $_POST['kelas_id'];
    $nilai_tugas = $_POST['nilai_tugas'];
    $nilai_uts = $_POST['nilai_uts'];
    $nilai_uas = $_POST['nilai_uas'];
    
    // Hitung nilai akhir (30% Tugas + 30% UTS + 40% UAS)
    $nilai_akhir = ($nilai_tugas * 0.3) + ($nilai_uts * 0.3) + ($nilai_uas * 0.4);
    $grade = nilaiToGrade($nilai_akhir);
    
    try {
        $stmt = $pdo->prepare("
            UPDATE kelas 
            SET nilai_tugas = ?, nilai_uts = ?, nilai_uas = ?, nilai_akhir = ?, grade = ?
            WHERE id = ?
        ");
        $stmt->execute([$nilai_tugas, $nilai_uts, $nilai_uas, $nilai_akhir, $grade, $kelas_id]);
        
        setAlert('success', 'Nilai berhasil diupdate!');
        header("Location: nilai.php?kode_mk=" . $_POST['kode_mk']);
        exit();
        
    } catch (PDOException $e) {
        setAlert('danger', 'Gagal mengupdate nilai!');
    }
}

include '../includes/header.php';
?>

<div class="card">
    <h2>📝 Input Nilai Mahasiswa</h2>
    <p>Input dan update nilai mahasiswa</p>
</div>

<!-- Pilih Mata Kuliah -->
<div class="card">
    <h3>Pilih Mata Kuliah</h3>
    <form method="GET">
        <div style="display: flex; gap: 15px; align-items: end;">
            <div class="form-group" style="flex: 1; margin: 0;">
                <label>Mata Kuliah</label>
                <select name="kode_mk" required>
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE dosen_nip = ?");
                    $stmt->execute([$nip]);
                    while ($mk = $stmt->fetch()) {
                        $selected = (isset($_GET['kode_mk']) && $_GET['kode_mk'] == $mk['kode_mk']) ? 'selected' : '';
                        echo "<option value='{$mk['kode_mk']}' {$selected}>{$mk['kode_mk']} - {$mk['nama_mk']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
    </form>
</div>

<?php if (isset($_GET['kode_mk'])): ?>
    <?php
    $kode_mk = $_GET['kode_mk'];
    
    // Get mata kuliah info
    $stmt = $pdo->prepare("SELECT * FROM mata_kuliah WHERE kode_mk = ?");
    $stmt->execute([$kode_mk]);
    $mk_info = $stmt->fetch();
    ?>
    
    <div class="card">
        <h3><?php echo $mk_info['nama_mk']; ?></h3>
        <p>Kode: <?php echo $mk_info['kode_mk']; ?> | SKS: <?php echo $mk_info['sks']; ?></p>
    </div>
    
    <!-- Tabel Nilai Mahasiswa -->
    <div class="table-container">
        <h3>Daftar Nilai Mahasiswa</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama Mahasiswa</th>
                    <th>Tugas (30%)</th>
                    <th>UTS (30%)</th>
                    <th>UAS (40%)</th>
                    <th>Nilai Akhir</th>
                    <th>Grade</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->prepare("
                    SELECT k.*, m.nama 
                    FROM kelas k
                    JOIN mahasiswa m ON k.nim = m.nim
                    WHERE k.kode_mk = ?
                    ORDER BY m.nama
                ");
                $stmt->execute([$kode_mk]);
                
                $no = 1;
                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch()) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>{$row['nim']}</td>";
                        echo "<td>{$row['nama']}</td>";
                        echo "<td>{$row['nilai_tugas']}</td>";
                        echo "<td>{$row['nilai_uts']}</td>";
                        echo "<td>{$row['nilai_uas']}</td>";
                        echo "<td><strong>{$row['nilai_akhir']}</strong></td>";
                        echo "<td><span class='badge badge-success'>{$row['grade']}</span></td>";
                        echo "<td>";
                        echo "<button onclick='editNilai({$row['id']}, \"{$row['nim']}\", \"{$row['nama']}\", {$row['nilai_tugas']}, {$row['nilai_uts']}, {$row['nilai_uas']})' class='btn btn-warning'>Edit</button>";
                        echo "</td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>Belum ada mahasiswa yang mengambil mata kuliah ini</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal Edit Nilai  -->
<div id="modalEditNilai" aria-hidden="true"
     style="display:none; position:fixed; z-index:2000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.45);">
  <div id="modalBox" role="dialog"
       style="background:white; margin:60px auto; padding:24px; width:90%; max-width:520px; border-radius:10px; box-shadow:0 6px 24px rgba(0,0,0,0.2);">
    <h3 style="margin-bottom:16px;">Edit Nilai</h3>

    <form id="formEditNilai" method="POST" novalidate>
      <input type="hidden" name="kelas_id" id="edit_kelas_id">
      <input type="hidden" name="kode_mk" value="<?php echo isset($kode_mk) ? htmlspecialchars($kode_mk) : ''; ?>">

      <div class="form-group">
        <label>NIM</label>
        <input type="text" id="edit_nim" readonly style="background:#f5f5f5; width:100%;">
      </div>

      <div class="form-group">
        <label>Nama Mahasiswa</label>
        <input type="text" id="edit_nama" readonly style="background:#f5f5f5; width:100%;">
      </div>

      <div class="form-group">
        <label>Nilai Tugas (30%)</label>
        <input type="number" name="nilai_tugas" id="edit_tugas" min="0" max="100" step="0.01" required style="width:100%;">
      </div>

      <div class="form-group">
        <label>Nilai UTS (30%)</label>
        <input type="number" name="nilai_uts" id="edit_uts" min="0" max="100" step="0.01" required style="width:100%;">
      </div>

      <div class="form-group">
        <label>Nilai UAS (40%)</label>
        <input type="number" name="nilai_uas" id="edit_uas" min="0" max="100" step="0.01" required style="width:100%;">
      </div>

      <div style="display:flex; gap:10px; margin-top:12px;">
        <button type="submit" name="update_nilai" class="btn btn-primary">Simpan</button>
        <!-- pastikan type="button" supaya tidak submit -->
        <button type="button" id="btnCancelEdit" class="btn btn-secondary">Batal</button>
      </div>
    </form>
  </div>
</div>

<style>
/* (opsional) animasi kecil */
@keyframes modalFadeIn { from{opacity:0; transform:translateY(-6px)} to{opacity:1; transform:none} }
#modalBox { animation: modalFadeIn .18s ease; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('modalEditNilai');
  const modalBox = document.getElementById('modalBox');
  const btnCancel = document.getElementById('btnCancelEdit');
  const form = document.getElementById('formEditNilai');

  // safety checks
  if (!modal || !modalBox || !btnCancel) {
    console.error('Modal elements not found:', { modal: !!modal, modalBox: !!modalBox, btnCancel: !!btnCancel });
    return;
  }

  // open function (you already call editNilai(...) from PHP-generated buttons)
  window.editNilai = function (id, nim, nama, tugas, uts, uas) {
    document.getElementById('edit_kelas_id').value = id;
    document.getElementById('edit_nim').value = nim;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_tugas').value = tugas ?? '';
    document.getElementById('edit_uts').value = uts ?? '';
    document.getElementById('edit_uas').value = uas ?? '';

    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');

    // focus first numeric input
    document.getElementById('edit_tugas').focus();
  };

  // close function
  function closeModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');

    // optional: reset form or remove values
    // form.reset();
  }

  // attach events
  btnCancel.addEventListener('click', function (e) {
    e.preventDefault();
    closeModal();
  });

  // click outside modalBox -> close
  modal.addEventListener('click', function (e) {
    // if clicked directly on the overlay (modal), not modalBox or its children
    if (e.target === modal) {
      closeModal();
    }
  });

  // close with ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.style.display === 'block') {
      closeModal();
    }
  });

  // debug helper: log JS errors to console (so you can see issues)
  window.addEventListener('error', function (ev) {
    console.error('JS error:', ev.error || ev.message);
  });
});
</script>



<div class="mt-20">
    <a href="index.php" class="btn btn-primary">← Kembali ke Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>