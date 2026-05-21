<?php
// Script de un solo uso — eliminar después de ejecutar
require_once __DIR__ . '/config/database.php';

$nuevoPassword = 'Pakal2026!';
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
