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