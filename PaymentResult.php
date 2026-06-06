<?php
require_once "php/auth.php";
require_once "php/database.php";

require_once "php/ItemQueries.php";
require_once "php/OrderModal.php";
require_once "php/BillModal.php";
require_once "php/RefundController.php";

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
    <link rel="stylesheet" href="css/paymentresult.css">

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
      <div class="info-row-no-border">
        <span class="info-label">Total Amount</span>
        <span class="info-value total-amount-highlight"><?= htmlspecialchars($display_total) ?></span>
      </div>

      <hr class="divider">

      <div class="section-label">Products Ordered</div>

      <?php if (empty($products)): ?>
        <p class="no-products-message">No product details available.</p>
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