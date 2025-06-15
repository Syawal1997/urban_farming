<?php
require_once 'database.php';

function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPenjual() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'penjual';
}

function isPembeli() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'pembeli';
}

function getProduk() {
    global $pdo;
    $stmt = $pdo->query("SELECT produk.*, users.nama as nama_penjual FROM produk JOIN users ON produk.id_penjual = users.id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProdukById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>