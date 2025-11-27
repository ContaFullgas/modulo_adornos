-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3307
-- Tiempo de generación: 24-11-2025 a las 18:21:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `adornos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `celebrations`
--

CREATE TABLE `celebrations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `celebrations`
--

INSERT INTO `celebrations` (`id`, `name`) VALUES
(3, 'Año Nuevo'),
(4, 'Día de Muertos'),
(2, 'Halloween'),
(1, 'Navidad'),
(5, 'Pascua');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departments`
--

INSERT INTO `departments` (`id`, `name`) VALUES
(1, 'Contabilidad'),
(2, 'Facturación'),
(4, 'contabilidad - sistemas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `total_quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `code` varchar(100) NOT NULL,
  `celebration_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `items`
--

INSERT INTO `items` (`id`, `description`, `image`, `total_quantity`, `available_quantity`, `created_at`, `code`, `celebration_id`) VALUES
(2, '', '1763592829_3.png', 1, 0, '2025-11-19 22:53:49', '3', 1),
(3, '', '1763595994_4.png', 2, 0, '2025-11-19 23:46:34', '4', 1),
(4, '', '1763596207_5.png', 1, 1, '2025-11-19 23:50:07', '5', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('reservado','devuelto') DEFAULT 'reservado',
  `notes` text DEFAULT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `picked_up_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservations`
--

INSERT INTO `reservations` (`id`, `item_id`, `dept_id`, `user_id`, `quantity`, `status`, `notes`, `reserved_at`, `picked_up_at`, `returned_at`) VALUES
(12, 2, 1, 1, 1, 'devuelto', '', '2025-11-20 22:54:45', NULL, '2025-11-20 16:54:54'),
(13, 2, 1, 1, 1, 'reservado', '', '2025-11-20 22:55:07', NULL, NULL),
(14, 3, 1, 1, 1, 'reservado', '', '2025-11-22 15:58:30', NULL, NULL),
(15, 3, 2, 1, 1, 'devuelto', '', '2025-11-22 15:58:40', NULL, '2025-11-22 10:18:52'),
(16, 4, 2, 1, 1, 'devuelto', '', '2025-11-22 16:18:28', NULL, '2025-11-22 10:20:07'),
(17, 3, 2, 4, 1, 'devuelto', '', '2025-11-22 16:19:18', NULL, '2025-11-24 10:13:11'),
(18, 3, 2, 4, 1, 'reservado', '', '2025-11-24 16:13:26', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `returned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `handled_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `returns`
--

INSERT INTO `returns` (`id`, `reservation_id`, `item_id`, `dept_id`, `quantity`, `notes`, `returned_at`, `handled_by`) VALUES
(12, 12, 2, 1, 1, '', '2025-11-20 22:54:54', 1),
(13, 15, 3, 2, 1, '', '2025-11-22 16:18:52', 1),
(14, 16, 4, 2, 1, '', '2025-11-22 16:20:07', 1),
(15, 17, 3, 2, 1, '', '2025-11-24 16:13:11', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('admin','usuario') NOT NULL DEFAULT 'usuario',
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `department_id`, `created_at`) VALUES
(1, 'admin', 'admin', 'Administrador', NULL, 'admin', NULL, '2025-11-19 21:42:25'),
(4, 'LEOZUL ', '123', 'Leonardo Dzul', NULL, 'usuario', 2, '2025-11-22 15:56:26');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `celebrations`
--
ALTER TABLE `celebrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_items_code` (`code`),
  ADD KEY `idx_items_celebration` (`celebration_id`);

--
-- Indices de la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `dept_id` (`dept_id`),
  ADD KEY `handled_by` (`handled_by`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `celebrations`
--
ALTER TABLE `celebrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_celebration` FOREIGN KEY (`celebration_id`) REFERENCES `celebrations` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_3` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_4` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
