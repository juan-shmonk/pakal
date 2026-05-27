<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/carrito.php                               ║
// ║  PROPÓSITO: Gestionar las operaciones del carrito de compras ║
// ║                                                              ║
// ║  Maneja tres acciones distintas según lo que llegue en el   ║
// ║  campo "action" del formulario:                              ║
// ║                                                              ║
// ║  • agregar   — Añade un producto al carrito                  ║
// ║  • eliminar  — Quita un producto del carrito                 ║
// ║  • actualizar — Cambia la cantidad de un producto             ║
// ║                                                              ║
// ║  Todos los cambios se guardan en la base de datos para que   ║
// ║  el carrito persista aunque el usuario cierre el navegador.  ║
// ╚══════════════════════════════════════════════════════════════╝

require_once '../config/session.php';
require_once '../config/database.php';

// ── El carrito requiere estar logueado ───────────────────────────
// Un carrito está vinculado a una cuenta de usuario en la base de datos.
if (!estaLogueado()) {
    flash('warning', 'Debes iniciar sesión para agregar productos al carrito.');
    header('Location: ../auth.php?tab=login');
    exit;
}

// Solo aceptamos solicitudes POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../carrito.php');
    exit;
}

// Leemos la acción que nos envió el formulario.
$action     = $_POST['action'] ?? '';
$pdo        = getPDO();
$usuario_id = $_SESSION['usuario_id'];  // El ID del usuario logueado

// ═══════════════════════════════════════════════════════════════
// FUNCIÓN AUXILIAR: getOCrearCarrito()
// ═══════════════════════════════════════════════════════════════
// ¿Qué hace? Cada usuario tiene UN carrito en la base de datos.
// Esta función busca el carrito del usuario. Si no existe (primera vez),
// lo crea automáticamente.
// Devuelve el ID del carrito (número entero).
//
// La tabla "carritos" es un contenedor: guarda quién es el dueño.
// La tabla "carrito_items" guarda los productos dentro de ese contenedor.
function getOCrearCarrito(PDO $pdo, int $usuario_id): int
{
    // Buscamos si ya existe un carrito para este usuario.
    $stmt = $pdo->prepare('SELECT id FROM carritos WHERE usuario_id = ? LIMIT 1');
    $stmt->execute([$usuario_id]);
    $row = $stmt->fetch();

    // Si ya existe, devolvemos su ID.
    if ($row) return (int) $row['id'];

    // Si no existe, creamos uno nuevo y devolvemos el ID generado.
    $pdo->prepare('INSERT INTO carritos (usuario_id) VALUES (?)')->execute([$usuario_id]);
    return (int) $pdo->lastInsertId();
}

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "agregar" — Añadir un producto al carrito
// ═══════════════════════════════════════════════════════════════
if ($action === 'agregar') {

    // Validación CSRF (el formulario en detalle.php trae el token).
    if (!validarCSRF($_POST['csrf_token'] ?? '')) {
        flash('error', 'Token de seguridad inválido.');
        header('Location: ../detalle.php?id=' . (int) ($_POST['producto_id'] ?? 0));
        exit;
    }

    // (int) convierte a entero para evitar inyección SQL.
    // max(1, ...) garantiza que la cantidad sea al menos 1 (nunca 0 ni negativo).
    $producto_id = (int) ($_POST['producto_id'] ?? 0);
    $cantidad    = max(1, (int) ($_POST['cantidad'] ?? 1));

    // ── Verificar que el producto existe y está disponible ────────
    // Consultamos la BD para confirmar que el producto es real y está "activo".
    // También obtenemos el stock y el precio actual (no confiamos en el precio del formulario).
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

    // Verificar que haya stock.
    if ($producto['stock'] <= 0) {
        flash('warning', 'Este producto está agotado.');
        header('Location: ../detalle.php?id=' . $producto_id);
        exit;
    }

    // El precio que se guarda es el de rebaja si existe, o el precio normal.
    // "??" es el operador null coalescing: si precio_rebaja es null, usa precio.
    $precio     = $producto['precio_rebaja'] ?? $producto['precio'];
    $carrito_id = getOCrearCarrito($pdo, $usuario_id);

    // ── Verificar si el producto ya estaba en el carrito ──────────
    $stmt = $pdo->prepare(
        'SELECT id, cantidad FROM carrito_items WHERE carrito_id = ? AND producto_id = ?'
    );
    $stmt->execute([$carrito_id, $producto_id]);
    $item = $stmt->fetch();

    if ($item) {
        // El producto ya estaba: SUMAMOS la nueva cantidad a la existente.
        // min(..., $producto['stock']) evita agregar más unidades de las que hay en stock.
        $nueva = min($item['cantidad'] + $cantidad, $producto['stock']);
        $pdo->prepare('UPDATE carrito_items SET cantidad = ? WHERE id = ?')
            ->execute([$nueva, $item['id']]);
    } else {
        // Es la primera vez que se agrega este producto: INSERTAMOS una nueva fila.
        // Guardamos el precio actual para que no cambie si el precio del producto se modifica después.
        $pdo->prepare(
            'INSERT INTO carrito_items (carrito_id, producto_id, cantidad, precio_unit) VALUES (?, ?, ?, ?)'
        )->execute([$carrito_id, $producto_id, $cantidad, $precio]);
    }

    rotarCSRF();
    flash('success', 'Producto agregado al carrito.');
    header('Location: ../carrito.php');
    exit;
}

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "eliminar" — Quitar un producto del carrito
// ═══════════════════════════════════════════════════════════════
if ($action === 'eliminar') {
    $item_id = (int) ($_POST['item_id'] ?? 0);  // ID de la línea en carrito_items

    // Borramos el ítem, pero SOLO si pertenece al carrito del usuario actual.
    // El JOIN garantiza que un usuario no pueda borrar items de otro usuario
    // aunque adivine el item_id.
    $pdo->prepare(
        'DELETE ci FROM carrito_items ci
         JOIN carritos c ON ci.carrito_id = c.id
         WHERE ci.id = ? AND c.usuario_id = ?'
    )->execute([$item_id, $usuario_id]);

    header('Location: ../carrito.php');
    exit;
}

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "actualizar" — Cambiar la cantidad de un ítem
// ═══════════════════════════════════════════════════════════════
if ($action === 'actualizar') {
    $item_id  = (int) ($_POST['item_id'] ?? 0);
    $cantidad = max(1, (int) ($_POST['cantidad'] ?? 1));  // Mínimo 1 unidad

    // Actualizamos la cantidad, con la misma verificación de seguridad:
    // el ítem debe pertenecer al carrito del usuario actual (JOIN carritos).
    $pdo->prepare(
        'UPDATE carrito_items ci
         JOIN carritos c ON ci.carrito_id = c.id
         SET ci.cantidad = ?
         WHERE ci.id = ? AND c.usuario_id = ?'
    )->execute([$cantidad, $item_id, $usuario_id]);

    header('Location: ../carrito.php');
    exit;
}

// Si llegó una acción desconocida, simplemente volvemos al carrito.
header('Location: ../carrito.php');
exit;
