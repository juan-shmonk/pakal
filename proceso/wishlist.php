<?php
// ── Proceso: Toggle Wishlist ─────────────────────────────────
require_once '../config/session.php';
require_once '../config/database.php';

$redirect = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? '../productos.php';

// Solo redirigir a rutas propias del proyecto
if (!str_starts_with($redirect, '/proyecto/')) {
    $redirect = '/proyecto/productos.php';
}

if (!estaLogueado()) {
    flash('info', 'Inicia sesión para guardar productos en tus favoritos.');
    header('Location: ../auth.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: ' . $redirect);
    exit;
}

$producto_id = (int)($_POST['producto_id'] ?? 0);
$usuario_id  = (int)$_SESSION['usuario_id'];

if ($producto_id <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$pdo = getPDO();

$stmt = $pdo->prepare('SELECT 1 FROM wishlist WHERE usuario_id = ? AND producto_id = ?');
$stmt->execute([$usuario_id, $producto_id]);

if ($stmt->fetch()) {
    $pdo->prepare('DELETE FROM wishlist WHERE usuario_id = ? AND producto_id = ?')
        ->execute([$usuario_id, $producto_id]);
} else {
    try {
        $pdo->prepare('INSERT INTO wishlist (usuario_id, producto_id) VALUES (?, ?)')
            ->execute([$usuario_id, $producto_id]);
    } catch (PDOException $e) {
        // Ignorar duplicados
    }
}

rotarCSRF();
header('Location: ' . $redirect);
exit;
