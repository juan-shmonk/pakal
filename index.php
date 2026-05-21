<?php
$pageTitle = 'PAKAL — Moda Premium';
$activeNav = 'inicio';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <!-- HERO -->
    <section class="hero" aria-label="Portada principal">
      <div class="hero__media">
        <span class="hero__img-label" aria-hidden="true">Editorial</span>
      </div>
      <div class="hero__overlay" aria-hidden="true"></div>
      <div class="hero__content">
        <p class="hero__tag">Colección Tikal — Primavera 2026</p>
        <h1 class="hero__title">
          La Herencia<br>
          <em>del Tiempo</em>
        </h1>
        <p class="hero__subtitle">
          Inspirados en la magnificencia de la civilización maya. Prendas que fusionan la tradición ancestral con la elegancia contemporánea.
        </p>
        <div class="hero__actions">
          <a href="/proyecto/productos.php" class="btn btn--white btn--lg">Explorar Colección</a>
          <a href="#destacados" class="btn btn--lg" style="border-color:rgba(255,255,255,0.3);color:rgba(255,255,255,0.7);">Ver Novedades</a>
        </div>
      </div>
      <div class="hero__scroll-hint" aria-hidden="true">
        <span>Desplazar</span>
        <div class="hero__scroll-line"></div>
      </div>
    </section>

    <!-- CATEGORY STRIP -->
    <section class="category-strip" aria-label="Catálogos por género">
      <div class="category-strip__inner">

        <article class="category-strip__item">
          <div class="category-strip__media media-1" data-label="H" aria-label="Fotografía editorial masculina"></div>
          <div class="category-strip__body">
            <h2 class="category-strip__name">Colección Hombre</h2>
            <p class="category-strip__count">248 prendas</p>
            <a href="/proyecto/productos.php?genero=hombre" class="btn btn--sm">Explorar</a>
          </div>
        </article>

        <article class="category-strip__item">
          <div class="category-strip__media media-2" data-label="M" aria-label="Fotografía editorial femenina"></div>
          <div class="category-strip__body">
            <h2 class="category-strip__name">Colección Mujer</h2>
            <p class="category-strip__count">312 prendas</p>
            <a href="/proyecto/productos.php?genero=mujer" class="btn btn--sm">Explorar</a>
          </div>
        </article>

        <article class="category-strip__item">
          <div class="category-strip__media media-3" data-label="I" aria-label="Fotografía editorial infantil"></div>
          <div class="category-strip__body">
            <h2 class="category-strip__name">Colección Infantil</h2>
            <p class="category-strip__count">96 prendas</p>
            <a href="/proyecto/productos.php?genero=infantil" class="btn btn--sm">Explorar</a>
          </div>
        </article>

      </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <section class="products-section container" id="destacados" aria-label="Productos destacados">
      <div class="section-header">
        <div class="section-header__left">
          <span class="section-label">Curación editorial</span>
          <h2 class="section-title">Destacados de <em>Temporada</em></h2>
        </div>
        <a href="/proyecto/productos.php" class="section-header__right">Ver todo</a>
      </div>

      <div class="product-grid" role="list">

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--1">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Heritage</p>
            <h3 class="product-card__name">Blazer Estructurado Chichen</h3>
            <div class="product-card__price-row">
              <span class="product-card__price">$6,800 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--2">Imagen Editorial</div>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Noir</p>
            <h3 class="product-card__name">Pantalón Sastre Uxmal</h3>
            <div class="product-card__price-row">
              <span class="product-card__price">$3,200 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--3">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--rebaja">−30%</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Origin</p>
            <h3 class="product-card__name">Camisa de Lino Palenque</h3>
            <div class="product-card__price-row">
              <span class="product-card__price product-card__price--sale">$1,890 MXN</span>
              <span class="product-card__price--old">$2,700 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--4">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Heritage</p>
            <h3 class="product-card__name">Abrigo de Mezcla Cobá</h3>
            <div class="product-card__price-row">
              <span class="product-card__price">$12,400 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--5">Imagen Editorial</div>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Noir</p>
            <h3 class="product-card__name">Polo Piqué Tulum</h3>
            <div class="product-card__price-row">
              <span class="product-card__price">$1,650 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--6">Imagen Editorial</div>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Origin</p>
            <h3 class="product-card__name">Suéter Merino Xibalbá</h3>
            <div class="product-card__price-row">
              <span class="product-card__price">$4,100 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--1">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--rebaja">−20%</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Heritage</p>
            <h3 class="product-card__name">Cinturón Piel Tikal</h3>
            <div class="product-card__price-row">
              <span class="product-card__price product-card__price--sale">$1,440 MXN</span>
              <span class="product-card__price--old">$1,800 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--3">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Noir</p>
            <h3 class="product-card__name">Zapato Oxford Copán</h3>
            <div class="product-card__price-row">
              <span class="product-card__price">$8,900 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>

      </div>

      <div class="text-center mt-lg">
        <a href="/proyecto/productos.php" class="btn btn--lg">Ver toda la colección</a>
      </div>
    </section>

    <!-- MAYA HERITAGE BANNER -->
    <section class="maya-banner" aria-label="Herencia Maya">
      <div class="maya-banner__inner">
        <div class="maya-banner__glyph" aria-hidden="true">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" width="48" height="48">
            <circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="1"/>
            <circle cx="24" cy="24" r="13" stroke="currentColor" stroke-width="1"/>
            <line x1="24" y1="2" x2="24" y2="11" stroke="currentColor" stroke-width="1"/>
            <line x1="24" y1="37" x2="24" y2="46" stroke="currentColor" stroke-width="1"/>
            <line x1="2" y1="24" x2="11" y2="24" stroke="currentColor" stroke-width="1"/>
            <line x1="37" y1="24" x2="46" y2="24" stroke="currentColor" stroke-width="1"/>
            <line x1="7.5" y1="7.5" x2="13.5" y2="13.5" stroke="currentColor" stroke-width="1"/>
            <line x1="34.5" y1="34.5" x2="40.5" y2="40.5" stroke="currentColor" stroke-width="1"/>
            <line x1="40.5" y1="7.5" x2="34.5" y2="13.5" stroke="currentColor" stroke-width="1"/>
            <line x1="7.5" y1="40.5" x2="13.5" y2="34.5" stroke="currentColor" stroke-width="1"/>
            <rect x="20" y="20" width="8" height="8" fill="currentColor" opacity="0.6"/>
          </svg>
        </div>
        <p class="maya-banner__label">Raíces — Herencia — Distinción</p>
        <h2 class="maya-banner__title">
          Vestir con la <em>sabiduría</em><br>de los ancestros
        </h2>
        <p class="maya-banner__text">
          Cada pieza PAKAL nace del respeto profundo por la civilización maya: su geometría sagrada, sus texturas naturales y su filosofía del tiempo eterno.
        </p>
        <a href="#" class="btn btn--white">Nuestra Historia</a>
      </div>
    </section>

    <!-- BENEFITS -->
    <section class="benefits" aria-label="Beneficios de compra">
      <div class="container">
        <div class="benefits__grid">
          <div class="benefit">
            <div class="benefit__icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            </div>
            <h3 class="benefit__title">Envío Premium</h3>
            <p class="benefit__text">Envío express gratuito en pedidos mayores a $2,500 MXN. Entrega en 24–48 h.</p>
          </div>
          <div class="benefit">
            <div class="benefit__icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            </div>
            <h3 class="benefit__title">Devoluciones Fáciles</h3>
            <p class="benefit__text">30 días para devolver o cambiar sin preguntas. Proceso simple y gratuito.</p>
          </div>
          <div class="benefit">
            <div class="benefit__icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h3 class="benefit__title">Pago Seguro</h3>
            <p class="benefit__text">Cifrado SSL de nivel bancario. Aceptamos tarjetas, transferencias y OXXO Pay.</p>
          </div>
          <div class="benefit">
            <div class="benefit__icon" aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.07 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21 16z"/></svg>
            </div>
            <h3 class="benefit__title">Asesoría Personal</h3>
            <p class="benefit__text">Estilistas disponibles vía chat y WhatsApp de lunes a sábado de 9 am a 8 pm.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- COLECCIÓN MUJER -->
    <section class="products-section container" aria-label="Novedades para mujer">
      <div class="section-header">
        <div class="section-header__left">
          <span class="section-label">Colección Mujer</span>
          <h2 class="section-title">Elegancia <em>sin límites</em></h2>
        </div>
        <a href="/proyecto/productos.php?genero=mujer" class="section-header__right">Ver colección</a>
      </div>
      <div class="product-grid">
        <article class="product-card">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--2">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Femme</p>
            <h3 class="product-card__name">Vestido Midi Ixchel</h3>
            <div class="product-card__price-row"><span class="product-card__price">$5,400 MXN</span></div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>
        <article class="product-card">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--4">Imagen Editorial</div>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Femme</p>
            <h3 class="product-card__name">Blusa Seda Ixmucané</h3>
            <div class="product-card__price-row"><span class="product-card__price">$3,100 MXN</span></div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>
        <article class="product-card">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--6">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--rebaja">−25%</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Femme</p>
            <h3 class="product-card__name">Falda Plisada Copán</h3>
            <div class="product-card__price-row">
              <span class="product-card__price product-card__price--sale">$2,250 MXN</span>
              <span class="product-card__price--old">$3,000 MXN</span>
            </div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>
        <article class="product-card">
          <div class="product-card__media">
            <div class="product-card__img-placeholder img-placeholder--3">Imagen Editorial</div>
            <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
            <button class="product-card__wishlist" aria-label="Agregar a wishlist">♡</button>
          </div>
          <div class="product-card__body">
            <p class="product-card__brand">PAKAL Femme</p>
            <h3 class="product-card__name">Pantalón Wide Leg Tulum</h3>
            <div class="product-card__price-row"><span class="product-card__price">$3,800 MXN</span></div>
            <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
          </div>
        </article>
      </div>
    </section>

    <!-- NEWSLETTER -->
    <section class="newsletter" aria-label="Suscripción al boletín">
      <div class="newsletter__inner">
        <div style="margin:0 auto 24px;width:28px;height:28px;color:var(--gold);opacity:0.6;" aria-hidden="true">
          <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="1" y="1" width="26" height="26" stroke="currentColor" stroke-width="1"/>
            <rect x="5" y="5" width="18" height="18" stroke="currentColor" stroke-width="1"/>
            <rect x="11" y="11" width="6" height="6" fill="currentColor"/>
          </svg>
        </div>
        <span class="section-label" style="justify-content:center;display:flex;">Comunidad PAKAL</span>
        <h2 class="newsletter__title">Sé el primero en saberlo</h2>
        <p class="newsletter__subtitle">
          Suscríbete para recibir novedades exclusivas, lanzamientos anticipados y acceso a ventas privadas.
        </p>
        <form class="newsletter__form" onsubmit="return false;" novalidate>
          <input type="email" class="newsletter__input" placeholder="Tu correo electrónico" required aria-label="Correo electrónico">
          <button type="submit" class="newsletter__btn">Suscribirme</button>
        </form>
        <p style="font-size:0.65rem;color:var(--gray-light);margin-top:14px;letter-spacing:0.06em;">Sin spam. Puedes cancelar en cualquier momento.</p>
      </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
