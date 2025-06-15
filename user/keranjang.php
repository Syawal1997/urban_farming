<?php
session_start();
require_once '../../config/functions.php';

if (!isLoggedIn() || !isPembeli()) {
    redirect('login.php');
}

// Tambah ke keranjang
if (isset($_GET['action']) && $_GET['action'] === 'add_to_cart' && isset($_GET['id'])) {
    $produk_id = $_GET['id'];
    $pembeli_id = $_SESSION['user_id'];
    
    // Cek apakah produk sudah ada di keranjang
    $stmt = $pdo->prepare("SELECT * FROM keranjang WHERE id_pembeli = ? AND id_produk = ?");
    $stmt->execute([$pembeli_id, $produk_id]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Update jumlah jika sudah ada
        $stmt = $pdo->prepare("UPDATE keranjang SET jumlah = jumlah + 1 WHERE id = ?");
        $stmt->execute([$item['id']]);
    } else {
        // Tambah baru jika belum ada
        $stmt = $pdo->prepare("INSERT INTO keranjang (id_pembeli, id_produk, jumlah) VALUES (?, ?, 1)");
        $stmt->execute([$pembeli_id, $produk_id]);
    }
    
    $_SESSION['success'] = 'Produk berhasil ditambahkan ke keranjang!';
    redirect('keranjang.php');
}

// Hapus dari keranjang
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id = ? AND id_pembeli = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $_SESSION['success'] = 'Produk berhasil dihapus dari keranjang!';
    redirect('keranjang.php');
}

// Update jumlah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['jumlah'] as $id => $jumlah) {
        if ($jumlah <= 0) {
            $stmt = $pdo->prepare("DELETE FROM keranjang WHERE id = ? AND id_pembeli = ?");
            $stmt->execute([$id, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ? AND id_pembeli = ?");
            $stmt->execute([$jumlah, $id, $_SESSION['user_id']]);
        }
    }
    $_SESSION['success'] = 'Keranjang berhasil diperbarui!';
    redirect('keranjang.php');
}

// Ambil data keranjang
$stmt = $pdo->prepare("
    SELECT k.*, p.nama, p.harga, p.gambar, p.stok 
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

<!-- HTML sama seperti dashboard.php dengan konten berikut: -->
<div class="col-md-9">
    <h3>Keranjang Belanja</h3>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (empty($keranjang)): ?>
        <div class="alert alert-info">Keranjang belanja Anda kosong.</div>
    <?php else: ?>
        <form method="POST">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keranjang as $item): ?>
                        <tr>
                            <td>
                                <img src="../../assets/images/<?= $item['gambar'] ?: 'default.jpg' ?>" width="50" class="me-2">
                                <?= $item['nama'] ?>
                            </td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td>
                                <input type="number" name="jumlah[<?= $item['id'] ?>]" value="<?= $item['jumlah'] ?>" min="1" max="<?= $item['stok'] ?>" class="form-control" style="width: 70px;">
                            </td>
                            <td>Rp <?= number_format($item['harga'] * $item['jumlah'], 0, ',', '.') ?></td>
                            <td>
                                <a href="keranjang.php?action=remove&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th colspan="2">Rp <?= number_format($total, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="d-flex justify-content-between">
                <button type="submit" name="update_cart" class="btn btn-outline-success">Perbarui Keranjang</button>
                <a href="../../checkout.php" class="btn btn-success">Lanjut ke Pembayaran</a>
            </div>
        </form>
    <?php endif; ?>
</div>