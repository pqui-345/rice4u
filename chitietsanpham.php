<?php
// ============================================================
// TRANG CHI TIẾT SẢN PHẨM - rice4u
// ============================================================
require_once __DIR__ . '/includes/db.php';

// ── Lấy ID sản phẩm ──
$id_sp = trim($_GET['id'] ?? '');
if (!$id_sp) {
    header('Location: sanpham.php');
    exit;
}

// ── Lấy thông tin sản phẩm ──
$stmt = $pdo->prepare("
    SELECT sp.*, lg.ten_loai,
           ha.duong_dan AS hinh_chinh
    FROM sanpham sp
    LEFT JOIN loaigao lg ON sp.id_loai = lg.id_loai
    LEFT JOIN hinhanh_sp ha ON sp.id_sp = ha.id_sp AND ha.la_anh_chinh = 1
    WHERE sp.id_sp = :id AND sp.trang_thai = 1
    LIMIT 1
");
$stmt->execute([':id' => $id_sp]);
$sp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sp) {
    header('Location: sanpham.php');
    exit;
}

// ── Lấy tất cả hình ảnh ──
$stmt_imgs = $pdo->prepare("
    SELECT duong_dan, alt_text, la_anh_chinh
    FROM hinhanh_sp
    WHERE id_sp = :id
    ORDER BY la_anh_chinh DESC, id_anh ASC
");
$stmt_imgs->execute([':id' => $id_sp]);
$hinh_list = $stmt_imgs->fetchAll(PDO::FETCH_ASSOC);

// ── Lấy quy cách/trọng lượng ──
$stmt_qc = $pdo->prepare("
    SELECT * FROM gia_quy_cach
    WHERE id_sp = :id AND trang_thai = 1
    ORDER BY trong_luong ASC
");
$stmt_qc->execute([':id' => $id_sp]);
$quy_cach_list = $stmt_qc->fetchAll(PDO::FETCH_ASSOC);

// ── Lấy đánh giá ──
$stmt_dg = $pdo->prepare("
    SELECT dg.*, kh.ho_ten
    FROM danhgia dg
    LEFT JOIN khachhang kh ON dg.id_kh = kh.id_kh
    WHERE dg.id_sp = :id AND dg.trang_thai = 'da_duyet'
    ORDER BY dg.ngay_dg DESC
    LIMIT 6
");
$stmt_dg->execute([':id' => $id_sp]);
$danh_gia = $stmt_dg->fetchAll(PDO::FETCH_ASSOC);

// ── Điểm trung bình đánh giá ──
$stmt_avg = $pdo->prepare("SELECT AVG(so_sao), COUNT(*) FROM danhgia WHERE id_sp = :id AND trang_thai = 'da_duyet'");
$stmt_avg->execute([':id' => $id_sp]);
[$avg_sao, $so_dg] = $stmt_avg->fetch(PDO::FETCH_NUM);
$avg_sao = round((float)$avg_sao, 1);

// ── Sản phẩm liên quan ──
$stmt_lq = $pdo->prepare("
    SELECT sp.id_sp, sp.ten_sp, sp.gia_ban, sp.gia_goc, sp.phan_tram_giam,
           sp.noi_bat, sp.ban_chay, sp.hang_moi,
           ha.duong_dan AS hinh_chinh
    FROM sanpham sp
    LEFT JOIN hinhanh_sp ha ON sp.id_sp = ha.id_sp AND ha.la_anh_chinh = 1
    WHERE sp.id_loai = :loai AND sp.id_sp != :id AND sp.trang_thai = 1
    ORDER BY sp.luot_ban DESC
    LIMIT 4
");
$stmt_lq->execute([':loai' => $sp['id_loai'], ':id' => $id_sp]);
$sp_lienquan = $stmt_lq->fetchAll(PDO::FETCH_ASSOC);

// ── Helpers ──
function formatGia($gia) {
    return number_format($gia, 0, ',', '.') . '₫';
}

function resolveImagePath($path, $default = '/rice4u/asset/images/default.jpg') {
    if (empty($path)) return $default;

    $path = trim((string)$path);
    if (preg_match('#^https?://#i', $path)) return $path;

    $normalized = str_replace('\\', '/', ltrim($path, '/'));
    $candidates = [$normalized];

    if (strpos($normalized, 'asset/') !== 0) {
        $candidates[] = 'asset/' . $normalized;
    }

    // Thử tất cả đuôi file phổ biến
    $dot = strrpos($normalized, '.');
    if ($dot !== false) {
        $base = substr($normalized, 0, $dot);
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $candidate = $base . '.' . $ext;
            $candidates[] = $candidate;
            if (strpos($candidate, 'asset/') !== 0) {
                $candidates[] = 'asset/' . $candidate;
            }
        }
    }

    foreach ($candidates as $candidate) {
        $candidate = ltrim($candidate, '/');
        $candidateFull = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $candidate);
        if (is_file($candidateFull)) {
            return '/rice4u/' . $candidate;
        }
    }

    return $default;
}

function renderStars($avg, $size = 18) {
    $html = '<span class="stars-wrap" style="display:inline-flex;gap:2px;">';
    for ($i = 1; $i <= 5; $i++) {
        if ($avg >= $i) {
            $html .= "<svg width='{$size}' height='{$size}' viewBox='0 0 24 24' fill='#FFAA00'><path d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/></svg>";
        } elseif ($avg >= $i - 0.5) {
            $html .= "<svg width='{$size}' height='{$size}' viewBox='0 0 24 24'><defs><linearGradient id='half'><stop offset='50%' stop-color='#FFAA00'/><stop offset='50%' stop-color='#ddd'/></linearGradient></defs><path d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z' fill='url(#half)'/></svg>";
        } else {
            $html .= "<svg width='{$size}' height='{$size}' viewBox='0 0 24 24' fill='#ddd'><path d='M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z'/></svg>";
        }
    }
    return $html . '</span>';
}

$hinh_main = resolveImagePath($sp['hinh_chinh'] ?? null);
$het_hang  = ($sp['so_luong_ton'] <= 0);
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/rice4u/asset/images/favicon.ico" type="image/x-icon">
<title><?= htmlspecialchars($sp['ten_sp']) ?> – Rice4U</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/rice4u/asset/styles.css">
<style>
/* ══════════════════════════════════════
   TRANG CHI TIẾT SẢN PHẨM
══════════════════════════════════════ */

/* ── BREADCRUMB ── */
.breadcrumb {
  background: var(--gray-light);
  padding: 14px 5%;
  margin-top: 72px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.83rem;
  color: var(--text-mid);
}
.breadcrumb a {
  color: var(--text-mid);
  text-decoration: none;
  transition: color 0.2s;
}
.breadcrumb a:hover { color: var(--green-dark); }
.breadcrumb .sep { color: #bbb; }
.breadcrumb .current { color: var(--green-dark); font-weight: 600; }

/* ── LAYOUT CHÍNH ── */
.detail-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 52px 5%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 64px;
  align-items: start;
}

/* ── GALLERY ── */
.gallery { position: sticky; top: 90px; }

.main-img-wrap {
  position: relative;
  border-radius: 28px;
  overflow: hidden;
  background: var(--gray-light);
  aspect-ratio: 1 / 1;
  margin-bottom: 16px;
  box-shadow: 0 8px 40px rgba(35,114,39,0.12);
}
.main-img-wrap img {
  width: 100%; height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
  cursor: zoom-in;
}
.main-img-wrap img:hover { transform: scale(1.04); }

.badge-overlay {
  position: absolute; top: 18px; left: 18px;
  display: flex; flex-direction: column; gap: 8px;
  z-index: 3;
}
.badge-overlay .badge {
  display: inline-block;
  font-size: 0.72rem; font-weight: 700;
  padding: 5px 14px;
  border-radius: 100px;
  letter-spacing: 0.04em;
}
.badge-green  { background: var(--green-dark);   color: white; }
.badge-amber  { background: var(--amber);         color: white; }
.badge-red    { background: #ff4444;              color: white; }
.badge-gray   { background: #999;                 color: white; }

.thumb-list {
  display: flex; gap: 12px; flex-wrap: wrap;
}
.thumb {
  width: 78px; height: 78px;
  border-radius: 14px;
  overflow: hidden;
  border: 2.5px solid transparent;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.thumb img { width: 100%; height: 100%; object-fit: cover; }
.thumb:hover, .thumb.active { border-color: var(--green-dark); }

/* ── INFO ── */
.product-info {}

.pi-tag {
  display: inline-block;
  color: var(--green-mid);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-bottom: 10px;
}
.pi-name {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 3vw, 2.5rem);
  color: var(--text-dark);
  line-height: 1.25;
  margin-bottom: 14px;
}
.pi-origin {
  font-size: 0.86rem;
  color: var(--text-mid);
  margin-bottom: 16px;
  display: flex; align-items: center; gap: 6px;
}
.pi-origin svg { color: var(--green-dark); }

.pi-rating {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 20px;
}
.pi-rating .avg-num {
  font-size: 1.1rem; font-weight: 700; color: var(--text-dark);
}
.pi-rating .review-count {
  font-size: 0.82rem; color: var(--text-mid);
}

.pi-price-block {
  background: var(--gray-light);
  border-radius: 20px;
  padding: 22px 24px;
  margin-bottom: 24px;
  display: flex; align-items: flex-end; gap: 14px;
}
.pi-price-main {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: var(--green-dark);
  line-height: 1;
}
.pi-price-orig {
  font-size: 1rem;
  color: #bbb;
  text-decoration: line-through;
  font-weight: 400;
}
.pi-price-save {
  background: #ffecec;
  color: #ff4444;
  font-size: 0.78rem; font-weight: 700;
  padding: 4px 10px; border-radius: 100px;
  margin-left: auto;
}

/* ── QUY CÁCH ── */
.quy-cach-label {
  font-size: 0.85rem; font-weight: 600; color: var(--text-dark);
  margin-bottom: 10px;
}
.quy-cach-list {
  display: flex; gap: 10px; flex-wrap: wrap;
  margin-bottom: 28px;
}
.qc-btn {
  padding: 10px 22px;
  border-radius: 12px;
  border: 2px solid #e0e0e0;
  background: white;
  font-family: 'Be Vietnam Pro', sans-serif;
  font-size: 0.88rem;
  font-weight: 500;
  color: var(--text-mid);
  cursor: pointer;
  transition: all 0.2s;
  display: flex; flex-direction: column; align-items: center; gap: 2px;
}
.qc-btn:hover { border-color: var(--green-dark); color: var(--green-dark); }
.qc-btn.active {
  border-color: var(--green-dark);
  background: var(--green-dark);
  color: white;
}
.qc-btn span { font-size: 0.75rem; opacity: 0.8; }
.qc-btn.active span { opacity: 0.85; }

/* ── SỐ LƯỢNG & THÊM VÀO GIỎ ── */
.qty-row {
  display: flex; align-items: center; gap: 16px;
  margin-bottom: 20px;
}
.qty-label { font-size: 0.85rem; font-weight: 600; color: var(--text-dark); min-width: 70px; }
.qty-control {
  display: flex; align-items: center;
  border: 2px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
}
.qty-btn {
  width: 40px; height: 44px;
  background: var(--gray-light);
  border: none; cursor: pointer;
  font-size: 1.2rem; color: var(--text-mid);
  transition: background 0.2s;
  display: flex; align-items: center; justify-content: center;
}
.qty-btn:hover { background: #e8f5e9; color: var(--green-dark); }
.qty-input {
  width: 56px; height: 44px;
  border: none; outline: none;
  text-align: center;
  font-family: 'Be Vietnam Pro', sans-serif;
  font-size: 1rem; font-weight: 600;
  color: var(--text-dark);
  background: white;
}
.qty-stock {
  font-size: 0.8rem; color: #888;
}
.qty-stock.low { color: #ff4444; font-weight: 600; }

.action-row {
  display: flex; gap: 14px; flex-wrap: wrap;
  margin-bottom: 28px;
}
.btn-cart {
  flex: 1; min-width: 180px;
  background: var(--amber);
  color: white; border: none; cursor: pointer;
  padding: 16px 28px;
  border-radius: 100px;
  font-family: 'Be Vietnam Pro', sans-serif;
  font-size: 0.95rem; font-weight: 700;
  letter-spacing: 0.04em;
  box-shadow: 0 8px 28px rgba(255,170,0,0.3);
  transition: all 0.3s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
}
.btn-cart:hover {
  background: #e89900;
  transform: translateY(-2px);
  box-shadow: 0 12px 36px rgba(255,170,0,0.4);
}
.btn-cart:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; transform: none; }

.btn-buy {
  flex: 1; min-width: 160px;
  background: var(--green-dark);
  color: white; border: none; cursor: pointer;
  padding: 16px 28px;
  border-radius: 100px;
  font-family: 'Be Vietnam Pro', sans-serif;
  font-size: 0.95rem; font-weight: 600;
  letter-spacing: 0.04em;
  box-shadow: 0 8px 28px rgba(35,114,39,0.3);
  transition: all 0.3s;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  text-decoration: none;
}
.btn-buy:hover {
  background: #1a5c1e;
  transform: translateY(-2px);
}
.btn-buy:disabled { background: #ccc; cursor: not-allowed; box-shadow: none; transform: none; }

/* ── BENEFITS ── */
.benefits-row {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 12px; margin-bottom: 28px;
}
.benefit-item {
  display: flex; align-items: center; gap: 10px;
  background: var(--gray-light);
  border-radius: 14px; padding: 13px 15px;
  font-size: 0.83rem; font-weight: 500; color: var(--text-dark);
}
.benefit-item svg { color: var(--green-dark); flex-shrink: 0; }

/* ── TABS ── */
.tabs-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 5% 72px;
}
.tab-nav {
  display: flex; gap: 4px;
  border-bottom: 2px solid #eee;
  margin-bottom: 36px;
  flex-wrap: wrap;
}
.tab-btn {
  padding: 13px 28px;
  border: none; background: none;
  font-family: 'Be Vietnam Pro', sans-serif;
  font-size: 0.9rem; font-weight: 500;
  color: var(--text-mid);
  cursor: pointer;
  border-bottom: 2.5px solid transparent;
  margin-bottom: -2px;
  transition: all 0.2s;
  border-radius: 8px 8px 0 0;
}
.tab-btn:hover { color: var(--green-dark); background: var(--gray-light); }
.tab-btn.active { color: var(--green-dark); border-bottom-color: var(--green-dark); font-weight: 700; }

.tab-pane { display: none; animation: fadeUp 0.4s ease; }
.tab-pane.active { display: block; }

/* ── MÔ TẢ ── */
.desc-content {
  font-size: 0.96rem;
  color: var(--text-mid);
  line-height: 1.85;
  font-weight: 300;
  max-width: 780px;
}
.desc-content h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem;
  color: var(--text-dark);
  margin: 24px 0 10px;
}
.desc-content ul {
  padding-left: 20px;
}
.desc-content ul li { margin-bottom: 6px; }
.desc-content p { margin-bottom: 14px; }

/* ── THÔNG SỐ ── */
.spec-table {
  width: 100%; max-width: 680px;
  border-collapse: collapse;
}
.spec-table tr { border-bottom: 1px solid #f0f0f0; }
.spec-table tr:last-child { border-bottom: none; }
.spec-table td {
  padding: 14px 16px;
  font-size: 0.9rem;
}
.spec-table td:first-child {
  font-weight: 600;
  color: var(--text-dark);
  width: 200px;
  background: var(--gray-light);
  border-radius: 8px 0 0 8px;
}
.spec-table td:last-child {
  color: var(--text-mid);
}

/* ── ĐÁNH GIÁ ── */
.reviews-header {
  display: flex; align-items: center; gap: 28px;
  background: var(--gray-light);
  border-radius: 24px; padding: 28px 32px;
  margin-bottom: 32px;
  flex-wrap: wrap; gap: 24px;
}
.review-summary-num {
  font-family: 'Playfair Display', serif;
  font-size: 3.5rem; font-weight: 700;
  color: var(--green-dark); line-height: 1;
}
.review-summary-label {
  font-size: 0.82rem; color: var(--text-mid);
  margin-top: 4px;
}

.review-bars { flex: 1; min-width: 200px; }
.review-bar-row {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 7px;
  font-size: 0.8rem; color: var(--text-mid);
}
.review-bar-track {
  flex: 1; height: 6px;
  background: #e0e0e0; border-radius: 10px;
  overflow: hidden;
}
.review-bar-fill {
  height: 100%;
  background: var(--amber);
  border-radius: 10px;
  transition: width 0.8s ease;
}

.review-list { display: flex; flex-direction: column; gap: 20px; }
.review-card {
  background: var(--gray-light);
  border-radius: 18px; padding: 22px 24px;
}
.review-card-top {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 10px;
}
.reviewer-avatar {
  width: 42px; height: 42px;
  background: var(--green-mid);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 700; font-size: 1rem;
  flex-shrink: 0;
}
.reviewer-name { font-weight: 600; font-size: 0.92rem; color: var(--text-dark); }
.review-date { font-size: 0.78rem; color: #aaa; }
.review-title { font-weight: 600; font-size: 0.92rem; color: var(--text-dark); margin-bottom: 6px; }
.review-body { font-size: 0.88rem; color: var(--text-mid); font-weight: 300; line-height: 1.65; }

.no-reviews {
  text-align: center; padding: 52px 20px;
  color: #bbb;
}
.no-reviews p:first-child { font-size: 2.5rem; margin-bottom: 12px; }

/* ── SẢN PHẨM LIÊN QUAN ── */
.related-section {
  background: var(--cream);
  border-radius: 48px;
  padding: 72px 5%;
  margin: 0 0 8px;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
  gap: 24px;
  max-width: 1200px;
  margin: 0 auto;
}

/* ── TOAST ── */
.toast {
  position: fixed; bottom: 30px; right: 30px;
  background: var(--green-dark); color: white;
  padding: 15px 24px;
  border-radius: 14px;
  font-size: 0.9rem; font-weight: 500;
  box-shadow: 0 8px 32px rgba(35,114,39,0.35);
  z-index: 999;
  display: flex; align-items: center; gap: 10px;
  transform: translateY(80px); opacity: 0;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast.show { transform: translateY(0); opacity: 1; }

/* ── LIGHTBOX ── */
.lightbox {
  display: none;
  position: fixed; inset: 0; z-index: 200;
  background: rgba(0,0,0,0.85);
  align-items: center; justify-content: center;
  padding: 20px;
}
.lightbox.open { display: flex; }
.lightbox img {
  max-width: 90vw; max-height: 90vh;
  border-radius: 16px;
  object-fit: contain;
}
.lightbox-close {
  position: absolute; top: 20px; right: 24px;
  background: none; border: none;
  color: white; font-size: 2rem; cursor: pointer; line-height: 1;
}

@media (max-width: 900px) {
  .detail-wrapper { grid-template-columns: 1fr; gap: 36px; }
  .gallery { position: static; }
  .benefits-row { grid-template-columns: 1fr; }
  .action-row { flex-direction: column; }
  .btn-cart, .btn-buy { min-width: 100%; }
  .reviews-header { flex-direction: column; align-items: flex-start; }
}
</style>
</head>
<body>

<!-- HEADER -->
<!-- <header>
  <a href="trangchu.php" class="logo">
    <img src="/rice4u/asset/images/logo.png" alt="Rice4U Logo" class="logo-img">
  </a>
  <nav>
    <a href="trangchu.php">Trang Chủ</a>
    <a href="sanpham.php">Sản Phẩm</a>
    <a href="#">Câu Chuyện Gạo Việt</a>
    <a href="#">Về Chúng Tôi</a>
    <a href="#">Liên Hệ</a>
  </nav>
  <div class="header-actions">
    <button class="icon-btn" title="Tìm kiếm">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </button>
    <button class="icon-btn" id="cartIconBtn" title="Giỏ hàng" style="position:relative">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      <span class="cart-badge" id="cartCount">0</span>
    </button>
    <a href="#" class="btn-primary" style="padding:11px 24px;font-size:0.82rem;">Đăng Nhập</a>
  </div>
</header> -->

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <a href="trangchu.php">Trang Chủ</a>
  <span class="sep">›</span>
  <a href="sanpham.php">Sản Phẩm</a>
  <?php if ($sp['ten_loai']): ?>
  <span class="sep">›</span>
  <a href="sanpham.php?loai=<?= $sp['id_loai'] ?>"><?= htmlspecialchars($sp['ten_loai']) ?></a>
  <?php endif; ?>
  <span class="sep">›</span>
  <span class="current"><?= htmlspecialchars($sp['ten_sp']) ?></span>
</div>

<!-- CHI TIẾT SẢN PHẨM -->
<div class="detail-wrapper">

  <!-- ── GALLERY ── -->
  <div class="gallery reveal">
    <div class="main-img-wrap" id="mainImgWrap">
      <div class="badge-overlay">
        <?php if ($sp['ban_chay']): ?><span class="badge badge-green">Bán Chạy</span><?php endif; ?>
        <?php if ($sp['hang_moi']): ?><span class="badge badge-amber">🌱 Mới</span><?php endif; ?>
        <?php if ($sp['noi_bat']): ?><span class="badge badge-amber">⭐ Nổi Bật</span><?php endif; ?>
        <?php if ($het_hang): ?><span class="badge badge-gray">Hết Hàng</span><?php endif; ?>
        <?php if ($sp['phan_tram_giam'] > 0): ?><span class="badge badge-red">-<?= $sp['phan_tram_giam'] ?>%</span><?php endif; ?>
      </div>
      <img src="<?= $hinh_main ?>"
           alt="<?= htmlspecialchars($sp['ten_sp']) ?>"
           id="mainImg"
           onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'"
           onclick="openLightbox(this.src)">
    </div>

    <?php if (count($hinh_list) > 1): ?>
    <div class="thumb-list">
      <?php foreach ($hinh_list as $i => $h): ?>
        <?php $src = resolveImagePath($h['duong_dan'] ?? null); ?>
        <div class="thumb <?= $i === 0 ? 'active' : '' ?>"
             onclick="changeMainImg(this, '<?= htmlspecialchars($src) ?>')">
          <img src="<?= $src ?>"
               alt="<?= htmlspecialchars($h['alt_text'] ?? $sp['ten_sp']) ?>"
               onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── THÔNG TIN SẢN PHẨM ── -->
  <div class="product-info reveal">
    <span class="pi-tag"><?= htmlspecialchars($sp['ten_loai'] ?? 'Gạo Việt') ?></span>
    <h1 class="pi-name"><?= htmlspecialchars($sp['ten_sp']) ?></h1>

    <?php if (!empty($sp['xuat_xu'])): ?>
    <div class="pi-origin">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
      Xuất xứ: <strong><?= htmlspecialchars($sp['xuat_xu']) ?></strong>
    </div>
    <?php endif; ?>

    <!-- Đánh giá -->
    <div class="pi-rating">
      <?= renderStars($avg_sao) ?>
      <span class="avg-num"><?= $avg_sao > 0 ? $avg_sao : '—' ?></span>
      <span class="review-count">(<?= $so_dg ?> đánh giá)</span>
      <?php if ($sp['luot_ban'] > 0): ?>
        <span style="font-size:0.82rem;color:#aaa;margin-left:8px;">• <?= number_format($sp['luot_ban']) ?> đã bán</span>
      <?php endif; ?>
    </div>

    <!-- Giá -->
    <div class="pi-price-block">
      <div>
        <div class="pi-price-main" id="priceDisplay"><?= formatGia($sp['gia_ban']) ?></div>
        <?php if ($sp['gia_goc']): ?>
          <div class="pi-price-orig"><?= formatGia($sp['gia_goc']) ?></div>
        <?php else: ?>
          <div style="font-size:0.8rem;color:#aaa;margin-top:2px;">/ 1kg</div>
        <?php endif; ?>
      </div>
      <?php if ($sp['phan_tram_giam'] > 0): ?>
        <span class="pi-price-save">Tiết kiệm <?= $sp['phan_tram_giam'] ?>%</span>
      <?php endif; ?>
    </div>

    <!-- Quy cách -->
    <?php if (!empty($quy_cach_list)): ?>
    <div class="quy-cach-label">Chọn quy cách:</div>
    <div class="quy-cach-list" id="qcList">
      <?php foreach ($quy_cach_list as $i => $qc): ?>
        <button class="qc-btn <?= $i === 0 ? 'active' : '' ?>"
                onclick="selectQC(this, <?= $qc['gia_ban'] ?>, <?= (int)$qc['so_luong_ton'] ?>, <?= $qc['id'] ?>)"
                data-id="<?= $qc['id'] ?>"
                data-gia="<?= $qc['gia_ban'] ?>"
                data-ton="<?= (int)$qc['so_luong_ton'] ?>">
          <?= $qc['trong_luong'] ?>kg
          <span><?= formatGia($qc['gia_ban']) ?></span>
        </button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Số lượng -->
    <div class="qty-row">
      <span class="qty-label">Số lượng:</span>
      <div class="qty-control">
        <button class="qty-btn" onclick="changeQty(-1)">−</button>
        <input type="number" class="qty-input" id="qtyInput" value="1" min="1" max="<?= max(1, (int)$sp['so_luong_ton']) ?>">
        <button class="qty-btn" onclick="changeQty(1)">+</button>
      </div>
      <?php if (!$het_hang): ?>
        <span class="qty-stock <?= $sp['so_luong_ton'] <= 20 ? 'low' : '' ?>">
          <?= $sp['so_luong_ton'] <= 20 ? '⚡ Chỉ còn ' . (int)$sp['so_luong_ton'] . ' kg' : 'Còn hàng' ?>
        </span>
      <?php endif; ?>
    </div>

    <!-- Nút hành động -->
    <div class="action-row">
      <button class="btn-cart" id="btnCart"
        <?= $het_hang ? 'disabled' : '' ?>
        onclick="themVaoGio()">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <?= $het_hang ? 'Hết Hàng' : 'Thêm Vào Giỏ' ?>
      </button>
      <button class="btn-buy" id="btnBuy"
        <?= $het_hang ? 'disabled' : '' ?>
        onclick="muaNgay()">
        Mua Ngay →
      </button>
    </div>

    <!-- Lợi ích -->
    <div class="benefits-row">
      <div class="benefit-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        Giao hàng toàn quốc
      </div>
      <div class="benefit-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Kiểm định chất lượng
      </div>
      <div class="benefit-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Miễn phí từ 500.000₫
      </div>
      <div class="benefit-item">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 4v6h6"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
        Đổi trả trong 7 ngày
      </div>
    </div>

  </div>
</div>

<!-- ── TABS: MÔ TẢ / THÔNG SỐ / ĐÁNH GIÁ ── -->
<div class="tabs-section">
  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab(this, 'tab-mota')">📝 Mô Tả Sản Phẩm</button>
    <button class="tab-btn" onclick="switchTab(this, 'tab-thongso')">📋 Thông Số</button>
    <button class="tab-btn" onclick="switchTab(this, 'tab-danhgia')">⭐ Đánh Giá (<?= $so_dg ?>)</button>
  </div>

  <!-- Tab: Mô tả -->
  <div class="tab-pane active" id="tab-mota">
    <div class="desc-content">
      <?php if (!empty($sp['mo_ta_ngan'])): ?>
        <p><strong><?= htmlspecialchars($sp['mo_ta_ngan']) ?></strong></p>
      <?php endif; ?>
      <?php if (!empty($sp['mo_ta_day_du'])): ?>
        <?= $sp['mo_ta_day_du'] ?>
      <?php elseif (!empty($sp['mo_ta_ngan'])): ?>
        <p><?= nl2br(htmlspecialchars($sp['mo_ta_ngan'])) ?></p>
      <?php else: ?>
        <p>Sản phẩm <?= htmlspecialchars($sp['ten_sp']) ?> được tuyển chọn từ những vùng lúa nổi tiếng nhất Việt Nam, đảm bảo chất lượng tốt nhất cho bữa cơm gia đình bạn.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tab: Thông số -->
  <div class="tab-pane" id="tab-thongso">
    <table class="spec-table">
      <tr><td>Mã sản phẩm</td><td><?= htmlspecialchars($sp['id_sp']) ?></td></tr>
      <tr><td>Tên sản phẩm</td><td><?= htmlspecialchars($sp['ten_sp']) ?></td></tr>
      <?php if ($sp['ten_loai']): ?>
      <tr><td>Loại gạo</td><td><?= htmlspecialchars($sp['ten_loai']) ?></td></tr>
      <?php endif; ?>
      <?php if ($sp['xuat_xu']): ?>
      <tr><td>Xuất xứ</td><td><?= htmlspecialchars($sp['xuat_xu']) ?></td></tr>
      <?php endif; ?>
      <?php if (!empty($quy_cach_list)): ?>
      <tr><td>Quy cách</td><td>
        <?= implode(' / ', array_map(fn($q) => $q['trong_luong'].'kg', $quy_cach_list)) ?>
      </td></tr>
      <?php endif; ?>
      <tr><td>Trạng thái</td><td><?= $het_hang ? '<span style="color:#ff4444;font-weight:600;">Hết hàng</span>' : '<span style="color:var(--green-dark);font-weight:600;">Còn hàng</span>' ?></td></tr>
      <tr><td>Đã bán</td><td><?= number_format($sp['luot_ban'] ?? 0) ?> kg</td></tr>
      <?php if (!empty($sp['ngay_tao'])): ?>
      <tr><td>Ngày cập nhật</td><td><?= date('d/m/Y', strtotime($sp['ngay_tao'])) ?></td></tr>
      <?php endif; ?>
    </table>
  </div>

  <!-- Tab: Đánh giá -->
  <div class="tab-pane" id="tab-danhgia">
    <?php if ($so_dg > 0): ?>
      <!-- Tóm tắt -->
      <div class="reviews-header">
        <div style="text-align:center;">
          <div class="review-summary-num"><?= $avg_sao ?></div>
          <div><?= renderStars($avg_sao, 22) ?></div>
          <div class="review-summary-label"><?= $so_dg ?> đánh giá</div>
        </div>
        <div class="review-bars">
          <?php
          for ($s = 5; $s >= 1; $s--):
            $cnt_s = 0;
            foreach ($danh_gia as $d) if ((int)$d['so_sao'] === $s) $cnt_s++;
            // Lấy từ DB đầy đủ hơn
            $stmt_s = $pdo->prepare("SELECT COUNT(*) FROM danhgia WHERE id_sp=? AND so_sao=? AND trang_thai='da_duyet'");
            $stmt_s->execute([$id_sp, $s]);
            $cnt_s = $stmt_s->fetchColumn();
            $pct = $so_dg > 0 ? round($cnt_s / $so_dg * 100) : 0;
          ?>
          <div class="review-bar-row">
            <span><?= $s ?>★</span>
            <div class="review-bar-track">
              <div class="review-bar-fill" style="width:<?= $pct ?>%"></div>
            </div>
            <span><?= $cnt_s ?></span>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Danh sách đánh giá -->
      <div class="review-list">
        <?php foreach ($danh_gia as $dg): ?>
        <div class="review-card">
          <div class="review-card-top">
            <div class="reviewer-avatar">
              <?= mb_strtoupper(mb_substr($dg['ho_ten'] ?? 'K', 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <div>
              <div class="reviewer-name"><?= htmlspecialchars($dg['ho_ten'] ?? 'Khách hàng') ?></div>
              <div style="display:flex;align-items:center;gap:8px;">
                <?= renderStars($dg['so_sao'], 14) ?>
                <span class="review-date"><?= date('d/m/Y', strtotime($dg['ngay_dg'])) ?></span>
              </div>
            </div>
          </div>
          <?php if ($dg['tieu_de']): ?>
            <div class="review-title"><?= htmlspecialchars($dg['tieu_de']) ?></div>
          <?php endif; ?>
          <?php if ($dg['noi_dung']): ?>
            <div class="review-body"><?= nl2br(htmlspecialchars($dg['noi_dung'])) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="no-reviews">
        <p>⭐</p>
        <p>Chưa có đánh giá nào. Hãy là người đầu tiên chia sẻ trải nghiệm!</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── SẢN PHẨM LIÊN QUAN ── -->
<?php if (!empty($sp_lienquan)): ?>
<section class="related-section">
  <div class="section-header">
    <span class="section-tag">Cùng Danh Mục</span>
    <h2 class="section-title">Sản Phẩm Liên Quan</h2>
  </div>
  <div class="related-grid">
    <?php foreach ($sp_lienquan as $lq): ?>
      <?php
        $lq_hinh = resolveImagePath($lq['hinh_chinh'] ?? null);
        $lq_het  = false;
      ?>
      <a href="chitietsanpham.php?id=<?= urlencode($lq['id_sp']) ?>" style="text-decoration:none;">
        <div class="product-card">
          <?php if ($lq['ban_chay']): ?><span class="product-badge" style="background:var(--green-mid)">Bán Chạy</span><?php endif; ?>
          <?php if ($lq['hang_moi']): ?><span class="product-badge">Mới</span><?php endif; ?>
          <?php if ($lq['phan_tram_giam'] > 0): ?><span class="discount-badge">-<?= $lq['phan_tram_giam'] ?>%</span><?php endif; ?>
          <div class="product-img-wrap">
            <img src="<?= $lq_hinh ?>"
                 alt="<?= htmlspecialchars($lq['ten_sp']) ?>"
                 onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
          </div>
          <div class="product-body product-body--compact">
            <h3 class="product-name"><?= htmlspecialchars($lq['ten_sp']) ?></h3>
            <div class="product-footer product-footer--centered">
              <div class="product-price">
                <?= formatGia($lq['gia_ban']) ?>
                <?php if ($lq['gia_goc']): ?>
                  <small style="text-decoration:line-through;color:#bbb;"><?= formatGia($lq['gia_goc']) ?></small>
                <?php else: ?>
                  <small>/ 1kg</small>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- FOOTER -->
<!-- <footer>
  <div class="footer-bottom" style="max-width:100%;padding:28px 5%;">
    <p>© 2026 <span>rice4u</span> – Tinh Hoa Đất Việt. Bảo lưu mọi quyền.</p>
    <a href="sanpham.php" style="font-size:0.85rem;color:var(--green-dark);text-decoration:none;">← Quay Lại Sản Phẩm</a>
  </div>
</footer> -->

<!-- TOAST -->
<div class="toast" id="toast">
  <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
  <span id="toastMsg">Đã thêm vào giỏ hàng!</span>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">×</button>
  <img src="" id="lightboxImg" alt="Ảnh phóng to">
</div>

<script>
// ── Dữ liệu sản phẩm ──
const spId   = '<?= $sp['id_sp'] ?>';
let   qcId   = <?= !empty($quy_cach_list) ? $quy_cach_list[0]['id'] : 'null' ?>;
let   curGia = <?= $sp['gia_ban'] ?>;
let   maxQty = <?= max(1, (int)$sp['so_luong_ton']) ?>;

// ── Chuyển ảnh chính ──
function changeMainImg(thumb, src) {
  document.getElementById('mainImg').src = src;
  document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
  thumb.classList.add('active');
}

// ── Chọn quy cách ──
function selectQC(btn, gia, ton, id) {
  document.querySelectorAll('.qc-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  curGia = gia;
  qcId   = id;
  maxQty = ton || 1;

  // Tính giá × số lượng hiện tại
  const qty = parseInt(document.getElementById('qtyInput').value);
  document.getElementById('priceDisplay').textContent = formatGia(gia * qty);

  document.getElementById('qtyInput').max = maxQty;
  if (qty > maxQty) document.getElementById('qtyInput').value = maxQty;
}
// ── Số lượng ──
function changeQty(delta) {
  const input = document.getElementById('qtyInput');
  let val = parseInt(input.value) + delta;
  val = Math.max(1, Math.min(val, maxQty));
  input.value = val;

  // Cập nhật giá theo số lượng
  const tongGia = curGia * val;
  document.getElementById('priceDisplay').textContent = formatGia(tongGia);
}
// ── Format giá ──
function formatGia(gia) {
  return new Intl.NumberFormat('vi-VN').format(gia) + '₫';
}

// ── Thêm vào giỏ ──
function themVaoGio() {
  const qty = parseInt(document.getElementById('qtyInput').value);
  const btn = document.getElementById('btnCart');
  const origHtml = btn.innerHTML;
  btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Đã Thêm!';
  btn.style.background = 'var(--green-dark)';
  const body = new URLSearchParams({
    id_sp: spId,
    so_luong: String(qty)
  }).toString();

  fetch('/rice4u/api/giohang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body
  })
  .then(r => r.ok ? r.json() : null)
  .then(data => {
    if (!data || !data.success) {
      showToast('Không thể thêm sản phẩm vào giỏ hàng');
      return;
    }

    const badgeHeader = document.getElementById('cart-count');
    if (badgeHeader && typeof data.total_items !== 'undefined') {
      badgeHeader.textContent = data.total_items;
    }

    const badgeLocal = document.getElementById('cartCount');
    if (badgeLocal && typeof data.total_items !== 'undefined') {
      badgeLocal.textContent = data.total_items;
    }

    showToast(`Đã thêm ${qty} sản phẩm vào giỏ hàng!`);
  })
  .catch(() => {
    showToast('Không thể thêm sản phẩm vào giỏ hàng');
  })
  .finally(() => {
    setTimeout(() => { btn.innerHTML = origHtml; btn.style.background = ''; }, 2200);
  });
}

// ── Mua ngay ──
function muaNgay() {
  themVaoGio();
  setTimeout(() => { window.location.href = 'giohang.php'; }, 600);
}

// ── Toast ──
function showToast(msg) {
  const toast = document.getElementById('toast');
  document.getElementById('toastMsg').textContent = msg;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3200);
}

// ── Lightbox ──
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
}

// ── Tabs ──
function switchTab(btn, targetId) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById(targetId).classList.add('active');
}

// ── Reveal animation ──
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
<?php include 'includes/footer.php'; ?>

