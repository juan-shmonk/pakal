<?php
// ── Configuración y Helpers de Sesión ───────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 60 * 60 * 24 * 7, // 7 días
        'path'     => '/',
        'secure'   => false,             // true en producción con HTTPS
        'httponly' => true,              // JS no puede leer la cookie
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ── Autenticación ────────────────────────────────────────────

function estaLogueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function esAdmin(): bool
{
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

/** Devuelve array con datos del usuario actual o null. */
function usuarioActual(): ?array
{
    if (!estaLogueado()) return null;
    return [
        'id'       => $_SESSION['usuario_id'],
        'nombre'   => $_SESSION['usuario_nombre'],
        'apellido' => $_SESSION['usuario_apellido'],
        'email'    => $_SESSION['usuario_email'],
        'rol'      => $_SESSION['usuario_rol'],
    ];
}

/** Redirige a login si el usuario no está autenticado. */
function requiereLogin(string $redirect = 'auth.php'): void
{
    if (!estaLogueado()) {
        header("Location: $redirect");
        exit;
    }
}

/** Redirige al inicio si el usuario no es administrador. */
function requiereAdmin(string $redirect = '../index.php'): void
{
    if (!esAdmin()) {
        header("Location: $redirect");
        exit;
    }
}

// ── CSRF ─────────────────────────────────────────────────────

/** Genera y guarda un token CSRF único por sesión. */
function generarCSRF(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Valida el token CSRF enviado con el formulario. */
function validarCSRF(string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Rota el token CSRF (úsalo después de cada acción exitosa). */
function rotarCSRF(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── Flash Messages ───────────────────────────────────────────

/**
 * Guarda un mensaje de un solo uso en sesión.
 * $tipo: 'success' | 'error' | 'warning' | 'info'
 */
function flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Devuelve y elimina todos los mensajes flash.
 * @return array<array{tipo: string, mensaje: string}>
 */
function obtenerFlash(): array
{
    $mensajes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $mensajes;
}

// ── Login / Logout helpers ───────────────────────────────────

/** Llena la sesión con los datos del usuario autenticado. */
function iniciarSesionUsuario(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id']       = (int) $usuario['id'];
    $_SESSION['usuario_nombre']   = $usuario['nombre'];
    $_SESSION['usuario_apellido'] = $usuario['apellido'];
    $_SESSION['usuario_email']    = $usuario['email'];
    $_SESSION['usuario_rol']      = $usuario['rol'];
}

/** Destruye la sesión completamente. */
function cerrarSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(
            session_name(), '',
            time() - 42000,
            $p['path'], $p['domain'],
            $p['secure'], $p['httponly']
        );
    }

    session_destroy();
}
