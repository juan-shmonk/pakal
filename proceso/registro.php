<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/registro.php                              ║
// ║  PROPÓSITO: Crear una cuenta nueva de usuario               ║
// ║                                                              ║
// ║  Recibe los datos del formulario de registro (de auth.php),  ║
// ║  los valida, y los guarda en la base de datos.              ║
// ║  Si todo es correcto, inicia sesión automáticamente.         ║
// ║                                                              ║
// ║  Flujo:                                                      ║
// ║  1. El usuario llena el formulario de "Crear cuenta"         ║
// ║  2. El formulario envía los datos AQUÍ (método POST)         ║
// ║  3. Validamos todos los campos                               ║
// ║  4. Verificamos que el email no esté ya registrado           ║
// ║  5. Guardamos al usuario en la base de datos                 ║
// ║  6. Iniciamos sesión automáticamente y redirigimos al inicio ║
// ╚══════════════════════════════════════════════════════════════╝

require_once '../config/session.php';
require_once '../config/database.php';

// Solo aceptamos solicitudes POST (no acceso directo en el navegador).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth.php');
    exit;
}

// ── Verificar token CSRF ─────────────────────────────────────────
// Protección contra formularios enviados desde otros sitios.
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
    header('Location: ../auth.php?tab=registro');
    exit;
}

// ── Recoger y limpiar los datos del formulario ───────────────────
// trim() elimina espacios extra al inicio/final de cada campo.
// strtolower() convierte el email a minúsculas para uniformidad.
$nombre     = trim($_POST['nombre']     ?? '');
$apellido   = trim($_POST['apellido']   ?? '');
$email      = strtolower(trim($_POST['email']   ?? ''));
$telefono   = trim($_POST['telefono']   ?? '');
$password   = $_POST['password']  ?? '';   // No hacemos trim: los espacios son parte de la contraseña
$password2  = $_POST['password2'] ?? '';   // Confirmación de contraseña
$newsletter = isset($_POST['newsletter']) ? 1 : 0;  // 1 si marcó la casilla, 0 si no

// ── Validaciones de cada campo ───────────────────────────────────
// Acumulamos todos los errores en un array para mostrarlos juntos.
$errores = [];

// El nombre debe tener al menos 2 caracteres (no aceptamos "J").
if (empty($nombre) || strlen($nombre) < 2) {
    $errores[] = 'El nombre debe tener al menos 2 caracteres.';
}

// El apellido también mínimo 2 caracteres.
if (empty($apellido) || strlen($apellido) < 2) {
    $errores[] = 'El apellido debe tener al menos 2 caracteres.';
}

// El email debe tener formato válido (texto@texto.algo).
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Ingresa un correo electrónico válido.';
}

// La contraseña debe tener al menos 8 caracteres.
if (strlen($password) < 8) {
    $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
}

// La contraseña debe mezclar letras y números (más segura).
// preg_match busca si hay al menos una letra ([A-Za-z]) y un número ([0-9]).
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errores[] = 'La contraseña debe contener letras y números.';
}

// Las dos contraseñas escritas deben ser exactamente iguales.
if ($password !== $password2) {
    $errores[] = 'Las contraseñas no coinciden.';
}

// Si se escribió teléfono, debe tener un formato razonable (7-20 dígitos con guiones, paréntesis, etc.).
if (!empty($telefono) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $telefono)) {
    $errores[] = 'El número de teléfono no es válido.';
}

// ── Si hay errores, regresar al formulario ───────────────────────
// Si alguna validación falló, mostramos todos los errores y volvemos
// al formulario de registro. También guardamos los datos que SÍ
// estaban bien para que el usuario no tenga que escribirlos de nuevo.
if (!empty($errores)) {
    flash('error', implode(' ', $errores));  // Une todos los mensajes con un espacio

    // Guardamos los datos válidos en sesión para pre-rellenar el formulario.
    // htmlspecialchars evita que caracteres especiales rompan el HTML.
    $_SESSION['reg_prefill'] = [
        'nombre'   => htmlspecialchars($nombre),
        'apellido' => htmlspecialchars($apellido),
        'email'    => htmlspecialchars($email),
        'telefono' => htmlspecialchars($telefono),
    ];
    header('Location: ../auth.php?tab=registro');
    exit;
}

// ── Guardar el nuevo usuario en la base de datos ────────────────
try {
    $pdo = getPDO();

    // ── Verificar que el email no esté ya registrado ─────────────
    // Dos usuarios no pueden tener el mismo email (es el identificador único).
    $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
    $check->execute([$email]);
    if ($check->fetch()) {
        // El email ya existe: sugerimos iniciar sesión en su lugar.
        flash('error', 'Este correo electrónico ya está registrado. ¿Quieres <a href="auth.php?tab=login">iniciar sesión</a>?');
        header('Location: ../auth.php?tab=registro');
        exit;
    }

    // ── Cifrar (hashear) la contraseña ───────────────────────────
    // NUNCA guardamos contraseñas en texto plano en la base de datos.
    // password_hash usa el algoritmo bcrypt con "costo" 12.
    // "Costo 12" significa que el proceso es deliberadamente lento:
    // cada intento de adivinar la contraseña tarda ~0.3 segundos.
    // Eso hace que un atacante necesite años para probar millones de contraseñas.
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

    // ── Insertar el nuevo usuario en la tabla "usuarios" ─────────
    $insert = $pdo->prepare(
        'INSERT INTO usuarios (nombre, apellido, email, telefono, password_hash, newsletter)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $insert->execute([
        $nombre,
        $apellido,
        $email,
        $telefono ?: null,  // Si el teléfono está vacío, guardamos NULL (no un string vacío)
        $hash,              // La contraseña cifrada, no la original
        $newsletter,        // 1 o 0 según si marcó la casilla
    ]);

    // lastInsertId() devuelve el ID que MySQL asignó automáticamente al nuevo usuario.
    $nuevoId = (int) $pdo->lastInsertId();

    // ── Auto-login después del registro ──────────────────────────
    // Iniciamos sesión automáticamente para que el usuario no tenga
    // que volver a escribir email y contraseña inmediatamente después de registrarse.
    iniciarSesionUsuario([
        'id'       => $nuevoId,
        'nombre'   => $nombre,
        'apellido' => $apellido,
        'email'    => $email,
        'rol'      => 'cliente',  // Los usuarios nuevos siempre son clientes, nunca admins
    ]);
    rotarCSRF();  // Renovar el token de seguridad

    // Limpiamos el prefill de sesión (ya no se necesita).
    unset($_SESSION['reg_prefill']);

    // Mensaje de bienvenida que se verá en la página de inicio.
    flash('success', '¡Bienvenido a PAKAL, ' . htmlspecialchars($nombre) . '! Tu cuenta ha sido creada con éxito.');
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    // Si algo sale mal en la base de datos, mostramos mensaje genérico.
    flash('error', 'Error al crear la cuenta. Por favor intenta más tarde.');
    header('Location: ../auth.php?tab=registro');
    exit;
}
