
function processRefund($conn, $order_id, $user_id, $order)
{
    $refund_success = false;
    $refund_error   = '';
    $refund_ref     = '';

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

            return [
        'success'   => $refund_success,
        'error'     => $refund_error,
        'reference' => $refund_ref
    ];
}
      }
    }
  }
}