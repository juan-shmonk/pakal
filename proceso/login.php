<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/login.php                                 ║
// ║  PROPÓSITO: Procesar el formulario de inicio de sesión       ║
// ║                                                              ║
// ║  Este archivo NO muestra nada en pantalla. Solo recibe los   ║
// ║  datos del formulario de login (de auth.php), los verifica   ║
// ║  contra la base de datos, y redirige al usuario.             ║
// ║                                                              ║
// ║  Flujo:                                                      ║
// ║  1. El usuario llena email + contraseña en auth.php          ║
// ║  2. El formulario envía esos datos AQUÍ (método POST)        ║
// ║  3. Este script los valida y verifica contra la base de datos ║
// ║  4. Si todo está bien: inicia sesión y manda al inicio       ║
// ║  5. Si algo falla: manda de vuelta a auth.php con un mensaje ║
// ╚══════════════════════════════════════════════════════════════╝

// Cargamos las configuraciones necesarias: sesión y base de datos.
require_once '../config/session.php';
require_once '../config/database.php';

// ── Paso 1: Solo aceptar solicitudes POST ────────────────────────
// Un formulario de login SIEMPRE debe enviarse por POST (no por GET).
// GET pone los datos en la URL (visible), POST los envía de forma oculta.
// Si alguien intenta abrir este archivo directamente en el navegador,
// lo mandamos a auth.php sin hacer nada.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../auth.php');
    exit;
}

// ── Paso 2: Validar el token CSRF ────────────────────────────────
// Comprobamos que el formulario vino del propio sitio y no de otro lugar.
// (Explicación de CSRF: ver config/session.php)
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido. Por favor intenta de nuevo.');
    header('Location: ../auth.php?tab=login');
    exit;
}

// ── Paso 3: Recoger y limpiar los datos del formulario ───────────
// strtolower convierte el email a minúsculas (juan@EJEMPLO.com → juan@ejemplo.com)
// trim elimina espacios al inicio y al final que el usuario pudo teclear accidentalmente
$email    = strtolower(trim($_POST['email']    ?? ''));
$password =            trim($_POST['password'] ?? '');
$redirigir = '../index.php'; // A dónde irá el usuario después de loguearse

// ── Paso 4: Validaciones básicas del formulario ──────────────────
// Verificamos que los campos no vengan vacíos.
if (empty($email) || empty($password)) {
    flash('error', 'Por favor completa todos los campos.');
    header('Location: ../auth.php?tab=login');
    exit;
}

// Verificamos que el email tenga un formato válido (texto@texto.texto).
// filter_var con FILTER_VALIDATE_EMAIL usa las reglas estándar de internet.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'El formato del correo electrónico no es válido.');
    header('Location: ../auth.php?tab=login');
    exit;
}

// ── Paso 5: Buscar al usuario en la base de datos ───────────────
try {
    $pdo  = getPDO();

    // Preparamos una consulta SQL segura (con "?" en lugar del email directamente).
    // Esto evita inyección SQL: si alguien escribe código SQL como email,
    // la consulta preparada lo trata como texto, no como código.
    $stmt = $pdo->prepare(
        'SELECT id, nombre, apellido, email, password_hash, rol, activo
         FROM usuarios
         WHERE email = ?
         LIMIT 1'  // Solo necesitamos un resultado (los emails son únicos)
    );
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();  // fetch() devuelve una fila o false si no encontró nada

    // ── Paso 6: Verificar contraseña ─────────────────────────────
    // password_verify compara la contraseña que escribió el usuario
    // con el "hash" guardado en la base de datos.
    //
    // Un "hash" es una versión cifrada de la contraseña: el sistema
    // NUNCA guarda contraseñas en texto plano. Si alguien roba la BD,
    // solo verá algo como: $2y$12$Abc123... (incomprensible).
    //
    // Si el usuario no existe ($usuario es false) O la contraseña no coincide:
    // mandamos el mismo mensaje genérico para no revelar si el email existe.
    if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
        flash('error', 'Correo electrónico o contraseña incorrectos.');
        // Guardamos el email en sesión para pre-rellenar el campo en el formulario.
        // Así el usuario no tiene que escribirlo de nuevo.
        $_SESSION['auth_email_prefill'] = htmlspecialchars($email);
        header('Location: ../auth.php?tab=login');
        exit;
    }

    // ── Paso 7: Verificar que la cuenta está activa ──────────────
    // Un administrador puede desactivar cuentas (activo = 0).
    // (bool) convierte 0/1 de MySQL a false/true de PHP.
    if (!(bool) $usuario['activo']) {
        flash('error', 'Tu cuenta ha sido suspendida. Contacta a soporte.');
        header('Location: ../auth.php?tab=login');
        exit;
    }

    // ── Paso 8: Login exitoso ─────────────────────────────────────
    // Si llegamos aquí, el email existe, la contraseña es correcta y la cuenta está activa.

    // Guardamos los datos del usuario en la sesión del servidor.
    iniciarSesionUsuario($usuario);

    // Cambiamos el token CSRF para que el anterior no pueda reutilizarse.
    rotarCSRF();

    // Actualizamos la fecha de último acceso en la base de datos.
    $pdo->prepare('UPDATE usuarios SET updated_at = NOW() WHERE id = ?')
        ->execute([$usuario['id']]);

    // Mostramos mensaje de bienvenida (se verá en la próxima página).
    flash('success', '¡Bienvenido de nuevo, ' . htmlspecialchars($usuario['nombre']) . '!');

    // ── Paso 9: Redirigir según el rol del usuario ────────────────
    // Los administradores van al panel de administración.
    // Los clientes normales van a la página de inicio.
    if ($usuario['rol'] === 'admin') {
        header('Location: ../admin.php');
    } else {
        header('Location: ../index.php');
    }
    exit;

} catch (PDOException $e) {
    // Si hay un error en la base de datos (ej: MySQL caído), mostramos
    // un mensaje genérico sin revelar detalles técnicos.
    // En producción: error_log($e->getMessage()); — guardar en archivo de log.
    flash('error', 'Error del servidor. Por favor intenta más tarde.');
    header('Location: ../auth.php?tab=login');
    exit;
}
