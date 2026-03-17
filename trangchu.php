<?php
// ============================================================
// TRANG CHỦ¦ - rice4u
// ============================================================
$page_title = 'Rice4U “Tinh Hoa Gạo Việt';

require_once 'includes/db.php';

$products  = [];
$db_error  = '';

try {
    $stmt = $pdo->query("
        SELECT
            sp.id_sp, sp.ten_sp, sp.xuat_xu, sp.mo_ta_ngan,
            sp.gia_ban, sp.gia_goc, sp.phan_tram_giam,
            sp.noi_bat, sp.ban_chay, sp.hang_moi,
            lg.ten_loai,
            ha.duong_dan AS hinh_chinh
        FROM sanpham sp
        LEFT JOIN loaigao lg ON sp.id_loai = lg.id_loai
        LEFT JOIN hinhanh_sp ha ON sp.id_sp = ha.id_sp AND ha.la_anh_chinh = 1
        WHERE sp.trang_thai = 1 AND sp.noi_bat = 1
        ORDER BY sp.luot_ban DESC
        LIMIT 8
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}

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

    // Thử thêm các đuôi file khác nhau (giống sanpham.php)
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
        if (is_file(__DIR__ . '/' . $candidate)) {
            return '/rice4u/' . $candidate;
        }
    }

    return $default;
}

include 'includes/header.php';
?>

<?php if ($db_error): ?>
<div style="background:#fee;color:#c00;padding:12px 20px;font-size:0.85rem;font-family:monospace;">
    Lỗi kết nối CSDL: <?= htmlspecialchars($db_error) ?>
</div>
<?php endif; ?>

<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div class="hero-badge">
            <span></span>
            Gạo sạch · Giao tận nơi · Chất lượng hàng đầu
        </div>
        <h1>Tinh Hoa <em>Gạo Việt,</em><br>Giao Tận Nhà.</h1>
        <p>Khám phá hương vị truyền thống từ những cánh đồng lúa sạch chất lượng nhất — được chắt lọc từ thiên nhiên và tâm huyết của người nông dân Việt Nam.</p>
        <div class="hero-ctas">
            <a href="/rice4u/sanpham.php" class="btn-primary">Mua Ngay</a>
            <a href="/rice4u/sanpham.php" class="btn-ghost">Khám Phá Sản Phẩm</a>
        </div>
    </div>
    <div class="hero-stats">
        <div class="stat">
            <span class="stat-num">50+</span>
            <span class="stat-label">Loại Gạo</span>
        </div>
        <div class="stat">
            <span class="stat-num">12K+</span>
            <span class="stat-label">Khách Hàng</span>
        </div>
        <div class="stat">
            <span class="stat-num">100%</span>
            <span class="stat-label">Hữu Cơ</span>
        </div>
    </div>
</section>

<div class="trust-bar">
    <div class="trust-item">
        <div class="trust-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </div>
    Giao hàng toàn quốc
    </div>
    <div class="trust-item">
        <div class="trust-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9S3 16.97 3 12 7.03 3 12 3s9 4.03 9 9z"/></svg>
        </div>
        Kiểm định chất lượng
    </div>
    <div class="trust-item">
        <div class="trust-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M4 4h16v16H4z"/><path d="M8 8h8v8H8z"/></svg>
        </div>
        Đóng gói bảo quản
    </div>
    <div class="trust-item">
        <div class="trust-icon">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
        </div>
    Nguồn gốc minh bạch
    </div>
</div>

<section class="products-section">
    <div class="section-header reveal">
        <span class="section-tag">🌾 Chọn Lọc Từ Đồng Ruộng</span>
        <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
        <p class="section-desc">Mỗi hạt gạo là tinh túy của đất trời và tâm huyết của người nông dân Việt Nam.
        </p>
    </div>

    <div class="products-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
                <?php
                    $badge = '';
                    if ($p['ban_chay']) $badge = '<span class="product-badge" style="background:var(--green-mid)">Bán Chạy</span>';
                    if ($p['hang_moi']) $badge = '<span class="product-badge">Mới</span>';
                    if ($p['id_sp'] === 'SP001') $badge = '<span class="product-badge hot">🏆Sổ 1</span>';
                    $hinh = resolveImagePath($p['hinh_chinh'] ?? null);
                    $giam = ($p['phan_tram_giam'] > 0)
                        ? '<span class="discount-badge">-' . $p['phan_tram_giam'] . '%</span>'
                        : '';
                ?>
                <div class="product-card reveal">
                    <?= $badge ?>
                    <div class="product-img-wrap"
                         onclick="window.location='/rice4u/chitietsanpham.php?id=<?= $p['id_sp'] ?>'"
                         style="cursor:pointer;">
                        <?= $giam ?>
                        <img src="<?= $hinh ?>"
                             alt="<?= htmlspecialchars($p['ten_sp']) ?>"
                             onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
                    </div>
                    <div class="product-body product-body--compact">
                        <h3 class="product-name"
                            onclick="window.location='/rice4u/chitietsanpham.php?id=<?= $p['id_sp'] ?>'"
                            style="cursor:pointer;">
                            <?= htmlspecialchars($p['ten_sp']) ?>
                        </h3>
                        <div class="product-footer product-footer--centered">
                            <div class="product-price">
                                <?= formatGia($p['gia_ban']) ?>
                                <?php if ($p['gia_goc']): ?>
                                    <small style="text-decoration:line-through;color:#aaa;"><?= formatGia($p['gia_goc']) ?></small>
                                <?php else: ?>
                                    <small>/ 1kg</small>
                                <?php endif; ?>
                            </div>
                            <button class="btn-add btn-add--large" onclick="themVaoGio('<?= $p['id_sp'] ?>')">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                Thêm Vào Giỏ
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#888;">
                <p style="font-size:2rem;margin-bottom:12px;">>🌾</p>
                <p>Đang tải sản phẩm... Vui lòng kiểm tra kết nối cơ sở dữ liệu</p>
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align:center;margin-top:52px;">
        <a href="/rice4u/sanpham.php" class="btn-primary">Xem Tất Cả Sản Phẩm →</a>
    </div>
</section>

<div class="banner-section reveal">
    <img src="/rice4u/asset/images/banner.png" alt="Tinh Hoa Đất Việt – Rice4U">
</div>

<section style="background:var(--white);padding:8px 0;">
    <div class="story-section">
        <div class="story-img-stack reveal">
            <span class="story-leaf">🌿</span>
            <img src="/rice4u/asset/images/default1.jpg" alt="Cánh đồng lúa" class="story-img-main"
                 onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
            <img src="https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&q=80"
                 alt="Hạt gạo" class="story-img-accent"
                 onerror="this.onerror=null;this.src='/rice4u/asset/images/default.jpg'">
        </div>
        <div class="story-content reveal">
            <span class="section-tag">>🌾 Câu Chuyện Gạo Việt</span>
            <h2 class="section-title">Hành Trình Từ Cánh Đồng Đến Bàn Ăn</h2>
            <p>Mỗi hạt gạo Rice4U là kết tinh của những cánh đồng lúa ngát hương ở khắp vùng đồng bằng Việt Nam — nơi người nông dân cần mẫn gieo trồng từng mảng xanh với tình yêu đất và tâm huyết với nghề.</p>
            <p>Chúng tôi cam kết quy trình canh tác sạch, không sử dụng hóa chất độc hại, bảo vệ hệ sinh thái và sức khỏe của bạn cùng gia đình.</p>
            <div class="story-features">
                <div class="feature-chip"><span class="feature-chip-icon">>🌱</span><span class="feature-chip-text">Canh Tác Sạch</span></div>
                <div class="feature-chip"><span class="feature-chip-icon">🔬</span><span class="feature-chip-text">Kiểm Định Chuẩn</span></div>
                <div class="feature-chip"><span class="feature-chip-icon">📦</span><span class="feature-chip-text">Bảo Quản Tốt</span></div>
                <div class="feature-chip"><span class="feature-chip-icon">🚚</span><span class="feature-chip-text">Giao Hàng Nhanh</span></div>
            </div>
            <a href="#" class="btn-primary">Tìm Hiểu Thêm</a>
        </div>
    </div>
</section>

<script>
// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => e.target.classList.add('visible'), i * 80);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

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
<?php include 'includes/footer.php'; ?>