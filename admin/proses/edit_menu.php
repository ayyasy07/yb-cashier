<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../../auth/login_admin.php');
    exit;
}

require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../menu_admin.php');
    exit;
}

$id_menu = $_POST['id_menu'] ?? 0;
$nama_menu = $_POST['nama_menu'] ?? '';
$id_kategori = $_POST['id_kategori'] ?? '';
$harga = $_POST['harga'] ?? 0;
$deskripsi = $_POST['deskripsi'] ?? '';
$status = $_POST['status'] ?? 'aktif';

if (empty($id_menu) || empty($nama_menu) || empty($id_kategori) || empty($harga)) {
    $_SESSION['error'] = 'Semua field wajib diisi';
    header('Location: ../menu_admin.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE menu 
        SET nama_menu = ?, id_kategori = ?, harga = ?, deskripsi = ?, status = ?
        WHERE id_menu = ?
    ");
    $stmt->execute([$nama_menu, $id_kategori, $harga, $deskripsi, $status, $id_menu]);
    
    $_SESSION['success'] = 'Menu berhasil diperbarui';
    header('Location: ../menu_admin.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal memperbarui menu: ' . $e->getMessage();
    header('Location: ../menu_admin.php');
    exit;
}
?>