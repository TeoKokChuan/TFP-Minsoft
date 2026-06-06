<?php
require_once "php/auth.php";
require_once "php/database.php";
require_once "php/OrderModal.php";
$user_id = (int) $_SESSION['User_ID'];
$user_name = $_SESSION['User_Name'] ?? 'User';

// ── Load all orders for this user ──────────────────────────────────────────
// customer_order: Order_ID, User_ID, orderStatus, orderDate, TotalPrice, PaymentMethod
$stmt = $conn->prepare("
    SELECT Order_ID, orderStatus, orderDate, TotalPrice, PaymentMethod
    FROM customer_order
    WHERE User_ID = ?
    ORDER BY orderDate DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
  $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Check History — Minsoft Solution</title>
  <ref link="stylesheet" href="css/checkhistory.css">
</head>

<body>

  <?php include "header.php"; ?>

  <main class="main">
    <a href="index.php" class="back-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <polyline points="15 18 9 12 15 6" />
      </svg>
      Back to Home
    </a>

    <div class="page-header">
      <h1 class="page-title">Order History</h1>
      <p class="page-subtitle">View all your orders and their current status</p>
    </div>

    <div class="filters-row">
      <div class="search-wrap">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8" />
          <path d="m21 21-4.35-4.35" />
        </svg>
        <input type="text" class="search-input" id="searchInput" placeholder="Search order ID or payment method…"
          oninput="filterTable()">
      </div>
      <select class="filter-select" id="statusFilter" onchange="filterTable()">
        <option value="">All Status</option>
        <option value="Pending">Pending</option>
        <option value="Processing">Processing</option>
        <option value="Shipped">Shipped</option>
        <option value="Delivered">Delivered</option>
        <option value="Refunded">Refunded</option>
        <option value="pending-refund">Pending Refund</option>
      </select>
    </div>

    <div class="table-card">
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Total Amount</th>
              <th>Payment Method</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="6">
                  <div class="empty-state">You have no orders yet.</div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $o): ?>
                <tr data-order-id="<?= $o['Order_ID'] ?>" data-status="<?= htmlspecialchars($o['orderStatus']) ?>"
                  data-payment="<?= htmlspecialchars($o['PaymentMethod']) ?>">
                  <td><span class="inv-id">ORD-<?= str_pad($o['Order_ID'], 5, '0', STR_PAD_LEFT) ?></span></td>
                  <td class="table-text-muted"><?= date('d M Y, h:i A', strtotime($o['orderDate'])) ?></td>
                  <td><span class="amount">RM <?= number_format($o['TotalPrice'], 2) ?></span></td>
                  <td class="table-text-muted"><?= htmlspecialchars($o['PaymentMethod']) ?></td>
                  <td><span
                      class="status <?= htmlspecialchars($o['orderStatus']) ?>"><?= htmlspecialchars($o['orderStatus']) ?></span>
                  </td>
                  <td>
                    <a href="HistoryDetail.php?id=<?= $o['Order_ID'] ?>" class="btn-view">
                      View Detail
                      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6" />
                      </svg>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="pagination">
        <div class="page-info">
          Showing <strong id="showingCount">—</strong> of <strong id="totalCount">0</strong> orders
        </div>
        <div class="page-btns">
          <button class="page-btn" id="prevBtn" onclick="changePage(-1)" disabled>‹</button>
          <button class="page-btn active" id="pageNum">1</button>
          <button class="page-btn" id="nextBtn" onclick="changePage(1)">›</button>
        </div>
      </div>
    </div>
  </main>

  <?php include "footer.php"; ?>

 <script src="js/checkhistory.js"></script>

</body>

</html>