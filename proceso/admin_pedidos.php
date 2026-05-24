<?php
// ── Proceso: Gestión de Pedidos (Admin) ──────────────────────
require_once '../config/session.php';
require_once '../config/database.php';

if (!estaLogueado() || !esAdmin()) {
    flash('error', 'Acceso denegado.');
    header('Location: ../index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php?seccion=pedidos');
    exit;
}
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: ../admin.php?seccion=pedidos');
    exit;
}

$action = $_POST['action'] ?? '';
$pdo    = getPDO();

// ── Cambiar estado de un pedido ──────────────────────────────
if ($action === 'cambiar_estado') {
    $pedido_id  = (int)($_POST['pedido_id'] ?? 0);
    $estados    = ['pendiente','confirmado','enviado','entregado','cancelado'];
    $nuevo_est  = $_POST['estado'] ?? '';

    if ($pedido_id <= 0 || !in_array($nuevo_est, $estados)) {
        flash('error', 'Datos inválidos.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    $stmt = $pdo->prepare('SELECT estado FROM pedidos WHERE id = ?');
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        flash('error', 'Pedido no encontrado.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    // Si se cancela un pedido, devolver stock
    if ($nuevo_est === 'cancelado' && $pedido['estado'] !== 'cancelado') {
        $items = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ?');
        $items->execute([$pedido_id]);
        $stmt_stock = $pdo->prepare('UPDATE productos SET stock = stock + ? WHERE id = ?');
        foreach ($items->fetchAll() as $item) {
            $stmt_stock->execute([$item['cantidad'], $item['producto_id']]);
        }
    }

    // Si se reactiva un pedido cancelado, volver a descontar stock
    if ($pedido['estado'] === 'cancelado' && $nuevo_est !== 'cancelado') {
        $items = $pdo->prepare('SELECT producto_id, cantidad FROM pedido_items WHERE pedido_id = ?');
        $items->execute([$pedido_id]);
        $stmt_stock = $pdo->prepare('UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id = ?');
        foreach ($items->fetchAll() as $item) {
            $stmt_stock->execute([$item['cantidad'], $item['producto_id']]);
        }
    }

    $pdo->prepare('UPDATE pedidos SET estado = ? WHERE id = ?')->execute([$nuevo_est, $pedido_id]);
    rotarCSRF();

    $labels = ['pendiente'=>'Pendiente','confirmado'=>'Confirmado','enviado'=>'Enviado',
               'entregado'=>'Entregado','cancelado'=>'Cancelado'];
    flash('success', 'Pedido #' . str_pad($pedido_id, 4, '0', STR_PAD_LEFT) . ' actualizado a «' . $labels[$nuevo_est] . '».');

    $redirect = isset($_POST['desde_detalle']) ? 'detalle-pedido&pedido_id=' . $pedido_id : 'pedidos';
    header('Location: ../admin.php?seccion=' . $redirect);
    exit;
}

// ── Eliminar pedido ──────────────────────────────────────────
if ($action === 'eliminar') {
    $pedido_id = (int)($_POST['pedido_id'] ?? 0);

    if ($pedido_id <= 0) {
        flash('error', 'Pedido no válido.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    $stmt = $pdo->prepare('SELECT estado FROM pedidos WHERE id = ?');
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        flash('error', 'Pedido no encontrado.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    if (!in_array($pedido['estado'], ['cancelado', 'entregado'])) {
        flash('warning', 'Solo puedes eliminar pedidos con estado «Cancelado» o «Entregado». Cambia el estado primero.');
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    $pdo->prepare('DELETE FROM pedidos WHERE id = ?')->execute([$pedido_id]);
    rotarCSRF();
    flash('success', 'Pedido #' . str_pad($pedido_id, 4, '0', STR_PAD_LEFT) . ' eliminado.');
    header('Location: ../admin.php?seccion=pedidos');
    exit;
}

header('Location: ../admin.php?seccion=pedidos');
exit;
