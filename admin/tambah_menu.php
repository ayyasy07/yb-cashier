<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../auth/login-admin.php');
    exit;
}
require_once '../config/database.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu · YOBE</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: #faf8ff;
            display: flex;
            min-height: 100vh;
            color: #2d2a3a;
        }

        .sidebar {
            width: 280px;
            background: #ffffff;
            height: 100vh;
            position: fixed;
            padding: 2rem 1.5rem;
            border-right: 1px solid #f0eaff;
            box-shadow: 4px 0 15px rgba(94, 84, 142, 0.05);
            display: flex;
            flex-direction: column;
        }

        .brand {
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f5f0ff;
        }

        .brand-main {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #5e548e, #be95c4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
            line-height: 1.2;
            display: block;
        }

        .brand-sub {
            font-size: 0.7rem;
            color: #9a8cbb;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
            padding-left: 2px;
            display: block;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.9rem 1.2rem;
            color: #6b6580;
            text-decoration: none;
            border-radius: 14px;
            margin-bottom: 0.4rem;
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-item i {
            width: 22px;
            font-size: 1.1rem;
        }

        .nav-item:hover {
            background: #f5f0ff;
            color: #5e548e;
        }

        .nav-item.active {
            background: #5e548e;
            color: white;
            box-shadow: 0 6px 12px rgba(94, 84, 142, 0.2);
        }

        .nav-item.active i {
            color: white;
        }

        .logout-wrapper {
            margin-top: auto;
            border-top: 2px solid #f0eaff;
            padding-top: 1.5rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0.9rem 1.2rem;
            color: #e06b6b;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .logout-btn i {
            width: 22px;
        }

        .logout-btn:hover {
            background: #fff1f1;
            color: #c94f4f;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem 2.5rem;
            background: #faf8ff;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            background: linear-gradient(135deg, #2d2a3a, #5e548e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-title i {
            margin-right: 12px;
            color: #be95c4;
        }

        .btn-back {
            background: white;
            color: #5e548e;
            border: 1px solid #f0eaff;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-back i {
            font-size: 0.9rem;
        }

        .btn-back:hover {
            border-color: #5e548e;
            background: #f5f0ff;
            transform: translateX(-3px);
        }

        /* ===== FORM TAMBAH MENU ===== */
        .form-container {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #f0eaff;
            padding: 2.5rem;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(94, 84, 142, 0.08);
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #5e548e;
            margin-bottom: 0.5rem;
        }

        .form-label i {
            margin-right: 6px;
            color: #be95c4;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid #f0eaff;
            border-radius: 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: #faf8ff;
        }

        .form-input:hover,
        .form-select:hover,
        .form-textarea:hover {
            border-color: #be95c4;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #5e548e;
            box-shadow: 0 0 0 4px rgba(94, 84, 142, 0.1);
            background: white;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .radio-group {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #5e548e;
        }

        .status-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.aktif {
            background: #e4f3e5;
            color: #2f8f5c;
        }

        .status-badge.nonaktif {
            background: #fff1f0;
            color: #e06b6b;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2.5rem;
            padding-top: 1.8rem;
            border-top: 2px solid #f5f0ff;
        }

        .btn-primary {
            background: #5e548e;
            color: white;
            border: none;
            padding: 0.9rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 6px 16px rgba(94, 84, 142, 0.25);
        }

        .btn-primary i {
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            background: #4e457a;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(94, 84, 142, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #6b6580;
            border: 2px solid #f0eaff;
            padding: 0.9rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-secondary i {
            font-size: 0.9rem;
        }

        .btn-secondary:hover {
            border-color: #e06b6b;
            color: #e06b6b;
            background: #fff1f1;
        }

        .info-box {
            background: #f5f0ff;
            border-radius: 16px;
            padding: 1.2rem;
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-icon {
            width: 36px;
            height: 36px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5e548e;
            font-size: 1rem;
        }

        .info-text {
            color: #5e548e;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .info-text a {
            color: #be95c4;
            text-decoration: none;
            font-weight: 600;
        }

        .info-text a:hover {
            text-decoration: underline;
        }

        /* Berry accent */
        .berry-accent {
            display: flex;
            gap: 6px;
            margin-top: 2rem;
            justify-content: flex-end;
        }

        .berry-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #be95c4;
        }

        .berry-dot:nth-child(2) {
            background: #5e548e;
        }

        .berry-dot:nth-child(3) {
            background: #6b8cae;
        }

        .berry-dot:nth-child(4) {
            background: #e06b6b;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">
            <div class="brand-main">yobe</div>
            <div class="brand-sub">YOGURT & BERRY BAR</div>
        </div>

        <nav>
            <a href="dashboard.php" class="nav-item"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="menu_admin.php" class="nav-item active"><i class="fas fa-ice-cream"></i> Menu</a>
            <a href="laporan.php" class="nav-item"><i class="fas fa-file-alt"></i> Laporan</a>
            <a href="user_setting.php" class="nav-item"><i class="fas fa-cog"></i> Pengaturan</a>
        </nav>

        <div class="logout-wrapper">
            <a href="#" class="logout-btn" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h1 class="page-title"><i class="fas fa-plus-circle"></i> Tambah Menu Baru</h1>
            <a href="menu_admin.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Form Tambah Menu -->
        <div class="form-container">
            <form action="proses/proses_tambah_menu.php" method="POST">
                <!-- Nama Menu -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-tag"></i> Nama Menu <span style="color: #e06b6b;">*</span></label>
                    <input type="text" class="form-input" name="nama_menu" placeholder="Contoh: Classic Berry Yogurt" required>
                </div>

                <!-- Kategori Dropdown -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-folder"></i> Kategori</label>
                    <select class="form-select" name="id_kategori" required>
                        <option value="">Pilih Kategori</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
                        while($kategori = $stmt->fetch()) {
                            echo '<option value="' . $kategori['id_kategori'] . '">' . ucfirst($kategori['nama_kategori']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                    <div class="form-group">
                        <label class="form-label"><i class="fas fa-coin"></i> Harga (Rp) <span style="color: #e06b6b;">*</span></label>
                        <input type="number" class="form-input" name="harga" placeholder="14000" min="0" required>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-circle-check"></i> Status</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="status" value="aktif" checked> 
                            <span class="status-badge aktif">Aktif</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="status" value="nonaktif"> 
                            <span class="status-badge nonaktif">Nonaktif</span>
                        </label>
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Deskripsi Menu</label>
                    <textarea class="form-textarea" name="deskripsi" placeholder="Jelaskan tentang menu ini..."></textarea>
                </div>

                <!-- Tombol Action -->
                <div class="form-actions">
                    <a href="menu_admin.php" class="btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i> Simpan Menu
                    </button>
                </div>
            </form>
        </div>

        <!-- Berry accent -->
        <div class="berry-accent">
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
        </div>
    </div>

    <script>
        function confirmLogout() {
            if (confirm('Yakin ingin keluar?')) {
                window.location.href = '../auth/logout.php';
            }
            return false;
        }

    </script>
</body>
</html>