<?php
require_once __DIR__ . '/config/database.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = "dosen";

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Semua field wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak sama!";
    } else {

        try {

            $pdo->beginTransaction();

            $hashed = md5($password);

            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed, $role]);

            $user_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO dosen (nip, nama, jurusan, email, no_hp, user_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_POST['nip'],
                $_POST['nama'],
                $_POST['jurusan'],
                $_POST['email'],
                $_POST['no_hp'],
                $user_id
            ]);

            $pdo->commit();
            header("Location: index.php?registered=dosen");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Dosen | SIMKAMPUS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#eef5ff; }
        .box {
            margin-top: 50px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0px 0px 20px rgba(0,0,0,0.1);
        }
        .title {
            font-size: 26px;
            font-weight: 800;
            color:#198754;
            text-align:center;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="box">

                <div class="title mb-3">Daftar Dosen</div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">

                    <label class="mt-2">Username</label>
                    <input type="text" name="username" class="form-control" required>

                    <label class="mt-2">Password</label>
                    <input type="password" name="password" class="form-control" required>

                    <label class="mt-2">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>

                    <hr>

                    <label class="mt-2">NIP</label>
                    <input type="text" name="nip" class="form-control" required>

                    <label class="mt-2">Nama</label>
                    <input type="text" name="nama" class="form-control" required>

                    <label class="mt-2">Jurusan</label>
                    <input type="text" name="jurusan" class="form-control" required>

                    <label class="mt-2">Email</label>
                    <input type="email" name="email" class="form-control">

                    <label class="mt-2">No HP</label>
                    <input type="text" name="no_hp" class="form-control">

                    <button class="btn btn-success w-100 mt-4">Daftar</button>

                    <div class="text-center mt-3">
                        <a href="register.php">Kembali</a>
                    </div>

                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>
