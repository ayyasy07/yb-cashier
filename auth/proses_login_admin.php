<?php
session_start();
require_once '../config/database.php';

// Hapus error sebelumnya
unset($_SESSION['error']);

// Cek apakah form dikirim dengan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login_admin.php');
    exit;
}

// Ambil data dari form
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi input tidak boleh kosong
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email dan password harus diisi';
    header('Location: login_admin.php');
    exit;
}

try {
    // Cari user dengan email tersebut DAN role = 'admin'
    $sql = "SELECT * FROM users WHERE email = :email AND role = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    
    // Cek apakah user ditemukan DAN password cocok
    if ($user && password_verify($password, $user['password'])) {
        // Login sukses
        $_SESSION['admin_id'] = $user['id_users'];
        $_SESSION['admin_nama'] = $user['nama'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_login'] = true;
        
        // Redirect ke dashboard
        header('Location: ../admin/dashboard.php');
        exit;
    } else {
        // Login gagal
        $_SESSION['error'] = 'Email atau password salah';
        header('Location: login_admin.php');
        exit;
    }
    
} catch (PDOException $e) {
    // Error database
    $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    header('Location: login_admin.php');
    exit;
}
?>