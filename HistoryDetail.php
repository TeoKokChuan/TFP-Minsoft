<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>History Detail — Minsoft Solution</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
    rel="stylesheet">
  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #ffffff;
      color: #111;
      min-height: 100vh;
      font-size: 15px;
      line-height: 1.6;
    }

    /* ── Topbar ── */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      height: 62px;
      background: #ffffff;
      border-bottom: 1px solid #e0e0e0;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .logo {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-weight: 800;
      font-size: 1.25rem;
      color: #1d6fd8;
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
      background: #e8f0fb;
      border: 1.5px solid #c5d8f5;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s, border-color 0.2s;
    }

    .profile-btn:hover {
      background: #d0e4f8;
      border-color: #1d6fd8;
    }

    .profile-btn svg {
      width: 18px;
      height: 18px;
      fill: #1d6fd8;
    }

    /* ── Main ── */
    .main {
      max-width: 860px;
      margin: 0 auto;
      padding: 36px 32px;
    }

    /* ── Back button ── */
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 8px 15px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.84rem;
      color: #555;
      cursor: pointer;
      transition: all 0.18s;
      margin-bottom: 24px;
      text-decoration: none;
    }

    .back-btn:hover {
      border-color: #1d6fd8;
      color: #1d6fd8;
      background: #e8f0fb;
    }

    /* ── Hero card ── */
    .hero-card {
      background: #f5f5f5;
      border: 1px solid #ddd;
      border-radius: 14px;
      padding: 28px;
      margin-bottom: 18px;
      position: relative;
      overflow: hidden;
      animation: fadeUp 0.35s ease both;
    }

    .hero-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent, #1d6fd8, transparent);
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
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 1.3rem;
      font-weight: 700;
      color: #111;
      letter-spacing: -0.3px;
    }

    .hero-left-sub {
      font-size: 0.83rem;
      color: #777;
      margin-top: 3px;
    }

    .hero-amount {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 1.85rem;
      font-weight: 700;
      letter-spacing: -1px;
      line-height: 1;
    }

    .hero-amount.paid {
      color: #2a7a2a;
    }

    .hero-amount.failed {
      color: #c0392b;
    }

    .hero-amount.pending {
      color: #b47800;
    }

    .hero-amount.refunded {
      color: #6d4ac0;
    }

    .hero-amount.pending-refund {
      color: #b47800;
    }

    .hero-amount.shipping {
      color: #b47800;
    }

    .hero-amount.shipped {
      color: #1d6fd8;
    }

    .hero-amount.refund {
      color: #c0392b;
    }

    .hero-amount-label {
      font-size: 0.73rem;
      color: #888;
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
      color: #999;
      display: block;
      margin-bottom: 2px;
    }

    .meta-item span {
      font-size: 0.88rem;
      font-weight: 500;
      color: #111;
    }

    /* ── Status badges ── */
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

    .status.paid {
      background: rgba(42, 122, 42, 0.1);
      color: #2a7a2a;
    }

    .status.paid::before {
      background: #2a7a2a;
    }

    .status.refund {
      background: rgba(192, 57, 43, 0.1);
      color: #c0392b;
    }

    .status.refund::before {
      background: #c0392b;
    }

    .status.refunded {
      background: rgba(109, 74, 192, 0.1);
      color: #6d4ac0;
    }

    .status.refunded::before {
      background: #6d4ac0;
    }

    .status.shipping {
      background: rgba(180, 120, 0, 0.1);
      color: #b47800;
    }

    .status.shipping::before {
      background: #b47800;
    }

    .status.shipped {
      background: rgba(29, 111, 216, 0.1);
      color: #1d6fd8;
    }

    .status.shipped::before {
      background: #1d6fd8;
    }

    .status.pending {
      background: rgba(180, 120, 0, 0.1);
      color: #b47800;
    }

    .status.pending::before {
      background: #b47800;
    }

    .status.pending-refund {
      background: rgba(180, 120, 0, 0.1);
      color: #b47800;
    }

    .status.pending-refund::before {
      background: #b47800;
    }

    /* ── Items card ── */
    .items-card {
      background: #f5f5f5;
      border: 1px solid #ddd;
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 18px;
      animation: fadeUp 0.4s ease 0.08s both;
    }

    .items-header {
      padding: 16px 22px;
      border-bottom: 1px solid #e5e5e5;
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #ebebeb;
    }

    .items-title {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.95rem;
      font-weight: 700;
      color: #111;
    }

    .items-subtitle {
      font-size: 0.77rem;
      color: #888;
      margin-top: 2px;
    }

    /* ── Select all ── */
    .select-all-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      user-select: none;
    }

    .select-all-wrap span {
      font-size: 0.8rem;
      color: #777;
      transition: color 0.15s;
    }

    .select-all-wrap:hover span {
      color: #111;
    }

    /* ── Custom checkbox ── */
    .cb {
      width: 20px;
      height: 20px;
      border: 2px solid #ccc;
      border-radius: 6px;
      background: #fff;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.18s;
    }

    .cb.checked {
      background: #1d6fd8;
      border-color: #1d6fd8;
      box-shadow: 0 0 0 3px rgba(29, 111, 216, 0.15);
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

    /* ── Line items ── */
    .line-item {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 22px;
      border-bottom: 1px solid #e8e8e8;
      background: #fff;
      transition: background 0.15s;
      cursor: pointer;
    }

    .line-item:last-child {
      border-bottom: none;
    }

    .line-item:hover:not(.disabled) {
      background: #eef3fc;
    }

    .line-item.disabled {
      opacity: 0.45;
      cursor: not-allowed;
      pointer-events: none;
    }

    .li-info {
      flex: 1;
    }

    .li-name {
      font-size: 0.88rem;
      font-weight: 500;
      color: #111;
    }

    .li-desc {
      font-size: 0.77rem;
      color: #888;
      margin-top: 1px;
    }

    .li-price {
      font-weight: 700;
      font-size: 0.9rem;
      color: #111;
      flex-shrink: 0;
    }

    .li-badge {
      font-size: 0.69rem;
      padding: 2px 9px;
      border-radius: 5px;
      font-weight: 600;
      flex-shrink: 0;
    }

    .li-badge.refunded {
      background: rgba(109, 74, 192, 0.08);
      color: #6d4ac0;
      border: 1px solid rgba(109, 74, 192, 0.2);
    }

    .li-badge.refund-pending {
      background: rgba(180, 120, 0, 0.08);
      color: #b47800;
      border: 1px solid rgba(180, 120, 0, 0.2);
    }

    /* ── Refund footer ── */
    .refund-footer {
      padding: 14px 22px;
      border-top: 1px solid #e5e5e5;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }

    .refund-summary {
      font-size: 0.85rem;
      color: #777;
    }

    .refund-summary strong {
      font-size: 1rem;
      font-weight: 700;
      color: #1d6fd8;
    }

    .btn-refund {
      display: flex;
      align-items: center;
      gap: 8px;
      background: rgba(192, 57, 43, 0.07);
      border: 1px solid rgba(192, 57, 43, 0.25);
      border-radius: 8px;
      padding: 9px 18px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.87rem;
      font-weight: 600;
      color: #c0392b;
      cursor: pointer;
      transition: all 0.18s;
    }

    .btn-refund:hover:not(:disabled) {
      background: rgba(192, 57, 43, 0.14);
      border-color: #c0392b;
    }

    .btn-refund:disabled {
      opacity: 0.3;
      cursor: not-allowed;
    }

    /* ── Action bar ── */
    .action-bar {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      animation: fadeUp 0.4s ease 0.14s both;
    }

    .btn-primary {
      display: flex;
      align-items: center;
      gap: 7px;
      background: #1d6fd8;
      border: none;
      border-radius: 8px;
      padding: 11px 22px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: background 0.2s, transform 0.12s;
    }

    .btn-primary:hover {
      background: #155bb5;
      transform: translateY(-1px);
    }

    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-ghost {
      display: flex;
      align-items: center;
      gap: 7px;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 11px 22px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.88rem;
      color: #555;
      cursor: pointer;
      transition: all 0.18s;
      text-decoration: none;
    }

    .btn-ghost:hover {
      border-color: #1d6fd8;
      color: #1d6fd8;
      background: #e8f0fb;
    }

    /* ══════════════════════════════
   CONFIRM MODAL
══════════════════════════════ */
    .overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
      backdrop-filter: blur(4px);
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.22s;
    }

    .overlay.open {
      opacity: 1;
      pointer-events: all;
    }

    .modal {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 16px;
      width: 460px;
      max-width: 95vw;
      padding: 28px;
      position: relative;
      transform: translateY(16px) scale(0.97);
      transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .overlay.open .modal {
      transform: none;
    }

    .modal-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: #e8f0fb;
      border: 1px solid #c5d8f5;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
      color: #1d6fd8;
    }

    .modal-title {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 1.05rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 8px;
    }

    .modal-desc {
      font-size: 0.84rem;
      color: #666;
      line-height: 1.7;
      margin-bottom: 20px;
    }

    .modal-note {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: #f0f5ff;
      border: 1px solid #c5d8f5;
      border-radius: 8px;
      padding: 11px 13px;
      font-size: 0.81rem;
      color: #555;
      line-height: 1.6;
      margin-bottom: 20px;
    }

    .modal-note svg {
      color: #1d6fd8;
      flex-shrink: 0;
      margin-top: 2px;
    }

    /* Items in modal */
    .modal-items {
      background: #f5f5f5;
      border: 1px solid #e5e5e5;
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 16px;
    }

    .modal-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 15px;
      border-bottom: 1px solid #ebebeb;
      font-size: 0.84rem;
    }

    .modal-item:last-child {
      border-bottom: none;
    }

    .modal-item-name {
      color: #111;
      font-weight: 500;
    }

    .modal-item-price {
      color: #555;
      font-weight: 600;
    }

    .modal-total {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-bottom: 18px;
      border-bottom: 1px solid #e5e5e5;
      margin-bottom: 18px;
    }

    .modal-total-label {
      font-size: 0.8rem;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .modal-total-value {
      font-family: 'Syne', sans-serif;
      font-size: 1.3rem;
      font-weight: 700;
      color: #1d6fd8;
      letter-spacing: -0.5px;
    }

    /* Refund reason */
    .refund-reason {
      margin-bottom: 18px;
    }

    .refund-reason label {
      display: block;
      font-size: 0.74rem;
      font-weight: 600;
      color: #777;
      margin-bottom: 7px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .refund-reason textarea {
      width: 100%;
      min-height: 88px;
      resize: none;
      border-radius: 8px;
      padding: 10px 13px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.85rem;
      background: #fff;
      border: 1px solid #ccc;
      color: #111;
      outline: none;
      transition: border-color 0.18s, box-shadow 0.18s;
    }

    .refund-reason textarea:focus {
      border-color: #1d6fd8;
      box-shadow: 0 0 0 3px rgba(29, 111, 216, 0.1);
    }

    .refund-reason textarea::placeholder {
      color: #bbb;
    }

    .modal-actions {
      display: flex;
      gap: 10px;
    }

    .btn-send-request {
      flex: 1;
      background: #1d6fd8;
      border: none;
      border-radius: 8px;
      padding: 12px 18px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: background 0.2s, transform 0.1s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 7px;
    }

    .btn-send-request:hover {
      background: #155bb5;
    }

    .btn-send-request:active {
      transform: scale(0.98);
    }

    .btn-cancel-modal {
      flex: 1;
      background: transparent;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 12px 18px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.88rem;
      color: #666;
      cursor: pointer;
      transition: all 0.18s;
    }

    .btn-cancel-modal:hover {
      border-color: #aaa;
      color: #111;
      background: #f5f5f5;
    }

    /* ══════════════════════════════
   NOTIF MODAL
══════════════════════════════ */
    .notif-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.45);
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
      border: 1px solid #b6e8c4;
      border-radius: 16px;
      width: 420px;
      max-width: 95vw;
      padding: 32px 28px;
      text-align: center;
      transform: translateY(16px) scale(0.97);
      transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }

    .notif-overlay.open .notif-modal {
      transform: none;
    }

    .notif-icon-wrap {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: #f0faf3;
      border: 2px solid #b6e8c4;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      animation: pulse 1.8s ease infinite;
      color: #2a7a2a;
    }

    @keyframes pulse {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(42, 122, 42, 0.2);
      }

      50% {
        box-shadow: 0 0 0 10px rgba(42, 122, 42, 0);
      }
    }

    .notif-title {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
      background: #e8f0fb;
      border: 1px solid #c5d8f5;
      border-radius: 7px;
      padding: 5px 13px;
      font-size: 0.78rem;
      color: #1d6fd8;
      font-weight: 600;
      letter-spacing: 0.04em;
      margin-bottom: 20px;
      margin-top: 6px;
    }

    .notif-steps {
      background: #f5f5f5;
      border: 1px solid #e5e5e5;
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
      background: #e8f0fb;
      border: 1px solid #c5d8f5;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.67rem;
      font-weight: 700;
      color: #1d6fd8;
      flex-shrink: 0;
    }

    .btn-notif-close {
      width: 100%;
      background: #1d6fd8;
      border: none;
      border-radius: 8px;
      padding: 12px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      font-size: 0.88rem;
      font-weight: 600;
      color: #fff;
      cursor: pointer;
      transition: background 0.2s;
    }

    .btn-notif-close:hover {
      background: #155bb5;
    }

    /* ── Not found ── */
    .not-found {
      text-align: center;
      padding: 80px 20px;
      color: #999;
    }

    .not-found h2 {
      font-family: 'Syne', sans-serif;
      font-size: 1.2rem;
      font-weight: 700;
      color: #111;
      margin-bottom: 8px;
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

      .hero-amount-label {
        text-align: left;
      }

      .topbar-label {
        display: none;
      }
    }
  </style>
</head>

<body>

  <!-- ── Topbar ── -->
  <header class="topbar">
    <a href="CheckHistory.php" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>
    <div class="topbar-right">
      <span class="topbar-label">John Doe</span>
      <a href="profile.php" class="profile-btn" title="My Account">
        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path
            d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
        </svg>
      </a>
    </div>
  </header>

  <main class="main" id="mainContent">
    <!-- JS populates this -->
  </main>

  <!-- ══ Confirm refund modal ══ -->
  <div class="overlay" id="confirmOverlay" onclick="overlayClick(event,'confirmOverlay')">
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
      <div class="modal-desc">
        Your refund request will be submitted to our billing team for review.
        You will be notified via email once the admin approves or rejects your request.
      </div>

      <div class="modal-note">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span>This does not guarantee a refund. Admin will review and process eligible requests within <strong
            style="color:#111">5–10 business days</strong>.</span>
      </div>

      <div class="modal-items" id="confirmItemsList"></div>

      <div class="refund-reason">
        <label for="refundReason">Refund Reason</label>
        <textarea id="refundReason" placeholder="Write your reason for requesting a refund..."></textarea>
      </div>

      <div class="modal-total">
        <span class="modal-total-label">Requested Refund</span>
        <span class="modal-total-value" id="confirmTotalValue">RM 0.00</span>
      </div>

      <div class="modal-actions">
        <button class="btn-cancel-modal" onclick="closeConfirm()">Cancel</button>
        <button class="btn-send-request" onclick="submitRequest()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="22" y1="2" x2="11" y2="13" />
            <polygon points="22 2 15 22 11 13 2 9 22 2" />
          </svg>
          Send Request
        </button>
      </div>
    </div>
  </div>

  <!-- ══ Success notif modal ══ -->
  <div class="notif-overlay" id="notifOverlay">
    <div class="notif-modal">
      <div class="notif-icon-wrap">
        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <polyline points="20 6 9 17 4 12" />
        </svg>
      </div>

      <div class="notif-title">Refund Request Sent!</div>
      <div class="notif-desc">
        Your refund request has been submitted to our billing admin team.
        A confirmation has been sent to your registered email address.
      </div>

      <div class="notif-ref" id="notifRef">REF-XXXXXXXX</div>

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

      <button class="btn-notif-close" onclick="closeNotif()">Got it, back to invoices</button>
    </div>
  </div>

  <script>
    // ── Load data from localStorage ──
    function loadPayments() {
      try {
        const stored = localStorage.getItem('minsoft_payments');
        if (stored) return JSON.parse(stored);
      } catch (e) { }
      return null;
    }

    function savePayments(payments) {
      try { localStorage.setItem('minsoft_payments', JSON.stringify(payments)); } catch (e) { }
    }

    // ── Fallback data ──
    const fallbackPayments = [
      {
        id: 'INV-2026-0401',
        date: 'Apr 1, 2026', amount: 1049.00, status: 'paid',
        method: 'Online Banking', customer: 'John Doe', period: 'Apr 2026', txnId: 'TXN-DEMO0001',
        items: [
          { name: 'Dell Inspiron Laptop', desc: '15.6" FHD, Intel Core i7, 16GB RAM', price: 999.00, refunded: false, refundRequested: false },
          { name: 'Wireless Mouse Pro', desc: 'Ergonomic 2.4GHz Silent Optical', price: 50.00, refunded: false, refundRequested: false }
        ]
      },
      {
        id: 'INV-2026-0301',
        date: 'Mar 1, 2026', amount: 149.00, status: 'shipping',
        method: 'Credit Card', customer: 'John Doe', period: 'Mar 2026', txnId: 'TXN-DEMO0002',
        items: [
          { name: 'Mechanical Gaming Keyboard', desc: 'RGB Backlit - Brown Switches', price: 149.00, refunded: false, refundRequested: false }
        ]
      },
      {
        id: 'INV-2026-0201',
        date: 'Feb 1, 2026', amount: 89.00, status: 'shipped',
        method: 'Online Banking', customer: 'John Doe', period: 'Feb 2026', txnId: 'TXN-DEMO0003',
        items: [
          { name: 'USB-C Triple Display Dock', desc: '60W Power Delivery, Dual HDMI', price: 89.00, refunded: false, refundRequested: false }
        ]
      },
      {
        id: 'INV-2026-0101',
        date: 'Jan 1, 2026', amount: 499.00, status: 'refunded',
        method: 'PayPal', customer: 'John Doe', period: 'Jan 2026', txnId: 'TXN-DEMO0004',
        items: [
          { name: 'UltraSharp 27" 4K Monitor', desc: 'IPS Panel, 100% sRGB Color Gamut', price: 499.00, refunded: true, refundRequested: false }
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
      <div class="meta-item"><label>Invoice ID</label><span style="color:#1d6fd8;font-weight:600;font-size:0.83rem;">${p.id}</span></div>
      <div class="meta-item"><label>Transaction ID</label><span style="font-size:0.79rem;color:#888;">${p.txnId || '-'}</span></div>
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
        <div class="li-price">RM ${item.price.toFixed(2)}</div>
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
          <div class="hero-amount ${p.status}">RM ${p.amount.toFixed(2)}</div>
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
        <div class="refund-summary">Selected refund: <strong id="refundTotalDisplay">RM 0.00</strong></div>
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
      if (el) el.textContent = 'RM ' + total.toFixed(2);
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
        <span class="modal-item-name">RM ${item.name}</span>
        <span class="modal-item-price">RM ${item.price.toFixed(2)}</span>
      </div>`;
      });

      document.getElementById('confirmTotalValue').textContent = 'RM ' + total.toFixed(2);
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