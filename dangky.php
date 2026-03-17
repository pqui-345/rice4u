<?php
require_once __DIR__ . '/includes/db.php';

$page_title = 'Đăng Ký – Rice4U';
$active_nav = '';
$thong_bao  = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ten_dang_nhap     = $_POST['ten_dang_nhap'];
    $mat_khau_goc      = $_POST['mat_khau'];
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];
    $ten_kh            = $_POST['ten_kh'];
    $sdt               = $_POST['sdt'];
    $email             = $_POST['email'];
    $dia_chi           = $_POST['dia_chi'];

    if ($mat_khau_goc !== $xac_nhan_mat_khau) {
        $thong_bao = "Lỗi: Mật khẩu và xác nhận mật khẩu không khớp!";
    } else {
        $mat_khau = password_hash($mat_khau_goc, PASSWORD_DEFAULT);
        try {
            $pdo->beginTransaction();

            $stmt1 = $pdo->prepare("INSERT INTO TAI_KHOAN (ten_dang_nhap, mat_khau, vai_tro) VALUES (?, ?, 'khachhang')");
            $stmt1->execute([$ten_dang_nhap, $mat_khau]);
            $ma_tk_moi = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("INSERT INTO KHACHHANG (ho_ten, so_dien_thoai, dia_chi, email, ma_tk) VALUES (?, ?, ?, ?, ?)");
            $stmt2->execute([$ten_kh, $sdt, $dia_chi, $email, $ma_tk_moi]);

            $pdo->commit();
            $thong_bao = "Đăng ký thành công! Hãy đăng nhập.";
        } catch (Exception $e) {
            $pdo->rollBack();
$thong_bao = "Lỗi chi tiết: " . $e->getMessage();        }
    }
}

include 'includes/header.php';
?>

<style>
* { font-family: 'Be Vietnam Pro', Arial, Helvetica, sans-serif; }

main {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3em 1em;
    overflow: hidden;
    min-height: calc(100vh - 72px - 120px);
}

main::before {
    content: "";
    position: absolute;
    top: -20px; left: -20px; right: -20px; bottom: -20px;
    background: url(/rice4u/asset/images/bgr.jpg) no-repeat center center;
    background-size: cover;
    filter: blur(6px);
    z-index: -2;
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: -1;
}

.reg-container {
    position: relative;
    z-index: 1;
    background-color: #ffffff;
    width: 100%;
    max-width: 580px;
    padding: 2.2em 3em;
    border-radius: 1em;
    box-shadow: 0 1.5em 4em rgba(0, 0, 0, 0.2);
}

.reg-container h2 {
    text-align: center;
    color: var(--green-dark);
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    margin-top: 0;
    margin-bottom: 0.2em;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 1.5em;
    font-size: 0.9rem;
}

.form-row {
    display: flex;
    gap: 1em;
}
.form-row .form-group { flex: 1; }

.form-group {
    margin-bottom: 1em;
    position: relative;
}

.form-group input {
    width: 100%;
    font-family: 'Be Vietnam Pro', sans-serif;
    box-sizing: border-box;
    transition: all 0.25s ease;
    font-size: 0.92rem;
    padding: 0.85em 2.8em 0.85em 1.1em;
    border: 1.5px solid #e0e0e0;
    border-radius: 0.8em;
    background: var(--gray-light);
    outline: none;
}
.form-group input.no-icon { padding-right: 1.1em; }

.form-group input:focus {
    border-color: var(--green-mid);
    box-shadow: 0 0 0 3px rgba(81,154,102,0.13);
    background: white;
}

.eye-icon {
    position: absolute;
    top: 50%; right: 1em;
    transform: translateY(-50%);
    cursor: pointer;
    color: #aaa;
    display: flex;
    align-items: center;
    transition: color 0.2s;
}
.eye-icon:hover { color: var(--green-dark); }
.eye-icon svg { width: 1.2em; height: 1.2em; }

.btn-submit {
    width: 100%;
    background-color: var(--green-dark);
    color: white;
    border: none;
    font-family: 'Be Vietnam Pro', sans-serif;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 0.95rem;
    padding: 0.95em;
    border-radius: 100px;
    margin-top: 0.5em;
    letter-spacing: 0.03em;
    box-shadow: 0 6px 20px rgba(35,114,39,0.25);
}
.btn-submit:hover {
    background-color: #1a5c1e;
    transform: translateY(-1px);
    box-shadow: 0 10px 26px rgba(35,114,39,0.32);
}

.footer-link {
    text-align: center;
    font-size: 0.88rem;
    margin-top: 1.4em;
    color: #666;
}
.footer-link a {
    color: var(--green-mid);
    text-decoration: none;
    font-weight: 700;
}
.footer-link a:hover { text-decoration: underline; }

/* Thông báo */
.thong-bao {
    text-align: center;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 10px 14px;
    border-radius: 10px;
    margin-bottom: 1em;
}
.thong-bao.error   { color: #c0392b; background: #ffebee; border: 1px solid #ffcdd2; }
.thong-bao.success { color: #1b5e20; background: #e8f5e9; border: 1px solid #c8e6c9; }

@media (max-width: 600px) {
    .reg-container { padding: 1.8em 1.4em; }
    .form-row { flex-direction: column; gap: 0; }
}
</style>

<main>
    <div class="overlay"></div>
    <div class="reg-container">
        <h2>Tạo Tài Khoản</h2>
        <p class="subtitle">Trở thành thành viên của Cửa Hàng Gạo Rice4U 🌾</p>

        <?php if (!empty($thong_bao)):
            $is_success = str_contains($thong_bao, 'thành công');
        ?>
            <div class="thong-bao <?= $is_success ? 'success' : 'error' ?>">
                <?= htmlspecialchars($thong_bao) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="dangky.php">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="ten_dang_nhap" class="no-icon"
                           placeholder="Tên đăng nhập" required
                           value="<?= htmlspecialchars($_POST['ten_dang_nhap'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="ten_kh" class="no-icon"
                           placeholder="Họ và tên" required
                           value="<?= htmlspecialchars($_POST['ten_kh'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="password" id="mat-khau-input" name="mat_khau"
                           placeholder="Mật khẩu" required>
                    <span class="eye-icon" onclick="togglePassword('mat-khau-input', 'eye-svg-1')">
                        <svg id="eye-svg-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </span>
                </div>
                <div class="form-group">
                    <input type="password" id="xac-nhan-input" name="xac_nhan_mat_khau"
                           placeholder="Xác nhận mật khẩu" required>
                    <span class="eye-icon" onclick="togglePassword('xac-nhan-input', 'eye-svg-2')">
                        <svg id="eye-svg-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="sdt" class="no-icon"
                           placeholder="Số điện thoại" required
                           value="<?= htmlspecialchars($_POST['sdt'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="no-icon"
                           placeholder="Email liên hệ"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <input type="text" name="dia_chi" class="no-icon"
                       placeholder="Địa chỉ giao hàng"
                       value="<?= htmlspecialchars($_POST['dia_chi'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-submit">Đăng ký tài khoản</button>
        </form>

        <div class="footer-link">
            Đã có tài khoản? <a href="dangnhap.php">Đăng nhập ngay</a>
        </div>
    </div>
</main>

<script>
function togglePassword(inputId, iconId) {
    var input   = document.getElementById(inputId);
    var iconSvg = document.getElementById(iconId);
    var eyeOpen   = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    var eyeClosed = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    if (input.type === "password") {
        input.type = "text";
        iconSvg.innerHTML = eyeOpen;
    } else {
        input.type = "password";
        iconSvg.innerHTML = eyeClosed;
    }
}
</script>

<?php include 'includes/footer.php'; ?>