<?php
session_start();
if (!isset($_SESSION['kasir_login'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../config/database.php';

// Ambil data JSON dari request
$input = json_decode(file_get_contents('php://input'), true);

// Validasi input
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid: ' . json_last_error_msg()]);
    exit;
}

// Ambil data dari input
$customer_name = $input['customer_name'] ?? '';
$payment_method = $input['payment_method'] ?? '';
$payment_amount = $input['payment_amount'] ?? 0;
$cart_items = $input['cart_items'] ?? [];
$subtotal = $input['subtotal'] ?? 0;

// Validasi data wajib
if (empty($customer_name)) {
    echo json_encode(['success' => false, 'message' => 'Nama pemesan harus diisi']);
    exit;
}

if (empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Metode pembayaran harus dipilih']);
    exit;
}

if (empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja kosong']);
    exit;
}

if ($subtotal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Total belanja tidak valid']);
    exit;
}

// Untuk cash, validasi jumlah bayar
if ($payment_method === 'cash' && $payment_amount < $subtotal) {
    echo json_encode(['success' => false, 'message' => 'Uang tidak cukup']);
    exit;
}

try {
    // Mulai transaksi database
    $pdo->beginTransaction();
    
    // Hitung kembalian (untuk cash)
    $kembalian = ($payment_method === 'cash') ? $payment_amount - $subtotal : 0;
    
    // Simpan ke tabel orders
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            nama_pemesan, 
            total_harga, 
            metode_pembayaran, 
            bayar, 
            kembalian, 
            created_by,
            tanggal
        ) VALUES (
            ?, ?, ?, ?, ?, ?, NOW()
        )
    ");
    
    $bayar = ($payment_method === 'cash') ? $payment_amount : 0;
    
    $stmt->execute([
        $customer_name,
        $subtotal,
        $payment_method,
        $bayar,
        $kembalian,
        $_SESSION['kasir_id']
    ]);
    
    $order_id = $pdo->lastInsertId();
    
    // Simpan detail order
    $stmt_detail = $pdo->prepare("
        INSERT INTO detail_order (
            id_orders, 
            id_menu, 
            nama_menu_snapshot, 
            harga_snapshot, 
            qty, 
            catatan, 
            subtotal
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?
        )
    ");
    
    foreach ($cart_items as $item) {
        // Validasi setiap item
        if (!isset($item['id'], $item['name'], $item['price'], $item['qty'])) {
            throw new Exception('Data item tidak lengkap');
        }
        
        $item_subtotal = $item['price'] * $item['qty'];
        $catatan = $item['note'] ?? '';
        
        $stmt_detail->execute([
            $order_id,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['qty'],
            $catatan,
            $item_subtotal
        ]);
    }
    
    // Commit transaksi
    $pdo->commit();
    
    // Kirim respons sukses
    echo json_encode([
        'success' => true,
        'message' => 'Order berhasil disimpan',
        'order_id' => $order_id,
        'order_number' => '#ORD' . str_pad($order_id, 3, '0', STR_PAD_LEFT)
    ]);
    
} catch (PDOException $e) {
    // Rollback jika ada error database
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Rollback jika ada error lain
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>