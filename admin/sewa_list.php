<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db.php';
checkAdminAuth();

$stmt = $pdo->query("
    SELECT s.*, u.full_name, u.username, k.nama_kamar 
    FROM sewa s 
    JOIN users u ON s.user_id = u.id 
    JOIN kamar k ON s.kamar_id = k.id 
    ORDER BY s.created_at DESC
");
$sewa_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Sewa - Admin</title>
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
            <h2>Daftar Penyewaan</h2>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Kamar</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sewa_list as $sewa): ?>
                            <tr>
                                <td><?php echo $sewa['id']; ?></td>
                                <td><?php echo htmlspecialchars($sewa['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($sewa['nama_kamar']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($sewa['tanggal_mulai'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($sewa['tanggal_selesai'])); ?></td>
                                <td>Rp <?php echo number_format($sewa['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $sewa['status'] == 'pending' ? 'warning' : 
                                            ($sewa['status'] == 'approved' ? 'success' : 
                                            ($sewa['status'] == 'active' ? 'primary' : 
                                            ($sewa['status'] == 'rejected' ? 'danger' : 'secondary'))); 
                                    ?>">
                                        <?php echo ucfirst($sewa['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($sewa['status'] == 'pending'): ?>
                                        <a href="sewa_action.php?id=<?php echo $sewa['id']; ?>&action=approve" 
                                           class="btn btn-sm btn-success" onclick="return confirm('Setujui penyewaan ini?')">Setujui</a>
                                        <a href="sewa_action.php?id=<?php echo $sewa['id']; ?>&action=reject" 
                                           class="btn btn-sm btn-danger" onclick="return confirm('Tolak penyewaan ini?')">Tolak</a>
                                    <?php elseif ($sewa['status'] == 'approved'): ?>
                                        <a href="sewa_action.php?id=<?php echo $sewa['id']; ?>&action=activate" 
                                           class="btn btn-sm btn-primary" onclick="return confirm('Aktifkan penyewaan ini?')">Aktifkan</a>
                                    <?php elseif ($sewa['status'] == 'active'): ?>
                                        <a href="sewa_action.php?id=<?php echo $sewa['id']; ?>&action=complete" 
                                           class="btn btn-sm btn-secondary" onclick="return confirm('Selesaikan penyewaan ini?')">Selesaikan</a>
                                    <?php endif; ?>
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
