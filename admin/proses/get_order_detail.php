<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.nama as nama_kasir 
        FROM orders o
        LEFT JOIN users u ON o.created_by = u.id_users
        WHERE o.id_orders = ?
    ");
    $stmt->execute([$id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order tidak ditemukan']);
        exit;
    }
    
    $order['tanggal'] = date('d/m/Y H:i', strtotime($order['tanggal']));
    
    $stmt = $pdo->prepare("SELECT * FROM detail_order WHERE id_orders = ?");
    $stmt->execute([$id]);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>