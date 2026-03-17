<?php
// ============================================================
// TRANG SẢN PHẨM - rice4u  (có tìm kiếm)
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
$loai_filter  = trim($_GET['loai']   ?? '');
$sort         = trim($_GET['sort']   ?? 'ban_chay');
$search       = trim($_GET['search'] ?? '');          // ← TÌM KIẾM
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
$where  = "WHERE sp.trang_thai = 1";
$params = [];

if ($loai_filter) {
    $where .= " AND sp.id_loai = :loai";
    $params[':loai'] = $loai_filter;
}

// ── Xử lý tìm kiếm ──
if ($search !== '') {
    $where .= " AND (sp.ten_sp LIKE :search OR sp.xuat_xu LIKE :search2 OR sp.mo_ta_ngan LIKE :search3)";
    $like = '%' . $search . '%';
    $params[':search']  = $like;
    $params[':search2'] = $like;
    $params[':search3'] = $like;
}

// ── Đếm tổng ──
$count_sql = "SELECT COUNT(*) FROM sanpham sp $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total       = (int)$stmt->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

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

// ── Tiêu đề ──
$ten_loai_hien_tai = 'Tất Cả Sản Phẩm';
if ($search !== '') {
    $ten_loai_hien_tai = 'Kết quả tìm kiếm: "' . htmlspecialchars($search) . '"';
} else {
    foreach ($loai_list as $l) {
        if ($l['id_loai'] === $loai_filter) {
            $ten_loai_hien_tai = $l['ten_loai'];
            break;
        }
    }
}

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
    return strlen($text) > $max ? substr($text, 0, $max - 1) . '...' : $text;
}

function resolveImagePath($path, $default = '/rice4u/asset/images/default.jpg') {
    if (empty($path)) return $default;
    $path = trim((string)$path);
    if (preg_match('#^https?://#i', $path)) return $path;
    $normalized = str_replace('\\', '/', ltrim($path, '/'));
    $candidates = [$normalized];
    if (strpos($normalized, 'asset/') !== 0) $candidates[] = 'asset/' . $normalized;
    $dot = strrpos($normalized, '.');
    if ($dot !== false) {
        $base = substr($normalized, 0, $dot);
        foreach (['png', 'webp', 'jpeg', 'jpg'] as $ext) {
            $c = $base . '.' . $ext;
            $candidates[] = $c;
            if (strpos($c, 'asset/') !== 0) $candidates[] = 'asset/' . $c;
        }
    }
    foreach ($candidates as $candidate) {
        $candidate = ltrim($candidate, '/');
        if (is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $candidate)))
            return '/rice4u/' . $candidate;
    }
    return $default;
}

$page_title = 'Sản Phẩm – Rice4U';
$active_nav = 'sanpham';
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
  .page-hero {
    background: linear-gradient(135deg, #237227 0%, #519A66 100%);
    padding: 120px 5% 60px;
    text-align: center;
    color: white;
  }
  .page-hero h1 { font-family: 'Playfair Display', serif; font-size: clamp(2rem, 4vw, 3rem); margin-bottom: 12px; }
  .page-hero p  { opacity: 0.85; font-weight: 300; font-size: 1rem; }

  /* Breadcrumb tìm kiếm */
  .search-result-bar {
    background: #e8f5e9;
    border-left: 4px solid #237227;
    padding: 12px 5%;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.9rem;
    color: #237227;
    flex-wrap: wrap;
  }
  .search-result-bar a {
    color: #888;
    text-decoration: none;
    font-size: 0.85rem;
    margin-left: auto;
  }
  .search-result-bar a:hover { text-decoration: underline; }

  .shop-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 40px;
    max-width: 1300px;
    margin: 0 auto;
    padding: 56px 5%;
  }
  .sidebar { position: sticky; top: 90px; align-self: start; }
  .filter-box {
    background: white;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 24px rgba(0,0,0,.06);
    margin-bottom: 20px;
  }
  .filter-box h3 { font-size: .85rem; font-weight: 700; color: #237227; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid #FFD786; }
  .filter-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; border-radius: 12px; cursor: pointer;
    text-decoration: none; color: #4a5e4a; font-size: .88rem; font-weight: 400;
    transition: all .2s; margin-bottom: 4px;
  }
  .filter-item:hover { background: #f0f7f0; color: #237227; }
  .filter-item.active { background: #237227; color: white; font-weight: 600; }
  .filter-count { background: rgba(0,0,0,.08); border-radius: 100px; padding: 2px 8px; font-size: .75rem; }
  .filter-item.active .filter-count { background: rgba(255,255,255,.25); }

  .shop-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
  }
  .shop-toolbar h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #237227; }
  .shop-toolbar span { font-size: .85rem; color: #888; font-weight: 300; }
  .sort-select {
    border: 1.5px solid #e0e0e0; border-radius: 100px;
    padding: 9px 20px; font-family: 'Be Vietnam Pro', sans-serif;
    font-size: .85rem; color: #4a5e4a; background: white; cursor: pointer; outline: none;
  }
  .sort-select:focus { border-color: #237227; }

  .products-grid-full {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 24px;
  }

  /* Trạng thái không tìm thấy */
  .empty-search {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #aaa;
  }
  .empty-search h3 { font-size: 1.3rem; margin-bottom: 10px; color: #555; }
  .empty-search a {
    display: inline-block; margin-top: 16px;
    background: #237227; color: white;
    padding: 10px 24px; border-radius: 30px;
    text-decoration: none; font-size: .9rem;
  }

  /* PRODUCT CARD */
  .product-card { position: relative; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 3px 16px rgba(0,0,0,.06); transition: transform .25s, box-shadow .25s; }
  .product-card:hover { transform: translateY(-4px); box-shadow: 0 10px 32px rgba(35,114,39,.12); }
  .product-card.out-of-stock { opacity: .6; }
  .product-img-wrap { aspect-ratio: 1/1; overflow: hidden; background: #f5f5f5; cursor: pointer; position: relative; }
  .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
  .product-card:hover .product-img-wrap img { transform: scale(1.06); }
  .product-badge { position: absolute; top: 10px; left: 10px; background: var(--amber, #f9a825); color: white; font-size: .7rem; font-weight: 700; padding: 4px 10px; border-radius: 30px; z-index: 2; letter-spacing: .04em; }
  .product-badge.hot { background: #237227; }
  .discount-badge { position: absolute; top: 10px; right: 10px; background: #e53935; color: white; font-size: .7rem; font-weight: 700; padding: 4px 8px; border-radius: 6px; z-index: 2; }
  .product-body--compact { padding: 14px 16px 16px; }
  .product-name { font-size: .93rem; font-weight: 600; color: #222; margin: 0 0 8px; line-height: 1.35; cursor: pointer; }
  .product-name:hover { color: #237227; }
  .product-footer--centered { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  .product-price { font-size: 1rem; font-weight: 700; color: #237227; }
  .btn-add {color: white; border: none; border-radius: 20px; padding: 7px 14px; font-size: .78rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .2s; }
  .btn-add:hover { background: #1b5e20; }
  .btn-add--large { padding: 8px 16px; font-size: .82rem; }

  /* Pagination */
  .pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; flex-wrap: wrap; }
  .pagination a, .pagination span {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 50%;
    font-size: .88rem; font-weight: 600; text-decoration: none;
    border: 1.5px solid #e0e0e0; color: #555; transition: all .2s;
  }
  .pagination a:hover { border-color: #237227; color: #237227; background: #f0f7f0; }
  .pagination span.active { background: #237227; color: white; border-color: #237227; }
  .pagination span.dots { border: none; color: #aaa; }
</style>
</head>
<body>

<!-- PAGE HERO -->
<div class="page-hero">
  <h1>🌾 <?= htmlspecialchars($ten_loai_hien_tai) ?></h1>
  <p>
    <?php if ($search !== ''): ?>
      Tìm thấy <strong><?= $total ?></strong> sản phẩm cho từ khoá "<strong><?= htmlspecialchars($search) ?></strong>"
    <?php else: ?>
      Tìm thấy <strong><?= $total ?></strong> sản phẩm chất lượng cao từ các vùng lúa Việt Nam
    <?php endif; ?>
  </p>
</div>

<?php if ($search !== ''): ?>
<div class="search-result-bar">
  🔍 Kết quả tìm kiếm cho: <strong>"<?= htmlspecialchars($search) ?>"</strong>
  <a href="/rice4u/sanpham.php">✕ Xoá tìm kiếm</a>
</div>
<?php endif; ?>

<div class="shop-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="filter-box">
      <h3>📦 Loại Gạo</h3>
      <?php
        $stmt_all = $pdo->query("SELECT COUNT(*) FROM sanpham WHERE trang_thai = 1");
        $total_all = $stmt_all->fetchColumn();
      ?>
      <a href="sanpham.php?sort=<?= $sort ?><?= $search ? '&search='.urlencode($search) : '' ?>"
         class="filter-item <?= !$loai_filter ? 'active' : '' ?>">
        Tất Cả <span class="filter-count"><?= $total_all ?></span>
      </a>
      <?php foreach ($loai_list as $l): ?>
        <?php
          $sc = $pdo->prepare("SELECT COUNT(*) FROM sanpham WHERE id_loai = ? AND trang_thai = 1");
          $sc->execute([$l['id_loai']]);
          $cnt = $sc->fetchColumn();
          if ($cnt == 0) continue;
        ?>
        <a href="sanpham.php?loai=<?= $l['id_loai'] ?>&sort=<?= $sort ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           class="filter-item <?= $loai_filter === $l['id_loai'] ? 'active' : '' ?>">
          <?= htmlspecialchars($l['ten_loai']) ?> <span class="filter-count"><?= $cnt ?></span>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="filter-box">
      <h3>⭐ Sắp Xếp</h3>
      <?php
        $sorts = ['ban_chay'=>'Bán Chạy Nhất','moi_nhat'=>'Mới Nhất','gia_tang'=>'Giá Thấp → Cao','gia_giam'=>'Giá Cao → Thấp'];
        foreach ($sorts as $k => $label):
      ?>
        <a href="sanpham.php?loai=<?= $loai_filter ?>&sort=<?= $k ?><?= $search ? '&search='.urlencode($search) : '' ?>"
           class="filter-item <?= $sort===$k ? 'active' : '' ?>">
          <?= $label ?>
        </a>
      <?php endforeach; ?>
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
        <?php foreach ($sorts as $k => $label): ?>
          <option value="sanpham.php?loai=<?= $loai_filter ?>&sort=<?= $k ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                  <?= $sort===$k ? 'selected' : '' ?>>
            <?= $label ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="products-grid-full">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $p):
          $hinh    = resolveImagePath($p['hinh_chinh'] ?? null);
          $het     = ($p['so_luong_ton'] <= 0);
          $badge   = '';
          if ($p['ban_chay']) $badge = '<span class="product-badge" style="background:var(--green-mid)">Bán Chạy</span>';
          if ($p['hang_moi']) $badge = '<span class="product-badge">Mới</span>';
          if ($p['noi_bat'])  $badge = '<span class="product-badge hot">⭐ Nổi Bật</span>';
          $giam = ($p['phan_tram_giam'] > 0) ? '<span class="discount-badge">-'.$p['phan_tram_giam'].'%</span>' : '';
        ?>
        <div class="product-card <?= $het ? 'out-of-stock' : '' ?>">
          <?= $badge ?>
          <div class="product-img-wrap"
               onclick="window.location='/rice4u/chitietsanpham.php?id=<?= $p['id_sp'] ?>'">
            <?= $giam ?>
            <img src="<?= $hinh ?>" alt="<?= htmlspecialchars($p['ten_sp']) ?>"
                 onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
          </div>
          <div class="product-body--compact">
            <h3 class="product-name"
                onclick="window.location='/rice4u/chitietsanpham.php?id=<?= $p['id_sp'] ?>'">
              <?= htmlspecialchars($p['ten_sp']) ?>
            </h3>
            <div class="product-footer--centered">
              <div class="product-price">
                <?= formatGia($p['gia_ban']) ?>
                <?php if ($p['gia_goc']): ?>
                  <small style="text-decoration:line-through;color:#aaa;font-size:.8em;"><?= formatGia($p['gia_goc']) ?></small>
                <?php else: ?>
                  <small style="color:#aaa;font-size:.8em;">/ 1kg</small>
                <?php endif; ?>
              </div>
              <?php if (!$het): ?>
                <button class="btn-add btn-add--large" onclick="themVaoGio('<?= $p['id_sp'] ?>')">
                  + Giỏ
                </button>
              <?php else: ?>
                <span style="font-size:.75rem;color:#aaa;">Hết hàng</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

      <?php else: ?>
        <div class="empty-search">
          <div style="font-size:3rem;margin-bottom:12px;">🌾</div>
          <h3>Không tìm thấy sản phẩm nào<?= $search ? " cho \"" . htmlspecialchars($search) . "\"" : '' ?></h3>
          <p>Thử tìm với từ khoá khác hoặc xem tất cả sản phẩm.</p>
          <a href="/rice4u/sanpham.php">Xem tất cả sản phẩm</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <?php
          $link = "sanpham.php?loai=$loai_filter&sort=$sort&page=$i" . ($search ? '&search='.urlencode($search) : '');
        ?>
        <?php if ($i === $page): ?>
          <span class="active"><?= $i ?></span>
        <?php else: ?>
          <a href="<?= $link ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function themVaoGio(id_sp) {
  fetch('/rice4u/api/giohang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=add&id_sp=' + encodeURIComponent(id_sp) + '&so_luong=1'
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const badge = document.getElementById('cart-count');
      if (badge) badge.textContent = data.total_items ?? '';
      showToast('✅ Đã thêm vào giỏ hàng!');
    }
  })
  .catch(() => {});
}

function showToast(msg) {
  let t = document.getElementById('sp-toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'sp-toast';
    t.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:#237227;color:#fff;padding:12px 28px;border-radius:30px;font-size:.9rem;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.15);transition:opacity .3s';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.style.opacity = '1';
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.style.opacity = '0', 2200);
}
</script>
</body>
</html>