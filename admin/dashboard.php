<?php
session_start();
require_once '../../config/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Hitung jumlah data
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_penjual = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'penjual'")->fetchColumn();
$total_pembeli = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'pembeli'")->fetchColumn();
$total_produk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$total_transaksi = $pdo->query("SELECT COUNT(*) FROM transaksi")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<!-- Head sama seperti index.php -->
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <!-- Navbar sama seperti index.php -->
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-2">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Menu Admin
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                        <a href="users.php" class="list-group-item list-group-item-action">Pengguna</a>
                        <a href="produk.php" class="list-group-item list-group-item-action">Produk</a>
                        <a href="transaksi.php" class="list-group-item list-group-item-action">Transaksi</a>
                        <a href="../logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <h3>Dashboard Admin</h3>
                <p>Selamat datang, <?= $_SESSION['nama'] ?>!</p>
                
                <div class="row mt-4">
                    <div class="col-md-4 mb-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pengguna</h5>
                                <h2><?= $total_users ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Penjual</h5>
                                <h2><?= $total_penjual ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Pembeli</h5>
                                <h2><?= $total_pembeli ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Total Produk</h5>
                                <h2><?= $total_produk ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Transaksi</h5>
                                <h2><?= $total_transaksi ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer sama seperti index.php -->
</body>
</html>