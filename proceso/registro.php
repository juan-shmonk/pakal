<?php
// ── Proceso: Registro de Usuario ────────────────────────────
require_once '../config/session.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth.php');
    exit;
}

// ── Validar CSRF ─────────────────────────────────────────────
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
    header('Location: ../auth.php?tab=registro');
    exit;
}

// ── Recoger y limpiar datos ───────────────────────────────────
$nombre     = trim($_POST['nombre']     ?? '');
$apellido   = trim($_POST['apellido']   ?? '');
$email      = strtolower(trim($_POST['email']   ?? ''));
$telefono   = trim($_POST['telefono']   ?? '');
$password   = $_POST['password']  ?? '';
$password2  = $_POST['password2'] ?? '';
$newsletter = isset($_POST['newsletter']) ? 1 : 0;

// ── Validaciones ─────────────────────────────────────────────
$errores = [];

if (empty($nombre) || strlen($nombre) < 2) {
    $errores[] = 'El nombre debe tener al menos 2 caracteres.';
}
if (empty($apellido) || strlen($apellido) < 2) {
    $errores[] = 'El apellido debe tener al menos 2 caracteres.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Ingresa un correo electrónico válido.';
}
if (strlen($password) < 8) {
    $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
}
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errores[] = 'La contraseña debe contener letras y números.';
}
if ($password !== $password2) {
    $errores[] = 'Las contraseñas no coinciden.';
}
if (!empty($telefono) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $telefono)) {
    $errores[] = 'El número de teléfono no es válido.';
}

if (!empty($errores)) {
    flash('error', implode(' ', $errores));
    // Conservar datos válidos para el formulario
    $_SESSION['reg_prefill'] = [
        'nombre'   => htmlspecialchars($nombre),
        'apellido' => htmlspecialchars($apellido),
        'email'    => htmlspecialchars($email),
        'telefono' => htmlspecialchars($telefono),
    ];
    header('Location: ../auth.php?tab=registro');
    exit;
}

// ── Insertar en base de datos ────────────────────────────────
try {
    $pdo = getPDO();

    // Verificar email duplicado
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        flash('error', 'Este correo electrónico ya está registrado. ¿Quieres <a href="auth.php?tab=login">iniciar sesión</a>?');
        header('Location: ../auth.php?tab=registro');
        exit;
    }

    // Hashear contraseña con bcrypt (costo 12)
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nombre, apellido, email, telefono, password_hash, newsletter)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $insert->execute([
        $nombre,
        $apellido,
        $email,
        $telefono ?: null,
        $hash,
        $newsletter,
    ]);

    $nuevoId = (int) $pdo->lastInsertId();

    // ── Auto-login tras registro ─────────────────────────────
    iniciarSesionUsuario([
        'id'       => $nuevoId,
        'nombre'   => $nombre,
        'apellido' => $apellido,
        'email'    => $email,
        'rol'      => 'cliente',
    ]);
    rotarCSRF();

    // Limpiar prefill si existía
    unset($_SESSION['reg_prefill']);

    flash('success', '¡Bienvenido a PAKAL, ' . htmlspecialchars($nombre) . '! Tu cuenta ha sido creada con éxito.');
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    flash('error', 'Error al crear la cuenta. Por favor intenta más tarde.');
    header('Location: ../auth.php?tab=registro');
    exit;
}
