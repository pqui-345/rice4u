<?php
/**
 * RICE4U — includes/header.php
 * Chỉ là fragment HTML — KHÔNG có DOCTYPE, html, head, body
 * Trang chủ gọi: include 'includes/header.php';
 * Sau đó trang chủ tự đóng </body></html> ở cuối
 *
 * Mỗi trang cần khai báo trước khi include:
 *   $page_title    = 'Tên trang – Rice4U';   // tiêu đề tab
 *   $active_nav    = 'trangchu';              // 'trangchu' | 'sanpham' | 'lienhe'
 */
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$cartCount = 0;
if (!empty($_SESSION['gio_hang']) && is_array($_SESSION['gio_hang'])) {
  $cartCount = array_sum(array_map(static function ($item) {
    return (int)($item['so_luong'] ?? 0);
  }, $_SESSION['gio_hang']));
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title ?? 'Rice4U – Tinh Hoa Gạo Việt') ?></title>

  <link rel="icon" href="/rice4u/.vscode/asset/images/favicon.ico" type="image/x-icon">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

  <!-- CSS chung toàn site -->
  <link rel="stylesheet" href="/rice4u/.vscode/asset/styles.css">
  <link rel="stylesheet" href="/rice4u/.vscode/asset/header.css">
  <link rel="stylesheet" href="/rice4u/.vscode/asset/footer.css">
</head>

<body>

<header>
  <div class="logo-navi">
    <a class="logo" href="/rice4u/trangchu.php">
      <img src="/rice4u/.vscode/asset/images/logo.png" alt="Rice4U Logo">
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
    <div class="search-box">
      <input type="text" placeholder="Nhập loại gạo bạn muốn tìm...">
      <button class="search" type="button" aria-label="Tìm kiếm">
        <i class="fa-solid fa-magnifying-glass"></i>
      </button>
    </div>
  </div>

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
            <a href="/rice4u/hoso.php">Thông tin cá nhân</a>
            <a href="/rice4u/dangxuat.php" class="logout-button-link">Đăng xuất</a>
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
            <a href="/rice4u/dangnhap.php">Đăng nhập</a>
            <a href="/rice4u/dangky.php">Đăng ký</a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</header>

