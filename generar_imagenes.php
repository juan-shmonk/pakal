<?php
// Script de utilidad — corre una sola vez para generar las imágenes SVG de productos
// Accede a: http://localhost/proyecto/generar_imagenes.php

require_once __DIR__ . '/config/database.php';

$pdo = getPDO();
$productos = $pdo->query(
    'SELECT id, slug, nombre, marca, coleccion FROM productos ORDER BY id'
)->fetchAll();

// Paleta por ID (cíclica si hay más productos)
$paletas = [
    1 => ['#BFB0A0', '#8C7B68', '#4A3B2C'],
    2 => ['#A8AAAE', '#737880', '#2C2F35'],
    3 => ['#C4BC9C', '#8C8468', '#3C3420'],
    4 => ['#A4B0A8', '#607868', '#283430'],
    5 => ['#9CB4BC', '#547880', '#1C3C44'],
    6 => ['#B4A0A8', '#806070', '#3C202C'],
    7 => ['#C8A8B4', '#906070', '#3C1828'],
    8 => ['#C0BCB8', '#88837C', '#343028'],
];

$dir = __DIR__ . '/assets/img/productos/';
$generados = [];

foreach ($productos as $prod) {
    $idx = (($prod['id'] - 1) % count($paletas)) + 1;
    [$c1, $c2, $texto] = $paletas[$idx];

    $nombre     = htmlspecialchars($prod['nombre'], ENT_XML1);
    $coleccion  = htmlspecialchars($prod['coleccion'] ?? 'PAKAL', ENT_XML1);
    $marca      = htmlspecialchars($prod['marca'], ENT_XML1);
    $grad_id    = 'g' . $prod['id'];

    // Dividir nombre en dos líneas si es largo
    $words  = explode(' ', $prod['nombre']);
    $half   = (int) ceil(count($words) / 2);
    $line1  = htmlspecialchars(implode(' ', array_slice($words, 0, $half)), ENT_XML1);
    $line2  = htmlspecialchars(implode(' ', array_slice($words, $half)), ENT_XML1);

    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 750" width="600" height="750">
  <defs>
    <linearGradient id="{$grad_id}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%"   stop-color="{$c1}"/>
      <stop offset="100%" stop-color="{$c2}"/>
    </linearGradient>
  </defs>

  <!-- Fondo -->
  <rect width="600" height="750" fill="url(#{$grad_id})"/>

  <!-- Líneas decorativas mayas -->
  <rect x="40"  y="40"  width="520" height="670" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="1"/>
  <rect x="55"  y="55"  width="490" height="640" fill="none" stroke="rgba(255,255,255,0.10)" stroke-width="1"/>

  <!-- Icono central (rombo maya) -->
  <g transform="translate(300,310)">
    <polygon points="0,-80 60,0 0,80 -60,0"
             fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
    <polygon points="0,-50 38,0 0,50 -38,0"
             fill="none" stroke="rgba(255,255,255,0.20)" stroke-width="1"/>
    <circle cx="0" cy="0" r="6" fill="rgba(255,255,255,0.30)"/>
    <!-- Cruz interna -->
    <line x1="0" y1="-50" x2="0" y2="50" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
    <line x1="-50" y1="0" x2="50" y2="0"  stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
  </g>

  <!-- Colección (arriba) -->
  <text x="300" y="120"
        font-family="Georgia, serif" font-size="11" letter-spacing="4"
        fill="rgba(255,255,255,0.60)" text-anchor="middle"
        transform="uppercase">{$coleccion}</text>

  <!-- Divisor superior -->
  <line x1="220" y1="134" x2="380" y2="134" stroke="rgba(255,255,255,0.25)" stroke-width="0.8"/>

  <!-- Nombre del producto -->
  <text x="300" y="560"
        font-family="Georgia, serif" font-size="28" font-weight="400"
        fill="rgba(255,255,255,0.90)" text-anchor="middle">{$line1}</text>
  <text x="300" y="595"
        font-family="Georgia, serif" font-size="28" font-weight="400"
        fill="rgba(255,255,255,0.90)" text-anchor="middle">{$line2}</text>

  <!-- Divisor inferior -->
  <line x1="200" y1="618" x2="400" y2="618" stroke="rgba(255,255,255,0.25)" stroke-width="0.8"/>

  <!-- Marca -->
  <text x="300" y="645"
        font-family="Georgia, serif" font-size="11" letter-spacing="5"
        fill="rgba(255,255,255,0.55)" text-anchor="middle">{$marca}</text>
</svg>
SVG;

    $ruta = $dir . $prod['slug'] . '.svg';
    file_put_contents($ruta, $svg);
    $generados[] = $prod['slug'] . '.svg';
}

// Actualizar producto_imagenes en BD
$pdo->exec('DELETE FROM producto_imagenes');
$stmt = $pdo->prepare(
    'INSERT INTO producto_imagenes (producto_id, ruta, orden) VALUES (?, ?, 0)'
);
foreach ($productos as $prod) {
    $stmt->execute([$prod['id'], $prod['slug'] . '.svg']);
}

echo '<pre style="font-family:monospace;padding:24px;">';
echo "✓ Imágenes generadas (" . count($generados) . "):\n\n";
foreach ($generados as $f) {
    echo "  assets/img/productos/$f\n";
}
echo "\n✓ Tabla producto_imagenes actualizada.\n";
echo "\nPuedes eliminar este archivo después de ejecutarlo.\n";
echo '</pre>';
