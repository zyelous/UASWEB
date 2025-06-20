<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: user/dashboard.php');
    exit();
}

// Check for registration success message
if (isset($_GET['registered']) && $_GET['registered'] == 'success') {
    $success = 'Registrasi berhasil! Silakan login dengan akun Anda.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);
    
    if (empty($username) || empty($password)) {
        $error = 'Username/email dan password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_name'] = $user['full_name'];
            
            // Handle remember me functionality
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                
                // Store token in database (you might want to create a remember_tokens table)
                $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $stmt->execute([$token, $user['id']]);
            }
            
            // Redirect to intended page or dashboard
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'user/dashboard.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Username/email atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KostQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/user/login_user.css">

</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7 col-sm-9">
                <div class="login-container">
                    <!-- Header Section -->
                    <div class="login-header">
                        <div class="brand-logo">
                            <i class="fas fa-home"></i>
                        </div>
                        <h1 class="login-title">Selamat Datang</h1>
                        <p class="login-subtitle">Masuk ke akun KostQ Anda</p>
                    </div>
                    
                    <!-- Body Section -->
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-eco">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-eco">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm">
                            <!-- Username/Email Input -->
                            <div class="input-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="Username atau Email" required 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                            </div>
                            
                            <!-- Password Input -->
                            <div class="input-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            
                            
                            <!-- Login Button -->
                            <button type="submit" class="btn-login" id="loginBtn">
                                <div class="loading-spinner"></div>
                                <span class="btn-text">
                                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                                </span>
                            </button>
                        </form>
                        
                        
                        <!-- Auth Links -->
                        <div class="auth-links">
                            <a href="register_user.php" class="auth-link">
                                <i class="fas fa-user-plus me-1"></i>
                                Belum punya akun? Daftar
                            </a>
                            <br>
                            <a href="login_admin.php" class="auth-link">
                                <i class="fas fa-user-shield me-1"></i>
                                Login sebagai Admin
                            </a>
                            <br>
                            
                        </div>
                    </div>
                    
                    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        // Input focus effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });

        // Enter key handling
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });

        // Smooth animations on load
        window.addEventListener('load', function() {
            document.querySelector('.login-container').style.opacity = '0';
            document.querySelector('.login-container').style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                document.querySelector('.login-container').style.transition = 'all 0.6s ease';
                document.querySelector('.login-container').style.opacity = '1';
                document.querySelector('.login-container').style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
