<?php

require_once '../includes/auth.php';
require_once '../includes/database.php';

require_once '../models/OrderModel.php';
require_once '../models/BillModel.php';
require_once '../models/ProductModel.php';

require_once '../controllers/RefundController.php';

$order_id = (int)($_GET['id'] ?? 0);
$user_id  = $_SESSION['User_ID'];

$order = getOrder($conn,$order_id,$user_id);

if(!$order){
    header("Location: checkhistory.php");
    exit;
}

$bill  = getBill($conn,$order_id);
$items = getOrderItems($conn,$order_id);

$result = processRefund(
    $conn,
    $order_id,
    $user_id,
    $order
);

$refund_success = $result['success'];
$refund_error   = $result['error'];
$refund_ref     = $result['reference'];

if ($refund_success) {
    $order = getOrder($conn, $order_id, $user_id);
    $items = getOrderItems($conn, $order_id);
}

$status = strtolower($order['orderStatus']);

$sc = match ($status) {
    'completed', 'delivered' => 'success',
    'processing'             => 'processing',
    'shipped'                => 'shipped',
    'pending'                => 'pending',
    'pending-refund'         => 'refund',
    'refunded'               => 'refunded',
    default                  => 'pending'
};

>?
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Detail — Minsoft Solution</title>
  <link rel="stylesheet" href="css/PaymentResult.css">
    
</head>

<body>

  <?php include "header.php"; ?>

  <main class="main">

    <a href="checkhistory.php" class="back-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <polyline points="15 18 9 12 15 6" />
      </svg>
      Back to History
    </a>

    <div class="hero-card">
      <div class="hero-top">
        <div>
          <div class="hero-left-title">ORD-<?= str_pad($order['Order_ID'], 5, '0', STR_PAD_LEFT) ?></div>
          <div class="hero-left-sub">Placed on <?= date('d M Y, h:i A', strtotime($order['orderDate'])) ?></div>
        </div>
        <div>
          <div class="hero-amount <?= $sc ?>">RM <?= number_format($order['TotalPrice'], 2) ?></div>
          <div class="hero-amount-label">Total Charged</div>
        </div>
      </div>

      <div class="meta-grid">
        <div class="meta-item">
          <label>Status</label>
          <span><span class="status <?= $sc ?>"><?= htmlspecialchars($order['orderStatus']) ?></span></span>
        </div>
        <div class="meta-item">
          <label>Payment Method</label>
          <span><?= htmlspecialchars($order['PaymentMethod']) ?></span>
        </div>
        <div class="meta-item">
          <label>Order ID</label>
          <span
            style="color:#007bff;font-weight:700">ORD-<?= str_pad($order['Order_ID'], 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <?php if ($bill): ?>
          <div class="meta-item" style="grid-column: 1 / -1">
            <label>Shipping Address</label>
            <span><?= htmlspecialchars($bill['Shipping_address']) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="items-card">
      <div class="items-header">
        <div>
          <div class="items-title">Items Ordered</div>
          <div class="items-subtitle">
            <?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?> in this order
            <?php if (in_array($order['orderStatus'], ['Processing', 'Shipped', 'Delivered'])): ?>
              · <span style="color:#007bff;font-size:0.75rem">Select items to refund</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (empty($items)): ?>
        <div style="padding:24px;text-align:center;color:#aaa;font-size:0.88rem">No items found for this order.</div>
      <?php else: ?>
        <?php $canRefund = in_array($order['orderStatus'], ['Processing', 'Shipped', 'Delivered']); ?>
        <?php foreach ($items as $idx => $item): ?>
          <?php
          $isRefunded = ($item['item_status'] === 'Refunded');
          // 🚀 记录是否正在审核中
          $isPendingRefund = (!empty($item['refund_review']) && !$isRefunded);
          ?>

          <div class="line-item <?= ($canRefund && !$isRefunded && !$isPendingRefund) ? 'selectable' : '' ?>" <?= ($canRefund && !$isRefunded && !$isPendingRefund) ? "onclick=\"toggleItem($idx)\"" : '' ?> data-idx="<?= $idx ?>">
            <?php if ($canRefund): ?>
              <?php if (!$isRefunded && !$isPendingRefund): ?>
                <div style="display:flex; flex-direction:column; gap:6px; align-items:center;">
                  <div class="cb" id="cb-<?= $idx ?>"></div>
                  <?php if ($item['quantity'] > 1): ?>
                    <select id="qty-<?= $idx ?>"
                      style="display:none; width:45px; font-size:0.75rem; padding:2px; border-radius:4px; border:1px solid #ddd;"
                      onchange="updateRefundTotal()" onclick="event.stopPropagation()">
                      <?php for ($q = 1; $q <= $item['quantity']; $q++): ?>
                        <option value="<?= $q ?>" <?= $q == $item['quantity'] ? 'selected' : '' ?>><?= $q ?></option>
                      <?php endfor; ?>
                    </select>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <div style="width:20px; height:20px; flex-shrink:0;"></div>
              <?php endif; ?>
            <?php endif; ?>

            <div class="li-thumb">
              <img src="<?= htmlspecialchars($item['imageUrl']) ?>" alt="<?= htmlspecialchars($item['Product_name']) ?>"
                onerror="this.style.display='none';this.parentNode.textContent='📦'">
            </div>
            <div class="li-info">
              <div class="li-name" style="<?= $isRefunded ? 'text-decoration: line-through; color: #aaa;' : '' ?>">
                <?= htmlspecialchars($item['Product_name']) ?>
              </div>
              <div class="li-desc">Unit price: RM <?= number_format($item['unitPrice'], 2) ?></div>
            </div>
            <div class="li-price" style="<?= $isRefunded ? 'color: #aaa;' : '' ?>">
              RM <?= number_format($item['subtotal'], 2) ?>
              <small>×<?= (int) $item['quantity'] ?></small>

              <?php if ($isRefunded): ?>
                <div style="color: #dc3545; font-size: 0.75rem; font-weight: bold; margin-top: 4px;">⊗ Refunded</div>
              <?php elseif ($isPendingRefund): ?>
                <div style="color: #856404; font-size: 0.75rem; font-weight: bold; margin-top: 4px;">⏳ Pending Review</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if (in_array($order['orderStatus'], ['Processing', 'Shipped', 'Delivered'])): ?>
        <div class="refund-summary-bar">
          <span>Selected for refund: <strong id="refundTotalDisplay">RM 0.00</strong></span>
        </div>
      <?php endif; ?>

      <?php
      $subtotal = 0;
      foreach ($items as $i) {
        if ($i['item_status'] !== 'Refunded') {
          $subtotal += $i['subtotal'];
        }
      }
      $tax = round($subtotal * 0.06, 2);
      ?>
      <div class="totals-footer">
        <div class="total-line"><span>Subtotal</span><span>RM <?= number_format($subtotal, 2) ?></span></div>
        <div class="total-line free"><span>Shipping</span><span>Free</span></div>
        <div class="total-line"><span>Tax (6% SST)</span><span>RM <?= number_format($tax, 2) ?></span></div>
        <div class="total-line grand">
          <span>Total</span>
          <span>RM <?= number_format($order['TotalPrice'], 2) ?></span>
        </div>
      </div>
    </div>

    <div class="action-bar">
      <a href="checkhistory.php" class="btn-ghost">← Back to History</a>
      <?php if (in_array($order['orderStatus'], ['Processing', 'Shipped', 'Delivered'])): ?>
        <button type="button" class="btn-refund" id="refundBtn" disabled onclick="openRefundModal()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="22" y1="2" x2="11" y2="13" />
            <polygon points="22 2 15 22 11 13 2 9 22 2" />
          </svg>
          Request Refund
        </button>
      <?php elseif ($order['orderStatus'] === 'Pending'): ?>
        <button type="button" class="btn-refund-disabled" disabled title="Refund available once order is being processed">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="22" y1="2" x2="11" y2="13" />
            <polygon points="22 2 15 22 11 13 2 9 22 2" />
          </svg>
          Request Refund
        </button>
      <?php elseif ($order['orderStatus'] === 'pending-refund'): ?>
        <span class="refund-pending-badge">⏳ Refund Pending Review</span>
      <?php elseif ($order['orderStatus'] === 'Refunded'): ?>
        <span class="refund-done-badge">✔ Refunded</span>
      <?php endif; ?>
    </div>

    <?php if ($refund_error): ?>
      <div class="alert-error">⚠ <?= htmlspecialchars($refund_error) ?></div>
    <?php endif; ?>

  </main>

  <div class="overlay" id="refundOverlay" onclick="if(event.target===this)closeRefundModal()">
    <div class="modal">
      <div class="modal-icon">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
          <circle cx="9" cy="7" r="4" />
          <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
          <path d="M16 3.13a4 4 0 0 1 0 7.75" />
        </svg>
      </div>
      <div class="modal-title">Send Refund Request to Admin</div>
      <div class="modal-desc">Your request will be reviewed by our billing team. You will be notified once a decision is
        made.</div>
      <div class="modal-note">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span>This does not guarantee a refund. Eligible requests are processed within <strong>5–10 business
            days</strong>.</span>
      </div>

      <form method="POST" action="HistoryDetail.php?id=<?= $order_id ?>" enctype="multipart/form-data">
        <input type="hidden" name="request_refund" value="1">
        <div id="hiddenTxInputs"></div>
        <div id="modalItemsList"
          style="background:#f8f9fa;border:1px solid #eaeaea;border-radius:8px;padding:10px 14px;margin-bottom:14px;max-height:160px;overflow-y:auto;">
        </div>
        <div
          style="display:flex;justify-content:space-between;align-items:center;padding:8px 0 14px;border-bottom:1px solid #eee;margin-bottom:14px;">
          <span style="font-size:0.82rem;color:#888;">Requested Refund</span>
          <span style="font-weight:800;color:#007bff;font-size:1rem;" id="modalTotalDisplay">RM 0.00</span>
        </div>
        <div class="refund-reason-field">
          <label for="refundReason">Refund Reason <span style="color:#dc3545">*</span></label>
          <textarea id="refundReason" name="refund_reason" placeholder="Write your reason for requesting a refund..."
            required minlength="5"></textarea>
        </div>
        <div class="refund-reason-field">
          <label for="refundImage">
            Supporting Image
            <span style="color:#aaa;font-weight:400;text-transform:none;font-size:0.72rem;letter-spacing:0">(optional ·
              JPG, PNG, WEBP · max 5MB)</span>
          </label>
          <input type="file" id="refundImage" name="refund_image" accept="image/jpeg,image/png,image/jpg,image/webp"
            style="width:100%;padding:8px 10px;border:1px solid #ddd;border-radius:8px;font-size:0.85rem;background:#fff;cursor:pointer;font-family:'Segoe UI',sans-serif;"
            onchange="previewImage(this)">
          <div id="imagePreview" style="display:none;margin-top:10px;">
            <img id="previewImg" src="" alt="Preview"
              style="max-width:100%;max-height:140px;border-radius:8px;border:1px solid #eee;object-fit:contain;">
            <div style="font-size:0.72rem;color:#aaa;margin-top:4px;" id="previewName"></div>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn-cancel-modal" onclick="closeRefundModal()">Cancel</button>
          <button type="submit" class="btn-send-request">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <line x1="22" y1="2" x2="11" y2="13" />
              <polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
            Send Request
          </button>
        </div>
      </form>
    </div>
  </div>

  <?php if ($refund_success): ?>
    <div class="notif-overlay open" id="notifOverlay">
      <div class="notif-modal">
        <div class="notif-icon-wrap">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12" />
          </svg>
        </div>
        <div class="notif-title">Refund Request Sent!</div>
        <div class="notif-desc">Your request has been submitted to our billing team. A confirmation will be sent to your
          email.</div>
        <div class="notif-ref"><?= htmlspecialchars($refund_ref) ?></div>
        <div class="notif-steps">
          <div class="notif-step">
            <div class="notif-step-num">1</div><span>Admin team receives and reviews your request</span>
          </div>
          <div class="notif-step">
            <div class="notif-step-num">2</div><span>You'll receive an email update within 2 business days</span>
          </div>
          <div class="notif-step">
            <div class="notif-step-num">3</div><span>If approved, refund is processed within 5–10 business days</span>
          </div>
        </div>
        <button class="btn-notif-close" onclick="document.getElementById('notifOverlay').classList.remove('open')">Got
          it</button>
      </div>
    </div>
  <?php endif; ?>

  <?php include "footer.php"; ?>

  <script src="js/HistoryDetail.js"></script>

</body>

</html>