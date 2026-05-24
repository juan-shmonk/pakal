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

            <!-- MÉTODO DE PAGO -->
            <div style="margin-bottom:24px;">
              <p style="font-size:0.68rem;font-weight:500;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:12px;">
                Método de pago <span style="color:#C44A4A;">*</span>
              </p>

              <!-- Tabs de método -->
              <div class="pay-tabs">
                <button type="button" class="pay-tab pay-tab--active" data-method="tarjeta">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                  Tarjeta
                </button>
                <button type="button" class="pay-tab" data-method="contra_entrega">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  Contra entrega
                </button>
              </div>
              <input type="hidden" name="metodo_pago" id="metodo_pago" value="tarjeta">

              <!-- Panel: Tarjeta -->
              <div id="panel-tarjeta" class="pay-panel">

                <!-- Vista previa de la tarjeta -->
                <div class="card-preview" id="cardPreview">
                  <div class="card-preview__inner" id="cardInner">
                    <!-- Frente -->
                    <div class="card-preview__front">
                      <div class="card-preview__top">
                        <span class="card-preview__brand-label">PAKAL</span>
                        <div class="card-preview__brand-logo" id="cardBrandLogo">
                          <!-- ícono dinámico -->
                          <svg viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg" width="44" height="30" id="logoGeneric">
                            <rect x="1" y="1" width="46" height="30" rx="3" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                            <line x1="1" y1="10" x2="47" y2="10" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
                          </svg>
                          <svg viewBox="0 0 48 32" xmlns="http://www.w3.org/2000/svg" width="44" height="30" id="logoVisa" style="display:none;">
                            <text x="4" y="22" font-family="Arial" font-weight="bold" font-size="16" fill="white" letter-spacing="1">VISA</text>
                          </svg>
                          <svg viewBox="0 0 48 32" xmlns="http://www.w3.org/2000/svg" width="44" height="30" id="logoMC" style="display:none;">
                            <circle cx="18" cy="16" r="10" fill="#EB001B" opacity="0.9"/>
                            <circle cx="30" cy="16" r="10" fill="#F79E1B" opacity="0.9"/>
                          </svg>
                          <svg viewBox="0 0 48 32" xmlns="http://www.w3.org/2000/svg" width="44" height="30" id="logoAmex" style="display:none;">
                            <text x="3" y="21" font-family="Arial" font-weight="bold" font-size="11" fill="white" letter-spacing="0.5">AMEX</text>
                          </svg>
                        </div>
                      </div>
                      <div class="card-preview__chip" aria-hidden="true">
                        <svg viewBox="0 0 34 26" fill="none" xmlns="http://www.w3.org/2000/svg" width="34" height="26">
                          <rect x="1" y="1" width="32" height="24" rx="3" fill="#C4A96A" stroke="#B8963C" stroke-width="0.5"/>
                          <line x1="12" y1="1" x2="12" y2="25" stroke="#B8963C" stroke-width="0.5"/>
                          <line x1="22" y1="1" x2="22" y2="25" stroke="#B8963C" stroke-width="0.5"/>
                          <line x1="1" y1="9" x2="33" y2="9" stroke="#B8963C" stroke-width="0.5"/>
                          <line x1="1" y1="17" x2="33" y2="17" stroke="#B8963C" stroke-width="0.5"/>
                          <rect x="12" y="9" width="10" height="8" fill="#B8963C" opacity="0.4"/>
                        </svg>
                      </div>
                      <p class="card-preview__number" id="previewNumber">•••• &nbsp;•••• &nbsp;•••• &nbsp;••••</p>
                      <div class="card-preview__bottom">
                        <div>
                          <span class="card-preview__label">Titular</span>
                          <span class="card-preview__value" id="previewName">NOMBRE APELLIDO</span>
                        </div>
                        <div>
                          <span class="card-preview__label">Vence</span>
                          <span class="card-preview__value" id="previewExpiry">MM/AA</span>
                        </div>
                      </div>
                    </div>
                    <!-- Reverso -->
                    <div class="card-preview__back">
                      <div class="card-preview__magstripe"></div>
                      <div class="card-preview__cvv-row">
                        <div class="card-preview__signature"></div>
                        <div class="card-preview__cvv-box">
                          <span class="card-preview__label" style="color:var(--dark);">CVV</span>
                          <span class="card-preview__cvv-val" id="previewCvv">•••</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Campos de tarjeta -->
                <div class="card-fields">
                  <div class="card-field-group">
                    <label class="card-field-label">Número de tarjeta</label>
                    <div style="position:relative;">
                      <input type="text" id="cardNumber" class="form-input card-input"
                             placeholder="1234  5678  9012  3456"
                             maxlength="22" autocomplete="cc-number" inputmode="numeric">
                      <span class="card-input__status" id="cardNumberStatus" aria-live="polite"></span>
                    </div>
                    <span class="card-field-error" id="errCardNumber"></span>
                  </div>

                  <div class="card-field-group">
                    <label class="card-field-label">Nombre del titular (como aparece en la tarjeta)</label>
                    <input type="text" id="cardName" class="form-input card-input"
                           placeholder="JUAN PÉREZ" autocomplete="cc-name"
                           style="text-transform:uppercase;">
                    <span class="card-field-error" id="errCardName"></span>
                  </div>

                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="card-field-group">
                      <label class="card-field-label">Fecha de vencimiento</label>
                      <input type="text" id="cardExpiry" class="form-input card-input"
                             placeholder="MM/AA" maxlength="5"
                             autocomplete="cc-exp" inputmode="numeric">
                      <span class="card-field-error" id="errCardExpiry"></span>
                    </div>
                    <div class="card-field-group">
                      <label class="card-field-label">CVV</label>
                      <input type="text" id="cardCvv" class="form-input card-input"
                             placeholder="•••" maxlength="4"
                             autocomplete="cc-csc" inputmode="numeric">
                      <span class="card-field-error" id="errCardCvv"></span>
                    </div>
                  </div>

                  <div class="card-accepted">
                    <span>Aceptamos:</span>
                    <svg viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" width="38" height="24" title="Visa"><rect width="38" height="24" rx="3" fill="#1A1F71"/><text x="6" y="17" font-family="Arial" font-weight="bold" font-size="13" fill="white">VISA</text></svg>
                    <svg viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" width="38" height="24" title="Mastercard"><rect width="38" height="24" rx="3" fill="#252525"/><circle cx="15" cy="12" r="7" fill="#EB001B"/><circle cx="23" cy="12" r="7" fill="#F79E1B"/><path d="M19 6.8a7 7 0 0 1 0 10.4A7 7 0 0 1 19 6.8z" fill="#FF5F00"/></svg>
                    <svg viewBox="0 0 38 24" xmlns="http://www.w3.org/2000/svg" width="38" height="24" title="Amex"><rect width="38" height="24" rx="3" fill="#2E77BC"/><text x="4" y="16" font-family="Arial" font-weight="bold" font-size="9" fill="white">AMEX</text></svg>
                  </div>
                </div>
              </div>

              <!-- Panel: Contra entrega -->
              <div id="panel-contra_entrega" class="pay-panel" style="display:none;">
                <div class="pay-panel__info">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <div>
                    <strong>Pago al recibir tu pedido</strong>
                    <p>Nuestro equipo te contactará para coordinar la entrega y el método de pago exacto (efectivo o transferencia SPEI).</p>
                  </div>
                </div>
              </div>

            </div>

            <!-- Botón con estado de carga -->
            <button type="submit" id="btnConfirmar" class="btn btn--solid" style="width:100%;justify-content:center;padding:16px;font-size:0.78rem;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span id="btnConfirmarText">Confirmar Pedido — $<?= number_format($total, 0, '.', ',') ?> MXN</span>
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
