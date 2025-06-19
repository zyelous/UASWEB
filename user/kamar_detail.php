<?php
require_once '../includes/auth_user.php';
require_once '../includes/db.php';
checkUserAuth();

if (isset($_GET['id'])) {
    $kamar_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM kamar WHERE id = ? AND status = 'tersedia'");
    $stmt->execute([$kamar_id]);
    $kamar = $stmt->fetch();
    
    if (!$kamar) {
        header('Location: kamar_list.php');
        exit();
    }
} else {
    header('Location: kamar_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kamar - <?php echo htmlspecialchars($kamar['nama_kamar']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Penyewaan Kost</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Kamar</h2>
            <a href="kamar_list.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="<?php echo $kamar['foto'] ? '../uploads/kamar/' . $kamar['foto'] : '../assets/default.jpg'; ?>" 
                         class="card-img-top" alt="<?php echo htmlspecialchars($kamar['nama_kamar']); ?>" style="height: 400px; object-fit: cover;">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4><?php echo htmlspecialchars($kamar['nama_kamar']); ?></h4>
                    </div>
                    <div class="card-body">
                        <h5 class="text-primary">Rp <?php echo number_format($kamar['harga_per_bulan'], 0, ',', '.'); ?>/bulan</h5>
                        
                        <hr>
                        
                        <h6>Deskripsi:</h6>
                        <p><?php echo nl2br(htmlspecialchars($kamar['deskripsi'])); ?></p>
                        
                        <h6>Fasilitas:</h6>
                        <p><?php echo nl2br(htmlspecialchars($kamar['fasilitas'])); ?></p>
                        
                        <div class="mt-4">
                            <span class="badge bg-success fs-6">Tersedia</span>
                        </div>
                        
                        <div class="mt-4">
                            <a href="sewa_ajuan.php?kamar_id=<?php echo $kamar['id']; ?>" class="btn btn-primary btn-lg w-100">
                                Ajukan Penyewaan
                            </a>
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
