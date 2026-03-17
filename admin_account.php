<?php
session_start();
require './includes/db.php';

// Xử lý Xóa tài khoản
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM khachhang WHERE id_kh = ?");
    $stmt->execute([$id]);
    header("Location: admin_accounts.php?msg=deleted");
    exit();
}

// Lấy danh sách tài khoản
$stmt = $pdo->query("SELECT * FROM khachhang ORDER BY id_kh DESC");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý khách hàng - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f4f7f6; padding: 20px; }
        .admin-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-top: 20px;}

        h2 { 
            border-bottom: 2px solid #8B5A2B; 
            padding-bottom: 10px; 
            font-family: 'Playfair Display', serif;
            color: var(--green-dark); 
        }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #8B5A2B; color: white; }
        tr:hover { background-color: #f9f9f9; }
        .avatar-sm { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .btn { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 13px; color: white; }
        .btn-delete { background: #d32f2f; }
        .btn-edit { background: #1976d2; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="admin-container">
    <h2><i class="fa-solid fa-users-gear"></i> QUẢN LÝ TÀI KHOẢN KHÁCH HÀNG</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Điểm</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $acc): ?>
            <tr>
                <td><?= $acc['id_kh'] ?></td>
                <td>
                    <img src="<?= !empty($acc['anh_dai_dien']) ? $acc['anh_dai_dien'] : '/rice4u/asset/images/avatar.jpg' ?>" class="avatar-sm">
                </td>
                <td><strong><?= htmlspecialchars($acc['ho_ten']) ?></strong></td>
                <td><?= htmlspecialchars($acc['email']) ?></td>
                <td><?= htmlspecialchars($acc['so_dien_thoai']) ?></td>
                <td><?= number_format($acc['diem_tich_luy']) ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $acc['id_kh'] ?>" class="btn btn-edit"><i class="fa-solid fa-user-pen"></i></a>
                    <a href="admin_accounts.php?delete_id=<?= $acc['id_kh'] ?>" 
                       class="btn btn-delete" 
                       onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">
                       <i class="fa-solid fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php';  ?>

</body>
</html>