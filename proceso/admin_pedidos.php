<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/admin_pedidos.php                         ║
// ║  PROPÓSITO: Gestión de pedidos desde el panel de admin       ║
// ║                                                              ║
// ║  Maneja dos acciones exclusivas para administradores:        ║
// ║                                                              ║
// ║  • cambiar_estado — Actualiza el estado de un pedido         ║
// ║    (pendiente → confirmado → enviado → entregado/cancelado)  ║
// ║    Si se cancela, devuelve el stock al inventario.           ║
// ║    Si se reactiva un cancelado, vuelve a descontar el stock. ║
// ║                                                              ║
// ║  • eliminar — Borra un pedido de la base de datos            ║
// ║    (solo permitido en pedidos cancelados o entregados)       ║
// ╚══════════════════════════════════════════════════════════════╝

require_once '../config/session.php';
require_once '../config/database.php';

// ── Acceso exclusivo para administradores ────────────────────────
// estaLogueado() verifica sesión, esAdmin() verifica rol.
// Si alguno falla, mandamos al inicio con mensaje de error.
if (!estaLogueado() || !esAdmin()) {
    flash('error', 'Acceso denegado.');
    header('Location: ../index.php');
    exit;
}

// Solo acepta formularios POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php?seccion=pedidos');
    exit;
}

// Verificación de seguridad CSRF.
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: ../admin.php?seccion=pedidos');
    exit;
}

$action = $_POST['action'] ?? '';
$pdo    = getPDO();

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "cambiar_estado" — Actualizar el estado de un pedido
// ═══════════════════════════════════════════════════════════════
if ($action === 'cambiar_estado') {
    $pedido_id  = (int)($_POST['pedido_id'] ?? 0);

    // Lista de estados válidos (en orden de flujo normal de un pedido).
    $estados    = ['pendiente','confirmado','enviado','entregado','cancelado'];
    $nuevo_est  = $_POST['estado'] ?? '';

    // Validamos que el pedido_id sea positivo y el estado sea uno de los válidos.
    if ($pedido_id <= 0 || !in_array($nuevo_est, $estados)) {
        flash('error', 'Datos inválidos.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    // Buscamos el pedido actual para conocer su estado ANTES del cambio.
    // Necesitamos esto para lógica de stock (ver abajo).
    $stmt = $pdo->prepare('SELECT estado FROM pedidos WHERE id = ?');
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        flash('error', 'Pedido no encontrado.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    // ── Lógica de stock al cancelar un pedido ────────────────────
    // Si el nuevo estado es "cancelado" y el pedido NO estaba ya cancelado:
    // Devolvemos las unidades al inventario de cada producto.
    // Así esos productos vuelven a estar disponibles para otros clientes.
    if ($nuevo_est === 'cancelado' && $pedido['estado'] !== 'cancelado') {
        $items = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ?');
        $items->execute([$pedido_id]);
        $stmt_stock = $pdo->prepare('UPDATE productos SET stock = stock + ? WHERE id = ?');
        foreach ($items->fetchAll() as $item) {
            $stmt_stock->execute([$item['cantidad'], $item['producto_id']]);
        }
    }

    // ── Lógica de stock al reactivar un pedido cancelado ─────────
    // Si el pedido ESTABA cancelado y lo cambiamos a otro estado (ej: confirmado):
    // Volvemos a descontar las unidades del stock.
    // GREATEST(..., 0) garantiza que el stock no baje de 0.
    if ($pedido['estado'] === 'cancelado' && $nuevo_est !== 'cancelado') {
        $items = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ?');
        $items->execute([$pedido_id]);
        $stmt_stock = $pdo->prepare('UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
        foreach ($items->fetchAll() as $item) {
            $stmt_stock->execute([$item['cantidad'], $item['producto_id']]);
        }
    }

    // ── Actualizamos el estado del pedido en la BD ────────────────
    $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$nuevo_est, $pedido_id]);
    rotarCSRF();

    // Etiquetas legibles para el mensaje de confirmación.
    $labels = [
        'pendiente'  => 'Pendiente',
        'confirmado' => 'Confirmado',
        'enviado'    => 'Enviado',
        'entregado'  => 'Entregado',
        'cancelado'  => 'Cancelado',
    ];
    // str_pad formatea el ID con ceros a la izquierda: 5 → 0005.
    flash('success', 'Pedido #' . str_pad($pedido_id, 4, '0', STR_PAD_LEFT) . ' actualizado a «' . $labels[$nuevo_est] . '».');

    // Si se hizo desde la vista de detalle del pedido, volver ahí; si no, a la lista.
    $redirect = isset($_POST['desde_detalle']) ? 'detalle-pedido&pedido_id=' . $pedido_id : 'pedidos';
    header('Location: ../admin.php?seccion=' . $redirect);
    exit;
}

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "eliminar" — Borrar un pedido
// ═══════════════════════════════════════════════════════════════
if ($action === 'eliminar') {
    $pedido_id = (int)($_POST['pedido_id'] ?? 0);

    if ($pedido_id <= 0) {
        flash('error', 'Pedido no válido.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    // Buscamos el pedido para verificar su estado actual.
    $stmt = $pdo->prepare('SELECT estado FROM pedidos WHERE id = ?');
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        flash('error', 'Pedido no encontrado.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    // ── Solo se pueden borrar pedidos finalizados ─────────────────
    // Un pedido "pendiente", "confirmado" o "enviado" representa una
    // obligación activa con el cliente. No se debe borrar hasta que
    // esté "entregado" o "cancelado".
    if (!in_array($pedido['estado'], ['cancelado', 'entregado'])) {
        flash('warning', 'Solo puedes eliminar pedidos con estado «Cancelado» o «Entregado». Cambia el estado primero.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    // La tabla "pedido_items" se borra automáticamente gracias a
    // la restricción de clave foránea CASCADE en la base de datos.
    $pdo->prepare('DELETE FROM pedidos WHERE id = ?')->execute([$pedido_id]);
    rotarCSRF();
    flash('success', 'Pedido #' . str_pad($pedido_id, 4, '0', STR_PAD_LEFT) . ' eliminado.');
    header('Location: ../admin.php?seccion=pedidos');
    exit;
}

// Si llegó una acción desconocida, volvemos a la lista de pedidos.
header('Location: ../admin.php?seccion=pedidos');
exit;
