<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db.php';
checkAdminAuth();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total_kamar FROM kamar");
$total_kamar = $stmt->fetch()['total_kamar'];

$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch()['total_users'];

$stmt = $pdo->query("SELECT COUNT(*) as total_sewa FROM sewa");
$total_sewa = $stmt->fetch()['total_sewa'];

$stmt = $pdo->query("SELECT COUNT(*) as pending_sewa FROM sewa WHERE status = 'pending'");
$pending_sewa = $stmt->fetch()['pending_sewa'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Penyewaan Kost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Selamat datang, <?php echo $_SESSION['admin_username']; ?></span>
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Dashboard Admin</h2>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5>Total Kamar</h5>
                        <h2><?php echo $total_kamar; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5>Total Users</h5>
                        <h2><?php echo $total_users; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5>Total Sewa</h5>
                        <h2><?php echo $total_sewa; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5>Pending Sewa</h5>
                        <h2><?php echo $pending_sewa; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Menu Administrasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="kamar_list.php" class="btn btn-primary w-100 mb-2">Kelola Kamar</a>
                            </div>
                            <div class="col-md-4">
                                <a href="sewa_list.php" class="btn btn-success w-100 mb-2">Kelola Sewa</a>
                            </div>
                            <div class="col-md-4">
                                <a href="user_list.php" class="btn btn-info w-100 mb-2">Kelola User</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
