<?php
session_start();

// Matikan error display agar tidak mengganggu JSON
error_reporting(0);
ini_set('display_errors', 0);

if (!isset($_SESSION['kasir_login'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

$customer_name = $_POST['customer_name'] ?? '';
$subtotal = $_POST['subtotal'] ?? 0;
$cart_items_json = $_POST['cart_items'] ?? '';

if (empty($customer_name)) {
    echo json_encode(['success' => false, 'message' => 'Nama customer harus diisi']);
    exit;
}

if (empty($cart_items_json)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang kosong']);
    exit;
}

$cart_items = json_decode($cart_items_json, true);
if (!is_array($cart_items) || count($cart_items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Data keranjang tidak valid']);
    exit;
}

// Buat folder jika belum ada
$target_dir = "../../uploads/bukti/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Upload file bukti
$file_name = null;
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($ext, $allowed) && $_FILES['bukti']['size'] <= 2000000) {
        $file_name = time() . '_' . basename($_FILES['bukti']['name']);
        move_uploaded_file($_FILES['bukti']['tmp_name'], $target_dir . $file_name);
    }
}

try {
    $pdo->beginTransaction();
    
    // Simpan ke orders
    $stmt = $pdo->prepare("
        INSERT INTO orders (nama_pemesan, total_harga, metode_pembayaran, bayar, kembalian, created_by, bukti_pembayaran) 
        VALUES (?, ?, 'qris', 0, 0, ?, ?)
    ");
    $stmt->execute([$customer_name, $subtotal, $_SESSION['kasir_id'], $file_name]);
    $order_id = $pdo->lastInsertId();
    
    // Simpan detail order
    $stmt_detail = $pdo->prepare("
        INSERT INTO detail_order (id_orders, id_menu, nama_menu_snapshot, harga_snapshot, qty, catatan, subtotal) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($cart_items as $item) {
        $item_subtotal = $item['price'] * $item['qty'];
        $stmt_detail->execute([
            $order_id,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['qty'],
            $item['note'] ?? '',
            $item_subtotal
        ]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order_number' => '#ORD' . str_pad($order_id, 3, '0', STR_PAD_LEFT)
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>