<?php
require_once '../config/database.php';

// Reset paksa password admin
$email = 'admin@yobe.com';
$password_baru = 'admin123';
$hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

// Update database
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$result = $stmt->execute([$hash_baru, $email]);

if ($result) {
    echo "✅ Password admin BERHASIL direset!<br>";
    echo "Email: $email<br>";
    echo "Password baru: <strong>$password_baru</strong><br>";
    echo "Hash baru: $hash_baru<br><br>";
    
    // Verifikasi langsung
    $cek = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $cek->execute([$email]);
    $hash_baru_db = $cek->fetchColumn();
    
    $verif = password_verify($password_baru, $hash_baru_db);
    echo "Verifikasi setelah reset: " . ($verif ? "✅ BERHASIL" : "❌ GAGAL") . "<br><br>";
    
    echo "<a href='login_admin.php'><button>Login Sekarang</button></a>";
} else {
    echo "❌ Gagal mereset password";
}
?>