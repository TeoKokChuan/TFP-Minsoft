

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