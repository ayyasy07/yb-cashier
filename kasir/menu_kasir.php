<?php
session_start();
if (!isset($_SESSION['kasir_login'])) {
    header('Location: ../auth/login_kasir.php');
    exit;
}

require_once '../config/database.php';

// Ambil SEMUA menu (aktif dan nonaktif), urutkan aktif dulu
try {
    $stmt = $pdo->query("
        SELECT menu.*, kategori.nama_kategori 
        FROM menu 
        JOIN kategori ON menu.id_kategori = kategori.id_kategori 
        ORDER BY 
            CASE 
                WHEN menu.status = 'aktif' THEN 0 
                ELSE 1 
            END,
            kategori.nama_kategori, 
            menu.nama_menu
    ");
    $menus = $stmt->fetchAll();
    
    $total_aktif = 0;
    $total_nonaktif = 0;
    foreach ($menus as $menu) {
        if ($menu['status'] == 'aktif') {
            $total_aktif++;
        } else {
            $total_nonaktif++;
        }
    }
    
} catch (PDOException $e) {
    $error = "Gagal mengambil data menu: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu · YOBE Kasir</title>
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
            box-shadow: 4px 0 15px rgba(192, 108, 158, 0.05);
            display: flex;
            flex-direction: column;
        }

        .brand {
            margin-bottom: 2rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #fdf0f5; 
        }

        .brand-main {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #c06c9e, #9a4d7a);
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
            background: #fdf0f5;
            color: #c06c9e;
        }

        .nav-item.active {
            background: #c06c9e;
            color: white;
            box-shadow: 0 6px 12px rgba(192, 108, 158, 0.25);
        }

        .logout-wrapper {
            margin-top: auto;
            border-top: 2px solid #fdf0f5;
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

        .logout-btn:hover {
            background: #fff1f1;
            color: #c94f4f;
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 2rem 2rem;
            background: #faf8ff;
            height: 100vh;
            overflow-y: auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, #2d2a3a, #c06c9e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-title i {
            margin-right: 10px;
            color: #c06c9e;
        }

        .kasir-badge {
            background: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            border: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #c06c9e;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .info-card {
            background: #fdf0f5;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-left: 4px solid #c06c9e;
        }

        .info-card i {
            font-size: 1.2rem;
            color: #c06c9e;
        }

        .info-stats {
            display: flex;
            gap: 1.5rem;
            margin-left: auto;
        }

        .info-stats span {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }

        .info-stats .aktif {
            color: #2f8f5c;
            background: #e4f3e5;
            padding: 0.2rem 0.8rem;
            border-radius: 30px;
        }

        .info-stats .nonaktif {
            color: #c62828;
            background: #ffebee;
            padding: 0.2rem 0.8rem;
            border-radius: 30px;
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .category-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .category-tab {
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            background: white;
            border: 1px solid #f0eaff;
            color: #6b6580;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .category-tab i {
            font-size: 0.8rem;
        }

        .category-tab:hover {
            border-color: #c06c9e;
            color: #c06c9e;
        }

        .category-tab.active {
            background: #c06c9e;
            color: white;
            border-color: #c06c9e;
        }

        .search-wrapper {
            position: relative;
            min-width: 250px;
        }

        .search-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9a8cbb;
            font-size: 0.9rem;
        }

        .search-wrapper input {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.8rem;
            border: 1px solid #f0eaff;
            border-radius: 50px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .search-wrapper input:focus {
            outline: none;
            border-color: #c06c9e;
            box-shadow: 0 0 0 3px rgba(192, 108, 158, 0.1);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.2rem;
            margin-bottom: 2rem;
        }

        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #f0eaff;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(94, 84, 142, 0.05);
            position: relative;
            cursor: pointer;
        }

        .menu-card.aktif:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(192, 108, 158, 0.15);
            border-color: #c06c9e;
        }

        .menu-card.nonaktif:hover {
            border-color: #e06b6b;
            box-shadow: 0 8px 16px rgba(224, 107, 107, 0.1);
        }

        .menu-card.nonaktif {
            opacity: 0.7;
            background: #f9f9f9;
            border-color: #ffcdd2;
        }

        .menu-card.nonaktif .menu-icon {
            background: #e0e0e0;
            color: #9e9e9e;
        }

        .menu-card.nonaktif .menu-price {
            color: #9e9e9e;
        }

        .menu-icon {
            width: 56px;
            height: 56px;
            background: #fdf0f5;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #c06c9e;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }

        .menu-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d2a3a;
            margin-bottom: 0.3rem;
            padding-right: 70px;
        }

        .menu-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #c06c9e;
            margin-bottom: 0.5rem;
        }

        .menu-desc {
            color: #6b6580;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .menu-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.8rem;
            border-top: 1px solid #f5f0ff;
        }

        .menu-category {
            padding: 0.3rem 1rem;
            background: #f5f0ff;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #5e548e;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .menu-category i {
            font-size: 0.7rem;
            color: #c06c9e;
        }

        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.8rem;
            border-radius: 30px;
        }

        .status-badge.aktif {
            background: #e4f3e5;
            color: #2f8f5c;
        }

        .status-badge.nonaktif {
            background: #ffebee;
            color: #c62828;
        }

        /* Modal Detail */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
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
            border-radius: 24px;
            padding: 2rem;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0eaff;
        }

        .modal-header h3 {
            font-size: 1.5rem;
            color: #2d2a3a;
        }

        .modal-header i {
            font-size: 1.5rem;
            color: #c06c9e;
        }

        .modal-body {
            margin-bottom: 1.5rem;
        }

        .modal-body p {
            color: #6b6580;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .modal-info {
            background: #f5f0ff;
            padding: 0.8rem 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #5e548e;
            margin-top: 0.5rem;
        }

        .modal-info i {
            font-size: 1rem;
        }

        .btn-add {
            background: #c06c9e;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-add:hover {
            background: #a85687;
            transform: translateY(-2px);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
        }

        .btn-close {
            background: #f0eaff;
            color: #5e548e;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-close:hover {
            background: #e8e0ff;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            border: 1px solid #f0eaff;
            grid-column: 1 / -1;
            color: #9a8cbb;
        }

        .berry-accent {
            display: flex;
            gap: 6px;
            margin-top: 2rem;
            justify-content: flex-end;
        }

        .berry-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #c06c9e;
        }

        .berry-dot:nth-child(2) { background: #5e548e; }
        .berry-dot:nth-child(3) { background: #6b8cae; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">
            <div class="brand-main">yobe</div>
            <div class="brand-sub">kasir</div>
        </div>

        <nav>
            <a href="order.php" class="nav-item"><i class="fas fa-cash-register"></i> Order</a>
            <a href="menu_kasir.php" class="nav-item active"><i class="fas fa-ice-cream"></i> Menu</a>
        </nav>

        <div class="logout-wrapper">
            <a href="../auth/logout.php" class="logout-btn" onclick="return confirmLogout()">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1 class="page-title"><i class="fas fa-ice-cream"></i> Daftar Menu</h1>
            <div class="kasir-badge">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['kasir_nama'] ?? 'Kasir') ?>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>

        <div class="info-card">
            <i class="fas fa-info-circle"></i>
            <div>
                <p>Klik menu untuk melihat detail</p>
            </div>
            <div class="info-stats">
                <span class="aktif"><i class="fas fa-check-circle"></i> <?= $total_aktif ?> Aktif</span>
                <span class="nonaktif"><i class="fas fa-ban"></i> <?= $total_nonaktif ?> Nonaktif</span>
            </div>
        </div>

        <div class="menu-header">
            <div class="category-tabs">
                <button class="category-tab active" onclick="filterMenu('all')">
                    <i class="fas fa-utensils"></i> Semua
                </button>
                <button class="category-tab" onclick="filterMenu('yogurt')">
                    <i class="fas fa-ice-cream"></i> Yogurt
                </button>
                <button class="category-tab" onclick="filterMenu('bar')">
                    <i class="fas fa-cookie"></i> Bar
                </button>
                <button class="category-tab" onclick="filterMenu('smoothies')">
                    <i class="fas fa-blender"></i> Smoothies
                </button>
                <button class="category-tab" onclick="filterMenu('bowl')">
                    <i class="fas fa-utensils"></i> Bowl
                </button>
            </div>
            
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" id="searchMenu" placeholder="Cari menu...">
            </div>
        </div>

        <div class="menu-grid" id="menuGrid">
            <?php if (empty($menus)): ?>
            <div class="empty-state">
                <i class="fas fa-ice-cream"></i>
                <p>Tidak ada menu</p>
            </div>
            <?php else: ?>
                <?php foreach ($menus as $menu): 
                    $icon = 'fa-utensils';
                    if ($menu['nama_kategori'] == 'yogurt') $icon = 'fa-ice-cream';
                    elseif ($menu['nama_kategori'] == 'bar') $icon = 'fa-cookie';
                    elseif ($menu['nama_kategori'] == 'smoothies') $icon = 'fa-blender';
                    elseif ($menu['nama_kategori'] == 'bowl') $icon = 'fa-utensils';
                ?>
                <div class="menu-card <?= $menu['status'] ?>"
                     data-category="<?= $menu['nama_kategori'] ?>"
                     data-name="<?= strtolower($menu['nama_menu']) ?>"
                     data-desc="<?= strtolower($menu['deskripsi'] ?? '') ?>"
                     onclick="showDetail(<?= $menu['id_menu'] ?>, '<?= addslashes($menu['nama_menu']) ?>', '<?= addslashes($menu['deskripsi'] ?? 'Tidak ada deskripsi') ?>', <?= $menu['harga'] ?>, '<?= $menu['nama_kategori'] ?>', '<?= $menu['status'] ?>')">
                    
                    <div class="menu-icon">
                        <i class="fas <?= $icon ?>"></i>
                    </div>
                    <div class="menu-name"><?= htmlspecialchars($menu['nama_menu']) ?></div>
                    <div class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></div>
                    <div class="menu-desc" title="<?= htmlspecialchars($menu['deskripsi'] ?? 'Tidak ada deskripsi') ?>">
                        <?= htmlspecialchars($menu['deskripsi'] ?? 'Tidak ada deskripsi') ?>
                    </div>
                    <div class="menu-footer">
                        <span class="menu-category">
                            <i class="fas <?= $icon ?>"></i> <?= ucfirst($menu['nama_kategori']) ?>
                        </span>
                    </div>
                    
                    <span class="status-badge <?= $menu['status'] ?>">
                        <?php if ($menu['status'] == 'aktif'): ?>
                            <i class="fas fa-check-circle"></i> Tersedia
                        <?php else: ?>
                            <i class="fas fa-ban"></i> Tidak Tersedia
                        <?php endif; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="berry-accent">
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
            <span class="berry-dot"></span>
        </div>
    </div>

    <!-- Modal Detail Menu -->
    <div class="modal" id="menuDetailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Detail Menu</h3>
                <i class="fas fa-ice-cream" id="modalIcon"></i>
            </div>
            <div class="modal-body">
                <p id="modalDesc"></p>
                <div class="modal-info">
                    <i class="fas fa-tag"></i>
                    <span id="modalCategory"></span>
                </div>
                <div class="modal-info" style="margin-top: 0.5rem;">
                    <i class="fas fa-coin"></i>
                    <span id="modalPrice"></span>
                </div>
                
                <!-- Pesan tidak tersedia (untuk menu nonaktif) -->
                <div class="modal-info" id="modalUnavailable" style="margin-top: 0.5rem; background: #ffebee; color: #c62828; display: none;">
                    <i class="fas fa-ban"></i>
                    <span>Menu tidak tersedia untuk dipesan</span>
                </div>
                
                <!-- Tombol tambah ke keranjang (untuk menu aktif) -->
                <button class="btn-add" id="modalAddButton" style="display: none;">
                    <i class="fas fa-cart-plus"></i> Tambah ke Keranjang
                </button>
            </div>
            <div class="modal-footer">
                <button class="btn-close" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        let selectedMenuId = null;

        function filterMenu(category) {
            const tabs = document.querySelectorAll('.category-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            event.target.classList.add('active');

            const items = document.querySelectorAll('.menu-card');
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
            checkEmptyState();
        }

        document.getElementById('searchMenu').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const items = document.querySelectorAll('.menu-card');
            
            items.forEach(item => {
                const name = item.dataset.name;
                const desc = item.dataset.desc;
                
                if (name.includes(searchTerm) || desc.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
            checkEmptyState();
        });

        function checkEmptyState() {
            const menuGrid = document.getElementById('menuGrid');
            const visibleCards = document.querySelectorAll('.menu-card[style="display: block;"], .menu-card:not([style*="display: none"])');
            
            const existingEmpty = document.querySelector('.empty-state:not(.initial)');
            if (existingEmpty) existingEmpty.remove();
            
            if (visibleCards.length === 0) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'empty-state';
                emptyDiv.innerHTML = `
                    <i class="fas fa-search"></i>
                    <p>Tidak ada menu ditemukan</p>
                    <small>Coba kata kunci lain</small>
                `;
                menuGrid.appendChild(emptyDiv);
            }
        }

        function showDetail(id, nama, desc, harga, kategori, status) {
            selectedMenuId = id;
            
            document.getElementById('modalTitle').textContent = nama;
            document.getElementById('modalDesc').textContent = desc;
            document.getElementById('modalCategory').textContent = 'Kategori: ' + kategori.charAt(0).toUpperCase() + kategori.slice(1);
            document.getElementById('modalPrice').textContent = 'Harga: Rp ' + harga.toLocaleString('id-ID');
            
            const modalIcon = document.getElementById('modalIcon');
            if (kategori === 'yogurt') modalIcon.className = 'fas fa-ice-cream';
            else if (kategori === 'bar') modalIcon.className = 'fas fa-cookie';
            else if (kategori === 'smoothies') modalIcon.className = 'fas fa-blender';
            else modalIcon.className = 'fas fa-utensils';
            
            const addButton = document.getElementById('modalAddButton');
            const unavailableMessage = document.getElementById('modalUnavailable');
            
            if (status === 'aktif') {
                addButton.style.display = 'block';
                unavailableMessage.style.display = 'none';
            } else {
                addButton.style.display = 'none';
                unavailableMessage.style.display = 'flex';
            }
            
            document.getElementById('menuDetailModal').classList.add('active');
        }

        function addToCartFromModal() {
            if (!selectedMenuId) return;
            
            fetch('proses/get_menu_item.php?id=' + selectedMenuId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof addToCart === 'function') {
                            addToCart(data.id, data.nama, data.harga);
                        }
                        closeModal();
                        alert('Menu ditambahkan ke keranjang!');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        document.getElementById('modalAddButton').addEventListener('click', addToCartFromModal);

        function closeModal() {
            document.getElementById('menuDetailModal').classList.remove('active');
            selectedMenuId = null;
        }

        window.onclick = function(event) {
            const modal = document.getElementById('menuDetailModal');
            if (event.target == modal) closeModal();
        }

        function confirmLogout() {
            return confirm('Yakin ingin keluar?');
        }
    </script>
</body>
</html>