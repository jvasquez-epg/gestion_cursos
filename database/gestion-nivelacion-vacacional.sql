-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-07-2025 a las 00:00:06
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
-- Base de datos: `gestion-nivelacion-vacacional`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones`
--

CREATE TABLE `asignaciones` (
  `id` bigint(20) NOT NULL,
  `curso_id` bigint(20) NOT NULL,
  `docente_id` bigint(20) NOT NULL,
  `periodo_id` bigint(20) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` bigint(20) NOT NULL,
  `malla_id` bigint(20) NOT NULL,
  `ciclo` int(11) NOT NULL,
  `codigo` text NOT NULL,
  `nombre` text NOT NULL,
  `creditos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id`, `malla_id`, `ciclo`, `codigo`, `nombre`, `creditos`) VALUES
(1, 1, 1, '131B10001', 'LENGUAJE, REDACCIÓN Y ORATORIA', 4),
(2, 1, 1, '131B10002', 'MATEMÁTICA', 4),
(3, 1, 1, '9010', 'BASQUETBOL', 1),
(4, 1, 1, '131B10003', 'INGLÉS BÁSICO I', 2),
(5, 1, 1, '9040', 'FUTBOL', 1),
(6, 1, 1, '131B10008', 'FILOSOFIA', 3),
(7, 1, 1, '131B10004', 'DERECHO CONSTITUCIONAL Y DERECHOS HUMANOS', 3),
(8, 1, 1, '131B10009', 'INTRODUCCION A LA INGENIERIA DE SISTEMAS DE INFORMACION', 3),
(9, 1, 1, '131B10010', 'INFORMATICA I', 2),
(10, 1, 2, '131B10012', 'CALCULO DIFERENCIAL', 4),
(11, 1, 2, '131B10007', 'INGLÉS BÁSICO II', 2),
(12, 1, 2, '131B10011', 'ALGEBRA LINEAL', 4),
(13, 1, 2, '131B10013', 'ALGORITMO Y ESTRUCTURA DE DATOS', 3),
(14, 1, 2, '131B10005', 'REALIDAD NACIONAL Y DESARROLLO REGIONAL AMAZÓNICO', 4),
(15, 1, 2, '131B10014', 'INFORMATICA II', 2),
(16, 1, 2, '9000', 'ATLETISMO', 1),
(17, 1, 2, '131B10006', 'METODOLOGÍA DE LA INVESTIGACIÓN CIENTÍFICA', 3),
(18, 1, 2, '9110', 'VOLEIBOL', 1),
(19, 1, 3, '131B10020', 'ECONOMIA', 3),
(20, 1, 3, '131B10016', 'CALCULO INTEGRAL', 4),
(21, 1, 3, '131B10021', 'ESTADISTICA Y PROBABILIDAD', 3),
(22, 1, 3, '131B10015', 'MATEMATICA DISCRETA', 3),
(23, 1, 3, '131B10018', 'LENGUAJE DE PROGRAMACION I', 3),
(24, 1, 3, '131B10017', 'FISICA', 4),
(25, 1, 3, '131B10019', 'BASE DE DATOS I', 3),
(26, 1, 4, '131B10023', 'FISICA ELECTRONICA', 4),
(27, 1, 4, '131B10027', 'ESTADISTICA INFERENCIAL', 3),
(28, 1, 4, '131B10028', 'INGLES TECNICO I', 2),
(29, 1, 4, '131B10025', 'BASE DE DATOS II', 3),
(30, 1, 4, '131B10026', 'ADMINISTRACION GENERAL', 3),
(31, 1, 4, '131B10022', 'ECUACIONES DIFERENCIALES', 4),
(32, 1, 4, '131B10024', 'LENGUAJE DE PROGRAMACION II', 3),
(33, 1, 5, '131B10029', 'ELECTRONICA DIGITAL', 3),
(34, 1, 5, '131B10040', 'TECNOLOGIA MULTIMEDIA', 2),
(35, 1, 5, '131B10036', 'MARKETING DIGITAL', 2),
(36, 1, 5, '131B10038', 'GESTION DE RECURSOS HUMANOS', 2),
(37, 1, 5, '131B10039', 'EMPRENDIMIENTO DIGITAL', 2),
(38, 1, 5, '131B10034', 'SISTEMAS CONTABLES', 2),
(39, 1, 5, '131B10033', 'TALLER DE BASE DE DATOS', 3),
(40, 1, 5, '131B10031', 'LENGUAJE DE PROGRAMACION III', 4),
(41, 1, 5, '131B10035', 'TEORIA GENERAL DE SISTEMAS', 2),
(42, 1, 5, '131B10030', 'ECOLOGIA', 2),
(43, 1, 5, '131B10032', 'METODOS NUMERICOS', 3),
(44, 1, 5, '131B10037', 'GESTION FINANCIERA', 2),
(45, 1, 6, '131B10048', 'INGLES TECNICO II', 2),
(46, 1, 6, '131B10041', 'PROCESAMIENTO DE IMAGENES', 3),
(47, 1, 6, '131B10047', 'COSTOS Y PRESUPUESTOS', 3),
(48, 1, 6, '131B10045', 'INTELIGENCIA DE NEGOCIOS', 3),
(49, 1, 6, '131B10042', 'DISEÑO ASISTIDO POR COMPUTADORA', 2),
(50, 1, 6, '131B10044', 'LENGUAJE DE PROGRAMACION IV', 4),
(51, 1, 6, '131B10046', 'ARQUITECTURA DE COMPUTADORAS', 3),
(52, 1, 6, '131B10043', 'REDES Y COMUNICACIONES', 3),
(53, 1, 7, '131B10055', 'COMPUTACION PARALELA', 2),
(54, 1, 7, '131B10051', 'INGENIERIA DE SOFTWARE', 4),
(55, 1, 7, '131B10057', 'COMPUTACION GRAFICA', 2),
(56, 1, 7, '131B10054', 'SISTEMAS DE INFORMACION EMPRESARIAL', 4),
(57, 1, 7, '131B10050', 'SISTEMAS OPERATIVOS', 3),
(58, 1, 7, '131B10053', 'INVESTIGACION DE OPERACIONES', 3),
(59, 1, 7, '131B10058', 'BIOINFORMATICA', 2),
(60, 1, 7, '131B10049', 'LENGUAJE DE PROGRAMACION V', 3),
(61, 1, 7, '131B10052', 'GESTION DE PROYECTOS', 3),
(62, 1, 7, '131B10056', 'COMPUTACION MOVIL Y UBICUA', 2),
(63, 1, 8, '131B10061', 'INVESTIGACION, DESARROLLO E INNOVACION', 3),
(64, 1, 8, '131B10063', 'INTERACCION HOMBRE MAQUINA', 3),
(65, 1, 8, '131B10066', 'ANALISIS Y DISEÑO DE SISTEMAS DE INFORMACION', 4),
(66, 1, 8, '131B10064', 'GESTION DE OPERACIONES', 3),
(67, 1, 8, '131B10062', 'TALLER DE SOFTWARE I', 3),
(68, 1, 8, '131B10065', 'INGLES TECNICO III', 2),
(69, 1, 8, '131B10059', 'INTELIGENCIA ARTIFICIAL', 3),
(70, 1, 8, '131B10060', 'SISTEMAS DE INFORMACION GEOREFERENCIAL', 3),
(71, 1, 9, '131B10077', 'CALIDAD DE SOFTWARE', 2),
(72, 1, 9, '131B10076', 'PERITAJE INFORMATICO', 2),
(73, 1, 9, '131B10069', 'TALLER DE SOFTWARE II', 3),
(74, 1, 9, '131B10068', 'ROBOTICA', 3),
(75, 1, 9, '131B10070', 'ANALISIS Y GESTION DE PROCESOS', 3),
(76, 1, 9, '131B10071', 'GESTION DE SERVICIOS EN TECNOLOGIA DE INFORMACION', 3),
(77, 1, 9, '131B10067', 'SEMINARIO DE TESIS', 3),
(78, 1, 9, '131B10075', 'PEDAGOGIA INFORMATICA', 2),
(79, 1, 9, '131B10073', 'ARQUITECTURA DE SISTEMAS DE INFORMACION', 3),
(80, 1, 9, '131B10072', 'SEGURIDAD INFORMATICA', 3),
(81, 1, 9, '131B10074', 'ECONOMIA DIGITAL', 2),
(82, 1, 10, '131B10079', 'GERENCIA DE SISTEMAS DE INFORMACION', 3),
(83, 1, 10, '131B10081', 'PRACTICA PREPROFESIONAL', 8),
(84, 1, 10, '131B10078', 'TRABAJO DE INVESTIGACIÓN', 3),
(85, 1, 10, '131B10080', 'AUDITORIA INFORMATICA', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id` bigint(20) NOT NULL,
  `nombres` text NOT NULL,
  `apellido_paterno` text NOT NULL,
  `apellido_materno` text NOT NULL,
  `dni` text NOT NULL,
  `tipo` enum('Nombrado','Contratado') NOT NULL DEFAULT 'Nombrado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id`, `nombres`, `apellido_paterno`, `apellido_materno`, `dni`, `tipo`) VALUES
(1, 'ANGEL ILDEFONSO', 'CATASHUNGA', 'TORRES', '05370319', 'Contratado'),
(2, 'RICHARD ALEX', 'LOPEZ', 'ALBIÑO', '32875390', 'Contratado'),
(3, 'JIMMY MAX', 'RAMIREZ', 'VILLACORTA', '41256622', 'Contratado'),
(4, 'ISACC', 'OCAMPO', 'YAHUARCANI', '40350680', 'Contratado'),
(5, 'CESAR AUGUSTO', 'PALACIOS', 'CHAVEZ', '18141926', 'Contratado'),
(6, 'JOSE LUIS', 'PEREZ', 'ORDOÑEZ', '07705764', 'Contratado'),
(7, 'SAUL', 'FLORES', 'NUNTA', '09671010', 'Contratado'),
(8, 'CARLOS ALBERTO', 'GARCIA', 'CORTEGANO', '29520345', 'Contratado'),
(9, 'ALFONSO MIGUEL', 'RIOS', 'CACHIQUE', '41433164', 'Contratado'),
(10, 'GRECIA MILAGROS', 'BARRERA', 'ORTIZ', '45201260', 'Contratado'),
(11, 'MANUEL', 'TUESTA', 'MORENO', '05336037', 'Nombrado'),
(12, 'JUAN MANUEL', 'VERME INSUA', '', '08777434', 'Nombrado'),
(13, 'RAFAEL', 'VILCA BARBARÁN', '', '41372787', 'Nombrado'),
(18, 'CARLOS', 'GONZALEZ', 'ASPAJO', '10343235', 'Nombrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` bigint(20) NOT NULL,
  `codigo_universitario` text NOT NULL,
  `escuela` text NOT NULL,
  `firma_hash` text NOT NULL,
  `malla_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `codigo_universitario`, `escuela`, `firma_hash`, `malla_id`) VALUES
(14, '21131b0671', 'INGENIERIA DE SISTEMAS E INFORMÁTICA', '075c932ac43e9fa7c6fd74043f3e78ff', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mallas`
--

CREATE TABLE `mallas` (
  `id` bigint(20) NOT NULL,
  `escuela` text NOT NULL,
  `hash_malla` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mallas`
--

INSERT INTO `mallas` (`id`, `escuela`, `hash_malla`) VALUES
(1, 'INGENIERIA DE SISTEMAS E INFORMÁTICA', '8493afee82c84748a5444c619bdca69e');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos`
--

CREATE TABLE `periodos` (
  `id` bigint(20) NOT NULL,
  `anio` int(11) NOT NULL,
  `periodo` int(11) NOT NULL CHECK (`periodo` in (1,2,3)),
  `inicio_envio_solicitudes` datetime NOT NULL,
  `fin_envio_solicitudes` datetime NOT NULL,
  `inicio_asignacion_docentes` datetime NOT NULL,
  `fin_asignacion_docentes` datetime NOT NULL,
  `minimo_solicitudes` int(11) NOT NULL,
  `maximo_cursos` int(11) NOT NULL,
  `maximo_creditos` int(11) NOT NULL,
  `estado` enum('activo','cerrado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prerrequisitos`
--

CREATE TABLE `prerrequisitos` (
  `id` bigint(20) NOT NULL,
  `curso_id` bigint(20) NOT NULL,
  `prerrequisito_id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prerrequisitos`
--

INSERT INTO `prerrequisitos` (`id`, `curso_id`, `prerrequisito_id`) VALUES
(1, 10, 2),
(2, 11, 4),
(3, 12, 2),
(4, 13, 8),
(5, 14, 7),
(6, 15, 9),
(7, 17, 1),
(8, 19, 6),
(9, 19, 14),
(10, 20, 10),
(11, 21, 17),
(12, 21, 10),
(13, 22, 12),
(14, 23, 13),
(15, 24, 10),
(16, 25, 13),
(17, 26, 24),
(18, 27, 21),
(19, 28, 11),
(20, 29, 25),
(21, 30, 19),
(22, 31, 20),
(23, 31, 22),
(24, 32, 23),
(25, 33, 26),
(26, 35, 30),
(27, 38, 30),
(28, 39, 29),
(29, 40, 32),
(30, 41, 27),
(31, 42, 19),
(32, 43, 31),
(33, 43, 32),
(34, 45, 28),
(35, 46, 43),
(36, 47, 38),
(37, 48, 39),
(38, 48, 40),
(39, 49, 43),
(40, 50, 40),
(41, 51, 33),
(42, 52, 33),
(43, 54, 52),
(44, 54, 48),
(45, 56, 47),
(46, 56, 47),
(47, 57, 51),
(48, 58, 47),
(49, 60, 50),
(50, 61, 52),
(51, 63, 56),
(52, 64, 54),
(53, 65, 41),
(54, 65, 56),
(55, 66, 58),
(56, 67, 54),
(57, 68, 45),
(58, 69, 46),
(59, 70, 46),
(60, 73, 67),
(61, 74, 69),
(62, 75, 66),
(63, 76, 63),
(64, 77, 69),
(65, 77, 41),
(66, 79, 65),
(67, 80, 63),
(68, 82, 76),
(69, 84, 77),
(70, 85, 80),
(71, 85, 79),
(72, 10, 2),
(73, 11, 4),
(74, 12, 2),
(75, 13, 8),
(76, 14, 7),
(77, 15, 9),
(78, 17, 1),
(79, 19, 6),
(80, 19, 14),
(81, 20, 10),
(82, 21, 17),
(83, 21, 10),
(84, 22, 12),
(85, 23, 13),
(86, 24, 10),
(87, 25, 13),
(88, 26, 24),
(89, 27, 21),
(90, 28, 11),
(91, 29, 25),
(92, 30, 19),
(93, 31, 20),
(94, 31, 22),
(95, 32, 23),
(96, 33, 26),
(97, 35, 30),
(98, 38, 30),
(99, 39, 29),
(100, 40, 32),
(101, 41, 27),
(102, 42, 19),
(103, 43, 31),
(104, 43, 32),
(105, 45, 28),
(106, 46, 43),
(107, 47, 38),
(108, 48, 39),
(109, 48, 40),
(110, 49, 43),
(111, 50, 40),
(112, 51, 33),
(113, 52, 33),
(114, 54, 52),
(115, 54, 48),
(116, 56, 47),
(117, 56, 47),
(118, 57, 51),
(119, 58, 47),
(120, 60, 50),
(121, 61, 52),
(122, 63, 56),
(123, 64, 54),
(124, 65, 41),
(125, 65, 56),
(126, 66, 58),
(127, 67, 54),
(128, 68, 45),
(129, 69, 46),
(130, 70, 46),
(131, 73, 67),
(132, 74, 69),
(133, 75, 66),
(134, 76, 63),
(135, 77, 69),
(136, 77, 41),
(137, 79, 65),
(138, 80, 63),
(139, 82, 76),
(140, 84, 77),
(141, 85, 80),
(142, 85, 79),
(143, 10, 2),
(144, 11, 4),
(145, 12, 2),
(146, 13, 8),
(147, 14, 7),
(148, 15, 9),
(149, 17, 1),
(150, 19, 6),
(151, 19, 14),
(152, 20, 10),
(153, 21, 17),
(154, 21, 10),
(155, 22, 12),
(156, 23, 13),
(157, 24, 10),
(158, 25, 13),
(159, 26, 24),
(160, 27, 21),
(161, 28, 11),
(162, 29, 25),
(163, 30, 19),
(164, 31, 20),
(165, 31, 22),
(166, 32, 23),
(167, 33, 26),
(168, 35, 30),
(169, 38, 30),
(170, 39, 29),
(171, 40, 32),
(172, 41, 27),
(173, 42, 19),
(174, 43, 31),
(175, 43, 32),
(176, 45, 28),
(177, 46, 43),
(178, 47, 38),
(179, 48, 39),
(180, 48, 40),
(181, 49, 43),
(182, 50, 40),
(183, 51, 33),
(184, 52, 33),
(185, 54, 52),
(186, 54, 48),
(187, 56, 47),
(188, 56, 47),
(189, 57, 51),
(190, 58, 47),
(191, 60, 50),
(192, 61, 52),
(193, 63, 56),
(194, 64, 54),
(195, 65, 41),
(196, 65, 56),
(197, 66, 58),
(198, 67, 54),
(199, 68, 45),
(200, 69, 46),
(201, 70, 46),
(202, 73, 67),
(203, 74, 69),
(204, 75, 66),
(205, 76, 63),
(206, 77, 69),
(207, 77, 41),
(208, 79, 65),
(209, 80, 63),
(210, 82, 76),
(211, 84, 77),
(212, 85, 80),
(213, 85, 79),
(214, 10, 2),
(215, 11, 4),
(216, 12, 2),
(217, 13, 8),
(218, 14, 7),
(219, 15, 9),
(220, 17, 1),
(221, 19, 6),
(222, 19, 14),
(223, 20, 10),
(224, 21, 17),
(225, 21, 10),
(226, 22, 12),
(227, 23, 13),
(228, 24, 10),
(229, 25, 13),
(230, 26, 24),
(231, 27, 21),
(232, 28, 11),
(233, 29, 25),
(234, 30, 19),
(235, 31, 20),
(236, 31, 22),
(237, 32, 23),
(238, 33, 26),
(239, 35, 30),
(240, 38, 30),
(241, 39, 29),
(242, 40, 32),
(243, 41, 27),
(244, 42, 19),
(245, 43, 31),
(246, 43, 32),
(247, 45, 28),
(248, 46, 43),
(249, 47, 38),
(250, 48, 39),
(251, 48, 40),
(252, 49, 43),
(253, 50, 40),
(254, 51, 33),
(255, 52, 33),
(256, 54, 52),
(257, 54, 48),
(258, 56, 47),
(259, 56, 47),
(260, 57, 51),
(261, 58, 47),
(262, 60, 50),
(263, 61, 52),
(264, 63, 56),
(265, 64, 54),
(266, 65, 41),
(267, 65, 56),
(268, 66, 58),
(269, 67, 54),
(270, 68, 45),
(271, 69, 46),
(272, 70, 46),
(273, 73, 67),
(274, 74, 69),
(275, 75, 66),
(276, 76, 63),
(277, 77, 69),
(278, 77, 41),
(279, 79, 65),
(280, 80, 63),
(281, 82, 76),
(282, 84, 77),
(283, 85, 80),
(284, 85, 79),
(285, 10, 2),
(286, 11, 4),
(287, 12, 2),
(288, 13, 8),
(289, 14, 7),
(290, 15, 9),
(291, 17, 1),
(292, 19, 6),
(293, 19, 14),
(294, 20, 10),
(295, 21, 17),
(296, 21, 10),
(297, 22, 12),
(298, 23, 13),
(299, 24, 10),
(300, 25, 13),
(301, 26, 24),
(302, 27, 21),
(303, 28, 11),
(304, 29, 25),
(305, 30, 19),
(306, 31, 20),
(307, 31, 22),
(308, 32, 23),
(309, 33, 26),
(310, 35, 30),
(311, 38, 30),
(312, 39, 29),
(313, 40, 32),
(314, 41, 27),
(315, 42, 19),
(316, 43, 31),
(317, 43, 32),
(318, 45, 28),
(319, 46, 43),
(320, 47, 38),
(321, 48, 39),
(322, 48, 40),
(323, 49, 43),
(324, 50, 40),
(325, 51, 33),
(326, 52, 33),
(327, 54, 52),
(328, 54, 48),
(329, 56, 47),
(330, 56, 47),
(331, 57, 51),
(332, 58, 47),
(333, 60, 50),
(334, 61, 52),
(335, 63, 56),
(336, 64, 54),
(337, 65, 41),
(338, 65, 56),
(339, 66, 58),
(340, 67, 54),
(341, 68, 45),
(342, 69, 46),
(343, 70, 46),
(344, 73, 67),
(345, 74, 69),
(346, 75, 66),
(347, 76, 63),
(348, 77, 69),
(349, 77, 41),
(350, 79, 65),
(351, 80, 63),
(352, 82, 76),
(353, 84, 77),
(354, 85, 80),
(355, 85, 79),
(356, 10, 2),
(357, 11, 4),
(358, 12, 2),
(359, 13, 8),
(360, 14, 7),
(361, 15, 9),
(362, 17, 1),
(363, 19, 6),
(364, 19, 14),
(365, 20, 10),
(366, 21, 17),
(367, 21, 10),
(368, 22, 12),
(369, 23, 13),
(370, 24, 10),
(371, 25, 13),
(372, 26, 24),
(373, 27, 21),
(374, 28, 11),
(375, 29, 25),
(376, 30, 19),
(377, 31, 20),
(378, 31, 22),
(379, 32, 23),
(380, 33, 26),
(381, 35, 30),
(382, 38, 30),
(383, 39, 29),
(384, 40, 32),
(385, 41, 27),
(386, 42, 19),
(387, 43, 31),
(388, 43, 32),
(389, 45, 28),
(390, 46, 43),
(391, 47, 38),
(392, 48, 39),
(393, 48, 40),
(394, 49, 43),
(395, 50, 40),
(396, 51, 33),
(397, 52, 33),
(398, 54, 52),
(399, 54, 48),
(400, 56, 47),
(401, 56, 47),
(402, 57, 51),
(403, 58, 47),
(404, 60, 50),
(405, 61, 52),
(406, 63, 56),
(407, 64, 54),
(408, 65, 41),
(409, 65, 56),
(410, 66, 58),
(411, 67, 54),
(412, 68, 45),
(413, 69, 46),
(414, 70, 46),
(415, 73, 67),
(416, 74, 69),
(417, 75, 66),
(418, 76, 63),
(419, 77, 69),
(420, 77, 41),
(421, 79, 65),
(422, 80, 63),
(423, 82, 76),
(424, 84, 77),
(425, 85, 80),
(426, 85, 79),
(427, 10, 2),
(428, 11, 4),
(429, 12, 2),
(430, 13, 8),
(431, 14, 7),
(432, 15, 9),
(433, 17, 1),
(434, 19, 6),
(435, 19, 14),
(436, 20, 10),
(437, 21, 17),
(438, 21, 10),
(439, 22, 12),
(440, 23, 13),
(441, 24, 10),
(442, 25, 13),
(443, 26, 24),
(444, 27, 21),
(445, 28, 11),
(446, 29, 25),
(447, 30, 19),
(448, 31, 20),
(449, 31, 22),
(450, 32, 23),
(451, 33, 26),
(452, 35, 30),
(453, 38, 30),
(454, 39, 29),
(455, 40, 32),
(456, 41, 27),
(457, 42, 19),
(458, 43, 31),
(459, 43, 32),
(460, 45, 28),
(461, 46, 43),
(462, 47, 38),
(463, 48, 39),
(464, 48, 40),
(465, 49, 43),
(466, 50, 40),
(467, 51, 33),
(468, 52, 33),
(469, 54, 52),
(470, 54, 48),
(471, 56, 47),
(472, 56, 47),
(473, 57, 51),
(474, 58, 47),
(475, 60, 50),
(476, 61, 52),
(477, 63, 56),
(478, 64, 54),
(479, 65, 41),
(480, 65, 56),
(481, 66, 58),
(482, 67, 54),
(483, 68, 45),
(484, 69, 46),
(485, 70, 46),
(486, 73, 67),
(487, 74, 69),
(488, 75, 66),
(489, 76, 63),
(490, 77, 69),
(491, 77, 41),
(492, 79, 65),
(493, 80, 63),
(494, 82, 76),
(495, 84, 77),
(496, 85, 80),
(497, 85, 79),
(498, 10, 2),
(499, 11, 4),
(500, 12, 2),
(501, 13, 8),
(502, 14, 7),
(503, 15, 9),
(504, 17, 1),
(505, 19, 6),
(506, 19, 14),
(507, 20, 10),
(508, 21, 17),
(509, 21, 10),
(510, 22, 12),
(511, 23, 13),
(512, 24, 10),
(513, 25, 13),
(514, 26, 24),
(515, 27, 21),
(516, 28, 11),
(517, 29, 25),
(518, 30, 19),
(519, 31, 20),
(520, 31, 22),
(521, 32, 23),
(522, 33, 26),
(523, 35, 30),
(524, 38, 30),
(525, 39, 29),
(526, 40, 32),
(527, 41, 27),
(528, 42, 19),
(529, 43, 31),
(530, 43, 32),
(531, 45, 28),
(532, 46, 43),
(533, 47, 38),
(534, 48, 39),
(535, 48, 40),
(536, 49, 43),
(537, 50, 40),
(538, 51, 33),
(539, 52, 33),
(540, 54, 52),
(541, 54, 48),
(542, 56, 47),
(543, 56, 47),
(544, 57, 51),
(545, 58, 47),
(546, 60, 50),
(547, 61, 52),
(548, 63, 56),
(549, 64, 54),
(550, 65, 41),
(551, 65, 56),
(552, 66, 58),
(553, 67, 54),
(554, 68, 45),
(555, 69, 46),
(556, 70, 46),
(557, 73, 67),
(558, 74, 69),
(559, 75, 66),
(560, 76, 63),
(561, 77, 69),
(562, 77, 41),
(563, 79, 65),
(564, 80, 63),
(565, 82, 76),
(566, 84, 77),
(567, 85, 80),
(568, 85, 79),
(632, 10, 2),
(633, 11, 4),
(634, 12, 2),
(635, 13, 8),
(636, 14, 7),
(637, 15, 9),
(638, 17, 1),
(639, 19, 6),
(640, 19, 14),
(641, 20, 10),
(642, 21, 17),
(643, 21, 10),
(644, 22, 12),
(645, 23, 13),
(646, 24, 10),
(647, 25, 13),
(648, 26, 24),
(649, 27, 21),
(650, 28, 11),
(651, 29, 25),
(652, 30, 19),
(653, 31, 20),
(654, 31, 22),
(655, 32, 23),
(656, 33, 26),
(657, 35, 30),
(658, 38, 30),
(659, 39, 29),
(660, 40, 32),
(661, 41, 27),
(662, 42, 19),
(663, 43, 31),
(664, 43, 32),
(665, 45, 28),
(666, 46, 43),
(667, 47, 38),
(668, 48, 39),
(669, 48, 40),
(670, 49, 43),
(671, 50, 40),
(672, 51, 33),
(673, 52, 33),
(674, 54, 52),
(675, 54, 48),
(676, 56, 47),
(677, 56, 47),
(678, 57, 51),
(679, 58, 47),
(680, 60, 50),
(681, 61, 52),
(682, 63, 56),
(683, 64, 54),
(684, 65, 41),
(685, 65, 56),
(686, 66, 58),
(687, 67, 54),
(688, 68, 45),
(689, 69, 46),
(690, 70, 46),
(691, 73, 67),
(692, 74, 69),
(693, 75, 66),
(694, 76, 63),
(695, 77, 69),
(696, 77, 41),
(697, 79, 65),
(698, 80, 63),
(699, 82, 76),
(700, 84, 77),
(701, 85, 80),
(702, 85, 79),
(703, 10, 2),
(704, 11, 4),
(705, 12, 2),
(706, 13, 8),
(707, 14, 7),
(708, 15, 9),
(709, 17, 1),
(710, 19, 6),
(711, 19, 14),
(712, 20, 10),
(713, 21, 17),
(714, 21, 10),
(715, 22, 12),
(716, 23, 13),
(717, 24, 10),
(718, 25, 13),
(719, 26, 24),
(720, 27, 21),
(721, 28, 11),
(722, 29, 25),
(723, 30, 19),
(724, 31, 20),
(725, 31, 22),
(726, 32, 23),
(727, 33, 26),
(728, 35, 30),
(729, 38, 30),
(730, 39, 29),
(731, 40, 32),
(732, 41, 27),
(733, 42, 19),
(734, 43, 31),
(735, 43, 32),
(736, 45, 28),
(737, 46, 43),
(738, 47, 38),
(739, 48, 39),
(740, 48, 40),
(741, 49, 43),
(742, 50, 40),
(743, 51, 33),
(744, 52, 33),
(745, 54, 52),
(746, 54, 48),
(747, 56, 47),
(748, 56, 47),
(749, 57, 51),
(750, 58, 47),
(751, 60, 50),
(752, 61, 52),
(753, 63, 56),
(754, 64, 54),
(755, 65, 41),
(756, 65, 56),
(757, 66, 58),
(758, 67, 54),
(759, 68, 45),
(760, 69, 46),
(761, 70, 46),
(762, 73, 67),
(763, 74, 69),
(764, 75, 66),
(765, 76, 63),
(766, 77, 69),
(767, 77, 41),
(768, 79, 65),
(769, 80, 63),
(770, 82, 76),
(771, 84, 77),
(772, 85, 80),
(773, 85, 79),
(774, 10, 2),
(775, 11, 4),
(776, 12, 2),
(777, 13, 8),
(778, 14, 7),
(779, 15, 9),
(780, 17, 1),
(781, 19, 6),
(782, 19, 14),
(783, 20, 10),
(784, 21, 17),
(785, 21, 10),
(786, 22, 12),
(787, 23, 13),
(788, 24, 10),
(789, 25, 13),
(790, 26, 24),
(791, 27, 21),
(792, 28, 11),
(793, 29, 25),
(794, 30, 19),
(795, 31, 20),
(796, 31, 22),
(797, 32, 23),
(798, 33, 26),
(799, 35, 30),
(800, 38, 30),
(801, 39, 29),
(802, 40, 32),
(803, 41, 27),
(804, 42, 19),
(805, 43, 31),
(806, 43, 32),
(807, 45, 28),
(808, 46, 43),
(809, 47, 38),
(810, 48, 39),
(811, 48, 40),
(812, 49, 43),
(813, 50, 40),
(814, 51, 33),
(815, 52, 33),
(816, 54, 52),
(817, 54, 48),
(818, 56, 47),
(819, 56, 47),
(820, 57, 51),
(821, 58, 47),
(822, 60, 50),
(823, 61, 52),
(824, 63, 56),
(825, 64, 54),
(826, 65, 41),
(827, 65, 56),
(828, 66, 58),
(829, 67, 54),
(830, 68, 45),
(831, 69, 46),
(832, 70, 46),
(833, 73, 67),
(834, 74, 69),
(835, 75, 66),
(836, 76, 63),
(837, 77, 69),
(838, 77, 41),
(839, 79, 65),
(840, 80, 63),
(841, 82, 76),
(842, 84, 77),
(843, 85, 80),
(844, 85, 79),
(845, 10, 2),
(846, 11, 4),
(847, 12, 2),
(848, 13, 8),
(849, 14, 7),
(850, 15, 9),
(851, 17, 1),
(852, 19, 6),
(853, 19, 14),
(854, 20, 10),
(855, 21, 17),
(856, 21, 10),
(857, 22, 12),
(858, 23, 13),
(859, 24, 10),
(860, 25, 13),
(861, 26, 24),
(862, 27, 21),
(863, 28, 11),
(864, 29, 25),
(865, 30, 19),
(866, 31, 20),
(867, 31, 22),
(868, 32, 23),
(869, 33, 26),
(870, 35, 30),
(871, 38, 30),
(872, 39, 29),
(873, 40, 32),
(874, 41, 27),
(875, 42, 19),
(876, 43, 31),
(877, 43, 32),
(878, 45, 28),
(879, 46, 43),
(880, 47, 38),
(881, 48, 39),
(882, 48, 40),
(883, 49, 43),
(884, 50, 40),
(885, 51, 33),
(886, 52, 33),
(887, 54, 52),
(888, 54, 48),
(889, 56, 47),
(890, 56, 47),
(891, 57, 51),
(892, 58, 47),
(893, 60, 50),
(894, 61, 52),
(895, 63, 56),
(896, 64, 54),
(897, 65, 41),
(898, 65, 56),
(899, 66, 58),
(900, 67, 54),
(901, 68, 45),
(902, 69, 46),
(903, 70, 46),
(904, 73, 67),
(905, 74, 69),
(906, 75, 66),
(907, 76, 63),
(908, 77, 69),
(909, 77, 41),
(910, 79, 65),
(911, 80, 63),
(912, 82, 76),
(913, 84, 77),
(914, 85, 80),
(915, 85, 79);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progreso`
--

CREATE TABLE `progreso` (
  `id` bigint(20) NOT NULL,
  `estudiante_id` bigint(20) NOT NULL,
  `curso_id` bigint(20) NOT NULL,
  `estado` enum('Cumplido','Pendiente') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `progreso`
--

INSERT INTO `progreso` (`id`, `estudiante_id`, `curso_id`, `estado`) VALUES
(686, 14, 1, 'Cumplido'),
(687, 14, 2, 'Cumplido'),
(688, 14, 4, 'Cumplido'),
(689, 14, 6, 'Cumplido'),
(690, 14, 7, 'Cumplido'),
(691, 14, 8, 'Cumplido'),
(692, 14, 9, 'Cumplido'),
(693, 14, 10, 'Cumplido'),
(694, 14, 11, 'Cumplido'),
(695, 14, 12, 'Cumplido'),
(696, 14, 13, 'Cumplido'),
(697, 14, 14, 'Cumplido'),
(698, 14, 15, 'Cumplido'),
(699, 14, 17, 'Cumplido'),
(700, 14, 19, 'Cumplido'),
(701, 14, 20, 'Cumplido'),
(702, 14, 21, 'Cumplido'),
(703, 14, 22, 'Cumplido'),
(704, 14, 23, 'Cumplido'),
(705, 14, 24, 'Cumplido'),
(706, 14, 25, 'Cumplido'),
(707, 14, 26, 'Cumplido'),
(708, 14, 27, 'Cumplido'),
(709, 14, 28, 'Cumplido'),
(710, 14, 29, 'Cumplido'),
(711, 14, 30, 'Cumplido'),
(712, 14, 31, 'Cumplido'),
(713, 14, 32, 'Cumplido'),
(714, 14, 33, 'Cumplido'),
(715, 14, 35, 'Cumplido'),
(716, 14, 38, 'Cumplido'),
(717, 14, 39, 'Cumplido'),
(718, 14, 40, 'Cumplido'),
(719, 14, 41, 'Cumplido'),
(720, 14, 42, 'Cumplido'),
(721, 14, 43, 'Cumplido'),
(722, 14, 45, 'Cumplido'),
(723, 14, 46, 'Cumplido'),
(724, 14, 47, 'Cumplido'),
(725, 14, 48, 'Cumplido'),
(726, 14, 49, 'Cumplido'),
(727, 14, 50, 'Cumplido'),
(728, 14, 51, 'Cumplido'),
(729, 14, 52, 'Cumplido'),
(730, 14, 54, 'Cumplido'),
(731, 14, 56, 'Cumplido'),
(732, 14, 57, 'Cumplido'),
(733, 14, 58, 'Cumplido'),
(734, 14, 60, 'Pendiente'),
(735, 14, 61, 'Cumplido'),
(736, 14, 63, 'Cumplido'),
(737, 14, 64, 'Pendiente'),
(738, 14, 65, 'Cumplido'),
(739, 14, 66, 'Cumplido'),
(740, 14, 67, 'Pendiente'),
(741, 14, 68, 'Cumplido'),
(742, 14, 69, 'Cumplido'),
(743, 14, 70, 'Cumplido'),
(744, 14, 73, 'Pendiente'),
(745, 14, 74, 'Pendiente'),
(746, 14, 75, 'Pendiente'),
(747, 14, 76, 'Pendiente'),
(748, 14, 77, 'Pendiente'),
(749, 14, 79, 'Pendiente'),
(750, 14, 80, 'Pendiente'),
(751, 14, 82, 'Pendiente'),
(752, 14, 83, 'Pendiente'),
(753, 14, 84, 'Pendiente'),
(754, 14, 85, 'Pendiente'),
(755, 14, 5, 'Cumplido'),
(756, 14, 18, 'Cumplido'),
(757, 14, 34, 'Cumplido'),
(758, 14, 62, 'Cumplido'),
(759, 14, 71, 'Pendiente'),
(760, 14, 72, 'Pendiente'),
(761, 14, 78, 'Pendiente'),
(762, 14, 81, 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resoluciones`
--

CREATE TABLE `resoluciones` (
  `id` bigint(20) NOT NULL,
  `periodo_id` bigint(20) NOT NULL,
  `documento` longblob NOT NULL,
  `fecha_resolucion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) NOT NULL,
  `nombre` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`) VALUES
(1, 'estudiante'),
(2, 'administrador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` bigint(20) NOT NULL,
  `estudiante_id` bigint(20) NOT NULL,
  `curso_id` bigint(20) NOT NULL,
  `periodo_id` bigint(20) NOT NULL,
  `documento` longblob NOT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL,
  `nombres` text NOT NULL,
  `apellido_paterno` text NOT NULL,
  `apellido_materno` text NOT NULL,
  `dni` text NOT NULL,
  `correo` text NOT NULL,
  `telefono` text DEFAULT NULL,
  `usuario` text NOT NULL,
  `contraseña` text NOT NULL,
  `rol_id` bigint(20) NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombres`, `apellido_paterno`, `apellido_materno`, `dni`, `correo`, `telefono`, `usuario`, `contraseña`, `rol_id`, `creado_en`) VALUES
(3, 'José', 'Administrador', 'Sistema', '12345678', 'admin@unap.edu.pe', '999999998', '12345678', '$2y$10$g7lpFrtINUocAOSr8wOv5uMhvBYS0W6tcQAjEJjJGZUL2nO1vnIPC', 2, '2025-06-19 15:28:38'),
(14, 'JOSE OSWALDO', 'VASQUEZ', 'ZEVALLOS', '71254152', 'jovz9673@gmail.com', '930172868', '21131b0671', '$2y$10$LID0TvWDXZ1OHK2fBltIFenVyA6KhOcomx9dF7CdOwvnoZ9fZuvrW', 1, '2025-06-26 13:37:13');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `docente_id` (`docente_id`),
  ADD KEY `periodo_id` (`periodo_id`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `malla_id` (`malla_id`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`) USING HASH;

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_universitario` (`codigo_universitario`) USING HASH,
  ADD KEY `malla_id` (`malla_id`);

--
-- Indices de la tabla `mallas`
--
ALTER TABLE `mallas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hash_malla` (`hash_malla`) USING HASH;

--
-- Indices de la tabla `periodos`
--
ALTER TABLE `periodos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `prerrequisitos`
--
ALTER TABLE `prerrequisitos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `prerrequisito_id` (`prerrequisito_id`);

--
-- Indices de la tabla `progreso`
--
ALTER TABLE `progreso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Indices de la tabla `resoluciones`
--
ALTER TABLE `resoluciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `periodo_id` (`periodo_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`) USING HASH;

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `periodo_id` (`periodo_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`) USING HASH,
  ADD UNIQUE KEY `correo` (`correo`) USING HASH,
  ADD UNIQUE KEY `usuario` (`usuario`) USING HASH,
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `mallas`
--
ALTER TABLE `mallas`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `periodos`
--
ALTER TABLE `periodos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `prerrequisitos`
--
ALTER TABLE `prerrequisitos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=916;

--
-- AUTO_INCREMENT de la tabla `progreso`
--
ALTER TABLE `progreso`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT de la tabla `resoluciones`
--
ALTER TABLE `resoluciones`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignaciones`
--
ALTER TABLE `asignaciones`
  ADD CONSTRAINT `asignaciones_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asignaciones_ibfk_2` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asignaciones_ibfk_3` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`malla_id`) REFERENCES `mallas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `estudiantes_ibfk_1` FOREIGN KEY (`id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `estudiantes_ibfk_2` FOREIGN KEY (`malla_id`) REFERENCES `mallas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `prerrequisitos`
--
ALTER TABLE `prerrequisitos`
  ADD CONSTRAINT `prerrequisitos_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prerrequisitos_ibfk_2` FOREIGN KEY (`prerrequisito_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `progreso`
--
ALTER TABLE `progreso`
  ADD CONSTRAINT `progreso_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progreso_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resoluciones`
--
ALTER TABLE `resoluciones`
  ADD CONSTRAINT `resoluciones_ibfk_1` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitudes_ibfk_3` FOREIGN KEY (`periodo_id`) REFERENCES `periodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
