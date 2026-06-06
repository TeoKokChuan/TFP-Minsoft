<?php
require_once "php/auth.php";
require_once "php/database.php";
require_once "php/OrderModal.php";

$user_id = (int) $_SESSION['User_ID'];


$user_stmt = $conn->prepare("SELECT User_Name, Email, Phone, Address FROM user WHERE User_ID = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_row = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();


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

$has_build = !empty(array_filter($cart_items, fn($i) => !empty($i['build_ref'])));


$subtotal = array_sum(array_map(fn($i) => $i['Price'] * $i['cartQuantity'], $cart_items));
$tax = round($subtotal * 0.06);
$total = $subtotal + $tax;

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
  <link rel="stylesheet" href="css/checkout.css">
</head>

<body>

  <?php include "header.php"; ?>


  <?php if (!empty($errors['cart'])): ?>
    <div class="cart-error-container">
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
          <div class="field-no-margin">
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
            <label for="address2">Address Line 2 <span class="optional-text">(optional)</span></label>
            <input type="text" id="address2" name="address2" placeholder="Apartment, suite, unit..."
              value="<?= val('address2', $v) ?>">
          </div>
          <div class="form-row triple">
            <div class="field-no-margin">
              <label for="city">City</label>
              <input type="text" id="city" name="city" class="<?= errClass('city', $errors) ?>"
                placeholder="Johor Bahru" value="<?= val('city', $v) ?>">
              <?php err('city', $errors); ?>
            </div>
            <div class="field-no-margin">
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
            <div class="field-no-margin">
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
            <p class="empty-cart-message">
              Your cart is empty. <a href="products.php" class="shop-link">Shop now</a>
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

        <p class="assembly-text">
          Our technicians can assemble your Custom PC for you at no extra cost.
        </p>

        <select name="assembly_request"
          class="assembly-select">
          <option value="Yes">🛠️ Yes, assemble my Custom PC(s) for me</option>
          <option value="No">📦 No, just send me the sealed components</option>
        </select>
      </div>
          
    <?php else: ?>

      <div class="panel assembly-box assembly-disabled">
        <div class="panel-title assembly-title-disabled">
          <div class="panel-title-icon">🛠️</div>
          Installation &amp; Assembly
        </div>

        <p class="assembly-text">
          Assembly option is only available for orders that include a full PC build from the Builder.
        </p>

        <select disabled
          class="assembly-select-disabled">
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
    <div class="remove-modal-box">
      <div class="remove-modal-icon">🗑️</div>
      <h3 class="remove-modal-title">Remove Item?</h3>
      <p class="remove-modal-text">
        Are you sure you want to remove<br>
        <strong id="remove-item-name" class="remove-item-name"></strong> from your order?
      </p>
      <button type="button" class="btn-remove-confirm" onclick="confirmRemove()">Yes, Remove It</button>
      <button type="button" class="btn-secondary" onclick="closeRemoveModal()">Keep It</button>
    </div>
  </div>

<script>
window.checkoutData = {
    subtotal: <?= json_encode($subtotal) ?>,
    tax: <?= json_encode($tax) ?>,
    total: <?= json_encode($total) ?>
};
</script>

<script src="js/checkout.js"></script>

<?php include "includes/footer.php"; ?>

</body>
</html>