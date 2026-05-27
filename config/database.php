<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: config/database.php                               ║
// ║  PROPÓSITO: Conectar el sitio web con la base de datos       ║
// ║                                                              ║
// ║  Piensa en este archivo como la "llave" que abre la puerta   ║
// ║  de la bodega donde se guardan todos los datos del sitio:    ║
// ║  productos, usuarios, pedidos, carritos, etc.                ║
// ║                                                              ║
// ║  Este archivo se carga al inicio de CASI TODAS las páginas.  ║
// ╚══════════════════════════════════════════════════════════════╝

// ── Configuración de Base de Datos ──────────────────────────────
// Estos son los datos de conexión a MySQL (el motor que guarda toda la información).
// "localhost" significa que la base de datos está en la misma computadora que el servidor web.
// "pakal_tienda" es el nombre de la base de datos que se creó con setup.php.
// "root" y "" (vacío) son el usuario y contraseña por defecto de XAMPP.
define('DB_HOST',    'localhost');
define('DB_NAME',    'pakal_tienda');
define('DB_USER',    'root');
define('DB_PASS',    '');          // Vacío en XAMPP por defecto
define('DB_CHARSET', 'utf8mb4');   // utf8mb4 soporta emojis y acentos correctamente

// ──────────────────────────────────────────────────────────────────
// FUNCIÓN: getPDO()
// ──────────────────────────────────────────────────────────────────
// ¿Qué hace? Crea UNA SOLA conexión a la base de datos y la reutiliza
// cada vez que alguien la pida en la misma página.
//
// Esto se llama patrón "Singleton": en vez de abrir y cerrar la puerta
// de la bodega mil veces, la abrimos una sola vez y usamos esa misma
// puerta durante toda la visita a la página.
//
// PDO (PHP Data Objects) es la herramienta estándar de PHP para hablar
// con bases de datos de forma segura.
//
// Si no puede conectarse, muestra un mensaje de error en formato JSON
// (útil para depurar con herramientas de desarrollo del navegador).
function getPDO(): PDO
{
    // $pdo guardará la conexión entre llamadas a esta función.
    // "static" significa que el valor se mantiene aunque la función termine.
    static $pdo = null;

    // Solo creamos la conexión si todavía no existe.
    if ($pdo === null) {
        // "dsn" es la cadena de texto que le dice a PDO cómo conectarse:
        // qué motor usar (mysql), en qué servidor (localhost), a qué base de datos, con qué codificación.
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );

        // Opciones de comportamiento de la conexión:
        $opciones = [
            // Si hay un error en una consulta, lanza una excepción (PHP lo avisa de inmediato).
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Los resultados vienen como arrays asociativos: ['nombre' => 'Juan', 'email' => '...']
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Usa consultas preparadas REALES del servidor (más seguro contra inyección SQL).
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Intentamos abrir la conexión con los datos y opciones de arriba.
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        } catch (PDOException $e) {
            // Si falla la conexión (ej: MySQL no está corriendo), detenemos todo y mostramos el error.
            // En un sitio real de producción, aquí se guardaría el error en un archivo de log.
            die(json_encode([
                'error' => true,
                'mensaje' => 'No se pudo conectar a la base de datos.'
            ]));
        }
    }

    // Devolvemos la conexión para que otras partes del código puedan usarla.
    return $pdo;
}

// ──────────────────────────────────────────────────────────────────
// FUNCIÓN: wishlistUsuario()
// ──────────────────────────────────────────────────────────────────
// ¿Qué hace? Consulta la base de datos y devuelve la lista de IDs
// de productos que un usuario tiene guardados en sus favoritos.
//
// Se usa en las páginas de inicio, catálogo y detalle para saber
// si el corazoncito de favoritos debe aparecer relleno o vacío.
//
// Parámetros:
//   $pdo        — la conexión a la base de datos (viene de getPDO())
//   $usuario_id — el número de identificación del usuario logueado
//
// Retorna: un array (lista) de números, cada uno es el ID de un producto favorito.
// Ejemplo: [3, 7, 15] significa que el usuario tiene guardados los productos 3, 7 y 15.
function wishlistUsuario(PDO $pdo, int $usuario_id): array
{
    // Si no hay usuario válido, devolvemos lista vacía (no hay nada que buscar).
    if ($usuario_id <= 0) return [];

    // Preparamos la consulta SQL: busca todos los producto_id en la tabla "wishlist"
    // que pertenezcan a este usuario específico.
    $stmt = $pdo->prepare('SELECT producto_id FROM wishlist WHERE usuario_id = ?');
    $stmt->execute([$usuario_id]);

    // fetchAll() trae todos los resultados; array_column extrae solo la columna "producto_id"
    // y crea un array plano: [3, 7, 15] en lugar de [['producto_id'=>3], ['producto_id'=>7], ...]
    return array_column($stmt->fetchAll(), 'producto_id');
}

// ──────────────────────────────────────────────────────────────────
// FUNCIÓN: imagenesPorIds()
// ──────────────────────────────────────────────────────────────────
// ¿Qué hace? Dada una lista de IDs de productos, devuelve un mapa
// que relaciona cada producto con la ruta de su imagen principal.
//
// Ejemplo de lo que devuelve:
//   [3 => 'camisa-lino.jpg', 7 => 'zapato-piel.png', 15 => 'bolso-maya.svg']
//
// Se usa en cualquier página que muestre tarjetas de productos (inicio,
// catálogo, carrito, favoritos, etc.) para saber qué imagen mostrar.
//
// La tabla "producto_imagenes" puede tener varias imágenes por producto
// (distintos ángulos), pero aquí solo tomamos la de "orden = 0" (la principal).
function imagenesPorIds(PDO $pdo, array $ids): array
{
    // Si la lista de IDs está vacía, no hay nada que buscar.
    if (empty($ids)) return [];

    // Creamos tantos "?" como IDs hay, para armar la cláusula SQL IN (?,...,?).
    // Esto es más seguro que concatenar directamente los IDs en la cadena SQL.
    $ph   = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare(
        "SELECT producto_id, ruta
         FROM producto_imagenes
         WHERE producto_id IN ($ph)
         ORDER BY producto_id, orden ASC"  // ASC: el de menor orden (0) sale primero
    );
    $stmt->execute(array_values($ids));

    // Construimos el mapa: solo guardamos la PRIMERA imagen de cada producto.
    // Si un producto tiene 3 imágenes con órdenes 0, 1, 2 — nos quedamos con la 0.
    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        // array_key_exists verifica si ya guardamos algo para este producto.
        // Si ya está, lo ignoramos (ya tenemos la imagen principal).
        if (!array_key_exists($row['producto_id'], $map)) {
            $map[$row['producto_id']] = $row['ruta'];
        }
    }
    return $map;
}
