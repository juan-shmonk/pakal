<?php
$pageTitle = 'Ropa — PAKAL';
$activeNav = 'ropa';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <!-- PAGE HERO -->
    <div class="page-hero container">
      <nav class="page-hero__breadcrumb" aria-label="Ruta de navegación">
        <a href="/proyecto/index.php">Inicio</a>
        <span>›</span>
        <a href="/proyecto/index.php">Hombre</a>
        <span>›</span>
        <span style="color:var(--dark);">Ropa</span>
      </nav>
      <h1 class="page-hero__title">Ropa <em style="font-family:var(--font-display);font-style:italic;color:var(--gray-mid);">para Hombre</em></h1>
      <p class="page-hero__count">248 productos encontrados</p>
    </div>

    <!-- PRODUCTS LAYOUT -->
    <div class="products-page">
      <div class="container">
        <div class="products-layout">

          <!-- SIDEBAR FILTERS -->
          <aside class="filters" aria-label="Filtros de productos">
            <div class="filters__header">
              <span class="filters__title">Filtros</span>
              <button class="filters__clear">Limpiar todo</button>
            </div>

            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Categoría</button>
              <div class="filter-options">
                <label><input type="checkbox" checked> Blazers y Sacos</label>
                <label><input type="checkbox"> Camisas</label>
                <label><input type="checkbox"> Pantalones</label>
                <label><input type="checkbox"> Abrigos</label>
                <label><input type="checkbox"> Suéteres y Tejidos</label>
                <label><input type="checkbox"> Camisetas</label>
                <label><input type="checkbox"> Bermudas</label>
                <label><input type="checkbox"> Ropa Interior</label>
              </div>
            </div>

            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Precio</button>
              <div class="price-range">
                <div class="price-range__inputs">
                  <div>
                    <p class="price-range__label">Mínimo</p>
                    <input type="number" class="form-input" value="0" placeholder="$0">
                  </div>
                  <div>
                    <p class="price-range__label">Máximo</p>
                    <input type="number" class="form-input" value="20000" placeholder="$20,000">
                  </div>
                </div>
                <button class="btn btn--sm" style="width:100%;justify-content:center;">Aplicar</button>
              </div>
            </div>

            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Talla</button>
              <div class="size-grid">
                <button class="size-btn">XS</button>
                <button class="size-btn">S</button>
                <button class="size-btn active">M</button>
                <button class="size-btn">L</button>
                <button class="size-btn">XL</button>
                <button class="size-btn">XXL</button>
                <button class="size-btn unavailable">XXXL</button>
              </div>
            </div>

            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Color</button>
              <div class="color-swatches">
                <div class="color-swatch swatch-negro active" title="Negro"></div>
                <div class="color-swatch swatch-blanco" title="Blanco"></div>
                <div class="color-swatch swatch-gris" title="Gris"></div>
                <div class="color-swatch swatch-azul" title="Azul marino"></div>
                <div class="color-swatch swatch-cafe" title="Café"></div>
                <div class="color-swatch swatch-beis" title="Beige"></div>
                <div class="color-swatch swatch-verde" title="Verde oscuro"></div>
                <div class="color-swatch swatch-rojo" title="Burdeos"></div>
              </div>
            </div>

            <div class="filter-group">
              <button class="filter-group__toggle" aria-expanded="false">Colección</button>
              <div class="filter-options">
                <label><input type="checkbox"> Colección Tikal</label>
                <label><input type="checkbox"> Colección Chichen</label>
                <label><input type="checkbox"> Colección Uxmal</label>
                <label><input type="checkbox"> PAKAL Heritage</label>
                <label><input type="checkbox"> PAKAL Noir</label>
                <label><input type="checkbox"> PAKAL Origin</label>
              </div>
            </div>

            <div class="filter-group">
              <button class="filter-group__toggle" aria-expanded="false">Tejido y Material</button>
              <div class="filter-options">
                <label><input type="checkbox"> Algodón</label>
                <label><input type="checkbox"> Lino</label>
                <label><input type="checkbox"> Lana</label>
                <label><input type="checkbox"> Seda</label>
                <label><input type="checkbox"> Mezcla premium</label>
              </div>
            </div>

          </aside>

          <!-- PRODUCT AREA -->
          <div>

            <div class="products-toolbar">
              <div class="products-toolbar__sort">
                <label for="sortSelect">Ordenar por</label>
                <select id="sortSelect" class="products-toolbar__sort">
                  <option>Relevancia</option>
                  <option>Novedades</option>
                  <option>Precio: menor a mayor</option>
                  <option>Precio: mayor a menor</option>
                  <option>Más vendidos</option>
                </select>
              </div>
              <div class="products-toolbar__view" aria-label="Vista de productos">
                <button class="view-btn active" title="Grid 3 columnas">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor"><rect x="0" y="0" width="4" height="4"/><rect x="5" y="0" width="4" height="4"/><rect x="10" y="0" width="4" height="4"/><rect x="0" y="5" width="4" height="4"/><rect x="5" y="5" width="4" height="4"/><rect x="10" y="5" width="4" height="4"/><rect x="0" y="10" width="4" height="4"/><rect x="5" y="10" width="4" height="4"/><rect x="10" y="10" width="4" height="4"/></svg>
                </button>
                <button class="view-btn" title="Grid 2 columnas">
                  <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor"><rect x="0" y="0" width="6" height="6"/><rect x="8" y="0" width="6" height="6"/><rect x="0" y="8" width="6" height="6"/><rect x="8" y="8" width="6" height="6"/></svg>
                </button>
              </div>
            </div>

            <div class="product-grid product-grid--3col" role="list">

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--1">Imagen Editorial</div>
                  <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Heritage</p>
                  <h3 class="product-card__name">Blazer Estructurado Chichen</h3>
                  <div class="product-card__price-row"><span class="product-card__price">$6,800 MXN</span></div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

              <article class="product-card" role="listitem">
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

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--3">Imagen Editorial</div>
                  <span class="product-card__badge product-card__badge--rebaja">−30%</span>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
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
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Heritage</p>
                  <h3 class="product-card__name">Abrigo de Mezcla Cobá</h3>
                  <div class="product-card__price-row"><span class="product-card__price">$12,400 MXN</span></div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--5">Imagen Editorial</div>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Noir</p>
                  <h3 class="product-card__name">Polo Piqué Tulum</h3>
                  <div class="product-card__price-row"><span class="product-card__price">$1,650 MXN</span></div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--6">Imagen Editorial</div>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Origin</p>
                  <h3 class="product-card__name">Suéter Merino Xibalbá</h3>
                  <div class="product-card__price-row"><span class="product-card__price">$4,100 MXN</span></div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--1">Imagen Editorial</div>
                  <span class="product-card__badge product-card__badge--nuevo">Nuevo</span>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Heritage</p>
                  <h3 class="product-card__name">Traje de Lana Tikal I</h3>
                  <div class="product-card__price-row"><span class="product-card__price">$18,900 MXN</span></div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--3">Imagen Editorial</div>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Noir</p>
                  <h3 class="product-card__name">Chaqueta Denim Mayapán</h3>
                  <div class="product-card__price-row"><span class="product-card__price">$5,200 MXN</span></div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <div class="product-card__img-placeholder img-placeholder--5">Imagen Editorial</div>
                  <span class="product-card__badge product-card__badge--rebaja">−15%</span>
                  <button class="product-card__wishlist" aria-label="Wishlist">♡</button>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand">PAKAL Origin</p>
                  <h3 class="product-card__name">Camisa Oxford Kabah</h3>
                  <div class="product-card__price-row">
                    <span class="product-card__price product-card__price--sale">$1,615 MXN</span>
                    <span class="product-card__price--old">$1,900 MXN</span>
                  </div>
                  <a href="/proyecto/detalle.php" class="product-card__cta">Ver producto</a>
                </div>
              </article>

            </div>

            <nav class="pagination" aria-label="Paginación">
              <button class="pagination__btn pagination__btn--nav" aria-label="Página anterior">‹</button>
              <button class="pagination__btn active" aria-current="page">1</button>
              <button class="pagination__btn">2</button>
              <button class="pagination__btn">3</button>
              <button class="pagination__btn">4</button>
              <span style="padding:0 4px;font-size:0.8rem;color:var(--gray-light);">···</span>
              <button class="pagination__btn">12</button>
              <button class="pagination__btn pagination__btn--nav" aria-label="Página siguiente">›</button>
            </nav>

          </div>
        </div>
      </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
