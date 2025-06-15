<?php
session_start();
require_once '../../config/functions.php';

if (!isLoggedIn() || !isPembeli()) {
    redirect('login.php');
}

// Ambil data produk untuk ditampilkan
$produk = getProduk();
?>

<!DOCTYPE html>
<html lang="id">
<!-- Head sama seperti index.php -->
<body>
    <!-- Navbar sama seperti index.php -->

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Menu Pembeli
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                        <a href="keranjang.php" class="list-group-item list-group-item-action">Keranjang Belanja</a>
                        <a href="pesanan.php" class="list-group-item list-group-item-action">Pesanan Saya</a>
                        <a href="profil.php" class="list-group-item list-group-item-action">Profil Saya</a>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <h3>Selamat datang, <?= $_SESSION['nama'] ?>!</h3>
                <p>Ini adalah dashboard pembeli urban farming.</p>
                
                <h4 class="mt-4">Produk Terbaru</h4>
                <div class="row">
                    <?php foreach ($produk as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="../../assets/images/<?= $item['gambar'] ?: 'default.jpg' ?>" class="card-img-top" alt="<?= $item['nama'] ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= $item['nama'] ?></h5>
                                    <p class="card-text"><?= substr($item['deskripsi'], 0, 100) ?>...</p>
                                    <p class="text-success fw-bold">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                    <a href="../../produk.php?action=detail&id=<?= $item['id'] ?>" class="btn btn-sm btn-success">Detail</a>
                                    <a href="../../produk.php?action=add_to_cart&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline-success">+ Keranjang</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer sama seperti index.php -->
</body>
</html>