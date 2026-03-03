<?php
session_start();

$page_title = 'Giỏ hàng - Rice4U';
$active_nav = '';

$cartItems = $_SESSION['gio_hang'] ?? [];

function formatVnd($amount) {
    return number_format((float)$amount, 0, ',', '.') . '₫';
}

include 'includes/header.php';
?>

<style>
.cart-page { max-width: 1100px; margin: 40px auto; padding: 0 5%; }
.cart-page h1 { font-family: 'Playfair Display', serif; margin: 12px 0 24px; }
.cart-wrap { display: grid; grid-template-columns: 1fr 320px; gap: 24px; }
.cart-list { display: flex; flex-direction: column; gap: 12px; }
.cart-item { display: grid; grid-template-columns: 84px 1fr auto auto auto; gap: 14px; align-items: center; background: #fff; border-radius: 14px; padding: 12px; box-shadow: 0 3px 14px rgba(0,0,0,.06); }
.cart-item img { width: 84px; height: 84px; object-fit: cover; border-radius: 10px; }
.item-name { font-weight: 700; margin-bottom: 6px; }
.item-price { color: var(--green-dark); font-size: .92rem; }
.qty { display: flex; align-items: center; gap: 8px; }
.qty button { width: 30px; height: 30px; border: 1px solid #ddd; border-radius: 8px; background: #fff; cursor: pointer; }
.qty input { width: 58px; height: 32px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
.item-total { min-width: 120px; text-align: right; font-weight: 700; color: var(--green-dark); }
.remove-btn { border: none; background: #fff3f3; color: #d22; padding: 8px 10px; border-radius: 8px; cursor: pointer; }
.summary { background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 3px 14px rgba(0,0,0,.06); height: fit-content; }
.summary h2 { margin: 0 0 14px; font-size: 1.1rem; }
.summary-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
.summary-row.total { border: none; font-weight: 700; font-size: 1.05rem; padding-top: 12px; }
.empty { text-align: center; padding: 46px 20px; background:#fff; border-radius: 14px; }
.empty a { display:inline-block; margin-top: 10px; }
@media (max-width: 900px) {
  .cart-wrap { grid-template-columns: 1fr; }
  .cart-item { grid-template-columns: 70px 1fr; }
  .qty, .item-total, .remove-btn { justify-self: start; }
}
</style>

<main class="cart-page">
  <h1>Giỏ hàng của bạn</h1>

  <?php if (empty($cartItems)): ?>
    <div class="empty">
      <p>Giỏ hàng đang trống.</p>
      <a href="/rice4u/sanpham.php" class="btn-primary">Xem sản phẩm</a>
    </div>
  <?php else: ?>
    <div class="cart-wrap">
      <div class="cart-list" id="cartList">
        <?php foreach ($cartItems as $item): ?>
          <?php
            $id = (string)($item['id_sp'] ?? '');
            $name = (string)($item['ten_sp'] ?? 'Sản phẩm');
            $price = (float)($item['gia_ban'] ?? 0);
            $qty = max(1, (int)($item['so_luong'] ?? 1));
            $img = (string)($item['hinh'] ?? '/rice4u/.vscode/asset/images/default.jpg');
          ?>
          <div class="cart-item" data-id="<?= htmlspecialchars($id) ?>" data-price="<?= $price ?>">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" onerror="this.src='/rice4u/.vscode/asset/images/default.jpg'">
            <div>
              <div class="item-name"><?= htmlspecialchars($name) ?></div>
              <div class="item-price"><?= formatVnd($price) ?> / kg</div>
            </div>
            <div class="qty">
              <button type="button" onclick="changeQty(this, -1)">-</button>
              <input type="number" min="1" value="<?= $qty ?>" onchange="qtyChanged(this)">
              <button type="button" onclick="changeQty(this, 1)">+</button>
            </div>
            <div class="item-total"><?= formatVnd($price * $qty) ?></div>
            <button type="button" class="remove-btn" onclick="removeItem(this)">Xóa</button>
          </div>
        <?php endforeach; ?>
      </div>

      <aside class="summary">
        <h2>Tóm tắt đơn hàng</h2>
        <div class="summary-row">
          <span>Tạm tính</span>
          <span id="subtotal">0₫</span>
        </div>
        <div class="summary-row">
          <span>Vận chuyển</span>
          <span>Miễn phí</span>
        </div>
        <div class="summary-row total">
          <span>Tổng cộng</span>
          <span id="total">0₫</span>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</main>

<script>
function formatVnd(n) {
  return new Intl.NumberFormat('vi-VN').format(n) + '₫';
}

function setCartBadge(totalItems) {
  const badge = document.getElementById('cart-count');
  if (badge) badge.textContent = totalItems;
}

function recomputeSummary() {
  let total = 0;
  let count = 0;
  document.querySelectorAll('.cart-item').forEach(item => {
    const p = Number(item.dataset.price || 0);
    const q = Number(item.querySelector('input').value || 0);
    total += p * q;
    count += q;
    item.querySelector('.item-total').textContent = formatVnd(p * q);
  });
  const subtotal = document.getElementById('subtotal');
  const totalNode = document.getElementById('total');
  if (subtotal) subtotal.textContent = formatVnd(total);
  if (totalNode) totalNode.textContent = formatVnd(total);
  setCartBadge(count);
}

function syncCart(action, id, qty) {
  const body = new URLSearchParams({ action, id_sp: id });
  if (typeof qty !== 'undefined') body.set('so_luong', String(qty));

  return fetch('/rice4u/api/giohang.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body: body.toString()
  }).then(r => r.ok ? r.json() : null);
}

function changeQty(btn, delta) {
  const item = btn.closest('.cart-item');
  const input = item.querySelector('input');
  const next = Math.max(1, Number(input.value || 1) + delta);
  input.value = next;
  qtyChanged(input);
}

function qtyChanged(input) {
  const item = input.closest('.cart-item');
  const id = item.dataset.id;
  const qty = Math.max(1, Number(input.value || 1));
  input.value = qty;

  syncCart('update', id, qty)
    .then(data => {
      if (!data || !data.success) return;
      recomputeSummary();
      if (typeof data.total_items !== 'undefined') setCartBadge(data.total_items);
    })
    .catch(() => {});
}

function removeItem(btn) {
  const item = btn.closest('.cart-item');
  const id = item.dataset.id;

  syncCart('remove', id)
    .then(data => {
      if (!data || !data.success) return;
      item.remove();
      recomputeSummary();
      if (typeof data.total_items !== 'undefined') setCartBadge(data.total_items);
      if (!document.querySelector('.cart-item')) {
        window.location.reload();
      }
    })
    .catch(() => {});
}

recomputeSummary();
</script>

<?php include 'includes/footer.php'; ?>
