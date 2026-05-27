<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/logout.php                                ║
// ║  PROPÓSITO: Cerrar la sesión del usuario                    ║
// ║                                                              ║
// ║  Este archivo se ejecuta cuando el usuario hace clic en      ║
// ║  "Salir" en el menú del header.                              ║
// ║                                                              ║
// ║  ¿Qué hace?                                                  ║
// ║  1. Destruye todos los datos de sesión del servidor          ║
// ║  2. Elimina la cookie del navegador                          ║
// ║  3. Redirige al usuario a la página de inicio                ║
// ║                                                              ║
// ║  Después de esto, el usuario es un visitante anónimo.        ║
// ╚══════════════════════════════════════════════════════════════╝

// Cargamos la sesión para poder destruirla.
require_once '../config/session.php';

// cerrarSesion() borra los datos de sesión del servidor Y la cookie del navegador.
// Ver la función en config/session.php para más detalles.
cerrarSesion();

// Mandamos al usuario a la página de inicio (ya como visitante anónimo).
header('Location: ../index.php');
exit;
