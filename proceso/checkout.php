<?php
// ── Proceso: Crear Pedido ────────────────────────────────────
require_once '../config/session.php';
require_once '../config/database.php';

if (!estaLogueado()) {
    header('Location: ../auth.php?tab=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../checkout.php');
    exit;
}

if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
    header('Location: ../checkout.php');
    exit;
}

// ── Recoger datos del formulario ─────────────────────────────
$nombre    = trim($_POST['nombre']    ?? '');
$apellido  = trim($_POST['apellido']  ?? '');
$email     = trim($_POST['email']     ?? '');
$telefono  = trim($_POST['telefono']  ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad    = trim($_POST['ciudad']    ?? '');

if (empty($nombre) || empty($apellido) || empty($email) || empty($direccion) || empty($ciudad)) {
    flash('error', 'Por favor completa todos los campos obligatorios.');
    header('Location: ../checkout.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'El formato del correo electrónico no es válido.');
    header('Location: ../checkout.php');
    exit;
}

$pdo        = getPDO();
$usuario_id = $_SESSION['usuario_id'];

// ── Obtener ítems del carrito ─────────────────────────────────
$stmt = $pdo->prepare(
    'SELECT ci.id, ci.cantidad, ci.precio_unit, p.id AS producto_id, p.nombre, p.stock
     FROM carrito_items ci
     JOIN carritos c ON ci.carrito_id = c.id
     JOIN productos p ON ci.producto_id = p.id
     WHERE c.usuario_id = ?'
);
$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll();

if (empty($items)) {
    flash('warning', 'Tu carrito está vacío.');
    header('Location: ../carrito.php');
    exit;
}

$total             = array_sum(array_map(fn($i) => $i['cantidad'] * $i['precio_unit'], $items));
$nombre_completo   = $nombre . ' ' . $apellido;
$direccion_completa = $direccion . ', ' . $ciudad;

// ── Crear pedido en transacción ──────────────────────────────
try {
    $pdo->beginTransaction();

    // Insertar cabecera del pedido
    $pdo->prepare(
        'INSERT INTO pedidos (usuario_id, nombre, email, telefono, direccion, total)
         VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([
        $usuario_id,
        $nombre_completo,
        $email,
        $telefono ?: null,
        $direccion_completa,
        $total,
    ]);
    $pedido_id = (int) $pdo->lastInsertId();

    // Insertar líneas del pedido y reducir stock
    $stmt_item = $pdo->prepare(
        'INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio_unit)
         VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($items as $item) {
        $stmt_item->execute([
            $pedido_id,
            $item['producto_id'],
            $item['nombre'],
            $item['cantidad'],
            $item['precio_unit'],
        ]);
        // Descontar stock (nunca bajo de 0)
        $pdo->prepare('UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id = ?')
            ->execute([$item['cantidad'], $item['producto_id']]);
    }

    // Vaciar el carrito
    $pdo->prepare(
        'DELETE ci FROM carrito_items ci
         JOIN carritos c ON ci.carrito_id = c.id
         WHERE c.usuario_id = ?'
    )->execute([$usuario_id]);

    $pdo->commit();
    rotarCSRF();

    flash('success', '¡Pedido #' . $pedido_id . ' confirmado! Gracias por tu compra, ' . htmlspecialchars($nombre) . '.');
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    flash('error', 'Ocurrió un error al procesar tu pedido. Por favor intenta de nuevo.');
    header('Location: ../checkout.php');
    exit;
}
