<?php
require_once '../includes/auth_user.php';
require_once '../includes/db.php';
checkUserAuth();

$error = '';
$success = '';
$kamar = null;

if (isset($_GET['kamar_id'])) {
    $kamar_id = $_GET['kamar_id'];
    $stmt = $pdo->prepare("SELECT * FROM kamar WHERE id = ? AND status = 'tersedia'");
    $stmt->execute([$kamar_id]);
    $kamar = $stmt->fetch();
}

$stmt = $pdo->query("SELECT * FROM kamar WHERE status = 'tersedia' ORDER BY nama_kamar");
$available_rooms = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kamar_id = $_POST['kamar_id'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $catatan = $_POST['catatan'];
    
    if (strtotime($tanggal_mulai) >= strtotime($tanggal_selesai)) {
        $error = 'Tanggal selesai harus setelah tanggal mulai!';
    } else {
        $stmt = $pdo->prepare("SELECT harga_per_bulan FROM kamar WHERE id = ?");
        $stmt->execute([$kamar_id]);
        $kamar_data = $stmt->fetch();
        
        if ($kamar_data) {
        
            $start = new DateTime($tanggal_mulai);
            $end = new DateTime($tanggal_selesai);
            $interval = $start->diff($end);
            $months = $interval->m + ($interval->y * 12);
            if ($months == 0) $months = 1; // Minimum 1 month
            
            $total_harga = $kamar_data['harga_per_bulan'] * $months;
            
            $stmt = $pdo->prepare("SELECT * FROM sewa WHERE user_id = ? AND kamar_id = ? AND status IN ('pending', 'approved', 'active')");
            $stmt->execute([$_SESSION['user_id'], $kamar_id]);
            
            if ($stmt->fetch()) {
                $error = 'Anda sudah memiliki pengajuan sewa untuk kamar ini!';
            } else {
                // Insert rental request
                $stmt = $pdo->prepare("INSERT INTO sewa (user_id, kamar_id, tanggal_mulai, tanggal_selesai, total_harga, catatan) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$_SESSION['user_id'], $kamar_id, $tanggal_mulai, $tanggal_selesai, $total_harga, $catatan])) {
                    $success = 'Pengajuan sewa berhasil dikirim! Menunggu persetujuan admin.';
                } else {
                    $error = 'Terjadi kesalahan saat mengirim pengajuan!';
                }
            }
        } else {
            $error = 'Kamar tidak ditemukan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Penyewaan - User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/user/sewa_ajuan.css">
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

     <div class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-text">Ajukan Penyewaan Kost Anda</h1>
                    <p class="welcome-subtitle">Kelola penyewaan kost Anda dengan mudah dan ramah lingkungan</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-leaf" style="font-size: 5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ajukan Penyewaan</h2>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="kamar_id" class="form-label">Pilih Kamar</label>
                                <select class="form-control" id="kamar_id" name="kamar_id" required onchange="updatePrice()">
                                    <option value="">-- Pilih Kamar --</option>
                                    <?php foreach ($available_rooms as $room): ?>
                                        <option value="<?php echo $room['id']; ?>" 
                                                data-price="<?php echo $room['harga_per_bulan']; ?>"
                                                <?php echo ($kamar && $kamar['id'] == $room['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($room['nama_kamar']); ?> - 
                                            Rp <?php echo number_format($room['harga_per_bulan'], 0, ',', '.'); ?>/bulan
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" 
                                               min="<?php echo date('Y-m-d'); ?>" required onchange="calculateTotal()">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" 
                                               min="<?php echo date('Y-m-d'); ?>" required onchange="calculateTotal()">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Estimasi Total Harga</label>
                                <div class="alert alert-info" id="total_price">
                                    Pilih kamar dan tanggal untuk melihat estimasi harga
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                          placeholder="Tambahkan catatan atau permintaan khusus..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Kirim Pengajuan</button>
                        </form>
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
    <script>
        function calculateTotal() {
            const kamarSelect = document.getElementById('kamar_id');
            const startDate = document.getElementById('tanggal_mulai').value;
            const endDate = document.getElementById('tanggal_selesai').value;
            const totalPriceDiv = document.getElementById('total_price');
            
            if (kamarSelect.value && startDate && endDate) {
                const price = parseInt(kamarSelect.options[kamarSelect.selectedIndex].dataset.price);
                const start = new Date(startDate);
                const end = new Date(endDate);
                
                if (end > start) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const months = Math.max(1, Math.ceil(diffDays / 30));
                    const total = price * months;
                    
                    totalPriceDiv.innerHTML = `
                        <strong>Estimasi: ${months} bulan × Rp ${price.toLocaleString('id-ID')} = Rp ${total.toLocaleString('id-ID')}</strong>
                    `;
                } else {
                    totalPriceDiv.innerHTML = 'Tanggal selesai harus setelah tanggal mulai';
                }
            }
        }
        
        function updatePrice() {
            calculateTotal();
        }
    </script>
</body>
</html>
