/* PAKAL — Main JavaScript
   Interacciones globales para todas las páginas */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Filter toggles (productos.html) ─────────────────────── */
  document.querySelectorAll('.filter-group__toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.filter-group');
      const options = group.querySelector('.filter-options, .price-range, .size-grid, .color-swatches');
      const isOpen = btn.classList.contains('open');
      btn.classList.toggle('open', !isOpen);
      btn.setAttribute('aria-expanded', !isOpen);
      if (options) options.style.display = isOpen ? 'none' : '';
    });
  });

  /* ── Accordion (detalle.html) ─────────────────────────────── */
  document.querySelectorAll('.accordion-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const isOpen = btn.getAttribute('aria-expanded') === 'true';
      const content = btn.nextElementSibling;
      btn.setAttribute('aria-expanded', !isOpen);
      btn.querySelector('span:last-child').textContent = isOpen ? '+' : '−';
      if (content && content.classList.contains('accordion-content')) {
        content.style.display = isOpen ? 'none' : 'block';
      }
    });
  });

  /* ── Gallery thumbs (detalle.html) ───────────────────────── */
  document.querySelectorAll('.product-gallery__thumb').forEach((thumb, i) => {
    thumb.addEventListener('click', () => {
      document.querySelectorAll('.product-gallery__thumb').forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
    });
  });

  /* ── Size selector ────────────────────────────────────────── */
  document.querySelectorAll('.info-size-btn:not(.unavailable), .size-btn:not(.unavailable)').forEach(btn => {
    btn.addEventListener('click', () => {
      const group = btn.closest('.product-info__sizes, .size-grid');
      if (group) group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  /* ── Color swatches ───────────────────────────────────────── */
  document.querySelectorAll('.product-info .color-swatches .color-swatch').forEach(swatch => {
    swatch.addEventListener('click', () => {
      swatch.closest('.color-swatches').querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
      swatch.classList.add('active');
    });
  });

  /* ── Cart quantity buttons ────────────────────────────────── */
  document.querySelectorAll('.cart-item').forEach(item => {
    const minus = item.querySelector('.qty-btn:first-child');
    const plus  = item.querySelector('.qty-btn:last-child');
    const input = item.querySelector('.qty-input');
    if (!minus || !plus || !input) return;
    minus.addEventListener('click', () => {
      const v = parseInt(input.value, 10);
      if (v > 1) input.value = v - 1;
    });
    plus.addEventListener('click', () => {
      const v = parseInt(input.value, 10);
      if (v < 10) input.value = v + 1;
    });
  });

  /* ── Newsletter form ──────────────────────────────────────── */
  const nlForm = document.querySelector('.newsletter__form');
  if (nlForm) {
    nlForm.addEventListener('submit', e => {
      e.preventDefault();
      const input = nlForm.querySelector('.newsletter__input');
      if (input && input.value) {
        input.value = '';
        input.placeholder = '¡Suscripción exitosa! Gracias.';
        nlForm.querySelector('.newsletter__btn').textContent = '✓';
      }
    });
  }

  /* ── View toggle (productos.html) ────────────────────────── */
  document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    });
  });

  /* ── Promo bar close ──────────────────────────────────────── */
  const promoClose = document.querySelector('.promo-bar__close');
  if (promoClose) {
    promoClose.addEventListener('click', () => {
      const bar = document.getElementById('promoBar');
      if (bar) bar.style.display = 'none';
    });
  }

  /* ── Módulo de pago con tarjeta ───────────────────────────── */
  if (document.getElementById('cardNumber')) initPaymentModule();

});

function initPaymentModule() {

  /* — Elementos — */
  const elNum     = document.getElementById('cardNumber');
  const elName    = document.getElementById('cardName');
  const elExpiry  = document.getElementById('cardExpiry');
  const elCvv     = document.getElementById('cardCvv');
  const elForm    = elNum.closest('form');
  const elBtn     = document.getElementById('btnConfirmar');
  const elBtnTxt  = document.getElementById('btnConfirmarText');
  const elMetodo  = document.getElementById('metodo_pago');

  const prevNum    = document.getElementById('previewNumber');
  const prevName   = document.getElementById('previewName');
  const prevExpiry = document.getElementById('previewExpiry');
  const prevCvv    = document.getElementById('previewCvv');
  const cardInner  = document.getElementById('cardInner');

  const errNum    = document.getElementById('errCardNumber');
  const errName   = document.getElementById('errCardName');
  const errExpiry = document.getElementById('errCardExpiry');
  const errCvv    = document.getElementById('errCardCvv');
  const statusNum = document.getElementById('cardNumberStatus');

  /* — Algoritmo de Luhn — */
  function luhn(num) {
    let sum = 0, alt = false;
    for (let i = num.length - 1; i >= 0; i--) {
      let n = parseInt(num[i], 10);
      if (alt) { n *= 2; if (n > 9) n -= 9; }
      sum += n;
      alt = !alt;
    }
    return sum % 10 === 0;
  }

  /* — Detectar tipo de tarjeta — */
  function detectBrand(digits) {
    if (/^4/.test(digits))                        return 'visa';
    if (/^5[1-5]/.test(digits) || /^2[2-7]/.test(digits)) return 'mastercard';
    if (/^3[47]/.test(digits))                    return 'amex';
    return 'generic';
  }

  /* — Actualizar logo en la vista previa — */
  function updateBrandLogo(brand) {
    ['logoGeneric','logoVisa','logoMC','logoAmex'].forEach(id => {
      document.getElementById(id).style.display = 'none';
    });
    const map = { visa:'logoVisa', mastercard:'logoMC', amex:'logoAmex', generic:'logoGeneric' };
    document.getElementById(map[brand]).style.display = '';
  }

  /* — Formatear número: espacios cada 4 dígitos (Amex: 4-6-5) — */
  function formatCardNumber(raw, brand) {
    const digits = raw.replace(/\D/g, '');
    if (brand === 'amex') {
      return digits.replace(/^(\d{4})(\d{0,6})(\d{0,5}).*/, (_, a, b, c) =>
        [a, b, c].filter(Boolean).join('  ')
      );
    }
    return digits.replace(/(.{4})/g, '$1  ').trim();
  }

  /* — Input: número de tarjeta — */
  elNum.addEventListener('input', () => {
    const raw    = elNum.value.replace(/\D/g, '').slice(0, 16);
    const brand  = detectBrand(raw);
    const maxLen = brand === 'amex' ? 15 : 16;
    const digits = raw.slice(0, maxLen);
    const formatted = formatCardNumber(digits, brand);

    elNum.value = formatted;
    updateBrandLogo(brand);

    // Vista previa: reemplazar dígitos conocidos y dejar puntos para el resto
    const pad = digits.padEnd(maxLen, '•');
    if (brand === 'amex') {
      prevNum.innerHTML = `${pad.slice(0,4)}&nbsp;&nbsp;${pad.slice(4,10)}&nbsp;&nbsp;${pad.slice(10,15)}`;
    } else {
      prevNum.innerHTML = [pad.slice(0,4), pad.slice(4,8), pad.slice(8,12), pad.slice(12,16)]
        .join('&nbsp;&nbsp;');
    }

    // Icono de validación
    if (digits.length === maxLen) {
      statusNum.textContent = luhn(digits) ? '✓' : '✗';
      statusNum.style.color = luhn(digits) ? '#2E7D32' : '#C44A4A';
    } else {
      statusNum.textContent = '';
    }
    errNum.textContent = '';
  });

  /* — Input: nombre del titular — */
  elName.addEventListener('input', () => {
    const val = elName.value.toUpperCase().replace(/[^A-Z\s]/g, '');
    elName.value = val;
    prevName.textContent = val || 'NOMBRE APELLIDO';
    errName.textContent = '';
  });

  /* — Input: fecha de vencimiento — */
  elExpiry.addEventListener('input', () => {
    let val = elExpiry.value.replace(/\D/g, '');
    if (val.length >= 2) val = val.slice(0,2) + '/' + val.slice(2,4);
    elExpiry.value = val;
    prevExpiry.textContent = val || 'MM/AA';
    errExpiry.textContent = '';
  });

  /* — Input: CVV — voltear tarjeta — */
  elCvv.addEventListener('focus', () => cardInner.classList.add('flipped'));
  elCvv.addEventListener('blur',  () => cardInner.classList.remove('flipped'));
  elCvv.addEventListener('input', () => {
    const val = elCvv.value.replace(/\D/g, '').slice(0, 4);
    elCvv.value = val;
    prevCvv.textContent = '•'.repeat(val.length) || '•••';
    errCvv.textContent = '';
  });

  /* — Tabs de método de pago — */
  document.querySelectorAll('.pay-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.pay-tab').forEach(t => t.classList.remove('pay-tab--active'));
      document.querySelectorAll('.pay-panel').forEach(p => p.style.display = 'none');
      tab.classList.add('pay-tab--active');
      const method = tab.dataset.method;
      document.getElementById('panel-' + method).style.display = '';
      elMetodo.value = method;
    });
  });

  /* — Validación completa al enviar — */
  elForm.addEventListener('submit', function(e) {
    if (elMetodo.value !== 'tarjeta') return; // contra entrega: sin validación de tarjeta

    e.preventDefault();
    let valid = true;

    const digits = elNum.value.replace(/\D/g, '');
    const brand  = detectBrand(digits);
    const maxLen = brand === 'amex' ? 15 : 16;

    // Número
    if (digits.length < maxLen) {
      errNum.textContent = 'Ingresa los ' + maxLen + ' dígitos de la tarjeta.';
      valid = false;
    } else if (!luhn(digits)) {
      errNum.textContent = 'El número de tarjeta no es válido.';
      valid = false;
    }

    // Nombre
    if (elName.value.trim().length < 3) {
      errName.textContent = 'Ingresa el nombre como aparece en la tarjeta.';
      valid = false;
    }

    // Expiración
    const [mm, yy] = elExpiry.value.split('/').map(Number);
    const now = new Date();
    const expYear  = 2000 + (yy || 0);
    const expMonth = mm || 0;
    if (!mm || !yy || mm < 1 || mm > 12) {
      errExpiry.textContent = 'Formato inválido. Usa MM/AA.';
      valid = false;
    } else if (expYear < now.getFullYear() ||
              (expYear === now.getFullYear() && expMonth < now.getMonth() + 1)) {
      errExpiry.textContent = 'La tarjeta está vencida.';
      valid = false;
    }

    // CVV
    const cvvLen = brand === 'amex' ? 4 : 3;
    if (elCvv.value.length < cvvLen) {
      errCvv.textContent = `El CVV debe tener ${cvvLen} dígitos.`;
      valid = false;
    }

    if (!valid) return;

    // Simular procesamiento de pago
    elBtn.disabled = true;
    elBtn.style.opacity = '0.7';
    elBtnTxt.textContent = 'Procesando pago…';

    let dots = 0;
    const interval = setInterval(() => {
      dots = (dots + 1) % 4;
      elBtnTxt.textContent = 'Procesando pago' + '.'.repeat(dots);
    }, 400);

    setTimeout(() => {
      clearInterval(interval);
      elBtn.style.background = '#2E7D32';
      elBtn.style.borderColor = '#2E7D32';
      elBtnTxt.textContent = '✓ Pago aprobado — enviando pedido…';
      setTimeout(() => elForm.submit(), 800);
    }, 2200);
  });
}
