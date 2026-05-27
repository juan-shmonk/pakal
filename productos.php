<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: productos.php                                     ║
// ║  PROPÓSITO: Catálogo completo de productos con filtros       ║
// ║                                                              ║
// ║  Esta página muestra todos los productos disponibles y       ║
// ║  permite filtrarlos por múltiples criterios:                 ║
// ║                                                              ║
// ║  FILTROS (llegan como parámetros en la URL, ej: ?genero=mujer):
// ║  - genero     → hombre / mujer / infantil                   ║
// ║  - categoria_id → ID de una categoría específica            ║
// ║  - tipo       → ropa / zapatos / accesorios                  ║
// ║  - rebaja     → 1 (solo productos en oferta)                 ║
// ║  - orden      → nuevo / precio_asc / precio_desc             ║
// ║  - q          → texto libre de búsqueda                      ║
// ║                                                              ║
// ║  Los filtros se combinan dinámicamente en una consulta SQL.  ║
// ╚══════════════════════════════════════════════════════════════╝

require_once 'config/session.php';
require_once 'config/database.php';

$pdo = getPDO();

// ── Leer los filtros de la URL ────────────────────────────────────
// $_GET contiene los parámetros de la URL (lo que viene después del "?").
// Usamos "??" para dar un valor por defecto si el parámetro no viene.
// in_array verifica que el valor sea uno de los permitidos (seguridad: evita valores arbitrarios).

// Género seleccionado (null = todos los géneros).
$genero_filtro    = in_array($_GET['genero'] ?? '', ['hombre','mujer','infantil','unisex'])
                    ? $_GET['genero'] : null;
// Categoría específica (0 = todas las categorías).
$categoria_filtro = (int)($_GET['categoria_id'] ?? 0) ?: null;
// Si viene ?rebaja=1, solo mostramos productos con precio de rebaja.
$rebaja_filtro    = isset($_GET['rebaja']) && $_GET['rebaja'] === '1';
// Orden de presentación (por defecto: más nuevos primero).
$orden_filtro     = in_array($_GET['orden'] ?? '', ['precio_asc','precio_desc','nuevo'])
                    ? $_GET['orden'] : 'nuevo';
// Texto de búsqueda libre escrito por el usuario.
$busqueda         = trim($_GET['q'] ?? '');
// Tipo de producto (ropa, zapatos, accesorios) para los links del menú principal.
$tipo_filtro      = in_array($_GET['tipo'] ?? '', ['ropa','zapatos','accesorios'])
                    ? $_GET['tipo'] : null;

// ── Construir la consulta SQL dinámicamente con los filtros ────────
// Empezamos con la condición base: solo productos activos.
// Vamos añadiendo condiciones según qué filtros el usuario activó.
// Esto es más seguro que concatenar strings directamente en SQL.
$where  = ['p.estado = "activo"'];
$params = [];  // Los valores "?" que se pasarán de forma segura a la consulta

// Añadimos condición de género si el usuario filtró por él.
if ($genero_filtro) {
    $where[]  = 'c.genero = ?';
    $params[] = $genero_filtro;
}

// Añadimos condición de categoría específica si se seleccionó una.
if ($categoria_filtro) {
    $where[]  = 'p.categoria_id = ?';
    $params[] = $categoria_filtro;
}

// El tipo de producto se determina por el "slug" de la categoría en la BD.
// Los zapatos tienen slugs que empiezan con "zapatos", los accesorios tienen slug exacto.
// La ropa es todo lo demás (no zapatos ni accesorios).
if ($tipo_filtro === 'zapatos') {
    $where[] = "c.slug LIKE 'zapatos%'";
} elseif ($tipo_filtro === 'accesorios') {
    $where[] = "c.slug = 'accesorios'";
} elseif ($tipo_filtro === 'ropa') {
    $where[] = "c.slug NOT LIKE 'zapatos%' AND c.slug != 'accesorios'";
}

// Mostramos solo productos que tienen precio de rebaja (descuento).
if ($rebaja_filtro) {
    $where[] = 'p.precio_rebaja IS NOT NULL';  // IS NOT NULL = tiene precio de rebaja
}

// Búsqueda de texto: buscamos en nombre, marca y colección del producto.
// LIKE '% texto %' encuentra el texto en cualquier parte del campo.
// Añadimos el mismo $like tres veces porque se usa en tres columnas.
if ($busqueda !== '') {
    $where[]  = '(p.nombre LIKE ? OR p.marca LIKE ? OR p.coleccion LIKE ?)';
    $like     = '%' . $busqueda . '%';
    $params   = array_merge($params, [$like, $like, $like]);
}

// ── Determinar el orden de presentación ───────────────────────────
// match() es como un switch pero más moderno y conciso.
// COALESCE(precio_rebaja, precio): si hay precio de rebaja lo usa, si no el normal.
// Esto permite ordenar por el precio REAL que paga el cliente.
$order = match($orden_filtro) {
    'precio_asc'  => 'COALESCE(p.precio_rebaja, p.precio) ASC',   // Más barato primero
    'precio_desc' => 'COALESCE(p.precio_rebaja, p.precio) DESC',   // Más caro primero
    default       => 'p.destacado DESC, p.created_at DESC',         // Destacados y más nuevos primero
};

$sql = 'SELECT p.id, p.nombre, p.marca, p.precio, p.precio_rebaja, p.badge, p.stock, p.estado,
               c.nombre AS categoria_nombre
        FROM productos p
        JOIN categorias c ON p.categoria_id = c.id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY ' . $order;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

$imgs_productos = imagenesPorIds($pdo, array_column($productos, 'id'));
$csrf           = generarCSRF();
$wishlist_ids   = estaLogueado()
    ? wishlistUsuario($pdo, (int)$_SESSION['usuario_id'])
    : [];

// Categorías para sidebar
$categorias_sidebar = $pdo->query(
    'SELECT id, nombre, genero FROM categorias WHERE activo = 1 ORDER BY genero, nombre'
)->fetchAll();

$titulo = 'Todos los Productos';
if ($tipo_filtro === 'zapatos')    $titulo = 'Zapatos';
if ($tipo_filtro === 'accesorios') $titulo = 'Accesorios';
if ($tipo_filtro === 'ropa')       $titulo = 'Ropa';
if ($genero_filtro)                $titulo = 'Colección ' . ucfirst($genero_filtro);
if ($rebaja_filtro)                $titulo = 'Productos en Rebaja';
if ($busqueda !== '')              $titulo = 'Búsqueda: "' . htmlspecialchars($busqueda) . '"';

$pageTitle = $titulo . ' — PAKAL';
$activeNav = $rebaja_filtro ? 'rebajas' : ($tipo_filtro ?? 'ropa');
$activeCat = $genero_filtro ?? 'hombre';
require_once 'includes/header.php';
?>

    <!-- PAGE HERO -->
    <div class="page-hero container">
      <nav class="page-hero__breadcrumb" aria-label="Ruta de navegación">
        <a href="/proyecto/index.php">Inicio</a>
        <span>›</span>
        <span style="color:var(--dark);"><?= htmlspecialchars($titulo) ?></span>
      </nav>
      <h1 class="page-hero__title"><?= htmlspecialchars($titulo) ?></h1>
      <p class="page-hero__count"><?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?> encontrado<?= count($productos) !== 1 ? 's' : '' ?></p>
    </div>

    <!-- PRODUCTS LAYOUT -->
    <div class="products-page">
      <div class="container">
        <div class="products-layout">

          <!-- Sidebar de filtros -->
          <aside class="filters" aria-label="Filtros de productos">
            <div class="filters__header">
              <span class="filters__title">Filtros</span>
              <a href="/proyecto/productos.php" class="filters__clear">Limpiar todo</a>
            </div>

            <!-- Buscador rápido -->
            <form method="GET" action="/proyecto/productos.php" style="margin-bottom:20px;">
              <div style="position:relative;">
                <input type="text" name="q" class="form-input"
                       placeholder="Buscar producto..."
                       value="<?= htmlspecialchars($busqueda) ?>"
                       style="padding-right:40px;">
                <button type="submit" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-mid);">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>
                </button>
              </div>
            </form>

            <!-- Género -->
            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Género</button>
              <div class="filter-options">
                <a href="/proyecto/productos.php" style="display:block;padding:4px 0;font-size:0.82rem;color:<?= !$genero_filtro ? 'var(--dark)' : 'var(--gray-mid)' ?>;">
                  Todos
                </a>
                <?php foreach (['hombre','mujer','infantil'] as $g): ?>
                <a href="/proyecto/productos.php?genero=<?= $g ?>"
                   style="display:block;padding:4px 0;font-size:0.82rem;color:<?= $genero_filtro === $g ? 'var(--dark)' : 'var(--gray-mid)' ?>;font-weight:<?= $genero_filtro === $g ? '600' : '400' ?>;">
                  <?= ucfirst($g) ?>
                </a>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Categorías -->
            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Categoría</button>
              <div class="filter-options">
                <?php
                $genero_actual = $genero_filtro ?? 'hombre';
                $cats_filtradas = array_filter($categorias_sidebar,
                    fn($c) => $c['genero'] === $genero_actual || $c['genero'] === 'unisex'
                );
                foreach ($cats_filtradas as $cat):
                ?>
                <a href="/proyecto/productos.php?categoria_id=<?= $cat['id'] ?><?= $genero_filtro ? '&genero='.$genero_filtro : '' ?>"
                   style="display:block;padding:4px 0;font-size:0.82rem;color:<?= $categoria_filtro === $cat['id'] ? 'var(--dark)' : 'var(--gray-mid)' ?>;font-weight:<?= $categoria_filtro === $cat['id'] ? '600' : '400' ?>;">
                  <?= htmlspecialchars($cat['nombre']) ?>
                </a>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Rebajas -->
            <div class="filter-group">
              <button class="filter-group__toggle open" aria-expanded="true">Ofertas</button>
              <div class="filter-options">
                <a href="/proyecto/productos.php?rebaja=1"
                   style="display:block;padding:4px 0;font-size:0.82rem;color:<?= $rebaja_filtro ? 'var(--dark)' : 'var(--gray-mid)' ?>;font-weight:<?= $rebaja_filtro ? '600' : '400' ?>;">
                  Solo en rebaja
                </a>
              </div>
            </div>

          </aside>

          <!-- Área de productos -->
          <div>

            <div class="products-toolbar">
              <div class="products-toolbar__sort">
                <label for="sortSelect">Ordenar por</label>
                <select id="sortSelect" class="products-toolbar__sort"
                        onchange="window.location='/proyecto/productos.php?orden='+this.value+'<?= $genero_filtro ? '&genero='.$genero_filtro : '' ?><?= $categoria_filtro ? '&categoria_id='.$categoria_filtro : '' ?>'">
                  <option value="nuevo"       <?= $orden_filtro === 'nuevo'       ? 'selected' : '' ?>>Novedades</option>
                  <option value="precio_asc"  <?= $orden_filtro === 'precio_asc'  ? 'selected' : '' ?>>Precio: menor a mayor</option>
                  <option value="precio_desc" <?= $orden_filtro === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
                </select>
              </div>
            </div>

            <?php if (empty($productos)): ?>
            <div style="padding:64px 0;text-align:center;">
              <p style="font-size:0.95rem;color:var(--gray-mid);margin-bottom:16px;">
                No se encontraron productos con los filtros seleccionados.
              </p>
              <a href="/proyecto/productos.php" class="btn">Ver todos los productos</a>
            </div>
            <?php else: ?>

            <div class="product-grid product-grid--3col" role="list">
              <?php
              foreach ($productos as $i => $prod):
                $precio_mostrar = $prod['precio_rebaja'] ?? $prod['precio'];
                $tiene_rebaja   = $prod['precio_rebaja'] !== null;
                $agotado        = $prod['estado'] === 'agotado' || $prod['stock'] <= 0;
                $img_p          = $imgs_productos[$prod['id']] ?? null;
                $en_wish_p      = in_array($prod['id'], $wishlist_ids);
              ?>
              <article class="product-card" role="listitem">
                <div class="product-card__media">
                  <?php if ($img_p): ?>
                    <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($img_p) ?>"
                         alt="<?= htmlspecialchars($prod['nombre']) ?>"
                         style="width:100%;height:100%;object-fit:cover;display:block;">
                  <?php else: ?>
                    <div class="product-card__img-placeholder img-placeholder--<?= ($i % 6) + 1 ?>">Imagen Editorial</div>
                  <?php endif; ?>
                  <?php if ($prod['badge']): ?>
                  <span class="product-card__badge product-card__badge--<?= htmlspecialchars($prod['badge']) ?>">
                    <?php if ($prod['badge'] === 'rebaja' && $tiene_rebaja): ?>
                      −<?= round((1 - $prod['precio_rebaja'] / $prod['precio']) * 100) ?>%
                    <?php else: ?>
                      <?= ucfirst($prod['badge']) ?>
                    <?php endif; ?>
                  </span>
                  <?php endif; ?>
                  <?php if ($agotado): ?>
                  <span class="product-card__badge" style="background:#888;top:auto;bottom:12px;">Agotado</span>
                  <?php endif; ?>
                  <?php if (estaLogueado()): ?>
                  <form method="POST" action="/proyecto/proceso/wishlist.php" class="wishlist-form">
                    <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>">
                    <input type="hidden" name="redirect"    value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                    <button type="submit" class="btn-wishlist <?= $en_wish_p ? 'btn-wishlist--active' : '' ?>"
                            title="<?= $en_wish_p ? 'Quitar de favoritos' : 'Guardar en favoritos' ?>">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $en_wish_p ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                      </svg>
                    </button>
                  </form>
                  <?php else: ?>
                  <a href="/proyecto/auth.php" class="wishlist-form" title="Inicia sesión para guardar favoritos">
                    <span class="btn-wishlist">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                      </svg>
                    </span>
                  </a>
                  <?php endif; ?>
                </div>
                <div class="product-card__body">
                  <p class="product-card__brand"><?= htmlspecialchars($prod['marca']) ?></p>
                  <h3 class="product-card__name"><?= htmlspecialchars($prod['nombre']) ?></h3>
                  <div class="product-card__price-row">
                    <?php if ($tiene_rebaja): ?>
                      <span class="product-card__price product-card__price--sale">$<?= number_format($prod['precio_rebaja'], 0, '.', ',') ?> MXN</span>
                      <span class="product-card__price--old">$<?= number_format($prod['precio'], 0, '.', ',') ?> MXN</span>
                    <?php else: ?>
                      <span class="product-card__price">$<?= number_format($prod['precio'], 0, '.', ',') ?> MXN</span>
                    <?php endif; ?>
                  </div>
                  <a href="/proyecto/detalle.php?id=<?= $prod['id'] ?>" class="product-card__cta">Ver producto</a>
                </div>
              </article>
              <?php endforeach; ?>
            </div>

            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
