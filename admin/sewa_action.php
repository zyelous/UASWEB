<?php
require_once '../includes/auth_admin.php';
require_once '../includes/db.php';
checkAdminAuth();

if (isset($_GET['id']) && isset($_GET['action'])) {
    $sewa_id = $_GET['id'];
    $action = $_GET['action'];
    
    $new_status = '';
    switch ($action) {
        case 'approve':
            $new_status = 'approved';
            break;
        case 'reject':
            $new_status = 'rejected';
            break;
        case 'activate':
            $new_status = 'active';
            // Update kamar status to 'disewa'
            $stmt = $pdo->prepare("UPDATE kamar SET status = 'disewa' WHERE id = (SELECT kamar_id FROM sewa WHERE id = ?)");
            $stmt->execute([$sewa_id]);
            break;
        case 'complete':
            $new_status = 'completed';
            // Update kamar status back to 'tersedia'
            $stmt = $pdo->prepare("UPDATE kamar SET status = 'tersedia' WHERE id = (SELECT kamar_id FROM sewa WHERE id = ?)");
            $stmt->execute([$sewa_id]);
            break;
    }
    
    if ($new_status) {
        $stmt = $pdo->prepare("UPDATE sewa SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $sewa_id]);
    }
}

header('Location: sewa_list.php');
exit();
?>
