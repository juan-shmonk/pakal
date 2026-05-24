<?php
// ── Checkout ─────────────────────────────────────────────────
require_once 'config/session.php';
require_once 'config/database.php';

if (!estaLogueado()) {
    flash('warning', 'Debes iniciar sesión para completar tu compra.');
    header('Location: auth.php?tab=login');
    exit;
}

$pdo        = getPDO();
$usuario_id = $_SESSION['usuario_id'];
$usuario    = usuarioActual();

// Obtener ítems del carrito
$stmt = $pdo->prepare(
    'SELECT ci.id, ci.cantidad, ci.precio_unit,
            p.id AS producto_id, p.nombre, p.marca
     FROM carrito_items ci
     JOIN carritos c ON ci.carrito_id = c.id
     JOIN productos p ON ci.producto_id = p.id
     WHERE c.usuario_id = ?
     ORDER BY ci.id ASC'
);
$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll();

if (empty($items)) {
    flash('warning', 'Tu carrito está vacío.');
    header('Location: carrito.php');
    exit;
}

$total = array_sum(array_map(fn($i) => $i['cantidad'] * $i['precio_unit'], $items));
$csrf  = generarCSRF();

$pageTitle = 'Finalizar Compra — PAKAL';
$activeNav = '';
$activeCat = 'hombre';
require_once 'includes/header.php';
?>

    <div class="container" style="max-width:900px;padding-top:56px;padding-bottom:80px;">

      <!-- Breadcrumb / pasos -->
      <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:48px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.65rem;color:var(--gray-light);">1</div>
          <span style="font-size:0.68rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">Carrito</span>
        </div>
        <div style="width:80px;height:1px;background:var(--border);margin:0 16px;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;background:var(--black);display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:600;color:var(--white);">2</div>
          <span style="font-size:0.68rem;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;">Datos</span>
        </div>
        <div style="width:80px;height:1px;background:var(--border);margin:0 16px;"></div>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:0.65rem;color:var(--gray-light);">3</div>
          <span style="font-size:0.68rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gray-light);">Confirmación</span>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 340px;gap:40px;align-items:start;">

        <!-- Formulario de envío -->
        <div>
          <h1 style="font-family:var(--font-display);font-size:1.6rem;font-weight:400;margin-bottom:32px;">Datos de Entrega</h1>

          <form method="POST" action="/proyecto/proceso/checkout.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
              <div>
                <label style="display:block;font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:6px;">
                  Nombre <span style="color:#C44A4A;">*</span>
                </label>
                <input type="text" name="nombre" class="form-input"
                       value="<?= htmlspecialchars($usuario['nombre']) ?>"
                       required placeholder="Nombre">
              </div>
              <div>
                <label style="display:block;font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:6px;">
                  Apellido <span style="color:#C44A4A;">*</span>
                </label>
                <input type="text" name="apellido" class="form-input"
                       value="<?= htmlspecialchars($usuario['apellido']) ?>"
                       required placeholder="Apellido">
              </div>
            </div>

            <div style="margin-bottom:16px;">
              <label style="display:block;font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:6px;">
                Correo electrónico <span style="color:#C44A4A;">*</span>
              </label>
              <input type="email" name="email" class="form-input"
                     value="<?= htmlspecialchars($usuario['email']) ?>"
                     required placeholder="correo@ejemplo.com">
            </div>

            <div style="margin-bottom:16px;">
              <label style="display:block;font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:6px;">
                Teléfono
              </label>
              <input type="tel" name="telefono" class="form-input"
                     value="<?= htmlspecialchars($usuario['id'] ? '' : '') ?>"
                     placeholder="55 1234 5678">
            </div>

            <div style="margin-bottom:16px;">
              <label style="display:block;font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:6px;">
                Dirección <span style="color:#C44A4A;">*</span>
              </label>
              <input type="text" name="direccion" class="form-input"
                     required placeholder="Calle, número interior/exterior, colonia">
            </div>

            <div style="margin-bottom:32px;">
              <label style="display:block;font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:6px;">
                Ciudad <span style="color:#C44A4A;">*</span>
              </label>
              <input type="text" name="ciudad" class="form-input"
                     required placeholder="Ciudad de México">
            </div>

            <div style="padding:20px;background:#F5F4F2;border:1px solid var(--border);margin-bottom:24px;">
              <p style="font-size:0.68rem;letter-spacing:0.1em;text-transform:uppercase;font-weight:500;margin-bottom:8px;">Método de pago</p>
              <p style="font-size:0.82rem;color:var(--gray-mid);">Contra entrega / Transferencia bancaria</p>
              <p style="font-size:0.72rem;color:var(--gray-light);margin-top:4px;">El equipo de PAKAL te contactará para coordinar el pago.</p>
            </div>

            <button type="submit" class="btn btn--solid" style="width:100%;justify-content:center;padding:16px;font-size:0.78rem;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Confirmar Pedido — $<?= number_format($total, 0, '.', ',') ?> MXN
            </button>

            <p style="font-size:0.65rem;color:var(--gray-light);text-align:center;margin-top:12px;">
              Al confirmar aceptas nuestros términos de servicio.
            </p>
          </form>
        </div>

        <!-- Resumen del pedido -->
        <aside>
          <h2 style="font-size:0.78rem;font-weight:600;letter-spacing:0.12em;text-transform:uppercase;margin-bottom:20px;">Resumen del Pedido</h2>

          <?php foreach ($items as $item): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--border);">
            <div>
              <p style="font-size:0.82rem;font-weight:500;"><?= htmlspecialchars($item['nombre']) ?></p>
              <p style="font-size:0.68rem;color:var(--gray-mid);"><?= htmlspecialchars($item['marca']) ?> · Cant: <?= $item['cantidad'] ?></p>
            </div>
            <span style="font-size:0.85rem;font-weight:500;">
              $<?= number_format($item['cantidad'] * $item['precio_unit'], 0, '.', ',') ?>
            </span>
          </div>
          <?php endforeach; ?>

          <div style="display:flex;justify-content:space-between;padding:16px 0;border-bottom:1px solid var(--border);">
            <span style="font-size:0.78rem;color:var(--gray-mid);">Envío</span>
            <span style="font-size:0.78rem;color:#2E7D32;">Gratis</span>
          </div>

          <div style="display:flex;justify-content:space-between;padding:16px 0;font-weight:600;">
            <span style="font-size:0.9rem;">Total</span>
            <span style="font-size:0.9rem;">$<?= number_format($total, 0, '.', ',') ?> MXN</span>
          </div>

          <a href="/proyecto/carrito.php" style="display:block;text-align:center;font-size:0.68rem;color:var(--gray-mid);text-decoration:underline;margin-top:8px;">
            ← Volver al carrito
          </a>
        </aside>

      </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
