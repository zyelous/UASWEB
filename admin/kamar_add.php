<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db.php';
checkAdminAuth();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kamar = $_POST['nama_kamar'];
    $deskripsi = $_POST['deskripsi'];
    $harga_per_bulan = $_POST['harga_per_bulan'];
    $fasilitas = $_POST['fasilitas'];
    $status = $_POST['status'];
    
    // Handle file upload
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload_dir = '../uploads/kamar/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $foto;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
            // File uploaded successfully
        } else {
            $foto = '';
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO kamar (nama_kamar, deskripsi, harga_per_bulan, fasilitas, foto, status) VALUES (?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([$nama_kamar, $deskripsi, $harga_per_bulan, $fasilitas, $foto, $status])) {
        $success = 'Kamar berhasil ditambahkan!';
    } else {
        $error = 'Terjadi kesalahan saat menambahkan kamar!';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kamar - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tambah Kamar</h2>
            <a href="kamar_list.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="card">
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nama_kamar" class="form-label">Nama Kamar</label>
                        <input type="text" class="form-control" id="nama_kamar" name="nama_kamar" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="harga_per_bulan" class="form-label">Harga per Bulan</label>
                        <input type="number" class="form-control" id="harga_per_bulan" name="harga_per_bulan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fasilitas" class="form-label">Fasilitas</label>
                        <textarea class="form-control" id="fasilitas" name="fasilitas" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="foto" class="form-label">Foto Kamar</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="disewa">Disewa</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Tambah Kamar</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
