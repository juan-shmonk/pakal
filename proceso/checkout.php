<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/checkout.php                              ║
// ║  PROPÓSITO: Crear un pedido oficial a partir del carrito     ║
// ║                                                              ║
// ║  Este es el paso final de la compra. Recibe el formulario    ║
// ║  de datos de entrega (de checkout.php), crea el pedido en   ║
// ║  la base de datos, reduce el stock de cada producto vendido  ║
// ║  y vacía el carrito del usuario.                             ║
// ║                                                              ║
// ║  Usa una "transacción" de base de datos: si algo falla en    ║
// ║  el medio (ej: error al guardar un ítem), TODO se cancela    ║
// ║  y el sistema queda en el estado anterior. Nunca a medias.   ║
// ╚══════════════════════════════════════════════════════════════╝

require_once '../config/session.php';
require_once '../config/database.php';

// Solo usuarios logueados pueden hacer pedidos.
if (!estaLogueado()) {
    header('Location: ../auth.php?tab=login');
    exit;
}

// Solo acepta formularios enviados por POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../checkout.php');
    exit;
}

// Verificación de seguridad CSRF.
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido. Intenta de nuevo.');
    header('Location: ../checkout.php');
    exit;
}

// ── Recoger los datos de entrega del formulario ──────────────────
$nombre    = trim($_POST['nombre']    ?? '');
$apellido  = trim($_POST['apellido']  ?? '');
$email     = trim($_POST['email']     ?? '');
$telefono  = trim($_POST['telefono']  ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad    = trim($_POST['ciudad']    ?? '');

// Verificamos que los campos obligatorios no estén vacíos.
if (empty($nombre) || empty($apellido) || empty($email) || empty($direccion) || empty($ciudad)) {
    flash('error', 'Por favor completa todos los campos obligatorios.');
    header('Location: ../checkout.php');
    exit;
}

// Verificamos que el email tenga formato válido.
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'El formato del correo electrónico no es válido.');
    header('Location: ../checkout.php');
    exit;
}

$pdo        = getPDO();
$usuario_id = $_SESSION['usuario_id'];

// ── Obtener los ítems del carrito ────────────────────────────────
// Consultamos todo lo que hay en el carrito del usuario:
// la cantidad, el precio unitario (guardado cuando se agregó), y datos del producto.
$stmt = $pdo->prepare(
    'SELECT ci.id, ci.cantidad, ci.precio_unit, p.id AS producto_id, p.nombre, p.stock
     FROM carrito_items ci
     JOIN carritos c ON ci.carrito_id = c.id
     JOIN productos p ON ci.producto_id = p.id
     WHERE c.usuario_id = ?'
);
$stmt->execute([$usuario_id]);
$items = $stmt->fetchAll();

// Si el carrito está vacío, no podemos hacer un pedido.
if (empty($items)) {
    flash('warning', 'Tu carrito está vacío.');
    header('Location: ../carrito.php');
    exit;
}

// ── Calcular el total del pedido ─────────────────────────────────
// array_sum suma todos los valores del array.
// array_map aplica una función a cada ítem: cantidad × precio unitario.
// fn($i) es una función flecha (sintaxis moderna de PHP): recibe un ítem, devuelve su subtotal.
$total             = array_sum(array_map(fn($i) => $i['cantidad'] * $i['precio_unit'], $items));
$nombre_completo   = $nombre . ' ' . $apellido;
$direccion_completa = $direccion . ', ' . $ciudad;

// ═══════════════════════════════════════════════════════════════
// CREAR EL PEDIDO CON TRANSACCIÓN
// ═══════════════════════════════════════════════════════════════
// Una transacción garantiza que todas las operaciones de BD
// se completen JUNTAS o NINGUNA se aplique.
//
// Imagina que el servidor se cae justo después de guardar el pedido
// pero antes de reducir el stock. Sin transacción, tendríamos un
// pedido registrado pero el stock no se descontó (inconsistencia).
// Con transacción, si algo falla, se hace rollBack() y todo vuelve
// al estado anterior como si nada hubiera pasado.
try {
    // Iniciamos la transacción: desde aquí todas las operaciones son "tentativas".
    $pdo->beginTransaction();

    // ── Paso 1: Crear la cabecera del pedido ─────────────────────
    // La tabla "pedidos" guarda los datos generales: quién, cuánto, a dónde.
    $pdo->prepare(
        'INSERT INTO pedidos (usuario_id, nombre, email, telefono, direccion, total)
         VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([
        $usuario_id,
        $nombre_completo,
        $email,
        $telefono ?: null,      // Si no hay teléfono, guardamos NULL
        $direccion_completa,
        $total,
    ]);
    // Obtenemos el ID asignado al pedido recién creado.
    $pedido_id = (int) $pdo->lastInsertId();

    // ── Paso 2: Guardar cada ítem del pedido y reducir stock ──────
    // La tabla "pedido_items" guarda los productos individuales del pedido.
    // Guardamos el nombre y precio del producto AHORA porque en el futuro
    // el producto podría cambiar de nombre o precio.
    $stmt_item = $pdo->prepare(
        'INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio_unit)
         VALUES (?, ?, ?, ?, ?)'
    );
    foreach ($items as $item) {
        $stmt_item->execute([
            $pedido_id,
            $item['producto_id'],
            $item['nombre'],       // Guardamos el nombre actual del producto
            $item['cantidad'],
            $item['precio_unit'],  // Guardamos el precio que tenía cuando se agregó al carrito
        ]);

        // Descontamos las unidades vendidas del stock del producto.
        // GREATEST(stock - ?, 0) garantiza que el stock nunca baje de 0.
        $pdo->prepare('UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id = ?')
            ->execute([$item['cantidad'], $item['producto_id']]);
    }

    // ── Paso 3: Vaciar el carrito ─────────────────────────────────
    // Borramos todos los ítems del carrito del usuario (ya están en el pedido).
    $pdo->prepare(
        'DELETE ci FROM carrito_items ci
         JOIN carritos c ON ci.carrito_id = c.id
         WHERE c.usuario_id = ?'
    )->execute([$usuario_id]);

    // ── Confirmar todas las operaciones ───────────────────────────
    // commit() aplica todos los cambios de la transacción de forma permanente.
    $pdo->commit();
    rotarCSRF();

    // Mensaje de confirmación para el usuario.
    flash('success', '¡Pedido #' . $pedido_id . ' confirmado! Gracias por tu compra, ' . htmlspecialchars($nombre) . '.');
    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    // Si algo falló, deshacemos TODOS los cambios de la transacción.
    // El carrito y el stock vuelven al estado que tenían antes.
    $pdo->rollBack();
    flash('error', 'Ocurrió un error al procesar tu pedido. Por favor intenta de nuevo.');
    header('Location: ../checkout.php');
    exit;
}
