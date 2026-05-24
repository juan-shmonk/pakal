<?php
// ── Mis Favoritos ────────────────────────────────────────────
require_once 'config/session.php';
require_once 'config/database.php';

if (!estaLogueado()) {
    flash('info', 'Inicia sesión para ver tus favoritos.');
    header('Location: auth.php');
    exit;
}

$pdo        = getPDO();
$usuario_id = (int)$_SESSION['usuario_id'];
$csrf       = generarCSRF();

$stmt = $pdo->prepare(
    'SELECT p.id, p.nombre, p.marca, p.precio, p.precio_rebaja, p.badge, p.stock, p.estado
     FROM wishlist w
     JOIN productos p ON w.producto_id = p.id
     WHERE w.usuario_id = ?
     ORDER BY w.created_at DESC'
);
$stmt->execute([$usuario_id]);
$favoritos = $stmt->fetchAll();

$imgs_favoritos = imagenesPorIds($pdo, array_column($favoritos, 'id'));
$wishlist_ids   = array_column($favoritos, 'id');

$pageTitle = 'Mis Favoritos — PAKAL';
$activeNav = '';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <div class="page-hero container">
      <nav class="page-hero__breadcrumb" aria-label="Ruta de navegación">
        <a href="/proyecto/index.php">Inicio</a>
        <span>›</span>
        <span style="color:var(--dark);">Mis Favoritos</span>
      </nav>
      <h1 class="page-hero__title">Mis <em>Favoritos</em></h1>
      <p class="page-hero__count"><?= count($favoritos) ?> producto<?= count($favoritos) !== 1 ? 's' : '' ?> guardado<?= count($favoritos) !== 1 ? 's' : '' ?></p>
    </div>

    <div class="container" style="padding-bottom:80px;">

      <?php if (empty($favoritos)): ?>
      <div style="text-align:center;padding:80px 0;">
        <div style="width:56px;height:56px;margin:0 auto 24px;color:var(--gray-light);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
        </div>
        <p style="font-size:1rem;color:var(--gray-mid);margin-bottom:24px;">
          Aún no tienes productos guardados.<br>
          Explora el catálogo y guarda los que más te gusten.
        </p>
        <a href="/proyecto/productos.php" class="btn btn--solid btn--lg">Explorar catálogo</a>
      </div>
      <?php else: ?>

      <div class="product-grid product-grid--3col" role="list">
        <?php foreach ($favoritos as $i => $prod):
          $precio_mostrar = $prod['precio_rebaja'] ?? $prod['precio'];
          $tiene_rebaja   = $prod['precio_rebaja'] !== null;
          $agotado        = $prod['estado'] === 'agotado' || $prod['stock'] <= 0;
          $img_p          = $imgs_favoritos[$prod['id']] ?? null;
        ?>
        <article class="product-card" role="listitem">
          <div class="product-card__media" style="position:relative;">
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
            <!-- Botón quitar de favoritos -->
            <form method="POST" action="/proyecto/proceso/wishlist.php" class="wishlist-form">
              <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="producto_id" value="<?= $prod['id'] ?>">
              <input type="hidden" name="redirect"    value="/proyecto/wishlist.php">
              <button type="submit" class="btn-wishlist btn-wishlist--active" title="Quitar de favoritos">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5">
                  <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
              </button>
            </form>
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

<?php require_once 'includes/footer.php'; ?>
