<?php
session_start();

$order_id  = $_POST['order_id']     ?? 'ORDER-' . strtoupper(substr(uniqid(), -6));
$date      = $_POST['date']         ?? date('d M Y');
$total     = $_POST['total']        ?? 'RM 5,332';
$user_name = $_SESSION['user_name'] ?? 'Customer';

$products = [
  [
    'name'  => 'Dell Inspiron 15',
    'emoji' => '💻',
    'specs' => ['Intel i5', '16GB RAM', '512GB SSD'],
  ],
  [
    'name'  => 'Wireless Mouse Pro',
    'emoji' => '🖱️',
    'specs' => ['Bluetooth 5.0', 'Ergonomic Design'],
  ],
  [
    'name'  => 'Mechanical Keyboard',
    'emoji' => '⌨️',
    'specs' => ['TKL Layout', 'RGB Backlight', 'Blue Switch'],
  ],
  [
    'name'  => '27" IPS Monitor',
    'emoji' => '🖥️',
    'specs' => ['1440p Resolution', '144Hz', 'HDR Support'],
  ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Result — Minsoft Solution</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', sans-serif;
  background: #ffffff;
  color: #020617;
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
  text-decoration: none;
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

/* ── Body ── */
.body {
  display: flex;
  justify-content: center;
  padding: 0 24px 80px;
}

/* ── Panel ── */
.panel {
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 14px;
  padding: 32px;
  width: 100%;
  max-width: 620px;
  animation: fadeUp 0.4s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Panel Title ── */
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
  font-size: 0.85rem;
}

/* ── Success badge ── */
.success-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #f0faf3;
  border: 1px solid #b6e8c4;
  border-radius: 10px;
  padding: 14px 16px;
  margin-bottom: 24px;
}
.success-icon {
  width: 38px; height: 38px;
  border-radius: 50%;
  background: #2a7a2a;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.success-icon svg { width: 20px; height: 20px; fill: #fff; }
.success-text-main {
  font-family: 'Segoe UI', sans-serif;
  font-size: 1rem; font-weight: 700;
  color: #1a5c1a;
}
.success-text-sub {
  font-size: 0.78rem;
  color: #3a7a3a;
  margin-top: 2px;
}

/* ── Divider ── */
.divider { border: none; border-top: 1px solid #e5e5e5; margin: 20px 0; }

/* ── Info Table ── */
.info-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
.info-table tr td {
  padding: 10px 0;
  font-size: 0.86rem;
  vertical-align: top;
}
.info-table tr td:first-child {
  color: #1d6fd8;
  width: 140px;
  font-weight: 500;
  font-size: 0.78rem;
}
.info-table tr td:last-child {
  color: #111;
  font-weight: 500;
}
.info-table tr:not(:last-child) td {
  border-bottom: 1px solid #ebebeb;
}

/* ── Product Cards ── */
.products { display: flex; flex-direction: column; gap: 8px; }
.product-card {
  display: flex; align-items: center; gap: 12px;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 9px;
  padding: 11px 13px;
  transition: border-color 0.18s;
}
.product-card:hover { border-color: #c5d8f5; }
.product-img {
  width: 44px; height: 44px;
  border-radius: 8px;
  background: #eef3fc;
  border: 1px solid #d0e2f8;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.3rem; flex-shrink: 0;
}
.product-info { flex: 1; }
.product-name {
  font-family: 'Segoe UI', sans-serif;
  font-size: 0.87rem; font-weight: 700;
  color: #111; margin-bottom: 4px;
}
.product-specs { list-style: none; padding: 0; }
.product-specs li {
  font-size: 0.74rem; color: #777;
  display: flex; align-items: center; gap: 6px;
  line-height: 1.7;
}
.product-specs li::before {
  content: '';
  width: 4px; height: 4px;
  border-radius: 50%;
  background: #1d6fd8;
  flex-shrink: 0;
}

/* ── Thank you message ── */
.thankyou {
  font-size: 0.83rem;
  color: #666;
  line-height: 1.75;
}

/* ── Back to Home Button ── */
.btn-home {
  display: inline-flex; align-items: center; gap: 9px;
  padding: 12px 26px;
  background: #1d6fd8;
  border: none; border-radius: 8px;
  font-family: 'Segoe UI', sans-serif;
  font-size: 0.9rem; font-weight: 700;
  color: #fff; cursor: pointer;
  text-decoration: none;
  transition: background 0.2s, transform 0.12s;
  letter-spacing: 0.2px;
}
.btn-home:hover  { background: #155bb5; transform: translateY(-1px); }
.btn-home:active { transform: translateY(0); }
.btn-home svg { width: 16px; height: 16px; fill: #fff; }

/* ── Responsive ── */
@media (max-width: 540px) {
  .topbar     { padding: 0 16px; }
  .breadcrumb { display: none; }
  .panel      { padding: 22px 16px; }
  .step-line  { width: 30px; }
}
</style>
</head>
<body>

<!-- ── Topbar ── -->
<header class="topbar">
  <a href="index.php" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>

  <div class="breadcrumb">
    <span>Cart</span>
    <span>›</span>
    <span>Checkout</span>
    <span>›</span>
    <span class="active">Confirmation</span>
  </div>

  <a href="profile.php" class="profile-btn" title="My Account">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
    </svg>
  </a>
</header>

<!-- ── Steps ── -->
<div class="steps-bar">
  <div class="step done">
    <div class="step-num">✓</div> Cart
  </div>
  <div class="step-line"></div>
  <div class="step done">
    <div class="step-num">✓</div> Checkout
  </div>
  <div class="step-line"></div>
  <div class="step active">
    <div class="step-num">3</div> Confirmation
  </div>
</div>

<!-- ── Body ── -->
<div class="body">
  <div class="panel">

    <!-- Panel Title -->
    <div class="panel-title">
      <div class="panel-title-icon">📋</div>
      Payment Result
    </div>

    <!-- Success badge -->
    <div class="success-badge">
      <div class="success-icon">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
        </svg>
      </div>
      <div>
        <div class="success-text-main">Payment Approved!</div>
        <div class="success-text-sub">Your transaction was completed successfully.</div>
      </div>
    </div>

    <hr class="divider">

    <!-- Info table -->
    <table class="info-table">
      <tr>
        <td>Order ID</td>
        <td><?= htmlspecialchars($order_id) ?></td>
      </tr>
      <tr>
        <td>Date</td>
        <td><?= htmlspecialchars($date) ?></td>
      </tr>
      <tr>
        <td>Total Amount</td>
        <td><?= htmlspecialchars($total) ?></td>
      </tr>
      <tr>
        <td>Products Ordered</td>
        <td>
          <div class="products">
            <?php foreach ($products as $p): ?>
            <div class="product-card">
              <div class="product-img"><?= $p['emoji'] ?></div>
              <div class="product-info">
                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                <ul class="product-specs">
                  <?php foreach ($p['specs'] as $spec): ?>
                  <li><?= htmlspecialchars($spec) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </td>
      </tr>
    </table>

    <hr class="divider">

    <!-- Thank you message -->
    <div class="thankyou">
      Thank you, <strong><?= htmlspecialchars($user_name) ?></strong>! Your payment has been successfully processed.
      You will receive an email confirmation shortly with the full details of your order.
    </div>

    <!-- Back to Home -->
    <a href="index.php" class="btn-home" style="margin-top:22px;display:inline-flex">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
      </svg>
      Back to Home
    </a>

  </div>
</div>

</body>
</html>