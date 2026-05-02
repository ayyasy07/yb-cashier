<?php
// 1. Koneksi ke database yobe_db
$conn = new mysqli("localhost", "root", "", "yobe_db");

// Cek koneksi
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

// 2. Buat hash password untuk admin & kasir
$admin_pass = password_hash('adminyobe123', PASSWORD_BCRYPT);
$kasir_pass = password_hash('kasiryobe01', PASSWORD_BCRYPT);

// 3. Insert user ke tabel users
$sql = "INSERT INTO users (nama, email, password, role) VALUES
('Admin Yobe','admin@yobe.com','$admin_pass','admin'),
('Kasir Yobe','kasir@yobe.com','$kasir_pass','kasir')";

// Jalankan query
if($conn->query($sql) === TRUE){
    echo "Users berhasil dibuat";
} else {
    echo "Error: " . $conn->error;
}

// 4. Tutup koneksi
$conn->close();
?>