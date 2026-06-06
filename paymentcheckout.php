<?php
require_once "php/auth.php";
require_once "php/database.php";

require_once "php/OrderModal.php";
require_once "php/BillModal.php";

$user_id = (int) $_SESSION['User_ID'];
$checkout = $_SESSION['checkout'];
$total = (float) ($_SESSION['checkout_total'] ?? 0);
$subtotal = (float) ($_SESSION['checkout_subtotal'] ?? 0);

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

  if ($expiry === '') {
    $errors['expiry'] = 'Expiry date is required.';
  } elseif (!preg_match('/^\d{2}\s*\/\s*\d{2}$/', $expiry)) {
    $errors['expiry'] = 'Use MM / YY format.';
  } else {
    $parts = preg_split('/\s*\/\s*/', $expiry);
    $exp_month = (int) $parts[0];
    $exp_year = (int) ('20' . trim($parts[1]));
    $cur_month = (int) date('m');
    $cur_year = (int) date('Y');
    if ($exp_month < 1 || $exp_month > 12) {
      $errors['expiry'] = 'Invalid month. Use MM / YY format.';
    } elseif ($exp_year < $cur_year || ($exp_year === $cur_year && $exp_month < $cur_month)) {
      $errors['expiry'] = 'This card has expired.';
    }
  }

  if ($cvv === '')
    $errors['cvv'] = 'CVV is required.';
  elseif (!preg_match('/^\d{3}$/', $cvv))
    $errors['cvv'] = 'CVV must be 3 digits.';

  if (empty($errors)) {

    $order_stmt = $conn->prepare(
      "INSERT INTO customer_order (User_ID, orderStatus, orderDate, TotalPrice, PaymentMethod)
             VALUES (?, 'Pending', NOW(), ?, 'Credit Card')"
    );
    $order_stmt->bind_param('id', $user_id, $total);
    $order_stmt->execute();
    $order_id = $conn->insert_id;
    $order_stmt->close();

    $shipping_addr = $_SESSION['checkout_address'] ?? $checkout['address1'];
    $user_name = $checkout['user_name'] ?? '';

    $bill_stmt = $conn->prepare(
      "INSERT INTO bill_master (Bill_ID, User_ID, Date_time, User_name, Shipping_address, Total_amount, Bill_Status)
             VALUES (?, ?, NOW(), ?, ?, ?, 'Pending')"
    );
    $bill_stmt->bind_param('iissd', $order_id, $user_id, $user_name, $shipping_addr, $total);
    $bill_stmt->execute();
    $bill_stmt->close();


    $cart_stmt = $conn->prepare(
      "SELECT c.Product_ID, c.cartQuantity, p.Price
             FROM cart c
             JOIN product p ON c.Product_ID = p.Product_ID
             WHERE c.User_ID = ?"
    );
    $cart_stmt->bind_param('i', $user_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    $tx_stmt = $conn->prepare(
      "INSERT INTO bill_transaction (Bill_ID, Product_ID, quantity, unitPrice, subtotal)
             VALUES (?, ?, ?, ?, ?)"
    );

    while ($row = $cart_result->fetch_assoc()) {
      $line_sub = $row['Price'] * $row['cartQuantity'];
      $tx_stmt->bind_param(
        'iiidd',
        $order_id,
        $row['Product_ID'],
        $row['cartQuantity'],
        $row['Price'],
        $line_sub
      );
      $tx_stmt->execute();

      $stock_stmt = $conn->prepare(
        "UPDATE product SET stockQuantity = stockQuantity - ? 
         WHERE Product_ID = ? AND stockQuantity >= ?"
    );
    $stock_stmt->bind_param('iii',
        $row['cartQuantity'],
        $row['Product_ID'],
        $row['cartQuantity']   
    );
    $stock_stmt->execute();
    $stock_stmt->close();
    }
    $cart_stmt->close();
    $tx_stmt->close();

    $del_stmt = $conn->prepare("DELETE FROM cart WHERE User_ID = ?");
    $del_stmt->bind_param('i', $user_id);
    $del_stmt->execute();
    $del_stmt->close();

    $_SESSION['payment'] = [
      'order_id' => $order_id,
      'method' => 'Credit Card',
      'total' => $total,
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
  <link rel="stylesheet" href="css/paymentcheckout.css">
</head>

<body>

<?php include "paymentHeader.php"; ?>


  <div class="body">
    <form id="payForm" method="POST" action="paymentcheckout.php" class="payment-form">

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
          <input
    type="text"
    class="card-name-input <?= errClass('cardName', $errors) ?>"
    id="cardName"
    name="cardName"
    placeholder="JOHN DOE"
    autocomplete="cc-name"
    value="<?= val('cardName', $v) ?>">
          <?php err('cardName', $errors); ?>
        </div>

        <div class="frow">
          <div class="field field-no-margin">
            <label for="expiry">Expiry Date</label>
            <input type="text" class="<?= errClass('expiry', $errors) ?>" id="expiry" name="expiry"
              placeholder="MM / YY" maxlength="7" autocomplete="cc-exp" value="<?= val('expiry', $v) ?>"
              oninput="fmtExp(this)">
            <?php err('expiry', $errors); ?>
          </div>
          <div class="field field-no-margin">
            <label for="cvv">CVV</label>
            <input type="password" class="<?= errClass('cvv', $errors) ?>" id="cvv" name="cvv" placeholder="•••"
              maxlength="3" autocomplete="cc-csc">
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

<?php include "footer.php"; ?>


<script src="js/paymentcheckout.js"></script>

</body>

</html>