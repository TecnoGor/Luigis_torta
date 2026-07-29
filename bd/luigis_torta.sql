-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-07-2026 a las 15:06:13
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `luigis_torta`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `activa` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`, `orden`, `activa`, `created_at`) VALUES
(1, 'Tortas Grandes', 'Tortas completas para celebraciones grandes, disponibles en 15 a 30 porciones', NULL, 1, 1, '2026-07-29 12:15:14'),
(2, 'Tortas Medianas', 'Tortas medianas ideales para reuniones familiares, de 8 a 12 porciones', NULL, 2, 1, '2026-07-29 12:15:14'),
(3, 'Tortas Pequeñas', 'Tortas individuales o para 2-4 personas', NULL, 3, 1, '2026-07-29 12:15:14'),
(4, 'Porciones', 'Porciones individuales de nuestras tortas y postres', NULL, 4, 1, '2026-07-29 12:15:14'),
(5, 'Dulces y Postres', 'Galletas, brownies, cupcakes, flan, gelatinas y más', NULL, 5, 1, '2026-07-29 12:15:14'),
(6, 'Tortas Grandes', 'Tortas completas para celebraciones grandes, disponibles en 15 a 30 porciones', NULL, 1, 1, '2026-07-29 12:52:47'),
(7, 'Tortas Medianas', 'Tortas medianas ideales para reuniones familiares, de 8 a 12 porciones', NULL, 2, 1, '2026-07-29 12:52:47'),
(8, 'Tortas Pequeñas', 'Tortas individuales o para 2-4 personas', NULL, 3, 1, '2026-07-29 12:52:47'),
(9, 'Porciones', 'Porciones individuales de nuestras tortas y postres', NULL, 4, 1, '2026-07-29 12:52:47'),
(10, 'Dulces y Postres', 'Galletas, brownies, cupcakes, flan, gelatinas y más', NULL, 5, 1, '2026-07-29 12:52:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_inventario`
--

CREATE TABLE `historial_inventario` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad_anterior` int(11) NOT NULL,
  `cantidad_nueva` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_inventario`
--

INSERT INTO `historial_inventario` (`id`, `producto_id`, `cantidad_anterior`, `cantidad_nueva`, `motivo`, `usuario_id`, `created_at`) VALUES
(1, 2, 3, 3, 'Ajuste manual', 1, '2026-07-29 13:01:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `unidad_medida` varchar(50) DEFAULT 'pieza',
  `disponible` tinyint(1) DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `imagen`, `stock`, `unidad_medida`, `disponible`, `destacado`, `created_at`, `updated_at`) VALUES
(1, 1, 'Torta de Chocolate Premium', 'Deliciosa torta de chocolate belga con ganache y decoración artesanal. 20 porciones.', 850.00, NULL, 5, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(2, 1, 'Torta de Vainilla Especial', 'Torta clásica de vainilla con betún de mantequilla y flores de azúcar. 20 porciones.', 780.00, NULL, 3, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(3, 1, 'Torta Tres Leches', 'Nuestra famosa torta tres leches con canela y merengue italiano. 20 porciones.', 900.00, NULL, 4, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(4, 2, 'Torta Red Velvet', 'Torta red velvet con frosting de queso crema. 10 porciones.', 550.00, NULL, 6, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(5, 2, 'Torta de Zanahoria', 'Torta de zanahoria con nuez y frosting de crema de queso. 10 porciones.', 500.00, NULL, 4, 'pieza', 1, 0, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(6, 3, 'Mini Pastel de Fresa', 'Pastel individual de fresa con crema chantilly.', 85.00, NULL, 15, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(7, 3, 'Mini Torta de Limón', 'Torcito de limón con merengue flambeado.', 80.00, NULL, 12, 'pieza', 1, 0, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(8, 4, 'Porción de Chocolate', 'Porción generosa de torta de chocolate con ganache.', 55.00, NULL, 30, 'porción', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(9, 4, 'Porción de Tres Leches', 'Porción de nuestra famosa torta tres leches.', 60.00, NULL, 25, 'porción', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(10, 4, 'Porción de Red Velvet', 'Porción de red velvet con frosting de queso crema.', 55.00, NULL, 20, 'porción', 1, 0, '2026-07-29 12:15:14', '2026-07-29 12:15:14'),
(11, 5, 'Cupcakes de Vainilla (6 pzas)', 'Cupcakes decorados de vainilla con buttercream.', 150.00, 'prod_6a69f0926357f.jpg', 20, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:22:42'),
(12, 5, 'Brownies de Chocolate (8 pzas)', 'Brownies intensos de chocolate con nuez.', 180.00, 'prod_6a69f06dc557b.jpg', 18, 'pieza', 1, 0, '2026-07-29 12:15:14', '2026-07-29 12:22:05'),
(13, 5, 'Flan Napolitano', 'Flan casero con caramelo y vainilla.', 120.00, 'prod_6a69f09e789db.jpg', 10, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:22:54'),
(14, 5, 'Gelatina de Mosaico', 'Gelatina decorada con leche condensada.', 95.00, 'prod_6a69f0be42bb9.jpg', 14, 'pieza', 1, 0, '2026-07-29 12:15:14', '2026-07-29 12:23:26'),
(15, 5, 'Galletas Decoradas (12 pzas)', 'Galletas de mantequilla decoradas con royal icing.', 200.00, 'prod_6a69f0b38b859.jpg', 10, 'pieza', 1, 1, '2026-07-29 12:15:14', '2026-07-29 12:23:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_sabores`
--

CREATE TABLE `producto_sabores` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_sabores`
--

INSERT INTO `producto_sabores` (`id`, `producto_id`, `nombre`, `precio`, `stock`, `imagen`, `orden`, `created_at`) VALUES
(1, 1, 'Chocolate Amargo', 850.00, 5, NULL, 1, '2026-07-29 12:31:47'),
(2, 1, 'Chocolate con Leche', 850.00, 8, NULL, 2, '2026-07-29 12:31:47'),
(3, 1, 'Chocolate Blanco', 900.00, 3, NULL, 3, '2026-07-29 12:31:47'),
(4, 5, 'Clßsica', 500.00, 10, NULL, 1, '2026-07-29 12:31:47'),
(5, 5, 'Con Nueces', 550.00, 6, NULL, 2, '2026-07-29 12:31:47'),
(6, 5, 'Light', 520.00, 4, NULL, 3, '2026-07-29 12:31:47'),
(7, 15, 'Chocolate', 20.00, 12, 'prod_6a69f9e6d680b.jpg', 0, '2026-07-29 13:02:30'),
(8, 15, 'Fresa', 13.00, 12, 'prod_6a69f9f7e78c2.png', 0, '2026-07-29 13:02:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL,
  `rol` enum('admin','editor') DEFAULT 'editor',
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nombre_completo`, `rol`, `activo`, `created_at`) VALUES
(1, 'admin', '$2y$10$wNCIhKn2n6N2Inmrpo5RWutgfVyFs11thLNnlqAYgfQ50ygtdZBO6', 'Administrador', 'admin', 1, '2026-07-29 12:15:14');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_inventario`
--
ALTER TABLE `historial_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `producto_sabores`
--
ALTER TABLE `producto_sabores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `historial_inventario`
--
ALTER TABLE `historial_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `producto_sabores`
--
ALTER TABLE `producto_sabores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `historial_inventario`
--
ALTER TABLE `historial_inventario`
  ADD CONSTRAINT `historial_inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `historial_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto_sabores`
--
ALTER TABLE `producto_sabores`
  ADD CONSTRAINT `producto_sabores_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
