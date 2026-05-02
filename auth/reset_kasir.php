<?php
require_once '../config/database.php';

// Reset password kasir
$email = 'kasir@yobe.com';
$password_baru = 'kasir123';
$hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ? AND role = 'kasir'");
$stmt->execute([$hash_baru, $email]);

echo "✅ Password kasir telah direset menjadi: $password_baru";
echo "<br><a href='login_kasir.php'>Kembali ke Login</a>";
?>