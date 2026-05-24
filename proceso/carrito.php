<?php
// ── Proceso: Operaciones del Carrito ────────────────────────
require_once '../config/session.php';
require_once '../config/database.php';

// El carrito requiere sesión iniciada
if (!estaLogueado()) {
    flash('warning', 'Debes iniciar sesión para agregar productos al carrito.');
    header('Location: ../auth.php?tab=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../carrito.php');
    exit;
}

$action     = $_POST['action'] ?? '';
$pdo        = getPDO();
$usuario_id = $_SESSION['usuario_id'];

// ── Helper: obtener o crear el carrito del usuario ───────────
function getOCrearCarrito(PDO $pdo, int $usuario_id): int
{
    $stmt = $pdo->prepare('SELECT id FROM carritos WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$usuario_id]);
    $row = $stmt->fetch();
    if ($row) return (int) $row['id'];

    $pdo->prepare('INSERT INTO carritos (usuario_id) VALUES (?)')->execute([$usuario_id]);
    return (int) $pdo->lastInsertId();
}

// ── Agregar producto ─────────────────────────────────────────
if ($action === 'agregar') {
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token de seguridad inválido.');
        header('Location: ../detalle.php?id=' . (int) ($_POST['producto_id'] ?? 0));
        exit;
    }

    $producto_id = (int) ($_POST['producto_id'] ?? 0);
    $cantidad    = max(1, (int) ($_POST['cantidad'] ?? 1));

    // Verificar que el producto existe y está activo
    $stmt = $pdo->prepare(
        'SELECT id, precio, precio_rebaja, stock FROM productos WHERE id = ? AND estado = "activo"'
    );
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        flash('error', 'Producto no disponible.');
        header('Location: ../productos.php');
        exit;
    }

    if ($producto['stock'] <= 0) {
        flash('warning', 'Este producto está agotado.');
        header('Location: ../detalle.php?id=' . $producto_id);
        exit;
    }

    $precio     = $producto['precio_rebaja'] ?? $producto['precio'];
    $carrito_id = getOCrearCarrito($pdo, $usuario_id);

    // Si ya existe el producto en el carrito, sumar cantidad
    $stmt = $pdo->prepare(
        'SELECT id, cantidad FROM carrito_items WHERE carrito_id = ? AND producto_id = ?'
    );
    $stmt->execute([$carrito_id, $producto_id]);
    $item = $stmt->fetch();

    if ($item) {
        $nueva = min($item['cantidad'] + $cantidad, $producto['stock']);
        $pdo->prepare('UPDATE carrito_items SET cantidad = ? WHERE id = ?')
            ->execute([$nueva, $item['id']]);
    } else {
        $pdo->prepare(
            'INSERT INTO carrito_items (carrito_id, producto_id, cantidad, precio_unit) VALUES (?, ?, ?, ?)'
        )->execute([$carrito_id, $producto_id, $cantidad, $precio]);
    }

    rotarCSRF();
    flash('success', 'Producto agregado al carrito.');
    header('Location: ../carrito.php');
    exit;
}

// ── Eliminar ítem ────────────────────────────────────────────
if ($action === 'eliminar') {
    $item_id = (int) ($_POST['item_id'] ?? 0);

    // Eliminar solo si el ítem pertenece al carrito del usuario
    $pdo->prepare(
        'DELETE ci FROM carrito_items ci
         JOIN carritos c ON ci.carrito_id = c.id
         WHERE ci.id = ? AND c.usuario_id = ?'
    )->execute([$item_id, $usuario_id]);

    header('Location: ../carrito.php');
    exit;
}

// ── Actualizar cantidad ──────────────────────────────────────
if ($action === 'actualizar') {
    $item_id  = (int) ($_POST['item_id'] ?? 0);
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));

    $pdo->prepare(
        'UPDATE carrito_items ci
         JOIN carritos c ON ci.carrito_id = c.id
         SET ci.cantidad = ?
         WHERE ci.id = ? AND c.usuario_id = ?'
    )->execute([$cantidad, $item_id, $usuario_id]);

    header('Location: ../carrito.php');
    exit;
}

header('Location: ../carrito.php');
exit;
