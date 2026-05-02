<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../../auth/login_admin.php');
    exit;
}

require_once '../../config/database.php';

// Cek apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../tambah_menu.php');
    exit;
}

// Ambil data dari form
$nama_menu = $_POST['nama_menu'] ?? '';
$id_kategori = $_POST['id_kategori'] ?? '';
$harga = $_POST['harga'] ?? 0;
$status = $_POST['status'] ?? 'aktif';
$deskripsi = $_POST['deskripsi'] ?? '';

// Validasi data
if (empty($nama_menu) || empty($id_kategori) || empty($harga)) {
    $_SESSION['error'] = 'Semua field wajib diisi';
    header('Location: ../tambah_menu.php');
    exit;
}

try {
    // Simpan ke database
    $stmt = $pdo->prepare("
        INSERT INTO menu (nama_menu, id_kategori, harga, deskripsi, status, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$nama_menu, $id_kategori, $harga, $deskripsi, $status]);
    
    $_SESSION['success'] = 'Menu berhasil ditambahkan';
    header('Location: ../menu_admin.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menambahkan menu: ' . $e->getMessage();
    header('Location: ../tambah_menu.php');
    exit;
}
?>