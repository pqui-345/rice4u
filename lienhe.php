<?php
// ============================================================
// TRANG LIÊN HỆ - Rice4U
// ============================================================
session_start();

$page_title = 'Liên Hệ – Rice4U';
$active_nav = 'lienhe';

// Xử lý form gửi (thay thế gui_lienhe.php riêng lẻ, gộp vào đây)
$thong_bao   = '';
$loai_tb     = ''; // 'success' | 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $name    = trim($_POST['name']    ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $email   = trim($_POST['email']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($phone) || empty($email) || empty($message)) {
        $thong_bao = 'Vui lòng nhập đầy đủ thông tin!';
        $loai_tb   = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $thong_bao = 'Email không hợp lệ. Vui lòng nhập lại!';
        $loai_tb   = 'error';
    } else {
        // TODO: lưu DB hoặc gửi mail ở đây
        $thong_bao = "Cảm ơn $name! Yêu cầu đã gửi thành công. Rice4U sẽ liên hệ sớm nhất.";
        $loai_tb   = 'success';
        // Xóa dữ liệu cũ sau khi gửi thành công
        $name = $phone = $email = $message = '';
    }
}

// Include header — mở <!DOCTYPE>, <html>, <head>, <body>, <header>
include 'includes/header.php';
?>

<!-- CSS riêng trang liên hệ -->
<style>
/* ══════════════════════════════════════
   TRANG LIÊN HỆ
══════════════════════════════════════ */

/* ── HERO ── */
.contact-hero {
    background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-mid) 100%);
    padding: 60px 5% 48px;
    text-align: center;
    color: white;
}
.contact-hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    margin-bottom: 10px;
}
.contact-hero p {
    opacity: 0.88;
    font-weight: 300;
    font-size: 1rem;
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.7;
}

/* ── LAYOUT CHÍNH ── */
.contact-layout {
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 48px;
    max-width: 1100px;
    margin: 0 auto;
    padding: 64px 5%;
    align-items: start;
}

/* ── THÔNG TIN CỬA HÀNG ── */
.store-info {
    background: var(--gray-light, #F5F5F5);
    border-radius: 24px;
    padding: 36px 32px;
    position: sticky;
    top: 90px;
}
.store-info h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: var(--green-dark, #237227);
    margin-bottom: 8px;
}
.store-info .tagline {
    color: var(--text-mid, #4a5e4a);
    font-size: 0.88rem;
    font-weight: 300;
    line-height: 1.7;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 1.5px solid rgba(35,114,39,0.12);
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 18px;
}
.info-icon {
    width: 40px; height: 40px;
    background: white;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: var(--green-dark, #237227);
    font-size: 0.95rem;
    flex-shrink: 0;
    box-shadow: 0 2px 10px rgba(35,114,39,0.10);
}
.info-text strong {
    display: block;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-dark, #1a2e1a);
    margin-bottom: 2px;
}
.info-text span {
    font-size: 0.88rem;
    color: var(--text-mid, #4a5e4a);
    font-weight: 400;
    line-height: 1.5;
}

/* ── FORM ── */
.contact-form-wrap {
    background: white;
    border-radius: 24px;
    padding: 40px 40px 36px;
    box-shadow: 0 4px 32px rgba(35,114,39,0.08);
}
.contact-form-wrap h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    color: var(--green-dark, #237227);
    margin-bottom: 6px;
}
.contact-form-wrap .subtitle {
    font-size: 0.88rem;
    color: var(--text-mid, #4a5e4a);
    font-weight: 300;
    margin-bottom: 28px;
}

/* Thông báo */
.alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 18px;
    border-radius: 12px;
    font-size: 0.88rem;
    font-weight: 500;
    margin-bottom: 22px;
}
.alert.success {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #c8e6c9;
}
.alert.error {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
}

/* Form grid */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}
.form-group label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-dark, #1a2e1a);
    letter-spacing: 0.02em;
}
.form-group label span { color: #ee4d2d; margin-left: 2px; }

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #e0e0e0;
    border-radius: 12px;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 0.9rem;
    color: var(--text-dark, #1a2e1a);
    background: var(--gray-light, #F5F5F5);
    outline: none;
    transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
    resize: none;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--green-mid, #519A66);
    box-shadow: 0 0 0 3px rgba(81,154,102,0.12);
    background: white;
}
.form-group textarea { min-height: 130px; }

.btn-send {
    width: 100%;
    background: var(--green-dark, #237227);
    color: white;
    border: none;
    padding: 15px 24px;
    border-radius: 100px;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    cursor: pointer;
    transition: all 0.28s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 6px 20px rgba(35,114,39,0.28);
    margin-top: 8px;
}
.btn-send:hover {
    background: #1a5c1e;
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(35,114,39,0.34);
}

/* ── RESPONSIVE ── */
@media (max-width: 860px) {
    .contact-layout {
        grid-template-columns: 1fr;
        padding: 40px 5%;
        gap: 28px;
    }
    .store-info { position: static; }
    .contact-form-wrap { padding: 28px 22px; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<!-- HERO -->
<section class="contact-hero">
    <h1>📬 Liên Hệ Với Chúng Tôi</h1>
    <p>Rice4U luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy để lại lời nhắn, chúng tôi sẽ phản hồi sớm nhất!</p>
</section>

<!-- NỘI DUNG CHÍNH -->
<div class="contact-layout">

    <!-- THÔNG TIN CỬA HÀNG -->
    <aside class="store-info">
        <h2>Cửa Hàng Gạo Rice4U</h2>
        <p class="tagline">Chuyên cung cấp các loại gạo đặc sản vùng miền, đảm bảo chất lượng và an toàn thực phẩm cho mọi gia đình Việt.</p>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="info-text">
                <strong>Địa chỉ</strong>
                <span>Đường 3/2, phường Ninh Kiều, TP. Cần Thơ</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-phone"></i></div>
            <div class="info-text">
                <strong>Điện thoại</strong>
                <span>(000) 0000 0000</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-envelope"></i></div>
            <div class="info-text">
                <strong>Email</strong>
                <span>CT299@ctu.edu.vn</span>
            </div>
        </div>

        <div class="info-item">
            <div class="info-icon"><i class="fas fa-clock"></i></div>
            <div class="info-text">
                <strong>Giờ làm việc</strong>
                <span>Thứ 2 – Chủ nhật<br>9:00 – 19:00</span>
            </div>
        </div>
    </aside>

    <!-- FORM GỬI LIÊN HỆ -->
    <div class="contact-form-wrap">
        <h2>Gửi Lời Nhắn</h2>
        <p class="subtitle">Điền thông tin bên dưới, chúng tôi sẽ liên hệ lại trong vòng 24 giờ.</p>

        <!-- Thông báo kết quả -->
        <?php if (!empty($thong_bao)): ?>
            <div class="alert <?= $loai_tb ?>">
                <i class="fas <?= $loai_tb === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($thong_bao) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Họ và tên <span>*</span></label>
                    <input type="text" id="name" name="name"
                           placeholder="Nguyễn Văn A"
                           value="<?= htmlspecialchars($name ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại <span>*</span></label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="0909 000 000"
                           value="<?= htmlspecialchars($phone ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email <span>*</span></label>
                <input type="email" id="email" name="email"
                       placeholder="example@email.com"
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="message">Nội dung <span>*</span></label>
                <textarea id="message" name="message"
                          placeholder="Bạn cần tư vấn loại gạo nào? Rice4U rất vui được hỗ trợ..."
                          required><?= htmlspecialchars($message ?? '') ?></textarea>
            </div>

            <button type="submit" name="submit" class="btn-send">
                <i class="fas fa-paper-plane"></i>
                Gửi Yêu Cầu
            </button>

        </form>
    </div>

</div>

<?php include 'includes/footer.php'; ?>