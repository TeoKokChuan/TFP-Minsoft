
    let subtotal = <?= (float) $subtotal ?>;
    let pendingRemoveBtn = null;
    let pendingRemovePrice = 0;

    function fmt(n) {
      return 'RM ' + n.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotals() {
      const tax = Math.round(subtotal * 0.06);
      const total = subtotal + tax;
      const count = document.querySelectorAll('.order-item').length;
      document.getElementById('subtotal-label') && (document.getElementById('subtotal-label').textContent = 'Subtotal (' + count + ' item' + (count !== 1 ? 's' : '') + ')');
      document.getElementById('subtotal-val') && (document.getElementById('subtotal-val').textContent = fmt(subtotal));
      document.getElementById('tax-val') && (document.getElementById('tax-val').textContent = fmt(tax));
      document.getElementById('total-val') && (document.getElementById('total-val').textContent = fmt(total));
      if (subtotal <= 0) {
        const btn = document.getElementById('orderBtn');
        if (btn) { btn.disabled = true; btn.innerHTML = '🚫 Cart is Empty'; }
      }
    }

    function askRemove(btn, price) {
      pendingRemoveBtn = btn; pendingRemovePrice = price;
      document.getElementById('remove-item-name').textContent = btn.closest('.order-item').querySelector('.item-info p').textContent;
      document.getElementById('removeOverlay').style.display = 'flex';
    }
    function confirmRemove() {
      closeRemoveModal();
      if (!pendingRemoveBtn) return;
      const item = pendingRemoveBtn.closest('.order-item');
      item.style.transition = 'opacity 0.25s,transform 0.25s';
      item.style.opacity = '0'; item.style.transform = 'translateX(10px)';
      setTimeout(() => { item.remove(); subtotal -= pendingRemovePrice; pendingRemoveBtn = null; pendingRemovePrice = 0; updateTotals(); }, 270);
    }
    function closeRemoveModal() { document.getElementById('removeOverlay').style.display = 'none'; }

    const rules = {
      user_name: { label: 'Full name', required: true, pattern: /^[a-zA-Z\s'\-]+$/, patternMsg: 'Name may only contain letters.' },
      email: { label: 'Email', required: true, pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, patternMsg: 'Enter a valid email.' },
      phone: { label: 'Phone number', required: true, pattern: /^[+0-9\s\-()\u200f]{7,20}$/, patternMsg: 'Enter a valid phone number.' },
      address1: { label: 'Street address', required: true },
      city: { label: 'City', required: true, pattern: /^[a-zA-Z\s'\-]+$/, patternMsg: 'City name may only contain letters.' },
      state: { label: 'State', required: true },
      postcode: { label: 'Postcode', required: true, pattern: /^\d{5}$/, patternMsg: 'Postcode must be exactly 5 digits.' },
    };

    function clearClientErrors() {
      document.querySelectorAll('.client-err').forEach(e => e.remove());
      document.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));
      const b = document.getElementById('client-banner'); if (b) b.remove();
    }
    function showClientError(input, msg) {

  input.classList.add('is-error');

  // REMOVE existing server/client errors first
  input.closest('.field')
       ?.querySelectorAll('.field-error')
       .forEach(e => e.remove());

  const err = document.createElement('div');

  err.className = 'field-error client-err';

  err.innerHTML =
    '<span class="field-error-icon">⚠</span>' + msg;

  input.closest('.field').appendChild(err);
}

    function validateForm() {
      clearClientErrors();
      let firstError = null, hasError = false;
      for (const [name, rule] of Object.entries(rules)) {
        const el = document.querySelector('[name="' + name + '"]');
        if (!el) continue;
        const val = el.value.trim();
        if (rule.required && val === '') {
          showClientError(el, rule.label + ' is required.'); if (!firstError) firstError = el; hasError = true;
        } else if (val !== '' && rule.pattern && !rule.pattern.test(val)) {
          showClientError(el, rule.patternMsg); if (!firstError) firstError = el; hasError = true;
        }
      }
      if (hasError) {
        const banner = document.createElement('div');
        banner.id = 'client-banner'; banner.className = 'error-banner client-err';
        banner.innerHTML = '<span class="error-banner-icon">⚠️</span> Please fix the highlighted fields before continuing.';
        const existingBanner = document.getElementById('client-banner');

if (!existingBanner) {
  document.querySelector('.panel').before(banner);
}
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' }); firstError.focus();
      }
      return !hasError;
    }
    function handlePlaceOrder() { if (!validateForm()) return; showConfirmModal(); }

    function validateSingleField(input) {

  const name = input.name;
  const rule = rules[name];

  if (!rule) return;

  const value = input.value.trim();

  // remove old errors first
  input.classList.remove('is-error');

  input.closest('.field')
       ?.querySelectorAll('.field-error')
       .forEach(e => e.remove());

  let hasError = false;

  // required validation
  if (rule.required && value === '') {

    showClientError(input, rule.label + ' is required.');
    hasError = true;
  }

  // pattern validation
  else if (
    value !== '' &&
    rule.pattern &&
    !rule.pattern.test(value)
  ) {

    showClientError(input, rule.patternMsg);
    hasError = true;
  }

  // remove top banner if all fields valid
  checkGlobalErrors();
};

function checkGlobalErrors() {

  const hasErrors =
    document.querySelectorAll('.field .field-error').length > 0;

  // remove ALL error banners when no errors remain
  if (!hasErrors) {

    document.querySelectorAll('.error-banner')
      .forEach(banner => banner.remove());
  }
}

document.addEventListener('input', function (e) {

  if (e.target.matches('input, select')) {

    validateSingleField(e.target);
  }
});

    function showConfirmModal() {
      const items = document.querySelectorAll('.order-item');
      let html = '';
      items.forEach(item => {
        const name = item.querySelector('.item-info p').textContent;
        const desc = item.querySelector('.item-info small').textContent;
        const price = item.querySelector('.item-price').childNodes[0].textContent.trim();
        const imgSrc = item.querySelector('.item-thumb img')?.src || '';
html += `<div class="modal-item">
  <div class="modal-item-thumb" style="overflow:hidden;border-radius:8px;">
    ${imgSrc ? `<img src="${imgSrc}" style="width:100%;height:100%;object-fit:cover;">` : '📦'}
  </div>
  <div style="flex:1"><p style="font-size:0.85rem;font-weight:500;color:#111;margin:0 0 2px">${name}</p>
  <small style="font-size:0.72rem;color:#777">${desc}</small></div>
  <div style="font-size:0.88rem;font-weight:600;color:#111">${price}</div>
</div>`;
      });
      document.getElementById('modalItems').innerHTML = html;
      const tax = Math.round(subtotal * 0.06);
      const count = items.length;
      document.getElementById('modal-subtotal-label').textContent = 'Subtotal (' + count + ' item' + (count !== 1 ? 's' : '') + ')';
      document.getElementById('modal-subtotal').textContent = fmt(subtotal);
      document.getElementById('modal-tax').textContent = fmt(tax);
      document.getElementById('modal-total').textContent = fmt(subtotal + tax);
      document.getElementById('confirmOverlay').style.display = 'flex';
    }
    function closeConfirmModal() { document.getElementById('confirmOverlay').style.display = 'none'; }
    function submitOrder(btn) {
  btn.innerHTML = '⏳ Processing…'; btn.style.background = '#0056b3'; btn.disabled = true;
  document.getElementById('checkoutForm').submit();  // ← targets checkout form
}
    document.getElementById('confirmOverlay').addEventListener('click', function (e) { if (e.target === this) closeConfirmModal(); });
    document.getElementById('removeOverlay').addEventListener('click', function (e) { if (e.target === this) closeRemoveModal(); });
