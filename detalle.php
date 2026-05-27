<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: detalle.php                                       ║
// ║  PROPÓSITO: Página de detalle de un producto específico      ║
// ║                                                              ║
// ║  Muestra toda la información de un producto:                 ║
// ║  - Galería de imágenes con miniaturas clicables              ║
// ║  - Nombre, marca, precio (con rebaja si aplica)              ║
// ║  - Descripción, material, colección, stock disponible        ║
// ║  - Botón "Agregar al carrito"                                ║
// ║  - Botón de favoritos (♡)                                    ║
// ║  - Productos relacionados de la misma categoría              ║
// ║                                                              ║
// ║  El ID del producto viene en la URL: ?id=5                   ║
// ║  Si el ID no es válido o el producto no existe → catálogo    ║
// ╚══════════════════════════════════════════════════════════════╝

require_once 'config/session.php';
require_once 'config/database.php';

// Leemos el ID del producto desde la URL (?id=5).
// (int) convierte el valor a entero; si no es número, da 0.
$producto_id = (int) ($_GET['id'] ?? 0);

// Si no llegó un ID válido, redirigimos al catálogo.
if ($producto_id <= 0) {
    header('Location: productos.php');
    exit;
}

$pdo = getPDO();

// ── Obtener los datos completos del producto ──────────────────────
// Usamos JOIN para obtener también el nombre de la categoría y género
// (están en la tabla "categorias", relacionada por categoria_id).
// No mostramos borradores (solo activos y agotados son visibles).
$stmt = $pdo->prepare(
    'SELECT p.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug, c.genero
     FROM productos p
     JOIN categorias c ON p.categoria_id = c.id
     WHERE p.id = ? AND p.estado != "borrador"
     LIMIT 1'
);
$stmt->execute([$producto_id]);
$p = $stmt->fetch();  // $p contiene todos los datos del producto (o false si no existe)

// Si el producto no existe o es borrador, mostramos error y vamos al catálogo.
if (!$p) {
    flash('error', 'Producto no encontrado.');
    header('Location: productos.php');
    exit;
}

// ── Obtener productos relacionados ────────────────────────────────
// Buscamos otros productos de la MISMA categoría para mostrarlos
// en la sección "Completa el look" al final de la página.
// El ORDER BY pone los destacados primero y luego los más nuevos.
$stmt = $pdo->prepare(
    'SELECT id, nombre, marca, precio, precio_rebaja, badge
     FROM productos
     WHERE categoria_id = ? AND id != ? AND estado = "activo"
     ORDER BY destacado DESC, created_at DESC
     LIMIT 4'
);
$stmt->execute([$p['categoria_id'], $producto_id]);
$relacionados = $stmt->fetchAll();
$imgs_relacionados = imagenesPorIds($pdo, array_column($relacionados, 'id'));

// ── Calcular valores para mostrar ────────────────────────────────
// Si hay precio de rebaja, se muestra ese; si no, el precio normal.
$precio_display   = $p['precio_rebaja'] ?? $p['precio'];
// true si tiene descuento activo (para mostrar el precio original tachado).
$tiene_rebaja     = $p['precio_rebaja'] !== null;
// Un producto está agotado si su estado es 'agotado' O si su stock llegó a 0.
$agotado          = $p['estado'] === 'agotado' || $p['stock'] <= 0;

// ── Obtener las imágenes del producto ────────────────────────────
// Traemos hasta 4 imágenes en orden (la primera es la principal, las demás son miniaturas).
// PDO::FETCH_COLUMN devuelve un array plano solo con las rutas: ['img1.jpg', 'img2.png', ...]
$stmt_img = $pdo->prepare(
    'SELECT ruta FROM producto_imagenes WHERE producto_id = ? ORDER BY orden ASC LIMIT 4'
);
$stmt_img->execute([$producto_id]);
$imagenes_detalle = $stmt_img->fetchAll(PDO::FETCH_COLUMN);

// Generamos el token CSRF para los formularios de esta página (carrito y wishlist).
$csrf = generarCSRF();

// ── Verificar si este producto está en los favoritos del usuario ──
if (estaLogueado()) {
    $sw = $pdo->prepare('SELECT 1 FROM wishlist WHERE usuario_id = ? AND producto_id = ?');
    $sw->execute([$_SESSION['usuario_id'], $producto_id]);
    $en_wishlist = (bool) $sw->fetch();  // true = sí está, false = no está
} else {
    $en_wishlist = false;  // Visitantes no tienen favoritos
}

$pageTitle = htmlspecialchars($p['nombre']) . ' — PAKAL';
$activeNav = 'ropa';
$activeCat = $p['genero'] === 'mujer' ? 'mujer' : ($p['genero'] === 'infantil' ? 'infantil' : 'hombre');
require_once 'includes/header.php';
?>

    <!-- Breadcrumb -->
    <div class="container" style="padding-top:28px;padding-bottom:0;">
      <nav class="page-hero__breadcrumb" aria-label="Ruta de navegación">
        <a href="/proyecto/index.php">Inicio</a>
        <span>›</span>
        <a href="/proyecto/productos.php?genero=<?= htmlspecialchars($p['genero']) ?>">
          <?= ucfirst($p['genero']) ?>
        </a>
        <span>›</span>
        <a href="/proyecto/productos.php?categoria_id=<?= $p['categoria_id'] ?>">
          <?= htmlspecialchars($p['categoria_nombre']) ?>
        </a>
        <span>›</span>
        <span style="color:var(--dark);"><?= htmlspecialchars($p['nombre']) ?></span>
      </nav>
    </div>

    <!-- PRODUCT DETAIL -->
    <section class="product-detail container" aria-label="Detalle del producto">
      <div class="product-detail__inner">

        <!-- Galería -->
        <div class="product-gallery" aria-label="Galería de imágenes del producto">
          <div class="product-gallery__main" role="img" aria-label="Imagen principal" id="gallery-main">
            <?php if (!empty($imagenes_detalle)): ?>
              <img id="gallery-main-img"
                   src="/proyecto/assets/img/productos/<?= htmlspecialchars($imagenes_detalle[0]) ?>"
                   alt="<?= htmlspecialchars($p['nombre']) ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
              <div class="product-gallery__main-placeholder">
                <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(155deg,#E5E1DC 0%,#D0CBC4 100%);gap:12px;">
                  <span style="font-size:0.72rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">
                    <?= htmlspecialchars($p['coleccion'] ?? 'PAKAL') ?>
                  </span>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <div class="product-gallery__thumbs" role="list" aria-label="Miniaturas">
            <?php foreach ($imagenes_detalle as $ti => $truta): ?>
            <div class="product-gallery__thumb <?= $ti === 0 ? 'active' : '' ?>" role="listitem"
                 onclick="document.getElementById('gallery-main-img').src=this.querySelector('img').src;
                          document.querySelectorAll('.product-gallery__thumb').forEach(t=>t.classList.remove('active'));
                          this.classList.add('active');"
                 style="cursor:pointer;">
              <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($truta) ?>"
                   alt="Miniatura <?= $ti + 1 ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block;">
            </div>
            <?php endforeach; ?>
            <?php if (empty($imagenes_detalle)): ?>
              <?php for ($i = 1; $i <= 4; $i++): ?>
              <div class="product-gallery__thumb <?= $i === 1 ? 'active' : '' ?>" role="listitem">
                <div class="product-gallery__thumb-img thumb-img-<?= $i ?>"></div>
              </div>
              <?php endfor; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Información del producto -->
        <div class="product-info">

          <p class="product-info__brand"><?= htmlspecialchars($p['marca']) ?></p>
          <h1 class="product-info__name"><?= htmlspecialchars($p['nombre']) ?></h1>

          <?php if ($tiene_rebaja): ?>
            <p class="product-info__price" style="color:var(--black);">
              $<?= number_format($p['precio_rebaja'], 0, '.', ',') ?> MXN
              <span style="font-size:0.85rem;text-decoration:line-through;color:var(--gray-light);margin-left:8px;">
                $<?= number_format($p['precio'], 0, '.', ',') ?> MXN
              </span>
            </p>
          <?php else: ?>
            <p class="product-info__price">$<?= number_format($p['precio'], 0, '.', ',') ?> MXN</p>
          <?php endif; ?>

          <p class="product-info__price-note">IVA incluido · Envío gratuito</p>

          <?php if ($agotado): ?>
            <div style="margin:16px 0;padding:12px 16px;background:#FFF8E1;border:1px solid #FFE082;">
              <p style="font-size:0.78rem;color:#E65100;font-weight:500;">Este producto está temporalmente agotado.</p>
            </div>
          <?php endif; ?>

          <div class="product-info__divider"></div>

          <?php if ($p['descripcion']): ?>
          <p class="product-info__description"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
          <div class="product-info__divider"></div>
          <?php endif; ?>

          <!-- Acciones: carrito + favoritos -->
          <div class="product-info__actions" style="display:flex;gap:10px;align-items:stretch;margin-top:4px;">
            <?php if (!$agotado): ?>
            <form method="POST" action="/proyecto/proceso/carrito.php" style="flex:1;display:flex;">
              <input type="hidden" name="action"      value="agregar">
              <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="cantidad"    value="1">
              <button type="submit" class="btn-add-cart" style="flex:1;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                  <line x1="3" y1="6" x2="21" y2="6"/>
                  <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                Agregar al carrito
              </button>
            </form>
            <?php else: ?>
            <button class="btn-add-cart" disabled style="flex:1;opacity:0.5;cursor:not-allowed;">Sin stock disponible</button>
            <?php endif; ?>

            <!-- Botón favoritos -->
            <?php if (estaLogueado()): ?>
            <form method="POST" action="/proyecto/proceso/wishlist.php">
              <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
              <input type="hidden" name="redirect"    value="/proyecto/detalle.php?id=<?= $p['id'] ?>">
              <button type="submit" class="btn-wishlist-detail <?= $en_wishlist ? 'btn-wishlist-detail--active' : '' ?>"
                      title="<?= $en_wishlist ? 'Quitar de favoritos' : 'Guardar en favoritos' ?>">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="<?= $en_wishlist ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <?= $en_wishlist ? 'Guardado' : 'Favoritos' ?>
              </button>
            </form>
            <?php else: ?>
            <a href="/proyecto/auth.php" class="btn-wishlist-detail" title="Inicia sesión para guardar favoritos">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
              Favoritos
            </a>
            <?php endif; ?>
          </div>

          <div class="product-info__divider"></div>

          <!-- Metadata -->
          <div class="product-info__meta">
            <?php if ($p['coleccion']): ?>
            <div class="meta-row">
              <span class="meta-row__key">Colección</span>
              <span class="meta-row__val"><?= htmlspecialchars($p['coleccion']) ?></span>
            </div>
            <?php endif; ?>
            <?php if ($p['material']): ?>
            <div class="meta-row">
              <span class="meta-row__key">Composición</span>
              <span class="meta-row__val"><?= htmlspecialchars($p['material']) ?></span>
            </div>
            <?php endif; ?>
            <div class="meta-row">
              <span class="meta-row__key">Categoría</span>
              <span class="meta-row__val"><?= htmlspecialchars($p['categoria_nombre']) ?></span>
            </div>
            <div class="meta-row">
              <span class="meta-row__key">Disponibilidad</span>
              <span class="meta-row__val"><?= $agotado ? 'Agotado' : $p['stock'] . ' unidades' ?></span>
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
          </div>

        </div>
      </div>
    </section>

    <?php if (!empty($relacionados)): ?>
    <!-- PRODUCTOS RELACIONADOS -->
    <section class="related-products" aria-label="Productos relacionados">
      <div class="container">
        <div class="section-header">
          <div class="section-header__left">
            <span class="section-label">Completa el look</span>
            <h2 class="section-title">Prendas <em>relacionadas</em></h2>
          </div>
          <a href="/proyecto/productos.php?categoria_id=<?= $p['categoria_id'] ?>" class="section-header__right">Ver más</a>
        </div>
        <div class="product-grid">
          <?php
          foreach ($relacionados as $i => $r):
            $precio_r = $r['precio_rebaja'] ?? $r['precio'];
            $rebaja_r = $r['precio_rebaja'] !== null;
            $img_r    = $imgs_relacionados[$r['id']] ?? null;
          ?>
          <article class="product-card">
            <div class="product-card__media">
              <?php if ($img_r): ?>
                <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($img_r) ?>"
                     alt="<?= htmlspecialchars($r['nombre']) ?>"
                     style="width:100%;height:100%;object-fit:cover;display:block;">
              <?php else: ?>
                <div class="product-card__img-placeholder img-placeholder--<?= ($i % 4) + 2 ?>">Imagen Editorial</div>
              <?php endif; ?>
              <?php if ($r['badge']): ?>
              <span class="product-card__badge product-card__badge--<?= htmlspecialchars($r['badge']) ?>">
                <?= $r['badge'] === 'rebaja' ? '−' . round((1 - $r['precio_rebaja']/$r['precio']) * 100) . '%' : ucfirst($r['badge']) ?>
              </span>
              <?php endif; ?>
            </div>
            <div class="product-card__body">
              <p class="product-card__brand"><?= htmlspecialchars($r['marca']) ?></p>
              <h3 class="product-card__name"><?= htmlspecialchars($r['nombre']) ?></h3>
              <div class="product-card__price-row">
                <?php if ($rebaja_r): ?>
                  <span class="product-card__price product-card__price--sale">$<?= number_format($r['precio_rebaja'], 0, '.', ',') ?> MXN</span>
                  <span class="product-card__price--old">$<?= number_format($r['precio'], 0, '.', ',') ?> MXN</span>
                <?php else: ?>
                  <span class="product-card__price">$<?= number_format($r['precio'], 0, '.', ',') ?> MXN</span>
                <?php endif; ?>
              </div>
              <a href="/proyecto/detalle.php?id=<?= $r['id'] ?>" class="product-card__cta">Ver producto</a>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
    <?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
