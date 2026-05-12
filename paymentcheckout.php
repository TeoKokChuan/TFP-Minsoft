<?php
session_start();

// ── Database connection ────────────────────────────────────────────────────
$conn = mysqli_connect('localhost', 'root', '', 'onlinecomputershop');
if (!$conn)
  die('Database connection failed: ' . mysqli_connect_error());

// ── Guards ─────────────────────────────────────────────────────────────────
if (!isset($_SESSION['User_ID'])) {
  header('Location: login.php');
  exit;
}
if (!isset($_SESSION['checkout'])) {
  header('Location: checkout.php');
  exit;
}

$user_id = (int) $_SESSION['User_ID'];
$checkout = $_SESSION['checkout'];
$total = (float) ($_SESSION['checkout_total'] ?? 0);
$subtotal = (float) ($_SESSION['checkout_subtotal'] ?? 0);

// ── Form handling ──────────────────────────────────────────────────────────
$errors = [];
$v = $_POST ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $cardNum = trim($_POST['cardNum'] ?? '');
  $cardName = trim($_POST['cardName'] ?? '');
  $expiry = trim($_POST['expiry'] ?? '');
  $cvv = trim($_POST['cvv'] ?? '');

  if ($cardNum === '')
    $errors['cardNum'] = 'Card number is required.';
  elseif (strlen(preg_replace('/\s/', '', $cardNum)) < 16)
    $errors['cardNum'] = 'Enter a valid 16-digit card number.';

  if ($cardName === '')
    $errors['cardName'] = 'Name on card is required.';

  if ($expiry === '')
    $errors['expiry'] = 'Expiry date is required.';
  elseif (!preg_match('/^\d{2}\s*\/\s*\d{2}$/', $expiry))
    $errors['expiry'] = 'Use MM / YY format.';

  if ($cvv === '')
    $errors['cvv'] = 'CVV is required.';
  elseif (!preg_match('/^\d{3,4}$/', $cvv))
    $errors['cvv'] = 'CVV must be 3 or 4 digits.';

  if (empty($errors)) {

    // ── 1. Insert into customer_order ──────────────────────────────────
    // customer_order: Order_ID, User_ID, orderStatus, orderDate, TotalPrice, PaymentMethod
    $order_sql = "INSERT INTO customer_order (User_ID, orderStatus, orderDate, TotalPrice, PaymentMethod)
                      VALUES (?, 'Pending', NOW(), ?, 'Credit Card')";
    $ostmt = mysqli_prepare($conn, $order_sql);
    mysqli_stmt_bind_param($ostmt, 'id', $user_id, $total);
    mysqli_stmt_execute($ostmt);
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($ostmt);

    // ── 2. Insert into bill_master ─────────────────────────────────────
    // bill_master: Bill_ID, User_ID, Date_time, User_name, Shipping_address, Total_amount, Bill_Status
    $shipping_addr = $_SESSION['checkout_address'] ?? $checkout['address1'];
    $user_name = $checkout['user_name'] ?? '';

    $bill_sql = "INSERT INTO bill_master (Bill_ID, User_ID, Date_time, User_name, Shipping_address, Total_amount, Bill_Status)
                     VALUES (?, ?, NOW(), ?, ?, ?, 'Pending')";
    $bstmt = mysqli_prepare($conn, $bill_sql);
    mysqli_stmt_bind_param(
      $bstmt,
      'iissd',
      $order_id,
      $user_id,
      $user_name,
      $shipping_addr,
      $total
    );
    mysqli_stmt_execute($bstmt);
    mysqli_stmt_close($bstmt);

    // ── 3. Insert into bill_transaction from cart ──────────────────────
    // bill_transaction: transaction_ID, Bill_ID, Product_ID, quantity, unitPrice, subtotal
    $cart_sql = "SELECT c.Product_ID, c.cartQuantity, p.Price
                     FROM cart c
                     JOIN product p ON c.Product_ID = p.Product_ID
                     WHERE c.User_ID = ?";
    $cstmt = mysqli_prepare($conn, $cart_sql);
    mysqli_stmt_bind_param($cstmt, 'i', $user_id);
    mysqli_stmt_execute($cstmt);
    $cart_result = mysqli_stmt_get_result($cstmt);

    $tstmt = mysqli_prepare(
      $conn,
      "INSERT INTO bill_transaction (Bill_ID, Product_ID, quantity, unitPrice, subtotal)
             VALUES (?, ?, ?, ?, ?)"
    );

    while ($row = mysqli_fetch_assoc($cart_result)) {
      $line_subtotal = $row['Price'] * $row['cartQuantity'];
      mysqli_stmt_bind_param(
        $tstmt,
        'iiidd',
        $order_id,
        $row['Product_ID'],
        $row['cartQuantity'],
        $row['Price'],
        $line_subtotal
      );
      mysqli_stmt_execute($tstmt);
    }
    mysqli_stmt_close($cstmt);
    mysqli_stmt_close($tstmt);

    // ── 4. Clear the cart ──────────────────────────────────────────────
    $del = mysqli_prepare($conn, "DELETE FROM cart WHERE User_ID = ?");
    mysqli_stmt_bind_param($del, 'i', $user_id);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);

    // ── 5. Store minimal info for result page (no card data) ──────────
    $_SESSION['payment'] = [
      'order_id' => $order_id,
      'method' => 'Credit Card',
    ];
    unset(
      $_SESSION['checkout'],
      $_SESSION['checkout_address'],
      $_SESSION['checkout_total'],
      $_SESSION['checkout_subtotal'],
      $_SESSION['checkout_tax']
    );

    header('Location: paymentresult.php');
    exit;
  }
}

function err($field, $errors)
{
  if (isset($errors[$field]))
    echo '<div class="field-error">' . htmlspecialchars($errors[$field]) . '</div>';
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
  <title>Payment — Minsoft Solution</title>
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

    .steps-bar {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 20px;
    }

    .step {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.82rem;
      font-weight: 600;
      color: #bbb;
    }

    .step.active {
      color: #007bff;
    }

    .step.done {
      color: #28a745;
    }

    .step-num {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      border: 2px solid currentColor;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.72rem;
      font-weight: 700;
      flex-shrink: 0;
    }

    .step.active .step-num {
      background: #007bff;
      color: #fff;
      border-color: #007bff;
    }

    .step.done .step-num {
      background: #28a745;
      color: #fff;
      border-color: #28a745;
    }

    .step-line {
      width: 60px;
      height: 2px;
      background: #eee;
      margin: 0 10px;
    }

    .body {
      display: flex;
      justify-content: center;
      padding: 30px 24px 80px;
    }

    .panel {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #eaeaea;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
      padding: 32px;
      width: 100%;
      max-width: 520px;
      margin: 0 auto;
      animation: fadeUp 0.4s ease both;
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

    .panel-title {
      font-size: 1rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 24px;
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

    .field {
      margin-bottom: 18px;
    }

    .field:last-of-type {
      margin-bottom: 0;
    }

    .field label {
      display: block;
      font-size: 0.78rem;
      font-weight: 600;
      color: #555;
      margin-bottom: 6px;
    }

    .iw {
      position: relative;
    }

    .field input {
      width: 100%;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 11px 14px;
      color: #222;
      font-size: 0.92rem;
      outline: none;
      transition: 0.3s;
    }

    .field input:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .field input::placeholder {
      color: #bbb;
    }

    .field input.pr {
      padding-right: 90px;
    }

    .field input.is-error {
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

    .field-error::before {
      content: '⚠';
      font-size: 0.7rem;
    }

    .cicons {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      display: flex;
      gap: 5px;
    }

    .cico {
      padding: 3px 7px;
      background: #f0f6ff;
      border: 1px solid #cce0ff;
      border-radius: 4px;
      font-size: 0.6rem;
      color: #007bff;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .frow {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .divider {
      border: none;
      border-top: 1px solid #eee;
      margin: 22px 0;
    }

    .actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .btn-back {
      padding: 11px 20px;
      border-radius: 8px;
      border: 1px solid #ddd;
      background: transparent;
      color: #888;
      font-size: 0.87rem;
      font-weight: 500;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: 0.3s;
      text-decoration: none;
      white-space: nowrap;
    }

    .btn-back:hover {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
    }

    .btn-pay {
      flex: 1;
      padding: 12px;
      background-color: #111;
      border: none;
      border-radius: 8px;
      font-size: 0.95rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: 0.3s;
    }

    .btn-pay:hover {
      background-color: #007bff;
    }

    @media (max-width: 540px) {
      .body {
        padding: 20px 16px 60px;
      }

      .frow {
        grid-template-columns: 1fr;
      }

      .step-line {
        width: 28px;
      }

      .breadcrumb {
        display: none;
      }

      .panel {
        padding: 22px 18px;
      }
    }
  </style>
</head>

<body>

  <header class="topbar">
    <a href="index.php" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>
    <div class="breadcrumb">
      <span>Cart</span><span>›</span>
      <span>Checkout</span><span>›</span>
      <span class="active">Payment</span>
    </div>
    <div class="profile-btn" title="My Account">
      <svg viewBox="0 0 24 24">
        <path
          d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
      </svg>
    </div>
  </header>

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

  <div class="body">
    <form id="payForm" method="POST" action="paymentcheckout.php" style="width:100%">
      <div class="panel">

        <div class="panel-title">
          <div class="panel-title-icon">💳</div>
          Credit / Debit Card
        </div>

        <?php if (!empty($errors)): ?>
          <div class="error-banner">⚠️ Please fill in the necessary information.</div>
        <?php endif; ?>

        <div class="field">
          <label for="cardNum">Card Number</label>
          <div class="iw">
            <input type="text" class="pr<?= errClass('cardNum', $errors) ?>" id="cardNum" name="cardNum"
              placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="cc-number"
              value="<?= val('cardNum', $v) ?>" oninput="fmtCard(this)">
            <div class="cicons">
              <div class="cico">VISA</div>
              <div class="cico">MC</div>
            </div>
          </div>
          <?php err('cardNum', $errors); ?>
        </div>

        <div class="field">
          <label for="cardName">Name on Card</label>
          <input type="text" class="<?= errClass('cardName', $errors) ?>" id="cardName" name="cardName"
            placeholder="JOHN DOE" autocomplete="cc-name" style="text-transform:uppercase;letter-spacing:1.2px"
            value="<?= val('cardName', $v) ?>">
          <?php err('cardName', $errors); ?>
        </div>

        <div class="frow">
          <div class="field" style="margin-bottom:0">
            <label for="expiry">Expiry Date</label>
            <input type="text" class="<?= errClass('expiry', $errors) ?>" id="expiry" name="expiry"
              placeholder="MM / YY" maxlength="7" autocomplete="cc-exp" value="<?= val('expiry', $v) ?>"
              oninput="fmtExp(this)">
            <?php err('expiry', $errors); ?>
          </div>
          <div class="field" style="margin-bottom:0">
            <label for="cvv">CVV</label>
            <input type="password" class="<?= errClass('cvv', $errors) ?>" id="cvv" name="cvv" placeholder="•••"
              maxlength="4" autocomplete="cc-csc">
            <?php err('cvv', $errors); ?>
          </div>
        </div>

        <hr class="divider">

        <div class="actions">
          <a href="checkout.php" class="btn-back">← Back</a>
          <button type="button" class="btn-pay" onclick="doPay()">
            🔒 Pay Now &nbsp;·&nbsp; RM <?= number_format($total, 2) ?>
          </button>
        </div>

      </div>
    </form>
  </div>

  <script>
    function fmtCard(el) {
      let v = el.value.replace(/\D/g, '').slice(0, 16);
      el.value = v.match(/.{1,4}/g)?.join(' ') ?? v;
    }
    function fmtExp(el) {
      let v = el.value.replace(/\D/g, '').slice(0, 4);
      if (v.length >= 3) v = v.slice(0, 2) + ' / ' + v.slice(2);
      el.value = v;
    }
    function doPay() {
      document.querySelectorAll('.client-err').forEach(e => e.remove());
      document.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));
      const cardNum = document.getElementById('cardNum');
      const cardName = document.getElementById('cardName');
      const expiry = document.getElementById('expiry');
      const cvv = document.getElementById('cvv');
      let valid = true;
      if (!cardNum.value.trim()) { showErr(cardNum, 'Card number is required.'); valid = false; }
      else if (cardNum.value.replace(/\s/g, '').length < 16) { showErr(cardNum, 'Enter a valid 16-digit card number.'); valid = false; }
      if (!cardName.value.trim()) { showErr(cardName, 'Name on card is required.'); valid = false; }
      if (!expiry.value.trim()) { showErr(expiry, 'Expiry date is required.'); valid = false; }
      else if (!/^\d{2}\s*\/\s*\d{2}$/.test(expiry.value)) { showErr(expiry, 'Use MM / YY format.'); valid = false; }
      if (!cvv.value.trim()) { showErr(cvv, 'CVV is required.'); valid = false; }
      else if (!/^\d{3,4}$/.test(cvv.value)) { showErr(cvv, 'CVV must be 3 or 4 digits.'); valid = false; }
      if (!valid) return;
      document.getElementById('payForm').submit();
    }
    function showErr(input, msg) {
      input.classList.add('is-error');
      const err = document.createElement('div');
      err.className = 'field-error client-err'; err.textContent = msg;
      input.closest('.field').appendChild(err);
    }
    document.addEventListener('input', function (e) {
      if (e.target.classList.contains('is-error')) {
        e.target.classList.remove('is-error');
        e.target.closest('.field')?.querySelector('.client-err')?.remove();
      }
    });
  </script>

</body>

</html>
<?php mysqli_close($conn); ?>