-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 28-11-2025 a las 15:55:18
-- Versión del servidor: 10.11.14-MariaDB-cll-lve-log
-- Versión de PHP: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fullgasop_adornos`
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
(4, 'Sistemas'),
(6, 'Nominas'),
(7, 'Fiscal');

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
(11, '', '1764199710_3.png', 1, 1, '2025-11-26 23:28:30', 'NB-03', 1),
(12, '', '1764200500_NB-078.png', 1, 1, '2025-11-26 23:41:40', 'NB-78', 1),
(13, '', '1764201407_NB-79.png', 3, 3, '2025-11-26 23:56:47', 'NB-79', 1),
(14, '', '1764257193_NB-53.png', 1, 1, '2025-11-27 15:26:33', 'NB-53', 1),
(15, 'PEGATINAS PARA VENTANAS CRISTAL', '1764257219_Imagen3111.png', 1, 1, '2025-11-27 15:26:59', 'NB-01A', 1),
(16, 'PEGATINAS PARA VENTANAS CRISTAL', '1764257287_Imagen32222222.png', 1, 1, '2025-11-27 15:28:07', 'NB-01B', 1),
(17, '', '1764257479_Imagen333333333.png', 1, 1, '2025-11-27 15:31:19', 'NB-01C', 1),
(18, 'PEGATINAS PARA VENTANAS CRISTAL', '1764257525_Imagen4.png', 1, 1, '2025-11-27 15:32:05', 'NB-02A', 1),
(19, '', '1764257556_Imagen5.png', 1, 1, '2025-11-27 15:32:36', 'NB-02B', 1),
(20, '', '1764258935_NB-54.png', 6, 6, '2025-11-27 15:55:35', 'NB-54', 1),
(21, '', '1764259064_NB-55.png', 1, 1, '2025-11-27 15:57:44', 'NB-55', 1),
(22, '', '1764259781_NB_28.png', 1, 1, '2025-11-27 16:09:41', 'NB-28', 1),
(23, '', '1764259802_NB_29.png', 1, 1, '2025-11-27 16:10:02', 'NB-29', 1),
(24, '', '1764259827_NB_30.jpg', 1, 1, '2025-11-27 16:10:27', 'NB-30', 1),
(25, '', '1764259854_NB_31.jpg', 1, 1, '2025-11-27 16:10:54', 'NB-31', 1),
(26, '', '1764260097_NB_32.jpg', 1, 1, '2025-11-27 16:14:57', 'NB-32', 1),
(27, '', '1764262132_NB.jpg', 1, 1, '2025-11-27 16:16:02', 'NB-33', 1),
(29, '10 PIEZAS', '1764260239_NB_35.jpg', 1, 1, '2025-11-27 16:17:19', 'NB-35', 1),
(30, '10 PIEZAS', '1764260361_NB_36.jpg', 1, 1, '2025-11-27 16:19:21', 'NB-36', 1),
(32, '', '1764260433_Imagen7.png', 1, 1, '2025-11-27 16:20:33', 'NB-04', 1),
(34, '', '1764260466_NB_39.jpg', 1, 1, '2025-11-27 16:21:06', 'NB-39', 1),
(35, '', '1764260490_Imagen8.png', 1, 1, '2025-11-27 16:21:30', 'NB-05', 1),
(36, '', '1764260499_NB_40A_40B.png', 1, 1, '2025-11-27 16:21:39', 'NB-40', 1),
(37, '', '1764260523_NB_41.jpg', 1, 1, '2025-11-27 16:22:03', 'NB-41', 1),
(38, '', '1764260550_NB_42.jpg', 1, 1, '2025-11-27 16:22:30', 'NB-42', 1),
(39, '', '1764260590_NB_43.jpg', 1, 1, '2025-11-27 16:23:10', 'NB-43', 1),
(40, '', '1764260621_NB_44A_44B.jpg', 2, 2, '2025-11-27 16:23:41', 'NB-44', 1),
(41, '', '1764260646_NB_45.png', 1, 1, '2025-11-27 16:24:06', 'NB-45', 1),
(42, '', '1764260675_NB_46.jpg', 1, 1, '2025-11-27 16:24:35', 'NB-46', 1),
(43, '', '1764260691_Imagen9111.png', 1, 1, '2025-11-27 16:24:51', 'NB-06A', 1),
(44, '', '1764260759_NB_47.jpg', 1, 1, '2025-11-27 16:25:59', 'NB-47', 1),
(45, '', '1764260765_Imagen9222.png', 1, 1, '2025-11-27 16:26:05', 'NB-06B', 1),
(46, '', '1764260780_NB_48.jpg', 1, 1, '2025-11-27 16:26:20', 'NB-48', 1),
(47, '', '1764260829_NB_49A_49B_49C.png', 3, 3, '2025-11-27 16:27:09', 'NB-49A-49B-49C', 1),
(48, '', '1764260836_Imagen10.png', 1, 1, '2025-11-27 16:27:16', 'NB-07', 1),
(49, '', '1764260858_NB_50A_50B.jpg', 2, 2, '2025-11-27 16:27:38', 'NB-50A-50B', 1),
(50, '', '1764260881_NB_51.jpg', 1, 1, '2025-11-27 16:28:01', 'NB-51', 1),
(51, '', '1764260900_NB_52.jpg', 1, 1, '2025-11-27 16:28:20', 'NB-52', 1),
(52, '', '1764260920_Imagen1.png', 1, 1, '2025-11-27 16:28:40', 'NB-08', 1),
(53, '', '1764260951_Imagen11.png', 2, 2, '2025-11-27 16:29:11', 'NB-09', 1),
(54, '', '1764261986_Imagen12.png', 1, 1, '2025-11-27 16:46:26', 'NB-10', 1),
(55, '', '1764262054_Imagen13.png', 1, 1, '2025-11-27 16:47:34', 'NB-11', 1),
(56, 'Color Plateado', '1764262092_NB-80.png', 3, 3, '2025-11-27 16:48:12', 'NB-80A', 1),
(57, 'ROJO', '1764262104_Imagen14.png', 1, 1, '2025-11-27 16:48:24', 'NB-12A', 1),
(58, 'VERDE', '1764262133_Imagen14.png', 1, 1, '2025-11-27 16:48:53', 'NB-12B', 1),
(59, 'Color Dorado', '1764262140_NB-80.png', 1, 1, '2025-11-27 16:49:00', 'NB-80B', 1),
(60, '', '1764262161_NB-81.png', 1, 1, '2025-11-27 16:49:21', 'NB-81', 1),
(61, '', '1764262182_Imagen15.png', 7, 7, '2025-11-27 16:49:42', 'NB-13', 1),
(62, '', '1764262217_NB-82.png', 3, 3, '2025-11-27 16:50:17', 'NB-82', 1),
(63, '', '1764262254_Imagen16.png', 1, 1, '2025-11-27 16:50:54', 'NB-14', 1),
(64, '', '1764262257_NB-83.png', 5, 5, '2025-11-27 16:50:57', 'NB-83', 1),
(65, '', '1764262282_NB-84.png', 1, 1, '2025-11-27 16:51:22', 'NB-84', 1),
(66, '', '1764262297_Imagen17.png', 6, 6, '2025-11-27 16:51:37', 'NB-15', 1),
(67, '', '1764262302_NB-85.png', 2, 2, '2025-11-27 16:51:42', 'NB-85', 1),
(68, '', '1764262347_NB-86.png', 2, 2, '2025-11-27 16:52:27', 'NB-86', 1),
(69, '', '1764262372_NB-87.png', 1, 1, '2025-11-27 16:52:52', 'NB-87', 1),
(70, '', '1764262385_Imagen18.png', 6, 6, '2025-11-27 16:53:05', 'NB-16', 1),
(71, '', '1764262389_NB-88.png', 1, 1, '2025-11-27 16:53:09', 'NB-88', 1),
(72, '', '1764262419_NB-89.png', 1, 1, '2025-11-27 16:53:39', 'NB-89', 1),
(73, '', '1764262437_Imagen19.png', 8, 8, '2025-11-27 16:53:57', 'NB-17', 1),
(74, '', '1764262437_NB-90.png', 1, 1, '2025-11-27 16:53:57', 'NB-90', 1),
(75, '', '1764262451_NB-91.png', 1, 1, '2025-11-27 16:54:11', 'NB-91', 1),
(76, '', '1764262467_NB-92.png', 1, 1, '2025-11-27 16:54:27', 'NB-92', 1),
(77, '', '1764262487_NB-93.png', 1, 1, '2025-11-27 16:54:47', 'NB-93', 1),
(79, '', '1764262601_Imagen2.png', 1, 1, '2025-11-27 16:56:41', 'NB-19', 1),
(80, '', '1764262614_NB-94.png', 1, 1, '2025-11-27 16:56:54', 'NB-94', 1),
(81, 'NO TIENE ESTRELLAS', '1764262650_Imagen21.png', 2, 2, '2025-11-27 16:57:30', 'NB-20', 1),
(82, '', '1764262729_Imagen22.png', 1, 1, '2025-11-27 16:58:49', 'NB-21', 1),
(83, '', '1764262806_NB_27.jpg', 1, 1, '2025-11-27 17:00:06', 'NB-27', 1),
(84, 'SIN LUCES', '1764262845_Imagen23.png', 1, 1, '2025-11-27 17:00:45', 'NB-22', 1),
(85, '', '1764262941_Imagen24.png', 8, 8, '2025-11-27 17:02:21', 'NB-23', 1),
(86, 'ARBOLITOS Y RENOS', '1764263029_Imagen25.png', 1, 1, '2025-11-27 17:03:49', 'NB-24A', 1),
(87, 'ESFERAS', '1764263057_Imagen25.png', 1, 1, '2025-11-27 17:04:17', 'NB-24B', 1),
(88, 'LETRAS', '1764263132_Imagen25.png', 1, 1, '2025-11-27 17:05:32', 'NB-24C', 1),
(89, '', '1764263212_Imagen26.png', 1, 1, '2025-11-27 17:06:52', 'NB-25', 1),
(90, '', '1764263252_Imagen27.png', 1, 1, '2025-11-27 17:07:32', 'NB-26', 1),
(91, '', '1764263913_NB-56.png', 1, 1, '2025-11-27 17:18:33', 'NB-56', 1),
(93, '', '1764263972_NB-58.png', 1, 1, '2025-11-27 17:19:32', 'NB-58', 1),
(94, '', '1764264277_NB-59.png', 1, 1, '2025-11-27 17:24:37', 'NB-59', 1),
(95, '', '1764264369_NB-60.png', 1, 1, '2025-11-27 17:26:09', 'NB-60', 1),
(96, '', '1764264424_NB-61.png', 1, 1, '2025-11-27 17:27:04', 'NB-61', 1),
(97, '', '1764264446_NB-62.png', 1, 1, '2025-11-27 17:27:26', 'NB-62', 1),
(98, '', '1764264478_NB-63.png', 1, 1, '2025-11-27 17:27:58', 'NB-63', 1),
(99, '', '1764264516_NB-64.png', 1, 1, '2025-11-27 17:28:36', 'NB-64', 1),
(100, '', '1764264571_NB-65.png', 1, 1, '2025-11-27 17:29:31', 'NB-65', 1),
(101, '', '1764264597_NB-66.png', 1, 1, '2025-11-27 17:29:57', 'NB-66', 1),
(102, '1.7 metros', '1764264713_NB-67.png', 1, 1, '2025-11-27 17:31:53', 'NB-67', 1),
(103, '', '1764264737_NB-68.png', 1, 1, '2025-11-27 17:32:17', 'NB-68', 1),
(104, '', '1764264807_NB-69.png', 1, 1, '2025-11-27 17:33:27', 'NB-69', 1),
(105, '', '1764264890_NB-70.png', 1, 1, '2025-11-27 17:34:50', 'NB-70', 1),
(106, '', '1764265163_NB-71.png', 1, 1, '2025-11-27 17:39:23', 'NB-71', 1),
(107, '', '1764265260_NB-72.png', 1, 1, '2025-11-27 17:41:00', 'NB-72', 1),
(108, '', '1764265427_NB-73.png', 1, 1, '2025-11-27 17:43:47', 'NB-73', 1),
(109, '', '1764265713_NB-74.png', 1, 1, '2025-11-27 17:48:33', 'NB-74', 1),
(110, '', '1764266341_NB-75.png', 1, 1, '2025-11-27 17:59:01', 'NB-75', 1),
(111, '', '1764266475_NB-76.png', 1, 1, '2025-11-27 18:01:15', 'NB-76', 1),
(112, '', '1764266583_NB-77.png', 1, 1, '2025-11-27 18:03:03', 'NB-77', 1),
(113, '', '1764267342_G-1.png', 1, 1, '2025-11-27 18:15:42', 'G-01', 1),
(114, '', '1764267641_G-2.png', 1, 1, '2025-11-27 18:20:41', 'G-02', 1),
(115, '', '1764267797_G-3.png', 1, 1, '2025-11-27 18:23:17', 'G-03', 1),
(117, '', '1764269122_G-5.png', 1, 1, '2025-11-27 18:45:22', 'G-05', 1),
(119, '', '1764269176_G-7.png', 1, 1, '2025-11-27 18:46:16', 'G-07', 1),
(120, '', '1764269220_G-8.png', 1, 1, '2025-11-27 18:47:00', 'G-08', 1),
(121, '', '1764269240_G-9.png', 1, 1, '2025-11-27 18:47:20', 'G-09', 1),
(122, '', '1764269261_G-10.png', 1, 1, '2025-11-27 18:47:41', 'G-10', 1),
(123, '', '1764269287_G-11.png', 1, 1, '2025-11-27 18:48:07', 'G-11', 1),
(124, '', '1764269302_G-12.png', 1, 1, '2025-11-27 18:48:22', 'G-12', 1),
(125, '', '1764269394_CMFP-1.png', 3, 3, '2025-11-27 18:49:54', 'M-01', 1),
(126, '', '1764269420_CMFP-2.png', 2, 3, '2025-11-27 18:50:20', 'M-02', 1),
(127, '', '1764269464_CMFP-3.png', 2, 2, '2025-11-27 18:51:04', 'M-03', 1),
(128, '', '1764269515_CMFP-4.png', 4, 4, '2025-11-27 18:51:55', 'M-04', 1),
(129, '', '1764269640_CMFP-5.png', 3, 3, '2025-11-27 18:54:00', 'M-05', 1),
(130, '', '1764269665_CMFP-6.png', 2, 2, '2025-11-27 18:54:25', 'M-06', 1),
(131, '', '1764269720_CMFP-7.png', 5, 5, '2025-11-27 18:55:20', 'M-07', 1),
(132, '', '1764269811_L-1.png', 1, 1, '2025-11-27 18:56:51', 'L-01', 1),
(133, '', '1764269901_L-2.png', 1, 1, '2025-11-27 18:58:21', 'L-02', 1),
(134, '', '1764269921_L-3.png', 1, 1, '2025-11-27 18:58:41', 'L-03', 1),
(135, 'CASCADA MULTICOLOR 300 FOCOS LED. CABLE TRANSPARENTE', '1764269956_L-4.png', 1, 1, '2025-11-27 18:59:16', 'L-04', 1),
(136, 'CASCADA LUZ BLANCA CALIDA 300 FOCOS LED. CABLE TRANSPARENTE', '1764270034_L-5.png', 1, 1, '2025-11-27 19:00:34', 'L-05', 1),
(139, '140 FOCOS LED MULTICOLOR . CABLE TRANSPARENTE', '1764270353_L-8.png', 1, 1, '2025-11-27 19:05:53', 'L-08', 1),
(140, '', '1764270430_L-9.png', 1, 1, '2025-11-27 19:07:10', 'L-09', 1),
(141, '', '1764270446_L-10.png', 1, 1, '2025-11-27 19:07:26', 'L-10', 1),
(143, 'LUCES CON PILAS PARA ARBOLITO DE NAVIDAD DE FIELTRO.', '1764270561_L-12.png', 1, 1, '2025-11-27 19:09:21', 'L-12', 1),
(144, 'CORTINA 8 FIGURAS NAVIDEÑAS. COLOR CALIDO. 3 METROS', '1764270580_L-13.png', 1, 1, '2025-11-27 19:09:40', 'L-13', 1),
(145, '', '1764270606_L-14.png', 1, 1, '2025-11-27 19:10:06', 'L-14', 1),
(146, 'MAGUERA MULTICOLOR 10 METROS', '1764270641_L-15.png', 1, 1, '2025-11-27 19:10:41', 'L-15', 1),
(147, '', '1764280805_NB-95.png', 1, 1, '2025-11-27 22:00:05', 'NB-95', 1),
(148, '', '1764280826_NB-96.png', 1, 1, '2025-11-27 22:00:26', 'NB-96', 1),
(149, '', '1764280858_NB-97.png', 1, 1, '2025-11-27 22:00:58', 'NB-97', 1),
(150, '', '1764280895_NB-98.png', 1, 1, '2025-11-27 22:01:35', 'NB-98', 1),
(151, '', '1764280938_NB-99.png', 1, 1, '2025-11-27 22:02:18', 'NB-99', 1),
(152, '', '1764280963_NB-100.png', 1, 1, '2025-11-27 22:02:43', 'NB-100', 1),
(153, '', '1764281035_NB-101.png', 1, 1, '2025-11-27 22:03:55', 'NB-101', 1),
(154, '', '1764281152_NB-102.png', 1, 1, '2025-11-27 22:05:52', 'NB-102', 1);

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
  `status` enum('reservado','en_proceso','finalizado','devuelto') DEFAULT 'reservado',
  `notes` text DEFAULT NULL,
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `picked_up_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 'fgomez', 'ggomez', 'Fernando Gomez', NULL, 'usuario', 2, '2025-11-22 15:56:26'),
(5, 'jtrejo', 'jtrejo', 'Jorge Trejo', NULL, 'usuario', 1, '2025-11-27 22:23:37'),
(6, 'achack', 'achack', 'Ana Chack', NULL, 'usuario', 6, '2025-11-28 21:50:49'),
(7, 'aguillen', 'aguillen', 'Antonio Guillen', NULL, 'usuario', 7, '2025-11-28 21:51:55'),
(8, 'isantiago', 'isantiago', 'Ismael Santiago', NULL, 'usuario', 4, '2025-11-28 21:53:13');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT de la tabla `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
