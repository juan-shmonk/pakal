<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/wishlist.php                              ║
// ║  PROPÓSITO: Agregar o quitar un producto de favoritos        ║
// ║                                                              ║
// ║  Funciona como un "toggle" (interruptor):                    ║
// ║  - Si el producto YA está en favoritos → lo quita           ║
// ║  - Si el producto NO está en favoritos → lo agrega          ║
// ║                                                              ║
// ║  Se llama desde el botón de corazón (♡) en las tarjetas de  ║
// ║  producto de cualquier página (inicio, catálogo, detalle).   ║
// ╚══════════════════════════════════════════════════════════════╝

require_once '../config/session.php';
require_once '../config/database.php';

// ── Determinar a dónde regresar después ─────────────────────────
// El formulario envía una URL de "redirect" para volver a la misma página
// desde donde se hizo clic. Si no viene, usamos la URL anterior o el catálogo.
$redirect = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '../productos.php';

// Por seguridad, solo redirigimos a rutas del propio proyecto.
// Si alguien manipulara el campo "redirect" para apuntar a otro sitio,
// lo ignoramos y lo mandamos al catálogo.
if (!str_starts_with($redirect, '/proyecto/')) {
    $redirect = '/proyecto/productos.php';
}

// ── Solo para usuarios logueados ─────────────────────────────────
// La lista de favoritos es personal, necesita cuenta de usuario.
if (!estaLogueado()) {
    flash('info', 'Inicia sesión para guardar productos en tus favoritos.');
    header('Location: ../auth.php');
    exit;
}

// Solo aceptar solicitudes POST (el botón de corazón está dentro de un formulario).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

// Verificación de seguridad CSRF.
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: ' . $redirect);
    exit;
}

// ── Obtener datos del formulario ─────────────────────────────────
// (int) convierte el valor a número entero (descarta texto malicioso).
$producto_id = (int)($_POST['producto_id'] ?? 0);
$usuario_id  = (int)$_SESSION['usuario_id'];

// Si no llegó un ID de producto válido, no hacemos nada.
if ($producto_id <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$pdo = getPDO();

// ── Verificar si el producto ya está en favoritos ────────────────
// Buscamos si existe una fila con esta combinación usuario + producto.
$stmt = $pdo->prepare('SELECT 1 FROM wishlist WHERE usuario_id = ? AND producto_id = ?');
$stmt->execute([$usuario_id, $producto_id]);

if ($stmt->fetch()) {
    // ── El producto SÍ está en favoritos: lo quitamos ────────────
    // Borramos esa fila de la tabla wishlist.
    $pdo->prepare('DELETE FROM wishlist WHERE usuario_id = ? AND producto_id = ?')
        ->execute([$usuario_id, $producto_id]);
} else {
    // ── El producto NO está en favoritos: lo agregamos ────────────
    try {
        $pdo->prepare('INSERT INTO wishlist (usuario_id, producto_id) VALUES (?, ?)')
            ->execute([$usuario_id, $producto_id]);
    } catch (PDOException $e) {
        // Si hay error de duplicado (el usuario hizo doble clic muy rápido),
        // simplemente lo ignoramos. La lógica de toggle ya manejó el caso.
    }
}

// Renovar el token CSRF para la próxima acción.
rotarCSRF();

// Regresar a la página desde donde se hizo clic.
header('Location: ' . $redirect);
exit;
