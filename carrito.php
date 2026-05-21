<?php
$pageTitle = 'Carrito — PAKAL';
$activeNav = '';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <div class="cart-page container">

      <!-- Progress steps -->
      <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:56px;padding-top:48px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;background:var(--black);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:600;color:var(--white);">1</div>
          <span style="font-size:0.68rem;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Carrito</span>
        </div>
        <div style="width:80px;height:1px;background:var(--border);margin:0 16px;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:600;color:var(--gray-light);">2</div>
          <span style="font-size:0.68rem;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">Datos</span>
        </div>
        <div style="width:80px;height:1px;background:var(--border);margin:0 16px;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:600;color:var(--gray-light);">3</div>
          <span style="font-size:0.68rem;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">Pago</span>
        </div>
      </div>

      <div class="cart-page__inner">

        <!-- Cart Items -->
        <div>
          <div class="cart-header">
            <h1 class="cart-header__title">Mi Carrito</h1>
            <span class="cart-header__count">3 productos</span>
          </div>

          <article class="cart-item" aria-label="Blazer Estructurado Chichen">
            <div class="cart-item__img">
              <div class="cart-item__img-placeholder ci-1" style="width:100%;height:100%;"></div>
            </div>
            <div class="cart-item__info">
              <p class="cart-item__brand">PAKAL Heritage</p>
              <h2 class="cart-item__name">Blazer Estructurado Chichen</h2>
              <div class="cart-item__attrs">
                <span class="cart-item__attr">Talla: <span>M</span></span>
                <span class="cart-item__attr">Color: <span>Negro Obsidiana</span></span>
              </div>
              <div class="cart-item__qty" aria-label="Cantidad">
                <button class="qty-btn" aria-label="Disminuir cantidad">−</button>
                <input type="number" class="qty-input" value="1" min="1" max="10" aria-label="Cantidad">
                <button class="qty-btn" aria-label="Aumentar cantidad">+</button>
              </div>
            </div>
            <div class="cart-item__price-col">
              <span class="cart-item__price">$6,800 MXN</span>
              <button class="cart-item__remove" aria-label="Eliminar del carrito">Eliminar</button>
            </div>
          </article>

          <article class="cart-item" aria-label="Pantalón Sastre Uxmal">
            <div class="cart-item__img">
              <div class="cart-item__img-placeholder ci-2" style="width:100%;height:100%;"></div>
            </div>
            <div class="cart-item__info">
              <p class="cart-item__brand">PAKAL Noir</p>
              <h2 class="cart-item__name">Pantalón Sastre Uxmal</h2>
              <div class="cart-item__attrs">
                <span class="cart-item__attr">Talla: <span>32</span></span>
                <span class="cart-item__attr">Color: <span>Negro</span></span>
              </div>
              <div class="cart-item__qty" aria-label="Cantidad">
                <button class="qty-btn" aria-label="Disminuir cantidad">−</button>
                <input type="number" class="qty-input" value="1" min="1" max="10" aria-label="Cantidad">
                <button class="qty-btn" aria-label="Aumentar cantidad">+</button>
              </div>
            </div>
            <div class="cart-item__price-col">
              <span class="cart-item__price">$3,200 MXN</span>
              <button class="cart-item__remove" aria-label="Eliminar del carrito">Eliminar</button>
            </div>
          </article>

          <article class="cart-item" aria-label="Camisa de Lino Palenque">
            <div class="cart-item__img">
              <div class="cart-item__img-placeholder ci-3" style="width:100%;height:100%;"></div>
            </div>
            <div class="cart-item__info">
              <p class="cart-item__brand">PAKAL Origin</p>
              <h2 class="cart-item__name">Camisa de Lino Palenque</h2>
              <div class="cart-item__attrs">
                <span class="cart-item__attr">Talla: <span>M</span></span>
                <span class="cart-item__attr">Color: <span>Blanco Cenote</span></span>
              </div>
              <div class="cart-item__qty" aria-label="Cantidad">
                <button class="qty-btn" aria-label="Disminuir cantidad">−</button>
                <input type="number" class="qty-input" value="2" min="1" max="10" aria-label="Cantidad">
                <button class="qty-btn" aria-label="Aumentar cantidad">+</button>
              </div>
            </div>
            <div class="cart-item__price-col">
              <span class="cart-item__price">$5,400 MXN</span>
              <span style="font-size:0.68rem;color:var(--gray-light);">$2,700 × 2</span>
              <button class="cart-item__remove" aria-label="Eliminar del carrito">Eliminar</button>
            </div>
          </article>

          <div style="padding-top:24px;">
            <a href="/proyecto/productos.php" style="display:inline-flex;align-items:center;gap:8px;font-size:0.68rem;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-mid);transition:color 0.28s ease;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
              Continuar comprando
            </a>
          </div>
        </div>

        <!-- Order Summary -->
        <aside class="cart-summary" aria-label="Resumen del pedido">
          <h2 class="cart-summary__title">Resumen del pedido</h2>

          <div class="summary-row">
            <span class="summary-row__label">Subtotal (3 productos)</span>
            <span class="summary-row__value">$15,400 MXN</span>
          </div>
          <div class="summary-row">
            <span class="summary-row__label">Envío</span>
            <span class="summary-row__value" style="color:#2E7D32;font-size:0.78rem;letter-spacing:0.06em;">Gratis</span>
          </div>
          <div class="summary-row">
            <span class="summary-row__label">Descuento aplicado</span>
            <span class="summary-row__value" style="color:#C44A4A;">−$0 MXN</span>
          </div>

          <div class="cart-summary__promo" aria-label="Código de descuento">
            <input type="text" placeholder="Código de descuento" aria-label="Código de descuento">
            <button>Aplicar</button>
          </div>

          <div class="summary-row summary-row--total">
            <span class="summary-row__label">Total</span>
            <span class="summary-row__value">$15,400 MXN</span>
          </div>

          <p style="font-size:0.65rem;color:var(--gray-light);margin:8px 0 20px;letter-spacing:0.04em;line-height:1.6;">
            Incluye IVA. El precio final puede variar según método de pago.
          </p>

          <button class="cart-summary__checkout" onclick="<?php if (!estaLogueado()): ?>window.location='/proyecto/auth.php'<?php else: ?>alert('Proceder al pago')<?php endif; ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Finalizar compra
          </button>

          <?php if (!estaLogueado()): ?>
          <p style="font-size:0.65rem;color:var(--gray-mid);margin-top:10px;text-align:center;">
            Necesitas <a href="/proyecto/auth.php" style="color:var(--dark);text-decoration:underline;">iniciar sesión</a> para completar tu pedido.
          </p>
          <?php endif; ?>

          <div class="cart-summary__secure">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Pago 100% seguro · SSL
          </div>

          <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);">
            <p style="font-size:0.6rem;letter-spacing:0.14em;text-transform:uppercase;color:var(--gray-light);margin-bottom:12px;">Métodos de pago aceptados</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
              <div style="border:1px solid var(--border);padding:5px 10px;font-size:0.65rem;color:var(--gray-mid);">Visa</div>
              <div style="border:1px solid var(--border);padding:5px 10px;font-size:0.65rem;color:var(--gray-mid);">Mastercard</div>
              <div style="border:1px solid var(--border);padding:5px 10px;font-size:0.65rem;color:var(--gray-mid);">Amex</div>
              <div style="border:1px solid var(--border);padding:5px 10px;font-size:0.65rem;color:var(--gray-mid);">OXXO Pay</div>
              <div style="border:1px solid var(--border);padding:5px 10px;font-size:0.65rem;color:var(--gray-mid);">Transferencia</div>
            </div>
          </div>
        </aside>

      </div>

      <!-- Recommended -->
      <div style="margin-top:80px;padding-top:60px;border-top:1px solid var(--border);">
        <div class="section-header">
          <div class="section-header__left">
            <span class="section-label">También te puede interesar</span>
            <h2 class="section-title">Completa <em>tu look</em></h2>
          </div>
          <a href="/proyecto/productos.php" class="section-header__right">Ver más</a>
        </div>
        <div class="product-grid">
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--5">Imagen Editorial</div>
              <button class="product-card__wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Heritage</p>
              <h3 class="product-card__name">Cinturón Piel Tikal</h3>
              <div class="product-card__price-row"><span class="product-card__price">$1,800 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--4">Imagen Editorial</div>
              <button class="product-card__wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Noir</p>
              <h3 class="product-card__name">Zapato Oxford Copán</h3>
              <div class="product-card__price-row"><span class="product-card__price">$8,900 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--6">Imagen Editorial</div>
              <button class="product-card__wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Origin</p>
              <h3 class="product-card__name">Pañuelo Seda Ixchel</h3>
              <div class="product-card__price-row"><span class="product-card__price">$680 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--2">Imagen Editorial</div>
              <button class="product-card__wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Heritage</p>
              <h3 class="product-card__name">Suéter Merino Xibalbá</h3>
              <div class="product-card__price-row"><span class="product-card__price">$4,100 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
        </div>
      </div>

    </div>

<?php require_once 'includes/footer.php'; ?>
