<?php
// ============================================================
// TRANG SẢN PHẨM - rice4u
// ============================================================
$host     = 'localhost';
$dbname   = 'rice4u';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối: " . $e->getMessage());
}

// ── Bộ lọc & phân trang ──
$loai_filter  = $_GET['loai']   ?? '';
$sort         = $_GET['sort']   ?? 'ban_chay';
$page         = max(1, (int)($_GET['page'] ?? 1));
$per_page     = 12;
$offset       = ($page - 1) * $per_page;

// ── Lấy danh sách loại gạo ──
$loai_list = $pdo->query("SELECT id_loai, ten_loai FROM loaigao WHERE trang_thai = 1 ORDER BY ten_loai")->fetchAll(PDO::FETCH_ASSOC);

// ── Sắp xếp ──
$order_map = [
    'ban_chay'  => 'sp.luot_ban DESC',
    'gia_tang'  => 'sp.gia_ban ASC',
    'gia_giam'  => 'sp.gia_ban DESC',
    'moi_nhat'  => 'sp.ngay_tao DESC',
];
$order_sql = $order_map[$sort] ?? 'sp.luot_ban DESC';

// ── Điều kiện lọc ──
$where = "WHERE sp.trang_thai = 1";
$params = [];
if ($loai_filter) {
    $where .= " AND sp.id_loai = :loai";
    $params[':loai'] = $loai_filter;
}

// ── Đếm tổng ──
$count_sql = "SELECT COUNT(*) FROM sanpham sp $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// ── Lấy sản phẩm ──
$sql = "
    SELECT sp.id_sp, sp.ten_sp, sp.xuat_xu, sp.mo_ta_ngan,
           sp.gia_ban, sp.gia_goc, sp.phan_tram_giam,
           sp.noi_bat, sp.ban_chay, sp.hang_moi, sp.so_luong_ton,
           lg.ten_loai,
           ha.duong_dan AS hinh_chinh
    FROM sanpham sp
    LEFT JOIN loaigao lg ON sp.id_loai = lg.id_loai
    LEFT JOIN hinhanh_sp ha ON sp.id_sp = ha.id_sp AND ha.la_anh_chinh = 1
    $where
    ORDER BY $order_sql
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,   PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatGia($gia) {
    return number_format($gia, 0, ',', '.') . '₫';
}

function rutGonMoTa($text, $max = 78) {
    $text = trim((string)$text);
    if ($text === '') return '';

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text, 'UTF-8') > $max
            ? mb_substr($text, 0, $max - 1, 'UTF-8') . '...'
            : $text;
    }

    return strlen($text) > $max
        ? substr($text, 0, $max - 1) . '...'
        : $text;
}

function resolveImagePath($path, $default = '/rice4u/asset/images/default.jpg') {
    if (empty($path)) return $default;

    $path = trim((string)$path);
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $normalized = str_replace('\\', '/', ltrim($path, '/'));
    $candidates = [$normalized];

    if (strpos($normalized, 'asset/') !== 0) {
        $candidates[] = 'asset/' . $normalized;
    }

    $dot = strrpos($normalized, '.');
    if ($dot !== false) {
        $base = substr($normalized, 0, $dot);
        foreach (['png', 'webp', 'jpeg', 'jpg'] as $ext) {
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

// ── Tên loại hiện tại ──
$ten_loai_hien_tai = 'Tất Cả Sản Phẩm';
foreach ($loai_list as $l) {
    if ($l['id_loai'] === $loai_filter) {
        $ten_loai_hien_tai = $l['ten_loai'];
        break;
    }
}
include 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="/rice4u/asset/images/favicon.ico" type="image/x-icon">
<title>Sản Phẩm – Rice4U</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/rice4u/asset/styles.css">
<style>
  /* ── TRANG SẢN PHẨM ── */
  .page-hero {
    background: linear-gradient(135deg, #237227 0%, #519A66 100%);
    padding: 120px 5% 60px;
    text-align: center;
    color: white;
  }
  .page-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 4vw, 3rem);
    margin-bottom: 12px;
  }
  .page-hero p { opacity: 0.85; font-weight: 300; font-size: 1rem; }

  .shop-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 40px;
    max-width: 1300px;
    margin: 0 auto;
    padding: 56px 5%;
  }

  /* ── SIDEBAR ── */
  .sidebar { position: sticky; top: 90px; align-self: start; }

  .filter-box {
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    margin-bottom: 20px;
  }
  .filter-box h3 {
    font-size: 0.85rem;
    font-weight: 700;
    color: #237227;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 18px;
    padding-bottom: 10px;
    border-bottom: 2px solid #FFD786;
  }

  .filter-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-radius: 12px;
    cursor: pointer;
    text-decoration: none;
    color: #4a5e4a;
    font-size: 0.88rem;
    font-weight: 400;
    transition: all 0.2s;
    margin-bottom: 4px;
  }
  .filter-item:hover { background: #f0f7f0; color: #237227; }
  .filter-item.active {
    background: #237227;
    color: white;
    font-weight: 600;
  }
  .filter-count {
    background: rgba(0,0,0,0.08);
    border-radius: 100px;
    padding: 2px 8px;
    font-size: 0.75rem;
  }
  .filter-item.active .filter-count { background: rgba(255,255,255,0.25); }

  /* ── MAIN CONTENT ── */
  .shop-main { }

  .shop-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    flex-wrap: wrap;
    gap: 12px;
  }
  .shop-toolbar h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #237227;
  }
  .shop-toolbar span {
    font-size: 0.85rem;
    color: #888;
    font-weight: 300;
  }

  .sort-select {
    border: 1.5px solid #e0e0e0;
    border-radius: 100px;
    padding: 9px 20px;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 0.85rem;
    color: #4a5e4a;
    background: white;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
  }
  .sort-select:focus { border-color: #237227; }

  .products-grid-full {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
  }

  /* Kế thừa product-card từ /rice4u/asset/styles.css */

  /* ── PHÂN TRANG ── */
  .pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 52px;
    flex-wrap: wrap;
  }
  .page-btn {
    width: 42px; height: 42px;
    border-radius: 12px;
    border: 1.5px solid #e0e0e0;
    background: white;
    color: #4a5e4a;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
  }
  .page-btn:hover { border-color: #237227; color: #237227; }
  .page-btn.active { background: #237227; border-color: #237227; color: white; }
  .page-btn.disabled { opacity: 0.4; pointer-events: none; }

  /* ── EMPTY STATE ── */
  .empty-state {
    grid-column: 1/-1;
    text-align: center;
    padding: 80px 20px;
    color: #aaa;
  }
  .empty-state p:first-child { font-size: 3rem; margin-bottom: 16px; }
  .empty-state p:last-child { font-size: 0.95rem; }

  /* ── HẾT HÀNG ── */
  .out-of-stock { opacity: 0.6; }
  .out-of-stock .btn-add {
    background: #ccc !important;
    cursor: not-allowed;
    box-shadow: none;
  }

  @media (max-width: 900px) {
    .shop-layout { grid-template-columns: 1fr; }
    .sidebar { position: static; }
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
    <a href="sanpham.php" style="color:var(--green-dark);font-weight:600;">Sản Phẩm</a>
    <a href="#">Câu Chuyện Gạo Việt</a>
    <a href="#">Về Chúng Tôi</a>
    <a href="#">Liên Hệ</a>
  </nav>
  <div class="header-actions">
    <button class="icon-btn" title="Tìm kiếm">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    </button>
    <button class="icon-btn" title="Giỏ hàng" style="position:relative">
      <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
      <span class="cart-badge">0</span>
    </button>
    <a href="#" class="btn-primary" style="padding:11px 24px;font-size:0.82rem;">Đăng Nhập</a>
  </div>
</header> -->

<!-- PAGE HERO -->
<div class="page-hero">
  <h1>🌾 <?= htmlspecialchars($ten_loai_hien_tai) ?></h1>
  <p>Tìm thấy <strong><?= $total ?></strong> sản phẩm chất lượng cao từ các vùng lúa Việt Nam</p>
</div>

<!-- SHOP LAYOUT -->
<div class="shop-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="filter-box">
      <h3>📦 Loại Gạo</h3>

      <!-- Tất cả -->
      <?php
        $stmt_all = $pdo->query("SELECT COUNT(*) FROM sanpham WHERE trang_thai = 1");
        $total_all = $stmt_all->fetchColumn();
      ?>
      <a href="sanpham.php?sort=<?= $sort ?>"
         class="filter-item <?= !$loai_filter ? 'active' : '' ?>">
        Tất Cả
        <span class="filter-count"><?= $total_all ?></span>
      </a>

      <!-- Từng loại -->
      <?php foreach ($loai_list as $l): ?>
        <?php
          $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM sanpham WHERE id_loai = ? AND trang_thai = 1");
          $stmt_count->execute([$l['id_loai']]);
          $count_loai = $stmt_count->fetchColumn();
          if ($count_loai == 0) continue;
        ?>
        <a href="sanpham.php?loai=<?= $l['id_loai'] ?>&sort=<?= $sort ?>"
           class="filter-item <?= $loai_filter === $l['id_loai'] ? 'active' : '' ?>">
          <?= htmlspecialchars($l['ten_loai']) ?>
          <span class="filter-count"><?= $count_loai ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="filter-box">
      <h3>⭐ Nổi Bật</h3>
      <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=ban_chay" class="filter-item <?= $sort==='ban_chay' ? 'active':'' ?>">Bán Chạy Nhất</a>
      <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=moi_nhat" class="filter-item <?= $sort==='moi_nhat' ? 'active':'' ?>">Mới Nhất</a>
      <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=gia_tang"  class="filter-item <?= $sort==='gia_tang'  ? 'active':'' ?>">Giá Thấp → Cao</a>
      <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=gia_giam"  class="filter-item <?= $sort==='gia_giam'  ? 'active':'' ?>">Giá Cao → Thấp</a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="shop-main">
    <div class="shop-toolbar">
      <div>
        <h2><?= htmlspecialchars($ten_loai_hien_tai) ?></h2>
        <span>Hiển thị <?= count($products) ?> / <?= $total ?> sản phẩm</span>
      </div>
      <select class="sort-select" onchange="window.location=this.value">
        <option value="sanpham.php?loai=<?= $loai_filter ?>&sort=ban_chay" <?= $sort==='ban_chay'?'selected':'' ?>>Bán Chạy Nhất</option>
        <option value="sanpham.php?loai=<?= $loai_filter ?>&sort=moi_nhat" <?= $sort==='moi_nhat'?'selected':'' ?>>Mới Nhất</option>
        <option value="sanpham.php?loai=<?= $loai_filter ?>&sort=gia_tang"  <?= $sort==='gia_tang'?'selected':'' ?>>Giá Thấp → Cao</option>
        <option value="sanpham.php?loai=<?= $loai_filter ?>&sort=gia_giam"  <?= $sort==='gia_giam'?'selected':'' ?>>Giá Cao → Thấp</option>
      </select>
    </div>

    <!-- LƯỚI SẢN PHẨM -->
    <div class="products-grid-full">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p): ?>
          <?php
            $hinh = resolveImagePath($p['hinh_chinh'] ?? null);

            $het_hang = ($p['so_luong_ton'] <= 0);

            $badge = '';
            if ($p['ban_chay'])  $badge = '<span class="product-badge" style="background:var(--green-mid)">Bán Chạy</span>';
            if ($p['hang_moi'])  $badge = '<span class="product-badge">Mới</span>';
            if ($p['noi_bat'])   $badge = '<span class="product-badge hot">⭐ Nổi Bật</span>';
          ?>
          <div class="product-card <?= $het_hang ? 'out-of-stock' : '' ?>">
            <?= $badge ?>
            <?php if ($het_hang): ?>
              <span class="product-badge" style="background:#999;top:48px;">Hết Hàng</span>
            <?php endif; ?>

            <div class="product-img-wrap" 
     onclick="window.location='chitietsanpham.php?id=<?= $p['id_sp'] ?>'"
     style="cursor:pointer;">
              <?php if ($p['phan_tram_giam'] > 0): ?>
                <span class="discount-badge">-<?= $p['phan_tram_giam'] ?>%</span>
              <?php endif; ?>
              <img src="<?= $hinh ?>"
                   alt="<?= htmlspecialchars($p['ten_sp']) ?>"
                   onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
            </div>
            <div class="product-body product-body--compact">
              <h3 class="product-name"><?= htmlspecialchars($p['ten_sp']) ?></h3>
              <div class="product-footer product-footer--centered">
                <div class="product-price">
                  <?= formatGia($p['gia_ban']) ?>
                  <?php if ($p['gia_goc']): ?>
                    <small style="text-decoration:line-through;color:#bbb;"><?= formatGia($p['gia_goc']) ?></small>
                  <?php else: ?>
                    <small>/ 1kg</small>
                  <?php endif; ?>
                </div>
                <button class="btn-add btn-add--large"
                  <?= $het_hang ? 'disabled' : "onclick=\"themVaoGio('{$p['id_sp']}')\"" ?>>
                  <?php if ($het_hang): ?>
                    Hết Hàng
                  <?php else: ?>
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    Thêm Vào Giỏ
                  <?php endif; ?>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <p>🌾</p>
          <p>Không có sản phẩm nào trong danh mục này.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- PHÂN TRANG -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <!-- Trang trước -->
      <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=<?= $sort ?>&page=<?= $page-1 ?>"
         class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php if ($i == 1 || $i == $total_pages || abs($i - $page) <= 2): ?>
          <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=<?= $sort ?>&page=<?= $i ?>"
             class="page-btn <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php elseif (abs($i - $page) == 3): ?>
          <span class="page-btn disabled">…</span>
        <?php endif; ?>
      <?php endfor; ?>

      <!-- Trang sau -->
      <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=<?= $sort ?>&page=<?= $page+1 ?>"
         class="page-btn <?= $page >= $total_pages ? 'disabled' : '' ?>">›</a>
    </div>
    <?php endif; ?>

  </main>
</div>

<!-- FOOTER rút gọn -->
<!-- <footer>
  <div class="footer-bottom" style="max-width:100%;padding:28px 5%;">
    <p>© 2026 <span>rice4u</span> – Tinh Hoa Đất Việt. Bảo lưu mọi quyền.</p>
    <a href="trangchu.php" style="font-size:0.85rem;color:var(--green-dark);text-decoration:none;">← Về Trang Chủ</a>
  </div>
</footer> -->

<script>
  function themVaoGio(idSp) {
    const btn = event.currentTarget;
    const orig = btn.innerHTML;
    btn.innerHTML = '✓ Đã Thêm';
    btn.style.background = '#237227';
    setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; }, 1800);

    const body = new URLSearchParams({ id_sp: idSp, so_luong: '1' }).toString();
    fetch('/rice4u/api/giohang.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
      if (!data || !data.success) return;
      const badge = document.getElementById('cart-count');
      if (badge && typeof data.total_items !== 'undefined') {
        badge.textContent = data.total_items;
      }
    })
    .catch(() => {});
  }
</script>
</body>
</html>
<?php include 'includes/footer.php'; ?>


