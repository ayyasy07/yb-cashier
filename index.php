<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YOBE · Yogurt & Berry Bar</title>
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
            min-height: 100vh;
            background: linear-gradient(145deg, #faf8ff 0%, #fdf0f5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            color: #2d2a3a;
        }

        /* Animated background berries */
        .floating-berries {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            pointer-events: none;
        }

        .berry {
            position: absolute;
            border-radius: 50%;
            filter: blur(1px);
            animation: float 8s infinite ease-in-out;
        }

        .berry-1 {
            width: 180px;
            height: 180px;
            background: radial-gradient(circle at 30% 30%, #c06c9e20, #c06c9e10);
            top: -50px;
            right: -30px;
            animation-delay: 0s;
        }

        .berry-2 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle at 70% 70%, #5e548e20, #5e548e10);
            bottom: -80px;
            left: -50px;
            animation-delay: 2s;
        }

        .berry-3 {
            width: 120px;
            height: 120px;
            background: radial-gradient(circle at 50% 50%, #be95c430, #be95c415);
            top: 20%;
            left: 10%;
            animation-delay: 4s;
        }

        .berry-4 {
            width: 90px;
            height: 90px;
            background: radial-gradient(circle at 40% 40%, #6b8cae30, #6b8cae15);
            bottom: 20%;
            right: 15%;
            animation-delay: 1s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Main container */
        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 600px;
            padding: 2rem;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Glass card */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 48px;
            padding: 3.5rem 2.5rem;
            box-shadow: 0 30px 60px rgba(94, 84, 142, 0.15),
                        0 0 0 1px rgba(255, 255, 255, 0.8) inset;
            border: 1px solid rgba(255, 255, 255, 0.5);
            text-align: center;
        }

        /* Logo section */
        .logo-section {
            margin-bottom: 2rem;
        }

        .logo-main {
            font-size: 5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #5e548e, #c06c9e, #6b8cae);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -2px;
            line-height: 1.1;
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .logo-main::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 10%;
            width: 80%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #c06c9e, #5e548e, #c06c9e, transparent);
            border-radius: 2px;
        }

        .logo-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #c06c9e;
            border-radius: 50%;
            margin: 0 4px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.2); background: #5e548e; }
        }

        .logo-sub {
            font-size: 0.9rem;
            color: #9a8cbb;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 0.8rem;
        }

        /* Kata-kata motivasi */
        .quote-section {
            margin: 2.5rem 0;
            padding: 1.5rem 0;
            position: relative;
        }

        .quote-section::before,
        .quote-section::after {
            content: '';
            position: absolute;
            left: 20%;
            right: 20%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #c06c9e40, #5e548e40, #c06c9e40, transparent);
        }

        .quote-section::before {
            top: 0;
        }

        .quote-section::after {
            bottom: 0;
        }

        .quote-icon {
            font-size: 2rem;
            color: #c06c9e;
            opacity: 0.5;
            margin-bottom: 0.5rem;
        }

        .quote-text {
            font-size: 1.4rem;
            font-weight: 500;
            line-height: 1.5;
            color: #2d2a3a;
            max-width: 400px;
            margin: 0 auto;
        }

        .quote-author {
            margin-top: 0.8rem;
            color: #9a8cbb;
            font-size: 0.9rem;
            font-style: italic;
        }

        /* Versi lebih pendek (untuk mobile) */
        .quote-short {
            display: none;
        }

        /* Start button */
        .start-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 1.2rem 3rem;
            background: linear-gradient(135deg, #5e548e, #c06c9e);
            color: white;
            text-decoration: none;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.3rem;
            letter-spacing: 1px;
            box-shadow: 0 20px 35px rgba(94, 84, 142, 0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            cursor: pointer;
            margin-top: 1.5rem;
            width: 100%;
            max-width: 320px;
        }

        .start-btn i {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .start-btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 30px 45px rgba(94, 84, 142, 0.4);
        }

        .start-btn:hover i {
            transform: translateX(8px);
        }

        .start-btn:active {
            transform: translateY(0) scale(0.98);
        }

        /* Small note */
        .footer-note {
            margin-top: 2rem;
            color: #9a8cbb;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .footer-note i {
            color: #c06c9e;
            font-size: 0.7rem;
        }

        .footer-note a {
            color: #5e548e;
            text-decoration: none;
            font-weight: 500;
        }

        .footer-note a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .glass-card {
                padding: 2.5rem 1.5rem;
            }

            .logo-main {
                font-size: 4rem;
            }

            .quote-text {
                font-size: 1.2rem;
            }

            .quote-long {
                display: none;
            }

            .quote-short {
                display: block;
            }

            .start-btn {
                padding: 1rem 2rem;
                font-size: 1.1rem;
                max-width: 280px;
            }
        }

        @media (max-width: 400px) {
            .logo-main {
                font-size: 3.2rem;
            }

            .quote-text {
                font-size: 1rem;
            }
        }

        /* Loading animation for button (optional) */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.7);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Floating berries background -->
    <div class="floating-berries">
        <div class="berry berry-1"></div>
        <div class="berry berry-2"></div>
        <div class="berry berry-3"></div>
        <div class="berry berry-4"></div>
    </div>

    <div class="container">
        <div class="glass-card">
            <!-- Logo YOBE -->
            <div class="logo-section">
                <div class="logo-main">
                    yobe<span class="logo-dot"></span>
                </div>
                <div class="logo-sub">yogurt & berry bar</div>
            </div>

            <div class="quote-section">
                <div class="quote-icon">
                    <i class="fas fa-quote-right"></i>
                </div>
                
                <div class="quote-text quote-long">
                    "Setiap senyuman pelanggan<br>
                    adalah bonus untuk hati."
                </div>
                
                <div class="quote-text quote-short">
                    "Senyum pelanggan,<br>bonus hati."
                </div>
                
                <div class="quote-author">
                    — untuk kasir YOBE
                </div>
            </div>

            <a href="auth/login_kasir.php" class="start-btn ripple" onclick="animateButton(this)">
                <i class="fas fa-arrow-right-to-bracket"></i>
                Mulai
                <i class="fas fa-arrow-right"></i>
            </a>

            <!-- Footer note -->
            <div class="footer-note">
                <i class="fas fa-circle"></i>
                <span>Khusus untuk kasir YOBE</span>
                <i class="fas fa-circle"></i>
            </div>
        </div>
    </div>

    <script>
        // Optional: tambahan animasi untuk button
        function animateButton(button) {
            button.classList.add('ripple');
            
            // Buat efek ripple
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.width = '10px';
            ripple.style.height = '10px';
            ripple.style.background = 'rgba(255, 255, 255, 0.5)';
            ripple.style.borderRadius = '50%';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }

        // Efek hover tambahan pada logo
        document.querySelector('.logo-main').addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.02)';
            this.style.transition = 'transform 0.3s ease';
        });

        document.querySelector('.logo-main').addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });

        // Random quote jika ingin (tapi sesuai permintaan, cukup 1)
        // const quotes = [
        //     "Setiap senyuman pelanggan adalah bonus untuk hati.",
        //     "Hidup ini seperti yogurt, nikmati setiap rasanya.",
        //     "Jadilah berry yang manis dalam hari pelanggan."
        // ];
        // Tapi sesuai permintaan, cukup 1 quote saja.
    </script>
</body>
</html>