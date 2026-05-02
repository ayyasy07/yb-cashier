<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../../auth/login_admin.php');
    exit;
}

require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    $_SESSION['error'] = 'ID menu tidak ditemukan';
    header('Location: ../menu_admin.php');
    exit;
}

try {
    // Cek apakah menu sudah pernah dipesan
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM detail_order WHERE id_menu = ?");
    $stmt->execute([$id]);
    $used = $stmt->fetch()['total'];
    
    if ($used > 0) {
        $_SESSION['error'] = 'Menu tidak dapat dihapus karena sudah pernah dipesan';
        header('Location: ../menu_admin.php');
        exit;
    }
    
    // Hapus menu
    $stmt = $pdo->prepare("DELETE FROM menu WHERE id_menu = ?");
    $stmt->execute([$id]);
    
    $_SESSION['success'] = 'Menu berhasil dihapus';
    header('Location: ../menu_admin.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal menghapus menu: ' . $e->getMessage();
    header('Location: ../menu_admin.php');
    exit;
}
?>