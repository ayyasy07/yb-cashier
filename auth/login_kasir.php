<?php
session_start();

// Jika sudah login, redirect ke order
if (isset($_SESSION['kasir_login'])) {
    header('Location: ../kasir/order.php');
    exit;
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kasir · YOBE</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2d2a3a;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 80%;
            height: 150%;
            background: radial-gradient(circle at center, #fdf0f5 0%, transparent 70%);
            z-index: 0;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 70%;
            height: 100%;
            background: radial-gradient(circle at center, #f5f0ff 0%, transparent 70%);
            z-index: 0;
        }

        .container {
            width: 100%;
            max-width: 420px;
            padding: 1.5rem;
            position: relative;
            z-index: 10;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 40px;
            padding: 2.8rem 2.2rem;
            box-shadow: 0 30px 60px rgba(192, 108, 158, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #c06c9e, #be95c4, #6b8cae);
            z-index: 2;
        }

        .brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-main {
            font-size: 3.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #c06c9e, #9a4d7a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 0.85rem;
            color: #9a8cbb;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .brand-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #c06c9e;
            border-radius: 50%;
            margin: 0 4px;
        }

        .login-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-title h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d2a3a;
            margin-bottom: 0.5rem;
        }

        .login-title p {
            color: #9a8cbb;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #c06c9e;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-label i {
            margin-right: 6px;
            color: #be95c4;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1.2rem;
            color: #9a8cbb;
            font-size: 1rem;
            z-index: 1;
        }

        .form-input {
            width: 100%;
            padding: 1rem 3rem 1rem 3rem;
            border: 2px solid #f0eaff;
            border-radius: 60px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #c06c9e;
            box-shadow: 0 0 0 4px rgba(192, 108, 158, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 1.2rem;
            color: #9a8cbb;
            cursor: pointer;
            font-size: 1rem;
            z-index: 2;
            transition: all 0.2s ease;
        }

        .toggle-password:hover {
            color: #c06c9e;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #c06c9e, #9a4d7a);
            color: white;
            border: none;
            border-radius: 60px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px rgba(192, 108, 158, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 35px rgba(192, 108, 158, 0.4);
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #6b6580;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .back-link a:hover {
            color: #c06c9e;
            gap: 10px;
        }

        .alert {
            background: #fff1f1;
            border-left: 4px solid #e06b6b;
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c94f4f;
            font-size: 0.9rem;
        }

        .alert.success {
            background: #e4f3e5;
            border-left-color: #2f8f5c;
            color: #2f8f5c;
        }

        .demo-credentials {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fdf0f5;
            border-radius: 20px;
            border: 1px dashed #c06c9e;
            font-size: 0.8rem;
            color: #c06c9e;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="brand">
                <div class="brand-main">
                    yobe<span class="brand-dot"></span>
                </div>
                <div class="brand-sub">kasir</div>
            </div>

            <div class="login-title">
                <h2>Login Kasir</h2>
                <p>Masuk ke aplikasi kasir YOBE</p>
            </div>

            <?php if ($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form action="proses_login_kasir.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email Kasir
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" class="form-input" name="email_login" placeholder="Masukkan Email" required>
                    </div>
                </div>
                        
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" class="form-input" name="password_login" id="password" placeholder="Masukkan Password" required>
                        <!-- HANYA 1 ICON - menggunakan class toggle-password -->
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                    </div>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login Kasir
                </button>
            </form>

        
        </div>
    </div>
    
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this;
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>