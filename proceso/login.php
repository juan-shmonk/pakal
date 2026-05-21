<?php
// ── Proceso: Iniciar Sesión ──────────────────────────────────
require_once '../config/session.php';
require_once '../config/database.php';

// Solo acepta POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth.php');
    exit;
}

// ── Validar CSRF ─────────────────────────────────────────────
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido. Por favor intenta de nuevo.');
    header('Location: ../auth.php?tab=login');
    exit;
}

$email    = strtolower(trim($_POST['email']    ?? ''));
$password =            trim($_POST['password'] ?? '');
$redirigir = '../index.php'; // destino post-login por defecto

// ── Validación básica ────────────────────────────────────────
if (empty($email) || empty($password)) {
    flash('error', 'Por favor completa todos los campos.');
    header('Location: ../auth.php?tab=login');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'El formato del correo electrónico no es válido.');
    header('Location: ../auth.php?tab=login');
    exit;
}

// ── Consulta a la base de datos ──────────────────────────────
try {
    $pdo  = getPDO();
    $stmt = $pdo->prepare(
        'SELECT id, nombre, apellido, email, password_hash, rol, activo
         FROM usuarios
         WHERE email = ?
         LIMIT 1'
    );
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();

    // Verificar existencia y contraseña
    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        flash('error', 'Correo electrónico o contraseña incorrectos.');
        // Guardamos el email para pre-rellenar el campo
        $_SESSION['auth_email_prefill'] = htmlspecialchars($email);
        header('Location: ../auth.php?tab=login');
        exit;
    }

    // Verificar que la cuenta esté activa
    if (!(bool) $usuario['activo']) {
        flash('error', 'Tu cuenta ha sido suspendida. Contacta a soporte.');
        header('Location: ../auth.php?tab=login');
        exit;
    }

    // ── Login exitoso ────────────────────────────────────────
    iniciarSesionUsuario($usuario);
    rotarCSRF();

    // Actualizar fecha de último acceso
    $pdo->prepare('UPDATE usuarios SET updated_at = NOW() WHERE id = ?')
        ->execute([$usuario['id']]);

    flash('success', '¡Bienvenido de nuevo, ' . htmlspecialchars($usuario['nombre']) . '!');

    // Redirigir según rol
    if ($usuario['rol'] === 'admin') {
        header('Location: ../admin.php');
    } else {
        header('Location: ../index.php');
    }
    exit;

} catch (PDOException $e) {
    // Log real en producción: error_log($e->getMessage());
    flash('error', 'Error del servidor. Por favor intenta más tarde.');
    header('Location: ../auth.php?tab=login');
    exit;
}
