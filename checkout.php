<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — Minsoft Solution</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', sans-serif;
  background: #ffffff;
  color: #111;
  min-height: 100vh;
  font-size: 15px;
  line-height: 1.6;
}

/* ── Topbar ── */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 62px;
  background: #020617;
  border-bottom: 1px solid #e0e0e0;
  position: sticky;
  top: 0;
  z-index: 10;
}
.logo {
  font-family: 'Segoe UI', sans-serif;
  font-weight: 800;
  font-size: 1.25rem;
  color: #1d6fd8;
  text-decoration: none;
  letter-spacing: -0.5px;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #777;
}
.breadcrumb span.active { color: #111; font-weight: 500; }

.profile-btn {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: #e8f0fb;
  border: 1.5px solid #c5d8f5;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}
.profile-btn:hover { background: #d0e4f8; border-color: #1d6fd8; }
.profile-btn svg { width: 18px; height: 18px; fill: #1d6fd8; }

/* ── Steps ── */
.steps-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.step {
  display: flex; align-items: center; gap: 10px;
  font-size: 0.82rem; font-weight: 500;
  color: #aaa;
}
.step.active { color: #1d6fd8; }
.step.done   { color: #2a7a2a; }
.step-num {
  width: 26px; height: 26px;
  border-radius: 50%;
  border: 1.5px solid currentColor;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
}
.step.active .step-num { background: #1d6fd8; color: #fff; border-color: #1d6fd8; }
.step.done   .step-num { background: #2a7a2a; color: #fff; border-color: #2a7a2a; }
.step-line { width: 60px; height: 1px; background: #e0e0e0; margin: 0 12px; }

/* ── Page Layout ── */
.page {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 24px;
  max-width: 1000px;
  margin: 0 auto;
  padding: 0 24px 80px;
  align-items: start;
}

/* ── Panels ── */
.panel {
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 14px;
  padding: 28px;
  margin-bottom: 20px;
  animation: fadeUp 0.4s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
.panel-title {
  font-family: 'Segoe UI', sans-serif;
  font-size: 1.05rem; font-weight: 700;
  color: #111;
  margin-bottom: 22px;
  display: flex; align-items: center; gap: 10px;
}
.panel-title-icon {
  width: 30px; height: 30px;
  border-radius: 8px;
  background: #e8f0fb;
  border: 1px solid #c5d8f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem; color: #1d6fd8;
}

/* ── Form ── */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
.field { margin-bottom: 14px; }
.field:last-child { margin-bottom: 0; }
.field label {
  display: block;
  font-size: 0.75rem; font-weight: 500;
  color: #555; margin-bottom: 6px;
}
.field input, .field select {
  width: 100%;
  background: #ffffff;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 9px 13px;
  color: #111;
  font-family: 'Segoe UI', sans-serif;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  appearance: none;
}
.field input:focus, .field select:focus {
  border-color: #1d6fd8;
  box-shadow: 0 0 0 3px rgba(29,111,216,0.1);
}
.field input::placeholder { color: #bbb; }

/* ── Order Summary ── */
.order-items-scroll {
  max-height: 200px;
  overflow-y: auto;
  padding-right: 4px;
  margin-bottom: 4px;
}
.order-items-scroll::-webkit-scrollbar { width: 4px; }
.order-items-scroll::-webkit-scrollbar-track { background: #f0f0f0; border-radius: 4px; }
.order-items-scroll::-webkit-scrollbar-thumb { background: #c5d8f5; border-radius: 4px; }

.order-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  margin-bottom: 8px;
  transition: border-color 0.2s;
}
.order-item:last-child { margin-bottom: 0; }
.order-item:hover { border-color: #c5d8f5; }

.item-thumb {
  width: 42px; height: 42px;
  border-radius: 9px;
  background: #eef3fc;
  border: 1px solid #d0e2f8;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.2rem; flex-shrink: 0;
}
.item-info { flex: 1; }
.item-info p { font-size: 0.87rem; font-weight: 500; color: #111; margin-bottom: 2px; }
.item-info small { font-size: 0.74rem; color: #777; }
.item-price {
  font-size: 0.9rem; font-weight: 600;
  color: #111; text-align: right; white-space: nowrap;
}
.item-qty { font-size: 0.68rem; color: #999; margin-top: 1px; }

.remove-btn {
  background: transparent;
  border: 1px solid rgba(220,60,60,0.25);
  border-radius: 5px;
  color: #c0392b;
  cursor: pointer;
  padding: 4px 7px;
  font-size: 0.68rem;
  margin-left: 4px;
  transition: background 0.15s;
  white-space: nowrap;
}
.remove-btn:hover { background: rgba(220,60,60,0.08); }

/* ── Divider & Line Items ── */
.divider { border: none; border-top: 1px solid #e5e5e5; margin: 14px 0; }

.line-item {
  display: flex; justify-content: space-between; align-items: center;
  font-size: 0.85rem; padding: 4px 0; color: #666;
}
.line-item span:last-child { color: #111; }
.line-item.free span:last-child { color: #2a7a2a; font-weight: 500; }
.line-item.total { font-size: 1.05rem; padding: 5px 0; }
.line-item.total span:first-child { color: #111; font-weight: 600; }
.line-item.total span:last-child  { color: #1d6fd8; font-size: 1.2rem; font-weight: 700; }

/* ── Place Order Button ── */
.order-btn {
  width: 100%;
  background: #1d6fd8;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 14px;
  font-family: 'Segoe UI', sans-serif;
  font-size: 1rem; font-weight: 700;
  cursor: pointer;
  margin-top: 16px;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  transition: background 0.2s, transform 0.15s;
  letter-spacing: 0.3px;
}
.order-btn:hover  { background: #155bb5; transform: translateY(-1px); }
.order-btn:active { transform: translateY(0); }
.order-btn:disabled { background: #aaa; opacity: 0.5; cursor: not-allowed; transform: none; }

/* ── Modals shared ── */
.modal-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 1000;
  align-items: center; justify-content: center;
  padding: 24px;
}
.modal-box {
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 28px;
  width: 100%; max-width: 400px;
  animation: fadeUp 0.25s ease both;
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 20px;
}
.modal-title {
  font-size: 1rem; font-weight: 700; color: #111;
  display: flex; align-items: center; gap: 8px;
}
.modal-title-icon {
  width: 30px; height: 30px; border-radius: 8px;
  background: #e8f0fb; border: 1px solid #c5d8f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.78rem;
}
.modal-close-btn {
  width: 28px; height: 28px; border-radius: 6px;
  border: 1px solid #ddd; background: transparent;
  color: #888; cursor: pointer; font-size: 13px;
  transition: background 0.15s;
}
.modal-close-btn:hover { background: #f0f0f0; }

.modal-item {
  background: #f8f8f8; border-radius: 8px;
  border: 1px solid #eee; padding: 10px;
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 8px;
}
.modal-item-thumb {
  width: 40px; height: 40px; border-radius: 8px;
  background: #eef3fc; border: 1px solid #d0e2f8;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.05rem; flex-shrink: 0;
}

.btn-confirm {
  width: 100%; padding: 13px;
  background: #1d6fd8; border: none; border-radius: 8px;
  color: #fff; font-size: 0.95rem; font-weight: 700;
  cursor: pointer; margin-top: 16px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: background 0.2s;
}
.btn-confirm:hover { background: #155bb5; }
.btn-confirm:disabled { background: #1555a0; opacity: 0.8; cursor: not-allowed; }

.btn-secondary {
  width: 100%; padding: 11px;
  background: transparent;
  border: 1px solid #ddd; border-radius: 8px;
  color: #777; font-size: 0.85rem; cursor: pointer; margin-top: 8px;
  transition: background 0.15s;
}
.btn-secondary:hover { background: #f5f5f5; }

/* ── Remove-confirm modal ── */
.remove-modal-icon {
  width: 52px; height: 52px; border-radius: 50%;
  background: #fff0f0; border: 1.5px solid rgba(220,60,60,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; margin: 0 auto 16px;
}
.btn-remove-confirm {
  width: 100%; padding: 12px;
  background: #c0392b; border: none; border-radius: 8px;
  color: #fff; font-size: 0.9rem; font-weight: 700;
  cursor: pointer; margin-top: 16px;
  transition: background 0.2s;
}
.btn-remove-confirm:hover { background: #a93226; }

/* ── Responsive ── */
@media (max-width: 860px) {
  .page { grid-template-columns: 1fr; padding: 0 20px 60px; }
  .breadcrumb { display: none; }
  .form-row.triple { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 500px) {
  .form-row, .form-row.triple { grid-template-columns: 1fr; }
  .step-line { width: 30px; }
}
</style>
</head>
<body>

<!-- ── Topbar ── -->
<header class="topbar">
  <a href="#" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>
  <div class="breadcrumb">
    <span>Cart</span>
    <span>›</span>
    <span class="active">Checkout</span>
    <span>›</span>
    <span>Confirmation</span>
  </div>
  <div class="profile-btn" title="My Account">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
    </svg>
  </div>
</header>

<!-- ── Steps ── -->
<div class="steps-bar">
  <div class="step done">
    <div class="step-num">✓</div> Cart
  </div>
  <div class="step-line"></div>
  <div class="step active">
    <div class="step-num">2</div> Checkout
  </div>
  <div class="step-line"></div>
  <div class="step">
    <div class="step-num">3</div> Confirmation
  </div>
</div>

<!-- ── Page ── -->
<div class="page">

  <!-- LEFT COLUMN -->
  <div>

    <!-- Contact Information -->
    <div class="panel" style="animation-delay:0.05s">
      <div class="panel-title">
        <div class="panel-title-icon">👤</div>
        Contact Information
      </div>
      <div class="form-row">
        <div class="field">
          <label>First Name</label>
          <input type="text" name="first_name" placeholder="John">
        </div>
        <div class="field">
          <label>Last Name</label>
          <input type="text" name="last_name" placeholder="Doe">
        </div>
      </div>
      <div class="field">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="john@email.com">
      </div>
      <div class="field" style="margin-bottom:0">
        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="+60 12-345 6789">
      </div>
    </div>

    <!-- Delivery Address -->
    <div class="panel" style="animation-delay:0.15s">
      <div class="panel-title">
        <div class="panel-title-icon">📍</div>
        Delivery Address
      </div>
      <div class="field">
        <label>Street Address</label>
        <input type="text" name="address1" placeholder="No. 12, Jalan Setia...">
      </div>
      <div class="field">
        <label>Address Line 2 <span style="color:#aaa;font-weight:300;font-size:0.7rem">(optional)</span></label>
        <input type="text" name="address2" placeholder="Apartment, suite, unit...">
      </div>
      <div class="form-row triple">
        <div class="field" style="margin-bottom:0">
          <label>City</label>
          <input type="text" name="city" placeholder="Johor Bahru">
        </div>
        <div class="field" style="margin-bottom:0">
          <label>State</label>
          <select name="state">
            <option value="">Select</option>
            <option>Johor</option>
            <option>Selangor</option>
            <option>Kuala Lumpur</option>
            <option>Penang</option>
            <option>Sabah</option>
            <option>Sarawak</option>
            <option>Melaka</option>
            <option>Sembilan</option>
            <option>Kedah</option>
          </select>
        </div>
        <div class="field" style="margin-bottom:0">
          <label>Postcode</label>
          <input type="text" name="postcode" placeholder="80000" maxlength="5">
        </div>
      </div>
    </div>

  </div><!-- end left column -->

  <!-- RIGHT COLUMN — Order Summary -->
  <div>
    <div class="panel" style="animation-delay:0.2s; position: sticky; top: 82px;">
      <div class="panel-title">
        <div class="panel-title-icon">🛍️</div>
        Order Summary
      </div>

      <!-- Scrollable items list -->
      <div class="order-items-scroll">
        <div class="order-item">
          <div class="item-thumb">💻</div>
          <div class="item-info">
            <p>Dell Inspiron 15</p>
            <small>Intel i5 · 16GB RAM · 512GB SSD</small>
          </div>
          <div class="item-price">RM 3,500<div class="item-qty">×1</div></div>
          <button class="remove-btn" onclick="askRemove(this, 3500)">✕ Remove</button>
        </div>
        <div class="order-item">
          <div class="item-thumb">🖱️</div>
          <div class="item-info">
            <p>Wireless Mouse Pro</p>
            <small>Bluetooth 5.0 · Ergonomic</small>
          </div>
          <div class="item-price">RM 50<div class="item-qty">×1</div></div>
          <button class="remove-btn" onclick="askRemove(this, 50)">✕ Remove</button>
        </div>
        <div class="order-item">
          <div class="item-thumb">⌨️</div>
          <div class="item-info">
            <p>Mechanical Keyboard</p>
            <small>TKL · RGB · Blue Switch</small>
          </div>
          <div class="item-price">RM 280<div class="item-qty">×1</div></div>
          <button class="remove-btn" onclick="askRemove(this, 280)">✕ Remove</button>
        </div>
        <div class="order-item">
          <div class="item-thumb">🖥️</div>
          <div class="item-info">
            <p>27" IPS Monitor</p>
            <small>1440p · 144Hz · HDR</small>
          </div>
          <div class="item-price">RM 1,200<div class="item-qty">×1</div></div>
          <button class="remove-btn" onclick="askRemove(this, 1200)">✕ Remove</button>
        </div>
      </div>

      <hr class="divider">

      <div class="line-item">
        <span id="subtotal-label">Subtotal (4 items)</span>
        <span id="subtotal-val">RM 5,030</span>
      </div>
      <div class="line-item free">
        <span>Shipping</span>
        <span>Free</span>
      </div>
      <div class="line-item">
        <span>Tax (6% SST)</span>
        <span id="tax-val">RM 302</span>
      </div>

      <hr class="divider">

      <div class="line-item total">
        <span>Total</span>
        <span id="total-val">RM 5,332</span>
      </div>

      <button class="order-btn" id="orderBtn" onclick="showConfirmModal()">
        🔒 Place Order &nbsp;→
      </button>
    </div>
  </div>

</div><!-- end .page -->


<!-- ══════════════════════════════════════════
     MODAL 1 — Order Confirmation
═══════════════════════════════════════════ -->
<div id="confirmOverlay" class="modal-overlay">
  <div class="modal-box">

    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon">🛍️</div>
        Confirm Your Order
      </div>
      <button class="modal-close-btn" onclick="closeConfirmModal()">✕</button>
    </div>

    <div id="modalItems"></div>

    <hr class="divider">

    <div class="line-item">
      <span id="modal-subtotal-label">Subtotal</span>
      <span id="modal-subtotal"></span>
    </div>
    <div class="line-item free">
      <span>Shipping</span>
      <span>Free</span>
    </div>
    <div class="line-item">
      <span>Tax (6% SST)</span>
      <span id="modal-tax"></span>
    </div>

    <hr class="divider">

    <div class="line-item total">
      <span>Total</span>
      <span id="modal-total"></span>
    </div>

    <button class="btn-confirm" id="confirmBtn" onclick="submitOrder(this)">
      🔒 Confirm &amp; Pay &nbsp;→
    </button>
    <button class="btn-secondary" onclick="closeConfirmModal()">Cancel</button>

  </div>
</div>


<!-- ══════════════════════════════════════════
     MODAL 2 — Remove Item Confirmation
═══════════════════════════════════════════ -->
<div id="removeOverlay" class="modal-overlay">
  <div class="modal-box" style="max-width:360px; text-align:center;">

    <div class="remove-modal-icon">🗑️</div>

    <h3 style="font-size:1rem; font-weight:700; color:#111; margin-bottom:10px;">Remove Item?</h3>
    <p style="font-size:0.85rem; color:#666; line-height:1.7;">
      Are you sure you want to remove<br>
      <strong id="remove-item-name" style="color:#111;"></strong>
      from your order?
    </p>

    <button class="btn-remove-confirm" onclick="confirmRemove()">Yes, Remove It</button>
    <button class="btn-secondary" onclick="closeRemoveModal()">Keep It</button>

  </div>
</div>


<script>
// ── State ─────────────────────────────────────────────────────────────────
let subtotal           = 5030;
let pendingRemoveBtn   = null;
let pendingRemovePrice = 0;

// ── Totals updater ────────────────────────────────────────────────────────
function updateTotals() {
  const tax   = Math.round(subtotal * 0.06);
  const total = subtotal + tax;
  const count = document.querySelectorAll('.order-item').length;

  document.getElementById('subtotal-label').textContent =
    'Subtotal (' + count + ' item' + (count !== 1 ? 's' : '') + ')';
  document.getElementById('subtotal-val').textContent = 'RM ' + subtotal.toLocaleString();
  document.getElementById('tax-val').textContent      = 'RM ' + tax.toLocaleString();
  document.getElementById('total-val').textContent    = 'RM ' + total.toLocaleString();

  if (subtotal === 0) {
    const btn = document.getElementById('orderBtn');
    btn.disabled = true;
    btn.innerHTML = '🚫 Cart is Empty';
  }
}

// ── Remove — step 1: show confirmation popup ──────────────────────────────
function askRemove(btn, price) {
  const item = btn.closest('.order-item');
  const name = item.querySelector('.item-info p').textContent;

  pendingRemoveBtn   = btn;
  pendingRemovePrice = price;

  document.getElementById('remove-item-name').textContent = name;
  document.getElementById('removeOverlay').style.display  = 'flex';
}

// ── Remove — step 2: user confirmed ──────────────────────────────────────
function confirmRemove() {
  closeRemoveModal();
  if (!pendingRemoveBtn) return;

  const item = pendingRemoveBtn.closest('.order-item');
  item.style.transition = 'opacity 0.25s, transform 0.25s';
  item.style.opacity    = '0';
  item.style.transform  = 'translateX(10px)';

  setTimeout(() => {
    item.remove();
    subtotal          -= pendingRemovePrice;
    pendingRemoveBtn   = null;
    pendingRemovePrice = 0;
    updateTotals();
  }, 270);
}

function closeRemoveModal() {
  document.getElementById('removeOverlay').style.display = 'none';
}

// ── Order Confirmation Modal ───────────────────────────────────────────────
function showConfirmModal() {
  const items = document.querySelectorAll('.order-item');
  let html = '';
  items.forEach(item => {
    const name  = item.querySelector('.item-info p').textContent;
    const desc  = item.querySelector('.item-info small').textContent;
    const icon  = item.querySelector('.item-thumb').textContent;
    const price = item.querySelector('.item-price').childNodes[0].textContent.trim();
    html += `
      <div class="modal-item">
        <div class="modal-item-thumb">${icon}</div>
        <div style="flex:1">
          <p style="font-size:0.85rem;font-weight:500;color:#111;margin:0 0 2px">${name}</p>
          <small style="font-size:0.72rem;color:#777">${desc}</small>
        </div>
        <div style="font-size:0.88rem;font-weight:600;color:#111">${price}</div>
      </div>`;
  });
  document.getElementById('modalItems').innerHTML = html;

  const tax   = Math.round(subtotal * 0.06);
  const count = items.length;
  document.getElementById('modal-subtotal-label').textContent =
    'Subtotal (' + count + ' item' + (count !== 1 ? 's' : '') + ')';
  document.getElementById('modal-subtotal').textContent = 'RM ' + subtotal.toLocaleString();
  document.getElementById('modal-tax').textContent      = 'RM ' + tax.toLocaleString();
  document.getElementById('modal-total').textContent    = 'RM ' + (subtotal + tax).toLocaleString();

  document.getElementById('confirmOverlay').style.display = 'flex';
}

function closeConfirmModal() {
  document.getElementById('confirmOverlay').style.display = 'none';
}

// ── Submit — redirect to paymentcheckout.php ──────────────────────────────
function submitOrder(btn) {
  btn.innerHTML        = '⏳ Processing…';
  btn.style.background = '#1555a0';
  btn.disabled         = true;
  setTimeout(() => {
    window.location.href = 'paymentcheckout.php';
  }, 1500);
}

// ── Close modals when clicking backdrop ───────────────────────────────────
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeConfirmModal();
});
document.getElementById('removeOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeRemoveModal();
});
</script>

</body>
</html>