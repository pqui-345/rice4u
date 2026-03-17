<?php
/**
 * includes/admin_topbar.php
 * Topbar dùng chung cho tất cả trang admin con
 * Dùng: include 'includes/admin_topbar.php';
 * Biến cần khai báo trước: $active_admin (tên trang hiện tại)
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? 'Admin – Rice4U' ?></title>
  <link rel="icon" href="/rice4u/asset/images/favicon.ico" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Be+Vietnam+Pro:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
  <style>
    :root {
      --green-dark : #1b5e20;
      --green-mid  : #2e7d32;
      --green-lite : #519A66;
      --amber      : #f9a825;
      --gray-bg    : #f4f7f4;
      --white      : #fff;
      --topbar-h   : 64px;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Be Vietnam Pro', Arial, sans-serif;
      background: var(--gray-bg);
      padding-top: var(--topbar-h);
      color: #222;
    }

    /* ── TOPBAR ── */
    .admin-topbar {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: var(--topbar-h);
      background: #fff;
      border-bottom: 1.5px solid #e8f0e8;
      display: flex;
      align-items: center;
      gap: 0;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,.06);
    }

    /* Nút Back */
    .topbar-back {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 0 18px;
      height: 100%;
      color: #555;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      border-right: 1.5px solid #eee;
      transition: background .18s, color .18s;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .topbar-back:hover {
      background: #f0f7f0;
      color: var(--green-dark);
    }
    .topbar-back i {
      font-size: 13px;
      color: var(--green-mid);
    }

    /* Logo */
    .topbar-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 0 20px;
      height: 100%;
      text-decoration: none;
      border-right: 1.5px solid #eee;
      flex-shrink: 0;
    }
    .topbar-logo img {
      height: 38px;
      width: auto;
      display: block;
    }
    .topbar-logo span {
      font-family: 'Playfair Display', serif;
      font-size: 16px;
      color: var(--green-dark);
      font-weight: 700;
      letter-spacing: .3px;
    }

    /* Nav links */
    .topbar-nav {
      display: flex;
      align-items: center;
      gap: 2px;
      padding: 0 14px;
      flex: 1;
      height: 100%;
      overflow-x: auto;
      scrollbar-width: none;
    }
    .topbar-nav::-webkit-scrollbar { display: none; }

    .topbar-nav a {
      display: flex;
      align-items: center;
      gap: 7px;
      padding: 8px 14px;
      border-radius: 8px;
      color: #555;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      white-space: nowrap;
      transition: background .18s, color .18s;
      position: relative;
    }
    .topbar-nav a:hover {
      background: #f0f7f0;
      color: var(--green-dark);
    }
    .topbar-nav a.active {
      background: #e8f5e9;
      color: var(--green-dark);
      font-weight: 700;
    }
    .topbar-nav a.active::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 14px; right: 14px;
      height: 2.5px;
      background: var(--green-mid);
      border-radius: 2px;
    }
    .topbar-nav a i {
      font-size: 13px;
      color: var(--green-lite);
    }
    .topbar-nav a.active i { color: var(--green-dark); }

    /* Right area */
    .topbar-right {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 0 18px;
      flex-shrink: 0;
      border-left: 1.5px solid #eee;
    }
    .topbar-admin-name {
      font-size: 12px;
      color: #888;
      padding-right: 8px;
      border-right: 1px solid #eee;
    }
    .topbar-logout {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: 8px;
      background: #fce4ec;
      color: #c62828;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      border: 1px solid #f8bbd0;
      transition: background .18s, color .18s;
      white-space: nowrap;
    }
    .topbar-logout:hover {
      background: #e53935;
      border-color: #e53935;
      color: #fff;
    }
    .topbar-logout i { font-size: 12px; }

    /* ── PAGE WRAPPER ── */
    .admin-page {
      max-width: 1400px;
      margin: 0 auto;
      padding: 28px 24px;
    }

    /* ── PAGE HEADER ── */
    .admin-page-header {
      background: linear-gradient(90deg, var(--green-dark), var(--green-mid));
      color: #fff;
      padding: 18px 24px;
      border-radius: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
      box-shadow: 0 4px 14px rgba(0,0,0,.1);
      flex-wrap: wrap;
      gap: 12px;
    }
    .admin-page-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 700;
      margin: 0;
      color: #fff;
    }
    .admin-page-header p {
      font-size: 13px;
      opacity: .85;
      margin: 4px 0 0;
      color: rgba(255,255,255,.85);
    }
    .admin-page-header .header-actions {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    /* ── CARD ── */
    .admin-card {
      background: var(--white);
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,.07);
      overflow: hidden;
    }

    /* ── ALERT ── */
    .alert { padding: 13px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; margin-bottom: 16px; }
    .alert-success { background: #e8f5e9; color: #1b5e20; border: 1px solid #a5d6a7; }
    .alert-error   { background: #fce4ec; color: #b71c1c; border: 1px solid #ef9a9a; }
  </style>
</head>
<body>

<nav class="admin-topbar">

  <!-- Nút quay lại trang admin -->
  <a href="/rice4u/admin.php" class="topbar-back">
    <i class="fa-solid fa-arrow-left"></i>
    Trang Admin
  </a>

  <!-- Logo -->
  <a href="/rice4u/admin.php" class="topbar-logo">
    <img src="/rice4u/asset/images/logo.png" alt="Rice4U">
    <span>Admin</span>
  </a>

  <!-- Navigation -->
  <div class="topbar-nav">
    <a href="/rice4u/quanlydonhang.php"
       class="<?= ($active_admin ?? '') === 'donhang' ? 'active' : '' ?>">
      <i class="fa-solid fa-box"></i> Đơn hàng
    </a>
    <a href="/rice4u/quanly_sanpham.php"
       class="<?= ($active_admin ?? '') === 'sanpham' ? 'active' : '' ?>">
      <i class="fa-solid fa-seedling"></i> Sản phẩm
    </a>
    <a href="/rice4u/admin_account.php"
       class="<?= ($active_admin ?? '') === 'taikhoan' ? 'active' : '' ?>">
      <i class="fa-solid fa-users"></i> Tài khoản
    </a>
    <a href="/rice4u/dashboard.php"
       class="<?= ($active_admin ?? '') === 'dashboard' ? 'active' : '' ?>">
      <i class="fa-solid fa-chart-bar"></i> Dashboard
    </a>
  </div>

  <!-- Right -->
  <div class="topbar-right">
    <span class="topbar-admin-name">
      <i class="fa-solid fa-circle-user" style="color:rgba(255,255,255,.5);margin-right:4px;"></i>
      Admin
    </span>
    <a href="/rice4u/dangxuat.php" class="topbar-logout">
      <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
    </a>
  </div>

</nav>