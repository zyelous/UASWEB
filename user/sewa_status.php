<?php
require_once '../includes/auth_user.php';
require_once '../includes/db.php';
checkUserAuth();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$where_conditions = ["s.user_id = ?"];
$params = [$_SESSION['user_id']];

if ($filter !== 'all') {
    $where_conditions[] = "s.status = ?";
    $params[] = $filter;
}

$where_clause = implode(' AND ', $where_conditions);

$stmt = $pdo->prepare("
    SELECT s.*, k.nama_kamar, k.foto, k.harga_per_bulan
    FROM sewa s 
    JOIN kamar k ON s.kamar_id = k.id 
    WHERE $where_clause
    ORDER BY 
        CASE 
            WHEN s.status = 'active' THEN 1
            WHEN s.status = 'approved' THEN 2
            WHEN s.status = 'pending' THEN 3
            WHEN s.status = 'rejected' THEN 4
            WHEN s.status = 'completed' THEN 5
        END,
        s.created_at DESC
");
$stmt->execute($params);
$sewa_list = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM sewa
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$counts = $stmt->fetch();

function getStatusDetails($status) {
    $details = [
        'pending' => [
            'icon' => 'clock',
            'color' => 'warning',
            'text' => 'Menunggu persetujuan admin',
            'progress' => 25
        ],
        'approved' => [
            'icon' => 'check-circle',
            'color' => 'success',
            'text' => 'Disetujui! Menunggu aktivasi',
            'progress' => 50
        ],
        'active' => [
            'icon' => 'home',
            'color' => 'primary',
            'text' => 'Sewa aktif - Anda dapat menempati kamar',
            'progress' => 75
        ],
        'rejected' => [
            'icon' => 'x-circle',
            'color' => 'danger',
            'text' => 'Pengajuan ditolak',
            'progress' => 100
        ],
        'completed' => [
            'icon' => 'check-square',
            'color' => 'secondary',
            'text' => 'Sewa selesai',
            'progress' => 100
        ]
    ];
    
    return $details[$status] ?? $details['pending'];
}

function calculateDuration($start, $end) {
    $start_date = new DateTime($start);
    $end_date = new DateTime($end);
    $interval = $start_date->diff($end_date);
    $months = $interval->y * 12 + $interval->m;
    return $months > 0 ? $months : 1; // Minimum 1 month
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Penyewaan - KostQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/user/sewa_status.css">
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
                        <a class="nav-link active" href="#"><i class="fas fa-file-contract me-1"></i>Sewa Saya</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="profile.php"><i class="fas fa-user-edit me-1"></i>Profil</a>
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
                    <h1 class="mb-2">Status Penyewaan</h1>
                    <p class="mb-0">Pantau status dan riwayat penyewaan kamar kost Anda</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-clipboard-list" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
   
        <div class="filter-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Filter Status</h5>
                <div class="text-muted">Total: <?php echo $counts['total']; ?> penyewaan</div>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="?filter=all" class="btn-filter <?php echo $filter == 'all' ? 'active' : ''; ?>">
                    Semua <span class="status-count"><?php echo $counts['total']; ?></span>
                </a>
                <a href="?filter=active" class="btn-filter <?php echo $filter == 'active' ? 'active' : ''; ?>">
                    Aktif <span class="status-count"><?php echo $counts['active']; ?></span>
                </a>
                <a href="?filter=pending" class="btn-filter <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                    Pending <span class="status-count"><?php echo $counts['pending']; ?></span>
                </a>
                <a href="?filter=approved" class="btn-filter <?php echo $filter == 'approved' ? 'active' : ''; ?>">
                    Disetujui <span class="status-count"><?php echo $counts['approved']; ?></span>
                </a>
                <a href="?filter=completed" class="btn-filter <?php echo $filter == 'completed' ? 'active' : ''; ?>">
                    Selesai <span class="status-count"><?php echo $counts['completed']; ?></span>
                </a>
                <a href="?filter=rejected" class="btn-filter <?php echo $filter == 'rejected' ? 'active' : ''; ?>">
                    Ditolak <span class="status-count"><?php echo $counts['rejected']; ?></span>
                </a>
            </div>
        </div>

        <?php if (empty($sewa_list)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list empty-icon"></i>
                <h4>Belum ada pengajuan sewa</h4>
                <p class="text-muted mb-4">Anda belum mengajukan penyewaan kamar kost</p>
                <a href="kamar_list.php" class="btn-eco">
                    <i class="fas fa-search me-1"></i>Lihat Kamar Tersedia
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($sewa_list as $sewa): 
                    $status_details = getStatusDetails($sewa['status']);
                    $duration = calculateDuration($sewa['tanggal_mulai'], $sewa['tanggal_selesai']);
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="rental-card">
                        <div class="rental-header">
                            <h5 class="mb-0 text-success"><?php echo htmlspecialchars($sewa['nama_kamar']); ?></h5>
                            <span class="status-badge status-<?php echo $sewa['status']; ?>">
                                <i class="fas fa-<?php echo $status_details['icon']; ?>"></i>
                                <?php echo ucfirst($sewa['status']); ?>
                            </span>
                        </div>
                        
                        <div class="rental-body">
                            <div class="row">
                                <div class="col-md-5 mb-3 mb-md-0">
                                    <div class="position-relative">
                                        <img src="<?php echo $sewa['foto'] ? '../uploads/kamar/' . $sewa['foto'] : '../assets/default.jpg'; ?>" 
                                             class="room-image" alt="Kamar">
                                        <div class="price-tag position-absolute bottom-0 end-0 m-2">
                                            <i class="fas fa-tag me-1"></i>
                                            <?php echo number_format($sewa['harga_per_bulan'], 0, ',', '.'); ?>/bln
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-7">
                                    <div class="rental-info">
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-calendar-alt"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Periode Sewa</div>
                                                <div><?php echo date('d M Y', strtotime($sewa['tanggal_mulai'])); ?> - 
                                                <?php echo date('d M Y', strtotime($sewa['tanggal_selesai'])); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-clock"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Durasi</div>
                                                <div><?php echo $duration; ?> bulan</div>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Total Biaya</div>
                                                <div>Rp <?php echo number_format($sewa['total_harga'], 0, ',', '.'); ?></div>
                                            </div>
                                        </div>
                                        
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-file-alt"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">ID Transaksi</div>
                                                <div>#<?php echo str_pad($sewa['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($sewa['catatan']): ?>
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="fw-bold mb-1">
                                        <i class="fas fa-comment-alt me-1 text-success"></i>Catatan:
                                    </div>
                                    <div><?php echo htmlspecialchars($sewa['catatan']); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <h6 class="mb-3">Status Pengajuan</h6>
                                <div class="progress mb-3" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $status_details['color']; ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $status_details['progress']; ?>%" 
                                         aria-valuenow="<?php echo $status_details['progress']; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                                
                                <ul class="progress-timeline">
                                    <li class="timeline-item">
                                        <div class="timeline-icon active">
                                            <i class="fas fa-paper-plane"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="fw-bold">Pengajuan</div>
                                            <div class="text-muted small">
                                                <?php echo date('d M Y, H:i', strtotime($sewa['created_at'])); ?>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li class="timeline-item">
                                        <div class="timeline-icon <?php echo in_array($sewa['status'], ['approved', 'active', 'completed']) ? 'active' : ''; ?>">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="fw-bold">Persetujuan</div>
                                            <div class="text-muted small">
                                                <?php echo in_array($sewa['status'], ['approved', 'active', 'completed']) ? 'Disetujui oleh admin' : 'Menunggu persetujuan'; ?>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li class="timeline-item">
                                        <div class="timeline-icon <?php echo in_array($sewa['status'], ['active', 'completed']) ? 'active' : ''; ?>">
                                            <i class="fas fa-key"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="fw-bold">Aktivasi</div>
                                            <div class="text-muted small">
                                                <?php echo in_array($sewa['status'], ['active', 'completed']) ? 'Sewa aktif' : 'Menunggu aktivasi'; ?>
                                            </div>
                                        </div>
                                    </li>
                                    
                                    <li class="timeline-item">
                                        <div class="timeline-icon <?php echo $sewa['status'] == 'completed' ? 'active' : ''; ?>">
                                            <i class="fas fa-flag-checkered"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="fw-bold">Selesai</div>
                                            <div class="text-muted small">
                                                <?php echo $sewa['status'] == 'completed' ? 'Sewa telah selesai' : 'Belum selesai'; ?>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="rental-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php echo $status_details['text']; ?>
                                </div>
                                
                                <?php if ($sewa['status'] == 'active'): ?>
                                <a href="#" class="btn-outline-eco btn-sm">
                                    <i class="fas fa-headset me-1"></i>Bantuan
                                </a>
                                <?php elseif ($sewa['status'] == 'rejected'): ?>
                                <a href="kamar_list.php" class="btn-outline-eco btn-sm">
                                    <i class="fas fa-search me-1"></i>Cari Kamar Lain
                                </a>
                                <?php elseif ($sewa['status'] == 'completed'): ?>
                                <a href="sewa_ajuan.php?kamar_id=<?php echo $sewa['kamar_id']; ?>" class="btn-outline-eco btn-sm">
                                    <i class="fas fa-redo me-1"></i>Sewa Lagi
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="row mt-4 mb-5">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e8, #f1f8e9); border-radius: 20px;">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-question-circle text-info" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h5 class="text-success">Butuh Bantuan?</h5>
                            <p class="text-muted">Tim kami siap membantu dengan pertanyaan seputar penyewaan</p>
                            <a href="#" class="btn-outline-eco">Hubungi Kami</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd, #f1f8e9); border-radius: 20px;">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-home text-success" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <h5 class="text-success">Cari Kamar Baru?</h5>
                            <p class="text-muted">Temukan kamar kost yang sesuai dengan kebutuhan Anda</p>
                            <a href="kamar_list.php" class="btn-eco">Lihat Kamar</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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
                                    