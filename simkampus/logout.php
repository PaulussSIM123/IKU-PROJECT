<?php
/**
 * Logout Page
 * Menghapus session dan redirect ke login
 */

session_start();

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: index.php");
exit();
?>