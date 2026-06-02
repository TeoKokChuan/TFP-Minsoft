<?php
session_start();
include "database.php";

if (!isset($_SESSION['User_ID'])) {
  header('Location: login.php');
  exit;
}
$user_id = (int) $_SESSION['User_ID'];
$user_name = $_SESSION['User_Name'] ?? 'Customer';

// ── Get order ID from session (set by paymentcheckout.php) ─────────────────
$order_id = (int) ($_SESSION['payment']['order_id'] ?? 0);

// Clear payment session
unset($_SESSION['payment']);

// ── Load order from DB ─────────────────────────────────────────────────────
if ($order_id) {
  $ostmt = $conn->prepare("
        SELECT Order_ID, orderDate, TotalPrice, PaymentMethod
        FROM customer_order
        WHERE Order_ID = ? AND User_ID = ?
    ");
  $ostmt->bind_param('ii', $order_id, $user_id);
  $ostmt->execute();
  $order = $ostmt->get_result()->fetch_assoc();
  $ostmt->close();
} else {
  $order = null;
}

// ── Load items from bill_transaction JOIN product ──────────────────────────
$products = [];
if ($order_id) {
  $tstmt = $conn->prepare("
        SELECT bt.quantity, bt.unitPrice, bt.subtotal,
               p.Product_name, p.imageUrl
        FROM bill_transaction bt
        JOIN product p ON bt.Product_ID = p.Product_ID
        WHERE bt.Bill_ID = ?
    ");
  $tstmt->bind_param('i', $order_id);
  $tstmt->execute();
  $tres = $tstmt->get_result();
  while ($row = $tres->fetch_assoc()) {
    $products[] = $row;
  }
  $tstmt->close();
}

// Fallback values if order not found
$display_order_id = $order ? 'ORD-' . str_pad($order['Order_ID'], 5, '0', STR_PAD_LEFT) : 'N/A';
$display_date = $order ? date('d M Y', strtotime($order['orderDate'])) : date('d M Y');
$display_total = $order ? 'RM ' . number_format($order['TotalPrice'], 2) : 'RM 0.00';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Result — Minsoft Solution</title>
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
      color: #020617;
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
      text-decoration: none;
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
      max-width: 1000px;
      margin: 0 auto;
      padding: 30px 32px 80px;
    }

    .panel {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #eaeaea;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
      padding: 32px;
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

    .success-badge {
      display: flex;
      align-items: center;
      gap: 14px;
      background: #f6fff9;
      border: 1px solid #c3e6cb;
      border-radius: 10px;
      padding: 16px 18px;
      margin-bottom: 24px;
    }

    .success-icon {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #28a745;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .success-icon svg {
      width: 22px;
      height: 22px;
      fill: #fff;
    }

    .success-text-main {
      font-size: 1rem;
      font-weight: 700;
      color: #155724;
    }

    .success-text-sub {
      font-size: 0.8rem;
      color: #28a745;
      margin-top: 2px;
    }

    .divider {
      border: none;
      border-top: 1px solid #eee;
      margin: 18px 0;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #f5f5f5;
      font-size: 0.87rem;
    }

    .info-row:last-child {
      border-bottom: none;
    }

    .info-label {
      color: #007bff;
      font-weight: 600;
      font-size: 0.78rem;
      min-width: 120px;
    }

    .info-value {
      color: #222;
      font-weight: 600;
      text-align: right;
    }

    .section-label {
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #bbb;
      margin-bottom: 12px;
    }

    .products-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 280px;
      overflow-y: auto;
      padding-right: 4px;
    }

    .products-list::-webkit-scrollbar {
      width: 4px;
    }

    .products-list::-webkit-scrollbar-track {
      background: #f8f9fa;
      border-radius: 4px;
    }

    .products-list::-webkit-scrollbar-thumb {
      background: #cce0ff;
      border-radius: 4px;
    }

    .product-card {
      display: flex;
      align-items: center;
      gap: 13px;
      background: #fff;
      border: 1px solid #eaeaea;
      border-radius: 12px;
      padding: 12px 14px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
      transition: 0.3s;
    }

    .product-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
      border-color: #007bff;
    }

    .product-img {
      width: 60px;
      height: 60px;
      border-radius: 8px;
      background: #f8f9fa;
      border: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      flex-shrink: 0;
      overflow: hidden;
    }

    .product-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
      display: block;
    }

    .product-info {
      flex: 1;
    }

    .product-name {
      font-size: 0.88rem;
      font-weight: 600;
      color: #111;
      margin-bottom: 2px;
    }

    .product-desc {
      font-size: 0.73rem;
      color: #aaa;
    }

    .product-price {
      font-size: 0.9rem;
      font-weight: 800;
      color: #000;
      white-space: nowrap;
      align-self: flex-start;
      padding-top: 2px;
      text-align: right;
    }

    .product-price small {
      display: block;
      font-size: 0.68rem;
      color: #aaa;
      font-weight: 400;
    }

    .order-line {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.85rem;
      padding: 5px 0;
      color: #888;
    }

    .order-line span:last-child {
      color: #222;
      font-weight: 600;
    }

    .order-line.free span:last-child {
      color: #28a745;
    }

    .order-line.total {
      font-size: 1.05rem;
      padding: 6px 0;
    }

    .order-line.total span:first-child {
      color: #111;
      font-weight: 700;
    }

    .order-line.total span:last-child {
      color: #007bff;
      font-size: 1.2rem;
      font-weight: 800;
    }

    .thankyou {
      font-size: 0.85rem;
      color: #666;
      line-height: 1.75;
    }

    .btn-home {
      display: inline-flex;
      align-items: center;
      gap: 9px;
      padding: 10px 24px;
      background-color: #111;
      border: none;
      border-radius: 8px;
      font-size: 0.9rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      text-decoration: none;
      transition: 0.3s;
      margin-top: 20px;
      margin-right: 10px;
    }

    .btn-home:hover {
      background-color: #007bff;
    }

    .btn-home svg {
      width: 16px;
      height: 16px;
      fill: #fff;
    }

    .btn-history {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 22px;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 0.88rem;
      color: #888;
      cursor: pointer;
      text-decoration: none;
      transition: 0.3s;
      margin-top: 20px;
    }

    .btn-history:hover {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
    }

    @media (max-width: 540px) {
      .topbar {
        padding: 0 16px;
      }

      .breadcrumb {
        display: none;
      }

      .panel {
        padding: 22px 16px;
      }

      .step-line {
        width: 28px;
      }

      .body {
        padding: 20px 16px 60px;
      }
    }
  </style>
</head>

<body>

<?php include "header.php"; ?>

  <div class="body">
    <div class="panel">

      <div class="panel-title">
        <div class="panel-title-icon">📋</div>
        Payment Result
      </div>

      <div class="success-badge">
        <div class="success-icon">
          <svg viewBox="0 0 24 24">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
          </svg>
        </div>
        <div>
          <div class="success-text-main">Payment Approved!</div>
          <div class="success-text-sub">Your transaction was completed successfully.</div>
        </div>
      </div>

      <hr class="divider">

      <div class="info-row">
        <span class="info-label">Order ID</span>
        <span class="info-value"><?= htmlspecialchars($display_order_id) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Date</span>
        <span class="info-value"><?= htmlspecialchars($display_date) ?></span>
      </div>
      <div class="info-row" style="border-bottom:none">
        <span class="info-label">Total Amount</span>
        <span class="info-value"
          style="color:#007bff;font-size:1rem;font-weight:800"><?= htmlspecialchars($display_total) ?></span>
      </div>

      <hr class="divider">

      <div class="section-label">Products Ordered</div>

      <?php if (empty($products)): ?>
        <p style="color:#aaa;font-size:0.88rem;padding:12px 0">No product details available.</p>
      <?php else: ?>
        <div class="products-list">
          <?php foreach ($products as $p): ?>
            <div class="product-card">
              <div class="product-img">
                <img src="<?= htmlspecialchars($p['imageUrl']) ?>" alt="<?= htmlspecialchars($p['Product_name']) ?>"
                  onerror="this.style.display='none';this.parentNode.textContent='📦'">
              </div>
              <div class="product-info">
                <div class="product-name"><?= htmlspecialchars($p['Product_name']) ?></div>
                <div class="product-desc">Unit price: RM <?= number_format($p['unitPrice'], 2) ?></div>
              </div>
              <div class="product-price">
                RM <?= number_format($p['subtotal'], 2) ?>
                <small>×<?= (int) $p['quantity'] ?></small>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <hr class="divider">

      <?php
      $subtotal = array_sum(array_column($products, 'subtotal'));
      $tax = round($subtotal * 0.06);
      ?>
      <div class="order-line"><span>Subtotal (<?= count($products) ?>
          item<?= count($products) !== 1 ? 's' : '' ?>)</span><span>RM <?= number_format($subtotal, 2) ?></span></div>
      <div class="order-line free"><span>Shipping</span><span>Free</span></div>
      <div class="order-line"><span>Tax (6% SST)</span><span>RM <?= number_format($tax, 2) ?></span></div>
      <hr class="divider">
      <div class="order-line total"><span>Total</span><span><?= htmlspecialchars($display_total) ?></span></div>

      <hr class="divider">

      <div class="thankyou">
        Thank you, <strong><?= htmlspecialchars($user_name) ?></strong>! Your payment has been successfully processed.
        You will receive an email confirmation shortly with the full details of your order.
      </div>

      <div>
        <a href="index.php" class="btn-home">
          <svg viewBox="0 0 24 24">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </svg>
          Back to Home
        </a>
        <a href="checkhistory.php" class="btn-history">View Order History</a>
      </div>

    </div>
  </div>
  <?php include "footer.php"; ?>


</body>

</html>