<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: {$role}/index.php");
    exit();
}

$error = '';

// Proses Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            // Cek password (gunakan password_verify di production)
            if ($user && md5($password) === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect ke dashboard sesuai role
                header("Location: {$user['role']}/index.php");
                exit();
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIM-Kampus</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <h1>📚 SIM-Kampus</h1>
        <p>Sistem Informasi Manajemen Kampus</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">
            Login
        </button>
    </form>
    
    <div style="margin-top: 20px; text-align: center; padding-top: 20px; border-top: 1px solid #ddd;">
        <p style="color: #666;">Belum punya akun? 
            <a href="register.php" style="color: #667eea; font-weight: bold; text-decoration: none;">Daftar di sini</a>
        </p>
    </div>
    
    <div style="margin-top: 15px; text-align: center; color: #666; font-size: 12px; padding: 15px; background: #f9f9f9; border-radius: 5px;">
        <p style="font-weight: bold; margin-bottom: 10px;">Demo Account:</p>
        <p>👨‍💼 Admin: <strong>admin</strong> / admin123</p>
        <p>👨‍🏫 Dosen: <strong>dosen1</strong> / dosen123</p>
        <p>👨‍🎓 Mahasiswa: <strong>mhs001</strong> / mhs123</p>
    </div>
</div>

</body>
</html>