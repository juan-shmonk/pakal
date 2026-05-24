-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-05-2026 a las 20:52:59
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pakal_tienda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carritos`
--

CREATE TABLE `carritos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carritos`
--

INSERT INTO `carritos` (`id`, `usuario_id`, `creado_en`) VALUES
(1, 1, '2026-05-24 05:06:09'),
(2, 2, '2026-05-24 09:35:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_items`
--

CREATE TABLE `carrito_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `carrito_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito_items`
--

INSERT INTO `carrito_items` (`id`, `carrito_id`, `producto_id`, `cantidad`, `precio_unit`) VALUES
(2, 1, 11, 1, 50000.00),
(4, 2, 11, 1, 50000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `genero` enum('hombre','mujer','infantil','unisex') NOT NULL DEFAULT 'unisex',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `slug`, `genero`, `activo`) VALUES
(1, 'Blazers y Sacos', 'blazers-sacos', 'hombre', 1),
(2, 'Camisas', 'camisas', 'hombre', 1),
(3, 'Pantalones', 'pantalones', 'hombre', 1),
(4, 'Abrigos', 'abrigos', 'hombre', 1),
(5, 'Suéteres y Tejidos', 'sueteres', 'hombre', 1),
(6, 'Camisetas', 'camisetas', 'hombre', 1),
(7, 'Zapatos', 'zapatos', 'hombre', 1),
(8, 'Accesorios', 'accesorios', 'unisex', 1),
(9, 'Vestidos', 'vestidos', 'mujer', 1),
(10, 'Blusas', 'blusas', 'mujer', 1),
(11, 'Faldas', 'faldas', 'mujer', 1),
(12, 'Niño', 'nino', 'infantil', 1),
(13, 'Niña', 'nina', 'infantil', 1),
(14, 'Zapatos', 'zapatos-mujer', 'mujer', 1),
(15, 'Zapatos', 'zapatos-infantil', 'infantil', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `direccion` text NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','confirmado','enviado','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `nombre`, `email`, `telefono`, `direccion`, `total`, `estado`, `created_at`) VALUES
(2, 2, 'edson al', 'edson@gmail.com', '9988776655', 'region quintana roo, qroo', 50000.00, 'pendiente', '2026-05-24 09:35:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `producto_id`, `nombre`, `cantidad`, `precio_unit`) VALUES
(2, 2, 11, 'Camisa de Lino Chaak', 1, 50000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `slug` varchar(280) NOT NULL,
  `marca` varchar(100) NOT NULL DEFAULT 'PAKAL',
  `coleccion` varchar(150) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_rebaja` decimal(10,2) DEFAULT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `badge` enum('nuevo','rebaja','exclusivo','') NOT NULL DEFAULT '',
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('activo','borrador','agotado') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `slug`, `marca`, `coleccion`, `descripcion`, `material`, `precio`, `precio_rebaja`, `categoria_id`, `stock`, `badge`, `destacado`, `estado`, `created_at`, `updated_at`) VALUES
(11, 'Camisa de Lino Chaak', 'camisa-de-lino-chaak', 'PAKAL Heritage', NULL, 'La mejor ropa de moda hecha por años de culturas que han transcendido hasta nuestro momento. Cómodas y de lujo.', '70% lino 30%  seda', 50000.00, NULL, 2, 9, 'nuevo', 1, 'activo', '2026-05-24 09:28:46', '2026-05-24 09:35:53'),
(12, 'Blusa tipo Huipil Ceyba', 'blusa-tipo-huipil-ceyba', 'PAKAL Femme', NULL, 'Inspirada en la elegancia de la cultura maya, diseñada para quienes buscan comodidad, frescura y un estilo artesanal sofisticado. Confeccionado en tono beige natural, presenta bordados geométricos en colores turquesa, verde jade, dorado y arena, ubicados en el pecho, mangas y borde inferior.\r\n\r\nSu diseño tipo túnica con mangas 3/4 y cuello abierto le da un toque fresco, cómodo y versátil, ideal para climas cálidos, salidas casuales, eventos culturales o looks de inspiración bohemia. La textura tipo lino aporta ligereza y una apariencia natural, mientras que los detalles bordados realzan su carácter único y elegante.', '%30 lino 70% algodón', 32000.00, NULL, 10, 6, 'nuevo', 1, 'activo', '2026-05-24 10:05:03', '2026-05-24 10:05:03'),
(31, 'Alpargatas Artesanales Ixchel', 'alpargatas-artesanales-ixchel', 'PAKAL Femme', NULL, 'Las Alpargatas Artesanales Ixchel combinan comodidad, frescura y un diseño inspirado en la riqueza visual de la cultura maya. Elaboradas en tono beige natural, destacan por sus bordados geométricos y detalles artesanales en colores turquesa, verde jade y arena, que aportan un estilo auténtico y elegante.\r\n\r\nSu diseño tipo alpargata las hace ideales para uso diario, paseos, eventos casuales o looks bohemios con un toque cultural. La textura tipo lino y la suela con acabado artesanal brindan una apariencia natural, ligera y versátil, perfecta para complementar outfits frescos y sofisticados.', '90% cuero 10%nilo', 13000.00, NULL, 14, 3, 'exclusivo', 1, 'activo', '2026-05-24 10:40:54', '2026-05-24 10:40:54'),
(32, 'Alpargatas Mayab para Hombre', 'alpargatas-mayab-para-hombre', 'PAKAL Origin', NULL, 'Las Alpargatas Mayab para Hombre son un calzado casual inspirado en la estética artesanal maya, pensado para hombres que buscan un estilo fresco, cómodo y con identidad cultural. Su diseño combina una silueta masculina tipo loafer/alpargata con bordados geométricos en tonos turquesa, arena y dorado, evocando patrones tradicionales mesoamericanos de forma elegante y moderna.\r\n\r\nSon ideales para usarse en climas cálidos, salidas casuales, eventos culturales, vacaciones, outfits de lino, pantalón claro o ropa de estilo artesanal premium.', '%80 hilo de algodón %20 goma', 5000.00, NULL, 7, 14, 'nuevo', 1, 'activo', '2026-05-24 17:51:04', '2026-05-24 17:51:04'),
(33, 'Camisa Infantil Mayab', 'camisa-infantil-mayab', 'PAKAL Kids', NULL, 'La Camisa Infantil Mayab es una prenda fresca y cómoda para niños, inspirada en diseños artesanales de estilo maya. Su color beige natural y sus bordados geométricos en tonos turquesa, verde jade y dorado le dan un toque cultural, elegante y tierno.\r\n\r\nCuenta con manga corta, botones frontales y un pequeño bolsillo bordado, ideal para eventos familiares, ocasiones especiales, sesiones de fotos, celebraciones culturales o uso casual en clima cálido.', 'Mezcla de lino y algodón', 9999.98, NULL, 12, 0, 'nuevo', 1, 'activo', '2026-05-24 17:58:30', '2026-05-24 17:58:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_imagenes`
--

CREATE TABLE `producto_imagenes` (
  `id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `orden` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_imagenes`
--

INSERT INTO `producto_imagenes` (`id`, `producto_id`, `ruta`, `orden`) VALUES
(10, 11, 'camisa-de-lino-chaak.png', 0),
(11, 12, 'blusa-tipo-huipil-ceyba.png', 0),
(12, 31, 'alpargatas-artesanales-ixchel.png', 0),
(13, 32, 'alpargatas-mayab-para-hombre.png', 0),
(14, 33, 'camisa-infantil-mayab.png', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(25) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `newsletter` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `telefono`, `password_hash`, `rol`, `activo`, `newsletter`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'PAKAL', 'admin@pakal.com', NULL, '$2y$12$fQhxB3lKxnitvumrnfzete0GZtWF8BR6BxaI2llcMzIeYHqV6ozHe', 'admin', 1, 0, '2026-05-24 05:02:20', '2026-05-24 17:48:10'),
(2, 'edson', 'al', 'edson@gmail.com', '9988776655', '$2y$12$ljsqg/Wx3Uuo8GCdIxh7x.WH.rdw9D0nkow6740fxaeQmqaAbhbsm', 'cliente', 1, 1, '2026-05-24 09:34:58', '2026-05-24 09:54:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wishlist`
--

CREATE TABLE `wishlist` (
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `wishlist`
--

INSERT INTO `wishlist` (`usuario_id`, `producto_id`, `created_at`) VALUES
(2, 11, '2026-05-24 09:50:27');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario` (`usuario_id`);

--
-- Indices de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ci_car` (`carrito_id`),
  ADD KEY `fk_ci_prod` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_slug` (`slug`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pi_ped` (`pedido_id`),
  ADD KEY `fk_pi_prod` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_slug` (`slug`),
  ADD KEY `idx_categoria` (`categoria_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_destacado` (`destacado`);

--
-- Indices de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`usuario_id`,`producto_id`),
  ADD KEY `fk_wish_prod` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carritos`
--
ALTER TABLE `carritos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD CONSTRAINT `fk_car_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD CONSTRAINT `fk_ci_car` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_ped_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `fk_pi_ped` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pi_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_prod_cat` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_imagenes`
--
ALTER TABLE `producto_imagenes`
  ADD CONSTRAINT `fk_img_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wish_prod` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wish_usr` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
