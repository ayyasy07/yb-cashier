<?php
session_start();
require_once '../config/database.php';

// Hapus session error sebelumnya
unset($_SESSION['error']);

// Cek apakah form dikirim
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_kasir.php');
    exit;
}

$email = $_POST['email_login'] ?? '';
$password = $_POST['password_login'] ?? '';

// Validasi input
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email dan password harus diisi';
    header('Location: login_kasir.php');
    exit;
}

try {
    // Cari user dengan email dan role kasir
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'kasir'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    // Cek apakah user ditemukan dan password cocok
    if ($user && password_verify($password, $user['password'])) {
        // Login sukses
        $_SESSION['kasir_id'] = $user['id_users'];
        $_SESSION['kasir_nama'] = $user['nama'];
        $_SESSION['kasir_email'] = $user['email'];
        $_SESSION['kasir_login'] = true;
        
        // Redirect ke halaman order
        header('Location: ../kasir/order.php');
        exit;
    } else {
        // Login gagal
        $_SESSION['error'] = 'Email atau password salah';
        header('Location: login_kasir.php');
        exit;
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    header('Location: login_kasir.php');
    exit;
}
?>