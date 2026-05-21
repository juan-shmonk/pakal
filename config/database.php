<?php
// ── Configuración de Base de Datos ──────────────────────────
// Cambia estos valores según tu entorno XAMPP
define('DB_HOST',    'localhost');
define('DB_NAME',    'pakal_tienda');
define('DB_USER',    'root');
define('DB_PASS',    '');          // Vacío en XAMPP por defecto
define('DB_CHARSET', 'utf8mb4');

/**
 * Devuelve una instancia única de PDO (Singleton).
 * Lanza una excepción si no puede conectar.
 */
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            // En producción: loggear y mostrar mensaje genérico
            die(json_encode([
                'error' => true,
                'mensaje' => 'No se pudo conectar a la base de datos.'
            ]));
        }
    }

    return $pdo;
}
