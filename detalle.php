<?php
$pageTitle = 'Blazer Estructurado Chichen — PAKAL';
$activeNav = 'ropa';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <!-- Breadcrumb -->
    <div class="container" style="padding-top:28px;padding-bottom:0;">
      <nav class="page-hero__breadcrumb" aria-label="Ruta de navegación">
        <a href="/proyecto/index.php">Inicio</a>
        <span>›</span>
        <a href="/proyecto/index.php">Hombre</a>
        <span>›</span>
        <a href="/proyecto/productos.php">Ropa</a>
        <span>›</span>
        <a href="/proyecto/productos.php">Blazers</a>
        <span>›</span>
        <span style="color:var(--dark);">Blazer Estructurado Chichen</span>
      </nav>
    </div>

    <!-- PRODUCT DETAIL -->
    <section class="product-detail container" aria-label="Detalle del producto">
      <div class="product-detail__inner">

        <!-- Gallery -->
        <div class="product-gallery" aria-label="Galería de imágenes del producto">
          <div class="product-gallery__main" role="img" aria-label="Imagen principal">
            <div class="product-gallery__main-placeholder">
              <div style="
                width:100%;height:100%;
                display:flex;flex-direction:column;
                align-items:center;justify-content:center;
                font-family:var(--font-display);
                color:var(--gray-light);
                background: linear-gradient(155deg, #E5E1DC 0%, #D0CBC4 100%);
                gap:12px;
              ">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.4"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span style="font-size:0.72rem;letter-spacing:0.12em;text-transform:uppercase;opacity:0.5;">Fotografía editorial</span>
              </div>
            </div>
          </div>

          <div class="product-gallery__thumbs" role="list" aria-label="Miniaturas de producto">
            <div class="product-gallery__thumb active" role="listitem"><div class="product-gallery__thumb-img thumb-img-1"></div></div>
            <div class="product-gallery__thumb" role="listitem"><div class="product-gallery__thumb-img thumb-img-2"></div></div>
            <div class="product-gallery__thumb" role="listitem"><div class="product-gallery__thumb-img thumb-img-3"></div></div>
            <div class="product-gallery__thumb" role="listitem"><div class="product-gallery__thumb-img thumb-img-4"></div></div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="product-info">

          <p class="product-info__brand">PAKAL Heritage</p>
          <h1 class="product-info__name">Blazer Estructurado<br>Chichen</h1>
          <p class="product-info__price">$6,800 MXN</p>
          <p class="product-info__price-note">IVA incluido · Envío gratuito</p>

          <div class="product-info__divider"></div>

          <p class="product-info__label">
            Color: <span style="font-weight:400;color:var(--gray-mid);text-transform:none;letter-spacing:0;">Negro Obsidiana</span>
          </p>
          <div class="color-swatches" style="margin-bottom:28px;">
            <div class="color-swatch swatch-negro active" title="Negro Obsidiana"></div>
            <div class="color-swatch swatch-gris" title="Gris Ceniza"></div>
            <div class="color-swatch swatch-azul" title="Azul Medianoche"></div>
            <div class="color-swatch swatch-cafe" title="Café Cacao"></div>
          </div>

          <p class="product-info__label">
            Talla
            <span class="product-info__size-guide">Guía de tallas</span>
          </p>
          <div class="product-info__sizes">
            <button class="info-size-btn unavailable">XS</button>
            <button class="info-size-btn">S</button>
            <button class="info-size-btn active">M</button>
            <button class="info-size-btn">L</button>
            <button class="info-size-btn">XL</button>
            <button class="info-size-btn unavailable">XXL</button>
          </div>

          <div class="product-info__actions">
            <button class="btn-add-cart" onclick="window.location='/proyecto/carrito.php'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
              Agregar al carrito
            </button>
            <button class="btn-wishlist-full">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              Agregar a Wishlist
            </button>
          </div>

          <div class="product-info__divider"></div>

          <p class="product-info__description">
            El Blazer Estructurado Chichen es una pieza de ingeniería textil que rinde homenaje a la arquitectura maya. Su silueta definida evoca las líneas precisas del templo de Chichen Itzá, mientras que su interior forrado en seda natural garantiza un confort excepcional durante todo el día.
          </p>

          <div class="product-info__meta">
            <div class="meta-row">
              <span class="meta-row__key">SKU</span>
              <span class="meta-row__val">PK-BLZ-CHI-001-M-N</span>
            </div>
            <div class="meta-row">
              <span class="meta-row__key">Colección</span>
              <span class="meta-row__val">Colección Tikal — Primavera 2026</span>
            </div>
            <div class="meta-row">
              <span class="meta-row__key">Origen</span>
              <span class="meta-row__val">Confeccionado en México</span>
            </div>
          </div>

          <div class="product-accordion">
            <div class="accordion-item">
              <button class="accordion-btn" aria-expanded="true">
                Materiales &amp; Composición <span>−</span>
              </button>
              <div class="accordion-content">
                <p>70% Lana virgen · 20% Seda natural · 10% Cachemira<br>Forro: 100% Seda natural<br>Botones: Cuerno natural certificado</p>
              </div>
            </div>
            <div class="accordion-item">
              <button class="accordion-btn" aria-expanded="false">Cuidado de la Prenda <span>+</span></button>
            </div>
            <div class="accordion-item">
              <button class="accordion-btn" aria-expanded="false">Envío y Devoluciones <span>+</span></button>
            </div>
            <div class="accordion-item">
              <button class="accordion-btn" aria-expanded="false">Artesanía Maya <span>+</span></button>
            </div>
          </div>

          <div style="display:flex;gap:24px;margin-top:28px;padding-top:24px;border-top:1px solid var(--border);">
            <div style="display:flex;align-items:center;gap:8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span style="font-size:0.68rem;color:var(--gray-mid);letter-spacing:0.06em;">Autenticidad garantizada</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
              <span style="font-size:0.68rem;color:var(--gray-mid);letter-spacing:0.06em;">Envío express gratis</span>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--gold)" stroke-width="1.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
              <span style="font-size:0.68rem;color:var(--gray-mid);letter-spacing:0.06em;">Devolución 30 días</span>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- RELATED PRODUCTS -->
    <section class="related-products" aria-label="Productos relacionados">
      <div class="container">
        <div class="section-header">
          <div class="section-header__left">
            <span class="section-label">Completa el look</span>
            <h2 class="section-title">Prendas <em>relacionadas</em></h2>
          </div>
          <a href="/proyecto/productos.php" class="section-header__right">Ver más</a>
        </div>
        <div class="product-grid">
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--2">Imagen Editorial</div>
              <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Noir</p>
              <h3 class="product-card__name">Pantalón Sastre Uxmal</h3>
              <div class="product-card__price-row"><span class="product-card__price">$3,200 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--3">Imagen Editorial</div>
              <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Origin</p>
              <h3 class="product-card__name">Camisa de Lino Palenque</h3>
              <div class="product-card__price-row"><span class="product-card__price">$2,700 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
          <article class="product-card">
            <div class="product-card__media">
              <div class="product-card__img-placeholder img-placeholder--5">Imagen Editorial</div>
              <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
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
              <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand">PAKAL Noir</p>
              <h3 class="product-card__name">Zapato Oxford Copán</h3>
              <div class="product-card__price-row"><span class="product-card__price">$8,900 MXN</span></div>
              <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
            </div>
          </article>
        </div>
      </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
