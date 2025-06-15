<?php
session_start();
require_once 'config/functions.php';

if (!isLoggedIn() || !isPembeli()) {
    redirect('login.php');
}

// Proses pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaksi_id = $_POST['transaksi_id'];
    $bukti_pembayaran = $_FILES['bukti_pembayaran'];
    
    // Validasi transaksi
    $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ? AND id_pembeli = ? AND status = 'menunggu_pembayaran'");
    $stmt->execute([$transaksi_id, $_SESSION['user_id']]);
    $transaksi = $stmt->fetch();
    
    if (!$transaksi) {
        $_SESSION['error'] = 'Transaksi tidak valid!';
        redirect('user/pesanan.php');
    }
    
    // Upload bukti pembayaran
    if ($bukti_pembayaran['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($bukti_pembayaran['name'], PATHINFO_EXTENSION);
        $nama_file = 'bukti_' . $transaksi_id . '_' . time() . '.' . $ext;
        $tujuan = 'assets/bukti_pembayaran/' . $nama_file;
        
        if (move_uploaded_file($bukti_pembayaran['tmp_name'], $tujuan)) {
            // Update status transaksi
            $stmt = $pdo->prepare("UPDATE transaksi SET status = 'diproses', bukti_pembayaran = ? WHERE id = ?");
            $stmt->execute([$nama_file, $transaksi_id]);
            
            $_SESSION['success'] = 'Bukti pembayaran berhasil diupload! Pesanan akan segera diproses.';
            redirect('user/pesanan.php');
        } else {
            $_SESSION['error'] = 'Gagal mengupload bukti pembayaran!';
        }
    } else {
        $_SESSION['error'] = 'Silakan pilih file bukti pembayaran!';
    }
    
    redirect('proses_pembayaran.php?transaksi_id=' . $transaksi_id);
}

$transaksi_id = $_GET['transaksi_id'] ?? 0;

// Validasi transaksi
$stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = ? AND id_pembeli = ? AND status = 'menunggu_pembayaran'");
$stmt->execute([$transaksi_id, $_SESSION['user_id']]);
$transaksi = $stmt->fetch();

if (!$transaksi) {
    $_SESSION['error'] = 'Transaksi tidak valid!';
    redirect('user/pesanan.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<!-- Head sama seperti index.php -->
<body>
    <!-- Navbar sama seperti index.php -->

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Proses Pembayaran</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <h5>Instruksi Pembayaran</h5>
                        <div class="alert alert-info">
                            <?php if ($transaksi['metode_pembayaran'] === 'transfer_bank'): ?>
                                <p>Silakan transfer ke rekening berikut:</p>
                                <p><strong>Bank:</strong> BCA</p>
                                <p><strong>No. Rekening:</strong> 1234567890</p>
                                <p><strong>Atas Nama:</strong> Urban Farming Marketplace</p>
                                <p><strong>Jumlah:</strong> Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></p>
                            <?php elseif ($transaksi['metode_pembayaran'] === 'e_wallet'): ?>
                                <p>Silakan transfer ke e-wallet berikut:</p>
                                <p><strong>Jenis:</strong> OVO/GoPay/Dana</p>
                                <p><strong>No. Telepon:</strong> 081234567890</p>
                                <p><strong>Atas Nama:</strong> Urban Farming Marketplace</p>
                                <p><strong>Jumlah:</strong> Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></p>
                            <?php else: ?>
                                <p>Anda memilih pembayaran COD. Silakan siapkan uang tunai sebesar:</p>
                                <p><strong>Jumlah:</strong> Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></p>
                                <p>Pembayaran dilakukan saat produk diterima.</p>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="transaksi_id" value="<?= $transaksi['id'] ?>">
                            
                            <?php if ($transaksi['metode_pembayaran'] !== 'cod'): ?>
                                <div class="mb-3">
                                    <label for="bukti_pembayaran" class="form-label">Upload Bukti Pembayaran</label>
                                    <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*,.pdf" required>
                                    <small class="text-muted">Format: JPG, PNG, PDF (maks. 2MB)</small>
                                </div>
                                
                                <button type="submit" class="btn btn-success w-100">Konfirmasi Pembayaran</button>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    Tidak perlu upload bukti untuk pembayaran COD.
                                </div>
                                <a href="user/pesanan.php" class="btn btn-success w-100">Kembali ke Pesanan</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer sama seperti index.php -->
</body>
</html>