<?php
$host = 'sql200.infinityfree.com'; // Ganti dengan hostname Anda
$dbname = 'if0_36123456_urbanfar_db'; // Ganti dengan nama database
$username = 'if0_36123456_urbanuser'; // Ganti dengan username
$password = ''; // Ganti dengan password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>