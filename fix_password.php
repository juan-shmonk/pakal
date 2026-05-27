<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: fix_password.php                                  ║
// ║  PROPÓSITO: Restablecer la contraseña del administrador      ║
// ║                                                              ║
// ║  Script de emergencia de un solo uso.                        ║
// ║  Se ejecuta en el navegador: http://localhost/proyecto/fix_password.php
// ║                                                              ║
// ║  ¿Para qué sirve?                                            ║
// ║  Si el admin olvida su contraseña y no puede entrar,         ║
// ║  este script la resetea a "Pakal2026!" directamente en BD.   ║
// ║                                                              ║
// ║  ⚠️ IMPORTANTE: Eliminar este archivo después de usarlo.     ║
// ║  Dejarlo en el servidor es un riesgo de seguridad: cualquier  ║
// ║  persona que sepa la URL podría resetear la contraseña.       ║
// ╚══════════════════════════════════════════════════════════════╝

require_once __DIR__ . '/config/database.php';

// La nueva contraseña en texto plano.
$nuevoPassword = 'Pakal2026!';

// password_hash cifra la contraseña antes de guardarla en BD.
// NUNCA guardamos contraseñas en texto plano. Solo el hash.
$hash = password_hash($nuevoPassword, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE email = 'admin@pakal.mx'");
    $stmt->execute([$hash]);
    $filas = $stmt->rowCount();

    if ($filas > 0) {
        echo "<p style='font-family:sans-serif;color:green;padding:20px;'>
            ✓ Contraseña actualizada correctamente.<br><br>
            Email: <strong>admin@pakal.mx</strong><br>
            Contraseña: <strong>$nuevoPassword</strong><br><br>
            <a href='/proyecto/auth.php'>Ir al Login</a>
            &nbsp;·&nbsp;
            <strong>Elimina este archivo después de ingresar.</strong>
        </p>";
    } else {
        echo "<p style='font-family:sans-serif;color:red;padding:20px;'>
            No se encontró el usuario admin@pakal.mx en la base de datos.
        </p>";
    }
} catch (PDOException $e) {
    echo "<p style='font-family:sans-serif;color:red;padding:20px;'>Error: " . $e->getMessage() . "</p>";
}
