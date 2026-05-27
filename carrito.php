<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: carrito.php                                       ║
// ║  PROPÓSITO: Mostrar el carrito de compras del usuario        ║
// ║                                                              ║
// ║  Esta página muestra todos los productos que el usuario      ║
// ║  tiene pendientes de comprar. Desde aquí puede:             ║
// ║  - Ver los productos con su imagen, nombre y precio          ║
// ║  - Cambiar la cantidad de cada producto                      ║
// ║  - Eliminar productos del carrito                            ║
// ║  - Ver el resumen con el total a pagar                       ║
// ║  - Proceder a "Finalizar compra" (ir a checkout.php)         ║
// ║                                                              ║
// ║  Si el usuario NO está logueado, el carrito aparece vacío   ║
// ║  y se invita a iniciar sesión.                               ║
// ╚══════════════════════════════════════════════════════════════╝

require_once 'config/session.php';
require_once 'config/database.php';

// Iniciamos con el carrito vacío y total en cero.
$items = [];
$total = 0.00;

// Solo consultamos la base de datos si hay un usuario logueado.
// Un visitante anónimo no tiene carrito en la BD.
if (estaLogueado()) {
    $pdo  = getPDO();

    // Consultamos todos los ítems del carrito del usuario actual.
    // JOIN une tres tablas:
    //   carrito_items → los productos en el carrito (cantidad, precio)
    //   carritos       → el contenedor del carrito (identifica al dueño)
    //   productos      → los datos del producto (nombre, marca, stock disponible)
    $stmt = $pdo->prepare(
        'SELECT ci.id, ci.cantidad, ci.precio_unit,
                p.id AS producto_id, p.nombre, p.marca, p.stock
         FROM carrito_items ci
         JOIN carritos c  ON ci.carrito_id  = c.id
         JOIN productos p ON ci.producto_id = p.id
         WHERE c.usuario_id = ?
         ORDER BY ci.id ASC'  // Mostramos en el orden en que se agregaron
    );
    $stmt->execute([$_SESSION['usuario_id']]);
    $items = $stmt->fetchAll();

    // Calculamos el total sumando precio × cantidad de cada ítem.
    foreach ($items as $item) {
        $total += $item['cantidad'] * $item['precio_unit'];
    }

    // Obtenemos las imágenes de todos los productos del carrito en una sola consulta.
    // array_column extrae todos los producto_id en un array simple.
    $imgs_carrito = imagenesPorIds($pdo, array_column($items, 'producto_id'));
}

$pageTitle = 'Carrito — PAKAL';
$activeNav = '';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <div class="cart-page container">

      <!-- Pasos del checkout -->
      <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:56px;padding-top:48px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;background:var(--black);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:600;color:var(--white);">1</div>
          <span style="font-size:0.68rem;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Carrito</span>
        </div>
        <div style="width:80px;height:1px;background:var(--border);margin:0 16px;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.65rem;color:var(--gray-light);">2</div>
          <span style="font-size:0.68rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">Datos</span>
        </div>
        <div style="width:80px;height:1px;background:var(--border);margin:0 16px;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.65rem;color:var(--gray-light);">3</div>
          <span style="font-size:0.68rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">Confirmación</span>
        </div>
      </div>

      <div class="cart-page__inner">

        <!-- Ítems del carrito -->
        <div>
          <div class="cart-header">
            <h1 class="cart-header__title">Mi Carrito</h1>
            <span class="cart-header__count"><?= count($items) ?> producto<?= count($items) !== 1 ? 's' : '' ?></span>
          </div>

          <?php if (empty($items)): ?>

            <?php if (!estaLogueado()): ?>
            <div style="padding:48px 0;text-align:center;">
              <p style="font-size:0.95rem;color:var(--gray-mid);margin-bottom:24px;">
                Inicia sesión para ver tu carrito.
              </p>
              <a href="/proyecto/auth.php?tab=login" class="btn btn--solid">Iniciar sesión</a>
            </div>
            <?php else: ?>
            <div style="padding:48px 0;text-align:center;">
              <p style="font-size:0.95rem;color:var(--gray-mid);margin-bottom:24px;">
                Tu carrito está vacío.
              </p>
              <a href="/proyecto/productos.php" class="btn btn--solid">Explorar productos</a>
            </div>
            <?php endif; ?>

          <?php else: ?>

            <?php
            $imgs_carrito = $imgs_carrito ?? [];
            foreach ($items as $idx => $item):
                $img_ci = $imgs_carrito[$item['producto_id']] ?? null;
            ?>
            <article class="cart-item" aria-label="<?= htmlspecialchars($item['nombre']) ?>">
              <div class="cart-item__img">
                <?php if ($img_ci): ?>
                  <img src="/proyecto/assets/img/productos/<?= htmlspecialchars($img_ci) ?>"
                       alt="<?= htmlspecialchars($item['nombre']) ?>"
                       style="width:100%;height:100%;object-fit:cover;display:block;">
                <?php else: ?>
                  <div class="cart-item__img-placeholder ci-<?= ($idx % 5) + 1 ?>" style="width:100%;height:100%;"></div>
                <?php endif; ?>
              </div>
              <div class="cart-item__info">
                <p class="cart-item__brand"><?= htmlspecialchars($item['marca']) ?></p>
                <h2 class="cart-item__name"><?= htmlspecialchars($item['nombre']) ?></h2>

                <!-- Actualizar cantidad -->
                <form method="POST" action="/proyecto/proceso/carrito.php"
                      style="display:inline-flex;align-items:center;gap:0;margin-top:12px;">
                  <input type="hidden" name="action"  value="actualizar">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <div class="cart-item__qty" aria-label="Cantidad">
                    <button type="button" class="qty-btn" aria-label="Disminuir"
                            onclick="let i=this.nextElementSibling;if(i.value>1){i.value--;this.form.submit();}">−</button>
                    <input type="number" class="qty-input" name="cantidad"
                           value="<?= $item['cantidad'] ?>" min="1"
                           max="<?= $item['stock'] ?>" aria-label="Cantidad"
                           onchange="this.form.submit()">
                    <button type="button" class="qty-btn" aria-label="Aumentar"
                            onclick="let i=this.previousElementSibling;i.value=parseInt(i.value)+1;this.form.submit();">+</button>
                  </div>
                </form>
              </div>

              <div class="cart-item__price-col">
                <span class="cart-item__price">
                  $<?= number_format($item['cantidad'] * $item['precio_unit'], 0, '.', ',') ?> MXN
                </span>
                <?php if ($item['cantidad'] > 1): ?>
                <span style="font-size:0.68rem;color:var(--gray-light);">
                  $<?= number_format($item['precio_unit'], 0, '.', ',') ?> × <?= $item['cantidad'] ?>
                </span>
                <?php endif; ?>

                <!-- Eliminar ítem -->
                <form method="POST" action="/proyecto/proceso/carrito.php" style="margin-top:8px;">
                  <input type="hidden" name="action"  value="eliminar">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="cart-item__remove">Eliminar</button>
                </form>
              </div>
            </article>
            <?php endforeach; ?>

          <?php endif; ?>

          <div style="padding-top:24px;">
            <a href="/proyecto/productos.php"
               style="display:inline-flex;align-items:center;gap:8px;font-size:0.68rem;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-mid);">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
              </svg>
              Continuar comprando
            </a>
          </div>
        </div>

        <!-- Resumen del pedido -->
        <aside class="cart-summary" aria-label="Resumen del pedido">
          <h2 class="cart-summary__title">Resumen del pedido</h2>

          <div class="summary-row">
            <span class="summary-row__label">Subtotal (<?= count($items) ?> productos)</span>
            <span class="summary-row__value">$<?= number_format($total, 0, '.', ',') ?> MXN</span>
          </div>
          <div class="summary-row">
            <span class="summary-row__label">Envío</span>
            <span class="summary-row__value" style="color:#2E7D32;font-size:0.78rem;">Gratis</span>
          </div>

          <div class="summary-row summary-row--total">
            <span class="summary-row__label">Total</span>
            <span class="summary-row__value">$<?= number_format($total, 0, '.', ',') ?> MXN</span>
          </div>

          <p style="font-size:0.65rem;color:var(--gray-light);margin:8px 0 20px;line-height:1.6;">
            Incluye IVA. Envío gratuito en todos los pedidos.
          </p>

          <?php if (!empty($items)): ?>
            <?php if (estaLogueado()): ?>
            <a href="/proyecto/checkout.php" class="cart-summary__checkout" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="11" width="18" height="11" rx="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
              Finalizar compra
            </a>
            <?php else: ?>
            <a href="/proyecto/auth.php?tab=login" class="cart-summary__checkout" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
              Iniciar sesión para comprar
            </a>
            <p style="font-size:0.65rem;color:var(--gray-mid);margin-top:10px;text-align:center;">
              Necesitas <a href="/proyecto/auth.php" style="color:var(--dark);text-decoration:underline;">iniciar sesión</a> para completar tu pedido.
            </p>
            <?php endif; ?>
          <?php else: ?>
            <button class="cart-summary__checkout" disabled style="opacity:0.5;cursor:not-allowed;">
              Carrito vacío
            </button>
          <?php endif; ?>

          <div class="cart-summary__secure">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Pago 100% seguro
          </div>
        </aside>

      </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
