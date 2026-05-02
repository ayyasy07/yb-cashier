<?php
session_start();
if (!isset($_SESSION['kasir_login'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID menu tidak ditemukan']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_menu, nama_menu, harga FROM menu WHERE id_menu = ? AND status = 'aktif'");
    $stmt->execute([$id]);
    $menu = $stmt->fetch();
    
    if ($menu) {
        echo json_encode([
            'success' => true,
            'id' => $menu['id_menu'],
            'nama' => $menu['nama_menu'],
            'harga' => $menu['harga']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan atau tidak aktif']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>