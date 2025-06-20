<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    
    if ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak sama!';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            $error = 'Username atau email sudah digunakan!';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan saat registrasi!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi User - Penyewaan Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-info text-white text-center">
                        <h4>Registrasi User</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">No. Telepon</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            
                            <button type="submit" class="btn btn-info w-100">Daftar</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="login_user.php" class="text-decoration-none">Sudah punya akun? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <footer class="mt-5 py-4" style="background: linear-gradient(135deg, #43a047, #66bb6a); color: white;">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 KostQ - Penyewaan Kost Ramah Lingkungan</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
