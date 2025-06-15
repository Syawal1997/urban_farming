<?php
session_start();
require_once 'config/functions.php';

if (!isLoggedIn() || !isPembeli()) {
    redirect('login.php');
}

// Proses checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $pembeli_id = $_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        // 1. Ambil data keranjang
        $stmt = $pdo->prepare("
            SELECT k.*, p.nama, p.harga, p.stok 
            FROM keranjang k 
            JOIN produk p ON k.id_produk = p.id 
            WHERE k.id_pembeli = ?
        ");
        $stmt->execute([$pembeli_id]);
        $keranjang = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($keranjang)) {
            throw new Exception("Keranjang belanja kosong!");
        }
        
        // 2. Hitung total
        $total = 0;
        foreach ($keranjang as $item) {
            $total += $item['harga'] * $item['jumlah'];
            
            // Cek stok
            if ($item['stok'] < $item['jumlah']) {
                throw new Exception("Stok produk '{$item['nama']}' tidak mencukupi!");
            }
        }
        
        // 3. Buat transaksi
        $stmt = $pdo->prepare("
            INSERT INTO transaksi (id_pembeli, total, metode_pembayaran, status) 
            VALUES (?, ?, ?, 'menunggu_pembayaran')
        ");
        $stmt->execute([$pembeli_id, $total, $metode_pembayaran]);
        $transaksi_id = $pdo->lastInsertId();
        
        // 4. Buat detail transaksi dan update stok
        foreach ($keranjang as $item) {
            // Detail transaksi
            $stmt = $pdo->prepare("
                INSERT INTO detail_transaksi (id_transaksi, id_produk, jumlah, harga) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$transaksi_id, $item['id_produk'], $item['jumlah'], $item['harga']]);
            
            // Update stok
            $stmt = $pdo->prepare("UPDATE produk SET stok = stok - ? WHERE id = ?");
            $stmt->execute([$item['jumlah'], $item['id_produk']]);
        }
        
        // 5. Kosongkan keranjang
        $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id_pembeli = ?");
        $stmt->execute([$pembeli_id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.';
        redirect('user/pesanan.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Ambil data keranjang untuk ditampilkan
$stmt = $pdo->prepare("
    SELECT k.*, p.nama, p.harga, p.gambar 
    FROM keranjang k 
    JOIN produk p ON k.id_produk = p.id 
    WHERE k.id_pembeli = ?
");
$stmt->execute([$_SESSION['user_id']]);
$keranjang = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total
$total = 0;
foreach ($keranjang as $item) {
    $total += $item['harga'] * $item['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<!-- Head sama seperti index.php -->
<body>
    <!-- Navbar sama seperti index.php -->

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Checkout</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (empty($keranjang)): ?>
                            <div class="alert alert-info">Keranjang belanja Anda kosong.</div>
                            <a href="produk.php" class="btn btn-success">Belanja Sekarang</a>
                        <?php else: ?>
                            <h5>Ringkasan Pesanan</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($keranjang as $item): ?>
                                        <tr>
                                            <td><?= $item['nama'] ?></td>
                                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                            <td><?= $item['jumlah'] ?></td>
                                            <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th>Rp <?= number_format($total, 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                            
                            <h5 class="mt-4">Metode Pembayaran</h5>
                            <form method="POST">
                                <div class="mb-3">
                                    <select class="form-select" name="metode_pembayaran" required>
                                        <option value="">Pilih Metode Pembayaran</option>
                                        <option value="transfer_bank">Transfer Bank</option>
                                        <option value="e_wallet">E-Wallet (OVO, GoPay, Dana)</option>
                                        <option value="cod">Cash on Delivery (COD)</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">Buat Pesanan</button>
                                    <a href="user/keranjang.php" class="btn btn-outline-secondary">Kembali ke Keranjang</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer sama seperti index.php -->
</body>
</html>