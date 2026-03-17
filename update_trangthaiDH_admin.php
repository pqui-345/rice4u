<?php
session_start();
require_once("includes/db.php");

/* kiểm tra admin */
if (!isset($_SESSION['ma_tk']) || $_SESSION['vai_tro'] !== 'admin') {
    header("Location: dangnhap.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id_dh'] ?? null;
    $status = $_POST['trang_thai_dh'] ?? null;

    if ($id && $status) {

        $sql = "UPDATE donhang
                SET trang_thai_dh = ?
                WHERE id_dh = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status, $id]);
    }
}

/* quay lại đúng trang quản lý */
header("Location: quanlydonhang.php");
exit();
?>