<?php
session_start();
if (!isset($_SESSION['kasir_login'])) {
    header('Location: ../auth/login_kasir.php');
    exit;
}

require_once '../config/database.php';

// Ambil data menu aktif
try {
    $stmt = $pdo->query("
        SELECT menu.*, kategori.nama_kategori 
        FROM menu 
        JOIN kategori ON menu.id_kategori = kategori.id_kategori 
        WHERE menu.status = 'aktif'
        ORDER BY kategori.nama_kategori, menu.nama_menu
    ");
    $menus = $stmt->fetchAll();
    
    $categories = [];
    foreach ($menus as $menu) {
        $categories[$menu['nama_kategori']] = true;
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
    <title>Order · YOBE Kasir</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', -apple-system, sans-serif; }
        body { background: #faf8ff; display: flex; min-height: 100vh; color: #2d2a3a; }
        .sidebar { width: 280px; background: #ffffff; height: 100vh; position: fixed; padding: 2rem 1.5rem; border-right: 1px solid #f0eaff; display: flex; flex-direction: column; }
        .brand { margin-bottom: 2rem; padding-bottom: 0.5rem; border-bottom: 2px solid #fdf0f5; }
        .brand-main { font-size: 2.2rem; font-weight: 700; background: linear-gradient(135deg, #c06c9e, #9a4d7a); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: block; }
        .brand-sub { font-size: 0.7rem; color: #9a8cbb; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; display: block; }
        .nav-item { display: flex; align-items: center; gap: 14px; padding: 0.9rem 1.2rem; color: #6b6580; text-decoration: none; border-radius: 14px; margin-bottom: 0.4rem; font-weight: 500; transition: all 0.2s; }
        .nav-item i { width: 22px; }
        .nav-item:hover { background: #fdf0f5; color: #c06c9e; }
        .nav-item.active { background: #c06c9e; color: white; }
        .logout-wrapper { margin-top: auto; border-top: 2px solid #fdf0f5; padding-top: 1.5rem; }
        .logout-btn { display: flex; align-items: center; gap: 14px; padding: 0.9rem 1.2rem; color: #e06b6b; text-decoration: none; border-radius: 14px; font-weight: 500; transition: all 0.2s; }
        .logout-btn:hover { background: #fff1f1; color: #c94f4f; }
        .main-content { margin-left: 260px; flex: 1; padding: 2rem; background: #faf8ff; height: 100vh; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; font-weight: 600; background: linear-gradient(135deg, #2d2a3a, #c06c9e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .page-title i { margin-right: 10px; color: #c06c9e; }
        .kasir-badge { background: white; padding: 0.6rem 1.2rem; border-radius: 50px; border: 1px solid #f0eaff; display: flex; align-items: center; gap: 8px; color: #c06c9e; font-weight: 500; font-size: 0.9rem; }
        .order-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; align-items: start; }
        .menu-section { background: white; border-radius: 24px; border: 1px solid #f0eaff; padding: 1.5rem; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .section-header h3 { font-weight: 600; color: #2d2a3a; }
        .filter-tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        .filter-tab { padding: 0.5rem 1.2rem; border-radius: 50px; background: #f5f0ff; border: 1px solid #f0eaff; color: #6b6580; cursor: pointer; font-size: 0.85rem; font-weight: 500; }
        .filter-tab:hover { border-color: #c06c9e; color: #c06c9e; }
        .filter-tab.active { background: #c06c9e; color: white; }
        .search-box { margin-bottom: 1.5rem; position: relative; }
        .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #9a8cbb; }
        .search-box input { width: 100%; padding: 0.8rem 1rem 0.8rem 2.8rem; border: 1px solid #f0eaff; border-radius: 50px; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 0.8rem; max-height: 500px; overflow-y: auto; }
        .menu-card { background: #faf8ff; border: 1px solid #f0eaff; border-radius: 16px; padding: 1rem; cursor: pointer; transition: all 0.2s; }
        .menu-card:hover { border-color: #c06c9e; transform: translateY(-2px); box-shadow: 0 8px 16px rgba(192, 108, 158, 0.1); }
        .menu-icon { width: 48px; height: 48px; background: #fdf0f5; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #c06c9e; font-size: 1.5rem; margin-bottom: 0.8rem; }
        .menu-name { font-weight: 600; color: #2d2a3a; font-size: 1rem; }
        .menu-price { color: #c06c9e; font-weight: 600; font-size: 1rem; }
        .menu-category { font-size: 0.7rem; color: #9a8cbb; }
        .cart-section { background: white; border-radius: 24px; border: 1px solid #f0eaff; padding: 1.5rem; position: sticky; top: 2rem; }
        .cart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; }
        .cart-header h3 { font-weight: 600; }
        .cart-count { background: #c06c9e; color: white; padding: 0.2rem 1rem; border-radius: 50px; font-size: 0.8rem; }
        .customer-input { width: 100%; padding: 0.8rem 1rem; border: 1px solid #f0eaff; border-radius: 12px; margin-bottom: 1rem; }
        .cart-items { max-height: 350px; overflow-y: auto; margin-bottom: 1rem; }
        .cart-item { background: #faf8ff; border-radius: 12px; padding: 0.8rem; margin-bottom: 0.5rem; border: 1px solid #f0eaff; }
        .cart-item-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .cart-item-name { font-weight: 600; font-size: 0.9rem; }
        .cart-item-price { color: #c06c9e; font-weight: 600; }
        .cart-item-actions { display: flex; align-items: center; gap: 0.5rem; }
        .qty-btn { width: 28px; height: 28px; border: 1px solid #f0eaff; background: white; border-radius: 8px; cursor: pointer; }
        .qty-value { min-width: 30px; text-align: center; }
        .cart-item-note { width: 100%; padding: 0.5rem; border: 1px solid #f0eaff; border-radius: 8px; margin-top: 0.5rem; }
        .remove-item { color: #e06b6b; cursor: pointer; font-size: 0.75rem; text-align: right; margin-top: 0.3rem; }
        .cart-summary { border-top: 1px solid #f0eaff; padding-top: 1rem; }
        .summary-row { display: flex; justify-content: space-between; margin: 0.5rem 0; color: #6b6580; }
        .total-row { font-weight: 700; font-size: 1.2rem; margin: 1rem 0; padding-top: 0.5rem; border-top: 1px dashed #f0eaff; }
        .payment-method { width: 100%; padding: 0.8rem; border: 1px solid #f0eaff; border-radius: 12px; margin: 0.5rem 0; }
        .payment-input { width: 100%; padding: 0.8rem; border: 1px solid #f0eaff; border-radius: 12px; margin: 0.5rem 0; }
        .change-amount { text-align: right; font-weight: 600; color: #5e548e; margin: 0.5rem 0; }
        .btn-order { width: 100%; padding: 1rem; background: #c06c9e; color: white; border: none; border-radius: 12px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn-order:hover { background: #a85687; transform: translateY(-2px); }
        .btn-order:disabled { background: #f0eaff; color: #9a8cbb; cursor: not-allowed; }
        .empty-cart { text-align: center; padding: 2rem; color: #9a8cbb; }
        .empty-cart i { font-size: 3rem; opacity: 0.5; }
        .alert { background: #fff1f1; border-left: 4px solid #e06b6b; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; color: #c94f4f; }
        .berry-accent { display: flex; gap: 6px; margin-top: 2rem; justify-content: flex-end; }
        .berry-dot { width: 8px; height: 8px; border-radius: 50%; background: #c06c9e; }
        .berry-dot:nth-child(2) { background: #5e548e; }
        .berry-dot:nth-child(3) { background: #6b8cae; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">
            <div class="brand-main">yobe</div>
            <div class="brand-sub">YOGURT & BERRY BAR</div>
        </div>
        <nav>
            <a href="order.php" class="nav-item active"><i class="fas fa-cash-register"></i> Order</a>
            <a href="menu_kasir.php" class="nav-item"><i class="fas fa-ice-cream"></i> Menu</a>
        </nav>
        <div class="logout-wrapper">
            <a href="../auth/logout.php" class="logout-btn" onclick="return confirmLogout()"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <h1 class="page-title"><i class="fas fa-cash-register"></i> Buat Order Baru</h1>
            <div class="kasir-badge">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['kasir_nama'] ?? 'Kasir') ?>
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="order-grid">
            <!-- Left: Menu Section -->
            <div class="menu-section">
                <div class="section-header"><h3><i class="fas fa-utensils" style="margin-right: 8px; color: #c06c9e;"></i> Pilih Menu</h3></div>
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterMenu('all')">Semua</button>
                    <?php foreach (array_keys($categories) as $cat): ?>
                    <button class="filter-tab" onclick="filterMenu('<?= $cat ?>')"><?= ucfirst($cat) ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="search-box"><i class="fas fa-search"></i><input type="text" id="searchMenu" placeholder="Cari menu..."></div>
                <div class="menu-grid" id="menuGrid">
                    <?php foreach ($menus as $menu): ?>
                    <div class="menu-card" data-category="<?= $menu['nama_kategori'] ?>" data-name="<?= strtolower($menu['nama_menu']) ?>" onclick="addToCart(<?= $menu['id_menu'] ?>, '<?= addslashes($menu['nama_menu']) ?>', <?= $menu['harga'] ?>)">
                        <div class="menu-icon"><i class="fas fa-<?= $menu['nama_kategori'] == 'yogurt' ? 'ice-cream' : 'cookie' ?>"></i></div>
                        <div class="menu-name"><?= htmlspecialchars($menu['nama_menu']) ?></div>
                        <div class="menu-price">Rp <?= number_format($menu['harga'], 0, ',', '.') ?></div>
                        <div class="menu-category"><?= ucfirst($menu['nama_kategori']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Cart Section -->
            <div class="cart-section">
                <div class="cart-header"><h3><i class="fas fa-shopping-cart" style="margin-right: 8px; color: #c06c9e;"></i> Keranjang</h3><span class="cart-count" id="cartCount">0</span></div>
                <input type="text" class="customer-input" id="customerName" placeholder="Nama pemesan *">
                <div class="cart-items" id="cartItems"><div class="empty-cart"><i class="fas fa-shopping-basket"></i><p>Belum ada item</p><small>Klik menu untuk menambah</small></div></div>
                <div class="cart-summary">
                    <div class="summary-row"><span>Subtotal</span><span id="subtotal">Rp 0</span></div>
                    <div class="total-row" id="totalDisplay"><span>Total</span><span>Rp 0</span></div>
                    <select class="payment-method" id="paymentMethod" onchange="handlePaymentMethodChange()">
                        <option value="cash">💵 Cash</option>
                        <option value="qris">📱 QRIS</option>
                    </select>
                    <input type="number" class="payment-input" id="paymentAmount" placeholder="Jumlah bayar" min="0" oninput="calculateChange()" style="display: block;">
                    
                    <!-- QRIS Section -->
                    <div id="qrisSection" style="display: none; text-align: center; margin: 1rem 0;">
                        <div style="background: #f5f0ff; padding: 1.5rem; border-radius: 16px;">
                            <div style="font-size: 1.2rem; font-weight: 600; color: #5e548e; margin-bottom: 1rem;">Total: <span id="qrisTotal">Rp 0</span></div>
                            <img src="../assets/img/qris_yobe.png" alt="QRIS" style="width: 180px; height: 180px; margin: 1rem 0; border: 2px solid white; border-radius: 12px;">
                            
                            <!-- UPLOAD BUKTI TRANSFER -->
                            <div style="margin: 1rem 0; text-align: left;">
                                <label style="display: block; font-size: 12px; font-weight: bold; color: #5e548e; margin-bottom: 5px;">
                                    <i class="fas fa-upload"></i> Upload Bukti Transfer
                                </label>
                                <input type="file" id="buktiTransfer" accept="image/*" style="width: 100%; padding: 5px;">
                                <small style="color: #666; font-size: 10px;">Format: JPG, PNG (Maks 2MB)</small>
                            </div>
                            
                            <div style="font-size: 0.85rem; color: #6b6580; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle"></i> Scan QRIS, transfer sesuai total, lalu upload bukti
                            </div>
                        </div>
                    </div>
                    
                    <div class="change-amount" id="changeDisplay">Kembalian: Rp 0</div>
                    <div style="display: flex; gap: 0.8rem;">
                        <button class="btn-order" id="orderBtn" onclick="processOrder()" style="flex: 1;" disabled><i class="fas fa-save"></i> Simpan</button>
                        <button class="btn-order" id="confirmQrisBtn" onclick="confirmQrisPayment()" style="flex: 1; background: #5e548e; display: none;"><i class="fas fa-check-circle"></i> Konfirmasi Bayar</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="berry-accent"><span class="berry-dot"></span><span class="berry-dot"></span><span class="berry-dot"></span></div>
    </div>

    <script>
    let cart = [];

    document.addEventListener('DOMContentLoaded', function() {
        const savedCart = sessionStorage.getItem('yobe_cart');
        if (savedCart) { cart = JSON.parse(savedCart); renderCart(); }
        handlePaymentMethodChange();
        const addToCartId = sessionStorage.getItem('yobe_add_to_cart');
        if (addToCartId) {
            fetch('proses/get_menu_item.php?id=' + addToCartId)
                .then(response => response.json())
                .then(data => { if (data.success) { addToCart(data.id, data.nama, data.harga); sessionStorage.removeItem('yobe_add_to_cart'); } })
                .catch(error => console.error('Error:', error));
        }
    });

    function filterMenu(category) {
        const tabs = document.querySelectorAll('.filter-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        document.querySelectorAll('.menu-card').forEach(item => {
            item.style.display = (category === 'all' || item.dataset.category === category) ? 'block' : 'none';
        });
    }

    document.getElementById('searchMenu').addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.menu-card').forEach(item => {
            item.style.display = item.dataset.name.includes(searchTerm) ? 'block' : 'none';
        });
    });

    function addToCart(id, name, price) {
        const existing = cart.find(item => item.id === id);
        if (existing) existing.qty++;
        else cart.push({ id, name, price, qty: 1, note: '' });
        renderCart();
    }

    function renderCart() {
        const cartContainer = document.getElementById('cartItems');
        const cartCount = document.getElementById('cartCount');
        const subtotalEl = document.getElementById('subtotal');
        const orderBtn = document.getElementById('orderBtn');
        const confirmBtn = document.getElementById('confirmQrisBtn');
        const method = document.getElementById('paymentMethod').value;
        
        if (cart.length === 0) {
            cartContainer.innerHTML = `<div class="empty-cart"><i class="fas fa-shopping-basket"></i><p>Belum ada item</p><small>Klik menu untuk menambah</small></div>`;
            cartCount.textContent = '0';
            subtotalEl.textContent = 'Rp 0';
            document.getElementById('totalDisplay').innerHTML = '<span>Total</span><span>Rp 0</span>';
            if (method === 'qris') confirmBtn.disabled = true;
            else orderBtn.disabled = true;
        } else {
            let html = '', subtotal = 0;
            cart.forEach((item, index) => {
                subtotal += item.price * item.qty;
                html += `<div class="cart-item">
                            <div class="cart-item-row">
                                <span class="cart-item-name">${item.name}</span>
                                <span class="cart-item-price">Rp ${(item.price * item.qty).toLocaleString()}</span>
                            </div>
                            <div class="cart-item-actions">
                                <button class="qty-btn" onclick="updateQty(${index}, -1)">−</button>
                                <span class="qty-value">${item.qty}</span>
                                <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                            </div>
                            <input type="text" class="cart-item-note" placeholder="Catatan (opsional)" value="${item.note}" onchange="updateNote(${index}, this.value)">
                            <div class="remove-item" onclick="removeItem(${index})">Hapus</div>
                        </div>`;
            });
            cartContainer.innerHTML = html;
            cartCount.textContent = cart.reduce((sum, item) => sum + item.qty, 0);
            subtotalEl.textContent = `Rp ${subtotal.toLocaleString()}`;
            document.getElementById('totalDisplay').innerHTML = `<span>Total</span><span>Rp ${subtotal.toLocaleString()}</span>`;
            const customerName = document.getElementById('customerName').value;
            if (method === 'qris') confirmBtn.disabled = !customerName || cart.length === 0;
            else {
                const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
                orderBtn.disabled = !customerName || cart.length === 0 || payment < subtotal;
            }
        }
        sessionStorage.setItem('yobe_cart', JSON.stringify(cart));
        if (method === 'qris') document.getElementById('qrisTotal').textContent = `Rp ${cart.reduce((sum, item) => sum + (item.price * item.qty), 0).toLocaleString()}`;
        else calculateChange();
    }

    function updateQty(index, change) {
        cart[index].qty += change;
        if (cart[index].qty <= 0) cart.splice(index, 1);
        renderCart();
    }

    function updateNote(index, note) { cart[index].note = note; renderCart(); }
    function removeItem(index) { cart.splice(index, 1); renderCart(); }

    function handlePaymentMethodChange() {
        const method = document.getElementById('paymentMethod').value;
        const paymentInput = document.getElementById('paymentAmount');
        const changeDisplay = document.getElementById('changeDisplay');
        const qrisSection = document.getElementById('qrisSection');
        const orderBtn = document.getElementById('orderBtn');
        const confirmBtn = document.getElementById('confirmQrisBtn');
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        if (method === 'qris') {
            paymentInput.style.display = 'none';
            changeDisplay.style.display = 'none';
            qrisSection.style.display = 'block';
            orderBtn.style.display = 'none';
            confirmBtn.style.display = 'block';
            document.getElementById('qrisTotal').textContent = `Rp ${subtotal.toLocaleString()}`;
            confirmBtn.disabled = !document.getElementById('customerName').value || cart.length === 0;
        } else {
            paymentInput.style.display = 'block';
            changeDisplay.style.display = 'block';
            qrisSection.style.display = 'none';
            orderBtn.style.display = 'block';
            confirmBtn.style.display = 'none';
            calculateChange();
            const payment = parseFloat(paymentInput.value) || 0;
            orderBtn.disabled = !document.getElementById('customerName').value || cart.length === 0 || payment < subtotal;
        }
    }

    function calculateChange() {
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
        const changeDisplay = document.getElementById('changeDisplay');
        const orderBtn = document.getElementById('orderBtn');
        const customerName = document.getElementById('customerName').value;
        const change = payment - subtotal;
        changeDisplay.innerHTML = change >= 0 ? `Kembalian: Rp ${change.toLocaleString()}` : `Kurang: Rp ${Math.abs(change).toLocaleString()}`;
        orderBtn.disabled = !customerName || cart.length === 0 || payment < subtotal;
    }

    document.getElementById('customerName').addEventListener('input', function() {
        const method = document.getElementById('paymentMethod').value;
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        if (method === 'qris') document.getElementById('confirmQrisBtn').disabled = !this.value || cart.length === 0;
        else {
            const payment = parseFloat(document.getElementById('paymentAmount').value) || 0;
            document.getElementById('orderBtn').disabled = !this.value || cart.length === 0 || payment < subtotal;
        }
    });

    function processOrder() {
        const customerName = document.getElementById('customerName').value;
        const paymentAmount = parseFloat(document.getElementById('paymentAmount').value) || 0;
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        if (paymentAmount < subtotal) { alert('Uang tidak cukup!'); return; }
        const orderBtn = document.getElementById('orderBtn');
        orderBtn.disabled = true;
        orderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        fetch('proses/simpan_order.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ customer_name: customerName, payment_method: 'cash', payment_amount: paymentAmount, subtotal, cart_items: cart })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`✅ Order ${data.order_number} untuk ${customerName} berhasil!`);
                window.open('cetak_struk.php?id=' + data.order_id + '&copy=double', '_blank');
                sessionStorage.removeItem('yobe_cart');
                cart = [];
                document.getElementById('customerName').value = '';
                document.getElementById('paymentAmount').value = '';
                renderCart();
                handlePaymentMethodChange();
            } else alert('❌ Gagal: ' + data.message);
        })
        .catch(error => { console.error('Error:', error); alert('Terjadi kesalahan, coba lagi'); })
        .finally(() => { orderBtn.disabled = false; orderBtn.innerHTML = '<i class="fas fa-save"></i> Simpan'; });
    }

    // FUNGSI QRIS DENGAN UPLOAD BUKTI
    function confirmQrisPayment() {
        const customerName = document.getElementById('customerName').value;
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        const buktiFile = document.getElementById('buktiTransfer').files[0];

        if (!customerName) {
            alert('Nama pemesan harus diisi!');
            return;
        }

        if (cart.length === 0) {
            alert('Keranjang masih kosong!');
            return;
        }

        if (!buktiFile) {
            alert('Harap upload bukti transfer terlebih dahulu!');
            return;
        }

        const confirmBtn = document.getElementById('confirmQrisBtn');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengupload...';

        const formData = new FormData();
        formData.append('bukti', buktiFile);
        formData.append('customer_name', customerName);
        formData.append('subtotal', subtotal);
        formData.append('cart_items', JSON.stringify(cart));

        fetch('proses/upload_bukti_qris.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`✅ Order ${data.order_number} untuk ${customerName} berhasil! Bukti tersimpan.`);
                window.open('cetak_struk.php?id=' + data.order_id + '&copy=double', '_blank');
                sessionStorage.removeItem('yobe_cart');
                cart = [];
                document.getElementById('customerName').value = '';
                document.getElementById('buktiTransfer').value = '';
                renderCart();
                handlePaymentMethodChange();
            } else {
                alert('❌ Gagal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan, coba lagi');
        })
        .finally(() => {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check-circle"></i> Konfirmasi Bayar';
        });
    }

    function confirmLogout() { return confirm('Yakin ingin keluar?'); }
    </script>
</body>
</html>