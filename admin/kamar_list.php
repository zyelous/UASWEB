<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db.php';
checkAdminAuth();

$stmt = $pdo->query("SELECT * FROM kamar ORDER BY created_at DESC");
$kamar_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kamar - Admin</title>
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
            <h2>Daftar Kamar</h2>
            <a href="kamar_add.php" class="btn btn-primary">Tambah Kamar</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Kamar</th>
                                <th>Harga/Bulan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kamar_list as $kamar): ?>
                            <tr>
                                <td><?php echo $kamar['id']; ?></td>
                                <td><?php echo htmlspecialchars($kamar['nama_kamar']); ?></td>
                                <td>Rp <?php echo number_format($kamar['harga_per_bulan'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $kamar['status'] == 'tersedia' ? 'success' : ($kamar['status'] == 'disewa' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($kamar['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="kamar_edit.php?id=<?php echo $kamar['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="kamar_delete.php?id=<?php echo $kamar['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
