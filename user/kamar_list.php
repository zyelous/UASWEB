<?php
require_once '../includes/auth_user.php';
require_once '../includes/db.php';
checkUserAuth();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$where_conditions = ["status = 'tersedia'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nama_kamar LIKE ? OR deskripsi LIKE ? OR fasilitas LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($min_price)) {
    $where_conditions[] = "harga_per_bulan >= ?";
    $params[] = $min_price;
}

if (!empty($max_price)) {
    $where_conditions[] = "harga_per_bulan <= ?";
    $params[] = $max_price;
}

$where_clause = implode(' AND ', $where_conditions);

$order_by = "created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "harga_per_bulan ASC";
        break;
    case 'price_high':
        $order_by = "harga_per_bulan DESC";
        break;
    case 'name':
        $order_by = "nama_kamar ASC";
        break;
    case 'newest':
    default:
        $order_by = "created_at DESC";
        break;
}

$sql = "SELECT * FROM kamar WHERE $where_clause ORDER BY $order_by";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$kamar_list = $stmt->fetchAll();

$price_range = $pdo->query("SELECT MIN(harga_per_bulan) as min_price, MAX(harga_per_bulan) as max_price FROM kamar WHERE status = 'tersedia'")->fetch();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kamar - EcoKost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/user/kamar_list.css>

    
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
                        <a class="nav-link active" href="#"><i class="fas fa-bed me-1"></i>Kamar</a>
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
                    <h1 class="mb-2">Temukan Kamar Impian Anda</h1>
                    <p class="mb-0">Pilih dari berbagai kamar kost ramah lingkungan dengan fasilitas terbaik</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-search" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="search-card">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1"></i>Cari Kamar
                    </label>
                    <input type="text" class="form-control" name="search" 
                           placeholder="Nama kamar, deskripsi, atau fasilitas..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-money-bill-wave me-1"></i>Harga Min
                    </label>
                    <input type="number" class="form-control" name="min_price" 
                           placeholder="Rp 0" value="<?php echo htmlspecialchars($min_price); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Harga Max</label>
                    <input type="number" class="form-control" name="max_price" 
                           placeholder="Rp 999999999" value="<?php echo htmlspecialchars($max_price); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort me-1"></i>Urutkan
                    </label>
                    <select class="form-select" name="sort">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn-eco w-100">
                        <i class="fas fa-search me-1"></i>Cari
                    </button>
                </div>
            </form>
            
            <?php if (!empty($search) || !empty($min_price) || !empty($max_price)): ?>
            <div class="mt-3">
                <a href="kamar_list.php" class="btn-outline-eco">
                    <i class="fas fa-times me-1"></i>Reset Filter
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="results-count">
            <i class="fas fa-info-circle me-1"></i>
            Ditemukan <?php echo count($kamar_list); ?> kamar tersedia
            <?php if (!empty($search)): ?>
                untuk pencarian "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </div>

        <?php if (empty($kamar_list)): ?>
            <div class="no-results">
                <i class="fas fa-search" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h4>Tidak ada kamar ditemukan</h4>
                <p>Coba ubah kriteria pencarian atau filter Anda</p>
                <a href="kamar_list.php" class="btn-eco">Lihat Semua Kamar</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($kamar_list as $kamar): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="room-card">
                        <div class="position-relative">
                            <img src="<?php echo $kamar['foto'] ? '../uploads/kamar/' . $kamar['foto'] : '../assets/default.jpg'; ?>" 
                                 class="card-img-top room-image w-100" 
                                 alt="<?php echo htmlspecialchars($kamar['nama_kamar']); ?>">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>Tersedia
                                </span>
                            </div>
                        </div>
                        
                        <div class="card-body p-4">
                            <h5 class="room-title"><?php echo htmlspecialchars($kamar['nama_kamar']); ?></h5>
                            
                            <p class="room-description">
                                <?php echo htmlspecialchars(substr($kamar['deskripsi'], 0, 120)); ?>
                                <?php if (strlen($kamar['deskripsi']) > 120): ?>...<?php endif; ?>
                            </p>
                            
                            <?php if (!empty($kamar['fasilitas'])): ?>
                                <div class="mb-3">
                                    <?php 
                                    $fasilitas = explode(',', $kamar['fasilitas']);
                                    foreach (array_slice($fasilitas, 0, 3) as $facility): 
                                    ?>
                                        <span class="facility-tag"><?php echo trim(htmlspecialchars($facility)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($fasilitas) > 3): ?>
                                        <span class="facility-tag">+<?php echo count($fasilitas) - 3; ?> lainnya</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="price-tag mb-3">
                                <i class="fas fa-tag me-1"></i>
                                Rp <?php echo number_format($kamar['harga_per_bulan'], 0, ',', '.'); ?>/bulan
                            </div>
                            
                            <div class="d-grid">
                                <a href="kamar_detail.php?id=<?php echo $kamar['id']; ?>" class="btn-eco">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="row mt-5 mb-5">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e8, #f1f8e9); border-radius: 20px;">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-question-circle text-info" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="text-success">Butuh Bantuan Memilih?</h5>
                        <p class="text-muted">Tim kami siap membantu Anda menemukan kamar yang tepat</p>
                        <a href="#" class="btn-outline-eco">Hubungi Kami</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd, #f1f8e9); border-radius: 20px;">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-heart text-danger" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h5 class="text-success">Sudah Menemukan Favorit?</h5>
                        <p class="text-muted">Segera ajukan penyewaan sebelum kamar terisi</p>
                        <a href="sewa_ajuan.php" class="btn-eco">Ajukan Sewa</a>
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
