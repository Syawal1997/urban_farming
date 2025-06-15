<?php
session_start();
require_once 'config/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, no_hp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $no_hp, $alamat, $role]);
        
        $_SESSION['success'] = 'Pendaftaran berhasil! Silakan login.';
        redirect('login.php');
    } catch (PDOException $e) {
        $error = 'Email sudah terdaftar!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Head sama seperti index.php -->
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <!-- Navbar sama seperti index.php -->
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Daftar Akun</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" required>
                            </div>
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Daftar sebagai</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="role" id="pembeli" value="pembeli" checked>
                                        <label class="form-check-label" for="pembeli">Pembeli</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="role" id="penjual" value="penjual">
                                        <label class="form-check-label" for="penjual">Penjual</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Daftar</button>
                        </form>
                        <div class="mt-3 text-center">
                            Sudah punya akun? <a href="login.php">Login disini</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer sama seperti index.php -->
</body>
</html>