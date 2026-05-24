<?php
// ── Panel de Administración ──────────────────────────────────
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

if (!estaLogueado()) {
    flash('warning', 'Debes iniciar sesión para acceder al panel.');
    header('Location: auth.php?tab=login');
    exit;
}
if (!esAdmin()) {
    flash('error', 'No tienes permisos para acceder a esta sección.');
    header('Location: index.php');
    exit;
}

$pdo     = getPDO();
$usuario = usuarioActual();
$flashes = obtenerFlash();
$csrf    = generarCSRF();

// ── Datos del Dashboard ──────────────────────────────────────
$stat_ventas    = (float) $pdo->query('SELECT COALESCE(SUM(total),0) FROM pedidos WHERE estado != "cancelado"')->fetchColumn();
$stat_pedidos   = (int)   $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
$stat_clientes  = (int)   $pdo->query('SELECT COUNT(*) FROM usuarios WHERE rol = "cliente"')->fetchColumn();
$stat_productos = (int)   $pdo->query('SELECT COUNT(*) FROM productos WHERE estado = "activo"')->fetchColumn();

$pedidos_recientes = $pdo->query(
    'SELECT p.id, p.nombre, p.total, p.estado, p.created_at,
            COUNT(pi.id) AS num_items
     FROM pedidos p
     LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
     GROUP BY p.id
     ORDER BY p.created_at DESC
     LIMIT 8'
)->fetchAll();

// ── Datos del Catálogo ────────────────────────────────────────
$productos_admin = $pdo->query(
    'SELECT p.id, p.nombre, p.marca, p.precio, p.precio_rebaja, p.stock, p.estado,
            c.nombre AS categoria
     FROM productos p
     JOIN categorias c ON p.categoria_id = c.id
     ORDER BY p.created_at DESC'
)->fetchAll();

$imgs_admin = imagenesPorIds($pdo, array_column($productos_admin, 'id'));

// ── Datos de Categorías ──────────────────────────────────────
$categorias_admin = $pdo->query(
    'SELECT c.id, c.nombre, c.slug, c.genero, c.activo,
            COUNT(p.id) AS num_productos
     FROM categorias c
     LEFT JOIN productos p ON p.categoria_id = c.id AND p.estado = "activo"
     GROUP BY c.id
     ORDER BY c.genero, c.nombre'
)->fetchAll();

// ── Datos de Clientes ─────────────────────────────────────────
$clientes_admin = $pdo->query(
    'SELECT u.id, u.nombre, u.apellido, u.email, u.telefono,
            u.newsletter, u.activo, u.created_at,
            COUNT(p.id) AS num_pedidos,
            COALESCE(SUM(p.total),0) AS total_gastado
     FROM usuarios u
     LEFT JOIN pedidos p ON p.usuario_id = u.id
     WHERE u.rol = "cliente"
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

$count_newsletter = (int) $pdo->query(
    'SELECT COUNT(*) FROM usuarios WHERE newsletter = 1'
)->fetchColumn();

// ── Datos de Pedidos ─────────────────────────────────────────
$pedidos_todos = $pdo->query(
    'SELECT p.id, p.nombre, p.email, p.telefono, p.direccion, p.total, p.estado, p.created_at,
            COUNT(pi.id) AS num_items
     FROM pedidos p
     LEFT JOIN pedido_items pi ON p.id = pi.pedido_id
     GROUP BY p.id
     ORDER BY p.created_at DESC'
)->fetchAll();

$count_activos = count(array_filter($pedidos_todos,
    fn($p) => !in_array($p['estado'], ['entregado','cancelado'])
));

// Detalle de pedido individual
$detalle_id     = (int)($_GET['pedido_id'] ?? 0);
$detalle_pedido = null;
$detalle_items  = [];
if ($detalle_id > 0) {
    $stmt_dp = $pdo->prepare('SELECT * FROM pedidos WHERE id = ?');
    $stmt_dp->execute([$detalle_id]);
    $detalle_pedido = $stmt_dp->fetch() ?: null;
    if ($detalle_pedido) {
        $stmt_di = $pdo->prepare('SELECT * FROM pedido_items WHERE pedido_id = ? ORDER BY id ASC');
        $stmt_di->execute([$detalle_id]);
        $detalle_items = $stmt_di->fetchAll();
    }
}

// Datos para el formulario de edición
$edit_id       = (int)($_GET['id'] ?? 0);
$edit_producto = null;
$edit_imagen   = null;
if ($edit_id > 0) {
    $stmt_e = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $stmt_e->execute([$edit_id]);
    $edit_producto = $stmt_e->fetch() ?: null;
    if ($edit_producto) {
        $stmt_ei = $pdo->prepare('SELECT ruta FROM producto_imagenes WHERE producto_id = ? AND orden = 0 LIMIT 1');
        $stmt_ei->execute([$edit_id]);
        $edit_imagen = $stmt_ei->fetchColumn() ?: null;
    }
}

$categorias_form = $pdo->query(
    'SELECT id, nombre, genero FROM categorias WHERE activo = 1 ORDER BY genero, nombre'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Admin — PAKAL</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    body { background: #F5F4F2; }
    /* Visibilidad de módulos — aquí para garantizar que aplique aunque el CSS externo falle */
    .tab-content        { display: none !important; }
    .tab-content.active { display: block !important; }
    .admin-tab-btn {
      padding: 9px 20px;
      border: 1px solid var(--border);
      background: var(--white);
      font-family: var(--font-body);
      font-size: 0.68rem;
      font-weight: 500;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--gray-mid);
      cursor: pointer;
      transition: all 0.28s ease;
      border-left: none;
    }
    .admin-tab-btn:first-child { border-left: 1px solid var(--border); }
    .admin-tab-btn.active { background: var(--black); color: var(--white); border-color: var(--black); }
    .mini-chart {
      height: 48px;
      display: flex;
      align-items: flex-end;
      gap: 3px;
      margin-top: 12px;
    }
    .mini-chart-bar {
      flex: 1;
      background: var(--border);
      border-radius: 1px;
      min-height: 4px;
      transition: background 0.28s ease;
    }
    .mini-chart-bar--gold { background: var(--gold); }
  </style>
</head>
<body>

  <div class="admin-layout">

    <!-- ── SIDEBAR ──────────────────────────────────────────── -->
    <aside class="admin-sidebar" aria-label="Menú de administración">
      <div class="admin-sidebar__logo">
        <p class="admin-sidebar__logo-name">PAKAL</p>
        <p class="admin-sidebar__logo-sub">Panel de Administración</p>
      </div>

      <nav class="admin-sidebar__nav">

        <div class="admin-nav-section">
          <p class="admin-nav-label">Principal</p>
          <a href="#" class="admin-nav-link active" onclick="showSection('dashboard');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            </span>
            Dashboard
          </a>
        </div>

        <div class="admin-nav-section">
          <p class="admin-nav-label">Catálogo</p>
          <a href="#" class="admin-nav-link" onclick="showSection('productos');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
            </span>
            Productos
            <span class="nav-badge"><?= $stat_productos ?></span>
          </a>
          <a href="#" class="admin-nav-link" onclick="showSection('nuevo-producto');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            </span>
            Agregar Producto
          </a>
          <a href="#" class="admin-nav-link" onclick="showSection('categorias');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            </span>
            Categorías
          </a>
        </div>

        <div class="admin-nav-section">
          <p class="admin-nav-label">Ventas</p>
          <a href="#" class="admin-nav-link" onclick="showSection('pedidos');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </span>
            Pedidos
            <?php if ($count_activos > 0): ?>
            <span class="nav-badge"><?= $count_activos ?></span>
            <?php endif; ?>
          </a>
          <a href="#" class="admin-nav-link" onclick="showSection('clientes');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            </span>
            Clientes
            <?php if (!empty($clientes_admin)): ?>
            <span class="nav-badge"><?= count($clientes_admin) ?></span>
            <?php endif; ?>
          </a>
        </div>

        <div class="admin-nav-section">
          <p class="admin-nav-label">Marketing</p>
          <a href="#" class="admin-nav-link" onclick="showSection('newsletter');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="22 8 12 15 2 8"/><rect x="2" y="6" width="20" height="14" rx="2"/></svg>
            </span>
            Newsletter
            <?php if ($count_newsletter > 0): ?>
            <span class="nav-badge"><?= $count_newsletter ?></span>
            <?php endif; ?>
          </a>
          <a href="#" class="admin-nav-link" onclick="showSection('descuentos');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
            </span>
            Descuentos
          </a>
        </div>

        <div class="admin-nav-section">
          <p class="admin-nav-label">Sistema</p>
          <a href="#" class="admin-nav-link" onclick="showSection('configuracion');return false;">
            <span class="nav-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </span>
            Configuración
          </a>
        </div>

      </nav>

      <!-- Sidebar footer user -->
      <div class="admin-sidebar__footer">
        <div class="admin-sidebar__user">
          <div class="admin-sidebar__avatar">
            <?= strtoupper(substr($usuario['nombre'], 0, 1) . substr($usuario['apellido'], 0, 1)) ?>
          </div>
          <div class="admin-sidebar__user-info">
            <p class="admin-sidebar__user-name"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></p>
            <p class="admin-sidebar__user-role">Administrador</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- ── Flash Messages ──────────────────────────────────────── -->
    <?php if (!empty($flashes)): ?>
    <div class="flash-container" id="flashContainer" role="alert" aria-live="polite"
         style="position:fixed;top:20px;right:20px;z-index:9999;max-width:380px;">
      <?php foreach ($flashes as $f): ?>
      <div class="flash flash--<?= htmlspecialchars($f['tipo']) ?>" style="margin-bottom:8px;">
        <span class="flash__icon"><?= $f['tipo'] === 'success' ? '✓' : '!' ?></span>
        <span class="flash__text"><?= htmlspecialchars($f['mensaje']) ?></span>
        <button class="flash__close" onclick="this.closest('.flash').remove()">✕</button>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── MAIN CONTENT ─────────────────────────────────────── -->
    <main class="admin-main" aria-label="Contenido principal del panel">

      <!-- Topbar -->
      <div class="admin-topbar">
        <div class="admin-topbar__left">
          <h1 class="admin-topbar__page" id="pageTitle">Dashboard</h1>
          <p class="admin-topbar__breadcrumb" id="pageBreadcrumb">PAKAL Admin › Dashboard</p>
        </div>
        <div class="admin-topbar__right">
          <a href="index.php" class="btn btn--sm" target="_blank" style="gap:6px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Ver tienda
          </a>
          <span class="btn btn--sm" style="pointer-events:none;opacity:0.7;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
            <?= date('d M Y') ?>
          </span>
          <a href="proceso/logout.php"
             class="btn btn--sm"
             style="border-color:#C44A4A;color:#C44A4A;"
             onclick="return confirm('¿Cerrar sesión del panel?')">
            Salir
          </a>
        </div>
      </div>

      <!-- ── DASHBOARD SECTION ─────────────────────────────── -->
      <div class="admin-content tab-content active" id="section-dashboard">

        <!-- Stats grid -->
        <div class="admin-stats">

          <div class="stat-card">
            <p class="stat-card__label">Ventas Totales</p>
            <p class="stat-card__value">$<?= number_format($stat_ventas, 0, '.', ',') ?></p>
            <p class="stat-card__change stat-card__change--up">Total acumulado</p>
            <div class="mini-chart">
              <div class="mini-chart-bar" style="height:40%;"></div>
              <div class="mini-chart-bar" style="height:60%;"></div>
              <div class="mini-chart-bar" style="height:45%;"></div>
              <div class="mini-chart-bar" style="height:75%;"></div>
              <div class="mini-chart-bar" style="height:55%;"></div>
              <div class="mini-chart-bar" style="height:80%;"></div>
              <div class="mini-chart-bar mini-chart-bar--gold" style="height:95%;"></div>
            </div>
          </div>

          <div class="stat-card">
            <p class="stat-card__label">Total Pedidos</p>
            <p class="stat-card__value"><?= $stat_pedidos ?></p>
            <p class="stat-card__change stat-card__change--up">Pedidos registrados</p>
            <div class="mini-chart">
              <div class="mini-chart-bar" style="height:50%;"></div>
              <div class="mini-chart-bar" style="height:70%;"></div>
              <div class="mini-chart-bar" style="height:60%;"></div>
              <div class="mini-chart-bar" style="height:85%;"></div>
              <div class="mini-chart-bar" style="height:65%;"></div>
              <div class="mini-chart-bar" style="height:90%;"></div>
              <div class="mini-chart-bar mini-chart-bar--gold" style="height:80%;"></div>
            </div>
          </div>

          <div class="stat-card">
            <p class="stat-card__label">Clientes Registrados</p>
            <p class="stat-card__value"><?= $stat_clientes ?></p>
            <p class="stat-card__change stat-card__change--up">Cuentas activas</p>
            <div class="mini-chart">
              <div class="mini-chart-bar" style="height:55%;"></div>
              <div class="mini-chart-bar" style="height:65%;"></div>
              <div class="mini-chart-bar" style="height:70%;"></div>
              <div class="mini-chart-bar" style="height:60%;"></div>
              <div class="mini-chart-bar" style="height:80%;"></div>
              <div class="mini-chart-bar" style="height:75%;"></div>
              <div class="mini-chart-bar mini-chart-bar--gold" style="height:85%;"></div>
            </div>
          </div>

          <div class="stat-card">
            <p class="stat-card__label">Productos Activos</p>
            <p class="stat-card__value"><?= $stat_productos ?></p>
            <p class="stat-card__change stat-card__change--up">En el catálogo</p>
            <div class="mini-chart">
              <div class="mini-chart-bar" style="height:90%;"></div>
              <div class="mini-chart-bar" style="height:92%;"></div>
              <div class="mini-chart-bar" style="height:88%;"></div>
              <div class="mini-chart-bar" style="height:95%;"></div>
              <div class="mini-chart-bar" style="height:90%;"></div>
              <div class="mini-chart-bar" style="height:93%;"></div>
              <div class="mini-chart-bar mini-chart-bar--gold" style="height:85%;"></div>
            </div>
          </div>

        </div>

        <!-- Recent orders + top products -->
        <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;">

          <!-- Recent orders table -->
          <div class="admin-table-card">
            <div class="admin-table-card__header">
              <h2 class="admin-table-card__title">Pedidos Recientes</h2>
              <div class="admin-table-card__actions">
                <a href="#" class="btn btn--sm">Ver todos</a>
              </div>
            </div>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Pedido</th>
                  <th>Cliente</th>
                  <th>Artículos</th>
                  <th>Total</th>
                  <th>Estado</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pedidos_recientes)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--gray-light);padding:24px;">Sin pedidos registrados aún.</td></tr>
                <?php else: foreach ($pedidos_recientes as $ped):
                  $badge_color = match($ped['estado']) {
                      'entregado'  => 'table-badge--active',
                      'enviado'    => 'table-badge--active',
                      'cancelado'  => 'table-badge--inactivo',
                      default      => 'table-badge--agotado',
                  };
                ?>
                <tr>
                  <td style="font-weight:600;color:var(--dark);">#<?= str_pad($ped['id'], 4, '0', STR_PAD_LEFT) ?></td>
                  <td><?= htmlspecialchars($ped['nombre']) ?></td>
                  <td><?= $ped['num_items'] ?> artículo<?= $ped['num_items'] != 1 ? 's' : '' ?></td>
                  <td style="font-weight:500;">$<?= number_format($ped['total'], 0, '.', ',') ?></td>
                  <td><span class="table-badge <?= $badge_color ?>"><?= ucfirst($ped['estado']) ?></span></td>
                  <td style="color:var(--gray-light);"><?= date('d M', strtotime($ped['created_at'])) ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Top products -->
          <div class="admin-table-card">
            <div class="admin-table-card__header">
              <h2 class="admin-table-card__title">Más Vendidos</h2>
            </div>
            <div style="padding:8px 0;">
              <div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:50px;background:linear-gradient(145deg,#E5E1DC,#D0CBC4);flex-shrink:0;"></div>
                <div style="flex:1;">
                  <p style="font-size:0.82rem;font-weight:500;margin-bottom:2px;">Blazer Chichen</p>
                  <p style="font-size:0.68rem;color:var(--gray-light);">PAKAL Heritage · $6,800</p>
                </div>
                <p style="font-size:0.85rem;font-weight:600;">24</p>
              </div>
              <div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:50px;background:linear-gradient(145deg,#E0DDD8,#CAC7C0);flex-shrink:0;"></div>
                <div style="flex:1;">
                  <p style="font-size:0.82rem;font-weight:500;margin-bottom:2px;">Pantalón Uxmal</p>
                  <p style="font-size:0.68rem;color:var(--gray-light);">PAKAL Noir · $3,200</p>
                </div>
                <p style="font-size:0.85rem;font-weight:600;">18</p>
              </div>
              <div style="padding:16px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:50px;background:linear-gradient(145deg,#DDE0DD,#C8CCC8);flex-shrink:0;"></div>
                <div style="flex:1;">
                  <p style="font-size:0.82rem;font-weight:500;margin-bottom:2px;">Camisa Palenque</p>
                  <p style="font-size:0.68rem;color:var(--gray-light);">PAKAL Origin · $2,700</p>
                </div>
                <p style="font-size:0.85rem;font-weight:600;">15</p>
              </div>
              <div style="padding:16px 24px;display:flex;align-items:center;gap:14px;">
                <div style="width:38px;height:50px;background:linear-gradient(145deg,#E2DDE0,#CCCAD0);flex-shrink:0;"></div>
                <div style="flex:1;">
                  <p style="font-size:0.82rem;font-weight:500;margin-bottom:2px;">Abrigo Cobá</p>
                  <p style="font-size:0.68rem;color:var(--gray-light);">PAKAL Heritage · $12,400</p>
                </div>
                <p style="font-size:0.85rem;font-weight:600;">11</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ── PRODUCTS TABLE SECTION ──────────────────────────  -->
      <div class="admin-content tab-content" id="section-productos">
        <div class="admin-table-card">
          <div class="admin-table-card__header">
            <h2 class="admin-table-card__title">Gestión de Productos</h2>
            <div class="admin-table-card__actions">
              <div class="admin-search">
                <span class="admin-search__icon">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input type="text" placeholder="Buscar producto..." aria-label="Buscar producto">
              </div>
              <select class="btn btn--sm" style="padding:9px 14px;cursor:pointer;" aria-label="Filtrar por categoría">
                <option>Todas las categorías</option>
                <option>Blazers</option>
                <option>Pantalones</option>
                <option>Camisas</option>
                <option>Abrigos</option>
              </select>
              <button class="btn btn--sm btn--solid" onclick="showSection('nuevo-producto')">
                + Agregar producto
              </button>
            </div>
          </div>

          <table class="admin-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($productos_admin)): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--gray-light);padding:24px;">Sin productos. <a href="#" onclick="showSection('nuevo-producto');return false;" style="color:var(--dark);text-decoration:underline;">Agrega el primero</a>.</td></tr>
              <?php else: foreach ($productos_admin as $prod_a):
                $badge_prod = match($prod_a['estado']) {
                    'activo'   => 'table-badge--active',
                    'agotado'  => 'table-badge--agotado',
                    default    => 'table-badge--inactivo',
                };
                $stock_color = $prod_a['stock'] <= 3 ? 'color:#E65100;font-weight:500;' : '';
              ?>
              <tr>
                <td>
                  <div class="product-row-info">
                    <?php $img_a = $imgs_admin[$prod_a['id']] ?? null; ?>
                    <?php if ($img_a): ?>
                      <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($img_a) ?>"
                           alt="<?= htmlspecialchars($prod_a['nombre']) ?>"
                           style="width:52px;height:52px;object-fit:cover;border-radius:4px;flex-shrink:0;">
                    <?php else: ?>
                      <div class="product-row-img-placeholder"></div>
                    <?php endif; ?>
                    <div>
                      <p class="product-row-name"><?= htmlspecialchars($prod_a['nombre']) ?></p>
                      <p class="product-row-brand"><?= htmlspecialchars($prod_a['marca']) ?></p>
                    </div>
                  </div>
                </td>
                <td style="color:var(--gray-mid);"><?= htmlspecialchars($prod_a['categoria']) ?></td>
                <td style="font-weight:500;">
                  <?php if ($prod_a['precio_rebaja']): ?>
                    <span style="text-decoration:line-through;color:var(--gray-light);font-size:0.75rem;">$<?= number_format($prod_a['precio'], 0, '.', ',') ?></span>
                    <span style="color:#C44A4A;"> $<?= number_format($prod_a['precio_rebaja'], 0, '.', ',') ?></span>
                  <?php else: ?>
                    $<?= number_format($prod_a['precio'], 0, '.', ',') ?>
                  <?php endif; ?>
                </td>
                <td style="<?= $stock_color ?>"><?= $prod_a['stock'] ?> unidades</td>
                <td><span class="table-badge <?= $badge_prod ?>"><?= ucfirst($prod_a['estado']) ?></span></td>
                <td>
                  <div class="table-actions">
                    <a href="/proyecto/detalle.php?id=<?= $prod_a['id'] ?>" target="_blank"
                       class="table-action-btn" title="Ver en tienda" aria-label="Ver producto">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <a href="/proyecto/admin.php?seccion=editar-producto&id=<?= $prod_a['id'] ?>"
                       class="table-action-btn" title="Editar producto" aria-label="Editar producto">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <form method="POST" action="proceso/admin_productos.php" style="display:inline;"
                          onsubmit="return confirm('¿Eliminar «<?= htmlspecialchars(addslashes($prod_a['nombre'])) ?>»? Esta acción no se puede deshacer.');">
                      <input type="hidden" name="action"      value="eliminar">
                      <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
                      <input type="hidden" name="producto_id" value="<?= $prod_a['id'] ?>">
                      <button type="submit" class="table-action-btn table-action-btn--danger" title="Eliminar" aria-label="Eliminar producto">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>

          <div style="padding:16px 24px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <p style="font-size:0.72rem;color:var(--gray-light);">
              <?= count($productos_admin) ?> producto<?= count($productos_admin) !== 1 ? 's' : '' ?> en total
            </p>
            <button class="btn btn--sm btn--solid" onclick="showSection('nuevo-producto')">+ Agregar producto</button>
          </div>
        </div>
      </div>

      <!-- ── NEW PRODUCT FORM ─────────────────────────────────  -->
      <div class="admin-content tab-content" id="section-nuevo-producto">
        <div class="admin-form-card">
          <div class="admin-form-card__header">
            <h2 class="admin-form-card__title">Agregar Nuevo Producto</h2>
            <div style="display:flex;gap:8px;">
              <div class="admin-tab-btn active" onclick="document.querySelectorAll('.admin-tab-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');">Información</div>
              <div class="admin-tab-btn" onclick="document.querySelectorAll('.admin-tab-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');">Imágenes</div>
              <div class="admin-tab-btn" onclick="document.querySelectorAll('.admin-tab-btn').forEach(b=>b.classList.remove('active'));this.classList.add('active');">Stock</div>
            </div>
          </div>

          <div class="admin-form-card__body">
            <form method="POST" action="proceso/admin_productos.php" enctype="multipart/form-data">

              <input type="hidden" name="action"     value="guardar">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

              <!-- Información básica -->
              <div class="admin-form-grid" style="margin-bottom:32px;">

                <div class="admin-form-group form-full">
                  <label class="admin-form-label" for="prod-nombre">Nombre del producto <span>*</span></label>
                  <input type="text" id="prod-nombre" name="nombre" class="admin-form-input"
                         placeholder="Ej. Blazer Estructurado Chichen" required>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-marca">Línea / Marca <span>*</span></label>
                  <select id="prod-marca" name="marca" class="admin-form-select" required>
                    <option value="">Seleccionar línea</option>
                    <option value="PAKAL Heritage">PAKAL Heritage</option>
                    <option value="PAKAL Noir">PAKAL Noir</option>
                    <option value="PAKAL Origin">PAKAL Origin</option>
                    <option value="PAKAL Femme">PAKAL Femme</option>
                    <option value="PAKAL Kids">PAKAL Kids</option>
                  </select>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-coleccion">Colección</label>
                  <input type="text" id="prod-coleccion" name="coleccion" class="admin-form-input"
                         placeholder="Ej. Colección Tikal">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-categoria">Categoría <span>*</span></label>
                  <select id="prod-categoria" name="categoria_id" class="admin-form-select" required>
                    <option value="">Seleccionar categoría</option>
                    <?php
                    $genero_actual_form = '';
                    foreach ($categorias_form as $cat_f):
                        if ($cat_f['genero'] !== $genero_actual_form):
                            if ($genero_actual_form !== '') echo '</optgroup>';
                            echo '<optgroup label="' . ucfirst($cat_f['genero']) . '">';
                            $genero_actual_form = $cat_f['genero'];
                        endif;
                    ?>
                    <option value="<?= $cat_f['id'] ?>"><?= htmlspecialchars($cat_f['nombre']) ?></option>
                    <?php endforeach; ?>
                    <?php if ($genero_actual_form !== '') echo '</optgroup>'; ?>
                  </select>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-precio">Precio (MXN) <span>*</span></label>
                  <input type="number" id="prod-precio" name="precio" class="admin-form-input"
                         placeholder="0.00" min="0.01" step="0.01" required>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-precio-rebaja">Precio de rebaja (MXN)</label>
                  <input type="number" id="prod-precio-rebaja" name="precio_rebaja" class="admin-form-input"
                         placeholder="Dejar vacío si no aplica" min="0" step="0.01">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-stock">Stock <span>*</span></label>
                  <input type="number" id="prod-stock" name="stock" class="admin-form-input"
                         placeholder="0" min="0" value="0" required>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-material">Composición / Material</label>
                  <input type="text" id="prod-material" name="material" class="admin-form-input"
                         placeholder="Ej. 70% Lana · 20% Seda · 10% Cachemira">
                </div>

                <div class="admin-form-group form-full">
                  <label class="admin-form-label" for="prod-descripcion">Descripción</label>
                  <textarea id="prod-descripcion" name="descripcion" class="admin-form-textarea" rows="4"
                            placeholder="Descripción del producto, materiales, inspiración..."></textarea>
                </div>

              </div>

              <!-- Estado y publicación -->
              <div class="admin-form-grid admin-form-grid--3" style="margin-bottom:0;">
                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-estado">Estado</label>
                  <select id="prod-estado" name="estado" class="admin-form-select">
                    <option value="activo">Activo — visible en tienda</option>
                    <option value="borrador">Borrador — no visible</option>
                    <option value="agotado">Agotado — visible sin stock</option>
                  </select>
                </div>
                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-badge">Badge especial</label>
                  <select id="prod-badge" name="badge" class="admin-form-select">
                    <option value="">Sin badge</option>
                    <option value="nuevo">Nuevo</option>
                    <option value="rebaja">Rebaja</option>
                    <option value="exclusivo">Exclusivo</option>
                  </select>
                </div>
                <div class="admin-form-group">
                  <label class="admin-form-label" for="prod-destacado">¿Destacar en portada?</label>
                  <select id="prod-destacado" name="destacado" class="admin-form-select">
                    <option value="0">No destacar</option>
                    <option value="1">Sí, mostrar en portada</option>
                  </select>
                </div>
              </div>

              <!-- Imagen del producto -->
              <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--border);">
                <label class="admin-form-label" style="display:block;margin-bottom:12px;">Imagen del producto</label>
                <div style="display:flex;gap:20px;align-items:flex-start;">
                  <div id="img-preview-nuevo" style="width:90px;height:115px;border:1px dashed var(--border);border-radius:4px;overflow:hidden;flex-shrink:0;background:#F5F4F2;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:0.62rem;color:var(--gray-light);text-align:center;padding:8px;">Sin imagen</span>
                  </div>
                  <div>
                    <input type="file" name="imagen" id="img-file-nuevo" accept="image/*"
                           style="display:none;" onchange="previewImg(this,'img-preview-nuevo')">
                    <label for="img-file-nuevo" class="btn btn--sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                      Elegir imagen
                    </label>
                    <p style="font-size:0.68rem;color:var(--gray-light);margin-top:8px;line-height:1.6;">
                      JPG, PNG, WebP o SVG · máx. 5 MB<br>
                      <em>Si no subes imagen, se genera una automáticamente.</em>
                    </p>
                  </div>
                </div>
              </div>

              <!-- Acciones del formulario -->
              <div class="admin-form-actions">
                <button type="button" class="btn" onclick="showSection('productos')">Cancelar</button>
                <button type="submit" class="btn btn--solid">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Guardar producto
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>

      <!-- ── PEDIDOS SECTION ────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-pedidos">

        <!-- Filtros rápidos -->
        <?php
        $est_count = array_count_values(array_column($pedidos_todos, 'estado'));
        $filtros = [
            ''           => 'Todos',
            'pendiente'  => 'Pendiente',
            'confirmado' => 'Confirmado',
            'enviado'    => 'Enviado',
            'entregado'  => 'Entregado',
            'cancelado'  => 'Cancelado',
        ];
        ?>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;">
          <?php foreach ($filtros as $val => $label):
            $cnt = $val === '' ? count($pedidos_todos) : ($est_count[$val] ?? 0);
          ?>
          <button class="btn btn--sm pedido-filtro-btn <?= $val === '' ? 'btn--solid' : '' ?>"
                  data-estado="<?= $val ?>"
                  onclick="filtrarPedidos('<?= $val ?>')">
            <?= $label ?> (<?= $cnt ?>)
          </button>
          <?php endforeach; ?>
        </div>

        <div class="admin-table-card">
          <div class="admin-table-card__header">
            <h2 class="admin-table-card__title">Gestión de Pedidos</h2>
          </div>
          <table class="admin-table" id="tabla-pedidos">
            <thead>
              <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Artículos</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pedidos_todos)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--gray-light);padding:32px;">Sin pedidos registrados aún.</td></tr>
              <?php else: foreach ($pedidos_todos as $ped):
                $est_styles = [
                    'pendiente'  => 'background:#FFF3CD;color:#856404;',
                    'confirmado' => 'background:#CFE2FF;color:#084298;',
                    'enviado'    => 'background:#D1ECF1;color:#0C5460;',
                    'entregado'  => 'background:#D1E7DD;color:#0A3622;',
                    'cancelado'  => 'background:#F5F4F2;color:#6C757D;',
                ];
                $est_style = $est_styles[$ped['estado']] ?? '';
              ?>
              <tr data-estado="<?= $ped['estado'] ?>">
                <td style="font-weight:600;color:var(--dark);">#<?= str_pad($ped['id'], 4, '0', STR_PAD_LEFT) ?></td>
                <td>
                  <p style="font-size:0.82rem;font-weight:500;margin:0;"><?= htmlspecialchars($ped['nombre']) ?></p>
                  <p style="font-size:0.70rem;color:var(--gray-light);margin:2px 0 0;"><?= htmlspecialchars($ped['email']) ?></p>
                </td>
                <td style="color:var(--gray-mid);"><?= $ped['num_items'] ?> art.</td>
                <td style="font-weight:500;">$<?= number_format($ped['total'], 0, '.', ',') ?></td>
                <td>
                  <form method="POST" action="proceso/admin_pedidos.php" style="margin:0;">
                    <input type="hidden" name="action"     value="cambiar_estado">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="pedido_id"  value="<?= $ped['id'] ?>">
                    <select name="estado" onchange="this.form.submit()"
                            style="font-size:0.72rem;border:1px solid var(--border);border-radius:3px;padding:4px 6px;cursor:pointer;<?= $est_style ?>">
                      <?php foreach (['pendiente','confirmado','enviado','entregado','cancelado'] as $e): ?>
                      <option value="<?= $e ?>" <?= $ped['estado'] === $e ? 'selected' : '' ?>>
                        <?= ucfirst($e) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td style="color:var(--gray-light);font-size:0.78rem;"><?= date('d M Y', strtotime($ped['created_at'])) ?></td>
                <td>
                  <div class="table-actions">
                    <a href="/proyecto/admin.php?seccion=detalle-pedido&pedido_id=<?= $ped['id'] ?>"
                       class="table-action-btn" title="Ver detalle">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <?php if (in_array($ped['estado'], ['cancelado','entregado'])): ?>
                    <form method="POST" action="proceso/admin_pedidos.php" style="display:inline;"
                          onsubmit="return confirm('¿Eliminar pedido #<?= str_pad($ped['id'], 4, '0', STR_PAD_LEFT) ?>? Esta acción no se puede deshacer.');">
                      <input type="hidden" name="action"     value="eliminar">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                      <input type="hidden" name="pedido_id"  value="<?= $ped['id'] ?>">
                      <button type="submit" class="table-action-btn table-action-btn--danger" title="Eliminar pedido">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
          <div style="padding:14px 24px;border-top:1px solid var(--border);">
            <p style="font-size:0.72rem;color:var(--gray-light);">
              <?= count($pedidos_todos) ?> pedido<?= count($pedidos_todos) !== 1 ? 's' : '' ?> en total
              · <?= $count_activos ?> activo<?= $count_activos !== 1 ? 's' : '' ?>
            </p>
          </div>
        </div>
      </div>

      <!-- ── DETALLE PEDIDO ──────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-detalle-pedido">
        <?php if ($detalle_pedido):
          $det_est_styles = [
              'pendiente'  => 'background:#FFF3CD;color:#856404;',
              'confirmado' => 'background:#CFE2FF;color:#084298;',
              'enviado'    => 'background:#D1ECF1;color:#0C5460;',
              'entregado'  => 'background:#D1E7DD;color:#0A3622;',
              'cancelado'  => 'background:#F5F4F2;color:#6C757D;',
          ];
          $subtotal = array_sum(array_map(fn($i) => $i['cantidad'] * $i['precio_unit'], $detalle_items));
        ?>
        <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:16px;">
          <div>
            <h2 style="font-size:1.1rem;font-weight:600;margin:0;">
              Pedido #<?= str_pad($detalle_pedido['id'], 4, '0', STR_PAD_LEFT) ?>
            </h2>
            <p style="font-size:0.72rem;color:var(--gray-light);margin:4px 0 0;">
              <?= date('d \d\e F Y, H:i', strtotime($detalle_pedido['created_at'])) ?>
            </p>
          </div>
          <a href="/proyecto/admin.php?seccion=pedidos" class="btn btn--sm" style="margin-left:auto;">
            ← Volver a pedidos
          </a>
        </div>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

          <!-- Items del pedido -->
          <div class="admin-table-card">
            <div class="admin-table-card__header">
              <h2 class="admin-table-card__title">Productos del pedido</h2>
            </div>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th style="text-align:center;">Cant.</th>
                  <th style="text-align:right;">Precio unit.</th>
                  <th style="text-align:right;">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($detalle_items as $di): ?>
                <tr>
                  <td style="font-weight:500;"><?= htmlspecialchars($di['nombre']) ?></td>
                  <td style="text-align:center;color:var(--gray-mid);">× <?= $di['cantidad'] ?></td>
                  <td style="text-align:right;">$<?= number_format($di['precio_unit'], 2, '.', ',') ?></td>
                  <td style="text-align:right;font-weight:500;">$<?= number_format($di['cantidad'] * $di['precio_unit'], 2, '.', ',') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr style="border-top:2px solid var(--border);">
                  <td colspan="3" style="text-align:right;font-weight:600;padding:14px 16px;">Total</td>
                  <td style="text-align:right;font-weight:700;font-size:1rem;padding:14px 16px;">
                    $<?= number_format($detalle_pedido['total'], 2, '.', ',') ?> MXN
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- Panel derecho: cliente + estado -->
          <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Info del cliente -->
            <div class="admin-table-card">
              <div class="admin-table-card__header">
                <h2 class="admin-table-card__title">Datos del cliente</h2>
              </div>
              <div style="padding:16px 24px;display:flex;flex-direction:column;gap:10px;">
                <div>
                  <p style="font-size:0.68rem;color:var(--gray-light);margin:0 0 2px;text-transform:uppercase;letter-spacing:0.08em;">Nombre</p>
                  <p style="font-size:0.85rem;font-weight:500;margin:0;"><?= htmlspecialchars($detalle_pedido['nombre']) ?></p>
                </div>
                <div>
                  <p style="font-size:0.68rem;color:var(--gray-light);margin:0 0 2px;text-transform:uppercase;letter-spacing:0.08em;">Correo</p>
                  <p style="font-size:0.82rem;margin:0;"><?= htmlspecialchars($detalle_pedido['email']) ?></p>
                </div>
                <?php if ($detalle_pedido['telefono']): ?>
                <div>
                  <p style="font-size:0.68rem;color:var(--gray-light);margin:0 0 2px;text-transform:uppercase;letter-spacing:0.08em;">Teléfono</p>
                  <p style="font-size:0.82rem;margin:0;"><?= htmlspecialchars($detalle_pedido['telefono']) ?></p>
                </div>
                <?php endif; ?>
                <div>
                  <p style="font-size:0.68rem;color:var(--gray-light);margin:0 0 2px;text-transform:uppercase;letter-spacing:0.08em;">Dirección de entrega</p>
                  <p style="font-size:0.82rem;margin:0;line-height:1.5;"><?= htmlspecialchars($detalle_pedido['direccion']) ?></p>
                </div>
              </div>
            </div>

            <!-- Cambiar estado -->
            <div class="admin-table-card">
              <div class="admin-table-card__header">
                <h2 class="admin-table-card__title">Estado del pedido</h2>
              </div>
              <div style="padding:20px 24px;">
                <div style="display:inline-block;padding:6px 14px;border-radius:20px;font-size:0.75rem;font-weight:600;margin-bottom:16px;
                            <?= $det_est_styles[$detalle_pedido['estado']] ?? '' ?>">
                  <?= ucfirst($detalle_pedido['estado']) ?>
                </div>
                <form method="POST" action="proceso/admin_pedidos.php">
                  <input type="hidden" name="action"       value="cambiar_estado">
                  <input type="hidden" name="csrf_token"   value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="pedido_id"    value="<?= $detalle_pedido['id'] ?>">
                  <input type="hidden" name="desde_detalle" value="1">
                  <div style="display:flex;flex-direction:column;gap:10px;">
                    <select name="estado" class="admin-form-select">
                      <?php foreach (['pendiente'=>'Pendiente','confirmado'=>'Confirmado',
                                      'enviado'=>'Enviado','entregado'=>'Entregado','cancelado'=>'Cancelado'] as $v => $l): ?>
                      <option value="<?= $v ?>" <?= $detalle_pedido['estado'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn--solid" style="width:100%;">
                      Actualizar estado
                    </button>
                  </div>
                </form>

                <?php if (in_array($detalle_pedido['estado'], ['cancelado','entregado'])): ?>
                <form method="POST" action="proceso/admin_pedidos.php" style="margin-top:12px;"
                      onsubmit="return confirm('¿Eliminar este pedido permanentemente?');">
                  <input type="hidden" name="action"     value="eliminar">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                  <input type="hidden" name="pedido_id"  value="<?= $detalle_pedido['id'] ?>">
                  <button type="submit" class="btn" style="width:100%;border-color:#C44A4A;color:#C44A4A;">
                    Eliminar pedido
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </div>
        <?php else: ?>
        <div style="padding:64px;text-align:center;">
          <p style="color:var(--gray-mid);margin-bottom:16px;">Selecciona un pedido de la lista para ver su detalle.</p>
          <a href="/proyecto/admin.php?seccion=pedidos" class="btn btn--solid">Ver pedidos</a>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── CATEGORÍAS ─────────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-categorias">
        <div class="admin-table-card">
          <div class="admin-table-card__header">
            <h2 class="admin-table-card__title">Categorías del Catálogo</h2>
            <span style="font-size:0.72rem;color:var(--gray-light);"><?= count($categorias_admin) ?> categorías registradas</span>
          </div>
          <table class="admin-table">
            <thead>
              <tr><th>Nombre</th><th>Género</th><th>Slug</th><th>Productos activos</th><th>Estado</th></tr>
            </thead>
            <tbody>
              <?php foreach ($categorias_admin as $cat_a): ?>
              <tr>
                <td style="font-weight:500;"><?= htmlspecialchars($cat_a['nombre']) ?></td>
                <td style="text-transform:capitalize;color:var(--gray-mid);"><?= $cat_a['genero'] ?></td>
                <td style="font-family:monospace;font-size:0.75rem;color:var(--gray-light);"><?= htmlspecialchars($cat_a['slug']) ?></td>
                <td style="text-align:center;"><?= $cat_a['num_productos'] ?></td>
                <td>
                  <span class="table-badge <?= $cat_a['activo'] ? 'table-badge--active' : 'table-badge--inactivo' ?>">
                    <?= $cat_a['activo'] ? 'Activa' : 'Inactiva' ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── CLIENTES ────────────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-clientes">
        <div class="admin-table-card">
          <div class="admin-table-card__header">
            <h2 class="admin-table-card__title">Clientes Registrados</h2>
            <span style="font-size:0.72rem;color:var(--gray-light);"><?= count($clientes_admin) ?> cliente<?= count($clientes_admin) !== 1 ? 's' : '' ?></span>
          </div>
          <table class="admin-table">
            <thead>
              <tr><th>Cliente</th><th>Teléfono</th><th>Pedidos</th><th>Total gastado</th><th>Newsletter</th><th>Registro</th><th>Estado</th></tr>
            </thead>
            <tbody>
              <?php if (empty($clientes_admin)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--gray-light);padding:32px;">Aún no hay clientes registrados.</td></tr>
              <?php else: foreach ($clientes_admin as $cli): ?>
              <tr>
                <td>
                  <p style="font-size:0.82rem;font-weight:500;margin:0;"><?= htmlspecialchars($cli['nombre'] . ' ' . $cli['apellido']) ?></p>
                  <p style="font-size:0.70rem;color:var(--gray-light);margin:2px 0 0;"><?= htmlspecialchars($cli['email']) ?></p>
                </td>
                <td style="color:var(--gray-mid);font-size:0.78rem;"><?= htmlspecialchars($cli['telefono'] ?? '—') ?></td>
                <td style="text-align:center;"><?= $cli['num_pedidos'] ?></td>
                <td style="font-weight:500;">$<?= number_format($cli['total_gastado'], 0, '.', ',') ?></td>
                <td style="text-align:center;">
                  <?= $cli['newsletter'] ? '<span style="color:#2E7D32;font-size:0.9rem;">✓</span>' : '<span style="color:var(--gray-light);">—</span>' ?>
                </td>
                <td style="color:var(--gray-light);font-size:0.75rem;"><?= date('d M Y', strtotime($cli['created_at'])) ?></td>
                <td>
                  <span class="table-badge <?= $cli['activo'] ? 'table-badge--active' : 'table-badge--inactivo' ?>">
                    <?= $cli['activo'] ? 'Activo' : 'Inactivo' ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── NEWSLETTER ──────────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-newsletter">
        <div style="display:grid;grid-template-columns:200px 1fr;gap:20px;align-items:start;">
          <div class="stat-card">
            <p class="stat-card__label">Suscriptores</p>
            <p class="stat-card__value"><?= $count_newsletter ?></p>
            <p class="stat-card__change stat-card__change--up">Han dado su consentimiento</p>
          </div>
          <div class="admin-table-card">
            <div class="admin-table-card__header">
              <h2 class="admin-table-card__title">Lista de suscriptores</h2>
            </div>
            <table class="admin-table">
              <thead>
                <tr><th>Nombre</th><th>Correo electrónico</th><th>Fecha de suscripción</th></tr>
              </thead>
              <tbody>
                <?php
                $suscriptores = $pdo->query(
                    'SELECT nombre, apellido, email, created_at FROM usuarios
                     WHERE newsletter = 1 ORDER BY created_at DESC'
                )->fetchAll();
                if (empty($suscriptores)): ?>
                <tr><td colspan="3" style="text-align:center;color:var(--gray-light);padding:32px;">Sin suscriptores aún.</td></tr>
                <?php else: foreach ($suscriptores as $sus): ?>
                <tr>
                  <td style="font-weight:500;"><?= htmlspecialchars($sus['nombre'] . ' ' . $sus['apellido']) ?></td>
                  <td><?= htmlspecialchars($sus['email']) ?></td>
                  <td style="color:var(--gray-light);font-size:0.75rem;"><?= date('d M Y', strtotime($sus['created_at'])) ?></td>
                </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── DESCUENTOS ──────────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-descuentos">
        <div class="admin-table-card" style="text-align:center;padding:64px 32px;">
          <div style="width:48px;height:48px;margin:0 auto 20px;color:var(--gray-light);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
          </div>
          <h3 style="font-size:1rem;font-weight:600;margin-bottom:8px;">Módulo de Descuentos</h3>
          <p style="color:var(--gray-mid);font-size:0.85rem;max-width:400px;margin:0 auto 24px;line-height:1.6;">
            Aquí podrás crear y gestionar códigos de descuento, promociones por temporada y precios especiales para clientes frecuentes.
          </p>
          <span class="table-badge table-badge--agotado" style="font-size:0.75rem;padding:6px 16px;">Próximamente</span>
        </div>
      </div>

      <!-- ── CONFIGURACIÓN ───────────────────────────────────── -->
      <div class="admin-content tab-content" id="section-configuracion">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
          <div class="admin-table-card">
            <div class="admin-table-card__header">
              <h2 class="admin-table-card__title">Información del sistema</h2>
            </div>
            <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;">
              <?php foreach ([
                  'PHP' => PHP_VERSION,
                  'Base de datos' => DB_NAME . ' @ ' . DB_HOST,
                  'Servidor' => $_SERVER['SERVER_SOFTWARE'] ?? 'XAMPP',
                  'Zona horaria' => date_default_timezone_get(),
                  'Fecha del servidor' => date('d M Y, H:i'),
              ] as $label => $valor): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:14px;border-bottom:1px solid var(--border);">
                <span style="font-size:0.78rem;color:var(--gray-mid);"><?= $label ?></span>
                <span style="font-size:0.78rem;font-weight:500;font-family:monospace;"><?= htmlspecialchars($valor) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="admin-table-card">
            <div class="admin-table-card__header">
              <h2 class="admin-table-card__title">Resumen de datos</h2>
            </div>
            <div style="padding:20px 24px;display:flex;flex-direction:column;gap:14px;">
              <?php
              $resumen = [
                  'Productos activos'   => $stat_productos,
                  'Categorías'          => count($categorias_admin),
                  'Clientes'            => count($clientes_admin),
                  'Pedidos totales'     => $stat_pedidos,
                  'Suscriptores'        => $count_newsletter,
              ];
              foreach ($resumen as $label => $valor): ?>
              <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:14px;border-bottom:1px solid var(--border);">
                <span style="font-size:0.78rem;color:var(--gray-mid);"><?= $label ?></span>
                <span style="font-size:0.85rem;font-weight:600;"><?= $valor ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- ── EDIT PRODUCT SECTION ───────────────────────────── -->
      <div class="admin-content tab-content" id="section-editar-producto">
        <?php if ($edit_producto): ?>
        <div class="admin-form-card">
          <div class="admin-form-card__header">
            <h2 class="admin-form-card__title">Editar: <?= htmlspecialchars($edit_producto['nombre']) ?></h2>
          </div>
          <div class="admin-form-card__body">
            <form method="POST" action="proceso/admin_productos.php" enctype="multipart/form-data">

              <input type="hidden" name="action"      value="actualizar">
              <input type="hidden" name="csrf_token"  value="<?= htmlspecialchars($csrf) ?>">
              <input type="hidden" name="producto_id" value="<?= $edit_producto['id'] ?>">

              <div class="admin-form-grid" style="margin-bottom:32px;">

                <div class="admin-form-group form-full">
                  <label class="admin-form-label" for="edit-nombre">Nombre del producto <span>*</span></label>
                  <input type="text" id="edit-nombre" name="nombre" class="admin-form-input" required
                         value="<?= htmlspecialchars($edit_producto['nombre']) ?>">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-marca">Línea / Marca <span>*</span></label>
                  <select id="edit-marca" name="marca" class="admin-form-select" required>
                    <?php foreach (['PAKAL Heritage','PAKAL Noir','PAKAL Origin','PAKAL Femme','PAKAL Kids'] as $m): ?>
                    <option value="<?= $m ?>" <?= $edit_producto['marca'] === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-coleccion">Colección</label>
                  <input type="text" id="edit-coleccion" name="coleccion" class="admin-form-input"
                         value="<?= htmlspecialchars($edit_producto['coleccion'] ?? '') ?>">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-categoria">Categoría <span>*</span></label>
                  <select id="edit-categoria" name="categoria_id" class="admin-form-select" required>
                    <option value="">Seleccionar categoría</option>
                    <?php
                    $gen_edit = '';
                    foreach ($categorias_form as $cat_e):
                        if ($cat_e['genero'] !== $gen_edit):
                            if ($gen_edit !== '') echo '</optgroup>';
                            echo '<optgroup label="' . ucfirst($cat_e['genero']) . '">';
                            $gen_edit = $cat_e['genero'];
                        endif;
                    ?>
                    <option value="<?= $cat_e['id'] ?>"
                            <?= $edit_producto['categoria_id'] == $cat_e['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat_e['nombre']) ?>
                    </option>
                    <?php endforeach; if ($gen_edit !== '') echo '</optgroup>'; ?>
                  </select>
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-precio">Precio (MXN) <span>*</span></label>
                  <input type="number" id="edit-precio" name="precio" class="admin-form-input"
                         min="0.01" step="0.01" required
                         value="<?= $edit_producto['precio'] ?>">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-precio-rebaja">Precio de rebaja (MXN)</label>
                  <input type="number" id="edit-precio-rebaja" name="precio_rebaja" class="admin-form-input"
                         min="0" step="0.01" placeholder="Dejar vacío si no aplica"
                         value="<?= $edit_producto['precio_rebaja'] ?? '' ?>">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-stock">Stock <span>*</span></label>
                  <input type="number" id="edit-stock" name="stock" class="admin-form-input"
                         min="0" required value="<?= $edit_producto['stock'] ?>">
                </div>

                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-material">Composición / Material</label>
                  <input type="text" id="edit-material" name="material" class="admin-form-input"
                         value="<?= htmlspecialchars($edit_producto['material'] ?? '') ?>">
                </div>

                <div class="admin-form-group form-full">
                  <label class="admin-form-label" for="edit-descripcion">Descripción</label>
                  <textarea id="edit-descripcion" name="descripcion" class="admin-form-textarea" rows="4"><?= htmlspecialchars($edit_producto['descripcion'] ?? '') ?></textarea>
                </div>

              </div>

              <div class="admin-form-grid admin-form-grid--3" style="margin-bottom:24px;">
                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-estado">Estado</label>
                  <select id="edit-estado" name="estado" class="admin-form-select">
                    <?php foreach (['activo'=>'Activo — visible en tienda','borrador'=>'Borrador — no visible','agotado'=>'Agotado — visible sin stock'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $edit_producto['estado'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-badge">Badge especial</label>
                  <select id="edit-badge" name="badge" class="admin-form-select">
                    <?php foreach (['' => 'Sin badge','nuevo'=>'Nuevo','rebaja'=>'Rebaja','exclusivo'=>'Exclusivo'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= $edit_producto['badge'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="admin-form-group">
                  <label class="admin-form-label" for="edit-destacado">¿Destacar en portada?</label>
                  <select id="edit-destacado" name="destacado" class="admin-form-select">
                    <option value="0" <?= !$edit_producto['destacado'] ? 'selected' : '' ?>>No destacar</option>
                    <option value="1" <?= $edit_producto['destacado'] ? 'selected' : '' ?>>Sí, mostrar en portada</option>
                  </select>
                </div>
              </div>

              <!-- Imagen actual + reemplazo -->
              <div style="padding-top:24px;border-top:1px solid var(--border);">
                <label class="admin-form-label" style="display:block;margin-bottom:12px;">Imagen del producto</label>
                <div style="display:flex;gap:20px;align-items:flex-start;">
                  <div id="img-preview-edit" style="width:90px;height:115px;border:1px solid var(--border);border-radius:4px;overflow:hidden;flex-shrink:0;">
                    <?php if ($edit_imagen): ?>
                      <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($edit_imagen) ?>"
                           alt="Imagen actual" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                      <div style="width:100%;height:100%;background:#F5F4F2;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:0.62rem;color:var(--gray-light);text-align:center;padding:4px;">Sin imagen</span>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div>
                    <input type="file" name="imagen" id="img-file-edit" accept="image/*"
                           style="display:none;" onchange="previewImg(this,'img-preview-edit')">
                    <label for="img-file-edit" class="btn btn--sm" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                      <?= $edit_imagen ? 'Cambiar imagen' : 'Subir imagen' ?>
                    </label>
                    <p style="font-size:0.68rem;color:var(--gray-light);margin-top:8px;line-height:1.6;">
                      JPG, PNG, WebP o SVG · máx. 5 MB<br>
                      <?= $edit_imagen ? '<em>Deja vacío para conservar la imagen actual.</em>' : '<em>Opcional.</em>' ?>
                    </p>
                  </div>
                </div>
              </div>

              <div class="admin-form-actions">
                <a href="/proyecto/admin.php?seccion=productos" class="btn">Cancelar</a>
                <button type="submit" class="btn btn--solid">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
                  Guardar cambios
                </button>
              </div>

            </form>
          </div>
        </div>
        <?php else: ?>
        <div style="padding:64px;text-align:center;">
          <p style="color:var(--gray-mid);margin-bottom:16px;">Selecciona un producto de la lista para editarlo.</p>
          <a href="/proyecto/admin.php?seccion=productos" class="btn btn--solid">Ver productos</a>
        </div>
        <?php endif; ?>
      </div>

    </main>
  </div>

  <script>
    function showSection(id) {
      document.querySelectorAll('.tab-content').forEach(s => s.classList.remove('active'));
      const el = document.getElementById('section-' + id);
      if (el) el.classList.add('active');
      const titles = {
        dashboard:         'Dashboard',
        productos:         'Gestión de Productos',
        'nuevo-producto':  'Agregar Producto',
        'editar-producto': 'Editar Producto',
        pedidos:           'Gestión de Pedidos',
        'detalle-pedido':  'Detalle de Pedido',
        categorias:        'Categorías',
        clientes:          'Clientes',
        newsletter:        'Newsletter',
        descuentos:        'Descuentos',
        configuracion:     'Configuración',
      };
      const breadcrumbs = {
        dashboard:         'PAKAL Admin › Dashboard',
        productos:         'PAKAL Admin › Catálogo › Productos',
        'nuevo-producto':  'PAKAL Admin › Catálogo › Agregar Producto',
        'editar-producto': 'PAKAL Admin › Catálogo › Editar Producto',
        pedidos:           'PAKAL Admin › Ventas › Pedidos',
        'detalle-pedido':  'PAKAL Admin › Ventas › Detalle de Pedido',
        categorias:        'PAKAL Admin › Catálogo › Categorías',
        clientes:          'PAKAL Admin › Ventas › Clientes',
        newsletter:        'PAKAL Admin › Marketing › Newsletter',
        descuentos:        'PAKAL Admin › Marketing › Descuentos',
        configuracion:     'PAKAL Admin › Sistema › Configuración',
      };
      document.getElementById('pageTitle').textContent      = titles[id]      || id;
      document.getElementById('pageBreadcrumb').textContent = breadcrumbs[id] || '';
    }

    function previewImg(input, previewId) {
      const preview = document.getElementById(previewId);
      if (!preview || !input.files || !input.files[0]) return;
      const reader = new FileReader();
      reader.onload = e => {
        preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
      };
      reader.readAsDataURL(input.files[0]);
    }

    function filtrarPedidos(estado) {
      document.querySelectorAll('#tabla-pedidos tbody tr[data-estado]').forEach(tr => {
        tr.style.display = (!estado || tr.dataset.estado === estado) ? '' : 'none';
      });
      document.querySelectorAll('.pedido-filtro-btn').forEach(btn => {
        const activo = btn.dataset.estado === estado;
        btn.classList.toggle('btn--solid', activo);
        btn.style.opacity = activo ? '1' : '0.7';
      });
    }

    const seccion  = new URLSearchParams(window.location.search).get('seccion');
    const pedidoId = new URLSearchParams(window.location.search).get('pedido_id');
    showSection(seccion || 'dashboard');
  </script>

</body>
</html>
