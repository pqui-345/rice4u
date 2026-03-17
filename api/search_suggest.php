<?php
/**
 * API gợi ý tìm kiếm - rice4u
 * GET /rice4u/api/search_suggest.php?q=gao
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=rice4u;charset=utf8mb4",
        "root", "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare("
        SELECT
            sp.id_sp,
            sp.ten_sp,
            sp.gia_ban,
            sp.xuat_xu,
            IFNULL(ha.duong_dan, '') AS hinh_chinh
        FROM sanpham sp
        LEFT JOIN hinhanh_sp ha ON sp.id_sp = ha.id_sp AND ha.la_anh_chinh = 1
        WHERE sp.trang_thai = 1
          AND (sp.ten_sp LIKE :q OR sp.xuat_xu LIKE :q2 OR sp.mo_ta_ngan LIKE :q3)
        ORDER BY
            CASE WHEN sp.ten_sp LIKE :q4 THEN 0 ELSE 1 END,
            sp.luot_ban DESC
        LIMIT 8
    ");

    $like = '%' . $q . '%';
    $stmt->execute([':q' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([]);
}