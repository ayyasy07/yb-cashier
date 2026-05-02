<?php
require_once '../config/database.php';

echo "<h2>Test Koneksi Database YOBE</h2>";

// Cek koneksi
echo "✅ Koneksi database berhasil!<br><br>";

// Cek tabel users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total = $stmt->fetch()['total'];
echo "Total users di database: " . $total . "<br><br>";

// Tampilkan semua user
$users = $pdo->query("SELECT id_users, email, nama, role FROM users")->fetchAll();
echo "<h3>Data Users:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Email</th><th>Nama</th><th>Role</th></tr>";
foreach ($users as $user) {
    echo "<tr>";
    echo "<td>" . $user['id_users'] . "</td>";
    echo "<td>" . $user['email'] . "</td>";
    echo "<td>" . $user['nama'] . "</td>";
    echo "<td>" . $user['role'] . "</td>";
    echo "</tr>";
}
echo "</table><br>";

// Test verifikasi password admin
echo "<h3>Test Password:</h3>";
$test_password = 'admin123';
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['admin@yobe.com']);
$admin = $stmt->fetch();

if ($admin) {
    $cocok = password_verify($test_password, $admin['password']);
    echo "Email admin: admin@yobe.com<br>";
    echo "Password test: $test_password<br>";
    echo "Hash di DB: " . $admin['password'] . "<br>";
    echo "Hasil verifikasi: " . ($cocok ? "✅ COCOK" : "❌ TIDAK COCOK") . "<br>";
} else {
    echo "❌ User admin@yobe.com tidak ditemukan!";
}
?>