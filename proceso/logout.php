<?php
// ── Proceso: Cerrar Sesión ───────────────────────────────────
require_once '../config/session.php';

cerrarSesion();

header('Location: ../index.php');
exit;
