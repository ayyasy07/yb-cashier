<?php
session_start();
if (!isset($_SESSION['kasir_login'])) {
    header('Location: ../auth/login_kasir.php');
    exit;
}

require_once '../config/database.php';

$order_id = $_GET['id'] ?? 0;
$copy = $_GET['copy'] ?? 'single';

if (empty($order_id)) die('ID order tidak ditemukan');

$stmt = $pdo->prepare("
    SELECT o.*, u.nama as nama_kasir 
    FROM orders o
    LEFT JOIN users u ON o.created_by = u.id_users
    WHERE o.id_orders = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) die('Order tidak ditemukan');

$stmt = $pdo->prepare("SELECT * FROM detail_order WHERE id_orders = ?");
$stmt->execute([$order_id]);
$details = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Struk #ORD<?= str_pad($order_id, 3, '0', STR_PAD_LEFT) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            width: 100%;
            max-width: 58mm;
            margin: 0 auto;
            padding: 2mm;
            font-size: 8px;
            background: white;
        }

        .struk {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .header, .footer, .copy-label {
            text-align: center;
        }

        .header h2 {
            font-size: 12px;
            margin: 1px 0;
            color: #c06c9e;
        }

        .header p {
            font-size: 7px;
            margin: 1px 0;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 3px 0;
        }

        .item-header, .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
        }

        .item-header {
            font-weight: bold;
            border-bottom: 1px dashed #000;
            margin-bottom: 3px;
            padding-bottom: 2px;
        }

        .item-row {
            margin-bottom: 2px;
        }

        .item-nama {
            width: 45%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-qty {
            width: 10%;
            text-align: center;
        }

        .item-harga {
            width: 20%;
            text-align: right;
        }

        .item-subtotal {
            width: 20%;
            text-align: right;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
            font-size: 8px;
        }

        .total-label {
            text-align: right;
            width: 70%;
        }

        .total-value {
            text-align: right;
            width: 30%;
            font-weight: bold;
        }

        .footer p {
            font-size: 6px;
            margin: 2px 0;
        }

        @media print {
            @page {
                size: auto;
                margin: 0;
            }
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .struk {
                margin: 0;
                padding: 2mm;
            }
        }
    </style>
</head>
<body>
    <?php if ($copy === 'double'): ?>
        <!-- STRUK DAPUR -->
        <div class="struk">
            <div class="copy-label" style="color:#e06b6b;">=== DAPUR ===</div>
            <div class="header">
                <h2>YOBE</h2>
                <p><?= date('d/m/Y H:i', strtotime($order['tanggal'])) ?></p>
                <p>#ORD<?= str_pad($order_id, 3, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div class="divider"></div>
            <div class="items">
                <?php foreach ($details as $item): ?>
                <div class="item-row">
                    <span class="item-nama"><?= htmlspecialchars($item['nama_menu_snapshot']) ?></span>
                    <span class="item-qty"><?= $item['qty'] ?>x</span>
                </div>
                <?php if (!empty($item['catatan'])): ?>
                <div style="font-size:6px; margin-left:5px; color:#666;">Catatan: <?= htmlspecialchars($item['catatan']) ?></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="divider"></div>
            <div class="footer"><p>--- UNTUK DAPUR ---</p></div>
        </div>

        <!-- STRUK CUSTOMER -->
        <div class="struk" style="page-break-before: always;">
            <div class="copy-label" style="color:#2f8f5c;">=== CUSTOMER ===</div>
            <div class="header">
                <h2>YOBE</h2>
                <p><?= date('d/m/Y H:i', strtotime($order['tanggal'])) ?></p>
                <p>Kasir: <?= htmlspecialchars($order['nama_kasir'] ?? 'Kasir') ?></p>
                <p>#ORD<?= str_pad($order_id, 3, '0', STR_PAD_LEFT) ?></p>
                <p>Customer: <?= htmlspecialchars($order['nama_pemesan']) ?></p>
            </div>
            <div class="divider"></div>
            <div class="item-header">
                <span class="item-nama">Item</span>
                <span class="item-qty">Qty</span>
                <span class="item-harga">Harga</span>
                <span class="item-subtotal">Subtotal</span>
            </div>
            <?php foreach ($details as $item): ?>
            <div class="item-row">
                <span class="item-nama"><?= htmlspecialchars($item['nama_menu_snapshot']) ?></span>
                <span class="item-qty"><?= $item['qty'] ?></span>
                <span class="item-harga">Rp <?= number_format($item['harga_snapshot'], 0, ',', '.') ?></span>
                <span class="item-subtotal">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
            <?php if (!empty($item['catatan'])): ?>
            <div style="font-size:6px; margin-left:5px; color:#666;">Catatan: <?= htmlspecialchars($item['catatan']) ?></div>
            <?php endif; ?>
            <?php endforeach; ?>
            <div class="divider"></div>
            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-value">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
            <?php if ($order['metode_pembayaran'] === 'cash'): ?>
            <div class="total-row">
                <span class="total-label">Bayar</span>
                <span class="total-value">Rp <?= number_format($order['bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row" style="color:#2f8f5c;">
                <span class="total-label">Kembalian</span>
                <span class="total-value">Rp <?= number_format($order['kembalian'], 0, ',', '.') ?></span>
            </div>
            <?php else: ?>
            <div class="total-row">
                <span class="total-label">QRIS</span>
                <span class="total-value">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="divider"></div>
            <div class="footer">
                <p>Terima kasih telah berbelanja!</p>
                <p>Scan QRIS untuk pembayaran non-tunai</p>
            </div>
        </div>

    <?php else: ?>
        <!-- STRUK SINGLE -->
        <div class="struk">
            <div class="header">
                <h2>YOBE</h2>
                <p><?= date('d/m/Y H:i', strtotime($order['tanggal'])) ?></p>
                <p>Kasir: <?= htmlspecialchars($order['nama_kasir'] ?? 'Kasir') ?></p>
                <p>#ORD<?= str_pad($order_id, 3, '0', STR_PAD_LEFT) ?></p>
                <p>Customer: <?= htmlspecialchars($order['nama_pemesan']) ?></p>
            </div>
            <div class="divider"></div>
            <div class="item-header">
                <span class="item-nama">Item</span>
                <span class="item-qty">Qty</span>
                <span class="item-harga">Harga</span>
                <span class="item-subtotal">Subtotal</span>
            </div>
            <?php foreach ($details as $item): ?>
            <div class="item-row">
                <span class="item-nama"><?= htmlspecialchars($item['nama_menu_snapshot']) ?></span>
                <span class="item-qty"><?= $item['qty'] ?></span>
                <span class="item-harga">Rp <?= number_format($item['harga_snapshot'], 0, ',', '.') ?></span>
                <span class="item-subtotal">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
            <?php if (!empty($item['catatan'])): ?>
            <div style="font-size:6px; margin-left:5px; color:#666;">Catatan: <?= htmlspecialchars($item['catatan']) ?></div>
            <?php endif; ?>
            <?php endforeach; ?>
            <div class="divider"></div>
            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-value">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
            <?php if ($order['metode_pembayaran'] === 'cash'): ?>
            <div class="total-row">
                <span class="total-label">Bayar</span>
                <span class="total-value">Rp <?= number_format($order['bayar'], 0, ',', '.') ?></span>
            </div>
            <div class="total-row" style="color:#2f8f5c;">
                <span class="total-label">Kembalian</span>
                <span class="total-value">Rp <?= number_format($order['kembalian'], 0, ',', '.') ?></span>
            </div>
            <?php else: ?>
            <div class="total-row">
                <span class="total-label">QRIS</span>
                <span class="total-value">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="divider"></div>
            <div class="footer">
                <p>Terima kasih telah berbelanja!</p>
                <p>Scan QRIS untuk pembayaran non-tunai</p>
            </div>
        </div>
    <?php endif; ?>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
                window.onafterprint = function() { window.close(); };
                setTimeout(function() { window.close(); }, 2000);
            }, 500);
        };
    </script>
</body>
</html>