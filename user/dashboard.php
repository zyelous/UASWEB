<?php
require_once '../includes/auth_user.php';
require_once '../includes/db.php';
checkUserAuth();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_sewa FROM sewa WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_sewa = $stmt->fetch()['total_sewa'];

$stmt = $pdo->prepare("SELECT COUNT(*) as active_sewa FROM sewa WHERE user_id = ? AND status = 'active'");
$stmt->execute([$_SESSION['user_id']]);
$active_sewa = $stmt->fetch()['active_sewa'];

$stmt = $pdo->prepare("SELECT COUNT(*) as pending_sewa FROM sewa WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$pending_sewa = $stmt->fetch()['pending_sewa'];
$stmt = $pdo->query("SELECT COUNT(*) as available_rooms FROM kamar WHERE status = 'tersedia'");
$available_rooms = $stmt->fetch()['available_rooms'];

$stmt = $pdo->prepare("
    SELECT s.*, k.nama_kamar, k.foto 
    FROM sewa s 
    JOIN kamar k ON s.kamar_id = k.id 
    WHERE s.user_id = ? 
    ORDER BY s.created_at DESC 
    LIMIT 3
");
$stmt->execute([$_SESSION['user_id']]);
$recent_rentals = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Penyewaan Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/user/dashboard.css">
    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #43a047, #66bb6a);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-home me-2"></i>KostQ
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kamar_list.php"><i class="fas fa-bed me-1"></i>Kamar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sewa_status.php"><i class="fas fa-file-contract me-1"></i>Sewa Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="profile.php"><i class="fas fa-user-edit me-1"></i>Profil</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <span class="navbar-text me-3">
                        <i class="fas fa-user-circle me-1"></i>
                        Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-text">Selamat Datang!</h1>
                    <p class="welcome-subtitle">Kelola penyewaan kost Anda dengan mudah dan ramah lingkungan</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-leaf" style="font-size: 5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <h3 class="section-title">Statistik Sewa</h3>
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stats-number"><?php echo $available_rooms; ?></div>
                    <div class="stats-label">Kamar Tersedia</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo $active_sewa; ?></div>
                    <div class="stats-label">Sewa Aktif</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-number"><?php echo $pending_sewa; ?></div>
                    <div class="stats-label">Sewa Pending</div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_sewa; ?></div>
                    <div class="stats-label">Total Sewa</div>
                </div>
            </div>
        </div>

        <h3 class="section-title">Menu Utama</h3>
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="menu-card">
                    <div class="menu-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="menu-title">Cari Kamar</div>
                    <div class="menu-description">Temukan kamar kost yang sesuai dengan kebutuhan Anda</div>
                    <a href="kamar_list.php" class="btn-eco">Lihat Kamar</a>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="menu-card">
                    <div class="menu-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="menu-title">Status Sewa</div>
                    <div class="menu-description">Pantau status penyewaan dan riwayat transaksi Anda</div>
                    <a href="sewa_status.php" class="btn-eco">Lihat Status</a>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="menu-card">
                    <div class="menu-icon">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <div class="menu-title">Profil Saya</div>
                    <div class="menu-description">Kelola informasi pribadi dan preferensi akun Anda</div>
                    <a href="profile.php" class="btn-eco">Edit Profil</a>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="menu-card">
                    <div class="menu-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="menu-title">Ajukan Sewa</div>
                    <div class="menu-description">Buat pengajuan sewa baru untuk kamar pilihan Anda</div>
                    <a href="sewa_ajuan.php" class="btn-eco">Ajukan Sewa</a>
                </div>
            </div>
        </div>

        <?php if (!empty($recent_rentals)): ?>
        <h3 class="section-title">Aktivitas Terbaru</h3>
        <div class="recent-rentals mb-5">
            <?php foreach ($recent_rentals as $rental): ?>
            <div class="rental-item">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="<?php echo $rental['foto'] ? '../uploads/kamar/' . $rental['foto'] : '../assets/default.jpg'; ?>" 
                             class="img-fluid rounded" alt="Kamar" style="height: 60px; object-fit: cover;">
                    </div>
                    <div class="col-md-4">
                        <h6 class="mb-1"><?php echo htmlspecialchars($rental['nama_kamar']); ?></h6>
                        <small class="text-muted">
                            <?php echo date('d/m/Y', strtotime($rental['tanggal_mulai'])); ?> - 
                            <?php echo date('d/m/Y', strtotime($rental['tanggal_selesai'])); ?>
                        </small>
                    </div>
                    <div class="col-md-3">
                        <strong>Rp <?php echo number_format($rental['total_harga'], 0, ',', '.'); ?></strong>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="status-badge status-<?php echo $rental['status']; ?>">
                            <?php echo ucfirst($rental['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="text-center mt-3">
                <a href="sewa_status.php" class="btn-eco">Lihat Semua Aktivitas</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="row mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e8, #f1f8e9); border-radius: 20px;">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-lightbulb text-warning" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="text-success">Tips Ramah Lingkungan</h5>
                        <p class="text-muted">Hemat energi dengan mematikan lampu dan AC saat tidak digunakan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd, #f1f8e9); border-radius: 20px;">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-headset text-primary" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="text-success">Butuh Bantuan?</h5>
                        <p class="text-muted">Tim customer service kami siap membantu Anda 24/7</p>
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
