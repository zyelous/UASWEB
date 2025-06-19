<?php
require_once '../includes/auth_user.php';
require_once '../includes/db.php';
checkUserAuth();

$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user's rental statistics for profile summary
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_rentals,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_rentals,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_rentals,
        MIN(created_at) as member_since
    FROM sewa 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user_stats = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Handle profile image upload
    $profile_image = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = '../uploads/profile/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $error = 'Format file tidak didukung! Gunakan JPG, PNG, atau GIF.';
        } elseif ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            $error = 'Ukuran file terlalu besar! Maksimal 2MB.';
        } else {
            // Delete old image if exists
            if ($user['profile_image'] && file_exists($upload_dir . $user['profile_image'])) {
                unlink($upload_dir . $user['profile_image']);
            }
            
            $profile_image = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $profile_image;
            
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = $user['profile_image']; // Revert if upload fails
                $error = 'Gagal mengupload foto profil!';
            }
        }
    }
    
    if (!$error) {
        // Check if email is already used by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error = 'Email sudah digunakan oleh user lain!';
        } else {
            // Update basic info
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $phone, $profile_image, $_SESSION['user_id']]);
            
            if (!$error) {
                $success = 'Profil berhasil diupdate!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                $_SESSION['user_name'] = $user['full_name'];
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
    <title>Profil Saya - EcoKost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/user/profile.css">
    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #43a047, #66bb6a);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-home me-2"></i>KostQ
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kamar_list.php"><i class="fas fa-bed me-1"></i>Kamar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sewa_status.php"><i class="fas fa-file-contract me-1"></i>Sewa Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-user-edit me-1"></i>Profil</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Profil Saya</h1>
                    <p class="mb-0">Kelola informasi pribadi dan pengaturan akun Anda</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-user-cog" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div>
                    <?php if ($user['profile_image']): ?>
                        <img src="../uploads/profile/<?php echo $user['profile_image']; ?>" 
                             class="profile-avatar" alt="Profile">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h3 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p class="mb-0 opacity-75">@<?php echo htmlspecialchars($user['username']); ?></p>
                    
                    <?php if ($user_stats['member_since']): ?>
                        <div class="member-badge">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Member sejak <?php echo date('M Y', strtotime($user_stats['member_since'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $user_stats['total_rentals']; ?></span>
                        <span class="stat-label">Total Sewa</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $user_stats['active_rentals']; ?></span>
                        <span class="stat-label">Sewa Aktif</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $user_stats['completed_rentals']; ?></span>
                        <span class="stat-label">Selesai</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-card">
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

            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="form-section">
                    <h4 class="section-title">
                        <i class="fas fa-user"></i>
                        Informasi Dasar
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-at"></i>Username
                                </label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">
                                    <i class="fas fa-user-tag"></i>Nama Lengkap
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>No. Telepon
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                       placeholder="+62 xxx xxxx xxxx">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn-eco me-3">
                        <i class="fas fa-save me-1"></i>Update Profil
                    </button>
                    <a href="dashboard.php" class="btn-outline-eco">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </form>
        </div>

        <div class="row mt-4 mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e8, #f1f8e9); border-radius: 20px;">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt text-success" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="text-success">Keamanan Akun</h5>
                        <p class="text-muted">Pastikan informasi akun Anda selalu aman dan terkini</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd, #f1f8e9); border-radius: 20px;">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-headset text-primary" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="text-success">Butuh Bantuan?</h5>
                        <p class="text-muted">Tim support kami siap membantu Anda</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-4" style="background: linear-gradient(135deg, #43a047, #66bb6a); color: white;">
        <div class="container text-center">
            <p class="mb-0">&copy; 2025 KostQ - Penyewaan Kost Ramah Lingkungan</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>   
</body>
</html>
