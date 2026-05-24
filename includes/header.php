<?php
/**
 * Componente reutilizable: Header del sitio
 *
 * Variables esperadas (opcionales):
 *   $pageTitle   string  Título de la página para <title>
 *   $activeNav   string  Enlace activo: 'inicio'|'novedades'|'ropa'|'zapatos'|'accesorios'|'rebajas'
 *   $activeCat   string  Catálogo activo: 'hombre'|'mujer'|'infantil'
 */

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

$pageTitle = $pageTitle ?? 'PAKAL — Moda Premium';
$activeNav = $activeNav ?? '';
$activeCat = $activeCat ?? 'hombre';

$usuario   = usuarioActual();
$logueado  = estaLogueado();

// Contar ítems en el carrito y wishlist del usuario actual
$cart_count    = 0;
$wish_count    = 0;
if ($logueado) {
    try {
        $pdo_hdr = getPDO();
        $stmt = $pdo_hdr->prepare(
            'SELECT COALESCE(SUM(ci.cantidad), 0)
             FROM carrito_items ci
             JOIN carritos c ON ci.carrito_id = c.id
             WHERE c.usuario_id = ?'
        );
        $stmt->execute([$_SESSION['usuario_id']]);
        $cart_count = (int) $stmt->fetchColumn();

        $stmt_w = $pdo_hdr->prepare('SELECT COUNT(*) FROM wishlist WHERE usuario_id = ?');
        $stmt_w->execute([$_SESSION['usuario_id']]);
        $wish_count = (int) $stmt_w->fetchColumn();
    } catch (PDOException $e) {
        $cart_count = 0;
        $wish_count = 0;
    }
}

// Flash messages
$flashes   = obtenerFlash();

// Helper para clases activas
function navClass(string $item, string $current): string {
    return $item === $current ? ' class="active"' : '';
}
function catClass(string $cat, string $current): string {
    return $cat === $current ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="stylesheet" href="/proyecto/css/styles.css">
</head>
<body>

<?php if (!empty($flashes)): ?>
<!-- ── Flash Messages ──────────────────────────────────────── -->
<div class="flash-container" id="flashContainer" role="alert" aria-live="polite">
  <?php foreach ($flashes as $f): ?>
  <div class="flash flash--<?= htmlspecialchars($f['tipo']) ?>">
    <span><?= $f['mensaje'] /* HTML permitido intencional para links */ ?></span>
    <button class="flash__close" onclick="this.parentElement.remove()" aria-label="Cerrar">✕</button>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- PROMO BAR -->
<div class="promo-bar" id="promoBar">
  <p>Envío gratuito en pedidos superiores a $2,500 MXN &nbsp;·&nbsp; Nueva Colección Tikal disponible</p>
  <button class="promo-bar__close" onclick="document.getElementById('promoBar').style.display='none'" aria-label="Cerrar">✕</button>
</div>

<!-- HEADER -->
<header class="site-header">
  <div class="container">
    <div class="header__top">

      <!-- Catálogos -->
      <nav class="header__catalog-nav" aria-label="Catálogos">
        <a href="/proyecto/index.php?cat=hombre"<?= catClass('hombre', $activeCat) ?>>Hombre</a>
        <a href="/proyecto/index.php?cat=mujer"<?= catClass('mujer', $activeCat) ?>>Mujer</a>
        <a href="/proyecto/index.php?cat=infantil"<?= catClass('infantil', $activeCat) ?>>Infantil</a>
      </nav>

      <!-- Logo -->
      <a href="/proyecto/index.php" class="header__logo" aria-label="PAKAL — Inicio">
        PAKAL
        <span class="header__logo-sub">La Distinción del Tiempo</span>
      </a>

      <!-- Acciones según estado de sesión -->
      <div class="header__actions">
        <?php if ($logueado): ?>
          <!-- Usuario logueado -->
          <div class="header__user-menu">
            <span class="header__user-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
              <?= htmlspecialchars($usuario['nombre']) ?>
            </span>
            <?php if (esAdmin()): ?>
              <a href="/proyecto/admin.php" style="font-size:0.65rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gold);">Admin</a>
            <?php endif; ?>
            <a href="/proyecto/proceso/logout.php"
               style="font-size:0.65rem;color:var(--gray-mid);"
               onclick="return confirm('¿Cerrar sesión?')">
              Salir
            </a>
          </div>
        <?php else: ?>
          <!-- No logueado -->
          <a href="/proyecto/auth.php">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Ingresar
          </a>
        <?php endif; ?>

        <?php if ($logueado): ?>
        <a href="/proyecto/wishlist.php" title="Mis favoritos" style="position:relative;">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
          </svg>
          Favoritos
          <?php if ($wish_count > 0): ?>
          <span class="cart-count"><?= $wish_count ?></span>
          <?php endif; ?>
        </a>
        <?php endif; ?>

        <a href="/proyecto/carrito.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
          Carrito
          <span class="cart-count" id="cartCount"><?= $cart_count ?></span>
        </a>
      </div>

    </div>
  </div>

  <!-- NAV PRINCIPAL -->
  <nav class="main-nav" aria-label="Navegación principal">
    <div class="container">
      <div class="main-nav__inner">
        <a href="/proyecto/index.php"<?= navClass('inicio', $activeNav) ?>>Inicio</a>
        <a href="/proyecto/productos.php?orden=nuevo"<?= navClass('novedades', $activeNav) ?>>Novedades</a>
        <a href="/proyecto/productos.php?tipo=ropa"<?= navClass('ropa', $activeNav) ?>>Ropa</a>
        <a href="/proyecto/productos.php?tipo=zapatos"<?= navClass('zapatos', $activeNav) ?>>Zapatos</a>
        <a href="/proyecto/productos.php?tipo=accesorios"<?= navClass('accesorios', $activeNav) ?>>Accesorios</a>
        <a href="/proyecto/productos.php?rebaja=1" class="nav-rebajas<?= $activeNav === 'rebajas' ? ' active' : '' ?>">Rebajas</a>
      </div>
    </div>
  </nav>
</header>

<main>
