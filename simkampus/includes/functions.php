<?php
/**
 * File Functions Helper
 * Berisi fungsi-fungsi yang sering digunakan
 */

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cek role user
function checkRole($allowed_roles = []) {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit();
    }
    
    if (!empty($allowed_roles) && !in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../index.php");
        exit();
    }
}

// Get user data by role
function getUserData($pdo, $user_id, $role) {
    try {
        if ($role == 'mahasiswa') {
            $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE user_id = ?");
        } elseif ($role == 'dosen') {
            $stmt = $pdo->prepare("SELECT * FROM dosen WHERE user_id = ?");
        } else {
            return ['username' => $_SESSION['username']];
        }
        
        $stmt->execute([$user_id]);
        return $stmt->fetch();
        
    } catch (PDOException $e) {
        return null;
    }
}

// Hitung IPK dari nilai
function hitungIPK($nilai_akhir) {
    return number_format($nilai_akhir / 25, 2);
}

// Konversi nilai ke grade
function nilaiToGrade($nilai) {
    if ($nilai >= 85) return 'A';
    if ($nilai >= 80) return 'A-';
    if ($nilai >= 75) return 'B+';
    if ($nilai >= 70) return 'B';
    if ($nilai >= 65) return 'B-';
    if ($nilai >= 60) return 'C+';
    if ($nilai >= 55) return 'C';
    if ($nilai >= 50) return 'D';
    return 'E';
}

// Format tanggal Indonesia
function formatTanggal($tanggal) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $split = explode('-', $tanggal);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

// Sanitize input
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Alert message
function setAlert($type, $message) {
    $_SESSION['alert_type'] = $type;
    $_SESSION['alert_message'] = $message;
}

function showAlert() {
    if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
        $type = $_SESSION['alert_type'];
        $message = $_SESSION['alert_message'];
        
        echo "<div class='alert alert-{$type}'>{$message}</div>";
        
        unset($_SESSION['alert_type']);
        unset($_SESSION['alert_message']);
    }
}

// Generate NIM otomatis
function generateNIM($pdo, $tahun) {
    $stmt = $pdo->prepare("SELECT nim FROM mahasiswa WHERE nim LIKE ? ORDER BY nim DESC LIMIT 1");
    $stmt->execute([$tahun . '%']);
    $last = $stmt->fetch();
    
    if ($last) {
        $number = (int)substr($last['nim'], 4) + 1;
    } else {
        $number = 1;
    }
    
    return $tahun . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>