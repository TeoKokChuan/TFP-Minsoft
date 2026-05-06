<?php
session_start();

$errors = [];
$activeTab = 'card'; // default tab to show on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? 'card';
    $activeTab = $method;

    if ($method === 'card') {
        $cardNum  = trim($_POST['cardNum']  ?? '');
        $cardName = trim($_POST['cardName'] ?? '');
        $expiry   = trim($_POST['expiry']   ?? '');
        $cvv      = trim($_POST['cvv']      ?? '');

        if ($cardNum  === '') $errors['cardNum']  = 'Card number is required.';
        elseif (strlen(preg_replace('/\s/', '', $cardNum)) < 16) $errors['cardNum'] = 'Enter a valid 16-digit card number.';

        if ($cardName === '') $errors['cardName'] = 'Name on card is required.';

        if ($expiry === '') $errors['expiry'] = 'Expiry date is required.';
        elseif (!preg_match('/^\d{2}\s*\/\s*\d{2}$/', $expiry)) $errors['expiry'] = 'Use MM / YY format.';

        if ($cvv === '') $errors['cvv'] = 'CVV is required.';
        elseif (!preg_match('/^\d{3,4}$/', $cvv)) $errors['cvv'] = 'CVV must be 3 or 4 digits.';

    } elseif ($method === 'fpx') {
        $bank = trim($_POST['bank'] ?? '');
        if ($bank === '') $errors['bank'] = 'Please select your bank.';
    }

    if (empty($errors)) {
        // Persist payment method to session
        $_SESSION['payment'] = [
            'method' => $method,
            'bank'   => $_POST['bank'] ?? '',
        ];
        header('Location: paymentresult.php');
        exit;
    }
}

$v = $_POST ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment — Minsoft Solution</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Segoe UI', sans-serif;
  background: #ffffff;
  color: #020617;
  min-height: 100vh;
  font-size: 15px;
  line-height: 1.6;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 62px;
  background: #020617;
  border-bottom: 1px solid #e0e0e0;
  position: sticky;
  top: 0;
  z-index: 10;
}
.logo {
  font-family: 'Segoe UI ', sans-serif;
  font-weight: 800;
  font-size: 1.25rem;
  color: #1d6fd8;
  text-decoration: none;
  letter-spacing: -0.5px;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.8rem;
  color: #777;
}
.breadcrumb span.active { color: #111; font-weight: 500; }

.profile-btn {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: #e8f0fb;
  border: 1.5px solid #c5d8f5;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
}
.profile-btn:hover { background: #d0e4f8; border-color: #1d6fd8; }
.profile-btn svg { width: 18px; height: 18px; fill: #1d6fd8; }

/* ── Steps ── */
.steps-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.step {
  display: flex; align-items: center; gap: 10px;
  font-size: 0.82rem; font-weight: 500;
  color: #aaa;
}
.step.active { color: #1d6fd8; }
.step.done   { color: #2a7a2a; }
.step-num {
  width: 26px; height: 26px;
  border-radius: 50%;
  border: 1.5px solid currentColor;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
}
.step.active .step-num { background: #1d6fd8; color: #fff; border-color: #1d6fd8; }
.step.done   .step-num { background: #2a7a2a; color: #fff; border-color: #2a7a2a; }
.step-line { width: 60px; height: 1px; background: #e0e0e0; margin: 0 12px; }

/* ── Body ── */
.body {
  display: flex;
  justify-content: center;
  padding: 0 24px 80px;
}

/* ── Panel ── */
.panel {
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 14px;
  padding: 28px;
  width: 100%;
  max-width: 680px;
  animation: fadeUp 0.4s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.panel-title {
  font-family: 'Segoe UI ', sans-serif;
  font-size: 1.05rem; font-weight: 700;
  color: #111;
  margin-bottom: 22px;
  display: flex; align-items: center; gap: 10px;
}
.panel-title-icon {
  width: 30px; height: 30px;
  border-radius: 8px;
  background: #e8f0fb;
  border: 1px solid #c5d8f5;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.85rem; color: #1d6fd8;
}

/* ── Payment Tabs ── */
.payment-tabs { display: flex; gap: 10px; margin-bottom: 22px; }
.pay-tab {
  flex: 1;
  padding: 11px 10px;
  background: #fff;
  border: 1.5px solid #ddd;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  font-size: 0.83rem;
  color: #777;
  font-family: 'Segoe UI', sans-serif;
  font-weight: 500;
  transition: border-color 0.2s, background 0.2s, color 0.2s;
}
.pay-tab.active {
  border-color: #1d6fd8;
  background: #e8f0fb;
  color: #1d6fd8;
  font-weight: 600;
}
.pay-tab:hover:not(.active) { border-color: #aac8f0; color: #555; }

/* ── Method panels ── */
.mpanel { display: none; }
.mpanel.show { display: block; }

/* ── Form fields ── */
.field { margin-bottom: 16px; }
.field:last-of-type { margin-bottom: 0; }
.field label {
  display: block;
  font-size: 0.75rem; font-weight: 500;
  color: #555; margin-bottom: 6px;
}
.iw { position: relative; }

.field input, .field select {
  width: 100%;
  background: #ffffff;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 9px 13px;
  color: #111;
  font-family: 'Segoe UI ', sans-serif;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  appearance: none;
}
.field input:focus, .field select:focus {
  border-color: #1d6fd8;
  box-shadow: 0 0 0 3px rgba(29,111,216,0.1);
}
.field input::placeholder { color: #bbb; }
.field input.pr { padding-right: 90px; }

/* ── Error states ── */
.field input.is-error,
.field select.is-error {
  border-color: #e53e3e;
  box-shadow: 0 0 0 3px rgba(229,62,62,0.1);
}
.field-error {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.72rem;
  color: #e53e3e;
  margin-top: 5px;
}
.field-error::before {
  content: '⚠';
  font-size: 0.7rem;
}

/* ── Card icons ── */
.cicons {
  position: absolute;
  right: 12px; top: 50%;
  transform: translateY(-50%);
  display: flex; gap: 5px;
}
.cico {
  padding: 3px 6px;
  background: #eef3fc;
  border: 1px solid #c5d8f5;
  border-radius: 4px;
  font-size: 0.6rem; color: #1d6fd8; font-weight: 700; letter-spacing: 0.5px;
}

.frow { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ── FPX Banks ── */
.banks { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 4px; }
.bank {
  padding: 10px 13px;
  background: #fff;
  border: 1.5px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.82rem;
  color: #555;
  display: flex; align-items: center; gap: 9px;
  transition: all 0.18s;
}
.bank:hover { border-color: #aac8f0; color: #333; }
.bank.sel   { border-color: #1d6fd8; background: #e8f0fb; color: #1d6fd8; }
.bdot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: #bbb;
  flex-shrink: 0;
  transition: background 0.15s;
}
.bank.sel .bdot { background: #1d6fd8; }
.bank.bank-error { border-color: #e53e3e; }

/* ── FPX note ── */
.note {
  display: flex; align-items: flex-start; gap: 7px;
  font-size: 0.73rem; color: #888;
  margin-top: 14px; line-height: 1.5;
}
.note-i { color: #1d6fd8; flex-shrink: 0; margin-top: 1px; }

/* ── Divider ── */
.divider { border: none; border-top: 1px solid #e5e5e5; margin: 20px 0; }

/* ── Actions ── */
.actions {
  display: flex; gap: 12px;
  margin-top: 26px; align-items: center;
}
.btn-back {
  padding: 12px 20px;
  border-radius: 8px;
  border: 1.5px solid #ddd;
  background: transparent;
  color: #777;
  font-family: 'Segoe UI ', sans-serif;
  font-size: 0.85rem; font-weight: 500;
  cursor: pointer;
  display: flex; align-items: center; gap: 8px;
  transition: all 0.18s;
  white-space: nowrap;
  text-decoration: none;
}
.btn-back:hover { border-color: #1d6fd8; color: #1d6fd8; background: #e8f0fb; }

.btn-pay {
  flex: 1; padding: 13px;
  background: #1d6fd8;
  border: none; border-radius: 8px;
  font-family: 'Segoe UI ', sans-serif;
  font-size: 0.95rem; font-weight: 700;
  color: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 9px;
  transition: background 0.2s, transform 0.12s;
  letter-spacing: 0.2px;
}
.btn-pay:hover  { background: #155bb5; transform: translateY(-1px); }
.btn-pay:active { transform: translateY(0); }

/* ── Global error banner ── */
.error-banner {
  display: flex; align-items: center; gap: 10px;
  background: #fff5f5;
  border: 1px solid rgba(229,62,62,0.3);
  border-radius: 8px;
  padding: 11px 14px;
  margin-bottom: 20px;
  font-size: 0.82rem;
  color: #c53030;
}
.error-banner-icon { font-size: 0.9rem; flex-shrink: 0; }

/* ── Responsive ── */
@media (max-width: 600px) {
  .frow   { grid-template-columns: 1fr; }
  .banks  { grid-template-columns: 1fr; }
  .payment-tabs { flex-direction: column; }
  .step-line { width: 30px; }
  .breadcrumb { display: none; }
}
</style>
</head>
<body>

<!-- ── Topbar ── -->
<header class="topbar">
  <a href="#" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>
  <div class="breadcrumb">
    <span>Cart</span>
    <span>›</span>
    <span>Checkout</span>
    <span>›</span>
    <span class="active">Payment</span>
  </div>
  <div class="profile-btn" title="My Account">
    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
    </svg>
  </div>
</header>

<!-- ── Steps ── -->
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

<!-- ── Body ── -->
<div class="body">
  <form id="payForm" method="POST" action="paymentcheckout.php" novalidate>

    <!-- Hidden field carries which tab/method is active -->
    <input type="hidden" name="method" id="methodField" value="<?= htmlspecialchars($activeTab) ?>">

    <div class="panel">

      <div class="panel-title">
        <div class="panel-title-icon">💳</div>
        Payment Method
      </div>

      <?php if (!empty($errors)): ?>
      <div class="error-banner">
        <span class="error-banner-icon">⚠️</span>
        Please fix the highlighted fields before continuing.
      </div>
      <?php endif; ?>

      <!-- ── Tabs ── -->
      <div class="payment-tabs">
        <button type="button" class="pay-tab <?= $activeTab === 'card' ? 'active' : '' ?>"
                onclick="switchTab('card', this)">
          💳 Credit / Debit
        </button>
        <button type="button" class="pay-tab <?= $activeTab === 'fpx' ? 'active' : '' ?>"
                onclick="switchTab('fpx', this)">
          🏦 FPX / Online Banking
        </button>
      </div>

      <!-- ══ Credit / Debit Card Panel ══ -->
      <div id="panel-card" class="mpanel <?= $activeTab === 'card' ? 'show' : '' ?>">

        <div class="field">
          <label for="cardNum">Card Number</label>
          <div class="iw">
            <input type="text" class="pr <?= isset($errors['cardNum']) ? 'is-error' : '' ?>"
                   id="cardNum" name="cardNum"
                   placeholder="4242 4242 4242 4242"
                   maxlength="19" autocomplete="cc-number"
                   value="<?= htmlspecialchars($v['cardNum'] ?? '') ?>"
                   oninput="fmtCard(this)">
            <div class="cicons">
              <div class="cico">VISA</div>
              <div class="cico">MC</div>
            </div>
          </div>
          <?php if (isset($errors['cardNum'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['cardNum']) ?></div>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="cardName">Name on Card</label>
          <input type="text" class="<?= isset($errors['cardName']) ? 'is-error' : '' ?>"
                 id="cardName" name="cardName"
                 placeholder="JOHN DOE"
                 autocomplete="cc-name"
                 style="text-transform:uppercase;letter-spacing:1.2px"
                 value="<?= htmlspecialchars($v['cardName'] ?? '') ?>">
          <?php if (isset($errors['cardName'])): ?>
            <div class="field-error"><?= htmlspecialchars($errors['cardName']) ?></div>
          <?php endif; ?>
        </div>

        <div class="frow">
          <div class="field" style="margin-bottom:0">
            <label for="expiry">Expiry Date</label>
            <input type="text"
                   class="<?= isset($errors['expiry']) ? 'is-error' : '' ?>"
                   id="expiry" name="expiry"
                   placeholder="MM / YY" maxlength="7"
                   autocomplete="cc-exp"
                   value="<?= htmlspecialchars($v['expiry'] ?? '') ?>"
                   oninput="fmtExp(this)">
            <?php if (isset($errors['expiry'])): ?>
              <div class="field-error"><?= htmlspecialchars($errors['expiry']) ?></div>
            <?php endif; ?>
          </div>
          <div class="field" style="margin-bottom:0">
            <label for="cvv">CVV</label>
            <input type="password"
                   class="<?= isset($errors['cvv']) ? 'is-error' : '' ?>"
                   id="cvv" name="cvv"
                   placeholder="•••" maxlength="4"
                   autocomplete="cc-csc">
            <?php if (isset($errors['cvv'])): ?>
              <div class="field-error"><?= htmlspecialchars($errors['cvv']) ?></div>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- end panel-card -->

      <!-- ══ FPX / Online Banking Panel ══ -->
      <div id="panel-fpx" class="mpanel <?= $activeTab === 'fpx' ? 'show' : '' ?>">

        <div class="field">
          <label>Select Your Bank</label>
          <!-- Hidden input carries the chosen bank name -->
          <input type="hidden" name="bank" id="bankField" value="<?= htmlspecialchars($v['bank'] ?? '') ?>">
        </div>

        <?php
        $banks = ['Maybank2U','CIMB Clicks','HLB Connect','RHB Now',
                  'Public Bank','AmBank','Affin Online','BSN'];
        $selectedBank = $v['bank'] ?? '';
        ?>
        <div class="banks <?= isset($errors['bank']) ? 'bank-error' : '' ?>">
          <?php foreach ($banks as $b): ?>
          <div class="bank <?= $selectedBank === $b ? 'sel' : '' ?>"
               onclick="selBank(this, '<?= htmlspecialchars($b) ?>')">
            <div class="bdot"></div><?= htmlspecialchars($b) ?>
          </div>
          <?php endforeach; ?>
        </div>

        <?php if (isset($errors['bank'])): ?>
          <div class="field-error" style="margin-top:8px"><?= htmlspecialchars($errors['bank']) ?></div>
        <?php endif; ?>

        <div class="note">
          <span class="note-i">ℹ</span>
          You will be redirected to your bank's secure portal to complete the payment.
        </div>

      </div><!-- end panel-fpx -->

      <hr class="divider">

      <!-- ── Actions ── -->
      <div class="actions">
        <a href="checkout.php" class="btn-back">← Back</a>
        <button type="button" class="btn-pay" onclick="doPay()">
          🔒 Pay Now &nbsp;·&nbsp; RM 5,332
        </button>
      </div>

    </div><!-- end .panel -->
  </form>
</div>

<script>
// ── Tab switcher ──────────────────────────────────────────────────────────
function switchTab(id, btn) {
  document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.mpanel').forEach(p => p.classList.remove('show'));
  btn.classList.add('active');
  document.getElementById('panel-' + id).classList.add('show');
  document.getElementById('methodField').value = id;
}

// ── Bank selector ─────────────────────────────────────────────────────────
function selBank(el, name) {
  document.querySelectorAll('.bank').forEach(b => b.classList.remove('sel'));
  el.classList.add('sel');
  document.getElementById('bankField').value = name;
  // Clear bank error highlight on selection
  document.querySelectorAll('.banks').forEach(b => b.classList.remove('bank-error'));
  const fe = document.querySelector('#panel-fpx .field-error');
  if (fe) fe.remove();
}

// ── Card number formatter ─────────────────────────────────────────────────
function fmtCard(el) {
  let v = el.value.replace(/\D/g, '').slice(0, 16);
  el.value = v.match(/.{1,4}/g)?.join(' ') ?? v;
}

// ── Expiry formatter MM / YY ──────────────────────────────────────────────
function fmtExp(el) {
  let v = el.value.replace(/\D/g, '').slice(0, 4);
  if (v.length >= 3) v = v.slice(0, 2) + ' / ' + v.slice(2);
  el.value = v;
}

// ── Client-side validation before submit ─────────────────────────────────
function doPay() {
  const method = document.getElementById('methodField').value;
  let valid = true;

  // Clear previous client errors
  document.querySelectorAll('.client-err').forEach(e => e.remove());
  document.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));

  if (method === 'card') {
    const cardNum  = document.getElementById('cardNum');
    const cardName = document.getElementById('cardName');
    const expiry   = document.getElementById('expiry');
    const cvv      = document.getElementById('cvv');

    if (!cardNum.value.trim()) {
      showErr(cardNum, 'Card number is required.'); valid = false;
    } else if (cardNum.value.replace(/\s/g,'').length < 16) {
      showErr(cardNum, 'Enter a valid 16-digit card number.'); valid = false;
    }
    if (!cardName.value.trim()) { showErr(cardName, 'Name on card is required.'); valid = false; }
    if (!expiry.value.trim())   { showErr(expiry, 'Expiry date is required.'); valid = false; }
    else if (!/^\d{2}\s*\/\s*\d{2}$/.test(expiry.value)) { showErr(expiry, 'Use MM / YY format.'); valid = false; }
    if (!cvv.value.trim())      { showErr(cvv, 'CVV is required.'); valid = false; }
    else if (!/^\d{3,4}$/.test(cvv.value)) { showErr(cvv, 'CVV must be 3 or 4 digits.'); valid = false; }

  } else if (method === 'fpx') {
    const bank = document.getElementById('bankField').value;
    if (!bank) {
      const banksEl = document.querySelector('.banks');
      banksEl.classList.add('bank-error');
      const err = document.createElement('div');
      err.className = 'field-error client-err';
      err.style.marginTop = '8px';
      err.textContent = 'Please select your bank.';
      banksEl.after(err);
      valid = false;
    }
  }

  if (!valid) return;
  document.getElementById('payForm').submit();
}

function showErr(input, msg) {
  input.classList.add('is-error');
  const err = document.createElement('div');
  err.className = 'field-error client-err';
  err.textContent = msg;
  input.closest('.field').appendChild(err);
}

// Clear error styling on input
document.addEventListener('input', function(e) {
  if (e.target.classList.contains('is-error')) {
    e.target.classList.remove('is-error');
    const fe = e.target.closest('.field')?.querySelector('.field-error');
    if (fe) fe.remove();
  }
});
</script>

</body>
</html>