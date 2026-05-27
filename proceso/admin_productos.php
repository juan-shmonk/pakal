<?php
// ╔══════════════════════════════════════════════════════════════╗
// ║  ARCHIVO: proceso/admin_productos.php                       ║
// ║  PROPÓSITO: Gestión completa de productos (solo admins)      ║
// ║                                                              ║
// ║  Implementa las cuatro operaciones CRUD sobre productos:     ║
// ║  C = Create  (action=guardar)  — Crear un producto nuevo     ║
// ║  R = Read    (en admin.php)    — Listado de productos        ║
// ║  U = Update  (action=actualizar) — Editar un producto        ║
// ║  D = Delete  (action=eliminar)  — Borrar un producto         ║
// ║                                                              ║
// ║  También maneja las imágenes de producto:                    ║
// ║  - Si el admin sube una imagen → la guarda en assets/img/    ║
// ║  - Si no sube imagen → genera automáticamente un SVG         ║
// ║    con el nombre del producto sobre fondo con degradado      ║
// ╚══════════════════════════════════════════════════════════════╝

require_once '../config/session.php';
require_once '../config/database.php';

// Solo administradores pueden gestionar productos.
if (!estaLogueado() || !esAdmin()) {
    flash('error', 'Acceso denegado.');
    header('Location: ../index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}
if (!validarCSRF($_POST['csrf_token'] ?? '')) {
    flash('error', 'Token de seguridad inválido.');
    header('Location: ../admin.php');
    exit;
}

$action = $_POST['action'] ?? '';
$pdo    = getPDO();

// ═══════════════════════════════════════════════════════════════
// FUNCIONES AUXILIARES
// ═══════════════════════════════════════════════════════════════

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: slugify()
// ──────────────────────────────────────────────────────────────
// Convierte un texto cualquiera en un "slug" para URLs amigables.
// Un slug es texto en minúsculas sin acentos ni caracteres especiales,
// con palabras separadas por guiones.
// Ejemplo: "Camisa Lino Ámbar" → "camisa-lino-ambar"
// ¿Para qué sirve? Para crear URLs limpias como /producto/camisa-lino-ambar
function slugify(string $texto): string
{
    $mapa = ['á'=>'a','à'=>'a','ä'=>'a','â'=>'a','é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
             'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i','ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
             'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u','ñ'=>'n'];
    $texto = mb_strtolower(strtr($texto, $mapa), 'UTF-8');
    return trim(preg_replace('/[\s-]+/', '-', preg_replace('/[^a-z0-9\s-]/u', '', $texto)), '-');
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: slugUnico()
// ──────────────────────────────────────────────────────────────
// Garantiza que el slug sea único en la base de datos.
// Si ya existe "camisa-lino", prueba "camisa-lino-2", "camisa-lino-3", etc.
// $excluir_id permite que un producto existente conserve su propio slug
// sin que se detecte como "duplicado" de sí mismo al editarlo.
function slugUnico(PDO $pdo, string $base, int $excluir_id = 0): string
{
    $slug = $base; $sufijo = 1;
    while (true) {
        // Comprobamos si el slug ya existe en la BD (excluyendo el producto actual si es edición).
        $chk = $pdo->prepare('SELECT id FROM productos WHERE slug = ? AND id != ?');
        $chk->execute([$slug, $excluir_id]);
        if (!$chk->fetch()) break;  // Si no existe, salimos del bucle
        $slug = $base . '-' . $sufijo++;  // Probamos con sufijo numérico
    }
    return $slug;
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: upsertImagen()
// ──────────────────────────────────────────────────────────────
// "Upsert" = Update + Insert (actualizar si existe, insertar si no).
// Guarda la imagen principal (orden=0) de un producto.
// Si ya había una imagen principal, la reemplaza. Si no había, la crea.
function upsertImagen(PDO $pdo, int $producto_id, string $filename): void
{
    // Comprobamos si ya existe una imagen principal para este producto.
    $stmt = $pdo->prepare('SELECT id FROM producto_imagenes WHERE producto_id = ? AND orden = 0');
    $stmt->execute([$producto_id]);
    if ($stmt->fetch()) {
        // Ya existe: actualizamos la ruta con la nueva imagen.
        $pdo->prepare('UPDATE producto_imagenes SET ruta = ? WHERE producto_id = ? AND orden = 0')
            ->execute([$filename, $producto_id]);
    } else {
        // No existe: insertamos la nueva imagen con orden=0 (posición principal).
        $pdo->prepare('INSERT INTO producto_imagenes (producto_id, ruta, orden) VALUES (?, ?, 0)')
            ->execute([$producto_id, $filename]);
    }
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: generarSVG()
// ──────────────────────────────────────────────────────────────
// Genera automáticamente una imagen SVG de placeholder cuando
// el administrador crea un producto sin subir una foto.
//
// Un SVG (Scalable Vector Graphics) es una imagen basada en código XML
// que se puede escalar a cualquier tamaño sin perder calidad.
//
// ¿Cómo funciona?
// - Elige una paleta de colores basada en el ID del producto (cíclica).
// - Dibuja un fondo con degradado de color.
// - Añade elementos decorativos mayas (rombos concéntricos).
// - Escribe el nombre del producto y la marca.
// - Devuelve el código XML del SVG como texto.
//
// El resultado es un archivo .svg guardado en assets/img/productos/
function generarSVG(int $producto_id, string $nombre, string $marca, string $coleccion): string
{
    $paletas = [
        ['#BFB0A0','#8C7B68'],['#A8AAAE','#737880'],['#C4BC9C','#8C8468'],
        ['#A4B0A8','#607868'],['#9CB4BC','#547880'],['#B4A0A8','#806070'],
        ['#C8A8B4','#906070'],['#C0BCB8','#88837C'],
    ];
    [$c1, $c2] = $paletas[($producto_id - 1) % count($paletas)];
    $gid  = 'g' . $producto_id;
    $col  = htmlspecialchars($coleccion ?: 'PAKAL', ENT_XML1);
    $mrc  = htmlspecialchars($marca, ENT_XML1);
    $words = explode(' ', $nombre);
    $half  = (int) ceil(count($words) / 2);
    $l1 = htmlspecialchars(implode(' ', array_slice($words, 0, $half)), ENT_XML1);
    $l2 = htmlspecialchars(implode(' ', array_slice($words, $half)), ENT_XML1);

    $s  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $s .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 750">' . "\n";
    $s .= "<defs><linearGradient id=\"$gid\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\">";
    $s .= "<stop offset=\"0%\" stop-color=\"$c1\"/><stop offset=\"100%\" stop-color=\"$c2\"/>";
    $s .= "</linearGradient></defs>\n";
    $s .= "<rect width=\"600\" height=\"750\" fill=\"url(#$gid)\"/>\n";
    $s .= "<rect x=\"40\" y=\"40\" width=\"520\" height=\"670\" fill=\"none\" stroke=\"rgba(255,255,255,0.22)\" stroke-width=\"1\"/>\n";
    $s .= "<g transform=\"translate(300,300)\">";
    $s .= "<polygon points=\"0,-90 65,0 0,90 -65,0\" fill=\"none\" stroke=\"rgba(255,255,255,0.30)\" stroke-width=\"1.5\"/>";
    $s .= "<polygon points=\"0,-55 40,0 0,55 -40,0\" fill=\"none\" stroke=\"rgba(255,255,255,0.18)\" stroke-width=\"1\"/>";
    $s .= "<circle cx=\"0\" cy=\"0\" r=\"5\" fill=\"rgba(255,255,255,0.35)\"/>";
    $s .= "<line x1=\"0\" y1=\"-55\" x2=\"0\" y2=\"55\" stroke=\"rgba(255,255,255,0.12)\" stroke-width=\"1\"/>";
    $s .= "<line x1=\"-55\" y1=\"0\" x2=\"55\" y2=\"0\" stroke=\"rgba(255,255,255,0.12)\" stroke-width=\"1\"/>";
    $s .= "</g>\n";
    $s .= "<text x=\"300\" y=\"116\" font-family=\"Georgia,serif\" font-size=\"11\" letter-spacing=\"4\" fill=\"rgba(255,255,255,0.55)\" text-anchor=\"middle\">$col</text>\n";
    $s .= "<line x1=\"220\" y1=\"130\" x2=\"380\" y2=\"130\" stroke=\"rgba(255,255,255,0.22)\" stroke-width=\"0.8\"/>\n";
    $s .= "<text x=\"300\" y=\"558\" font-family=\"Georgia,serif\" font-size=\"27\" fill=\"rgba(255,255,255,0.92)\" text-anchor=\"middle\">$l1</text>\n";
    $s .= "<text x=\"300\" y=\"592\" font-family=\"Georgia,serif\" font-size=\"27\" fill=\"rgba(255,255,255,0.92)\" text-anchor=\"middle\">$l2</text>\n";
    $s .= "<line x1=\"200\" y1=\"614\" x2=\"400\" y2=\"614\" stroke=\"rgba(255,255,255,0.22)\" stroke-width=\"0.8\"/>\n";
    $s .= "<text x=\"300\" y=\"642\" font-family=\"Georgia,serif\" font-size=\"11\" letter-spacing=\"5\" fill=\"rgba(255,255,255,0.50)\" text-anchor=\"middle\">$mrc</text>\n";
    $s .= "</svg>\n";
    return $s;
}

// ──────────────────────────────────────────────────────────────
// FUNCIÓN: procesarImagen()
// ──────────────────────────────────────────────────────────────
// Maneja la imagen de un producto después de crearlo o editarlo.
//
// Lógica:
// 1. Si el admin subió un archivo de imagen → lo valida y guarda.
// 2. Si NO subió imagen y es un producto NUEVO → genera SVG automático.
// 3. Si NO subió imagen y es EDICIÓN → no hace nada (conserva la imagen actual).
//
// Seguridad en la carga de archivos:
// - Verifica el tipo MIME real del archivo (no confía en la extensión del nombre).
// - Limita el tamaño máximo a 5 MB.
// - Solo acepta formatos de imagen conocidos (jpg, png, gif, webp, svg).
//
// $solo_si_hay_archivo=true en edición: si el admin no subió imagen nueva,
// no tocamos la imagen existente del producto.
function procesarImagen(PDO $pdo, int $producto_id, string $slug, string $nombre, string $marca, string $coleccion, bool $solo_si_hay_archivo = false): void
{
    // Directorio donde se guardan las imágenes de productos.
    // dirname(__DIR__) sube un nivel desde "proceso/" hasta la raíz del proyecto.
    $dir = dirname(__DIR__) . '/assets/img/productos/';

    // Verificamos si el admin subió un archivo de imagen.
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Tipos de imagen aceptados: formato MIME → extensión de archivo.
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif',
                    'image/webp'=>'webp','image/svg+xml'=>'svg'];

        // finfo detecta el tipo MIME REAL del archivo (no confía en el nombre del archivo).
        // Un atacante podría renombrar un archivo .php a .jpg; finfo lo detectaría.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['imagen']['tmp_name']);  // tmp_name = archivo temporal
        finfo_close($finfo);

        // Validamos: tipo permitido Y tamaño máximo de 5 MB (5 * 1024 * 1024 bytes).
        if (isset($allowed[$mime]) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
            $filename = $slug . '.' . $allowed[$mime];  // Nombre final: slug-del-producto.jpg
            // move_uploaded_file mueve el archivo del directorio temporal al destino final.
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $dir . $filename)) {
                upsertImagen($pdo, $producto_id, $filename);  // Guardamos la referencia en BD
                return;  // Éxito: salimos de la función
            }
        }
    }

    // Si estamos editando y no se subió imagen nueva, conservamos la imagen actual.
    if ($solo_si_hay_archivo) return;

    // Producto nuevo sin imagen: generamos un SVG de placeholder automáticamente.
    $filename = $slug . '.svg';
    file_put_contents($dir . $filename, generarSVG($producto_id, $nombre, $marca, $coleccion));
    upsertImagen($pdo, $producto_id, $filename);
}

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "guardar" — Crear un nuevo producto
// ═══════════════════════════════════════════════════════════════
if ($action === 'guardar') {

    // ── Recoger y limpiar los datos del formulario ────────────────
    // str_replace(',', '.', ...) convierte "1,500.00" a "1500.00" por si el usuario usa coma decimal.
    // ?: null convierte string vacío en NULL de la base de datos (más limpio que string vacío).
    $nombre      = trim($_POST['nombre']      ?? '');
    $marca       = trim($_POST['marca']       ?? 'PAKAL');
    $coleccion   = trim($_POST['coleccion']   ?? '') ?: null;
    $cat_id      = (int)($_POST['categoria_id'] ?? 0);
    $precio      = (float) str_replace(',', '.', $_POST['precio'] ?? '0');
    $precio_reb  = !empty($_POST['precio_rebaja'])
                   ? (float) str_replace(',', '.', $_POST['precio_rebaja']) : null;
    $stock       = (int)($_POST['stock']      ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $material    = trim($_POST['material']    ?? '') ?: null;
    // Validamos que el estado sea uno de los valores permitidos en la BD.
    $estado      = in_array($_POST['estado'] ?? '', ['activo','borrador','agotado'])
                   ? $_POST['estado'] : 'activo';
    // El badge es la etiqueta que aparece sobre la imagen: "Nuevo", "Rebaja", "Exclusivo".
    $badge       = in_array($_POST['badge'] ?? '', ['nuevo','rebaja','exclusivo',''])
                   ? $_POST['badge'] : '';
    // El campo "destacado" es un checkbox: '1' si marcado, '0' si no.
    $destacado   = ($_POST['destacado'] ?? '0') === '1' ? 1 : 0;

    if (empty($nombre) || $cat_id <= 0 || $precio <= 0) {
        flash('error', 'Nombre, categoría y precio son obligatorios.');
        header('Location: ../admin.php?seccion=nuevo-producto');
        exit;
    }

    $slug = slugUnico($pdo, slugify($nombre));

    try {
        $pdo->prepare(
            'INSERT INTO productos
             (nombre, slug, marca, coleccion, descripcion, material,
              precio, precio_rebaja, categoria_id, stock, badge, destacado, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $nombre, $slug, $marca, $coleccion, $descripcion, $material,
            $precio, $precio_reb, $cat_id, $stock, $badge, $destacado, $estado,
        ]);

        $nuevo_id = (int) $pdo->lastInsertId();
        procesarImagen($pdo, $nuevo_id, $slug, $nombre, $marca, $coleccion ?? '');
        rotarCSRF();
        flash('success', 'Producto "' . htmlspecialchars($nombre) . '" guardado correctamente.');
    } catch (PDOException $e) {
        flash('error', 'Error al guardar el producto. Inténtalo de nuevo.');
    }

    header('Location: ../admin.php?seccion=productos');
    exit;
}

// ── Actualizar producto existente ────────────────────────────
if ($action === 'actualizar') {

    $producto_id = (int)($_POST['producto_id'] ?? 0);
    if ($producto_id <= 0) {
        flash('error', 'Producto no válido.');
        header('Location: ../admin.php?seccion=productos');
        exit;
    }

    $nombre      = trim($_POST['nombre']      ?? '');
    $marca       = trim($_POST['marca']       ?? 'PAKAL');
    $coleccion   = trim($_POST['coleccion']   ?? '') ?: null;
    $cat_id      = (int)($_POST['categoria_id'] ?? 0);
    $precio      = (float) str_replace(',', '.', $_POST['precio'] ?? '0');
    $precio_reb  = !empty($_POST['precio_rebaja'])
                   ? (float) str_replace(',', '.', $_POST['precio_rebaja']) : null;
    $stock       = (int)($_POST['stock']      ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;
    $material    = trim($_POST['material']    ?? '') ?: null;
    $estado      = in_array($_POST['estado'] ?? '', ['activo','borrador','agotado'])
                   ? $_POST['estado'] : 'activo';
    $badge       = in_array($_POST['badge'] ?? '', ['nuevo','rebaja','exclusivo',''])
                   ? $_POST['badge'] : '';
    $destacado   = ($_POST['destacado'] ?? '0') === '1' ? 1 : 0;

    if (empty($nombre) || $cat_id <= 0 || $precio <= 0) {
        flash('error', 'Nombre, categoría y precio son obligatorios.');
        header('Location: ../admin.php?seccion=editar-producto&id=' . $producto_id);
        exit;
    }

    // Obtener slug actual
    $stmt_slug = $pdo->prepare('SELECT slug FROM productos WHERE id = ?');
    $stmt_slug->execute([$producto_id]);
    $slug_actual = $stmt_slug->fetchColumn();
    $slug_nuevo  = slugify($nombre);

    // Solo actualizar slug si el nombre cambió
    $slug = ($slug_nuevo === slugify(/* nombre original */ $slug_actual))
            ? $slug_actual
            : slugUnico($pdo, $slug_nuevo, $producto_id);

    // Forzar slug desde el campo para mantenerlo igual si es posible
    $slug = $slug_actual; // Preservar slug original para no romper URLs

    try {
        $pdo->prepare(
            'UPDATE productos SET
               nombre = ?, marca = ?, coleccion = ?, descripcion = ?, material = ?,
               precio = ?, precio_rebaja = ?, categoria_id = ?, stock = ?,
               badge = ?, destacado = ?, estado = ?
             WHERE id = ?'
        )->execute([
            $nombre, $marca, $coleccion, $descripcion, $material,
            $precio, $precio_reb, $cat_id, $stock,
            $badge, $destacado, $estado,
            $producto_id,
        ]);

        // Solo actualizar imagen si se subió una nueva
        procesarImagen($pdo, $producto_id, $slug_actual, $nombre, $marca, $coleccion ?? '', true);
        rotarCSRF();
        flash('success', 'Producto "' . htmlspecialchars($nombre) . '" actualizado correctamente.');
    } catch (PDOException $e) {
        flash('error', 'Error al actualizar el producto.');
    }

    header('Location: ../admin.php?seccion=productos');
    exit;
}

// ═══════════════════════════════════════════════════════════════
// ACCIÓN: "eliminar" — Borrar un producto
// ═══════════════════════════════════════════════════════════════
if ($action === 'eliminar') {

    $producto_id = (int)($_POST['producto_id'] ?? 0);
    if ($producto_id <= 0) {
        flash('error', 'Producto no válido.');
        header('Location: ../admin.php?seccion=productos');
        exit;
    }

    // ── Verificar que no haya pedidos activos con este producto ───
    // No podemos borrar un producto que está en un pedido pendiente, confirmado o enviado,
    // porque el cliente lo está esperando. Solo se puede eliminar si todos sus pedidos
    // están ya completados (entregado) o cancelados.
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM pedido_items pi
         JOIN pedidos p ON pi.pedido_id = p.id
         WHERE pi.producto_id = ? AND p.estado NOT IN ("cancelado","entregado")'
    );
    $stmt->execute([$producto_id]);
    $n_activos = (int) $stmt->fetchColumn();
    if ($n_activos > 0) {
        flash('warning', "No puedes eliminar este producto: tiene $n_activos pedido(s) activo(s). Ve a Pedidos, cámbialos a «Entregado» o «Cancelado» y vuelve a intentarlo.");
        header('Location: ../admin.php?seccion=pedidos');
        exit;
    }

    try {
        // Obtener imagen para borrar archivo
        $stmt_img = $pdo->prepare('SELECT ruta FROM producto_imagenes WHERE producto_id = ? AND orden = 0 LIMIT 1');
        $stmt_img->execute([$producto_id]);
        $ruta_img = $stmt_img->fetchColumn();

        $pdo->prepare('DELETE FROM productos WHERE id = ?')->execute([$producto_id]);

        if ($ruta_img) {
            $archivo = dirname(__DIR__) . '/assets/img/productos/' . $ruta_img;
            if (file_exists($archivo)) @unlink($archivo);
        }

        rotarCSRF();
        flash('success', 'Producto eliminado correctamente.');
    } catch (PDOException $e) {
        flash('error', 'Error al eliminar el producto.');
    }

    header('Location: ../admin.php?seccion=productos');
    exit;
}

header('Location: ../admin.php');
exit;
