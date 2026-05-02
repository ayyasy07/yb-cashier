<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

require_once '../config/database.php';

// Ambil parameter filter dari URL
$dari_tanggal = $_GET['dari_tanggal'] ?? date('Y-m-01');
$sampai_tanggal = $_GET['sampai_tanggal'] ?? date('Y-m-d');
$metode = $_GET['metode'] ?? '';

try {
    // Hitung total berdasarkan filter
    $sql_hitung = "SELECT 
                    COUNT(*) as total_transaksi,
                    SUM(total_harga) as total_pendapatan
                   FROM orders 
                   WHERE DATE(tanggal) BETWEEN ? AND ?";
    $params_hitung = [$dari_tanggal, $sampai_tanggal];
    
    if (!empty($metode)) {
        $sql_hitung .= " AND metode_pembayaran = ?";
        $params_hitung[] = $metode;
    }
    
    $stmt = $pdo->prepare($sql_hitung);
    $stmt->execute($params_hitung);
    $total = $stmt->fetch();
    
    // Ambil data transaksi dengan filter
    $sql = "SELECT 
                o.*,
                u.nama as nama_kasir
            FROM orders o
            LEFT JOIN users u ON o.created_by = u.id_users
            WHERE DATE(o.tanggal) BETWEEN ? AND ?";
    $params = [$dari_tanggal, $sampai_tanggal];
    
    if (!empty($metode)) {
        $sql .= " AND o.metode_pembayaran = ?";
        $params[] = $metode;
    }
    
    $sql .= " ORDER BY o.tanggal DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = "Gagal mengambil data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan · YOBE</title>
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
            font-size: 0.9rem;
            color: #6b6580;
            border: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }

        .date-badge i {
            color: #be95c4;
        }

        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #f0eaff;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(94, 84, 142, 0.05);
        }

        .filter-title {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #f5f0ff;
        }

        .filter-title i {
            color: #5e548e;
            font-size: 1.1rem;
        }

        .filter-title h3 {
            font-weight: 600;
            color: #2d2a3a;
            font-size: 1rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1.5fr 1.5fr 1.2fr 0.8fr;
            gap: 0.8rem;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .filter-label {
            font-size: 0.7rem;
            color: #5e548e;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-label i {
            margin-right: 4px;
            color: #be95c4;
            font-size: 0.7rem;
        }

        .filter-input {
            padding: 0.6rem 0.8rem;
            border: 1px solid #f0eaff;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.2s;
            background: #faf8ff;
            width: 100%;
        }

        .filter-input:focus {
            outline: none;
            border-color: #5e548e;
            box-shadow: 0 0 0 3px rgba(94, 84, 142, 0.1);
        }

        .btn-filter, .btn-reset {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .btn-filter {
            background: #5e548e;
            color: white;
            width: 100%;
        }

        .btn-filter:hover {
            background: #4e457a;
        }

        .btn-reset {
            background: white;
            color: #5e548e;
            border: 1px solid #f0eaff;
            width: 100%;
        }

        .btn-reset:hover {
            background: #f5f0ff;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 1.2rem;
            border: 1px solid #f0eaff;
            box-shadow: 0 4px 12px rgba(94, 84, 142, 0.05);
            border-left: 3px solid #5e548e;
        }

        .summary-card.total-pendapatan {
            border-left-color: #be95c4;
        }

        .summary-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.5rem;
        }

        .summary-icon {
            width: 32px;
            height: 32px;
            background: #f5f0ff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5e548e;
            font-size: 1rem;
        }

        .summary-label {
            color: #6b6580;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2d2a3a;
            margin-bottom: 0.2rem;
            line-height: 1.2;
        }

        .summary-desc {
            color: #9a8cbb;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .summary-desc i {
            font-size: 0.6rem;
        }

        .summary-desc span {
            background: #f5f0ff;
            padding: 0.2rem 0.6rem;
            border-radius: 30px;
            color: #5e548e;
            font-weight: 500;
            font-size: 0.65rem;
        }

        .table-container {
            background: white;
            border-radius: 16px;
            border: 1px solid #f0eaff;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(94, 84, 142, 0.05);
        }

        .transaksi-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .transaksi-table th {
            text-align: left;
            padding: 0.8rem 0.8rem;
            background: #f8f4ff;
            color: #5e548e;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            border-bottom: 1px solid #f0eaff;
            white-space: nowrap;
        }

        .transaksi-table td {
            padding: 0.8rem 0.8rem;
            border-bottom: 1px solid #f5f0ff;
            color: #2d2a3a;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .transaksi-table th:nth-child(1) { width: 12%; }
        .transaksi-table th:nth-child(2) { width: 10%; }
        .transaksi-table th:nth-child(3) { width: 12%; }
        .transaksi-table th:nth-child(4) { width: 10%; }
        .transaksi-table th:nth-child(5) { width: 8%; }
        .transaksi-table th:nth-child(6) { width: 12%; }
        .transaksi-table th:nth-child(7) { width: 10%; }
        .transaksi-table th:nth-child(8) { width: 10%; }

        .transaksi-table tr:hover td {
            background: #faf8ff;
        }

        .transaksi-table tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 30px;
            font-size: 0.7rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge.cash {
            background: #e4f3e5;
            color: #2f8f5c;
        }

        .badge.qris {
            background: #f0eaff;
            color: #5e548e;
        }

        .btn-detail {
            padding: 0.2rem 0.6rem;
            border: 1px solid #f0eaff;
            border-radius: 30px;
            font-size: 0.7rem;
            color: #6b6580;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            background: white;
        }

        .btn-detail i {
            font-size: 0.65rem;
        }

        .btn-detail:hover {
            border-color: #5e548e;
            color: #5e548e;
            background: #f5f0ff;
        }

        /* Modal untuk preview gambar */
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
            border-radius: 20px;
            padding: 1.5rem;
            max-width: 90%;
            width: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #f0eaff;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            color: #2d2a3a;
        }

        .modal-close {
            cursor: pointer;
            font-size: 1.3rem;
            color: #9a8cbb;
        }

        .modal-close:hover {
            color: #e06b6b;
        }

        .modal-body {
            text-align: center;
        }

        .modal-body img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
        }

        .modal-footer {
            margin-top: 1rem;
            text-align: center;
        }

        .btn-close {
            background: #f0eaff;
            color: #5e548e;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .btn-close:hover {
            background: #e8e0ff;
        }

        /* Modal Detail Transaksi */
        .modal-detail {
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .detail-row {
            display: flex;
            margin-bottom: 0.8rem;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #f0eaff;
        }

        .detail-label {
            width: 40%;
            color: #5e548e;
            font-weight: 600;
        }

        .detail-value {
            width: 60%;
            color: #2d2a3a;
        }

        .detail-items {
            margin-top: 1rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f5f0ff;
            font-size: 0.9rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #9a8cbb;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.5;
        }

        .pagination {
            display: flex;
            justify-content: flex-end;
            gap: 0.3rem;
            padding: 1rem;
            border-top: 1px solid #f5f0ff;
        }

        .page-btn {
            width: 30px;
            height: 30px;
            border-radius: 6px;
            border: 1px solid #f0eaff;
            background: white;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .page-btn:hover {
            border-color: #5e548e;
            color: #5e548e;
        }

        .page-btn.active {
            background: #5e548e;
            color: white;
            border-color: #5e548e;
        }

        .alert {
            background: #fff1f1;
            border-left: 3px solid #e06b6b;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #c94f4f;
            font-size: 0.85rem;
        }

        .berry-accent {
            display: flex;
            gap: 4px;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }

        .berry-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #be95c4;
        }

        .berry-dot:nth-child(2) { background: #5e548e; }
        .berry-dot:nth-child(3) { background: #6b8cae; }
        .berry-dot:nth-child(4) { background: #e06b6b; }
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
            <a href="menu_admin.php" class="nav-item"><i class="fas fa-ice-cream"></i> Menu</a>
            <a href="laporan.php" class="nav-item active"><i class="fas fa-file-alt"></i> Laporan</a>
            <a href="user_setting.php" class="nav-item"><i class="fas fa-cog"></i> Pengaturan</a>
        </nav>
        <div class="logout-wrapper">
            <a href="../auth/logout.php" class="logout-btn" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1 class="page-title"><i class="fas fa-file-alt"></i> Laporan Transaksi</h1>
            <div class="date-badge"><i class="far fa-calendar-alt"></i> <?= date('d M Y') ?></div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="filter-card">
            <div class="filter-title"><i class="fas fa-filter"></i><h3>Filter Laporan</h3></div>
            <form method="GET" action="">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-calendar-start"></i> Dari</label>
                        <input type="date" class="filter-input" name="dari_tanggal" value="<?= $dari_tanggal ?>">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-calendar-end"></i> Sampai</label>
                        <input type="date" class="filter-input" name="sampai_tanggal" value="<?= $sampai_tanggal ?>">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label"><i class="fas fa-credit-card"></i> Metode</label>
                        <select class="filter-input" name="metode">
                            <option value="">Semua</option>
                            <option value="cash" <?= $metode == 'cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="qris" <?= $metode == 'qris' ? 'selected' : '' ?>>QRIS</option>
                        </select>
                    </div>
                    <div class="filter-group" style="display: flex; gap: 5px;">
                        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
                        <a href="laporan.php" class="btn-reset"><i class="fas fa-undo"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-header"><div class="summary-icon"><i class="fas fa-receipt"></i></div><span class="summary-label">Total Transaksi</span></div>
                <div class="summary-value"><?= number_format($total['total_transaksi'] ?? 0, 0, ',', '.') ?></div>
                <div class="summary-desc"><i class="fas fa-calendar-alt"></i><span><?= date('d/m/Y', strtotime($dari_tanggal)) ?> - <?= date('d/m/Y', strtotime($sampai_tanggal)) ?></span></div>
            </div>
            <div class="summary-card total-pendapatan">
                <div class="summary-header"><div class="summary-icon"><i class="fas fa-coins"></i></div><span class="summary-label">Pendapatan</span></div>
                <div class="summary-value">Rp <?= number_format($total['total_pendapatan'] ?? 0, 0, ',', '.') ?></div>
                <div class="summary-desc"><i class="fas fa-calendar-alt"></i><span><?= date('d/m/Y', strtotime($dari_tanggal)) ?> - <?= date('d/m/Y', strtotime($sampai_tanggal)) ?></span></div>
            </div>
        </div>

        <div class="table-container">
            <table class="transaksi-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>ID Order</th>
                        <th>Pemesan</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Kasir</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-receipt"></i><p>Tidak ada transaksi</p></div><\/td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= date('d/m H:i', strtotime($order['tanggal'])) ?></td>
                            <td>#ORD<?= str_pad($order['id_orders'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($order['nama_pemesan']) ?></td>
                            <td>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                            <td><span class="badge <?= $order['metode_pembayaran'] ?>"><?= strtoupper($order['metode_pembayaran']) ?></span></td>
                            <td><?= htmlspecialchars($order['nama_kasir'] ?? 'Admin') ?></td>
                            <td>
                                <?php if ($order['metode_pembayaran'] == 'qris' && !empty($order['bukti_pembayaran'])): ?>
                                    <a href="javascript:void(0)" onclick="showImage('../uploads/bukti/<?= $order['bukti_pembayaran'] ?>')" class="btn-detail">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-detail" onclick="showDetail(<?= $order['id_orders'] ?>)">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="pagination">
                <button class="page-btn"><i class="fas fa-chevron-left"></i></button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>

        <div class="berry-accent">
            <span class="berry-dot"></span><span class="berry-dot"></span><span class="berry-dot"></span><span class="berry-dot"></span>
        </div>
    </div>

    <!-- Modal Detail Transaksi -->
    <div class="modal" id="detailModal">
        <div class="modal-content modal-detail">
            <div class="modal-header">
                <h3 id="modalOrderId">Detail Transaksi</h3>
                <i class="fas fa-times modal-close" onclick="closeModal('detailModal')"></i>
            </div>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- Modal Preview Gambar Bukti -->
    <div class="modal" id="imageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Bukti Pembayaran</h3>
                <i class="fas fa-times modal-close" onclick="closeModal('imageModal')"></i>
            </div>
            <div class="modal-body">
                <img id="previewImage" src="" alt="Bukti Pembayaran">
            </div>
            <div class="modal-footer">
                <button class="btn-close" onclick="closeModal('imageModal')">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        function confirmLogout() { return confirm('Yakin ingin keluar?'); }

        function showDetail(id) {
            fetch('proses/get_order_detail.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = `
                            <div class="detail-row"><span class="detail-label">ID Order</span><span class="detail-value">#ORD${data.order.id_orders.toString().padStart(3, '0')}</span></div>
                            <div class="detail-row"><span class="detail-label">Tanggal</span><span class="detail-value">${data.order.tanggal}</span></div>
                            <div class="detail-row"><span class="detail-label">Customer</span><span class="detail-value">${data.order.nama_pemesan}</span></div>
                            <div class="detail-row"><span class="detail-label">Kasir</span><span class="detail-value">${data.order.nama_kasir || 'Admin'}</span></div>
                            <div class="detail-row"><span class="detail-label">Metode</span><span class="detail-value">${data.order.metode_pembayaran.toUpperCase()}</span></div>
                            <h4 style="margin: 1rem 0 0.5rem; color: #5e548e;">Detail Item</h4>
                            <div class="detail-items">
                        `;
                        data.items.forEach(item => {
                            html += `<div class="detail-item"><span>${item.nama_menu_snapshot} x${item.qty}</span><span>Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</span></div>`;
                        });
                        html += `</div><div class="detail-row" style="margin-top: 1rem; font-weight: bold;"><span class="detail-label">Subtotal</span><span class="detail-value">Rp ${parseInt(data.order.total_harga).toLocaleString('id-ID')}</span></div>`;
                        if (data.order.metode_pembayaran === 'cash') {
                            html += `<div class="detail-row"><span class="detail-label">Bayar</span><span class="detail-value">Rp ${parseInt(data.order.bayar).toLocaleString('id-ID')}</span></div>
                                     <div class="detail-row" style="color: #2f8f5c;"><span class="detail-label">Kembalian</span><span class="detail-value">Rp ${parseInt(data.order.kembalian).toLocaleString('id-ID')}</span></div>`;
                        }
                        document.getElementById('modalContent').innerHTML = html;
                        document.getElementById('detailModal').classList.add('active');
                    } else alert('Gagal mengambil detail transaksi');
                }).catch(error => alert('Terjadi kesalahan: ' + error));
        }

        function showImage(src) {
            document.getElementById('previewImage').src = src;
            document.getElementById('imageModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>