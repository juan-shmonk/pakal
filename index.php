<?php
require_once 'config/session.php';
require_once 'config/database.php';

// Catálogo activo según parámetro ?cat=
$activeCat = in_array($_GET['cat'] ?? '', ['mujer', 'infantil']) ? $_GET['cat'] : 'hombre';

$cat_config = [
    'hombre'   => [
        'label'    => 'Colección Hombre',
        'sec_tag'  => 'Para él',
        'sec_title'=> 'Destacados de <em>Temporada</em>',
        'col_tag'  => 'Esencia Masculina',
        'col_title'=> 'Lo mejor en <em>Hombre</em>',
    ],
    'mujer'    => [
        'label'    => 'Colección Mujer',
        'sec_tag'  => 'Curación editorial',
        'sec_title'=> 'Destacados de <em>Temporada</em>',
        'col_tag'  => 'Colección Mujer',
        'col_title'=> 'Elegancia <em>sin límites</em>',
    ],
    'infantil' => [
        'label'    => 'Colección Infantil',
        'sec_tag'  => 'Para los pequeños',
        'sec_title'=> 'Destacados de <em>Temporada</em>',
        'col_tag'  => 'Moda Joven',
        'col_title'=> 'Estilo con <em>identidad</em>',
    ],
];
$cfg = $cat_config[$activeCat];

try {
    $pdo_idx = getPDO();

    // Conteos por género para category strip
    $stmt_gen = $pdo_idx->query(
        'SELECT c.genero, COUNT(p.id) AS total
         FROM productos p
         JOIN categorias c ON p.categoria_id = c.id
         WHERE p.estado = "activo"
         GROUP BY c.genero'
    );
    $conteos_genero = [];
    foreach ($stmt_gen->fetchAll() as $row) {
        $conteos_genero[$row['genero']] = (int)$row['total'];
    }

    // Destacados filtrados por género seleccionado
    $stmt_dest = $pdo_idx->prepare(
        'SELECT p.id, p.nombre, p.marca, p.precio, p.precio_rebaja, p.badge
         FROM productos p
         JOIN categorias c ON p.categoria_id = c.id
         WHERE p.destacado = 1 AND p.estado = "activo" AND c.genero = ?
         ORDER BY p.created_at DESC LIMIT 6'
    );
    $stmt_dest->execute([$activeCat]);
    $destacados = $stmt_dest->fetchAll();

    // Si no hay destacados en ese género, mostrar todos
    if (empty($destacados)) {
        $destacados = $pdo_idx->query(
            'SELECT p.id, p.nombre, p.marca, p.precio, p.precio_rebaja, p.badge
             FROM productos p
             WHERE p.destacado = 1 AND p.estado = "activo"
             ORDER BY p.created_at DESC LIMIT 6'
        )->fetchAll();
    }
    $imgs_destacados = imagenesPorIds($pdo_idx, array_column($destacados, 'id'));

    // Colección del género seleccionado
    $stmt_col = $pdo_idx->prepare(
        'SELECT p.id, p.nombre, p.marca, p.precio, p.precio_rebaja, p.badge
         FROM productos p
         JOIN categorias c ON p.categoria_id = c.id
         WHERE c.genero = ? AND p.estado = "activo"
         ORDER BY p.destacado DESC, p.created_at DESC LIMIT 4'
    );
    $stmt_col->execute([$activeCat]);
    $coleccion_cat = $stmt_col->fetchAll();
    $imgs_coleccion = imagenesPorIds($pdo_idx, array_column($coleccion_cat, 'id'));

} catch (PDOException $e) {
    $destacados     = $imgs_destacados  = [];
    $coleccion_cat  = $imgs_coleccion   = [];
    $conteos_genero = [];
}

$csrf         = generarCSRF();
$wishlist_ids = estaLogueado()
    ? wishlistUsuario(getPDO(), (int)$_SESSION['usuario_id'])
    : [];

$pageTitle = $cfg['label'] . ' — PAKAL';
$activeNav = 'inicio';
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
    <?php
    $cat_data = [
        'hombre'   => ['label' => 'Colección Hombre',   'sub' => 'HOMBRE',   'clase' => 'media-1'],
        'mujer'    => ['label' => 'Colección Mujer',    'sub' => 'MUJER',    'clase' => 'media-2'],
        'infantil' => ['label' => 'Colección Infantil', 'sub' => 'INFANTIL', 'clase' => 'media-3'],
    ];
    ?>
    <section class="category-strip" aria-label="Catálogos por género">
      <div class="category-strip__inner">

        <!-- Hombre -->
        <article class="category-strip__item">
          <div class="category-strip__media media-1" aria-label="Colección Hombre">
            <svg class="cat-svg" viewBox="0 0 400 480" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="20" y="20" width="360" height="440" stroke="rgba(255,255,255,0.10)" stroke-width="1"/>
              <rect x="40" y="40" width="320" height="400" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
              <polygon points="200,110 330,240 200,370 70,240" stroke="rgba(255,255,255,0.16)" stroke-width="1" fill="none"/>
              <polygon points="200,150 290,240 200,330 110,240" stroke="rgba(255,255,255,0.10)" stroke-width="1" fill="none"/>
              <polygon points="200,185 265,240 200,295 135,240" stroke="rgba(255,255,255,0.06)" stroke-width="1" fill="none"/>
              <line x1="200" y1="110" x2="200" y2="370" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
              <line x1="70"  y1="240" x2="330" y2="240" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
              <line x1="70"  y1="110" x2="330" y2="370" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
              <line x1="330" y1="110" x2="70"  y2="370" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
              <circle cx="200" cy="240" r="5" fill="rgba(255,255,255,0.22)"/>
              <circle cx="200" cy="240" r="18" stroke="rgba(255,255,255,0.08)" stroke-width="1" fill="none"/>
              <rect x="68"  y="108" width="10" height="10" stroke="rgba(255,255,255,0.18)" stroke-width="1" fill="none"/>
              <rect x="322" y="108" width="10" height="10" stroke="rgba(255,255,255,0.18)" stroke-width="1" fill="none"/>
              <rect x="68"  y="362" width="10" height="10" stroke="rgba(255,255,255,0.18)" stroke-width="1" fill="none"/>
              <rect x="322" y="362" width="10" height="10" stroke="rgba(255,255,255,0.18)" stroke-width="1" fill="none"/>
              <text x="200" y="76" font-family="Georgia,serif" font-size="8" letter-spacing="6" fill="rgba(255,255,255,0.28)" text-anchor="middle">PAKAL</text>
              <line x1="70"  y1="72" x2="166" y2="72" stroke="rgba(255,255,255,0.10)" stroke-width="0.8"/>
              <line x1="234" y1="72" x2="330" y2="72" stroke="rgba(255,255,255,0.10)" stroke-width="0.8"/>
              <text x="200" y="415" font-family="Georgia,serif" font-size="8" letter-spacing="5" fill="rgba(255,255,255,0.18)" text-anchor="middle">HOMBRE</text>
            </svg>
          </div>
          <div class="category-strip__body">
            <h2 class="category-strip__name">Colección Hombre</h2>
            <p class="category-strip__count"><?= $conteos_genero['hombre'] ?? 0 ?> prenda<?= ($conteos_genero['hombre'] ?? 0) !== 1 ? 's' : '' ?></p>
            <a href="/proyecto/productos.php?genero=hombre" class="btn btn--sm">Explorar</a>
          </div>
        </article>

        <!-- Mujer -->
        <article class="category-strip__item">
          <div class="category-strip__media media-2" aria-label="Colección Mujer">
            <svg class="cat-svg" viewBox="0 0 400 480" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="20" y="20" width="360" height="440" stroke="rgba(255,255,255,0.10)" stroke-width="1"/>
              <circle cx="200" cy="240" r="120" stroke="rgba(255,255,255,0.14)" stroke-width="1" fill="none"/>
              <circle cx="200" cy="240" r="80"  stroke="rgba(255,255,255,0.09)" stroke-width="1" fill="none"/>
              <circle cx="200" cy="240" r="44"  stroke="rgba(255,255,255,0.06)" stroke-width="1" fill="none"/>
              <polygon points="200,120 306,186 306,294 200,360 94,294 94,186" stroke="rgba(255,255,255,0.12)" stroke-width="1" fill="none"/>
              <polygon points="200,160 268,200 268,280 200,320 132,280 132,200" stroke="rgba(255,255,255,0.07)" stroke-width="1" fill="none"/>
              <line x1="200" y1="120" x2="200" y2="360" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
              <line x1="80"  y1="240" x2="320" y2="240" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
              <line x1="200" y1="196" x2="200" y2="284" stroke="rgba(255,255,255,0.09)" stroke-width="1"/>
              <line x1="156" y1="240" x2="244" y2="240" stroke="rgba(255,255,255,0.09)" stroke-width="1"/>
              <circle cx="200" cy="240" r="5" fill="rgba(255,255,255,0.25)"/>
              <circle cx="200" cy="120" r="3" fill="rgba(255,255,255,0.15)"/>
              <circle cx="200" cy="360" r="3" fill="rgba(255,255,255,0.15)"/>
              <circle cx="80"  cy="240" r="3" fill="rgba(255,255,255,0.15)"/>
              <circle cx="320" cy="240" r="3" fill="rgba(255,255,255,0.15)"/>
              <text x="200" y="76" font-family="Georgia,serif" font-size="8" letter-spacing="6" fill="rgba(255,255,255,0.28)" text-anchor="middle">PAKAL</text>
              <line x1="70"  y1="72" x2="166" y2="72" stroke="rgba(255,255,255,0.10)" stroke-width="0.8"/>
              <line x1="234" y1="72" x2="330" y2="72" stroke="rgba(255,255,255,0.10)" stroke-width="0.8"/>
              <text x="200" y="415" font-family="Georgia,serif" font-size="8" letter-spacing="5" fill="rgba(255,255,255,0.18)" text-anchor="middle">MUJER</text>
            </svg>
          </div>
          <div class="category-strip__body">
            <h2 class="category-strip__name">Colección Mujer</h2>
            <p class="category-strip__count"><?= $conteos_genero['mujer'] ?? 0 ?> prenda<?= ($conteos_genero['mujer'] ?? 0) !== 1 ? 's' : '' ?></p>
            <a href="/proyecto/productos.php?genero=mujer" class="btn btn--sm">Explorar</a>
          </div>
        </article>

        <!-- Infantil -->
        <article class="category-strip__item">
          <div class="category-strip__media media-3" aria-label="Colección Infantil">
            <svg class="cat-svg" viewBox="0 0 400 480" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
              <rect x="20" y="20" width="360" height="440" stroke="rgba(255,255,255,0.10)" stroke-width="1"/>
              <rect x="56" y="56" width="288" height="368" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
              <line x1="56"  y1="56"  x2="344" y2="424" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
              <line x1="344" y1="56"  x2="56"  y2="424" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
              <line x1="56"  y1="240" x2="344" y2="240" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
              <line x1="200" y1="56"  x2="200" y2="424" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
              <polygon points="200,150 270,240 200,330 130,240" stroke="rgba(255,255,255,0.16)" stroke-width="1" fill="none"/>
              <polygon points="200,175 245,240 200,305 155,240" stroke="rgba(255,255,255,0.10)" stroke-width="1" fill="none"/>
              <polygon points="200,200 226,240 200,280 174,240" stroke="rgba(255,255,255,0.07)" stroke-width="1" fill="none"/>
              <circle cx="200" cy="240" r="6"  fill="rgba(255,255,255,0.24)"/>
              <circle cx="130" cy="240" r="3"  fill="rgba(255,255,255,0.14)"/>
              <circle cx="270" cy="240" r="3"  fill="rgba(255,255,255,0.14)"/>
              <circle cx="200" cy="150" r="3"  fill="rgba(255,255,255,0.14)"/>
              <circle cx="200" cy="330" r="3"  fill="rgba(255,255,255,0.14)"/>
              <rect x="56"  y="56"  width="8" height="8" fill="rgba(255,255,255,0.14)"/>
              <rect x="336" y="56"  width="8" height="8" fill="rgba(255,255,255,0.14)"/>
              <rect x="56"  y="416" width="8" height="8" fill="rgba(255,255,255,0.14)"/>
              <rect x="336" y="416" width="8" height="8" fill="rgba(255,255,255,0.14)"/>
              <text x="200" y="76" font-family="Georgia,serif" font-size="8" letter-spacing="6" fill="rgba(255,255,255,0.28)" text-anchor="middle">PAKAL</text>
              <line x1="70"  y1="72" x2="166" y2="72" stroke="rgba(255,255,255,0.10)" stroke-width="0.8"/>
              <line x1="234" y1="72" x2="330" y2="72" stroke="rgba(255,255,255,0.10)" stroke-width="0.8"/>
              <text x="200" y="415" font-family="Georgia,serif" font-size="8" letter-spacing="5" fill="rgba(255,255,255,0.18)" text-anchor="middle">INFANTIL</text>
            </svg>
          </div>
          <div class="category-strip__body">
            <h2 class="category-strip__name">Colección Infantil</h2>
            <p class="category-strip__count"><?= $conteos_genero['infantil'] ?? 0 ?> prenda<?= ($conteos_genero['infantil'] ?? 0) !== 1 ? 's' : '' ?></p>
            <a href="/proyecto/productos.php?genero=infantil" class="btn btn--sm">Explorar</a>
          </div>
        </article>

      </div>
    </section>

    <!-- PRODUCTOS DESTACADOS -->
    <section class="products-section container" id="destacados" aria-label="Productos destacados">
      <div class="section-header">
        <div class="section-header__left">
          <span class="section-label"><?= $cfg['sec_tag'] ?></span>
          <h2 class="section-title"><?= $cfg['sec_title'] ?></h2>
        </div>
        <a href="/proyecto/productos.php?genero=<?= $activeCat ?>" class="section-header__right">Ver todo</a>
      </div>

      <div class="product-grid" role="list">
        <?php
        if (empty($destacados)): ?>
          <p style="grid-column:1/-1;text-align:center;color:var(--gray-mid);padding:32px 0;">
            Próximamente productos disponibles.
          </p>
        <?php else:
          foreach ($destacados as $i => $d):
            $precio_d = $d['precio_rebaja'] ?? $d['precio'];
            $rebaja_d = $d['precio_rebaja'] !== null;
            $img_d    = $imgs_destacados[$d['id']] ?? null;
        ?>
        <?php $en_wish_d = in_array($d['id'], $wishlist_ids); ?>
        <article class="product-card" role="listitem">
          <div class="product-card__media">
            <?php if ($img_d): ?>
              <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($img_d) ?>"
                   alt="<?= htmlspecialchars($d['nombre']) ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
              <div class="product-card__img-placeholder img-placeholder--<?= ($i % 6) + 1 ?>">Imagen Editorial</div>
            <?php endif; ?>
            <?php if ($d['badge']): ?>
            <span class="product-card__badge product-card__badge--<?= htmlspecialchars($d['badge']) ?>">
              <?php if ($d['badge'] === 'rebaja' && $rebaja_d): ?>
                −<?= round((1 - $d['precio_rebaja'] / $d['precio']) * 100) ?>%
              <?php else: ?>
                <?= ucfirst($d['badge']) ?>
              <?php endif; ?>
            </span>
            <?php endif; ?>
            <?php if ($logueado ?? false): ?>
            <form method="POST" action="/proyecto/proceso/wishlist.php" class="wishlist-form">
              <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="producto_id" value="<?= $d['id'] ?>">
              <input type="hidden" name="redirect"    value="/proyecto/index.php">
              <button type="submit" class="btn-wishlist <?= $en_wish_d ? 'btn-wishlist--active' : '' ?>"
                      title="<?= $en_wish_d ? 'Quitar de favoritos' : 'Guardar en favoritos' ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $en_wish_d ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
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
            <p class="product-card__brand"><?= htmlspecialchars($d['marca']) ?></p>
            <h3 class="product-card__name"><?= htmlspecialchars($d['nombre']) ?></h3>
            <div class="product-card__price-row">
              <?php if ($rebaja_d): ?>
                <span class="product-card__price product-card__price--sale">$<?= number_format($d['precio_rebaja'], 0, '.', ',') ?> MXN</span>
                <span class="product-card__price--old">$<?= number_format($d['precio'], 0, '.', ',') ?> MXN</span>
              <?php else: ?>
                <span class="product-card__price">$<?= number_format($d['precio'], 0, '.', ',') ?> MXN</span>
              <?php endif; ?>
            </div>
            <a href="/proyecto/detalle.php?id=<?= $d['id'] ?>" class="product-card__cta">Ver producto</a>
          </div>
        </article>
        <?php endforeach; endif; ?>
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

    <!-- COLECCIÓN POR GÉNERO -->
    <?php if (!empty($coleccion_cat)): ?>
    <section class="products-section container" aria-label="Colección <?= ucfirst($activeCat) ?>">
      <div class="section-header">
        <div class="section-header__left">
          <span class="section-label"><?= $cfg['col_tag'] ?></span>
          <h2 class="section-title"><?= $cfg['col_title'] ?></h2>
        </div>
        <a href="/proyecto/productos.php?genero=<?= $activeCat ?>" class="section-header__right">Ver colección</a>
      </div>
      <div class="product-grid">
        <?php foreach ($coleccion_cat as $i => $mj):
          $precio_mj  = $mj['precio_rebaja'] ?? $mj['precio'];
          $rebaja_mj  = $mj['precio_rebaja'] !== null;
          $img_mj     = $imgs_coleccion[$mj['id']] ?? null;
          $en_wish_mj = in_array($mj['id'], $wishlist_ids);
          $redirect_w = '/proyecto/index.php' . ($activeCat !== 'hombre' ? '?cat=' . $activeCat : '');
        ?>
        <article class="product-card">
          <div class="product-card__media">
            <?php if ($img_mj): ?>
              <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($img_mj) ?>"
                   alt="<?= htmlspecialchars($mj['nombre']) ?>"
                   style="width:100%;height:100%;object-fit:cover;display:block;">
            <?php else: ?>
              <div class="product-card__img-placeholder img-placeholder--<?= ($i % 6) + 1 ?>">Imagen Editorial</div>
            <?php endif; ?>
            <?php if ($mj['badge']): ?>
            <span class="product-card__badge product-card__badge--<?= htmlspecialchars($mj['badge']) ?>">
              <?php if ($mj['badge'] === 'rebaja' && $rebaja_mj): ?>
                −<?= round((1 - $mj['precio_rebaja'] / $mj['precio']) * 100) ?>%
              <?php else: ?>
                <?= ucfirst($mj['badge']) ?>
              <?php endif; ?>
            </span>
            <?php endif; ?>
            <?php if ($logueado ?? false): ?>
            <form method="POST" action="/proyecto/proceso/wishlist.php" class="wishlist-form">
              <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="producto_id" value="<?= $mj['id'] ?>">
              <input type="hidden" name="redirect"    value="<?= htmlspecialchars($redirect_w) ?>">
              <button type="submit" class="btn-wishlist <?= $en_wish_mj ? 'btn-wishlist--active' : '' ?>"
                      title="<?= $en_wish_mj ? 'Quitar de favoritos' : 'Guardar en favoritos' ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $en_wish_mj ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
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
            <p class="product-card__brand"><?= htmlspecialchars($mj['marca']) ?></p>
            <h3 class="product-card__name"><?= htmlspecialchars($mj['nombre']) ?></h3>
            <div class="product-card__price-row">
              <?php if ($rebaja_mj): ?>
                <span class="product-card__price product-card__price--sale">$<?= number_format($mj['precio_rebaja'], 0, '.', ',') ?> MXN</span>
                <span class="product-card__price--old">$<?= number_format($mj['precio'], 0, '.', ',') ?> MXN</span>
              <?php else: ?>
                <span class="product-card__price">$<?= number_format($precio_mj, 0, '.', ',') ?> MXN</span>
              <?php endif; ?>
            </div>
            <a href="/proyecto/detalle.php?id=<?= $mj['id'] ?>" class="product-card__cta">Ver producto</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

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

<script>
  if (new URLSearchParams(window.location.search).has('cat')) {
    document.getElementById('destacados')?.scrollIntoView({ behavior: 'smooth' });
  }
</script>

<?php require_once 'includes/footer.php'; ?>
