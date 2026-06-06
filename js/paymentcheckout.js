function fmtCard(el) {
      let v = el.value.replace(/\D/g, '').slice(0, 16);
      el.value = v.match(/.{1,4}/g)?.join(' ') ?? v;
    }
    function fmtExp(el) {
      let v = el.value.replace(/\D/g, '').slice(0, 4);
      if (v.length >= 3) v = v.slice(0, 2) + ' / ' + v.slice(2);
      el.value = v;
    }
    function doPay() {
      document.querySelectorAll('.client-err').forEach(e => e.remove());
      document.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));
      const cardNum = document.getElementById('cardNum');
      const cardName = document.getElementById('cardName');
      const expiry = document.getElementById('expiry');
      const cvv = document.getElementById('cvv');
      let valid = true;
      if (!cardNum.value.trim()) { showErr(cardNum, 'Card number is required.'); valid = false; }
      else if (cardNum.value.replace(/\s/g, '').length < 16) { showErr(cardNum, 'Enter a valid 16-digit card number.'); valid = false; }
      if (!cardName.value.trim()) { showErr(cardName, 'Name on card is required.'); valid = false; }
      if (!expiry.value.trim()) { showErr(expiry, 'Expiry date is required.'); valid = false; }
      else if (!/^\d{2}\s*\/\s*\d{2}$/.test(expiry.value)) { showErr(expiry, 'Use MM / YY format.'); valid = false; }
      else {
        const raw = expiry.value.replace(/\s/g, ''); // strip all spaces → e.g. "0126"
        const expM = parseInt(raw.substring(0, 2), 10);
        const expY = 2000 + parseInt(raw.substring(2, 4), 10);
        const now = new Date(); const curM = now.getMonth() + 1; const curY = now.getFullYear();
        if (expM < 1 || expM > 12) { showErr(expiry, 'Invalid month.'); valid = false; }
        else if (expY < curY || (expY === curY && expM < curM)) { showErr(expiry, 'This card has expired.'); valid = false; }
      }
      if (!cvv.value.trim()) { showErr(cvv, 'CVV is required.'); valid = false; }
      else if (!/^\d{3}$/.test(cvv.value)) { showErr(cvv, 'CVV must be 3 digits.'); valid = false; }
      if (!valid) return;
      document.getElementById('payForm').submit();
    }
    function showErr(input, msg) {
      input.classList.add('is-error');
      const err = document.createElement('div');
      err.className = 'field-error client-err'; err.textContent = msg;
      input.closest('.field').appendChild(err);
    }
    document.addEventListener('input', function (e) {
      if (e.target.classList.contains('is-error')) {
        e.target.classList.remove('is-error');
        e.target.closest('.field')?.querySelector('.client-err')?.remove();
      }
    });