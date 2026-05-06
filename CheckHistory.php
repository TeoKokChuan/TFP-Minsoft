<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Check History — Minsoft Solution</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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
  background: #020617;
  border-bottom: 1px solid #e0e0e0;
  position: sticky;
  top: 0;
  z-index: 10;
}
.logo {
  font-family: 'Segoe UI', sans-serif;
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
  width: 36px; height: 36px;
  border-radius: 50%;
  background: #e8f0fb;
  border: 1.5px solid #c5d8f5;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s;
  text-decoration: none;
}
.profile-btn:hover { background: #d0e4f8; border-color: #1d6fd8; }
.profile-btn svg { width: 18px; height: 18px; fill: #1d6fd8; }

/* ── Main ── */
.main {
  max-width: 1080px;
  margin: 0 auto;
  padding: 36px 32px;
}

/* ── Page Header ── */
.page-header { margin-bottom: 24px; }
.page-title {
  font-family: 'Segoe UI ', sans-serif;
  font-size: 1.5rem; font-weight: 700;
  color: #111; letter-spacing: -0.3px;
}
.page-subtitle { font-size: 0.85rem; color: #777; margin-top: 4px; }

/* ── Filters ── */
.filters-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.search-wrap { position: relative; flex: 1; min-width: 220px; }
.search-wrap svg {
  position: absolute; left: 11px; top: 50%;
  transform: translateY(-50%);
  color: #aaa; width: 15px; height: 15px;
  pointer-events: none;
}
.search-input {
  width: 100%;
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 9px 14px 9px 34px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.87rem;
  color: #111;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input::placeholder { color: #bbb; }
.search-input:focus {
  border-color: #1d6fd8;
  box-shadow: 0 0 0 3px rgba(29,111,216,0.1);
}
.filter-select {
  background: #fff;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 9px 32px 9px 13px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.87rem;
  color: #111;
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23777' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14L2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.filter-select:focus {
  border-color: #1d6fd8;
  box-shadow: 0 0 0 3px rgba(29,111,216,0.1);
}

/* ── Table Card ── */
.table-card {
  background: #f5f5f5;
  border: 1px solid #ddd;
  border-radius: 14px;
  overflow: hidden;
  animation: fadeUp 0.4s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: none; }
}

.table-scroll { overflow-x: auto; }

table { width: 100%; border-collapse: collapse; }

thead {
  background: #ebebeb;
  border-bottom: 1px solid #ddd;
}
th {
  padding: 12px 18px;
  text-align: left;
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #777;
  white-space: nowrap;
}

tbody tr {
  border-bottom: 1px solid #e8e8e8;
  transition: background 0.15s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #eef3fc; }

td { padding: 13px 18px; font-size: 0.87rem; vertical-align: middle; }

.inv-id {
  font-weight: 700;
  font-size: 0.84rem;
  color: #1d6fd8;
  letter-spacing: 0.02em;
}
.amount { font-weight: 600; color: #111; }

/* ── Status badges ── */
.status {
  display: inline-flex; align-items: center; gap: 6px;
  border-radius: 20px; padding: 4px 11px;
  font-size: 0.74rem; font-weight: 600; white-space: nowrap;
}
.status::before {
  content: '';
  width: 6px; height: 6px;
  border-radius: 50%; flex-shrink: 0;
}

.status.paid     { background: rgba(42,122,42,0.1);  color: #2a7a2a; }
.status.paid::before     { background: #2a7a2a; }

.status.refund   { background: rgba(192,57,43,0.1);  color: #c0392b; }
.status.refund::before   { background: #c0392b; }

.status.shipping { background: rgba(180,120,0,0.1);  color: #b47800; }
.status.shipping::before { background: #b47800; }

.status.shipped  { background: rgba(29,111,216,0.1); color: #1d6fd8; }
.status.shipped::before  { background: #1d6fd8; }

/* ── View button ── */
.btn-view {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 13px;
  border-radius: 7px;
  border: 1px solid #ddd;
  background: #fff;
  color: #555;
  font-size: 0.81rem;
  font-family: 'DM Sans', sans-serif;
  cursor: pointer;
  transition: all 0.18s;
}
.btn-view:hover {
  border-color: #1d6fd8;
  color: #1d6fd8;
  background: #e8f0fb;
}

/* ── Pagination ── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 20px;
  border-top: 1px solid #e5e5e5;
  background: #fff;
}
.page-info { font-size: 0.82rem; color: #777; }
.page-info strong { color: #111; }
.page-btns { display: flex; align-items: center; gap: 6px; }
.page-btn {
  width: 32px; height: 32px;
  border: 1px solid #ddd;
  border-radius: 7px;
  background: #fff;
  color: #555;
  font-size: 0.82rem;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}
.page-btn:hover,
.page-btn.active {
  border-color: #1d6fd8;
  color: #1d6fd8;
  background: #e8f0fb;
}
.page-btn:disabled { opacity: 0.3; pointer-events: none; }

/* ── Empty state ── */
.empty-state {
  text-align: center;
  padding: 48px 20px;
  color: #aaa;
  font-size: 0.9rem;
}
.empty-state svg { margin-bottom: 12px; opacity: 0.4; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .main { padding: 20px 16px; }
  .filters-row { flex-direction: column; align-items: stretch; }
  .topbar-label { display: none; }
}
</style>
</head>
<body>

<!-- ── Topbar ── -->
<header class="topbar">
  <a href="#" class="logo">Minsoft<span style="color:#a78bfa">.</span></a>
  <div class="topbar-right">
    <span class="topbar-label">John Doe</span>
    <a href="profile.php" class="profile-btn" title="My Account">
      <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
      </svg>
    </a>
  </div>
</header>

<!-- ── Main ── -->
<main class="main">

  <div class="page-header">
    <h1 class="page-title">Check History</h1>
    <p class="page-subtitle">View all invoices and their current payment status</p>
  </div>

  <!-- Filters -->
  <div class="filters-row">
    <div class="search-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
      </svg>
      <input type="text" class="search-input"
             placeholder="Search invoice ID or customer…"
             id="searchInput" oninput="filterTable()">
    </div>
    <select class="filter-select" id="statusFilter" onchange="filterTable()">
      <option value="">All Status</option>
      <option value="paid">Paid</option>
      <option value="refund">Refund</option>
      <option value="shipping">Shipping</option>
      <option value="shipped">Shipped</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-scroll">
      <table>
        <thead>
          <tr>
            <th>Invoice ID</th>
            <th>Date</th>
            <th>Customer</th>
            <th>Amount (MYR)</th>
            <th>Status</th>
            <th>Period</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="tableBody"></tbody>
      </table>
    </div>
    <div class="pagination">
      <div class="page-info">
        Showing <strong id="showingCount">—</strong> of <strong id="totalCount">0</strong> invoices
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
const invoices = [
  {
    id: "INV-2026-0401", date: "Apr 1, 2026", amount: 4299.00, status: "paid",
    customer: "John Doe", period: "Apr 2026", method: "Online Banking", txnId: "TXN-A1B2C3D4",
    items: [
      { name: "ASUS ROG Strix G16 Gaming Laptop", desc: "Intel Core i7-13650HX, RTX 4060, 16GB RAM, 512GB SSD", price: 3999.00, refunded: false, refundRequested: false },
      { name: "Laptop Carry Bag", desc: "16-inch padded carry sleeve", price: 300.00, refunded: false, refundRequested: false }
    ]
  },
  {
    id: "INV-2026-0302", date: "Mar 18, 2026", amount: 1850.00, status: "shipping",
    customer: "John Doe", period: "Mar 2026", method: "Credit Card", txnId: "TXN-SHIP0042",
    items: [
      { name: "Samsung 27\" QHD Monitor", desc: "2560×1440, 165Hz, IPS Panel", price: 1299.00, refunded: false, refundRequested: false },
      { name: "Monitor VESA Arm Mount", desc: "Single arm, full motion", price: 299.00, refunded: false, refundRequested: false },
      { name: "HDMI 2.1 Cable (2m)", desc: "4K@120Hz certified cable", price: 79.00, refunded: false, refundRequested: false },
      { name: "Screen Cleaning Kit", desc: "Microfibre cloth + spray", price: 49.00, refunded: false, refundRequested: false },
      { name: "Extended Warranty – 2 Years", desc: "Monitor coverage add-on", price: 124.00, refunded: false, refundRequested: false }
    ]
  },
  {
    id: "INV-2026-0301", date: "Mar 5, 2026", amount: 589.00, status: "shipped",
    customer: "John Doe", period: "Mar 2026", method: "Online Banking", txnId: "TXN-SHIP0039",
    items: [
      { name: "Logitech MX Keys S Keyboard", desc: "Wireless, backlit, multi-device", price: 389.00, refunded: false, refundRequested: false },
      { name: "Logitech MX Master 3S Mouse", desc: "8K DPI, silent clicks, Qi charging", price: 200.00, refunded: false, refundRequested: false }
    ]
  },
  {
    id: "INV-2026-0201", date: "Feb 14, 2026", amount: 2199.00, status: "paid",
    customer: "John Doe", period: "Feb 2026", method: "Credit Card", txnId: "TXN-B3C4D5E6",
    items: [
      { name: "Apple Mac Mini M4", desc: "M4 chip, 16GB RAM, 256GB SSD", price: 2199.00, refunded: false, refundRequested: false }
    ]
  },
  {
    id: "INV-2026-0101", date: "Jan 22, 2026", amount: 749.00, status: "refund",
    customer: "John Doe", period: "Jan 2026", method: "Online Banking", txnId: "TXN-REF0019",
    items: [
      { name: "Corsair RM850x PSU", desc: "850W, 80+ Gold, fully modular", price: 599.00, refunded: true, refundRequested: false },
      { name: "PSU Sleeved Cable Kit", desc: "ATX 24-pin + PCIe extensions", price: 150.00, refunded: true, refundRequested: false }
    ]
  },
  {
    id: "INV-2025-1201", date: "Dec 3, 2025", amount: 3299.00, status: "paid",
    customer: "John Doe", period: "Dec 2025", method: "Credit Card", txnId: "TXN-C5D6E7F8",
    items: [
      { name: "AMD Ryzen 9 7950X CPU", desc: "16-core, 32-thread, 5.7GHz boost", price: 1599.00, refunded: false, refundRequested: false },
      { name: "ASUS ROG Crosshair X670E", desc: "AM5 ATX Motherboard, DDR5", price: 1099.00, refunded: false, refundRequested: false },
      { name: "Noctua NH-D15 CPU Cooler", desc: "Dual tower, 140mm fans", price: 349.00, refunded: false, refundRequested: false },
      { name: "Thermal Paste (5g)", desc: "Kryonaut high-performance", price: 49.00, refunded: false, refundRequested: false },
      { name: "Anti-static Wrist Strap", desc: "ESD safety strap", price: 25.00, refunded: false, refundRequested: false },
      { name: "PCIe 4.0 NVMe SSD 2TB", desc: "Samsung 990 Pro, 7,450MB/s read", price: 599.00, refunded: false, refundRequested: false }
    ]
  },
  {
    id: "INV-2025-1101", date: "Nov 11, 2025", amount: 469.00, status: "shipped",
    customer: "John Doe", period: "Nov 2025", method: "Online Banking", txnId: "TXN-SHIP0028",
    items: [
      { name: "Razer DeathAdder V3 Pro", desc: "Wireless gaming mouse, 30K DPI", price: 299.00, refunded: false, refundRequested: false },
      { name: "Razer Goliathus Extended Mousepad", desc: "900×400mm, micro-textured", price: 170.00, refunded: false, refundRequested: false }
    ]
  },
  {
    id: "INV-2025-1001", date: "Oct 7, 2025", amount: 899.00, status: "paid",
    customer: "John Doe", period: "Oct 2025", method: "Credit Card", txnId: "TXN-D7E8F9G0",
    items: [
      { name: "Sony WH-1000XM5 Headphones", desc: "ANC, 30hr battery, LDAC", price: 899.00, refunded: false, refundRequested: false }
    ]
  }
];

localStorage.setItem("minsoft_payments", JSON.stringify(invoices));

let filtered = [...invoices];
let currentPage = 1;
const perPage = 6;

function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

function render() {
  const tbody = document.getElementById("tableBody");
  const start = (currentPage - 1) * perPage;
  const slice = filtered.slice(start, start + perPage);
  const total = filtered.length;
  const end   = Math.min(start + perPage, total);

  document.getElementById("showingCount").textContent = total ? `${start + 1}–${end}` : "0";
  document.getElementById("totalCount").textContent   = total;
  document.getElementById("pageNum").textContent      = currentPage;
  document.getElementById("prevBtn").disabled = currentPage === 1;
  document.getElementById("nextBtn").disabled = end >= total;

  if (!slice.length) {
    tbody.innerHTML = `
      <tr>
        <td colspan="7">
          <div class="empty-state">
            <svg width="40" height="40" fill="none" stroke="#aaa" stroke-width="1.5" viewBox="0 0 24 24">
              <path d="M9 17H7A5 5 0 0 1 7 7h2"/><path d="M15 7h2a5 5 0 0 1 0 10h-2"/>
              <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            <p>No invoices found matching your filters.</p>
          </div>
        </td>
      </tr>`;
    return;
  }

  tbody.innerHTML = slice.map(inv => `
    <tr>
      <td><span class="inv-id">${inv.id}</span></td>
      <td style="color:#555">${inv.date}</td>
      <td style="color:#111;font-weight:500">${inv.customer}</td>
      <td><span class="amount">MYR ${inv.amount.toFixed(2)}</span></td>
      <td><span class="status ${inv.status}">${cap(inv.status)}</span></td>
      <td style="color:#888;font-size:0.83rem">${inv.period}</td>
      <td>
        <button class="btn-view" onclick="viewDetail('${inv.id}')">
          View Detail
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </button>
      </td>
    </tr>
  `).join("");
}

function filterTable() {
  const q      = document.getElementById("searchInput").value.toLowerCase();
  const status = document.getElementById("statusFilter").value;
  filtered = invoices.filter(inv => {
    const matchQ = !q || inv.id.toLowerCase().includes(q) || inv.customer.toLowerCase().includes(q);
    const matchS = !status || inv.status === status;
    return matchQ && matchS;
  });
  currentPage = 1;
  render();
}

function changePage(dir) {
  currentPage += dir;
  render();
}

function viewDetail(id) {
  window.location.href = "HistoryDetail.php?id=" + encodeURIComponent(id);
}

render();
</script>

</body>
</html>