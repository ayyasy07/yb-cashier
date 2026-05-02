<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

require_once '../config/database.php';

// Ambil semua menu dengan nama kategori
try {
    $stmt = $pdo->query("
        SELECT 
            menu.id_menu,
            menu.nama_menu,
            menu.harga,
            menu.deskripsi,
            menu.status,
            menu.created_at,
            kategori.id_kategori,
            kategori.nama_kategori
        FROM menu 
        JOIN kategori ON menu.id_kategori = kategori.id_kategori 
        ORDER BY menu.created_at DESC
    ");
    $menus = $stmt->fetchAll();

    // Ambil semua kategori untuk filter
    $stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori");
    $kategori_list = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Gagal mengambil data menu: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu · YOBE</title>
    <!-- Font Awesome 6 Free -->
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

        /* Main Content */
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

        .btn-add {
            background: #5e548e;
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            box-shadow: 0 8px 16px rgba(94, 84, 142, 0.2);
            text-decoration: none;
        }

        .btn-add i {
            font-size: 1rem;
        }

        .btn-add:hover {
            background: #4e457a;
            transform: translateY(-2px);
        }

        .alert {
            background: #fff1f1;
            border-left: 4px solid #e06b6b;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c94f4f;
        }

        .alert.success {
            background: #e4f3e5;
            border-left-color: #2f8f5c;
            color: #2f8f5c;
        }

        .alert i {
            font-size: 1.2rem;
        }

        .category-pills {
            display: flex;
            gap: 0.8rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .category-pill {
            padding: 0.5rem 1.5rem;
            background: white;
            border: 1px solid #e8e0ff;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #5e548e;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .category-pill i {
            font-size: 1rem;
        }

        .category-pill:hover {
            border-color: #5e548e;
            background: #f5f0ff;
        }

        .category-pill.active {
            background: #5e548e;
            color: white;
            border-color: #5e548e;
        }

        .category-pill.active i {
            color: white;
        }

        .search-wrapper {
            margin-bottom: 2rem;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9a8cbb;
        }

        .search-input {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 3rem;
            border: 2px solid #f0eaff;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.2s;
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: #5e548e;
            box-shadow: 0 0 0 4px rgba(94, 84, 142, 0.1);
        }

        .menu-list {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid #f0eaff;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(94, 84, 142, 0.08);
        }

        .menu-header {
            display: grid;
            grid-template-columns: 3fr 1.2fr 1fr 1.2fr 1.5fr;
            padding: 1.2rem 1.8rem;
            background: #f8f4ff;
            color: #6b6580;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f0eaff;
        }

        /* Header Aksi ditengah */
        .menu-header span:last-child {
            text-align: center;
        }

        .menu-row {
            display: grid;
            grid-template-columns: 3fr 1.2fr 1fr 1.2fr 1.5fr;
            padding: 1rem 1.8rem; /* Kurangi padding vertical */
            align-items: center;
            border-bottom: 1px solid #f5f0ff;
            transition: background 0.2s;
            min-height: 72px; /* Tinggi minimal konsisten */
        }

        .menu-row:hover {
            background: #faf8ff;
        }

        .menu-row:last-child {
            border-bottom: none;
        }

        /* Border pembatas antar kolom */
        .menu-header span,
        .menu-row span,
        .menu-row > div {
            align-items: center;
        }

        .menu-header span:last-child,
        .menu-row > div:last-child {
            border-right: none;
            padding-right: 0;
        }

        .menu-name {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .menu-icon {
            width: 40px;
            height: 40px;
            background: #f5f0ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5e548e;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .menu-category {
            display: inline-flex;
            align-items: center;  /* Ini untuk center vertikal */
            justify-content: center;
            gap: 6px;
            padding: 0.3rem 1rem;
            background: #f5f0ff;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #5e548e;
            width: fit-content;
            height: 32px; /* Tinggi tetap */
            line-height: 1;
        }

        .menu-category i {
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            margin-top: 0; /* Hilangkan margin yang mungkin ada */
        }

        .menu-price {
            font-weight: 600;
            color: #5e548e;
            display: flex;
            align-items: center;
            height: 100%;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.3rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 80px;
            height: 32px;
        }

        .status-badge.aktif {
            background: #e4f3e5;
            color: #2f8f5c;
        }

        .status-badge.nonaktif {
            background: #fff1f0;
            color: #e06b6b;
        }

        /* Kolom Aksi - konten di tengah */
        .menu-row > div:last-child {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .action-group {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        /* Tombol status dengan teks */
        .btn-status {
            padding: 0.3rem 0.8rem;
            border: 1px solid #f0eaff;
            background: white;
            border-radius: 30px;
            color: #6b6580;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 500;
            transition: all 0.2s;
            white-space: nowrap;
            min-width: 70px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            justify-content: center;
        }

        .btn-status i {
            font-size: 0.7rem;
        }

        .btn-status:hover {
            border-color: #5e548e;
            background: #f5f0ff;
            color: #5e548e;
        }

        /* Tombol icon (Edit & Hapus) */
        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            border: 1px solid #f0eaff;
            background: white;
            border-radius: 8px;
            color: #6b6580;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-icon i {
            font-size: 1rem;
        }

        .btn-icon:hover {
            border-color: #5e548e;
            background: #f5f0ff;
            color: #5e548e;
        }

        .btn-icon.delete:hover {
            border-color: #e06b6b;
            background: #fff1f1;
            color: #e06b6b;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .menu-header, .menu-row {
                grid-template-columns: 2.5fr 1.2fr 1fr 1.2fr 1.8fr;
            }
        }

        @media (max-width: 992px) {
            .menu-header, .menu-row {
                grid-template-columns: 2fr 1.2fr 1fr 1.2fr 2fr;
            }
            
            .action-group {
                flex-direction: column;
                gap: 0.3rem;
            }
            
            .btn-status {
                width: 100%;
            }
            
            .btn-icon {
                width: 100%;
                height: 36px;
            }
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #9a8cbb;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 32px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(94, 84, 142, 0.25);
        }

        .modal-content h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #2d2a3a;
        }

        .modal-content p {
            color: #9a8cbb;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #5e548e;
            font-size: 0.9rem;
        }

        .form-group input, 
        .form-group select, 
        .form-group textarea {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #f0eaff;
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-group input:focus, 
        .form-group select:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #be95c4;
        }

        .status-radio {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .status-radio label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            color: #6b6580;
        }

        .status-radio input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #5e548e;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-primary, .btn-secondary {
            flex: 1;
            padding: 0.9rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #5e548e;
            color: white;
        }

        .btn-primary:hover {
            background: #4e457a;
        }

        .btn-secondary {
            background: #f5f0ff;
            color: #5e548e;
        }

        .btn-secondary:hover {
            background: #e8e0ff;
        }

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
            <div class="brand-sub">yogurt & berry bar</div>
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
            <h1 class="page-title"><i class="fas fa-ice-cream"></i> Menu YOBE</h1>
            <a href="tambah_menu.php" class="btn-add">
                <i class="fas fa-plus"></i> Tambah Menu
            </a>
        </div>

        <!-- Alert -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <i class="fas fa-check-circle"></i>
            <span><?= $_SESSION['success'] ?></span>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $_SESSION['error'] ?></span>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $error ?></span>
        </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchMenu" placeholder="Cari menu...">
        </div>

        <!-- Category Pills -->
        <div class="category-pills" id="categoryPills">
            <div class="category-pill active" onclick="filterCategory('all')">
                <i class="fas fa-utensils"></i> Semua
            </div>
            <?php foreach ($kategori_list as $kategori): 
                // Tentukan icon untuk filter
                $icon_filter = 'fa-tag'; // default
                if ($kategori['nama_kategori'] == 'yogurt') {
                    $icon_filter = 'fa-ice-cream';
                } elseif ($kategori['nama_kategori'] == 'bar') {
                    $icon_filter = 'fa-cookie';
                } elseif ($kategori['nama_kategori'] == 'bowl') {
                    $icon_filter = 'fa-blender';
                }
            ?>
            <div class="category-pill" onclick="filterCategory('<?= $kategori['nama_kategori'] ?>')">
                <i class="fas <?= $icon_filter ?>"></i>
                <?= ucfirst($kategori['nama_kategori']) ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Menu List -->
        <div class="menu-list">
            <div class="menu-header">
                <span>Nama Menu</span>
                <span>Kategori</span>
                <span>Harga</span>
                <span>Status</span>
                <span>Aksi</span>
            </div>

            <?php if (empty($menus)): ?>
            <div class="empty-state">
                <i class="fas fa-ice-cream"></i>
                <p>Belum ada menu</p>
                <small>Klik "Tambah Menu" untuk menambahkan menu pertama</small>
            </div>
            <?php else: ?>
                <?php foreach ($menus as $menu): 
                    // Tentukan icon berdasarkan kategori
                    $icon = 'fa-utensils'; // default
                    if ($menu['nama_kategori'] == 'yogurt') {
                        $icon = 'fa-ice-cream';
                    } elseif ($menu['nama_kategori'] == 'bar') {
                        $icon = 'fa-cookie';
                    } elseif ($menu['nama_kategori'] == 'bowl') {
                        $icon = 'fa-blender';
                    }
                ?>
                <div class="menu-row" data-category="<?= $menu['nama_kategori'] ?>" data-name="<?= strtolower($menu['nama_menu']) ?>" data-desc="<?= strtolower($menu['deskripsi'] ?? '') ?>">
                    <!-- Nama Menu dengan Icon -->
                    <span class="menu-name">
                        <span class="menu-icon">
                            <i class="fas <?= $icon ?>"></i>
                        </span>
                        <?= htmlspecialchars($menu['nama_menu']) ?>
                    </span>
                    
                    <!-- Kategori -->
                    <span>
                        <span class="menu-category">
                            <i class="fas <?= $icon ?>"></i>
                            <?= ucfirst($menu['nama_kategori']) ?>
                        </span>
                    </span>
                    
                    <!-- Harga -->
                    <span class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></span>
                    
                    <!-- Status -->
                    <span>
                        <span class="status-badge <?= $menu['status'] ?>">
                            <?= $menu['status'] == 'aktif' ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </span>
                    
                    <!-- Aksi -->
                    <div>
                        <div class="action-group">
                            <!-- Tombol Status (Aktifkan/Nonaktifkan) dengan teks -->
                            <button class="btn-status" onclick="toggleStatus(<?= $menu['id_menu'] ?>, '<?= $menu['status'] ?>')">
                                <i class="fas fa-<?= $menu['status'] == 'aktif' ? 'ban' : 'check' ?>"></i>
                                <?= $menu['status'] == 'aktif' ? 'Nonaktif' : 'Aktifkan' ?>
                            </button>
                            
                            <!-- Tombol Edit (icon saja) -->
                            <button class="btn-icon" onclick="editMenu(<?= $menu['id_menu'] ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Tombol Hapus (icon saja) -->
                            <button class="btn-icon delete" onclick="deleteMenu(<?= $menu['id_menu'] ?>)" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Berry accent -->
        <div class="berry-accent">
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
        </div>
    </div>

    <!-- Modal Edit Menu -->
    <div class="modal" id="editMenuModal">
        <div class="modal-content">
            <h3><i class="fas fa-edit"></i> Edit Menu</h3>
            <p>Ubah detail menu</p>
            
            <form id="editForm" method="POST" action="proses/edit_menu.php">
                <input type="hidden" name="id_menu" id="edit_id">
                
                <div class="form-group">
                    <label>Nama Menu</label>
                    <input type="text" name="nama_menu" id="edit_nama" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="id_kategori" id="edit_kategori" required>
                        <?php foreach ($kategori_list as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>"><?= ucfirst($kat['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" id="edit_harga" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <div class="status-radio">
                        <label>
                            <input type="radio" name="status" value="aktif" id="edit_status_aktif"> Aktif
                        </label>
                        <label>
                            <input type="radio" name="status" value="nonaktif" id="edit_status_nonaktif"> Nonaktif
                        </label>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Filter berdasarkan kategori
        function filterCategory(category) {
            const pills = document.querySelectorAll('.category-pill');
            pills.forEach(pill => pill.classList.remove('active'));
            event.target.classList.add('active');
            
            const rows = document.querySelectorAll('.menu-row');
            rows.forEach(row => {
                if (category === 'all' || row.dataset.category === category) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Live search
        document.getElementById('searchMenu').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.menu-row');
            
            rows.forEach(row => {
                const name = row.dataset.name || '';
                const desc = row.dataset.desc || '';
                
                if (name.includes(searchTerm) || desc.includes(searchTerm)) {
                    row.style.display = 'grid';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Edit menu - buka modal dan isi data
        function editMenu(id) {
            fetch('proses/get_menu.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_id').value = data.data.id_menu;
                        document.getElementById('edit_nama').value = data.data.nama_menu;
                        document.getElementById('edit_kategori').value = data.data.id_kategori;
                        document.getElementById('edit_harga').value = data.data.harga;
                        document.getElementById('edit_deskripsi').value = data.data.deskripsi || '';
                        
                        if (data.data.status === 'aktif') {
                            document.getElementById('edit_status_aktif').checked = true;
                        } else {
                            document.getElementById('edit_status_nonaktif').checked = true;
                        }
                        
                        document.getElementById('editMenuModal').classList.add('active');
                    } else {
                        alert('Gagal mengambil data menu');
                    }
                })
                .catch(error => {
                    alert('Terjadi kesalahan: ' + error);
                });
        }

        // Toggle status (aktif/nonaktif)
        function toggleStatus(id, currentStatus) {
            const newStatus = currentStatus === 'aktif' ? 'nonaktif' : 'aktif';
            
            if (confirm('Yakin ingin mengubah status menu ini menjadi ' + newStatus + '?')) {
                window.location.href = 'proses/update_status.php?id=' + id + '&status=' + newStatus;
            }
        }

        // Hapus menu
        function deleteMenu(id) {
            if (confirm('Yakin ingin menghapus menu ini? Data yang sudah dihapus tidak dapat dikembalikan.')) {
                window.location.href = 'proses/hapus_menu.php?id=' + id;
            }
        }

        // Tutup modal
        function closeModal() {
            document.getElementById('editMenuModal').classList.remove('active');
        }

        // Klik di luar modal untuk menutup
        window.onclick = function(event) {
            const modal = document.getElementById('editMenuModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Confirm logout
        function confirmLogout() {
            if (confirm('Yakin ingin keluar?')) {
                window.location.href = '../auth/logout.php';
            }
            return false;
        }
    </script>
</body>
</html>