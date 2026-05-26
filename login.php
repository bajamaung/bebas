<?php
require 'config/database.php';
require 'includes/auth.php';

// Cek jika sudah login
if (isLoggedIn()) {
    redirectByRole($_SESSION['role']);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'CSRF Token tidak valid!';
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        // Validasi input
        if (empty($username) || empty($password)) {
            $error = 'Username dan password harus diisi!';
        } else {
            // Prepared statement untuk keamanan
            $stmt = $conn->prepare("SELECT id, nama, username, password, role, status FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Cek status user
                if ($user['status'] != 'aktif') {
                    $error = 'Akun Anda telah dinonaktifkan!';
                } else if (verifyPassword($password, $user['password'])) {
                    // Password benar
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Set remember me cookie jika dipilih
                    if (isset($_POST['remember']) && $_POST['remember'] == '1') {
                        setcookie('remember_username', $username, time() + (86400 * 30), '/');
                    }
                    
                    // Log activity
                    logActivity($conn, $user['id'], 'Login', 'User melakukan login', 'users', $user['id']);
                    
                    // Redirect berdasarkan role
                    redirectByRole($user['role']);
                } else {
                    $error = 'Password salah!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
            }
        }
    }
}

// Ambil remember me value jika ada
$remember_username = isset($_COOKIE['remember_username']) ? htmlspecialchars($_COOKIE['remember_username']) : '';
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bangjo Sistem Peminjaman</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(30px);
            }
        }

        .login-container {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 90%;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-left {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-width: 50%;
        }

        .login-left h1 {
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .login-left p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .login-left-icon {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            font-size: 50px;
        }

        .login-right {
            padding: 60px 40px;
            min-width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-right h2 {
            font-size: 28px;
            color: #0f172a;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-right > p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 25px;
            animation: slideInRight 0.6s ease-out;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .form-group label {
            display: block;
            color: #374151;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #f9fafb;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
            font-size: 18px;
            user-select: none;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .remember-forgot label {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #374151;
            font-weight: 500;
            margin: 0;
        }

        .remember-forgot input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            cursor: pointer;
            accent-color: #2563eb;
        }

        .remember-forgot a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .remember-forgot a:hover {
            color: #1e40af;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border-left: 4px solid #16a34a;
        }

        .test-credentials {
            background-color: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 15px;
            margin-top: 30px;
            font-size: 13px;
            color: #1e3a8a;
            line-height: 1.6;
        }

        .test-credentials strong {
            display: block;
            margin-bottom: 8px;
            color: #1e40af;
        }

        .test-credentials code {
            background-color: #dbeafe;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 95%;
            }

            .login-left, .login-right {
                min-width: 100%;
                padding: 40px 25px;
            }

            .login-left {
                padding: 40px 25px;
            }

            .login-left h1 {
                font-size: 28px;
            }

            .login-left p {
                font-size: 14px;
            }

            .login-right h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side -->
        <div class="login-left">
            <div class="login-left-icon">📦</div>
            <h1>Bangjo</h1>
            <p>Sistem Peminjaman Alat Profesional dan Terpercaya</p>
            <p style="font-size: 14px; margin-top: 20px;">Kelola peminjaman alat dengan mudah, cepat, dan efisien</p>
        </div>

        <!-- Right Side -->
        <div class="login-right">
            <h2>Masuk</h2>
            <p>Silakan masuk dengan akun Anda</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    ⚠️ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Masukkan username Anda"
                        value="<?php echo htmlspecialchars($remember_username); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Masukkan password Anda"
                            required
                        >
                        <span class="password-toggle" onclick="togglePassword()">👁️</span>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn-login">Masuk</button>
            </form>

            <div class="test-credentials">
                <strong>🔐 Akun Demo:</strong>
                Admin: <code>admin</code> / <code>admin123</code><br>
                Petugas: <code>petugas</code> / <code>admin123</code><br>
                Peminjam: <code>peminjam</code> / <code>admin123</code>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggle.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggle.textContent = '👁️';
            }
        }

        // Auto-hide error message setelah 5 detik
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideDown 0.3s ease-out reverse';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html>
