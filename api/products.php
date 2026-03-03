<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../includes/db.php';

$loai  = $_GET['loai']  ?? null;
$limit = $_GET['limit'] ?? 8;
$noi_bat = $_GET['noi_bat'] ?? null;

$sql = "SELECT * FROM v_sanpham_day_du WHERE 1=1";
$params = [];

if ($loai) {
    $sql .= " AND id_loai = ?";
    $params[] = $loai;
}
if ($noi_bat) {
    $sql .= " AND noi_bat = 1";
}

$sql .= " ORDER BY luot_ban DESC LIMIT ?";
$params[] = (int)$limit;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products, JSON_UNESCAPED_UNICODE);
?>