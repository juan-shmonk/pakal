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

});
