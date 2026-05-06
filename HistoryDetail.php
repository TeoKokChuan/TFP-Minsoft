<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>History Detail — Minsoft Solution</title>

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --sky: #38bdf8;
  --sky-light: #7dd3fc;
  --accent: #a78bfa;
  --bg: #020617;
  --bg-card: #ffffff;
  --bg-surface: #101e34;
  --bg-input: #0d1a2e;
  --text: #000000;
  --text-muted: #797979;
  --border: rgba(0, 0, 0, 0.1);
  --border-md: rgba(0, 0, 0, 0.2);
  --border-strong: rgba(0, 0, 0, 0.35);
  --green: #4ade80;
  --red: #f87171;
  --yellow: #fbbf24;
  --font: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

html, body {
  font-family: var(--font);
  background: #ffffff;
  color: #ffffff;
  min-height: 100vh;
  font-size: 15px;
  line-height: 1.6;
}

/* ── Topbar ── */
.topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 24px; height: 62px;
  background: #020617;
  backdrop-filter: blur(14px);
  border-bottom: 1px solid var(--border);
  position: sticky; top: 0; z-index: 10;
}

.logo { font-weight: 800; font-size: 1.22rem; color: var(--sky); text-decoration: none; letter-spacing: -0.4px; }
.topbar-right { display: flex; align-items: center; gap: 14px; }
.topbar-label { font-size: 0.8rem; color: var(--text-muted); }

.avatar {
  width: 34px; height: 34px; border-radius: 50%;
  background: linear-gradient(135deg, var(--sky), var(--accent));
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.75rem; color: #060d1c; cursor: pointer;
}

/* ── Main ── */
.main { max-width: 860px; margin: 0 auto; padding: 36px 40px; }

/* ── Back ── */
.back-btn {
  display: inline-flex; align-items: center; gap: 8px;
  background: transparent; border: 1px solid var(--border-md);
  border-radius: 10px; padding: 8px 16px;
  font-family: var(--font); font-size: 0.85rem; color: var(--text-muted);
  cursor: pointer; transition: all 0.18s; margin-bottom: 28px;
  text-decoration: none;
}

.back-btn:hover { border-color: var(--text-muted); color: var(--text-muted); background: rgba(4, 45, 63, 0.05); }

/* ── Hero card ── */
.hero-card {
  background: var(--bg-card);
  border: 1px solid var(--border-md);
  border-radius: 20px; padding: 32px; margin-bottom: 20px;
  position: relative; overflow: hidden;
  animation: fadeUp 0.35s ease both;
}

.hero-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px;
  background: linear-gradient(90deg, transparent, var(--sky), transparent);
}

.hero-top {
  display: flex; justify-content: space-between; align-items: flex-start;
  margin-bottom: 28px; flex-wrap: wrap; gap: 16px;
}

.hero-left-title { font-size: 1.4rem; font-weight: 700; color: var(--text); letter-spacing: -0.3px; }
.hero-left-sub   { font-size: 0.84rem; color: var(--text-muted); margin-top: 3px; }

.hero-amount {
  font-size: 2rem; font-weight: 700; letter-spacing: -1.2px; line-height: 1;
}

.hero-amount.paid     { color: var(--green); }
.hero-amount.failed   { color: var(--red); }
.hero-amount.pending  { color: var(--yellow); }
.hero-amount.refunded { color: var(--accent); }
.hero-amount.pending-refund { color: var(--yellow); }

.hero-amount-label { font-size: 0.74rem; color: var(--text-muted); margin-top: 3px; text-align: right; }

.meta-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.meta-item label {
  font-size: 0.69rem; text-transform: uppercase; letter-spacing: 0.1em;
  color: var(--text-muted); display: block; margin-bottom: 3px;
}

.meta-item span { font-size: 0.9rem; font-weight: 500; color: var(--text); }

.status {
  display: inline-flex; align-items: center; gap: 5px;
  border-radius: 20px; padding: 4px 11px;
  font-size: 0.75rem; font-weight: 600; white-space: nowrap;
}

.status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.status.paid           { background: rgba(74,222,128,0.08); color: var(--green); }
.status.paid::before   { background: var(--green); box-shadow: 0 0 6px var(--green); }
.status.pending        { background: rgba(251,191,36,0.08); color: var(--yellow); }
.status.pending::before { background: var(--yellow); }
.status.failed         { background: rgba(248,113,113,0.08); color: var(--red); }
.status.failed::before { background: var(--red); }
.status.refunded       { background: rgba(167,139,250,0.08); color: var(--accent); }
.status.refunded::before { background: var(--accent); }
.status.pending-refund { background: rgba(251,191,36,0.08); color: var(--yellow); }
.status.pending-refund::before { background: var(--yellow); box-shadow: 0 0 6px var(--yellow); }

/* ── Items card ── */
.items-card {
  background: var(--bg-card); border: 1px solid var(--border-md);
  border-radius: 16px; overflow: hidden; margin-bottom: 20px;
  animation: fadeUp 0.4s ease 0.08s both;
}

.items-header {
  padding: 18px 24px; border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
}

.items-title   { font-size: 0.95rem; font-weight: 600; color: var(--text); }
.items-subtitle { font-size: 0.78rem; color: var(--text-muted); }

.select-all-wrap {
  display: flex; align-items: center; gap: 8px;
  cursor: pointer; user-select: none;
}

.select-all-wrap span { font-size: 0.8rem; color: var(--text-muted); transition: color 0.15s; }
.select-all-wrap:hover span { color: var(--text); }

/* Custom checkbox */
.cb {
  width: 20px; height: 20px;
  border: 2px solid var(--border-md); border-radius: 6px;
  background: #0d1a2e; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.18s;
}

.cb.checked {
  background: var(--sky); border-color: var(--sky);
  box-shadow: 0 0 10px rgba(56,189,248,0.3);
}

.cb.checked::after {
  content: '';
  width: 5px; height: 9px;
  border: 2px solid #040a14; border-top: none; border-left: none;
  transform: rotate(45deg) translateY(-1px); display: block;
}

/* Line item rows */
.line-item {
  display: flex; align-items: center; gap: 14px;
  padding: 16px 24px; border-bottom: 1px solid var(--border);
  transition: background 0.15s; cursor: pointer;
}

.line-item:last-child { border-bottom: none; }
.line-item:hover:not(.disabled) { background: rgba(56,189,248,0.03); }

.line-item.disabled { opacity: 0.45; cursor: not-allowed; pointer-events: none; }

.li-info { flex: 1; }
.li-name { font-size: 0.88rem; font-weight: 500; color: var(--text); }
.li-desc { font-size: 0.78rem; color: var(--text-muted); margin-top: 1px; }

.li-price { font-weight: 700; font-size: 0.9rem; color: var(--text); flex-shrink: 0; }

.li-badge {
  font-size: 0.7rem; padding: 2px 9px;
  border-radius: 5px; font-weight: 600; flex-shrink: 0;
}

.li-badge.refunded       { background: rgba(167,139,250,0.1); color: var(--accent); border: 1px solid rgba(167,139,250,0.2); }
.li-badge.refund-pending { background: rgba(251,191,36,0.1);  color: var(--yellow); border: 1px solid rgba(251,191,36,0.2); }

/* ── Refund footer ── */
.refund-footer {
  padding: 16px 24px; border-top: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  gap: 12px; flex-wrap: wrap;
}

.refund-summary { font-size: 0.85rem; color: var(--text-muted); }
.refund-summary strong { font-size: 1rem; font-weight: 700; color: var(--sky); }

.btn-refund {
  display: flex; align-items: center; gap: 8px;
  background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.25);
  border-radius: 10px; padding: 10px 20px;
  font-family: var(--font); font-size: 0.88rem; font-weight: 600; color: var(--red);
  cursor: pointer; transition: all 0.18s;
}

.btn-refund:hover:not(:disabled) { background: rgba(248,113,113,0.16); border-color: var(--red); box-shadow: 0 0 16px rgba(248,113,113,0.12); }
.btn-refund:disabled { opacity: 0.3; cursor: not-allowed; }

/* ── Action bar ── */
.action-bar {
  display: flex; gap: 12px; flex-wrap: wrap;
  animation: fadeUp 0.4s ease 0.14s both;
}

.btn-primary {
  display: flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, var(--sky), #0ea5e9);
  border: none; border-radius: 10px; padding: 11px 22px;
  font-family: var(--font); font-size: 0.88rem; font-weight: 600;
  color: #040a14; cursor: pointer; transition: opacity 0.18s;
}

.btn-primary:hover { opacity: 0.86; }

.btn-ghost {
  display: flex; align-items: center; gap: 7px;
  background: transparent; border: 1px solid var(--border-md);
  border-radius: 10px; padding: 11px 22px;
  font-family: var(--font); font-size: 0.88rem; color: var(--text-muted);
  cursor: pointer; transition: all 0.18s; text-decoration: none;
}

.btn-ghost:hover { border-color: var(--border-strong); color: var(--text); }

/* ════════════════════════════
   CONFIRM MODAL
════════════════════════════ */
.overlay {
  position: fixed; inset: 0;
  background: rgba(2,6,23,0.88);
  backdrop-filter: blur(8px);
  z-index: 100;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none;
  transition: opacity 0.22s;
}

.overlay.open { opacity: 1; pointer-events: all; }

.modal {
  background: var(--bg-card);
  border: 1px solid var(--border-strong);
  border-radius: 22px; width: 460px; max-width: 95vw;
  padding: 36px; position: relative;
  transform: translateY(18px) scale(0.97);
  transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow: 0 32px 100px rgba(0,0,0,0.7);
}

.overlay.open .modal { transform: none; }

.modal-icon {
  width: 56px; height: 56px; border-radius: 16px;
  background: rgba(56,189,248,0.08);
  border: 1px solid rgba(56,189,248,0.2);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 20px;
}

.modal-icon svg { color: var(--sky); }

.modal-title { font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 8px; }

.modal-desc { font-size: 0.86rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 22px; }

.modal-note {
  display: flex; align-items: flex-start; gap: 10px;
  background: rgba(56,189,248,0.05); border: 1px solid rgba(56,189,248,0.12);
  border-radius: 10px; padding: 12px 14px;
  font-size: 0.82rem; color: var(--text-muted); line-height: 1.6;
  margin-bottom: 22px;
}

.modal-note svg { color: var(--sky); flex-shrink: 0; margin-top: 2px; }

/* Items in modal */
.modal-items {
  background: var(--bg-surface); border: 1px solid var(--border);
  border-radius: 12px; overflow: hidden; margin-bottom: 18px;
}

.modal-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 11px 16px; border-bottom: 1px solid var(--border);
  font-size: 0.84rem;
}

.modal-item:last-child { border-bottom: none; }
.modal-item-name { color: var(--text); font-weight: 500; }
.modal-item-price { color: var(--text-muted); font-weight: 600; }

.modal-total {
  display: flex; align-items: center; justify-content: space-between;
  padding-bottom: 20px; border-bottom: 1px solid var(--border); margin-bottom: 22px;
}

.modal-total-label { font-size: 0.82rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; }
.modal-total-value { font-size: 1.35rem; font-weight: 700; color: var(--sky); letter-spacing: -0.5px; }

/* Refund reason textbox */
.refund-reason {
  margin-bottom: 18px;
}

.refund-reason label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.refund-reason textarea {
  width: 100%;
  min-height: 90px;
  resize: none;
  border-radius: 12px;
  padding: 12px 14px;
  font-family: var(--font);
  font-size: 0.85rem;
  background: var(--bg-input);
  border: 1px solid var(--border-md);
  color: var(--text);
  outline: none;
  transition: border 0.18s, box-shadow 0.18s;
}

.refund-reason textarea:focus {
  border-color: var(--sky);
  box-shadow: 0 0 0 3px rgba(56,189,248,0.12);
}

.refund-reason textarea::placeholder {
  color: rgba(122,147,181,0.7);
}

.modal-actions { display: flex; gap: 10px; }

.btn-send-request {
  flex: 1; background: linear-gradient(135deg, var(--sky), #0ea5e9);
  border: none; border-radius: 10px; padding: 12px 18px;
  font-family: var(--font); font-size: 0.88rem; font-weight: 600;
  color: #040a14; cursor: pointer; transition: opacity 0.18s, transform 0.1s;
  display: flex; align-items: center; justify-content: center; gap: 7px;
}

.btn-send-request:hover { opacity: 0.87; }
.btn-send-request:active { transform: scale(0.98); }

.btn-cancel-modal {
  flex: 1; background: transparent; border: 1px solid var(--border-md);
  border-radius: 10px; padding: 12px 18px;
  font-family: var(--font); font-size: 0.88rem; color: var(--text-muted);
  cursor: pointer; transition: all 0.18s;
}

.btn-cancel-modal:hover { border-color: var(--border-strong); color: var(--text); }

.notif-overlay {
  position: fixed; inset: 0;
  background: rgba(2,6,23,0.88);
  backdrop-filter: blur(8px);
  z-index: 200;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; pointer-events: none;
  transition: opacity 0.22s;
}

.notif-overlay.open { opacity: 1; pointer-events: all; }

.notif-modal {
  background: var(--bg-card);
  border: 1px solid rgba(74,222,128,0.25);
  border-radius: 22px; width: 420px; max-width: 95vw;
  padding: 38px 36px; text-align: center;
  transform: translateY(18px) scale(0.97);
  transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow: 0 0 0 1px rgba(74,222,128,0.08), 0 32px 80px rgba(0,0,0,0.65);
}

.notif-overlay.open .notif-modal { transform: none; }

.notif-icon-wrap {
  width: 68px; height: 68px; border-radius: 50%;
  background: rgba(74,222,128,0.1); border: 2px solid rgba(74,222,128,0.25);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 22px;
  animation: pulse 1.8s ease infinite;
}

@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(74,222,128,0.25); }
  50%       { box-shadow: 0 0 0 10px rgba(74,222,128,0); }
}

.notif-icon-wrap svg { color: var(--green); }

.notif-title { font-size: 1.2rem; font-weight: 700; color: var(--text); margin-bottom: 10px; }
.notif-desc  { font-size: 0.87rem; color: var(--text-muted); line-height: 1.7; margin-bottom: 8px; }

.notif-ref {
  display: inline-block;
  background: var(--bg-surface); border: 1px solid var(--border-md);
  border-radius: 8px; padding: 6px 14px;
  font-size: 0.78rem; color: var(--sky); font-weight: 600;
  letter-spacing: 0.04em; margin-bottom: 24px; margin-top: 8px;
}

.notif-steps {
  background: var(--bg-surface); border: 1px solid var(--border);
  border-radius: 12px; padding: 16px 18px;
  text-align: left; margin-bottom: 26px;
}

.notif-step {
  display: flex; align-items: flex-start; gap: 10px;
  font-size: 0.82rem; color: var(--text-muted); padding: 5px 0;
}

.notif-step-num {
  width: 20px; height: 20px; border-radius: 50%;
  background: rgba(56,189,248,0.1); border: 1px solid rgba(56,189,248,0.2);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.68rem; font-weight: 700; color: var(--sky); flex-shrink: 0;
}

.btn-notif-close {
  width: 100%; background: linear-gradient(135deg, var(--sky), #0ea5e9);
  border: none; border-radius: 10px; padding: 12px;
  font-family: var(--font); font-size: 0.88rem; font-weight: 600;
  color: #040a14; cursor: pointer; transition: opacity 0.18s;
}

.btn-notif-close:hover { opacity: 0.87; }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: none; }
}

.not-found {
  text-align: center; padding: 80px 20px;
  color: var(--text-muted);
}

.not-found h2 { font-size: 1.2rem; font-weight: 600; color: var(--text); margin-bottom: 8px; }

@media (max-width: 640px) {
  .main { padding: 20px 16px; }
  .meta-grid { grid-template-columns: 1fr 1fr; }
  .hero-top { flex-direction: column; }
  .hero-amount-label { text-align: left; }
}
</style>
</head>

<body>

<header class="topbar">
  <a href="CheckHistory.php" class="logo">Minsoft</a>
  <div class="topbar-right">
    <span class="topbar-label">John Doe</span>
    <div class="avatar">JD</div>
  </div>
</header>

<main class="main" id="mainContent">
  <!-- JS populates this -->
</main>

<!-- ── Confirm refund modal ── -->
<div class="overlay" id="confirmOverlay" onclick="overlayClick(event,'confirmOverlay')">
  <div class="modal">
    <div class="modal-icon">
      <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
    </div>

    <div class="modal-title">Send Refund Request to Admin</div>
    <div class="modal-desc">
      Your refund request will be submitted to our billing team for review.
      You will be notified via email once the admin approves or rejects your request.
    </div>

    <div class="modal-note">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span>This does not guarantee a refund. Admin will review and process eligible requests within <strong style="color:var(--text);">5–10 business days</strong>.</span>
    </div>

    <div class="modal-items" id="confirmItemsList"></div>

    <!-- Refund reason textbox -->
    <div class="refund-reason">
      <label for="refundReason">Refund Reason</label>
      <textarea id="refundReason" placeholder="Write your reason for requesting a refund..."></textarea>
    </div>

    <div class="modal-total">
      <span class="modal-total-label">Requested Refund</span>
      <span class="modal-total-value" id="confirmTotalValue">$0.00</span>
    </div>

    <div class="modal-actions">
      <button class="btn-cancel-modal" onclick="closeConfirm()">Cancel</button>
      <button class="btn-send-request" onclick="submitRequest()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <line x1="22" y1="2" x2="11" y2="13"/>
          <polygon points="22 2 15 22 11 13 2 9 22 2"/>
        </svg>
        Send Request
      </button>
    </div>
  </div>
</div>

<div class="notif-overlay" id="notifOverlay">
  <div class="notif-modal">
    <div class="notif-icon-wrap">
      <svg width="30" height="30" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
    </div>

    <div class="notif-title">Refund Request Sent!</div>
    <div class="notif-desc">
      Your refund request has been submitted to our billing admin team.
      A confirmation has been sent to your registered email address.
    </div>

    <div class="notif-ref" id="notifRef">REF-XXXXXXXX</div>

    <div class="notif-steps">
      <div class="notif-step"><div class="notif-step-num">1</div><span>Admin team receives and reviews your request</span></div>
      <div class="notif-step"><div class="notif-step-num">2</div><span>You'll receive an email update within 2 business days</span></div>
      <div class="notif-step"><div class="notif-step-num">3</div><span>If approved, refund is processed within 5–10 business days</span></div>
    </div>

    <button class="btn-notif-close" onclick="closeNotif()">Got it, back to invoices</button>
  </div>
</div>

<script>
// ── Load data from localStorage ──
function loadPayments() {
  try {
    const stored = localStorage.getItem('minsoft_payments');
    if (stored) return JSON.parse(stored);
  } catch(e) {}
  return null;
}

function savePayments(payments) {
  try { localStorage.setItem('minsoft_payments', JSON.stringify(payments)); } catch(e) {}
}

// ── Fallback data ──
const fallbackPayments = [
  { 
    id:'INV-2026-0401', 
    date:'Apr 1, 2026',  
    amount:1049.00, 
    status:'paid', 
    method:'Online Banking', 
    customer:'John Doe', 
    period:'Apr 2026', 
    txnId:'TXN-DEMO0001',
    items:[
      { name:'Dell Inspiron Laptop', desc:'15.6" FHD, Intel Core i7, 16GB RAM', price:999.00, refunded:false, refundRequested:false },
      { name:'Wireless Mouse Pro', desc:'Ergonomic 2.4GHz Silent Optical', price:50.00, refunded:false, refundRequested:false }
    ]
  },
  { 
    id:'INV-2026-0301', 
    date:'Mar 1, 2026',  
    amount:149.00, 
    status:'shipping', 
    method:'Credit Card', 
    customer:'John Doe', 
    period:'Mar 2026', 
    txnId:'TXN-DEMO0002',
    items:[
      { name:'Mechanical Gaming Keyboard', desc:'RGB Backlit - Brown Switches', price:149.00, refunded:false, refundRequested:false }
    ]
  },
  { 
    id:'INV-2026-0201', 
    date:'Feb 1, 2026',  
    amount:89.00, 
    status:'shipped', 
    method:'Online Banking', 
    customer:'John Doe', 
    period:'Feb 2026', 
    txnId:'TXN-DEMO0003',
    items:[
      { name:'USB-C Triple Display Dock', desc:'60W Power Delivery, Dual HDMI', price:89.00, refunded:false, refundRequested:false }
    ]
  },
  { 
    id:'INV-2026-0101', 
    date:'Jan 1, 2026',  
    amount:499.00, 
    status:'refunded', 
    method:'PayPal', 
    customer:'John Doe', 
    period:'Jan 2026', 
    txnId:'TXN-DEMO0004',
    items:[
      { name:'UltraSharp 27" 4K Monitor', desc:'IPS Panel, 100% sRGB Color Gamut', price:499.00, refunded:true, refundRequested:false }
    ]
  }
];

// ── State ──
let payments = loadPayments() || fallbackPayments;
let currentPayment = null;
let selectedItems = new Set();

function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }
function statusLabel(s) { return s === 'pending-refund' ? 'Pending Refund' : cap(s); }

// ── Parse URL ──
const urlParams = new URLSearchParams(window.location.search);
const invoiceId = urlParams.get('id');

// ── Init ──
function init() {
  const main = document.getElementById('mainContent');
  const p = payments.find(x => x.id === invoiceId);

  if (!p) {
    main.innerHTML = `
      <a href="CheckHistory.php" class="back-btn">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        Back to History
      </a>

      <div class="not-found">
        <h2>Invoice not found</h2>
        <p>The invoice <strong>${invoiceId || '—'}</strong> could not be located.</p>
      </div>`;
    return;
  }

  currentPayment = p;
  selectedItems = new Set();
  renderDetail(p, main);
}

function renderDetail(p, container) {
  const canRefund = p.status === 'paid';

  const metaHtml = `
    <div class="meta-grid">
      <div class="meta-item"><label>Status</label><span><span class="status ${p.status}">${statusLabel(p.status)}</span></span></div>
      <div class="meta-item"><label>Payment Method</label><span>${p.method}</span></div>
      <div class="meta-item"><label>Customer</label><span>${p.customer}</span></div>
      <div class="meta-item"><label>Billing Period</label><span>${p.period}</span></div>
      <div class="meta-item"><label>Invoice ID</label><span style="color:var(--sky);font-weight:600;font-size:0.83rem;">${p.id}</span></div>
      <div class="meta-item"><label>Transaction ID</label><span style="font-size:0.8rem;color:var(--text-muted);">${p.txnId || '-'}</span></div>
    </div>`;

  const lineItemsHtml = p.items.map((item, i) => {
    const isDisabled = item.refunded || item.refundRequested || !canRefund;
    let badge = '';

    if (item.refunded) badge = `<span class="li-badge refunded">Refunded</span>`;
    else if (item.refundRequested) badge = `<span class="li-badge refund-pending">Request Sent</span>`;

    return `
      <div class="line-item ${isDisabled ? 'disabled' : ''}" onclick="${isDisabled ? '' : `toggleItem(${i})`}">
        <div class="cb" id="cb-${i}"></div>
        <div class="li-info">
          <div class="li-name">${item.name}</div>
          <div class="li-desc">${item.desc}</div>
        </div>
        ${badge}
        <div class="li-price">$${item.price.toFixed(2)}</div>
      </div>`;
  }).join('');

  const selectAllHtml = canRefund ? `
    <div class="select-all-wrap" onclick="toggleSelectAll()">
      <div class="cb" id="cbAll"></div>
      <span>Select all</span>
    </div>` : '';

  container.innerHTML = `
    <a href="CheckHistory.php" class="back-btn">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <polyline points="15 18 9 12 15 6"/>
      </svg>
      Back to History
    </a>

    <div class="hero-card">
      <div class="hero-top">
        <div>
          <div class="hero-left-title">${p.id}</div>
          <div class="hero-left-sub">Issued on ${p.date} &nbsp;·&nbsp; ${p.period}</div>
        </div>
        <div>
          <div class="hero-amount ${p.status}">$${p.amount.toFixed(2)}</div>
          <div class="hero-amount-label">Total Charged</div>
        </div>
      </div>
      ${metaHtml}
    </div>

    <div class="items-card">
      <div class="items-header">
        <div>
          <div class="items-title">Line Items</div>
          <div class="items-subtitle">${canRefund ? 'Tick items to request a refund' : 'Refund requests are only available for paid invoices'}</div>
        </div>
        ${selectAllHtml}
      </div>

      ${lineItemsHtml}

      <div class="refund-footer">
        <div class="refund-summary">Selected refund: <strong id="refundTotalDisplay">$0.00</strong></div>
        <button class="btn-refund" id="refundBtn" disabled onclick="openConfirm()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="22" y1="2" x2="11" y2="13"/>
            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
          </svg>
          Request Refund
        </button>
      </div>
    </div>

    <div class="action-bar">
      <button class="btn-primary" onclick="downloadInvoice()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Download Invoice
      </button>

      <a href="CheckHistory.php" class="btn-ghost">Back to History</a>
    </div>`;
}

// ── Selection ──
function toggleItem(i) {
  if (selectedItems.has(i)) selectedItems.delete(i);
  else selectedItems.add(i);

  document.getElementById('cb-' + i).classList.toggle('checked', selectedItems.has(i));
  updateSelectAll();
  updateTotal();
}

function toggleSelectAll() {
  const refundable = currentPayment.items
    .map((item, i) => ({ item, i }))
    .filter(x => !x.item.refunded && !x.item.refundRequested);

  const allSelected = refundable.length > 0 && refundable.every(x => selectedItems.has(x.i));

  refundable.forEach(x => {
    if (allSelected) {
      selectedItems.delete(x.i);
      document.getElementById('cb-' + x.i).classList.remove('checked');
    } else {
      selectedItems.add(x.i);
      document.getElementById('cb-' + x.i).classList.add('checked');
    }
  });

  updateSelectAll();
  updateTotal();
}

function updateSelectAll() {
  const cbAll = document.getElementById('cbAll');
  if (!cbAll) return;

  const refundable = currentPayment.items
    .map((it, i) => ({ it, i }))
    .filter(x => !x.it.refunded && !x.it.refundRequested);

  const allSel = refundable.length > 0 && refundable.every(x => selectedItems.has(x.i));
  cbAll.classList.toggle('checked', allSel);
}

function updateTotal() {
  let total = 0;
  selectedItems.forEach(i => { total += currentPayment.items[i].price; });

  const el = document.getElementById('refundTotalDisplay');
  if (el) el.textContent = '$' + total.toFixed(2);

  const btn = document.getElementById('refundBtn');
  if (btn) btn.disabled = selectedItems.size === 0;
}

// ── Confirm modal ──
function openConfirm() {
  const p = currentPayment;
  let total = 0;

  const list = document.getElementById('confirmItemsList');
  list.innerHTML = '';

  document.getElementById("refundReason").value = "";

  selectedItems.forEach(i => {
    const item = p.items[i];
    total += item.price;

    list.innerHTML += `
      <div class="modal-item">
        <span class="modal-item-name">${item.name}</span>
        <span class="modal-item-price">$${item.price.toFixed(2)}</span>
      </div>`;
  });

  document.getElementById('confirmTotalValue').textContent = '$' + total.toFixed(2);
  document.getElementById('confirmOverlay').classList.add('open');
}

function closeConfirm() {
  document.getElementById('confirmOverlay').classList.remove('open');
}

function overlayClick(e, id) {
  if (e.target === document.getElementById(id)) {
    document.getElementById(id).classList.remove('open');
  }
}

// ── Submit request ──
function submitRequest() {
  const p = currentPayment;
  const reason = document.getElementById("refundReason").value.trim();

  if (reason.length < 5) {
    alert("Please enter a refund reason (at least 5 characters).");
    return;
  }

  const refNum = 'REF-' + Date.now().toString(36).toUpperCase().slice(-8);

  selectedItems.forEach(i => {
    p.items[i].refundRequested = true;
    p.items[i].refundReason = reason;
    p.items[i].refundRef = refNum;
  });

  const allRequested = p.items.every(x => x.refunded || x.refundRequested);
  if (allRequested) p.status = 'pending-refund';

  savePayments(payments);

  closeConfirm();
  selectedItems = new Set();

  document.getElementById('notifRef').textContent = refNum;
  document.getElementById('notifOverlay').classList.add('open');
}

function closeNotif() {
  document.getElementById('notifOverlay').classList.remove('open');
  renderDetail(currentPayment, document.getElementById('mainContent'));
}

function downloadInvoice() {
  alert('Preparing download for ' + currentPayment.id + '…\n(This would trigger a PDF download in production.)');
}

init();
</script>

</body>
</html>