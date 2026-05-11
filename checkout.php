<?php
session_start();

$errors = [];
$v      = [];  // repopulate values after failed submit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $v['first_name'] = trim($_POST['first_name'] ?? '');
    $v['last_name']  = trim($_POST['last_name']  ?? '');
    $v['email']      = trim($_POST['email']      ?? '');
    $v['phone']      = trim($_POST['phone']      ?? '');
    $v['address1']   = trim($_POST['address1']   ?? '');
    $v['address2']   = trim($_POST['address2']   ?? '');
    $v['city']       = trim($_POST['city']       ?? '');
    $v['state']      = trim($_POST['state']      ?? '');
    $v['postcode']   = trim($_POST['postcode']   ?? '');

    // ── Validation rules ───────────────────────────────────────────────
    if ($v['first_name'] === '')
        $errors['first_name'] = 'First name is required.';
    elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $v['first_name']))
        $errors['first_name'] = 'First name may only contain letters.';

    if ($v['last_name'] === '')
        $errors['last_name'] = 'Last name is required.';
    elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $v['last_name']))
        $errors['last_name'] = 'Last name may only contain letters.';

    if ($v['email'] === '')
        $errors['email'] = 'Email address is required.';
    elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Please enter a valid email address (e.g. john@email.com).';

    if ($v['phone'] === '')
        $errors['phone'] = 'Phone number is required.';
    elseif (!preg_match('/^[+0-9\s\-()]{7,20}$/', $v['phone']))
        $errors['phone'] = 'Enter a valid phone number (e.g. +60 12-345 6789).';

    if ($v['address1'] === '')
        $errors['address1'] = 'Street address is required.';

    if ($v['city'] === '')
        $errors['city'] = 'City is required.';
    elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $v['city']))
        $errors['city'] = 'City name may only contain letters.';

    if ($v['state'] === '')
        $errors['state'] = 'Please select a state.';

    if ($v['postcode'] === '')
        $errors['postcode'] = 'Postcode is required.';
    elseif (!preg_match('/^\d{5}$/', $v['postcode']))
        $errors['postcode'] = 'Postcode must be exactly 5 digits.';

    // ── On success: save to session and redirect ───────────────────────
    if (empty($errors)) {
        $_SESSION['checkout'] = $v;
        header('Location: paymentcheckout.php');
        exit;
    }
}

// Helper: render an inline error message
function err($field, $errors) {
    if (isset($errors[$field])) {
        echo '<div class="field-error"><span class="field-error-icon">⚠</span>' . htmlspecialchars($errors[$field]) . '</div>';
    }
}

// Helper: add is-error class if field has error
function errClass($field, $errors) {
    return isset($errors[$field]) ? ' is-error' : '';
}

// Helper: safely output repopulated value
function val($field, $v) {
    return htmlspecialchars($v[$field] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout — Minsoft Solution</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

body { background-color: #f8f9fa; color: #222; min-height: 100vh; font-size: 15px; line-height: 1.6; }

/* ── Topbar ── */
.topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 30px; height: 62px;
  background: #fff; border-bottom: 1px solid #eee;
  position: sticky; top: 0; z-index: 10;
  box-shadow: 0 2px 5px rgba(0,0,0,0.04);
}
.logo { font-weight: 800; font-size: 1.25rem; color: #007bff; text-decoration: none; letter-spacing: -0.5px; }
.breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #aaa; }
.breadcrumb span.active { color: #222; font-weight: 600; }
.profile-btn {
  width: 36px; height: 36px; border-radius: 50%;
  background: #f0f6ff; border: 1.5px solid #cce0ff;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: 0.3s;
}
.profile-btn:hover { background: #dbeeff; border-color: #007bff; }
.profile-btn svg { width: 18px; height: 18px; fill: #007bff; }

/* ── Steps ── */
.steps-bar { display: flex; align-items: center; justify-content: center; padding: 24px 20px; }
.step { display: flex; align-items: center; gap: 10px; font-size: 0.82rem; font-weight: 600; color: #bbb; }
.step.active { color: #007bff; }
.step.done   { color: #28a745; }
.step-num {
  width: 28px; height: 28px; border-radius: 50%;
  border: 2px solid currentColor;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
}
.step.active .step-num { background: #007bff; color: #fff; border-color: #007bff; }
.step.done   .step-num { background: #28a745; color: #fff; border-color: #28a745; }
.step-line { width: 60px; height: 2px; background: #eee; margin: 0 10px; }

/* ── Page layout ── */
.page {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 460px;
  gap: 28px;
  max-width: 1280px;
  margin: 0 auto;
  padding: 30px 40px 80px;
  align-items: start;
}

/* ── Panels ── */
.panel {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #eaeaea;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
  padding: 28px;
  margin-bottom: 20px;
  transition: box-shadow 0.3s;
}
.panel-title {
  font-size: 1rem; font-weight: 700; color: #111;
  margin-bottom: 22px; display: flex; align-items: center; gap: 10px;
  border-bottom: 1px solid #f0f0f0; padding-bottom: 14px;
}
.panel-title-icon {
  width: 30px; height: 30px; border-radius: 8px;
  background: #f0f6ff; border: 1px solid #cce0ff;
  display: flex; align-items: center; justify-content: center; font-size: 0.85rem;
}

/* ── Error banner ── */
.error-banner {
  display: flex; align-items: center; gap: 10px;
  background: #fff5f5; border: 1px solid #f5c6cb;
  border-radius: 8px; padding: 11px 14px; margin-bottom: 20px;
  font-size: 0.83rem; color: #dc3545;
}
.error-banner-icon { font-size: 1rem; flex-shrink: 0; }

/* ── Form ── */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-row.triple { grid-template-columns: 1fr 1fr 1fr; }
.field { margin-bottom: 14px; }
.field:last-child { margin-bottom: 0; }
.field label { display: block; font-size: 0.78rem; font-weight: 600; color: #555; margin-bottom: 6px; }
.field input, .field select {
  width: 100%; background: #fff; border: 1px solid #ddd;
  border-radius: 8px; padding: 10px 13px;
  color: #222; font-family: 'Segoe UI', sans-serif; font-size: 0.9rem;
  outline: none; transition: 0.3s; appearance: none;
}
.field input:focus, .field select:focus { border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
.field input::placeholder { color: #bbb; }
.field input.is-error, .field select.is-error { border-color: #dc3545; box-shadow: 0 0 0 3px rgba(220,53,69,0.1); }
.field-error { display: flex; align-items: center; gap: 5px; font-size: 0.73rem; color: #dc3545; margin-top: 5px; }
.field-error-icon { font-size: 0.7rem; flex-shrink: 0; }

/* ── Order Summary items ── */
.order-items-list { max-height: 260px; overflow-y: auto; padding-right: 4px; margin-bottom: 4px; }
.order-items-list::-webkit-scrollbar { width: 4px; }
.order-items-list::-webkit-scrollbar-track { background: #f8f9fa; border-radius: 4px; }
.order-items-list::-webkit-scrollbar-thumb { background: #cce0ff; border-radius: 4px; }

.order-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px; background: #fff;
  border: 1px solid #eaeaea; border-radius: 12px;
  margin-bottom: 8px; transition: 0.3s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.02);
}
.order-item:last-child { margin-bottom: 0; }
.order-item:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.06); border-color: #007bff; }
.item-thumb {
  width: 52px; height: 52px; border-radius: 8px;
  background: #f8f9fa; border: 1px solid #eee;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; flex-shrink: 0; overflow: hidden;
}
.item-thumb img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }
.item-info { flex: 1; }
.item-info p { font-size: 0.87rem; font-weight: 600; color: #111; margin-bottom: 2px; }
.item-info small { font-size: 0.74rem; color: #aaa; }
.item-price { font-size: 0.9rem; font-weight: 800; color: #000; text-align: right; white-space: nowrap; }
.item-qty { font-size: 0.68rem; color: #aaa; margin-top: 1px; }
.remove-btn {
  background: transparent; border: 1px solid #f5c6cb;
  border-radius: 6px; color: #dc3545; cursor: pointer;
  padding: 4px 8px; font-size: 0.68rem; margin-left: 4px; transition: 0.3s; white-space: nowrap;
}
.remove-btn:hover { background: #fff5f5; border-color: #dc3545; }

/* ── Divider & totals ── */
.divider { border: none; border-top: 1px solid #eee; margin: 14px 0; }
.line-item { display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding: 5px 0; color: #888; }
.line-item span:last-child { color: #222; font-weight: 600; }
.line-item.free span:last-child { color: #28a745; }
.line-item.total { font-size: 1.05rem; padding: 6px 0; }
.line-item.total span:first-child { color: #111; font-weight: 700; }
.line-item.total span:last-child  { color: #007bff; font-size: 1.2rem; font-weight: 800; }

/* ── Place Order button ── */
.order-btn {
  width: 100%; background-color: #111; color: #fff; border: none;
  border-radius: 8px; padding: 12px;
  font-size: 0.95rem; font-weight: 600;
  cursor: pointer; margin-top: 16px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: 0.3s;
}
.order-btn:hover  { background-color: #007bff; }
.order-btn:disabled { background: #ccc; cursor: not-allowed; }

/* ── Modals ── */
.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.4); z-index: 1000;
  align-items: center; justify-content: center; padding: 24px;
}
.modal-box {
  background: #fff; border-radius: 12px; border: 1px solid #eaeaea;
  box-shadow: 0 12px 30px rgba(0,0,0,0.1);
  padding: 28px; width: 100%; max-width: 420px;
}
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.modal-title { font-size: 1rem; font-weight: 700; color: #111; display: flex; align-items: center; gap: 8px; }
.modal-title-icon { width: 30px; height: 30px; border-radius: 8px; background: #f0f6ff; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
.modal-close-btn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #eee; background: transparent; color: #888; cursor: pointer; font-size: 13px; transition: 0.2s; }
.modal-close-btn:hover { background: #f8f9fa; }
.modal-item { background: #f8f9fa; border-radius: 10px; border: 1px solid #eaeaea; padding: 10px; display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.modal-item-thumb { width: 40px; height: 40px; border-radius: 8px; background: #fff; border: 1px solid #eee; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }

.btn-confirm {
  width: 100%; padding: 12px; background: #111; border: none; border-radius: 8px;
  color: #fff; font-size: 0.92rem; font-weight: 600; cursor: pointer; margin-top: 16px;
  display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s;
}
.btn-confirm:hover { background: #007bff; }
.btn-confirm:disabled { background: #aaa; cursor: not-allowed; }
.btn-secondary {
  width: 100%; padding: 10px; background: transparent;
  border: 1px solid #eee; border-radius: 8px;
  color: #888; font-size: 0.85rem; cursor: pointer; margin-top: 8px; transition: 0.3s;
}
.btn-secondary:hover { background: #f8f9fa; border-color: #ddd; color: #555; }

/* ── Remove confirm modal ── */
.remove-modal-icon { width: 52px; height: 52px; border-radius: 50%; background: #fff5f5; border: 1.5px solid #f5c6cb; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin: 0 auto 16px; }
.btn-remove-confirm { width: 100%; padding: 12px; background: #dc3545; border: none; border-radius: 8px; color: #fff; font-size: 0.9rem; font-weight: 600; cursor: pointer; margin-top: 16px; transition: 0.3s; }
.btn-remove-confirm:hover { background: #c82333; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }

/* ── Responsive ── */
@media (max-width: 1100px) { .page { grid-template-columns: minmax(0,1fr) 380px; padding: 20px 24px 80px; gap: 20px; } }
@media (max-width: 860px)  { .page { grid-template-columns: 1fr; padding: 16px 16px 60px; } .breadcrumb { display: none; } .form-row.triple { grid-template-columns: 1fr 1fr; } }
@media (max-width: 500px)  { .form-row, .form-row.triple { grid-template-columns: 1fr; } .step-line { width: 28px; } }
</style>
</head>
<body>

<!-- ── Topbar ── -->
<header class="topbar">
  <a href="#" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>
  <div class="breadcrumb">
    <span>Cart</span><span>›</span>
    <span class="active">Checkout</span><span>›</span>
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
  <div class="step done"><div class="step-num">✓</div> Cart</div>
  <div class="step-line"></div>
  <div class="step active"><div class="step-num">2</div> Checkout</div>
  <div class="step-line"></div>
  <div class="step"><div class="step-num">3</div> Confirmation</div>
</div>

<!-- ── Page ── -->
<form method="POST" action="checkout.php" novalidate>
<div class="page">

  <!-- LEFT COLUMN -->
  <div>

    <?php if (!empty($errors)): ?>
    <div class="error-banner">
      <span class="error-banner-icon">⚠️</span>
      Please fix the highlighted fields below before continuing.
    </div>
    <?php endif; ?>

    <!-- Contact Information -->
    <div class="panel" style="animation-delay:0.05s">
      <div class="panel-title">
        <div class="panel-title-icon">👤</div>
        Contact Information
      </div>

      <div class="form-row">
        <div class="field">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name"
                 class="<?= errClass('first_name', $errors) ?>"
                 placeholder="John"
                 value="<?= val('first_name', $v) ?>">
          <?php err('first_name', $errors); ?>
        </div>
        <div class="field">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name"
                 class="<?= errClass('last_name', $errors) ?>"
                 placeholder="Doe"
                 value="<?= val('last_name', $v) ?>">
          <?php err('last_name', $errors); ?>
        </div>
      </div>

      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               class="<?= errClass('email', $errors) ?>"
               placeholder="john@email.com"
               value="<?= val('email', $v) ?>">
        <?php err('email', $errors); ?>
      </div>

      <div class="field" style="margin-bottom:0">
        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone"
               class="<?= errClass('phone', $errors) ?>"
               placeholder="+60 12-345 6789"
               value="<?= val('phone', $v) ?>">
        <?php err('phone', $errors); ?>
      </div>
    </div>

    <!-- Delivery Address -->
    <div class="panel" style="animation-delay:0.15s">
      <div class="panel-title">
        <div class="panel-title-icon">📍</div>
        Delivery Address
      </div>

      <div class="field">
        <label for="address1">Street Address</label>
        <input type="text" id="address1" name="address1"
               class="<?= errClass('address1', $errors) ?>"
               placeholder="No. 12, Jalan Setia..."
               value="<?= val('address1', $v) ?>">
        <?php err('address1', $errors); ?>
      </div>

      <div class="field">
        <label for="address2">Address Line 2
          <span style="color:#aaa;font-weight:300;font-size:0.7rem">(optional)</span>
        </label>
        <input type="text" id="address2" name="address2"
               placeholder="Apartment, suite, unit..."
               value="<?= val('address2', $v) ?>">
      </div>

      <div class="form-row triple">
        <div class="field" style="margin-bottom:0">
          <label for="city">City</label>
          <input type="text" id="city" name="city"
                 class="<?= errClass('city', $errors) ?>"
                 placeholder="Johor Bahru"
                 value="<?= val('city', $v) ?>">
          <?php err('city', $errors); ?>
        </div>

        <div class="field" style="margin-bottom:0">
          <label for="state">State</label>
          <select id="state" name="state" class="<?= errClass('state', $errors) ?>">
            <option value="">Select</option>
            <?php
            $states = ['Johor','Selangor','Kuala Lumpur','Penang',
                       'Sabah','Sarawak','Melaka','Sembilan','Kedah'];
            foreach ($states as $s) {
                $sel = ($v['state'] ?? '') === $s ? ' selected' : '';
                echo "<option{$sel}>" . htmlspecialchars($s) . "</option>";
            }
            ?>
          </select>
          <?php err('state', $errors); ?>
        </div>

        <div class="field" style="margin-bottom:0">
          <label for="postcode">Postcode</label>
          <input type="text" id="postcode" name="postcode"
                 class="<?= errClass('postcode', $errors) ?>"
                 placeholder="80000" maxlength="5"
                 value="<?= val('postcode', $v) ?>">
          <?php err('postcode', $errors); ?>
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
      <div class="order-items-list">
        <div class="order-item">
          <div class="item-thumb">
            <img src="images/dell-inspiron15.jpg"
                 alt="Dell Inspiron 15"
                 onerror="this.style.display='none';this.parentNode.textContent='💻'">
          </div>
          <div class="item-info">
            <p>Dell Inspiron 15</p>
            <small>Intel i5 · 16GB RAM · 512GB SSD</small>
          </div>
          <div class="item-price">RM 3,500<div class="item-qty">×1</div></div>
          <button type="button" class="remove-btn" onclick="askRemove(this, 3500)">✕ Remove</button>
        </div>
        <div class="order-item">
          <div class="item-thumb">
            <img src="images/wireless-mouse.jpg"
                 alt="Wireless Mouse Pro"
                 onerror="this.style.display='none';this.parentNode.textContent='🖱️'">
          </div>
          <div class="item-info">
            <p>Wireless Mouse Pro</p>
            <small>Bluetooth 5.0 · Ergonomic</small>
          </div>
          <div class="item-price">RM 50<div class="item-qty">×1</div></div>
          <button type="button" class="remove-btn" onclick="askRemove(this, 50)">✕ Remove</button>
        </div>
        <div class="order-item">
          <div class="item-thumb">
            <img src="images/keyboard.jpg"
                 alt="Mechanical Keyboard"
                 onerror="this.style.display='none';this.parentNode.textContent='⌨️'">
          </div>
          <div class="item-info">
            <p>Mechanical Keyboard</p>
            <small>TKL · RGB · Blue Switch</small>
          </div>
          <div class="item-price">RM 280<div class="item-qty">×1</div></div>
          <button type="button" class="remove-btn" onclick="askRemove(this, 280)">✕ Remove</button>
        </div>
        <div class="order-item">
          <div class="item-thumb">
            <img src="images/monitor.jpg"
                 alt="27 inch IPS Monitor"
                 onerror="this.style.display='none';this.parentNode.textContent='🖥️'">
          </div>
          <div class="item-info">
            <p>27" IPS Monitor</p>
            <small>1440p · 144Hz · HDR</small>
          </div>
          <div class="item-price">RM 1,200<div class="item-qty">×1</div></div>
          <button type="button" class="remove-btn" onclick="askRemove(this, 1200)">✕ Remove</button>
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

      <!-- This button triggers JS validation first, then shows confirm modal -->
      <button type="button" class="order-btn" id="orderBtn" onclick="handlePlaceOrder()">
        🔒 Place Order &nbsp;→
      </button>
    </div>
  </div>

</div><!-- end .page -->
</form>


<!-- ══ MODAL 1 — Order Confirmation ══ -->
<div id="confirmOverlay" class="modal-overlay">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">
        <div class="modal-title-icon">🛍️</div>
        Confirm Your Order
      </div>
      <button type="button" class="modal-close-btn" onclick="closeConfirmModal()">✕</button>
    </div>

    <div id="modalItems"></div>

    <hr class="divider">
    <div class="line-item"><span id="modal-subtotal-label">Subtotal</span><span id="modal-subtotal"></span></div>
    <div class="line-item free"><span>Shipping</span><span>Free</span></div>
    <div class="line-item"><span>Tax (6% SST)</span><span id="modal-tax"></span></div>
    <hr class="divider">
    <div class="line-item total"><span>Total</span><span id="modal-total"></span></div>

    <button type="button" class="btn-confirm" id="confirmBtn" onclick="submitOrder(this)">
      🔒 Confirm &amp; Pay &nbsp;→
    </button>
    <button type="button" class="btn-secondary" onclick="closeConfirmModal()">Cancel</button>
  </div>
</div>


<!-- ══ MODAL 2 — Remove Item Confirmation ══ -->
<div id="removeOverlay" class="modal-overlay">
  <div class="modal-box" style="max-width:360px; text-align:center;">
    <div class="remove-modal-icon">🗑️</div>
    <h3 style="font-size:1rem; font-weight:700; color:#111; margin-bottom:10px;">Remove Item?</h3>
    <p style="font-size:0.85rem; color:#666; line-height:1.7;">
      Are you sure you want to remove<br>
      <strong id="remove-item-name" style="color:#111;"></strong>
      from your order?
    </p>
    <button type="button" class="btn-remove-confirm" onclick="confirmRemove()">Yes, Remove It</button>
    <button type="button" class="btn-secondary" onclick="closeRemoveModal()">Keep It</button>
  </div>
</div>


<script>
// ── State ────────────────────────────────────────────────────────────
let subtotal           = 5030;
let pendingRemoveBtn   = null;
let pendingRemovePrice = 0;

// ── Totals updater ────────────────────────────────────────────────────
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

// ── Remove — step 1: confirm popup ───────────────────────────────────
function askRemove(btn, price) {
  const item = btn.closest('.order-item');
  pendingRemoveBtn   = btn;
  pendingRemovePrice = price;
  document.getElementById('remove-item-name').textContent = item.querySelector('.item-info p').textContent;
  document.getElementById('removeOverlay').style.display = 'flex';
}

// ── Remove — step 2: confirmed ────────────────────────────────────────
function confirmRemove() {
  closeRemoveModal();
  if (!pendingRemoveBtn) return;
  const item = pendingRemoveBtn.closest('.order-item');
  item.style.transition = 'opacity 0.25s, transform 0.25s';
  item.style.opacity    = '0';
  item.style.transform  = 'translateX(10px)';
  setTimeout(() => {
    item.remove();
    subtotal -= pendingRemovePrice;
    pendingRemoveBtn = null; pendingRemovePrice = 0;
    updateTotals();
  }, 270);
}

function closeRemoveModal() {
  document.getElementById('removeOverlay').style.display = 'none';
}

// ── Client-side validation ────────────────────────────────────────────
const rules = {
  first_name: { label: 'First name',    required: true, pattern: /^[a-zA-Z\s'\-]+$/,  patternMsg: 'First name may only contain letters.' },
  last_name:  { label: 'Last name',     required: true, pattern: /^[a-zA-Z\s'\-]+$/,  patternMsg: 'Last name may only contain letters.' },
  email:      { label: 'Email address', required: true, pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, patternMsg: 'Enter a valid email (e.g. john@email.com).' },
  phone:      { label: 'Phone number',  required: true, pattern: /^[+0-9\s\-()\u200f]{7,20}$/, patternMsg: 'Enter a valid phone number (e.g. +60 12-345 6789).' },
  address1:   { label: 'Street address',required: true },
  city:       { label: 'City',          required: true, pattern: /^[a-zA-Z\s'\-]+$/, patternMsg: 'City name may only contain letters.' },
  state:      { label: 'State',         required: true },
  postcode:   { label: 'Postcode',      required: true, pattern: /^\d{5}$/, patternMsg: 'Postcode must be exactly 5 digits.' },
};

function clearClientErrors() {
  document.querySelectorAll('.client-err').forEach(e => e.remove());
  document.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));
  const banner = document.getElementById('client-banner');
  if (banner) banner.remove();
}

function showClientError(input, msg) {
  input.classList.add('is-error');
  const err = document.createElement('div');
  err.className = 'field-error client-err';
  err.innerHTML = '<span class="field-error-icon">⚠</span>' + msg;
  input.closest('.field').appendChild(err);
}

function validateForm() {
  clearClientErrors();
  let firstError = null;
  let hasError = false;

  for (const [name, rule] of Object.entries(rules)) {
    const el = document.querySelector('[name="' + name + '"]');
    if (!el) continue;
    const val = el.value.trim();

    if (rule.required && val === '') {
      showClientError(el, rule.label + ' is required.');
      if (!firstError) firstError = el;
      hasError = true;
    } else if (val !== '' && rule.pattern && !rule.pattern.test(val)) {
      showClientError(el, rule.patternMsg);
      if (!firstError) firstError = el;
      hasError = true;
    }
  }

  if (hasError) {
    // Show top banner
    const banner = document.createElement('div');
    banner.id = 'client-banner';
    banner.className = 'error-banner client-err';
    banner.innerHTML = '<span class="error-banner-icon">⚠️</span> Please fix the highlighted fields before continuing.';
    document.querySelector('.panel').before(banner);
    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    firstError.focus();
  }

  return !hasError;
}

// ── Place Order: validate first, then show confirm modal ─────────────
function handlePlaceOrder() {
  if (!validateForm()) return;
  showConfirmModal();
}

// Clear error on input change
document.addEventListener('input', function(e) {
  if (e.target.classList.contains('is-error')) {
    e.target.classList.remove('is-error');
    const fe = e.target.closest('.field')?.querySelector('.client-err');
    if (fe) fe.remove();
  }
});
document.addEventListener('change', function(e) {
  if (e.target.classList.contains('is-error')) {
    e.target.classList.remove('is-error');
    const fe = e.target.closest('.field')?.querySelector('.client-err');
    if (fe) fe.remove();
  }
});

// ── Order Confirmation Modal ──────────────────────────────────────────
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

// ── Submit: redirect to paymentcheckout.php ───────────────────────────
function submitOrder(btn) {
  btn.innerHTML        = '⏳ Processing…';
  btn.style.background = '#1555a0';
  btn.disabled         = true;
  // Submit the actual form so PHP session is populated
  document.querySelector('form').submit();
}

// ── Close modals on backdrop click ────────────────────────────────────
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeConfirmModal();
});
document.getElementById('removeOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeRemoveModal();
});
</script>

</body>
</html>