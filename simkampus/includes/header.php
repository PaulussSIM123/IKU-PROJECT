<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>SIM-Kampus</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="navbar">
    <h1>📚 SIM-Kampus</h1>
    <div class="user-info">
        <?php if (isset($_SESSION['username'])): ?>
            <span>
        <?= $_SESSION['username']; ?> 
        (<?= ucfirst($_SESSION['role']); ?>)
    </span>

    <?php if ($_SESSION['role'] == 'mahasiswa' || $_SESSION['role'] == 'dosen'): ?>
        <!-- TOMBOL PROFIL HANYA MHS & DOSEN -->
        <a href="profil.php">👤 Profil</a>
    <?php endif; ?>

    <!-- LOGOUT SELALU ADA UNTUK SEMUA ROLE -->
    <a href="../logout.php">Logout</a>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <?php
    // Tampilkan alert jika ada
    if (function_exists('showAlert')) {
        showAlert();
    }
    ?>