<?php
session_start();
include "database.php";

if (!isset($_SESSION['User_ID'])) {
  header('Location: login.php');
  exit;
}
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
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #ffffff;
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

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topbar-label {
      font-size: 0.82rem;
      color: #555;
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

    .main {
      max-width: 1300px;
      margin: 0 auto;
      padding: 36px 40px;
    }

    .page-header {
      margin-bottom: 28px;
    }

    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: #222;
      letter-spacing: -0.3px;
    }

    .page-subtitle {
      font-size: 0.87rem;
      color: #aaa;
      margin-top: 4px;
    }

    .filters-row {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 22px;
      flex-wrap: wrap;
    }

    .search-wrap {
      position: relative;
      flex: 1;
      min-width: 220px;
    }

    .search-wrap svg {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
      width: 15px;
      height: 15px;
      pointer-events: none;
    }

    .search-input {
      width: 100%;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 10px 14px 10px 36px;
      font-size: 0.87rem;
      color: #222;
      outline: none;
      transition: 0.3s;
    }

    .search-input::placeholder {
      color: #bbb;
    }

    .search-input:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .filter-select {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 10px 32px 10px 13px;
      font-size: 0.87rem;
      color: #222;
      outline: none;
      cursor: pointer;
      appearance: none;
      transition: 0.3s;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23aaa' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
    }

    .filter-select:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .table-card {
      background: #f8f9fa;
      border: 1px solid #eaeaea;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
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
      margin-bottom: 16px;
      text-decoration: none;
    }

    .back-btn:hover {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
    }

    .table-scroll {
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #f8f9fa;
      border-bottom: 1px solid #eaeaea;
    }

    th {
      padding: 12px 18px;
      text-align: left;
      font-size: 0.72rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #aaa;
      white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid #f5f5f5;
      transition: background 0.15s;
    }

    tbody tr:last-child {
      border-bottom: none;
    }

    tbody tr:hover {
      background: #f0f6ff;
    }

    td {
      padding: 14px 18px;
      font-size: 0.87rem;
      vertical-align: middle;
    }

    .inv-id {
      font-weight: 700;
      font-size: 0.84rem;
      color: #007bff;
      letter-spacing: 0.02em;
    }

    .amount {
      font-weight: 800;
      color: #000;
    }

    .status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 20px;
      padding: 4px 11px;
      font-size: 0.74rem;
      font-weight: 600;
      white-space: nowrap;
    }

    .status::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .status.Pending {
      background: rgba(255, 193, 7, 0.12);
      color: #856404;
    }

    .status.Pending::before {
      background: #ffc107;
    }

    .status.Processing {
      background: rgba(0, 123, 255, 0.1);
      color: #007bff;
    }

    .status.Processing::before {
      background: #007bff;
    }

    .status.Shipped {
      background: rgba(111, 66, 193, 0.1);
      color: #6f42c1;
    }

    .status.Shipped::before {
      background: #6f42c1;
    }

    .status.Delivered {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }

    .status.Delivered::before {
      background: #28a745;
    }

    .status.Refunded {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    .status.Refunded::before {
      background: #dc3545;
    }

    .status.pending-refund {
      background: rgba(255, 193, 7, 0.12);
      color: #856404;
    }

    .status.pending-refund::before {
      background: #ffc107;
    }

    .btn-view {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 7px 14px;
      border-radius: 8px;
      border: none;
      background-color: #111;
      color: #fff;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
    }

    .btn-view:hover {
      background-color: #007bff;
    }

    .pagination {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      border-top: 1px solid #eee;
      background: #f8f9fa;
    }

    .page-info {
      font-size: 0.82rem;
      color: #aaa;
    }

    .page-info strong {
      color: #222;
    }

    .page-btns {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .page-btn {
      width: 32px;
      height: 32px;
      border: 1px solid #eee;
      border-radius: 7px;
      background: #fff;
      color: #888;
      font-size: 0.82rem;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: 0.3s;
    }

    .page-btn:hover,
    .page-btn.active {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
    }

    .page-btn:disabled {
      opacity: 0.3;
      pointer-events: none;
    }

    .empty-state {
      text-align: center;
      padding: 48px 20px;
      color: #bbb;
      font-size: 0.9rem;
    }

    @media (max-width: 768px) {
      .main {
        padding: 20px 16px;
      }

      .filters-row {
        flex-direction: column;
        align-items: stretch;
      }

      .topbar-label {
        display: none;
      }
    }
  </style>
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
                  <td style="color:#555"><?= date('d M Y, h:i A', strtotime($o['orderDate'])) ?></td>
                  <td><span class="amount">RM <?= number_format($o['TotalPrice'], 2) ?></span></td>
                  <td style="color:#555"><?= htmlspecialchars($o['PaymentMethod']) ?></td>
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

  <script>
    const perPage = 10;
    let currentPage = 1;
    let allRows = Array.from(document.querySelectorAll('#tableBody tr[data-order-id]'));
    let filtered = [...allRows];

    function filterTable() {
      const q = document.getElementById('searchInput').value.toLowerCase();
      const status = document.getElementById('statusFilter').value;
      filtered = allRows.filter(row => {
        const id = row.dataset.orderId?.toLowerCase() || '';
        const payment = row.dataset.payment?.toLowerCase() || '';
        const st = row.dataset.status || '';
        const matchQ = !q || id.includes(q) || payment.includes(q);
        const matchS = !status || st === status;
        return matchQ && matchS;
      });
      currentPage = 1;
      render();
    }

    function render() {
      const total = filtered.length;
      const start = (currentPage - 1) * perPage;
      const end = Math.min(start + perPage, total);

      allRows.forEach(r => r.style.display = 'none');
      filtered.slice(start, end).forEach(r => r.style.display = '');

      document.getElementById('showingCount').textContent = total ? (start + 1) + '–' + end : '0';
      document.getElementById('totalCount').textContent = total;
      document.getElementById('pageNum').textContent = currentPage;
      document.getElementById('prevBtn').disabled = currentPage === 1;
      document.getElementById('nextBtn').disabled = end >= total;

      // Empty state
      const tbody = document.getElementById('tableBody');
      let emptyRow = document.getElementById('empty-row');
      if (total === 0) {
        if (!emptyRow) {
          emptyRow = document.createElement('tr');
          emptyRow.id = 'empty-row';
          emptyRow.innerHTML = '<td colspan="6"><div class="empty-state">No orders match your filters.</div></td>';
          tbody.appendChild(emptyRow);
        }
      } else {
        if (emptyRow) emptyRow.remove();
      }
    }

    function changePage(dir) { currentPage += dir; render(); }

    render();
  </script>

</body>

</html>