<?php
/**
 * RICE4U — includes/header.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = 0;
if (!empty($_SESSION['gio_hang']) && is_array($_SESSION['gio_hang'])) {
    $cartCount = array_sum(array_map(static function ($item) {
        return (int)($item['so_luong'] ?? 0);
    }, $_SESSION['gio_hang']));
}

// Giữ lại từ khoá tìm kiếm nếu đang ở trang sanpham
$search_val = htmlspecialchars(trim($_GET['search'] ?? ''));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title ?? 'Rice4U – Tinh Hoa Gạo Việt') ?></title>

  <link rel="icon" href="/rice4u/asset/images/favicon.ico" type="image/x-icon">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  <link rel="stylesheet" href="/rice4u/asset/styles.css">
  <link rel="stylesheet" href="/rice4u/asset/header.css">
  <link rel="stylesheet" href="/rice4u/asset/footer.css">

  <style>
    /* ══════════════════════════════════════════
       FIX 1: Dropdown user – không mất khi hover
       Dùng padding-top tạo "cầu nối" vô hình
    ══════════════════════════════════════════ */
    .user-icon {
      position: relative;
    }

    /* Vùng kích hoạt mở rộng xuống để chuột không "rơi" vào khoảng trống */
    .user-icon > a {
      display: inline-flex;
      align-items: center;
      padding-bottom: 10px; /* tạo cầu nối */
    }

    .log-out {
      display: none;
      position: absolute;
      top: calc(100% - 2px); /* sát ngay dưới icon, không có khoảng cách */
      right: 0;
      background: #ffffff;
      border: 1px solid #e0e0e0;
      box-shadow: 0 8px 24px rgba(0,0,0,0.13);
      padding: 6px 0;
      border-radius: 10px;
      min-width: 180px;
      z-index: 9999;
      /* Thêm padding-top vô hình để chuột không rớt khỏi hover zone */
      margin-top: 0;
    }

    /* Hiện dropdown khi hover vào TOÀN BỘ .user-icon (bao gồm cả .log-out) */
    .user-icon:hover .log-out {
      display: block;
    }

    .log-out a {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 18px;
      color: #333;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      transition: background 0.15s, color 0.15s;
      white-space: nowrap;
    }

    .log-out a:hover {
      background: #f5f5f5;
      color: var(--green-dark, #1b5e20);
    }

    .log-out a.logout-button-link {
      color: #e53935;
      border-top: 1px solid #f0f0f0;
      margin-top: 4px;
    }

    .log-out a.logout-button-link:hover {
      background: #fce4ec;
      color: #b71c1c;
    }

    /* Divider line trong dropdown */
    .log-out .dropdown-divider {
      height: 1px;
      background: #f0f0f0;
      margin: 4px 0;
    }

    /* ══════════════════════════════════════════
       FIX 2: Thanh tìm kiếm hoạt động
    ══════════════════════════════════════════ */
    .search-box {
      display: flex;
      align-items: center;
      position: relative;
    }

    .search-box input {
      border: none;
      outline: none;
      padding: 8px 40px 8px 16px;
      border-radius: 20px;
      background: rgba(255,255,255,0.15);
      color: inherit;
      font-size: 14px;
      font-family: inherit;
      width: 220px;
      transition: width 0.3s, background 0.3s;
    }

    .search-box input:focus {
      width: 280px;
      background: rgba(255,255,255,0.25);
    }

    .search-box input::placeholder {
      color: rgba(255,255,255,0.65);
    }

    .search-box .search {
      position: absolute;
      right: 8px;
      background: none;
      border: none;
      cursor: pointer;
      color: inherit;
      padding: 4px 6px;
      display: flex;
      align-items: center;
      font-size: 15px;
      transition: color 0.2s;
    }

    .search-box .search:hover {
      color: var(--amber, #f9a825);
    }

    /* Kết quả gợi ý tìm kiếm (autocomplete) */
    .search-suggestions {
      display: none;
      position: absolute;
      top: calc(100% + 6px);
      left: 0;
      right: 0;
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      z-index: 9999;
      overflow: hidden;
      max-height: 320px;
      overflow-y: auto;
    }

    .search-suggestions.show {
      display: block;
    }

    .suggestion-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 16px;
      cursor: pointer;
      text-decoration: none;
      color: #333;
      font-size: 14px;
      transition: background 0.15s;
      border-bottom: 1px solid #f5f5f5;
    }

    .suggestion-item:last-child { border-bottom: none; }

    .suggestion-item:hover {
      background: #f5f9f5;
      color: var(--green-dark, #1b5e20);
    }

    .suggestion-item img {
      width: 36px;
      height: 36px;
      object-fit: cover;
      border-radius: 6px;
      flex-shrink: 0;
    }

    .suggestion-item .s-name {
      font-weight: 500;
      flex: 1;
    }

    .suggestion-item .s-price {
      font-size: 12px;
      color: var(--green-mid, #2e7d32);
      font-weight: 600;
    }

    .suggestion-empty {
      padding: 14px 16px;
      color: #aaa;
      font-size: 13px;
      text-align: center;
    }
  </style>
</head>

<body>

<header>
  <div class="logo-navi">
    <a class="logo" href="/rice4u/trangchu.php">
      <img src="/rice4u/asset/images/logo.png" alt="Rice4U Logo">
    </a>
    <nav>
      <ul class="dieu-huong">
        <li>
          <a href="/rice4u/trangchu.php"
             class="<?= ($active_nav ?? '') === 'trangchu' ? 'active' : '' ?>">
            Trang Chủ
          </a>
        </li>
        <li>
          <a href="/rice4u/sanpham.php"
             class="<?= ($active_nav ?? '') === 'sanpham' ? 'active' : '' ?>">
            Sản Phẩm
          </a>
        </li>
        <li>
          <a href="/rice4u/lienhe.php"
             class="<?= ($active_nav ?? '') === 'lienhe' ? 'active' : '' ?>">
            Liên Hệ
          </a>
        </li>
      </ul>
    </nav>

    <!-- ── THANH TÌM KIẾM ── -->
    <div class="search-box" id="searchBox" style="position:relative;">
      <input
        type="text"
        id="searchInput"
        placeholder="Nhập loại gạo bạn muốn tìm..."
        value="<?= $search_val ?>"
        autocomplete="off"
      >
      <button class="search" type="button" aria-label="Tìm kiếm" onclick="doSearch()">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
      <!-- Gợi ý tìm kiếm -->
      <div class="search-suggestions" id="searchSuggestions"></div>
    </div>
  </div>

  <!-- ── KHU VỰC USER ── -->
  <div class="user">
    <?php if (isset($_SESSION['ma_tk'])): ?>
      <!-- Đã đăng nhập -->
      <div class="icon">
        <a href="/rice4u/giohang.php" aria-label="Giỏ hàng">
          <i class="fa-solid fa-cart-shopping">
            <span class="soluong" id="cart-count"><?= $cartCount ?></span>
          </i>
        </a>
        <div class="user-icon">
          <a href="/rice4u/hoso.php" aria-label="Tài khoản">
            <i class="fa-solid fa-user"></i>
          </a>
          <div class="log-out">
            <a href="/rice4u/hoso.php">
              <i class="fa-regular fa-user" style="width:16px;"></i>
              Thông tin cá nhân
            </a>
            <a href="/rice4u/dangxuat.php" class="logout-button-link">
              <i class="fa-solid fa-right-from-bracket" style="width:16px;"></i>
              Đăng xuất
            </a>
          </div>
        </div>
      </div>
    <?php else: ?>
      <!-- Chưa đăng nhập -->
      <button class="login-button">
        <a href="/rice4u/dangnhap.php">Đăng Nhập</a>
      </button>
      <button class="signup-button">
        <a href="/rice4u/dangky.php">Đăng Ký</a>
      </button>
      <div class="icon">
        <a href="/rice4u/giohang.php" aria-label="Giỏ hàng">
          <i class="fa-solid fa-cart-shopping">
            <span class="soluong" id="cart-count"><?= $cartCount ?></span>
          </i>
        </a>
        <div class="user-icon">
          <a href="/rice4u/dangnhap.php" aria-label="Đăng nhập">
            <i class="fa-solid fa-user"></i>
          </a>
          <div class="log-out">
            <a href="/rice4u/dangnhap.php">
              <i class="fa-solid fa-right-to-bracket" style="width:16px;"></i>
              Đăng nhập
            </a>
            <a href="/rice4u/dangky.php">
              <i class="fa-solid fa-user-plus" style="width:16px;"></i>
              Đăng ký
            </a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</header>

<script>
(function () {
  const input  = document.getElementById('searchInput');
  const box    = document.getElementById('searchBox');
  const drop   = document.getElementById('searchSuggestions');
  let   timer  = null;

  // ── Thực hiện tìm kiếm ──
  function doSearch() {
    const q = input.value.trim();
    if (q === '') return;               // Không làm gì nếu ô rỗng
    window.location.href = '/rice4u/sanpham.php?search=' + encodeURIComponent(q);
  }

  // Enter → tìm kiếm, Escape → ẩn gợi ý
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter')  { e.preventDefault(); doSearch(); }
    if (e.key === 'Escape') hideDrop();
  });

  // Nút kính lúp
  document.querySelector('.search').addEventListener('click', doSearch);

  // ── Gợi ý live ──
  input.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length < 2) { hideDrop(); return; }
    timer = setTimeout(() => loadSuggest(q), 280);
  });

  function loadSuggest(q) {
    fetch('/rice4u/api/search_suggest.php?q=' + encodeURIComponent(q))
      .then(r => r.ok ? r.json() : [])
      .then(items => showDrop(items))
      .catch(() => hideDrop());
  }

  function showDrop(items) {
    if (!items || items.length === 0) {
      drop.innerHTML = '<div class="suggestion-empty">Không tìm thấy sản phẩm phù hợp</div>';
      drop.classList.add('show');
      return;
    }
    let html = '';
    items.forEach(function (p) {
      // Xây đường dẫn ảnh: nếu có hinh_chinh thì thêm /rice4u/ phía trước
      let hinh = '/rice4u/asset/images/default.jpg';
      if (p.hinh_chinh && p.hinh_chinh !== '') {
        hinh = '/rice4u/' + p.hinh_chinh.replace(/^\//, '');
      }
      const gia = new Intl.NumberFormat('vi-VN').format(p.gia_ban) + '₫';
      const ten = String(p.ten_sp).replace(/</g,'&lt;').replace(/>/g,'&gt;');
      const xu  = p.xuat_xu ? '<span style="font-size:11px;color:#888;">'+p.xuat_xu+'</span>' : '';
      html += `<a class="suggestion-item" href="/rice4u/chitietsanpham.php?id=${encodeURIComponent(p.id_sp)}">
        <img src="${hinh}" onerror="this.src='/rice4u/asset/images/default.jpg'" alt="">
        <span class="s-name">${ten} ${xu}</span>
        <span class="s-price">${gia}</span>
      </a>`;
    });
    drop.innerHTML = html;
    drop.classList.add('show');
  }

  function hideDrop() {
    drop.classList.remove('show');
    drop.innerHTML = '';
  }

  // Click ra ngoài → ẩn gợi ý
  document.addEventListener('click', function (e) {
    if (!box.contains(e.target)) hideDrop();
  });
})();
</script>