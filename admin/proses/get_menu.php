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
    echo json_encode(['success' => false, 'message' => 'ID menu tidak ditemukan']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id_menu = ?");
    $stmt->execute([$id]);
    $menu = $stmt->fetch();
    
    if ($menu) {
        echo json_encode(['success' => true, 'data' => $menu]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Menu tidak ditemukan']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>