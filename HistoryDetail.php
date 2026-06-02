<?php
session_start();
include "database.php";

if (!isset($_SESSION['User_ID'])) {
  header('Location: login.php');
  exit;
}
$user_id = (int) $_SESSION['User_ID'];
$order_id = (int) ($_GET['id'] ?? 0);

if (!$order_id) {
  header('Location: checkhistory.php');
  exit;
}

$ostmt = $conn->prepare("SELECT Order_ID, orderStatus, orderDate, TotalPrice, PaymentMethod FROM customer_order WHERE Order_ID = ? AND User_ID = ?");
$ostmt->bind_param('ii', $order_id, $user_id);
$ostmt->execute();
$order = $ostmt->get_result()->fetch_assoc();
$ostmt->close();

if (!$order) {
  header('Location: checkhistory.php');
  exit;
}

$bstmt = $conn->prepare("SELECT Shipping_address, Bill_Status FROM bill_master WHERE Bill_ID = ?");
$bstmt->bind_param('i', $order_id);
$bstmt->execute();
$bill = $bstmt->get_result()->fetch_assoc();
$bstmt->close();

$tstmt = $conn->prepare("
    SELECT bt.transaction_ID, bt.Product_ID, bt.quantity, bt.unitPrice, bt.subtotal, bt.item_status, bt.refund_review, p.Product_name, p.imageUrl
    FROM bill_transaction bt
    JOIN product p ON bt.Product_ID = p.Product_ID
    WHERE bt.Bill_ID = ?
");
$tstmt->bind_param('i', $order_id);
$tstmt->execute();
$items_result = $tstmt->get_result();
$items = [];
while ($row = $items_result->fetch_assoc()) {
  $items[] = $row;
}
$tstmt->close();

$refund_success = false;
$refund_error = '';
$refund_ref = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_refund'])) {
  $reason = trim($_POST['refund_reason'] ?? '');
  $refund_items = $_POST['refund_items'] ?? [];
  $refund_qtys = $_POST['refund_qty'] ?? [];

  if (strlen($reason) < 5) {
    $refund_error = 'Please enter a refund reason (at least 5 characters).';
  } elseif (!in_array($order['orderStatus'], ['Processing', 'Shipped', 'Delivered'])) {
    $refund_error = 'Refund requests are only available for orders that are being processed, shipped, or delivered.';
  } elseif (empty($refund_items)) {
    $refund_error = 'Please select at least one item to refund.';
  } else {
    $image_filename = null;
    if (!empty($_FILES['refund_image']['name'])) {
      $allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
      if (!in_array($_FILES['refund_image']['type'], $allowed)) {
        $refund_error = 'Only JPG, PNG, or WEBP images are allowed.';
      } elseif ($_FILES['refund_image']['size'] > 5 * 1024 * 1024) {
        $refund_error = 'Image must be under 5MB.';
      } else {
        $ext = pathinfo($_FILES['refund_image']['name'], PATHINFO_EXTENSION);
        $image_filename = 'refund_' . $order_id . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['refund_image']['tmp_name'], 'uploads/' . $image_filename);
      }
    }

    if (empty($refund_error)) {
      // 🚀 核心修复：在这里处理顾客请求的退货数量（支持拆分同款商品）
      foreach ($refund_items as $pid) {
        $pid = intval($pid);
        $req_qty = isset($refund_qtys[$pid]) ? intval($refund_qtys[$pid]) : 1;

        $tx_res = $conn->query("SELECT * FROM bill_transaction WHERE Bill_ID = $order_id AND Product_ID = $pid AND (item_status IS NULL OR item_status != 'Refunded') LIMIT 1");
        if ($tx_row = $tx_res->fetch_assoc()) {
          $max_qty = intval($tx_row['quantity']);
          $unit_price = floatval($tx_row['unitPrice']);
          $tx_id = $tx_row['transaction_ID'];

          if ($req_qty > 0 && $req_qty < $max_qty) {
            // 顾客只想退部分，数据库拆成两行
            $keep_qty = $max_qty - $req_qty;
            $keep_sub = $keep_qty * $unit_price;
            $conn->query("UPDATE bill_transaction SET quantity = $keep_qty, subtotal = $keep_sub WHERE transaction_ID = $tx_id");

            $ref_sub = $req_qty * $unit_price;
            $b_ref = $conn->real_escape_string($tx_row['build_ref'] ?? '');
            $ins = $conn->prepare("INSERT INTO bill_transaction (Bill_ID, Product_ID, quantity, unitPrice, subtotal, refund_review, refund_image, build_ref) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->bind_param('iiiddsss', $order_id, $pid, $req_qty, $unit_price, $ref_sub, $reason, $image_filename, $b_ref);
            $ins->execute();
          } else {
            // 退全部
            $upd = $conn->prepare("UPDATE bill_transaction SET refund_review = ?, refund_image = ? WHERE transaction_ID = ?");
            $upd->bind_param('ssi', $reason, $image_filename, $tx_id);
            $upd->execute();
          }
        }
      }

      $rstmt = $conn->prepare("UPDATE customer_order SET orderStatus = 'pending-refund' WHERE Order_ID = ? AND User_ID = ?");
      $rstmt->bind_param('ii', $order_id, $user_id);
      $rstmt->execute();
      $rstmt->close();

      $refund_ref = 'REF-' . strtoupper(substr(md5($order_id . time()), 0, 8));
      $refund_success = true;

      // 刷新数据
      $ostmt2 = $conn->prepare("SELECT Order_ID, orderStatus, orderDate, TotalPrice, PaymentMethod FROM customer_order WHERE Order_ID = ? AND User_ID = ?");
      $ostmt2->bind_param('ii', $order_id, $user_id);
      $ostmt2->execute();
      $order = $ostmt2->get_result()->fetch_assoc();
      $ostmt2->close();

      // 重新抓取物品以反映拆行
      $tstmt2 = $conn->prepare("SELECT bt.transaction_ID, bt.Product_ID, bt.quantity, bt.unitPrice, bt.subtotal, bt.item_status, bt.refund_review, p.Product_name, p.imageUrl FROM bill_transaction bt JOIN product p ON bt.Product_ID = p.Product_ID WHERE bt.Bill_ID = ?");
      $tstmt2->bind_param('i', $order_id);
      $tstmt2->execute();
      $items_result = $tstmt2->get_result();
      $items = [];
      while ($row = $items_result->fetch_assoc()) {
        $items[] = $row;
      }
    }
  }
}

$status_class = ['Pending' => 'pending', 'Processing' => 'processing', 'Shipped' => 'shipped', 'Delivered' => 'delivered', 'Refunded' => 'refunded', 'pending-refund' => 'pending-refund'];
$sc = $status_class[$order['orderStatus']] ?? 'pending';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Detail — Minsoft Solution</title>
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
      max-width: 900px;
      margin: 0 auto;
      padding: 36px 32px;
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
      margin-bottom: 24px;
      text-decoration: none;
    }

    .back-btn:hover {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
    }

    .hero-card {
      background: #fff;
      border: 1px solid #eaeaea;
      border-radius: 12px;
      padding: 28px;
      margin-bottom: 18px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
      animation: fadeUp 0.35s ease both;
    }

    .hero-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent, #007bff, transparent);
    }

    .hero-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 14px;
    }

    .hero-left-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: #111;
    }

    .hero-left-sub {
      font-size: 0.83rem;
      color: #aaa;
      margin-top: 3px;
    }

    .hero-amount {
      font-size: 1.85rem;
      font-weight: 800;
      letter-spacing: -1px;
      line-height: 1;
    }

    .hero-amount.pending {
      color: #856404;
    }

    .hero-amount.processing {
      color: #007bff;
    }

    .hero-amount.shipped {
      color: #6f42c1;
    }

    .hero-amount.delivered {
      color: #28a745;
    }

    .hero-amount.refunded {
      color: #dc3545;
    }

    .hero-amount.pending-refund {
      color: #856404;
    }

    .hero-amount-label {
      font-size: 0.73rem;
      color: #aaa;
      margin-top: 3px;
      text-align: right;
    }

    .meta-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
    }

    .meta-item label {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #bbb;
      display: block;
      margin-bottom: 2px;
      font-weight: 600;
    }

    .meta-item span {
      font-size: 0.88rem;
      font-weight: 500;
      color: #222;
    }

    .status {
      display: inline-flex;
      align-items: center;
      gap: 5px;
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

    .status.pending {
      background: rgba(255, 193, 7, 0.12);
      color: #856404;
    }

    .status.pending::before {
      background: #ffc107;
    }

    .status.processing {
      background: rgba(0, 123, 255, 0.1);
      color: #007bff;
    }

    .status.processing::before {
      background: #007bff;
    }

    .status.shipped {
      background: rgba(111, 66, 193, 0.1);
      color: #6f42c1;
    }

    .status.shipped::before {
      background: #6f42c1;
    }

    .status.delivered {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }

    .status.delivered::before {
      background: #28a745;
    }

    .status.refunded {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    .status.refunded::before {
      background: #dc3545;
    }

    .status.pending-refund {
      background: rgba(255, 193, 7, 0.12);
      color: #856404;
    }

    .status.pending-refund::before {
      background: #ffc107;
    }

    .items-card {
      background: #fff;
      border: 1px solid #eaeaea;
      border-radius: 12px;
      overflow: hidden;
      margin-bottom: 18px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
      animation: fadeUp 0.4s ease 0.08s both;
    }

    .items-header {
      padding: 16px 22px;
      border-bottom: 1px solid #f0f0f0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f8f9fa;
    }

    .items-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: #111;
    }

    .items-subtitle {
      font-size: 0.77rem;
      color: #aaa;
      margin-top: 2px;
    }

    .line-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 22px;
      border-bottom: 1px solid #f5f5f5;
      background: #fff;
    }

    .line-item:last-child {
      border-bottom: none;
    }

    .line-item.selectable {
      cursor: pointer;
      transition: background 0.15s;
    }

    .line-item.selectable:hover {
      background: #f0f6ff;
    }

    .line-item.selected {
      background: #f0f6ff;
      border-left: 3px solid #007bff;
      padding-left: 19px;
    }

    .cb {
      width: 20px;
      height: 20px;
      border: 2px solid #ddd;
      border-radius: 6px;
      background: #fff;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: 0.2s;
    }

    .cb.checked {
      background: #007bff;
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
    }

    .cb.checked::after {
      content: '';
      width: 5px;
      height: 9px;
      border: 2px solid #fff;
      border-top: none;
      border-left: none;
      transform: rotate(45deg) translateY(-1px);
      display: block;
    }

    .refund-summary-bar {
      padding: 12px 22px;
      border-top: 1px solid #f0f0f0;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .refund-summary-bar span {
      font-size: 0.85rem;
      color: #888;
    }

    .refund-summary-bar strong {
      color: #007bff;
      font-size: 1rem;
      font-weight: 800;
    }

    .li-thumb {
      width: 52px;
      height: 52px;
      border-radius: 8px;
      background: #f8f9fa;
      border: 1px solid #eee;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      flex-shrink: 0;
      overflow: hidden;
    }

    .li-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 8px;
      display: block;
    }

    .li-info {
      flex: 1;
    }

    .li-name {
      font-size: 0.88rem;
      font-weight: 600;
      color: #111;
    }

    .li-desc {
      font-size: 0.77rem;
      color: #aaa;
      margin-top: 1px;
    }

    .li-price {
      font-weight: 800;
      font-size: 0.9rem;
      color: #000;
      flex-shrink: 0;
      text-align: right;
    }

    .li-price small {
      display: block;
      font-size: 0.7rem;
      color: #aaa;
      font-weight: 400;
      margin-top: 2px;
    }

    .totals-footer {
      padding: 16px 22px;
      border-top: 1px solid #f0f0f0;
      background: #f8f9fa;
    }

    .total-line {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      padding: 4px 0;
      color: #888;
    }

    .total-line span:last-child {
      color: #222;
      font-weight: 600;
    }

    .total-line.free span:last-child {
      color: #28a745;
    }

    .total-line.grand {
      font-size: 1.05rem;
      padding: 8px 0 0;
      border-top: 1px solid #eee;
      margin-top: 6px;
    }

    .total-line.grand span:first-child {
      color: #111;
      font-weight: 700;
    }

    .total-line.grand span:last-child {
      color: #007bff;
      font-size: 1.15rem;
      font-weight: 800;
    }

    .action-bar {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      animation: fadeUp 0.4s ease 0.14s both;
    }

    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background-color: #111;
      border: none;
      border-radius: 8px;
      padding: 11px 22px;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
    }

    .btn-primary:hover {
      background-color: #007bff;
    }

    .btn-ghost {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 11px 22px;
      font-size: 0.88rem;
      color: #888;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
    }

    .btn-ghost:hover {
      border-color: #007bff;
      color: #007bff;
      background: #f0f6ff;
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

    .btn-refund {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(220, 53, 69, 0.07);
      border: 1px solid rgba(220, 53, 69, 0.25);
      border-radius: 8px;
      padding: 10px 18px;
      font-size: 0.87rem;
      font-weight: 600;
      color: #dc3545;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-refund:hover {
      background: rgba(220, 53, 69, 0.14);
      border-color: #dc3545;
    }

    .btn-refund:disabled {
      opacity: 0.4;
      cursor: not-allowed;
      pointer-events: none;
    }

    .btn-refund-disabled {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(180, 180, 180, 0.07);
      border: 1px solid rgba(180, 180, 180, 0.3);
      border-radius: 8px;
      padding: 10px 18px;
      font-size: 0.87rem;
      font-weight: 600;
      color: #bbb;
      cursor: not-allowed;
      opacity: 0.6;
    }

    .refund-pending-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 193, 7, 0.12);
      border: 1px solid rgba(255, 193, 7, 0.3);
      border-radius: 8px;
      padding: 10px 16px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #856404;
    }

    .refund-done-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(40, 167, 69, 0.1);
      border: 1px solid rgba(40, 167, 69, 0.25);
      border-radius: 8px;
      padding: 10px 16px;
      font-size: 0.85rem;
      font-weight: 600;
      color: #28a745;
    }

    .alert-error {
      max-width: 900px;
      margin: 12px auto 0;
      padding: 0 32px;
      font-size: 0.84rem;
      color: #dc3545;
    }

   .overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  backdrop-filter: blur(4px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;

  opacity: 0;
  pointer-events: none;
  transition: opacity .22s;
}

.overlay.open {
  opacity: 1;
  pointer-events: all;
}

.modal {
  background: #fff;
  border: 1px solid #eaeaea;
  border-radius: 12px;
  width: 480px;
  max-width: 95vw;

  max-height: 90vh;
  overflow-y: auto;

  padding: 28px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
  transform: translateY(16px) scale(0.97);
  transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Prevent page scrolling while modal is open */
body.modal-open {
  overflow: hidden;
}

    .modal-icon {
      width: 46px;
      height: 46px;
      border-radius: 10px;
      background: #f0f6ff;
      border: 1px solid #cce0ff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
      color: #007bff;
    }

    .modal-title {
      font-size: 1rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 8px;
    }

    .modal-desc {
      font-size: 0.84rem;
      color: #666;
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .modal-note {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: #f0f6ff;
      border: 1px solid #cce0ff;
      border-radius: 8px;
      padding: 11px 13px;
      font-size: 0.81rem;
      color: #555;
      line-height: 1.6;
      margin-bottom: 18px;
    }

    .modal-note svg {
      color: #007bff;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .refund-reason-field {
      margin-bottom: 18px;
    }

    .refund-reason-field label {
      display: block;
      font-size: 0.75rem;
      font-weight: 600;
      color: #555;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    .refund-reason-field textarea {
      width: 100%;
      min-height: 90px;
      resize: none;
      border-radius: 8px;
      padding: 10px 13px;
      font-family: 'Segoe UI', sans-serif;
      font-size: 0.85rem;
      background: #fff;
      border: 1px solid #ddd;
      color: #222;
      outline: none;
      transition: 0.3s;
    }

    .refund-reason-field textarea:focus {
      border-color: #007bff;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .refund-reason-field textarea::placeholder {
      color: #bbb;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
    }

    .btn-send-request {
      flex: 1;
      background-color: #111;
      border: none;
      border-radius: 8px;
      padding: 12px 18px;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
      transition: 0.3s;
    }

    .btn-send-request:hover {
      background-color: #007bff;
    }

    .btn-cancel-modal {
      flex: 1;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 12px 18px;
      font-size: 0.88rem;
      color: #888;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-cancel-modal:hover {
      border-color: #aaa;
      color: #222;
      background: #f8f9fa;
    }

    .notif-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(4px);
      z-index: 200;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.22s;
    }

    .notif-overlay.open {
      opacity: 1;
      pointer-events: all;
    }

    .notif-modal {
      background: #fff;
      border: 1px solid #c3e6cb;
      border-radius: 12px;
      width: 420px;
      max-width: 95vw;
      padding: 32px 28px;
      text-align: center;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    }

    .notif-icon-wrap {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: #f6fff9;
      border: 2px solid #c3e6cb;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      color: #28a745;
      animation: pulse 1.8s ease infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.2);
      }

      50% {
        box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
      }
    }

    .notif-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 8px;
    }

    .notif-desc {
      font-size: 0.85rem;
      color: #666;
      line-height: 1.7;
      margin-bottom: 8px;
    }

    .notif-ref {
      display: inline-block;
      background: #f0f6ff;
      border: 1px solid #cce0ff;
      border-radius: 7px;
      padding: 5px 13px;
      font-size: 0.78rem;
      color: #007bff;
      font-weight: 700;
      letter-spacing: 0.04em;
      margin-bottom: 20px;
      margin-top: 6px;
    }

    .notif-steps {
      background: #f8f9fa;
      border: 1px solid #eaeaea;
      border-radius: 10px;
      padding: 14px 16px;
      text-align: left;
      margin-bottom: 22px;
    }

    .notif-step {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 0.81rem;
      color: #666;
      padding: 4px 0;
    }

    .notif-step-num {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #f0f6ff;
      border: 1px solid #cce0ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.67rem;
      font-weight: 700;
      color: #007bff;
      flex-shrink: 0;
    }

    .btn-notif-close {
      width: 100%;
      background-color: #111;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn-notif-close:hover {
      background: #007bff;
    }

    @media (max-width: 640px) {
      .main {
        padding: 20px 16px;
      }

      .meta-grid {
        grid-template-columns: 1fr 1fr;
      }

      .hero-top {
        flex-direction: column;
      }

      .topbar-label {
        display: none;
      }
    }
  </style>
</head>

<body>

  <?php include "paymentHeader.php"; ?>

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

  <script>
    // 🚀 JS 逻辑：读取你选的退款数量，而不是写死 
    const itemData = <?= json_encode(array_map(function ($i) {
      return [
        'id' => $i['Product_ID'],
        'name' => $i['Product_name'],
        'unitPrice' => (float) $i['unitPrice'],
        'qty' => (int) $i['quantity'],
        'isRefunded' => $i['item_status'] === 'Refunded'
      ];
    }, $items)) ?>;

    const selected = new Set();

    function fmt(n) {
      return 'RM ' + n.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function toggleItem(idx) {
      const cb = document.getElementById('cb-' + idx);
      const select = document.getElementById('qty-' + idx);
      const row = cb?.closest('.line-item');

      if (selected.has(idx)) {
        selected.delete(idx);
        cb?.classList.remove('checked');
        row?.classList.remove('selected');
        if (select) select.style.display = 'none';
      } else {
        selected.add(idx);
        cb?.classList.add('checked');
        row?.classList.add('selected');
        if (select) select.style.display = 'inline-block';
      }
      updateRefundTotal();
    }

    function updateRefundTotal() {
      let total = 0;
      selected.forEach(i => {
        const item = itemData[i];
        const select = document.getElementById('qty-' + i);
        const reqQty = select ? parseInt(select.value) : item.qty;
        total += (reqQty * item.unitPrice);
      });
      // 加回 6% SST
      const totalWithTax = total * 1.06;
      const el = document.getElementById('refundTotalDisplay');
      if (el) el.textContent = fmt(totalWithTax);
      const btn = document.getElementById('refundBtn');
      if (btn) btn.disabled = selected.size === 0;
    }

    function previewImage(input) {
      const preview = document.getElementById('imagePreview');
      const img = document.getElementById('previewImg');
      const name = document.getElementById('previewName');
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
          img.src = e.target.result;
          name.textContent = input.files[0].name + ' (' + (input.files[0].size / 1024).toFixed(1) + ' KB)';
          preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
      } else {
        preview.style.display = 'none';
      }
    }

    function openRefundModal() {
  if (selected.size === 0) {
    alert('Please select at least one item to refund.');
    return;
  }

  const list = document.getElementById('modalItemsList');
  let html = '';
  let inputsHtml = '';
  let total = 0;

  selected.forEach(i => {
    const item = itemData[i];
    const select = document.getElementById('qty-' + i);
    const reqQty = select ? parseInt(select.value) : item.qty;

    const lineTotal = reqQty * item.unitPrice;
    total += lineTotal;

    inputsHtml += `<input type="hidden" name="refund_items[]" value="${item.id}">`;
    inputsHtml += `<input type="hidden" name="refund_qty[${item.id}]" value="${reqQty}">`;

    html += `
      <div style="display:flex;justify-content:space-between;font-size:0.84rem;padding:5px 0;border-bottom:1px solid #f0f0f0;">
        <span>${item.name} ×${reqQty}</span>
        <span>${fmt(lineTotal)}</span>
      </div>
    `;
  });

  list.innerHTML = html;
  document.getElementById('hiddenTxInputs').innerHTML = inputsHtml;

  document.getElementById('modalTotalDisplay').textContent =
    fmt(total * 1.06);

  document.getElementById('refundOverlay').classList.add('open');
  document.body.classList.add('modal-open');

  // Lock background scroll
  document.body.classList.add('modal-open');
}

function closeRefundModal() {
  document.getElementById('refundOverlay').classList.remove('open');

  document.body.classList.remove('modal-open');

  document.getElementById('imagePreview').style.display = 'none';
  document.getElementById('refundImage').value = '';
}

    updateRefundTotal();
  </script>

</body>

</html>