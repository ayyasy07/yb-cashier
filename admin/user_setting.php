<?php
session_start();
if (!isset($_SESSION['admin_login'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

require_once '../config/database.php';

$admin_id = $_SESSION['admin_id'];
$admin_email = $_SESSION['admin_email'];

// Ambil data kasir (user dengan role kasir)
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'kasir' LIMIT 1");
    $stmt->execute();
    $kasir = $stmt->fetch();
    
    if (!$kasir) {
        $kasir = [
            'id_users' => 0,
            'nama' => 'Belum ada kasir',
            'email' => 'kasir@yobe.com',
            'is_active' => 0
        ];
    }
} catch (PDOException $e) {
    $error = "Gagal mengambil data: " . $e->getMessage();
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {
    
    // Update password admin
    if ($_POST['form_type'] === 'admin') {
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';
        
        if (empty($password_baru) || empty($konfirmasi)) {
            $_SESSION['error_admin'] = 'Password baru dan konfirmasi harus diisi';
        } elseif (strlen($password_baru) < 8) {
            $_SESSION['error_admin'] = 'Password minimal 8 karakter';
        } elseif ($password_baru !== $konfirmasi) {
            $_SESSION['error_admin'] = 'Password baru dan konfirmasi tidak cocok';
        } else {
            try {
                $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_users = ?");
                $stmt->execute([$hash_baru, $admin_id]);
                $_SESSION['success_admin'] = 'Password admin berhasil diubah';
            } catch (PDOException $e) {
                $_SESSION['error_admin'] = 'Gagal mengubah password: ' . $e->getMessage();
            }
        }
        header('Location: user_setting.php');
        exit;
    }
    
    // Update data kasir
    if ($_POST['form_type'] === 'kasir') {
        $nama_kasir = $_POST['nama_kasir'] ?? '';
        $email_kasir = $_POST['email_kasir'] ?? '';
        $password_baru = $_POST['password_kasir'] ?? '';
        
        if (empty($nama_kasir) || empty($email_kasir)) {
            $_SESSION['error_kasir'] = 'Nama dan email harus diisi';
        } else {
            try {
                if ($kasir['id_users'] > 0) {
                    if (!empty($password_baru)) {
                        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, password = ? WHERE id_users = ?");
                        $stmt->execute([$nama_kasir, $email_kasir, $hash_baru, $kasir['id_users']]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ? WHERE id_users = ?");
                        $stmt->execute([$nama_kasir, $email_kasir, $kasir['id_users']]);
                    }
                    $_SESSION['success_kasir'] = 'Data kasir berhasil diperbarui';
                } else {
                    $hash_default = password_hash('kasir123', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, is_active) VALUES (?, ?, ?, 'kasir', 1)");
                    $stmt->execute([$nama_kasir, $email_kasir, $hash_default]);
                    $_SESSION['success_kasir'] = 'Akun kasir berhasil dibuat';
                }
            } catch (PDOException $e) {
                $_SESSION['error_kasir'] = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
        header('Location: user_setting.php');
        exit;
    }
    
    // Reset password kasir ke default
    if ($_POST['form_type'] === 'reset_kasir' && $kasir['id_users'] > 0) {
        try {
            $hash_default = password_hash('kasir123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id_users = ?");
            $stmt->execute([$hash_default, $kasir['id_users']]);
            $_SESSION['success_kasir'] = 'Password kasir telah direset ke "kasir123"';
        } catch (PDOException $e) {
            $_SESSION['error_kasir'] = 'Gagal reset password: ' . $e->getMessage();
        }
        header('Location: user_setting.php');
        exit;
    }
    
    // Nonaktifkan kasir (update is_active = 0)
    if ($_POST['form_type'] === 'deactivate_kasir' && $kasir['id_users'] > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id_users = ?");
            $stmt->execute([$kasir['id_users']]);
            $_SESSION['success_kasir'] = 'Akun kasir telah dinonaktifkan';
        } catch (PDOException $e) {
            $_SESSION['error_kasir'] = 'Gagal menonaktifkan: ' . $e->getMessage();
        }
        header('Location: user_setting.php');
        exit;
    }
    
    // Aktifkan kasir (update is_active = 1)
    if ($_POST['form_type'] === 'activate_kasir') {
        try {
            if ($kasir['id_users'] > 0) {
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE id_users = ?");
                $stmt->execute([$kasir['id_users']]);
                $_SESSION['success_kasir'] = 'Akun kasir telah diaktifkan kembali';
            } else {
                $hash_default = password_hash('kasir123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (nama, email, password, role, is_active) VALUES (?, ?, ?, 'kasir', 1)");
                $stmt->execute(['Kasir Yobe', 'kasir@yobe.com', $hash_default]);
                $_SESSION['success_kasir'] = 'Akun kasir berhasil dibuat dan diaktifkan';
            }
        } catch (PDOException $e) {
            $_SESSION['error_kasir'] = 'Gagal mengaktifkan: ' . $e->getMessage();
        }
        header('Location: user_setting.php');
        exit;
    }
}

$success_admin = $_SESSION['success_admin'] ?? '';
$error_admin = $_SESSION['error_admin'] ?? '';
$success_kasir = $_SESSION['success_kasir'] ?? '';
$error_kasir = $_SESSION['error_kasir'] ?? '';
unset($_SESSION['success_admin'], $_SESSION['error_admin'], $_SESSION['success_kasir'], $_SESSION['error_kasir']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan · YOBE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', -apple-system, sans-serif; }
        body { background: #faf8ff; display: flex; min-height: 100vh; color: #2d2a3a; }
        .sidebar { width: 280px; background: #ffffff; height: 100vh; position: fixed; padding: 2rem 1.5rem; border-right: 1px solid #f0eaff; display: flex; flex-direction: column; }
        .brand { margin-bottom: 2rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f5f0ff; }
        .brand-main { font-size: 2.2rem; font-weight: 700; background: linear-gradient(135deg, #5e548e, #be95c4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: block; }
        .brand-sub { font-size: 0.7rem; color: #9a8cbb; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; display: block; }
        .nav-item { display: flex; align-items: center; gap: 14px; padding: 0.9rem 1.2rem; color: #6b6580; text-decoration: none; border-radius: 14px; margin-bottom: 0.4rem; font-weight: 500; transition: all 0.2s; }
        .nav-item i { width: 22px; }
        .nav-item:hover { background: #f5f0ff; color: #5e548e; }
        .nav-item.active { background: #5e548e; color: white; }
        .logout-wrapper { margin-top: auto; border-top: 2px solid #f0eaff; padding-top: 1.5rem; }
        .logout-btn { display: flex; align-items: center; gap: 14px; padding: 0.9rem 1.2rem; color: #e06b6b; text-decoration: none; border-radius: 14px; font-weight: 500; transition: all 0.2s; }
        .logout-btn:hover { background: #fff1f1; color: #c94f4f; }
        .main-content { margin-left: 280px; flex: 1; padding: 2rem 2.5rem; background: #faf8ff; height: 100vh; overflow-y: auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 2rem; font-weight: 600; background: linear-gradient(135deg, #2d2a3a, #5e548e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .page-title i { margin-right: 12px; color: #be95c4; }
        .user-badge { background: white; padding: 0.7rem 1.5rem; border-radius: 50px; font-size: 0.9rem; color: #6b6580; border: 2px solid #f0eaff; display: flex; align-items: center; gap: 10px; font-weight: 500; }
        .alert { padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
        .alert.success { background: #e4f3e5; color: #2f8f5c; border-left: 4px solid #2f8f5c; }
        .alert.error { background: #fff1f1; color: #c94f4f; border-left: 4px solid #e06b6b; }
        .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .settings-card { background: white; border-radius: 24px; border: 1px solid #f0eaff; padding: 1.8rem; transition: all 0.2s; box-shadow: 0 8px 20px rgba(94, 84, 142, 0.05); }
        .settings-card:hover { box-shadow: 0 15px 30px rgba(94, 84, 142, 0.15); }
        .card-header { display: flex; align-items: center; gap: 15px; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f5f0ff; }
        .card-icon { width: 48px; height: 48px; background: #f5f0ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #5e548e; font-size: 1.3rem; }
        .card-header h3 { font-weight: 600; color: #2d2a3a; font-size: 1.1rem; }
        .card-header p { color: #9a8cbb; font-size: 0.8rem; margin-top: 4px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-label { font-size: 0.8rem; color: #5e548e; margin-bottom: 0.4rem; font-weight: 600; text-transform: uppercase; display: block; }
        .form-label i { margin-right: 6px; color: #be95c4; }
        .form-input { width: 100%; padding: 0.8rem 1rem; border: 2px solid #f0eaff; border-radius: 12px; font-size: 0.9rem; background: #faf8ff; }
        .form-input:focus { outline: none; border-color: #5e548e; background: white; }
        .btn-save { background: #5e548e; color: white; border: none; padding: 0.8rem; border-radius: 12px; font-weight: 600; cursor: pointer; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 0.5rem; }
        .btn-save:hover { background: #4e457a; transform: translateY(-2px); }
        .action-group { display: flex; gap: 0.8rem; margin: 1rem 0; }
        .btn-secondary, .btn-danger, .btn-active { flex: 1; padding: 0.8rem; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.85rem; border: none; }
        .btn-secondary { background: #f5f0ff; color: #5e548e; border: 1px solid #f0eaff; }
        .btn-secondary:hover { background: #e8e0ff; }
        .btn-danger { background: #fff1f1; color: #e06b6b; border: 1px solid #ffe0e0; }
        .btn-danger:hover { background: #ffe5e5; }
        .btn-active { background: #2f8f5c; color: white; }
        .btn-active:hover { background: #1e6b45; }
        .kasir-info { background: #faf8ff; border-radius: 16px; padding: 1.2rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem; border: 1px solid #f0eaff; }
        .kasir-avatar { width: 48px; height: 48px; background: #be95c4; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; }
        .kasir-detail h4 { font-weight: 600; color: #2d2a3a; font-size: 1rem; }
        .kasir-detail p { color: #6b6580; font-size: 0.85rem; margin-top: 4px; }
        .status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 30px; font-size: 0.7rem; font-weight: 600; margin-left: 8px; }
        .status-badge.active { background: #e4f3e5; color: #2f8f5c; }
        .status-badge.inactive { background: #ffebee; color: #c62828; }
        .kasir-id { background: #f5f0ff; padding: 0.2rem 0.6rem; border-radius: 30px; font-size: 0.65rem; font-weight: 600; color: #5e548e; margin-left: 6px; }
        .info-card { background: #f5f0ff; border-radius: 12px; padding: 1rem; margin-top: 1.5rem; border-left: 3px solid #5e548e; font-size: 0.85rem; }
        .info-card i { color: #5e548e; margin-right: 8px; }
        .info-card small { color: #6b6580; display: block; margin-top: 0.5rem; font-size: 0.75rem; }
        .berry-accent { display: flex; gap: 6px; margin-top: 2rem; justify-content: flex-end; }
        .berry-dot { width: 8px; height: 8px; border-radius: 50%; background: #be95c4; }
        .berry-dot:nth-child(2) { background: #5e548e; }
        .berry-dot:nth-child(3) { background: #6b8cae; }
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
        <a href="laporan.php" class="nav-item"><i class="fas fa-file-alt"></i> Laporan</a>
        <a href="user_setting.php" class="nav-item active"><i class="fas fa-cog"></i> Pengaturan</a>
    </nav>
    <div class="logout-wrapper">
        <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h1 class="page-title"><i class="fas fa-cog"></i> Pengaturan</h1>
        <div class="user-badge">
            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($admin_email) ?>
        </div>
    </div>

    <?php if ($success_admin): ?>
    <div class="alert success"><i class="fas fa-check-circle"></i> <?= $success_admin ?></div>
    <?php endif; ?>
    <?php if ($error_admin): ?>
    <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $error_admin ?></div>
    <?php endif; ?>
    <?php if ($success_kasir): ?>
    <div class="alert success"><i class="fas fa-check-circle"></i> <?= $success_kasir ?></div>
    <?php endif; ?>
    <?php if ($error_kasir): ?>
    <div class="alert error"><i class="fas fa-exclamation-circle"></i> <?= $error_kasir ?></div>
    <?php endif; ?>

    <div class="settings-grid">
        <!-- Admin Settings -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-user-shield"></i></div>
                <div><h3>Akun Admin</h3><p>Ubah password admin</p></div>
            </div>
            <form method="POST">
                <input type="hidden" name="form_type" value="admin">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email Admin</label>
                    <input type="email" class="form-input" value="<?= htmlspecialchars($admin_email) ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-lock"></i> Password Baru</label>
                    <input type="password" name="password_baru" class="form-input" placeholder="Minimal 8 karakter" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-check-circle"></i> Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" class="form-input" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Password</button>
            </form>
            <div class="info-card"><i class="fas fa-shield-alt"></i> Tips keamanan<small>Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol. Minimal 8 karakter.</small></div>
        </div>

        <!-- Kasir Settings -->
        <div class="settings-card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-cash-register"></i></div>
                <div><h3>Akun Kasir</h3><p>Kelola akun kasir</p></div>
            </div>

            <div class="kasir-info">
                <div class="kasir-avatar"><i class="fas fa-user-tie"></i></div>
                <div class="kasir-detail">
                    <h4><?= htmlspecialchars($kasir['nama']) ?>
                        <?php if ($kasir['id_users'] > 0): ?>
                        <span class="kasir-id">#KSR<?= str_pad($kasir['id_users'], 3, '0', STR_PAD_LEFT) ?></span>
                        <?php endif; ?>
                        <?php if ($kasir['id_users'] > 0): ?>
                            <?php if ($kasir['is_active'] == 1): ?>
                            <span class="status-badge active">Aktif</span>
                            <?php else: ?>
                            <span class="status-badge inactive">Nonaktif</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h4>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($kasir['email']) ?></p>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="form_type" value="kasir">
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Nama Kasir</label>
                    <input type="text" name="nama_kasir" class="form-input" value="<?= htmlspecialchars($kasir['nama']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-envelope"></i> Email Kasir</label>
                    <input type="email" name="email_kasir" class="form-input" value="<?= htmlspecialchars($kasir['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="fas fa-key"></i> Password Baru (opsional)</label>
                    <input type="password" name="password_kasir" class="form-input" placeholder="Kosongkan jika tidak diubah">
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </form>

            <?php if ($kasir['id_users'] > 0): ?>
                <?php if ($kasir['is_active'] == 1): ?>
                <div class="action-group">
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="form_type" value="reset_kasir">
                        <button type="submit" class="btn-secondary" onclick="return confirm('Reset password kasir ke default (kasir123)?')"><i class="fas fa-undo-alt"></i> Reset</button>
                    </form>
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="form_type" value="deactivate_kasir">
                        <button type="submit" class="btn-danger" onclick="return confirm('Nonaktifkan akun kasir? Kasir tidak bisa login.')"><i class="fas fa-ban"></i> Nonaktifkan</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="action-group">
                    <form method="POST" style="flex: 1;">
                        <input type="hidden" name="form_type" value="activate_kasir">
                        <button type="submit" class="btn-active" onclick="return confirm('Aktifkan akun kasir? Kasir bisa login kembali.')"><i class="fas fa-check-circle"></i> Aktifkan Kasir</button>
                    </form>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="action-group">
                <form method="POST" style="flex: 1;">
                    <input type="hidden" name="form_type" value="activate_kasir">
                    <button type="submit" class="btn-active" onclick="return confirm('Buat akun kasir baru? Email: kasir@yobe.com, Password: kasir123')"><i class="fas fa-plus-circle"></i> Buat Kasir</button>
                </form>
            </div>
            <?php endif; ?>

            <div class="info-card" style="border-left-color: #be95c4;">
                <i class="fas fa-info-circle"></i> Hanya ada 1 akun kasir
                <small>Jika dinonaktifkan, kasir tidak bisa login sampai diaktifkan kembali.</small>
            </div>
        </div>
    </div>

    <div class="berry-accent">
        <span class="berry-dot"></span><span class="berry-dot"></span><span class="berry-dot"></span><span class="berry-dot"></span>
    </div>
</div>

<script>
    function confirmLogout() {
        return confirm('Yakin ingin keluar?');
    }
</script>
</body>
</html>