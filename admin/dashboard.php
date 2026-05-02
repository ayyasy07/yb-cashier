<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

require_once '../config/database.php';

// Ambil data statistik dari database
try {
    // Total per kategori dengan detail aktif/nonaktif
    $stmt = $pdo->query("
        SELECT 
            kategori.id_kategori,
            kategori.nama_kategori,
            COUNT(menu.id_menu) as total_menu,
            SUM(CASE WHEN menu.status = 'aktif' THEN 1 ELSE 0 END) as total_aktif,
            SUM(CASE WHEN menu.status = 'nonaktif' THEN 1 ELSE 0 END) as total_nonaktif
        FROM kategori 
        LEFT JOIN menu ON kategori.id_kategori = menu.id_kategori 
        GROUP BY kategori.id_kategori
        ORDER BY kategori.nama_kategori
    ");
    $kategori_stats = $stmt->fetchAll();

    // Total keseluruhan
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_semua,
            SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as total_aktif_semua,
            SUM(CASE WHEN status = 'nonaktif' THEN 1 ELSE 0 END) as total_nonaktif_semua
        FROM menu
    ");
    $total_semua = $stmt->fetch();

} catch (PDOException $e) {
    $error = "Gagal mengambil data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · YOBE</title>
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

        .date-badge {
            background: white;
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            font-size: 0.95rem;
            color: #6b6580;
            border: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(94, 84, 142, 0.05);
        }

        .date-badge i {
            color: #be95c4;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1.8rem;
            border: 1px solid #f0eaff;
            transition: all 0.2s;
            box-shadow: 0 8px 20px rgba(94, 84, 142, 0.05);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(94, 84, 142, 0.15);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .stat-label {
            color: #9a8cbb;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: #f5f0ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5e548e;
            font-size: 1.5rem;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 600;
            color: #2d2a3a;
            line-height: 1;
            margin-bottom: 0.3rem;
        }

        .stat-footer {
            color: #9a8cbb;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .stat-footer i {
            font-size: 0.8rem;
        }

        .summary-card {
            background: linear-gradient(135deg, #5e548e, #be95c4);
            border-radius: 24px;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 20px 40px rgba(94, 84, 142, 0.25);
        }

        .summary-content {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .summary-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }

        .summary-stats {
            display: flex;
            gap: 2.5rem;
        }

        .summary-stat {
            color: white;
        }

        .summary-stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .summary-stat-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .btn-add {
            background: white;
            color: #5e548e;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            transition: all 0.2s;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-add i {
            font-size: 1rem;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
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

        .alert i {
            font-size: 1.2rem;
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
            <a href="dashboard.php" class="nav-item active"><i class="fas fa-chart-pie"></i> Dashboard</a>
            <a href="menu_admin.php" class="nav-item"><i class="fas fa-ice-cream"></i> Menu</a>
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
            <h1 class="page-title"><i class="fas fa-chart-pie"></i> Dashboard YOBE</h1>
            <div class="date-badge">
                <i class="far fa-calendar-alt"></i> <?php echo date('d F Y'); ?>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $error ?></span>
        </div>
        <?php endif; ?>

        <!-- Stats Cards - Dinamis berdasarkan kategori -->
        <div class="stats-grid">
            <?php foreach ($kategori_stats as $kat): 
                // Tentukan icon berdasarkan nama kategori
                $icon = 'fa-tag'; // default
                if ($kat['nama_kategori'] == 'yogurt') {
                    $icon = 'fa-ice-cream';
                } elseif ($kat['nama_kategori'] == 'bar') {
                    $icon = 'fa-cookie';
                } elseif ($kat['nama_kategori'] == 'smoothies') {
                    $icon = 'fa-blender';
                } elseif ($kat['nama_kategori'] == 'bowl') {
                    $icon = 'fa-blender';
                }
            ?>
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total <?= ucfirst($kat['nama_kategori']) ?></span>
                    <span class="stat-icon"><i class="fas <?= $icon ?>"></i></span>
                </div>
                <div class="stat-value"><?= $kat['total_menu'] ?></div>
                <div class="stat-footer">
                    <i class="fas fa-check-circle" style="color: #2f8f5c;"></i> <?= $kat['total_aktif'] ?> Aktif · <?= $kat['total_nonaktif'] ?> Nonaktif
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Card Total Menu (tetap ada) -->
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-label">Total Menu</span>
                    <span class="stat-icon"><i class="fas fa-clipboard-list"></i></span>
                </div>
                <div class="stat-value"><?= $total_semua['total_semua'] ?></div>
                <div class="stat-footer">
                    <i class="fas fa-clipboard-list"></i> Semua kategori
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="summary-card">
            <div class="summary-content">
                <div class="summary-icon"><i class="fas fa-chart-pie"></i></div>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= $total_semua['total_semua'] ?></div>
                        <div class="summary-stat-label">Total Menu</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= $total_semua['total_aktif_semua'] ?></div>
                        <div class="summary-stat-label">Aktif</div>
                    </div>
                    <div class="summary-stat">
                        <div class="summary-stat-value"><?= $total_semua['total_nonaktif_semua'] ?></div>
                        <div class="summary-stat-label">Nonaktif</div>
                    </div>
                </div>
            </div>
            <button class="btn-add" onclick="window.location.href='tambah_menu.php'">
                <i class="fas fa-plus"></i> Tambah Menu
            </button>
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