<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../../auth/login-admin.php');
    exit;
}

require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? '';

if (empty($id) || !in_array($status, ['aktif', 'nonaktif'])) {
    $_SESSION['error'] = 'Parameter tidak valid';
    header('Location: ../menu_admin.php');
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE menu SET status = ? WHERE id_menu = ?");
    $stmt->execute([$status, $id]);
    
    $_SESSION['success'] = 'Status menu berhasil diubah';
    header('Location: ../menu_admin.php');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Gagal mengubah status: ' . $e->getMessage();
    header('Location: ../menu_admin.php');
    exit;
}
?>