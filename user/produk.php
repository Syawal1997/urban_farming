<?php
session_start();
require_once '../../config/functions.php';

if (!isLoggedIn() || !isPenjual()) {
    redirect('login.php');
}

// Tambah produk
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_produk'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $kategori = $_POST['kategori'];
    $penjual_id = $_SESSION['user_id'];
    
    // Upload gambar
    $gambar = 'default.jpg';
    if ($_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], '../../assets/images/' . $gambar);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO produk (id_penjual, nama, deskripsi, harga, stok, kategori, gambar) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$penjual_id, $nama, $deskripsi, $harga, $stok, $kategori, $gambar]);
    
    $_SESSION['success'] = 'Produk berhasil ditambahkan!';
    redirect('produk.php');
}

// Hapus produk
if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT gambar FROM produk WHERE id = ? AND id_penjual = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $produk = $stmt->fetch();
    
    if ($produk) {
        // Hapus gambar jika bukan default
        if ($produk['gambar'] !== 'default.jpg') {
            @unlink('../../assets/images/' . $produk['gambar']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM produk WHERE id = ? AND id_penjual = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        
        $_SESSION['success'] = 'Produk berhasil dihapus!';
    }
    redirect('produk.php');
}

// Ambil produk penjual
$stmt = $pdo->prepare("SELECT * FROM produk WHERE id_penjual = ?");
$stmt->execute([$_SESSION['user_id']]);
$produk = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HTML sama seperti dashboard.php dengan konten berikut: -->
<div class="col-md-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Kelola Produk</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahProdukModal">
            <i class="bi bi-plus"></i> Tambah Produk
        </button>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (empty($produk)): ?>
        <div class="alert alert-info">Anda belum memiliki produk.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produk as $item): ?>
                        <tr>
                            <td>
                                <img src="../../assets/images/<?= $item['gambar'] ?: 'default.jpg' ?>" width="50" class="img-thumbnail">
                            </td>
                            <td><?= $item['nama'] ?></td>
                            <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                            <td><?= $item['stok'] ?></td>
                            <td>
                                <a href="edit_produk.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="produk.php?action=hapus&id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="tambahProdukModal" tabindex="-1" aria-labelledby="tambahProdukModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahProdukModalLabel">Tambah Produk Baru</h5>
                <button type="button" class="btn-close" data-bs-close="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="harga" class="form-label">Harga</label>
                            <input type="number" class="form-control" id="harga" name="harga" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="stok" name="stok" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="kategori" class="form-label">Kategori</label>
                        <input type="text" class="form-control" id="kategori" name="kategori">
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Produk</label>
                        <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="tambah_produk" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>