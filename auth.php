<?php
// ── Auth Page — Login y Registro ────────────────────────────
require_once __DIR__ . '/config/session.php';

// Si ya está logueado, redirigir
if (estaLogueado()) {
    header('Location: index.php');
    exit;
}

// Tab activo: login | registro
$tab = in_array($_GET['tab'] ?? '', ['login', 'registro']) ? $_GET['tab'] : 'login';

// Recuperar prefill (errores previos)
$emailPrefill = htmlspecialchars($_SESSION['auth_email_prefill'] ?? '');
unset($_SESSION['auth_email_prefill']);

$regPrefill = $_SESSION['reg_prefill'] ?? [];
unset($_SESSION['reg_prefill']);

// Flash messages
$flashes = obtenerFlash();

// CSRF token
$csrf = generarCSRF();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $tab === 'registro' ? 'Crear cuenta' : 'Iniciar sesión' ?> — PAKAL</title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* ── Flash Messages ─────────────────────────────────────── */
    .flash-overlay {
      position: fixed;
      top: 24px;
      right: 24px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-width: 400px;
    }
    .flash {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 18px;
      border-left: 3px solid;
      background: var(--white);
      box-shadow: 0 4px 24px rgba(0,0,0,0.10);
      animation: slideIn 0.28s ease;
    }
    @keyframes slideIn {
      from { opacity:0; transform:translateX(20px); }
      to   { opacity:1; transform:translateX(0);    }
    }
    .flash--success { border-color: #2E7D32; }
    .flash--error   { border-color: #C44A4A; }
    .flash--warning { border-color: var(--gold); }
    .flash--info    { border-color: var(--dark); }
    .flash__icon { font-size: 1rem; flex-shrink:0; margin-top: 1px; }
    .flash--success .flash__icon { color: #2E7D32; }
    .flash--error   .flash__icon { color: #C44A4A; }
    .flash__text { font-size: 0.82rem; color: var(--dark); line-height: 1.5; }
    .flash__text a { color: var(--black); text-decoration: underline; }
    .flash__close {
      background: none; border: none;
      color: var(--gray-light); font-size: 0.9rem;
      cursor: pointer; flex-shrink: 0;
      transition: color 0.2s;
    }
    .flash__close:hover { color: var(--dark); }

    /* ── Auth layout ─────────────────────────────────────────── */
    .auth-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.68rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--gray-light);
      margin-bottom: 40px;
      text-decoration: none;
      transition: color 0.28s;
    }
    .auth-back:hover { color: var(--white); }

    /* ── Password toggle ────────────────────────────────────── */
    .field-password { position: relative; }
    .field-password input { padding-right: 44px; }
    .toggle-pass {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--gray-light);
      cursor: pointer;
      font-size: 0.78rem;
      letter-spacing: 0.06em;
      padding: 4px;
      transition: color 0.2s;
    }
    .toggle-pass:hover { color: var(--dark); }

    /* ── Password strength bar ──────────────────────────────── */
    .password-strength { margin-top: 6px; }
    .strength-bar {
      height: 3px;
      border-radius: 2px;
      background: var(--border);
      overflow: hidden;
      margin-bottom: 4px;
    }
    .strength-fill {
      height: 100%;
      width: 0%;
      transition: width 0.3s, background 0.3s;
      border-radius: 2px;
    }
    .strength-label {
      font-size: 0.65rem;
      color: var(--gray-light);
    }

    /* ── Error inline ────────────────────────────────────────── */
    .field-error {
      font-size: 0.68rem;
      color: #C44A4A;
      margin-top: 4px;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .form-input.invalid { border-color: #C44A4A; }
    .form-input.valid   { border-color: #2E7D32; }

    /* ── Register form scroll ─────────────────────────────────── */
    .auth-page__form-area { overflow-y: auto; }
    @media (max-height: 750px) {
      .auth-page__form-area { padding: 40px 60px; }
    }
  </style>
</head>
<body>

<!-- Flash messages (posición fija) -->
<?php if (!empty($flashes)): ?>
<div class="flash-overlay" role="alert" aria-live="polite">
  <?php foreach ($flashes as $f): ?>
  <div class="flash flash--<?= htmlspecialchars($f['tipo']) ?>">
    <span class="flash__icon">
      <?= $f['tipo'] === 'success' ? '✓' : ($f['tipo'] === 'error' ? '✕' : '!') ?>
    </span>
    <span class="flash__text"><?= $f['mensaje'] ?></span>
    <button class="flash__close" onclick="this.closest('.flash').remove()" aria-label="Cerrar">✕</button>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="auth-page">

  <!-- ── Panel Visual (izquierda) ──────────────────────────── -->
  <div class="auth-page__visual" aria-hidden="true">
    <div class="auth-page__visual-content">
      <div style="margin:0 auto 32px;width:56px;height:56px;color:var(--gold);opacity:0.85;">
        <svg viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
          <circle cx="28" cy="28" r="26" stroke="currentColor" stroke-width="1"/>
          <circle cx="28" cy="28" r="16" stroke="currentColor" stroke-width="1"/>
          <circle cx="28" cy="28" r="6"  fill="currentColor" opacity="0.6"/>
          <line x1="28" y1="2"  x2="28" y2="12" stroke="currentColor" stroke-width="1"/>
          <line x1="28" y1="44" x2="28" y2="54" stroke="currentColor" stroke-width="1"/>
          <line x1="2"  y1="28" x2="12" y2="28" stroke="currentColor" stroke-width="1"/>
          <line x1="44" y1="28" x2="54" y2="28" stroke="currentColor" stroke-width="1"/>
          <line x1="9"  y1="9"  x2="16" y2="16" stroke="currentColor" stroke-width="1"/>
          <line x1="40" y1="40" x2="47" y2="47" stroke="currentColor" stroke-width="1"/>
          <line x1="47" y1="9"  x2="40" y2="16" stroke="currentColor" stroke-width="1"/>
          <line x1="9"  y1="47" x2="16" y2="40" stroke="currentColor" stroke-width="1"/>
        </svg>
      </div>
      <p class="auth-page__visual-logo">PAKAL</p>
      <p class="auth-page__visual-tag">La Distinción del Tiempo</p>
      <p class="auth-page__visual-quote">
        "El tiempo maya no era lineal,<br>era eterno. Como la elegancia verdadera."
      </p>
      <div style="margin-top:56px;display:flex;align-items:center;justify-content:center;gap:6px;opacity:0.2;">
        <div style="width:4px;height:24px;background:var(--gold);"></div>
        <div style="width:4px;height:16px;background:var(--gold);"></div>
        <div style="width:4px;height:8px; background:var(--gold);"></div>
        <div style="width:4px;height:4px; background:var(--gold);"></div>
        <div style="width:4px;height:4px; background:var(--gold);"></div>
        <div style="width:4px;height:8px; background:var(--gold);"></div>
        <div style="width:4px;height:16px;background:var(--gold);"></div>
        <div style="width:4px;height:24px;background:var(--gold);"></div>
      </div>
    </div>
  </div>

  <!-- ── Panel de Formulario (derecha) ─────────────────────── -->
  <div class="auth-page__form-area">

    <a href="index.php" class="auth-back">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      Volver a la tienda
    </a>

    <!-- Tabs -->
    <div class="auth-tabs" role="tablist">
      <button class="auth-tab <?= $tab === 'login'    ? 'active' : '' ?>"
              role="tab"
              aria-selected="<?= $tab === 'login' ? 'true' : 'false' ?>"
              id="tab-login"
              aria-controls="panel-login"
              onclick="switchTab('login')">
        Iniciar sesión
      </button>
      <button class="auth-tab <?= $tab === 'registro' ? 'active' : '' ?>"
              role="tab"
              aria-selected="<?= $tab === 'registro' ? 'true' : 'false' ?>"
              id="tab-registro"
              aria-controls="panel-registro"
              onclick="switchTab('registro')">
        Crear cuenta
      </button>
    </div>

    <!-- ════════════════════════════════════════════════════
         PANEL LOGIN
    ════════════════════════════════════════════════════════ -->
    <div id="panel-login"
         role="tabpanel"
         aria-labelledby="tab-login"
         <?= $tab !== 'login' ? 'class="hidden"' : '' ?>>

      <form class="auth-form"
            id="formLogin"
            action="proceso/login.php"
            method="POST"
            novalidate>

        <!-- CSRF oculto -->
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <div>
          <h1 class="auth-form__title">Bienvenido</h1>
          <p class="auth-form__subtitle">Ingresa a tu cuenta PAKAL para continuar con tu experiencia de moda premium.</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-email">Correo electrónico</label>
          <input type="email"
                 id="login-email"
                 name="email"
                 class="form-input"
                 placeholder="tu@correo.com"
                 value="<?= $emailPrefill ?>"
                 autocomplete="email"
                 required>
          <span class="field-error" id="err-login-email" role="alert"></span>
        </div>

        <div class="form-group">
          <label class="form-label" for="login-pass">Contraseña</label>
          <div class="field-password">
            <input type="password"
                   id="login-pass"
                   name="password"
                   class="form-input"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   required>
            <button type="button"
                    class="toggle-pass"
                    onclick="togglePassword('login-pass', this)"
                    aria-label="Mostrar contraseña">
              Ver
            </button>
          </div>
          <span class="field-error" id="err-login-pass" role="alert"></span>
        </div>

        <span class="auth-form__forgot" style="cursor:default;color:var(--gray-light);">¿Olvidaste tu contraseña? Contacta a soporte.</span>

        <button type="submit" class="auth-form__submit" id="btn-login">
          Ingresar
        </button>

        <div class="auth-form__divider">o</div>

        <p style="font-size:0.8rem;text-align:center;color:var(--gray-mid);">
          ¿No tienes cuenta?
          <a href="#" style="color:var(--black);font-weight:500;text-decoration:underline;"
             onclick="switchTab('registro');return false;">
            Regístrate aquí
          </a>
        </p>

      </form>
    </div>

    <!-- ════════════════════════════════════════════════════
         PANEL REGISTRO
    ════════════════════════════════════════════════════════ -->
    <div id="panel-registro"
         role="tabpanel"
         aria-labelledby="tab-registro"
         <?= $tab !== 'registro' ? 'class="hidden"' : '' ?>>

      <form class="auth-form"
            id="formRegistro"
            action="proceso/registro.php"
            method="POST"
            novalidate>

        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <div>
          <h1 class="auth-form__title">Únete a PAKAL</h1>
          <p class="auth-form__subtitle">Crea tu cuenta y descubre la moda premium inspirada en la cultura maya.</p>
        </div>

        <!-- Nombre y Apellido -->
        <div class="form-group form-group--half">
          <div class="form-group">
            <label class="form-label" for="reg-nombre">Nombre <span style="color:#C44A4A;">*</span></label>
            <input type="text"
                   id="reg-nombre"
                   name="nombre"
                   class="form-input"
                   placeholder="Tu nombre"
                   value="<?= $regPrefill['nombre'] ?? '' ?>"
                   autocomplete="given-name"
                   minlength="2"
                   required>
            <span class="field-error" id="err-nombre" role="alert"></span>
          </div>
          <div class="form-group">
            <label class="form-label" for="reg-apellido">Apellido <span style="color:#C44A4A;">*</span></label>
            <input type="text"
                   id="reg-apellido"
                   name="apellido"
                   class="form-input"
                   placeholder="Tu apellido"
                   value="<?= $regPrefill['apellido'] ?? '' ?>"
                   autocomplete="family-name"
                   minlength="2"
                   required>
            <span class="field-error" id="err-apellido" role="alert"></span>
          </div>
        </div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-label" for="reg-email">Correo electrónico <span style="color:#C44A4A;">*</span></label>
          <input type="email"
                 id="reg-email"
                 name="email"
                 class="form-input"
                 placeholder="tu@correo.com"
                 value="<?= $regPrefill['email'] ?? '' ?>"
                 autocomplete="email"
                 required>
          <span class="field-error" id="err-reg-email" role="alert"></span>
        </div>

        <!-- Teléfono -->
        <div class="form-group">
          <label class="form-label" for="reg-telefono">
            Teléfono
            <span style="color:var(--gray-light);font-weight:400;font-size:0.6rem;">(opcional)</span>
          </label>
          <input type="tel"
                 id="reg-telefono"
                 name="telefono"
                 class="form-input"
                 placeholder="+52 55 0000 0000"
                 value="<?= $regPrefill['telefono'] ?? '' ?>"
                 autocomplete="tel">
        </div>

        <!-- Contraseña -->
        <div class="form-group">
          <label class="form-label" for="reg-pass">Contraseña <span style="color:#C44A4A;">*</span></label>
          <div class="field-password">
            <input type="password"
                   id="reg-pass"
                   name="password"
                   class="form-input"
                   placeholder="Mínimo 8 caracteres"
                   autocomplete="new-password"
                   minlength="8"
                   required
                   oninput="evaluarContrasena(this.value)">
            <button type="button"
                    class="toggle-pass"
                    onclick="togglePassword('reg-pass', this)"
                    aria-label="Mostrar contraseña">
              Ver
            </button>
          </div>
          <!-- Barra de fortaleza -->
          <div class="password-strength" id="strengthWrap" style="display:none;">
            <div class="strength-bar">
              <div class="strength-fill" id="strengthFill"></div>
            </div>
            <span class="strength-label" id="strengthLabel">Contraseña débil</span>
          </div>
          <span class="field-error" id="err-pass" role="alert"></span>
        </div>

        <!-- Confirmar contraseña -->
        <div class="form-group">
          <label class="form-label" for="reg-pass2">Confirmar contraseña <span style="color:#C44A4A;">*</span></label>
          <div class="field-password">
            <input type="password"
                   id="reg-pass2"
                   name="password2"
                   class="form-input"
                   placeholder="Repite tu contraseña"
                   autocomplete="new-password"
                   required>
            <button type="button"
                    class="toggle-pass"
                    onclick="togglePassword('reg-pass2', this)"
                    aria-label="Mostrar contraseña">
              Ver
            </button>
          </div>
          <span class="field-error" id="err-pass2" role="alert"></span>
        </div>

        <!-- Newsletter -->
        <div style="display:flex;align-items:flex-start;gap:10px;">
          <input type="checkbox"
                 id="reg-news"
                 name="newsletter"
                 value="1"
                 style="width:14px;height:14px;margin-top:3px;flex-shrink:0;">
          <label for="reg-news" style="font-size:0.78rem;color:var(--gray-mid);cursor:pointer;line-height:1.6;">
            Quiero recibir novedades, lanzamientos exclusivos y ofertas especiales por correo.
          </label>
        </div>

        <!-- Términos -->
        <p class="auth-form__terms">
          Al crear una cuenta, aceptas nuestros
          <a href="#">Términos y Condiciones</a> y nuestra
          <a href="#">Política de Privacidad</a>.
        </p>

        <button type="submit" class="auth-form__submit" id="btn-registro">
          Crear mi cuenta
        </button>

        <p style="font-size:0.8rem;text-align:center;color:var(--gray-mid);">
          ¿Ya tienes cuenta?
          <a href="#" style="color:var(--black);font-weight:500;text-decoration:underline;"
             onclick="switchTab('login');return false;">
            Ingresar aquí
          </a>
        </p>

      </form>
    </div><!-- /#panel-registro -->

  </div><!-- /.auth-page__form-area -->
</div><!-- /.auth-page -->

<script>
/* ── Tab switching ─────────────────────────────────────────── */
function switchTab(tab) {
  ['login', 'registro'].forEach(t => {
    const panel = document.getElementById('panel-' + t);
    const btn   = document.getElementById('tab-' + t);
    const isActive = t === tab;
    panel.classList.toggle('hidden', !isActive);
    btn.classList.toggle('active', isActive);
    btn.setAttribute('aria-selected', isActive);
  });
  // Actualizar URL sin recargar
  const url = new URL(window.location);
  url.searchParams.set('tab', tab);
  history.replaceState(null, '', url);
}

/* ── Mostrar/Ocultar contraseña ─────────────────────────────── */
function togglePassword(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.textContent = isText ? 'Ver' : 'Ocultar';
}

/* ── Fortaleza de contraseña ─────────────────────────────────── */
function evaluarContrasena(val) {
  const wrap  = document.getElementById('strengthWrap');
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');

  if (!val.length) { wrap.style.display = 'none'; return; }
  wrap.style.display = 'block';

  let score = 0;
  if (val.length >= 8)                    score++;
  if (val.length >= 12)                   score++;
  if (/[A-Z]/.test(val))                  score++;
  if (/[0-9]/.test(val))                  score++;
  if (/[^A-Za-z0-9]/.test(val))          score++;

  const configs = [
    { w: '20%',  bg: '#C44A4A', txt: 'Muy débil'  },
    { w: '40%',  bg: '#E65100', txt: 'Débil'      },
    { w: '60%',  bg: var_gold(), txt: 'Regular'   },
    { w: '80%',  bg: '#558B2F', txt: 'Buena'      },
    { w: '100%', bg: '#2E7D32', txt: 'Excelente'  },
  ];
  const c = configs[Math.min(score, 4)];
  fill.style.width      = c.w;
  fill.style.background = c.bg;
  label.textContent     = c.txt;
  label.style.color     = c.bg;
}
function var_gold() {
  return getComputedStyle(document.documentElement).getPropertyValue('--gold').trim() || '#C4A96A';
}

/* ── Validación del formulario de LOGIN ─────────────────────── */
document.getElementById('formLogin').addEventListener('submit', function(e) {
  let valid = true;
  const email = document.getElementById('login-email');
  const pass  = document.getElementById('login-pass');

  clearError('err-login-email');
  clearError('err-login-pass');

  if (!email.value.trim()) {
    setError('err-login-email', email, 'El correo es obligatorio.');
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    setError('err-login-email', email, 'Formato de correo inválido.');
    valid = false;
  }
  if (!pass.value) {
    setError('err-login-pass', pass, 'La contraseña es obligatoria.');
    valid = false;
  }

  if (!valid) {
    e.preventDefault();
    return;
  }

  // Estado de carga
  const btn = document.getElementById('btn-login');
  btn.textContent = 'Verificando...';
  btn.disabled = true;
});

/* ── Validación del formulario de REGISTRO ───────────────────── */
document.getElementById('formRegistro').addEventListener('submit', function(e) {
  let valid = true;
  const campos = [
    { id: 'reg-nombre',   err: 'err-nombre',    msg: 'El nombre es obligatorio (mín. 2 caracteres).', min: 2 },
    { id: 'reg-apellido', err: 'err-apellido',  msg: 'El apellido es obligatorio (mín. 2 caracteres).', min: 2 },
  ];

  // Limpiar todos los errores
  ['err-nombre','err-apellido','err-reg-email','err-pass','err-pass2'].forEach(clearError);

  // Nombre y apellido
  campos.forEach(c => {
    const el = document.getElementById(c.id);
    if (!el.value.trim() || el.value.trim().length < c.min) {
      setError(c.err, el, c.msg);
      valid = false;
    }
  });

  // Email
  const email = document.getElementById('reg-email');
  if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    setError('err-reg-email', email, 'Ingresa un correo electrónico válido.');
    valid = false;
  }

  // Contraseña
  const pass  = document.getElementById('reg-pass');
  const pass2 = document.getElementById('reg-pass2');

  if (pass.value.length < 8) {
    setError('err-pass', pass, 'Mínimo 8 caracteres.');
    valid = false;
  } else if (!/[A-Za-z]/.test(pass.value) || !/[0-9]/.test(pass.value)) {
    setError('err-pass', pass, 'Debe contener letras y números.');
    valid = false;
  }

  if (pass.value !== pass2.value) {
    setError('err-pass2', pass2, 'Las contraseñas no coinciden.');
    valid = false;
  }

  if (!valid) {
    e.preventDefault();
    // Scroll al primer error
    const firstErr = this.querySelector('.form-input.invalid');
    if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  const btn = document.getElementById('btn-registro');
  btn.textContent = 'Creando cuenta...';
  btn.disabled = true;
});

/* ── Helpers de validación ────────────────────────────────────── */
function setError(errId, input, msg) {
  const el = document.getElementById(errId);
  if (el) el.textContent = msg;
  if (input) input.classList.add('invalid');
}
function clearError(errId) {
  const el = document.getElementById(errId);
  if (el) el.textContent = '';
  // Limpiar clase del input correspondiente
  const inputId = errId.replace('err-', '').replace('login-', 'login-').replace('reg-', 'reg-');
  const inputEl = document.querySelector(`[id*="${inputId}"]`);
  if (inputEl) inputEl.classList.remove('invalid', 'valid');
}

/* ── Auto-cerrar flash después de 6s ─────────────────────────── */
document.querySelectorAll('.flash').forEach(f => {
  setTimeout(() => {
    f.style.transition = 'opacity 0.4s';
    f.style.opacity = '0';
    setTimeout(() => f.remove(), 400);
  }, 6000);
});
</script>

</body>
</html>
