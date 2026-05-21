<?php
/**
 * ═══════════════════════════════════════════════════════════
 *  PAKAL — Script de Configuración Inicial
 *  Ejecutar UNA SOLA VEZ en el navegador:
 *  http://localhost/proyecto/setup.php
 *
 *  IMPORTANTE: Elimina o restringe este archivo después de
 *  ejecutarlo para evitar que se pueda volver a usar.
 * ═══════════════════════════════════════════════════════════
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'pakal_tienda');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── Datos del administrador por defecto ──────────────────────
$adminNombre   = 'Admin';
$adminApellido = 'PAKAL';
$adminEmail    = 'admin@pakal.mx';
$adminPassword = 'Pakal2026!';

// ─────────────────────────────────────────────────────────────

header('Content-Type: text/html; charset=utf-8');
$log     = [];
$errores = 0;

function logStep(string $msg, bool $ok = true): void {
    global $log, $errores;
    $log[] = ['ok' => $ok, 'msg' => $msg];
    if (!$ok) $errores++;
}

// ── 1. Conectar a MySQL (sin dbname) ────────────────────────
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    logStep('Conexión a MySQL establecida.');
} catch (PDOException $e) {
    logStep('No se pudo conectar a MySQL: ' . $e->getMessage(), false);
    mostrarResultado($log, $errores);
    exit;
}

// ── 2. Borrar directorio de BD por filesystem y recrear ──────
// MySQL no puede hacer DROP DATABASE si quedan archivos .ibd
// huérfanos (errno 41). La solución definitiva es eliminar
// todos los archivos del directorio con PHP y luego recrear.
try {
    $row     = $pdo->query("SHOW VARIABLES LIKE 'datadir'")->fetch(PDO::FETCH_ASSOC);
    $datadir = $row ? rtrim($row['Value'], '/\\') : '';
    $dbDir   = $datadir . DIRECTORY_SEPARATOR . DB_NAME;

    if ($datadir && is_dir($dbDir)) {
        $archivos  = glob($dbDir . DIRECTORY_SEPARATOR . '*') ?: [];
        $borrados  = 0;
        $fallidos  = [];

        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                if (@unlink($archivo)) {
                    $borrados++;
                } else {
                    $fallidos[] = basename($archivo);
                }
            }
        }

        if (!empty($fallidos)) {
            logStep(
                'No se pudieron eliminar estos archivos (cierra XAMPP, bórralos manualmente y reintenta):<br>'
                . '<strong>' . implode(', ', $fallidos) . '</strong><br>'
                . '<small>Ruta: <code>' . htmlspecialchars($dbDir) . '</code></small>',
                false
            );
            mostrarResultado($log, $errores);
            exit;
        }

        // Directorio vacío → borrarlo (ya no hace falta DROP DATABASE)
        @rmdir($dbDir);
        logStep("Directorio de base de datos eliminado ($borrados archivo(s)). InnoDB reseteado.");
    } else {
        logStep('No existía directorio previo — creación desde cero.');
    }

    // El directorio ya no existe → CREATE DATABASE funciona directamente.
    // NO llamamos DROP DATABASE: con el dir borrado MySQL lo intentaría
    // limpiar en ibdata1 y se quedaría colgado.
    $pdo->exec(
        "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
    logStep('Base de datos "' . DB_NAME . '" creada limpia.');

    // Reconectar apuntando a la BD nueva
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    logStep('Conexión apuntando a "' . DB_NAME . '" lista.');

} catch (PDOException $e) {
    logStep('Error al preparar la base de datos: ' . $e->getMessage(), false);
    mostrarResultado($log, $errores);
    exit;
}

// ── 3. Ejecutar esquema SQL ──────────────────────────────────
$sqlFile = __DIR__ . '/database/pakal.sql';
if (!file_exists($sqlFile)) {
    logStep('Archivo database/pakal.sql no encontrado.', false);
} else {
    try {
        $sql = file_get_contents($sqlFile);

        // Eliminar comentarios de línea (-- ...) y de bloque (/* ... */)
        $sql = preg_replace('/--[^\n]*/', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Dividir por ; y filtrar sentencias vacías
        $sentencias = array_values(array_filter(
            array_map('trim', explode(';', $sql)),
            fn($s) => strlen($s) > 3
        ));

        $ok = 0; $fail = 0;
        foreach ($sentencias as $sentencia) {
            try {
                $pdo->exec($sentencia);
                $ok++;
            } catch (PDOException $ex) {
                $code = (int) $ex->errorInfo[1];
                // 1051 = DROP TABLE de tabla inexistente → ignorar
                if ($code === 1051) { $ok++; continue; }

                logStep(
                    'Sentencia fallida [' . $code . ']: ' . htmlspecialchars($ex->getMessage())
                    . '<br><small>' . htmlspecialchars(substr($sentencia, 0, 120)) . '…</small>',
                    false
                );
                $fail++;
            }
        }

        $msg = "Esquema SQL: {$ok} sentencias OK";
        if ($fail) $msg .= ", {$fail} con error";
        logStep($msg, $fail === 0);

    } catch (PDOException $e) {
        logStep('Error al procesar SQL: ' . $e->getMessage(), false);
    }
}

// ── 4. Crear usuario administrador ──────────────────────────
try {
    $check = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? OR rol = 'admin' LIMIT 1");
    $check->execute([$adminEmail]);

    if ($check->fetch()) {
        logStep('El usuario administrador ya existe. No se sobreescribió.');
    } else {
        $hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $ins  = $pdo->prepare(
            "INSERT INTO usuarios (nombre, apellido, email, password_hash, rol)
             VALUES (?, ?, ?, ?, 'admin')"
        );
        $ins->execute([$adminNombre, $adminApellido, $adminEmail, $hash]);
        logStep("Administrador creado → Email: <strong>$adminEmail</strong> · Contraseña: <strong>$adminPassword</strong>");
    }
} catch (PDOException $e) {
    logStep('Error al crear administrador: ' . $e->getMessage(), false);
}

// ── 5. Verificar estructura ──────────────────────────────────
try {
    $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    logStep('Tablas creadas: ' . implode(', ', $tablas));
} catch (PDOException $e) {
    logStep('Error al verificar tablas.', false);
}

mostrarResultado($log, $errores);

// ─────────────────────────────────────────────────────────────
function mostrarResultado(array $log, int $errores): void {
    $estado = $errores === 0 ? 'Configuración completada' : "Completado con $errores error(es)";
    $color  = $errores === 0 ? '#2E7D32' : '#C44A4A';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Setup — PAKAL</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Helvetica Neue', sans-serif; background: #F5F4F2; color: #1C1C1C; padding: 60px 24px; }
    .card { max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid #E2DDD8; padding: 48px; }
    .logo { font-size: 2rem; letter-spacing: 0.3em; font-weight: 400; margin-bottom: 4px; }
    .subtitle { font-size: 0.65rem; letter-spacing: 0.22em; text-transform: uppercase; color: #C4A96A; margin-bottom: 40px; }
    h1 { font-size: 1.4rem; font-weight: 400; margin-bottom: 32px; padding-bottom: 16px; border-bottom: 1px solid #E2DDD8; color: <?= $color ?>; }
    .step { display: flex; gap: 12px; align-items: flex-start; padding: 12px 0; border-bottom: 1px solid #F2F0EC; font-size: 0.85rem; line-height: 1.6; }
    .step:last-child { border-bottom: none; }
    .step__icon { font-size: 1rem; flex-shrink: 0; margin-top: 2px; }
    .step--ok  .step__icon { color: #2E7D32; }
    .step--err .step__icon { color: #C44A4A; }
    .step--ok  .step__msg  { color: #1C1C1C; }
    .step--err .step__msg  { color: #C44A4A; }
    .actions { margin-top: 40px; display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { display: inline-block; padding: 12px 28px; border: 1px solid #0A0A0A; font-size: 0.72rem; letter-spacing: 0.14em; text-transform: uppercase; text-decoration: none; color: #0A0A0A; transition: all 0.28s; }
    .btn:hover { background: #0A0A0A; color: #fff; }
    .btn--solid { background: #0A0A0A; color: #fff; }
    .btn--solid:hover { background: #333; }
    .warning { background: #FFF3E0; border: 1px solid #FFB74D; padding: 16px 20px; margin-top: 28px; font-size: 0.82rem; line-height: 1.7; color: #E65100; }
    .warning strong { display: block; margin-bottom: 4px; }
  </style>
</head>
<body>
<div class="card">
  <p class="logo">PAKAL</p>
  <p class="subtitle">Setup inicial de la tienda</p>
  <h1><?= $estado ?></h1>

  <div>
    <?php foreach ($log as $item): ?>
    <div class="step step--<?= $item['ok'] ? 'ok' : 'err' ?>">
      <span class="step__icon"><?= $item['ok'] ? '✓' : '✕' ?></span>
      <span class="step__msg"><?= $item['msg'] ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($errores === 0): ?>
  <div class="warning">
    <strong>⚠ Importante — seguridad</strong>
    Elimina o renombra <code>setup.php</code> una vez terminada la configuración para evitar que pueda volver a ejecutarse.
  </div>
  <div class="actions">
    <a href="auth.php" class="btn btn--solid">Ir a Login</a>
    <a href="index.php" class="btn">Ver tienda</a>
    <a href="admin.php" class="btn">Panel Admin</a>
  </div>
  <?php else: ?>
  <div class="actions">
    <a href="setup.php" class="btn">Reintentar</a>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
<?php
}
