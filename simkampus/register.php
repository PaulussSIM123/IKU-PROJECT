<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Registrasi | SIMKAMPUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#eef5ff; }
        .box {
            margin-top: 100px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .title { font-size: 26px; font-weight: 800; color:#0d6efd; }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="box">
                <div class="title">SIMKAMPUS</div>
                <p class="text-secondary mb-4">Pilih jenis pendaftaran akun</p>

                <!-- ✅ LINK BENAR -->
                <a href="register_mahasiswa.php" class="btn btn-primary w-100 mb-3">
                    Daftar Mahasiswa
                </a>

                <a href="register_dosen.php" class="btn btn-success w-100 mb-3">
                    Daftar Dosen
                </a>

                <a href="index.php" class="d-block mt-3 text-secondary">
                    Sudah Punya Akun? Kembali Login
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
