<?php
session_start();
include "database.php";

// ── Guard: must be logged in ───────────────────────────────────────────────
if (!isset($_SESSION['User_ID'])) {
  header('Location: login.php');
  exit;
}
$user_id = (int) $_SESSION['User_ID'];

// ── Load user info ─────────────────────────────────────────────────────────
$user_stmt = $conn->prepare("SELECT User_Name, Email, Phone, Address FROM user WHERE User_ID = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_row = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// ── Load cart items ────────────────────────────────────────────────────────
$cart_stmt = $conn->prepare("
    SELECT c.Cart_ID, c.cartQuantity, c.build_ref, p.Product_ID, p.Product_name, p.Price, p.imageUrl
    FROM cart c
    JOIN product p ON c.Product_ID = p.Product_ID
    WHERE c.User_ID = ?
");
$cart_stmt->bind_param('i', $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart_items = [];
while ($row = $cart_result->fetch_assoc()) {
  $cart_items[] = $row;
}
$cart_stmt->close();

// Check if any item has a build_ref (came from PC Builder)
$has_build = !empty(array_filter($cart_items, fn($i) => !empty($i['build_ref'])));

// Totals
$subtotal = array_sum(array_map(fn($i) => $i['Price'] * $i['cartQuantity'], $cart_items));
$tax = round($subtotal * 0.06);
$total = $subtotal + $tax;

// ── Form handling ──────────────────────────────────────────────────────────
$errors = [];

// Pre-fill from user record on first load
$v = $_POST ?: [
  'user_name' => $user_row['User_Name'] ?? '',
  'email' => $user_row['Email'] ?? '',
  'phone' => $user_row['Phone'] ?? '',
  'address1' => $user_row['Address'] ?? '',
  'address2' => '',
  'city' => '',
  'state' => '',
  'postcode' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $v['user_name'] = trim($_POST['user_name'] ?? '');
  $v['email'] = trim($_POST['email'] ?? '');
  $v['phone'] = trim($_POST['phone'] ?? '');
  $v['address1'] = trim($_POST['address1'] ?? '');
  $v['address2'] = trim($_POST['address2'] ?? '');
  $v['city'] = trim($_POST['city'] ?? '');
  $v['state'] = trim($_POST['state'] ?? '');
  $v['postcode'] = trim($_POST['postcode'] ?? '');

  if ($v['user_name'] === '')
    $errors['user_name'] = 'Full name is required.';
  elseif (!preg_match('/^[a-zA-Z\s\'-]+$/', $v['user_name']))
    $errors['user_name'] = 'Name may only contain letters.';

  if ($v['email'] === '')
    $errors['email'] = 'Email address is required.';
  elseif (!filter_var($v['email'], FILTER_VALIDATE_EMAIL))
    $errors['email'] = 'Please enter a valid email address.';

  if ($v['phone'] === '')
    $errors['phone'] = 'Phone number is required.';
  elseif (!preg_match('/^[+0-9\s\-()]{7,20}$/', $v['phone']))
    $errors['phone'] = 'Enter a valid phone number.';

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

  if (empty($cart_items))
    $errors['cart'] = 'Your cart is empty.';

  if (empty($errors)) {
    $full_address = $v['address1'];
    if (!empty($v['address2']))
      $full_address .= ', ' . $v['address2'];
    $full_address .= ', ' . $v['city'] . ', ' . $v['state'] . ' ' . $v['postcode'];

    $_SESSION['checkout'] = $v;
    $_SESSION['checkout_address'] = $full_address;
    $_SESSION['checkout_total'] = $total;
    $_SESSION['checkout_subtotal'] = $subtotal;
    $_SESSION['checkout_tax'] = $tax;
    $_SESSION['assembly_request'] = ($_POST['assembly_request'] ?? 'No');

    header('Location: paymentcheckout.php');
    exit;
  }
}

// ── Helpers ────────────────────────────────────────────────────────────────
function err($field, $errors)
{
  if (isset($errors[$field]))
    echo '<div class="field-error"><span class="field-error-icon">⚠</span>' . htmlspecialchars($errors[$field]) . '</div>';
}
function errClass($field, $errors)
{
  return isset($errors[$field]) ? ' is-error' : '';
}
function val($field, $v)
{
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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #f8f9fa;
      color: #222;
      min-height: 100vh;
      font-size: 15px;
      line-height: 1.6;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 30px;
      height: 62px;
      background: #fff;
      border-bottom: 1px solid #eee;
      position: sticky;
      top: 0;
      z-index: 10;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
    }

    .logo {
      font-weight: 800;
      font-size: 1.25rem;
      color: #007bff;
      text-decoration: none;
      letter-spacing: -0.5px;
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 0.8rem;
      color: #aaa;
    }

    .breadcrumb span.active {
      color: #222;
      font-weight: 600;
    }

    .profile-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #f0f6ff;
      border: 1.5px solid #cce0ff;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s;
    }

    .profile-btn:hover {
      background: #dbeeff;
      border-color: #007bff;
    }

    .profile-btn svg {
      width: 18px;
      height: 18px;
      fill: #007bff;
    }

    .page {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 460px;
      gap: 28px;
      max-width: 1280px;
      margin: 0 auto;
      padding: 30px 40px 80px;
      align-items: start;
    }

    .panel {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #eaeaea;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
      padding: 28px;
      margin-bottom: 20px;
    }

    .panel-title {
      font-size: 1rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 22px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid #f0f0f0;
      padding-bottom: 14px;
    }

    .panel-title-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: #f0f6ff;
      border: 1px solid #cce0ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
    }

    .error-banner {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fff5f5;
      border: 1px solid #f5c6cb;
      border-radius: 8px;
      padding: 11px 14px;
      margin-bottom: 20px;
      font-size: 0.83rem;
      color: #dc3545;
    }

    .error-banner-icon {
      font-size: 1rem;
      flex-shrink: 0;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .form-row.triple {
      grid-template-columns: 1fr 1fr 1fr;
    }

    .field {
      margin-bottom: 14px;
    }

    .field:last-child {
      margin-bottom: 0;
    }

    .field label {
      display: block;
      font-size: 0.78rem;
      font-weight: 600;
      color: #555;
      margin-bottom: 6px;
    }

    .field input,
    .field select {
      width: 100%;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 10px 13px;
      color: #222;
      font-size: 0.9rem;
      outline: none;
      transition: 0.3s;
      appearance: none;
    }

    .field input:focus,
    .field select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .field input::placeholder {
      color: #bbb;
    }

    .field input.is-error,
    .field select.is-error {
      border-color: #dc3545;
      box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    }

    .field-error {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 0.73rem;
      color: #dc3545;
      margin-top: 5px;
    }

    .field-error-icon {
      font-size: 0.7rem;
      flex-shrink: 0;
    }

    .order-items-list {
      max-height: 260px;
      overflow-y: auto;
      padding-right: 4px;
      margin-bottom: 4px;
    }

    .order-items-list::-webkit-scrollbar {
      width: 4px;
    }

    .order-items-list::-webkit-scrollbar-track {
      background: #f8f9fa;
      border-radius: 4px;
    }

    .order-items-list::-webkit-scrollbar-thumb {
      background: #cce0ff;
      border-radius: 4px;
    }

    .order-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px;
      background: #fff;
      border: 1px solid #eaeaea;
      border-radius: 12px;
      margin-bottom: 8px;
      transition: 0.3s;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
    }

    .order-item:last-child {
      margin-bottom: 0;
    }

    .order-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, 0.06);
      border-color: #007bff;
    }

    .item-thumb {
      width: 52px;
      height: 52px;
      border-radius: 8px;
      background: #f8f9fa;
      border: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      flex-shrink: 0;
      overflow: hidden;
    }

    .item-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
    }

    .item-info {
      flex: 1;
    }

    .item-info p {
      font-size: 0.87rem;
      font-weight: 600;
      color: #111;
      margin-bottom: 2px;
    }

    .item-info small {
      font-size: 0.74rem;
      color: #aaa;
    }

    .item-price {
      font-size: 0.9rem;
      font-weight: 800;
      color: #000;
      text-align: right;
      white-space: nowrap;
    }

    .item-qty {
      font-size: 0.68rem;
      color: #aaa;
      margin-top: 1px;
    }

    .divider {
      border: none;
      border-top: 1px solid #eee;
      margin: 14px 0;
    }

    .line-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.85rem;
      padding: 5px 0;
      color: #888;
    }

    .line-item span:last-child {
      color: #222;
      font-weight: 600;
    }

    .line-item.free span:last-child {
      color: #28a745;
    }

    .line-item.total {
      font-size: 1.05rem;
      padding: 6px 0;
    }

    .line-item.total span:first-child {
      color: #111;
      font-weight: 700;
    }

    .line-item.total span:last-child {
      color: #007bff;
      font-size: 1.2rem;
      font-weight: 800;
    }

    .order-btn {
      width: 100%;
      background-color: #111;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: 0.3s;
    }

    .order-btn:hover {
      background-color: #007bff;
    }

    .order-btn:disabled {
      background: #ccc;
      cursor: not-allowed;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .modal-box {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #eaeaea;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
      padding: 28px;
      width: 100%;
      max-width: 420px;
    }

    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 18px;
    }

    .modal-title {
      font-size: 1rem;
      font-weight: 700;
      color: #111;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .modal-title-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: #f0f6ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.85rem;
    }

    .modal-close-btn {
      width: 28px;
      height: 28px;
      border-radius: 6px;
      border: 1px solid #eee;
      background: transparent;
      color: #888;
      cursor: pointer;
      font-size: 13px;
    }

    .modal-close-btn:hover {
      background: #f8f9fa;
    }

    .modal-item {
      background: #f8f9fa;
      border-radius: 10px;
      border: 1px solid #eaeaea;
      padding: 10px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 8px;
    }

    .modal-item-thumb {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      background: #fff;
      border: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .btn-confirm {
      width: 100%;
      padding: 12px;
      background: #111;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 0.92rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: 0.3s;
    }

    .btn-confirm:hover {
      background: #007bff;
    }

    .btn-confirm:disabled {
      background: #aaa;
      cursor: not-allowed;
    }

    .btn-secondary {
      width: 100%;
      padding: 10px;
      background: transparent;
      border: 1px solid #eee;
      border-radius: 8px;
      color: #888;
      font-size: 0.85rem;
      cursor: pointer;
      margin-top: 8px;
      transition: 0.3s;
    }

    .btn-secondary:hover {
      background: #f8f9fa;
      border-color: #ddd;
      color: #555;
    }

    .remove-modal-icon {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: #fff5f5;
      border: 1.5px solid #f5c6cb;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      margin: 0 auto 16px;
    }

    .btn-remove-confirm {
      width: 100%;
      padding: 12px;
      background: #dc3545;
      border: none;
      border-radius: 8px;
      color: #fff;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      margin-top: 16px;
      transition: 0.3s;
    }

    .btn-remove-confirm:hover {
      background: #c82333;
    }

    .assembly-box {
      border: 1px solid #cce0ff;
      background: #f0f6ff;
    }

    .assembly-box .panel-title {
      border-bottom-color: #cce0ff;
    }

    .assembly-disabled {
      border: 1px solid #eee !important;
      background: #fafafa !important;
    }

    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(12px);
      }

      to {
        opacity: 1;
        transform: none;
      }
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 0.84rem;
      color: #888;
      cursor: pointer;
      transition: 0.3s;
      margin: 0 0 16px 0;
      text-decoration: none;
    }

    .back-btn:hover {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
    }

    /* scrollable modal items */
    #modalItems {
      max-height: 220px;
      overflow-y: auto;
      padding-right: 2px;
    }

    #modalItems::-webkit-scrollbar {
      width: 4px;
    }

    #modalItems::-webkit-scrollbar-track {
      background: #f8f9fa;
      border-radius: 4px;
    }

    #modalItems::-webkit-scrollbar-thumb {
      background: #cce0ff;
      border-radius: 4px;
    }

    @media (max-width: 1100px) {
      .page {
        grid-template-columns: minmax(0, 1fr) 380px;
        padding: 20px 24px 80px;
        gap: 20px;
      }
    }

    @media (max-width: 860px) {
      .page {
        grid-template-columns: 1fr;
        padding: 16px 16px 60px;
      }

      .breadcrumb {
        display: none;
      }

      .form-row.triple {
        grid-template-columns: 1fr 1fr;
      }
    }

    @media (max-width: 500px) {

      .form-row,
      .form-row.triple {
        grid-template-columns: 1fr;
      }

      .step-line {
        width: 28px;
      }
    }
  </style>
</head>

<body>

  <?php include "header.php"; ?>


  <?php if (!empty($errors['cart'])): ?>
    <div style="max-width:600px;margin:20px auto;padding:0 24px">
      <div class="error-banner">
        <span class="error-banner-icon">⚠️</span>
        <?= htmlspecialchars($errors['cart']) ?>
        <a href="products.php" style="margin-left:8px;color:#007bff">Shop now</a>
      </div>
    </div>
  <?php endif; ?>

 <form method="POST" action="checkout.php" novalidate id="checkoutForm"> 
    <div class="page">

      <!-- LEFT COLUMN -->
      <div>
        <a href="cart.php" class="back-btn">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="15 18 9 12 15 6" />
          </svg>
          Back to Cart
        </a>

        <?php if (!empty($errors) && !isset($errors['cart'])): ?>
          <div class="error-banner">
            <span class="error-banner-icon">⚠️</span>
            Please fix the highlighted fields below before continuing.
          </div>
        <?php endif; ?>

        <!-- Contact Information -->
        <div class="panel">
          <div class="panel-title">
            <div class="panel-title-icon">👤</div> Contact Information
          </div>

          <div class="field">
            <label for="user_name">Full Name</label>
            <input type="text" id="user_name" name="user_name" class="<?= errClass('user_name', $errors) ?>"
              placeholder="John Doe" value="<?= val('user_name', $v) ?>">
            <?php err('user_name', $errors); ?>
          </div>
          <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="<?= errClass('email', $errors) ?>"
              placeholder="john@email.com" value="<?= val('email', $v) ?>">
            <?php err('email', $errors); ?>
          </div>
          <div class="field" style="margin-bottom:0">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="<?= errClass('phone', $errors) ?>"
              placeholder="+60 12-345 6789" value="<?= val('phone', $v) ?>">
            <?php err('phone', $errors); ?>
          </div>
        </div>

        <!-- Delivery Address -->
        <div class="panel">
          <div class="panel-title">
            <div class="panel-title-icon">📍</div> Delivery Address
          </div>

          <div class="field">
            <label for="address1">Street Address</label>
            <input type="text" id="address1" name="address1" class="<?= errClass('address1', $errors) ?>"
              placeholder="No. 12, Jalan Setia..." value="<?= val('address1', $v) ?>">
            <?php err('address1', $errors); ?>
          </div>
          <div class="field">
            <label for="address2">Address Line 2 <span
                style="color:#aaa;font-weight:300;font-size:0.7rem">(optional)</span></label>
            <input type="text" id="address2" name="address2" placeholder="Apartment, suite, unit..."
              value="<?= val('address2', $v) ?>">
          </div>
          <div class="form-row triple">
            <div class="field" style="margin-bottom:0">
              <label for="city">City</label>
              <input type="text" id="city" name="city" class="<?= errClass('city', $errors) ?>"
                placeholder="Johor Bahru" value="<?= val('city', $v) ?>">
              <?php err('city', $errors); ?>
            </div>
            <div class="field" style="margin-bottom:0">
              <label for="state">State</label>
              <select id="state" name="state" class="<?= errClass('state', $errors) ?>">
                <option value="">Select</option>
                <?php
                $states = [
                  'Johor',
                  'Selangor',
                  'Kuala Lumpur',
                  'Penang',
                  'Sabah',
                  'Sarawak',
                  'Melaka',
                  'Negeri Sembilan',
                  'Kedah',
                  'Perak',
                  'Pahang',
                  'Terengganu',
                  'Kelantan',
                  'Perlis',
                  'Putrajaya',
                  'Labuan'
                ];
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
              <input type="text" id="postcode" name="postcode" class="<?= errClass('postcode', $errors) ?>"
                placeholder="80000" maxlength="5" value="<?= val('postcode', $v) ?>">
              <?php err('postcode', $errors); ?>
            </div>
          </div>
        </div>

      </div><!-- end left column -->

      <!-- RIGHT COLUMN -->
      <div>
        <div class="panel">
          <div class="panel-title">
            <div class="panel-title-icon">🛍️</div> Order Summary
          </div>

          

          <?php if (empty($cart_items)): ?>
            <p style="color:#aaa;font-size:0.88rem;text-align:center;padding:20px 0">
              Your cart is empty. <a href="products.php" style="color:#007bff">Shop now</a>
            </p>
          <?php else: ?>

            <div class="order-items-list">
              <?php foreach ($cart_items as $item):
                $line_total = $item['Price'] * $item['cartQuantity'];
                ?>
                <div class="order-item">
                  <div class="item-thumb">
                    <img src="<?= htmlspecialchars($item['imageUrl']) ?>"
                      alt="<?= htmlspecialchars($item['Product_name']) ?>"
                      onerror="this.style.display='none';this.parentNode.textContent='📦'">
                  </div>
                  <div class="item-info">
                    <p><?= htmlspecialchars($item['Product_name']) ?></p>
                    <small>Qty: <?= (int) $item['cartQuantity'] ?></small>
                  </div>
                  <div class="item-price">
                    RM <?= number_format($line_total, 2) ?>
                    <div class="item-qty">×<?= (int) $item['cartQuantity'] ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            
            
            

            <hr class="divider">
            <div class="line-item">
              <span>Subtotal (<?= count($cart_items) ?> item<?= count($cart_items) !== 1 ? 's' : '' ?>)</span>
              <span>RM <?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="line-item free"><span>Shipping</span><span>Free</span></div>
            <div class="line-item"><span>Tax (6% SST)</span><span>RM <?= number_format($tax, 2) ?></span></div>
            <hr class="divider">
            <div class="line-item total">
              <span>Total</span>
              <span>RM <?= number_format($total, 2) ?></span>
            </div>

            <button type="button" class="order-btn" id="orderBtn" onclick="handlePlaceOrder()">
              🔒 Place Order &nbsp;→
            </button>

          <?php endif; ?>
        </div>


        <?php if ($has_build): ?>

      <div class="panel assembly-box">
        <div class="panel-title">
          <div class="panel-title-icon">🛠️</div>
          Installation &amp; Assembly
        </div>

        <p style="font-size:0.85rem;color:#777;margin-bottom:16px;">
          Our technicians can assemble your Custom PC for you at no extra cost.
        </p>

        <select name="assembly_request"
          style="width:100%;background:#fff;border:1px solid #ddd;border-radius:8px;padding:10px 13px;color:#222;font-size:0.9rem;">
          <option value="Yes">🛠️ Yes, assemble my Custom PC(s) for me</option>
          <option value="No">📦 No, just send me the sealed components</option>
        </select>
      </div>

    <?php else: ?>

      <div class="panel assembly-box assembly-disabled">
        <div class="panel-title" style="opacity:0.5;">
          <div class="panel-title-icon">🛠️</div>
          Installation &amp; Assembly
        </div>

        <p style="font-size:0.85rem;color:#bbb;margin-bottom:16px;">
          Assembly option is only available for orders that include a full PC build from the Builder.
        </p>

        <select disabled
          style="width:100%;background:#f8f9fa;border:1px solid #eee;border-radius:8px;padding:10px 13px;color:#bbb;font-size:0.9rem;">
          <option>Not applicable for individual parts</option>
        </select>
      </div>

    <?php endif; ?>
      </div>

      

    </div>
  </form>

  <!-- ══ MODAL 1 — Order Confirmation ══ -->
  <div id="confirmOverlay" class="modal-overlay">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">
          <div class="modal-title-icon">🛍️</div> Confirm Your Order
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

  <!-- ══ MODAL 2 — Remove Item ══ -->
  <div id="removeOverlay" class="modal-overlay">
    <div class="modal-box" style="max-width:360px;text-align:center;">
      <div class="remove-modal-icon">🗑️</div>
      <h3 style="font-size:1rem;font-weight:700;color:#111;margin-bottom:10px;">Remove Item?</h3>
      <p style="font-size:0.85rem;color:#666;line-height:1.7;">
        Are you sure you want to remove<br>
        <strong id="remove-item-name" style="color:#111;"></strong> from your order?
      </p>
      <button type="button" class="btn-remove-confirm" onclick="confirmRemove()">Yes, Remove It</button>
      <button type="button" class="btn-secondary" onclick="closeRemoveModal()">Keep It</button>
    </div>
  </div>

  <script>
    let subtotal = <?= (float) $subtotal ?>;
    let pendingRemoveBtn = null;
    let pendingRemovePrice = 0;

    function fmt(n) {
      return 'RM ' + n.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotals() {
      const tax = Math.round(subtotal * 0.06);
      const total = subtotal + tax;
      const count = document.querySelectorAll('.order-item').length;
      document.getElementById('subtotal-label') && (document.getElementById('subtotal-label').textContent = 'Subtotal (' + count + ' item' + (count !== 1 ? 's' : '') + ')');
      document.getElementById('subtotal-val') && (document.getElementById('subtotal-val').textContent = fmt(subtotal));
      document.getElementById('tax-val') && (document.getElementById('tax-val').textContent = fmt(tax));
      document.getElementById('total-val') && (document.getElementById('total-val').textContent = fmt(total));
      if (subtotal <= 0) {
        const btn = document.getElementById('orderBtn');
        if (btn) { btn.disabled = true; btn.innerHTML = '🚫 Cart is Empty'; }
      }
    }

    function askRemove(btn, price) {
      pendingRemoveBtn = btn; pendingRemovePrice = price;
      document.getElementById('remove-item-name').textContent = btn.closest('.order-item').querySelector('.item-info p').textContent;
      document.getElementById('removeOverlay').style.display = 'flex';
    }
    function confirmRemove() {
      closeRemoveModal();
      if (!pendingRemoveBtn) return;
      const item = pendingRemoveBtn.closest('.order-item');
      item.style.transition = 'opacity 0.25s,transform 0.25s';
      item.style.opacity = '0'; item.style.transform = 'translateX(10px)';
      setTimeout(() => { item.remove(); subtotal -= pendingRemovePrice; pendingRemoveBtn = null; pendingRemovePrice = 0; updateTotals(); }, 270);
    }
    function closeRemoveModal() { document.getElementById('removeOverlay').style.display = 'none'; }

    const rules = {
      user_name: { label: 'Full name', required: true, pattern: /^[a-zA-Z\s'\-]+$/, patternMsg: 'Name may only contain letters.' },
      email: { label: 'Email', required: true, pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, patternMsg: 'Enter a valid email.' },
      phone: { label: 'Phone number', required: true, pattern: /^[+0-9\s\-()\u200f]{7,20}$/, patternMsg: 'Enter a valid phone number.' },
      address1: { label: 'Street address', required: true },
      city: { label: 'City', required: true, pattern: /^[a-zA-Z\s'\-]+$/, patternMsg: 'City name may only contain letters.' },
      state: { label: 'State', required: true },
      postcode: { label: 'Postcode', required: true, pattern: /^\d{5}$/, patternMsg: 'Postcode must be exactly 5 digits.' },
    };

    function clearClientErrors() {
      document.querySelectorAll('.client-err').forEach(e => e.remove());
      document.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));
      const b = document.getElementById('client-banner'); if (b) b.remove();
    }
    function showClientError(input, msg) {

  input.classList.add('is-error');

  // REMOVE existing server/client errors first
  input.closest('.field')
       ?.querySelectorAll('.field-error')
       .forEach(e => e.remove());

  const err = document.createElement('div');

  err.className = 'field-error client-err';

  err.innerHTML =
    '<span class="field-error-icon">⚠</span>' + msg;

  input.closest('.field').appendChild(err);
}

    function validateForm() {
      clearClientErrors();
      let firstError = null, hasError = false;
      for (const [name, rule] of Object.entries(rules)) {
        const el = document.querySelector('[name="' + name + '"]');
        if (!el) continue;
        const val = el.value.trim();
        if (rule.required && val === '') {
          showClientError(el, rule.label + ' is required.'); if (!firstError) firstError = el; hasError = true;
        } else if (val !== '' && rule.pattern && !rule.pattern.test(val)) {
          showClientError(el, rule.patternMsg); if (!firstError) firstError = el; hasError = true;
        }
      }
      if (hasError) {
        const banner = document.createElement('div');
        banner.id = 'client-banner'; banner.className = 'error-banner client-err';
        banner.innerHTML = '<span class="error-banner-icon">⚠️</span> Please fix the highlighted fields before continuing.';
        const existingBanner = document.getElementById('client-banner');

if (!existingBanner) {
  document.querySelector('.panel').before(banner);
}
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' }); firstError.focus();
      }
      return !hasError;
    }
    function handlePlaceOrder() { if (!validateForm()) return; showConfirmModal(); }

    function validateSingleField(input) {

  const name = input.name;
  const rule = rules[name];

  if (!rule) return;

  const value = input.value.trim();

  // remove old errors first
  input.classList.remove('is-error');

  input.closest('.field')
       ?.querySelectorAll('.field-error')
       .forEach(e => e.remove());

  let hasError = false;

  // required validation
  if (rule.required && value === '') {

    showClientError(input, rule.label + ' is required.');
    hasError = true;
  }

  // pattern validation
  else if (
    value !== '' &&
    rule.pattern &&
    !rule.pattern.test(value)
  ) {

    showClientError(input, rule.patternMsg);
    hasError = true;
  }

  // remove top banner if all fields valid
  checkGlobalErrors();
};

function checkGlobalErrors() {

  const hasErrors =
    document.querySelectorAll('.field .field-error').length > 0;

  // remove ALL error banners when no errors remain
  if (!hasErrors) {

    document.querySelectorAll('.error-banner')
      .forEach(banner => banner.remove());
  }
}

document.addEventListener('input', function (e) {

  if (e.target.matches('input, select')) {

    validateSingleField(e.target);
  }
});

    function showConfirmModal() {
      const items = document.querySelectorAll('.order-item');
      let html = '';
      items.forEach(item => {
        const name = item.querySelector('.item-info p').textContent;
        const desc = item.querySelector('.item-info small').textContent;
        const price = item.querySelector('.item-price').childNodes[0].textContent.trim();
        const imgSrc = item.querySelector('.item-thumb img')?.src || '';
html += `<div class="modal-item">
  <div class="modal-item-thumb" style="overflow:hidden;border-radius:8px;">
    ${imgSrc ? `<img src="${imgSrc}" style="width:100%;height:100%;object-fit:cover;">` : '📦'}
  </div>
  <div style="flex:1"><p style="font-size:0.85rem;font-weight:500;color:#111;margin:0 0 2px">${name}</p>
  <small style="font-size:0.72rem;color:#777">${desc}</small></div>
  <div style="font-size:0.88rem;font-weight:600;color:#111">${price}</div>
</div>`;
      });
      document.getElementById('modalItems').innerHTML = html;
      const tax = Math.round(subtotal * 0.06);
      const count = items.length;
      document.getElementById('modal-subtotal-label').textContent = 'Subtotal (' + count + ' item' + (count !== 1 ? 's' : '') + ')';
      document.getElementById('modal-subtotal').textContent = fmt(subtotal);
      document.getElementById('modal-tax').textContent = fmt(tax);
      document.getElementById('modal-total').textContent = fmt(subtotal + tax);
      document.getElementById('confirmOverlay').style.display = 'flex';
    }
    function closeConfirmModal() { document.getElementById('confirmOverlay').style.display = 'none'; }
    function submitOrder(btn) {
  btn.innerHTML = '⏳ Processing…'; btn.style.background = '#0056b3'; btn.disabled = true;
  document.getElementById('checkoutForm').submit();  // ← targets checkout form
}
    document.getElementById('confirmOverlay').addEventListener('click', function (e) { if (e.target === this) closeConfirmModal(); });
    document.getElementById('removeOverlay').addEventListener('click', function (e) { if (e.target === this) closeRemoveModal(); });
  </script>

</body>

</html>