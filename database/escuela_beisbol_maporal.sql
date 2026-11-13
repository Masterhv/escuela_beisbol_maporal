-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2026 a las 11:59:07
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
-- Base de datos: `escuela_beisbol_maporal`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `id_card` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_relationship` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(25) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('administrador','secretario','entrenador','representante') DEFAULT 'entrenador',
  `operation_key` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_date` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `id_card`, `birth_date`, `phone`, `address`, `emergency_contact_name`, `emergency_contact_relationship`, `emergency_contact_phone`, `full_name`, `role`, `operation_key`, `status`, `created_date`, `last_login`, `updated_at`, `reset_token`, `reset_token_expires_at`) VALUES
(1, 'admin', '$2y$10$DsLsuKBgNC6i/eat8U0a8ebsUUm0heGOeW1aCoioFxe5TUukQ9yDe', 'beisbolmaporal@gmail.com', '24527809', '1993-04-12', '+584262747945', '1 Diciembre', 'Eurris Velázquez', 'padre', '+584245103144', 'Administrador Principal', 'administrador', '$2y$10$veJzQjPjB5EPP6NoioJPyu5wP1UsveBjU.vScg8ay6Pzufe/lmkHW', 'active', '2025-07-19 12:38:06', '2026-11-13 06:39:18', '2026-11-13 06:39:18', NULL, NULL),
(2, 'valeromh', '$2y$10$UUxFXWeUA.pTzPEd0t87W.925RYMOZfckdkF3.1Pn4/l697h615Rq', 'valeromh@pdvsa.com.ve', '11324654', '1969-12-05', '0424-5852482', 'Urb. Ciudad Tavacare, Sector B, Bloque 8, Apartamento 3', 'Milangela de Valero', 'Esposa', '0414-1321316', 'Miguel Alejandro Valero Piedra', 'administrador', '$2y$10$TD1UMd8ZZFzhwg2Z6H4o4ufmflp5DqzQmUtGW9SatKIu091t/Wf4S', 'active', '2025-11-17 22:53:24', '2026-06-29 22:39:40', '2026-11-13 05:48:15', NULL, NULL),
(147, 'rattiae', '$2y$10$l98h3d8EIcw5H8yATVlKweXZJ0H3lHTXrXj.y23Z6fxhIVbCj.oPy', 'rattiae@gmail.com', '13216548', '1960-08-12', '0414-1234567', 'Raúl Leoni', 'Luis Rojas', 'sobrino', '0422-1234567', 'Edita Rattia de Escobar', 'representante', NULL, 'active', '2026-11-09 07:08:10', '2026-11-13 06:32:03', '2026-11-13 06:32:03', NULL, NULL),
(148, 'serranosv', '$2y$10$BuwbOFKrErvbBXwPjsQutOd4/271eWEf/hYCRYOE/dwtDyf.3cER2', 'serranosv@pdvsa.com.ve', '18244234', '1980-10-11', '0416-2783977', 'Raul Leoni', 'Yadi de Serrano', 'Pareja', '0414-3223234', 'Silvanny Serrano', 'secretario', NULL, 'active', '2026-11-09 07:41:01', '2026-11-13 05:13:59', '2026-11-13 05:13:59', NULL, NULL),
(149, 'piconr', '$2y$10$f2caHlOzDbGpi5H2zazEs.MP4OVFpTbxOOTwed98eBQb17f.vNY8e', 'ingpiconrolando@gmail.com', '19997839', '1991-10-12', '0414-7199654', 'Av Arismendi', 'Carlos Gutierrez', 'Primo', '0424-1339657', 'Rolando Picón', 'representante', NULL, 'active', '2026-11-09 14:35:57', '2026-11-13 02:18:25', '2026-11-13 02:18:25', NULL, NULL),
(150, 'penarv', '$2y$10$xBTB8/QT96NjaznO7eCC3.Pjm4aB57dIxbw6cCIRXqBVqmo1U8dmW', 'penarodolfourbano2@gmail.com', '11716893', '1974-10-31', '0412-6356891', 'Barrio el Pozón', 'Yoselin Peraza', 'Pareja', '0412-2074427', 'Rodolfo Peña', 'representante', NULL, 'active', '2026-11-09 16:48:41', '2026-11-09 16:49:01', '2026-11-09 16:49:01', NULL, NULL),
(156, 'guerreroe', '$2y$10$1i/V2uJ3MHG5FbGL3sJQz.knNc1JO2HU2XYNQ/QVMMEd9oZQ/Y64K', 'guerreroe@gmail.com', '19789987', '1990-07-12', '0412-5183359', 'Alto Barinas', 'Omaira Lopez', 'Madre', '0412-1234365', 'Ernesto Guerrero', 'entrenador', NULL, 'active', '2026-11-13 04:26:38', '2026-11-13 05:18:39', '2026-11-13 05:18:39', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `athletes`
--

CREATE TABLE `athletes` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `id_card` varchar(10) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `age` int(11) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `parent_id_card` varchar(10) DEFAULT NULL,
  `parent_phone` varchar(20) NOT NULL,
  `parent_email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `emergency_contact` varchar(100) NOT NULL,
  `emergency_phone` varchar(20) NOT NULL,
  `medical_conditions` text DEFAULT NULL,
  `registration_date` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `status_reason` varchar(500) DEFAULT NULL COMMENT 'Motivo del ultimo cambio a inactivo/suspendido. NULL = no registrado',
  `status_changed_at` datetime DEFAULT NULL COMMENT 'Cuando se cambio a inactivo/suspendido. NULL = no registrado',
  `status_changed_by` int(11) DEFAULT NULL COMMENT 'admins.id de quien lo cambio',
  `status_changed_name` varchar(100) DEFAULT NULL COMMENT 'Nombre de quien lo cambio (copiado: sigue legible si la cuenta cambia)',
  `status_changed_role` varchar(20) DEFAULT NULL COMMENT 'Rol de quien lo cambio',
  `withdrawn_at` datetime DEFAULT NULL COMMENT 'Fecha y hora del retiro formal (solvencia emitida)',
  `clearance_number` varchar(20) DEFAULT NULL COMMENT 'Numero de la solvencia de retiro, ej. SOL-2026-0001',
  `clearance_issued_by` varchar(100) DEFAULT NULL COMMENT 'Nombre de quien emitio la solvencia',
  `jersey_number` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `enrollment_email_status` enum('not_sent','sent','failed') NOT NULL DEFAULT 'not_sent',
  `enrollment_email_sent_at` datetime DEFAULT NULL,
  `enrollment_email_error` text DEFAULT NULL,
  `enrollment_whatsapp_status` enum('not_sent','sent','failed') NOT NULL DEFAULT 'not_sent',
  `enrollment_whatsapp_sent_at` datetime DEFAULT NULL,
  `enrollment_whatsapp_error` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `athletes`
--

INSERT INTO `athletes` (`id`, `first_name`, `last_name`, `id_card`, `date_of_birth`, `age`, `parent_name`, `parent_id_card`, `parent_phone`, `parent_email`, `address`, `emergency_contact`, `emergency_phone`, `medical_conditions`, `registration_date`, `category`, `status`, `status_reason`, `status_changed_at`, `status_changed_by`, `status_changed_name`, `status_changed_role`, `withdrawn_at`, `clearance_number`, `clearance_issued_by`, `jersey_number`, `photo`, `enrollment_email_status`, `enrollment_email_sent_at`, `enrollment_email_error`, `enrollment_whatsapp_status`, `enrollment_whatsapp_sent_at`, `enrollment_whatsapp_error`, `created_at`, `updated_at`) VALUES
(11, 'Leonardo', 'Mora', '32123456', '2013-06-12', 12, 'Juan Mora', '11321654', '0412-1234567', 'moraj@gmail.com', 'Urb. Ciudad Varyna, sector Apamate 1, Calle 4, casa 10', 'Olinda Mora', '0412-1234567', 'no posee', '2025-11-16', 'Pre-juvenil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 'img/atleta_691a643cb6f11_1763337276.jpeg', 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 16:18:40', '2026-10-13 22:58:44'),
(12, 'Ricardo', 'Escobar', '31654321', '2011-05-20', 14, 'Edita Rattia de Escobar', '13216548', '0414-1234567', 'rattiae@gmail.com', 'Urb. Dominga Ortiz de Páez, Sector 1, Calle 3, Casa 10', 'Luis Rojas', '0422-1234567', 'no posee', '2025-11-16', 'Juvenil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, 'img/atleta_691a6446cab68_1763337286.jpeg', 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 16:22:40', '2026-10-13 22:42:59'),
(13, 'Jesús Manuel', 'González', '34123456', '2016-08-15', 10, 'Mario González', '14321654', '0416-6549874', 'Gonzalezm@gmail.com', 'Urb. Las Palmas, sector 2, calle 1, casa 5', 'Jorge Lopez', '0416-3216546', 'no posee', '2025-11-16', 'Infantil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, 'img/atleta_691a64319df4a_1763337265.jpeg', 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 16:26:15', '2026-11-13 04:57:59'),
(14, 'Andrés', 'Ramirez', NULL, '2016-05-18', 9, 'Gonzalo Ramírez', '14654123', '0416-4564213', 'ramirezg@gmail.com', 'Terrazas de Santo Domingo', 'María de Ramírez', '0422-2342343', 'no', '2025-11-16', 'Infantil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 16:52:28', '2026-10-29 20:35:58'),
(15, 'Carlos', 'Zambrano', NULL, '2014-06-15', 11, 'Héctor Zambrano', '15321654', '0424-6549879', 'zambranoh@gmail.com', 'Palacio Fajardo, calle principal. casa H-12', 'Mario Zuñiga', '0414-3333333', 'no', '2025-11-16', 'Pre-juvenil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 16:54:59', '2025-11-16 16:54:59'),
(16, 'Anderson', 'Galindez', NULL, '2017-09-15', 9, 'José Galíndez', '14021313', '0416-9876546', 'Galindezj@gmail.com', 'Terracota, Calle 4, Casa 12', 'Fabian Mendez', '0416-3344442', 'no', '2025-11-16', 'Infantil', 'suspended', 'se mudó de Ciudad', '2026-11-08 17:02:56', 1, 'Administrador Principal', 'administrador', NULL, NULL, NULL, 3, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 16:57:20', '2026-11-08 17:02:56'),
(17, 'Roberto', 'Santiago', NULL, '2008-12-09', 17, 'María Teresa', '9846543', '0416-5554644', 'teresam@gmail.com', 'Urb, Santo Domingo, calle 12, casa 15', 'Hugo Santiago', '0414-9554154', 'no', '2025-11-16', 'Junior', 'inactive', 'Falta disciplinaria', '2026-11-08 17:04:02', 1, 'Administrador Principal', 'administrador', NULL, NULL, NULL, 7, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 17:00:38', '2026-11-08 17:04:02'),
(87, 'Dylan Josue', 'Picón Rodriguez', '33465242', '2013-06-06', 13, 'Rolando Picón', '19997839', '0414-7199654', 'ingpiconrolando@gmail.com', 'Av. Arismendi', 'Carlos Gutierrez', '0424-1339657', 'Ninguno', '2026-11-09', 'Juvenil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 'img/atleta_6af2064de7b73_1794246221.jpg', 'sent', '2026-11-09 13:43:45', NULL, 'sent', '2026-11-09 13:43:50', NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:50'),
(88, 'Jesus Mauel', 'Peña Escalona', '33504840', '2015-03-31', 11, 'Rodolfo Peña', '11716893', '0412-6356891', 'penarodolfourbano2@gmail.com', 'Barrio el Pozón', 'Yoselin Peraza de Peña', '0412-2074427', 'Asmático', '2026-11-09', 'Pre-juvenil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 6, NULL, 'sent', '2026-11-09 16:44:15', NULL, 'sent', '2026-11-09 16:44:19', NULL, '2026-11-09 16:44:06', '2026-11-09 16:44:19'),
(97, 'David', 'Escobar', NULL, '2018-09-06', 8, 'Edita Rattia de Escobar', '13216548', '0414-1234567', 'rattiae@gmail.com', 'Raúl Leoni', 'Carlos Aguero', '0412-5032108', 'Alérgico al Jabón', '2026-11-13', 'Infantil', 'active', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4, NULL, 'sent', '2026-11-13 06:10:43', NULL, 'sent', '2026-11-13 06:10:48', NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificate_counters`
--

CREATE TABLE `certificate_counters` (
  `year` smallint(5) UNSIGNED NOT NULL,
  `last_number` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ultimo numero de constancia emitido por anio; nunca disminuye';

--
-- Volcado de datos para la tabla `certificate_counters`
--

INSERT INTO `certificate_counters` (`year`, `last_number`) VALUES
(2026, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clearance_counters`
--

CREATE TABLE `clearance_counters` (
  `year` smallint(5) UNSIGNED NOT NULL,
  `last_number` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ultimo numero de solvencia de retiro emitido por anio; nunca disminuye, aunque se reactive al atleta';

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `dashboard_stats`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `dashboard_stats` (
`total_athletes` bigint(21)
,`active_athletes` bigint(21)
,`total_payments` bigint(21)
,`total_revenue` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_quantity` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_by_name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `type` enum('entrada','salida','ajuste') NOT NULL,
  `quantity` int(11) NOT NULL,
  `quantity_before` int(11) NOT NULL,
  `quantity_after` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `registered_by` int(11) DEFAULT NULL,
  `registered_by_name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membership_certificates`
--

CREATE TABLE `membership_certificates` (
  `id` int(11) NOT NULL,
  `certificate_number` varchar(20) NOT NULL COMMENT 'Numero correlativo, ej. CON-2026-0001',
  `athlete_id` int(11) NOT NULL,
  `issued_at` datetime NOT NULL COMMENT 'Fecha y hora de emision',
  `valid_until` date NOT NULL COMMENT 'Ultimo dia de vigencia (emision + 30 dias)',
  `issued_by` varchar(100) NOT NULL COMMENT 'Nombre de quien la emitio',
  `email_status` enum('not_sent','sent','failed') NOT NULL DEFAULT 'not_sent' COMMENT 'Estado del envio por correo',
  `email_sent_at` datetime DEFAULT NULL,
  `email_sent_to` varchar(100) DEFAULT NULL COMMENT 'Correo al que se envio',
  `email_error` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Constancias de pertenencia emitidas';

--
-- Volcado de datos para la tabla `membership_certificates`
--

INSERT INTO `membership_certificates` (`id`, `certificate_number`, `athlete_id`, `issued_at`, `valid_until`, `issued_by`, `email_status`, `email_sent_at`, `email_sent_to`, `email_error`) VALUES
(15, 'CON-2026-0001', 13, '2026-10-13 23:40:07', '2026-11-12', 'Administrador Principal', 'not_sent', NULL, NULL, NULL),
(16, 'CON-2026-0002', 88, '2026-11-09 16:56:48', '2026-12-09', 'Administrador Principal', 'sent', '2026-11-09 16:57:55', 'penarodolfourbano2@gmail.com', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `concept_id` int(11) DEFAULT NULL,
  `athlete_name` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference_number` varchar(40) DEFAULT NULL COMMENT 'Referencia bancaria reportada',
  `payment_method` varchar(30) DEFAULT NULL COMMENT 'transferencia, pago_movil, efectivo, otro',
  `due_date` date NOT NULL,
  `payment_date` date DEFAULT NULL,
  `status` enum('pending','in_review','approved','rejected') NOT NULL DEFAULT 'pending',
  `month` varchar(20) NOT NULL,
  `year` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `reported_by` int(11) DEFAULT NULL COMMENT 'admins.id de quien reporto/registro el pago',
  `reported_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'admins.id de quien aprobo o rechazo',
  `reviewed_at` datetime DEFAULT NULL,
  `review_comment` varchar(500) DEFAULT NULL COMMENT 'Motivo del rechazo o de la reapertura',
  `receipt_image` varchar(255) DEFAULT NULL COMMENT 'Ruta relativa al comprobante de pago adjunto',
  `whatsapp_status` enum('not_sent','sent','failed') NOT NULL DEFAULT 'not_sent',
  `whatsapp_sent_at` datetime DEFAULT NULL,
  `whatsapp_error` text DEFAULT NULL,
  `email_status` enum('not_sent','sent','failed') NOT NULL DEFAULT 'not_sent',
  `email_sent_at` datetime DEFAULT NULL,
  `email_error` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payments`
--

INSERT INTO `payments` (`id`, `athlete_id`, `concept_id`, `athlete_name`, `amount`, `reference_number`, `payment_method`, `due_date`, `payment_date`, `status`, `month`, `year`, `notes`, `reported_by`, `reported_at`, `reviewed_by`, `reviewed_at`, `review_comment`, `receipt_image`, `whatsapp_status`, `whatsapp_sent_at`, `whatsapp_error`, `email_status`, `email_sent_at`, `email_error`, `created_at`, `updated_at`) VALUES
(1, 12, 1, '', 10000.00, NULL, NULL, '2025-12-16', '2025-11-16', 'approved', 'Noviembre', 2025, 'referencia 1233', NULL, NULL, NULL, NULL, NULL, 'img/comprobantes/comprobante_6a3cb30f0bd0c_1782362895.jpg', 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 17:19:36', '2026-10-29 19:18:55'),
(2, 11, 1, '', 10000.00, NULL, NULL, '2025-12-16', NULL, 'pending', 'Noviembre', 2025, 'Pendiente por pagar', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'failed', NULL, 'El pago no está aprobado; no se envía el recibo.', '2025-11-16 17:39:10', '2026-11-13 00:59:28'),
(3, 13, 1, '', 10000.00, NULL, NULL, '2025-12-16', NULL, 'pending', 'Noviembre', 2025, 'referencia 9541', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 17:40:06', '2026-10-29 19:18:55'),
(4, 14, 1, '', 10000.00, NULL, NULL, '2025-12-16', '2025-11-16', 'approved', 'Noviembre', 2025, 'numero de referencia 6520', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 21:14:36', '2026-10-29 19:18:55'),
(5, 14, 1, '', 10000.00, NULL, NULL, '2026-01-16', '2025-11-16', 'approved', 'Diciembre', 2025, 'pago por adelantado de Diciembre, número de referencia 6895', NULL, NULL, NULL, NULL, NULL, 'img/comprobantes/comprobante_6a3cb2f5b8c16_1782362869.png', 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2025-11-16 21:16:55', '2026-10-29 19:18:55'),
(206, 12, 1, '', 10000.00, '01010101010101010101', 'transferencia', '2026-12-09', '2026-11-09', 'in_review', 'Noviembre', 2026, '', 147, '2026-11-09 07:18:17', NULL, NULL, NULL, 'img/comprobantes/comprobante_a240d42fb39048ea_1794223097.jpg', 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 07:10:09', '2026-11-09 07:18:17'),
(213, 87, 2, '', 30000.00, NULL, NULL, '2026-11-16', NULL, 'pending', 'Noviembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:41'),
(214, 87, 1, '', 10000.00, NULL, NULL, '2026-12-09', NULL, 'pending', 'Noviembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:41'),
(215, 87, 1, '', 10000.00, NULL, NULL, '2027-01-09', NULL, 'pending', 'Diciembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:41'),
(216, 87, 1, '', 10000.00, NULL, NULL, '2027-02-09', NULL, 'pending', 'Enero', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:41'),
(217, 87, 1, '', 10000.00, NULL, NULL, '2027-03-09', NULL, 'pending', 'Febrero', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:41'),
(218, 87, 1, '', 10000.00, NULL, NULL, '2027-04-09', NULL, 'pending', 'Marzo', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 13:43:41', '2026-11-09 13:43:41'),
(219, 88, 2, '', 30000.00, '09090909090909090909', 'transferencia', '2026-11-16', '2026-11-09', 'approved', 'Noviembre', 2026, 'Generado automaticamente al inscribir al atleta.', 150, '2026-11-09 16:51:54', 1, '2026-11-09 16:54:14', 'Recibido y Aprobado', 'img/comprobantes/comprobante_771fde05e473835a_1794257514.webp', 'sent', '2026-11-09 16:54:17', NULL, 'sent', '2026-11-09 16:54:20', NULL, '2026-11-09 16:44:06', '2026-11-09 16:54:20'),
(220, 88, 1, '', 10000.00, NULL, NULL, '2026-12-09', NULL, 'pending', 'Noviembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 16:44:06', '2026-11-09 16:44:06'),
(221, 88, 1, '', 10000.00, NULL, NULL, '2027-01-09', NULL, 'pending', 'Diciembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 16:44:06', '2026-11-09 16:44:06'),
(222, 88, 1, '', 10000.00, NULL, NULL, '2027-02-09', NULL, 'pending', 'Enero', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 16:44:06', '2026-11-09 16:44:06'),
(223, 88, 1, '', 10000.00, NULL, NULL, '2027-03-09', NULL, 'pending', 'Febrero', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 16:44:06', '2026-11-09 16:44:06'),
(224, 88, 1, '', 10000.00, NULL, NULL, '2027-04-09', NULL, 'pending', 'Marzo', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-09 16:44:06', '2026-11-09 16:44:06'),
(245, 97, 2, '', 30000.00, NULL, NULL, '2026-11-20', NULL, 'pending', 'Noviembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:35'),
(246, 97, 1, '', 10000.00, NULL, NULL, '2026-12-13', NULL, 'pending', 'Noviembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:35'),
(247, 97, 1, '', 10000.00, NULL, NULL, '2027-01-13', NULL, 'pending', 'Diciembre', 2026, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:35'),
(248, 97, 1, '', 10000.00, NULL, NULL, '2027-02-13', NULL, 'pending', 'Enero', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:35'),
(249, 97, 1, '', 10000.00, NULL, NULL, '2027-03-13', NULL, 'pending', 'Febrero', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:35'),
(250, 97, 1, '', 10000.00, NULL, NULL, '2027-04-13', NULL, 'pending', 'Marzo', 2027, 'Generado automaticamente al inscribir al atleta.', NULL, NULL, NULL, NULL, NULL, NULL, 'not_sent', NULL, NULL, 'not_sent', NULL, NULL, '2026-11-13 06:10:35', '2026-11-13 06:10:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_concepts`
--

CREATE TABLE `payment_concepts` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_monthly` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = se cobra por mes (mensualidad)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment_concepts`
--

INSERT INTO `payment_concepts` (`id`, `name`, `description`, `default_amount`, `is_monthly`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Mensualidad', 'Cuota mensual de la escuela', 10000.00, 1, 1, NULL, '2026-10-29 19:18:53', '2026-10-29 19:38:09'),
(2, 'Inscripcion', 'Pago unico de inscripcion del atleta', 30000.00, 0, 1, NULL, '2026-10-29 19:18:54', '2026-10-29 23:40:34'),
(3, 'Uniforme', 'Uniforme deportivo de la escuela', 40000.00, 0, 1, NULL, '2026-10-29 19:18:54', '2026-10-29 23:41:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `from_status` varchar(20) DEFAULT NULL,
  `to_status` varchar(20) NOT NULL,
  `action` varchar(30) NOT NULL COMMENT 'report, approve, reject, reopen, create, migrate',
  `actor_id` int(11) DEFAULT NULL,
  `actor_name` varchar(100) NOT NULL,
  `actor_role` varchar(20) NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL COMMENT 'Monto vigente al momento del cambio',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `payment_history`
--

INSERT INTO `payment_history` (`id`, `payment_id`, `from_status`, `to_status`, `action`, `actor_id`, `actor_name`, `actor_role`, `comment`, `amount`, `created_at`) VALUES
(1, 1, 'paid', 'approved', 'migrate', NULL, 'Migracion del sistema', 'sistema', 'Pago ya cobrado antes del flujo de verificacion; aprobador original no registrado.', 10000.00, '2026-10-29 19:18:55'),
(2, 4, 'paid', 'approved', 'migrate', NULL, 'Migracion del sistema', 'sistema', 'Pago ya cobrado antes del flujo de verificacion; aprobador original no registrado.', 10000.00, '2026-10-29 19:18:55'),
(3, 5, 'paid', 'approved', 'migrate', NULL, 'Migracion del sistema', 'sistema', 'Pago ya cobrado antes del flujo de verificacion; aprobador original no registrado.', 10000.00, '2026-10-29 19:18:55'),
(71, 206, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado.', 10000.00, '2026-11-09 07:10:09'),
(72, 206, 'pending', 'in_review', 'report', 147, 'Edita Rattia de Escobar', 'representante', NULL, 10000.00, '2026-11-09 07:18:17'),
(79, 213, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 30000.00, '2026-11-09 13:43:41'),
(80, 214, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 13:43:41'),
(81, 215, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 13:43:41'),
(82, 216, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 13:43:41'),
(83, 217, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 13:43:41'),
(84, 218, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 13:43:41'),
(85, 219, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 30000.00, '2026-11-09 16:44:06'),
(86, 220, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 16:44:06'),
(87, 221, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 16:44:06'),
(88, 222, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 16:44:06'),
(89, 223, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 16:44:06'),
(90, 224, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-09 16:44:06'),
(91, 219, 'pending', 'in_review', 'report', 150, 'Rodolfo Peña', 'representante', NULL, 30000.00, '2026-11-09 16:51:54'),
(92, 219, 'in_review', 'approved', 'approve', 1, 'Administrador Principal', 'administrador', 'Recibido y Aprobado', 30000.00, '2026-11-09 16:54:14'),
(113, 245, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 30000.00, '2026-11-13 06:10:35'),
(114, 246, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-13 06:10:35'),
(115, 247, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-13 06:10:35'),
(116, 248, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-13 06:10:35'),
(117, 249, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-13 06:10:35'),
(118, 250, NULL, 'pending', 'create', 1, 'Administrador Principal', 'administrador', 'Cobro generado por el plan de inscripcion.', 10000.00, '2026-11-13 06:10:35');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `payment_reports`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `payment_reports` (
`id` int(11)
,`athlete_id` int(11)
,`first_name` varchar(50)
,`last_name` varchar(50)
,`full_name` varchar(101)
,`category` varchar(50)
,`amount` decimal(10,2)
,`due_date` date
,`payment_date` date
,`status` enum('pending','in_review','approved','rejected')
,`month` varchar(20)
,`year` int(11)
,`notes` text
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pdf_verifications`
--

CREATE TABLE `pdf_verifications` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `generated_by_name` varchar(100) DEFAULT NULL,
  `fingerprint` varchar(64) NOT NULL,
  `payload` longtext NOT NULL,
  `generated_at` datetime DEFAULT current_timestamp(),
  `verified_count` int(11) DEFAULT 0,
  `last_verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pdf_verifications`
--

INSERT INTO `pdf_verifications` (`id`, `token`, `report_type`, `filename`, `generated_by`, `generated_by_name`, `fingerprint`, `payload`, `generated_at`, `verified_count`, `last_verified_at`) VALUES
(1, '45114a8857b98dd769e634f8b9a12a985a139f978093c9631b5a232217316863', 'athlete_list', 'Lista_Atletas.pdf', 1, 'Administrador Principal', '60a6030834b17c2be7ff21369e15d7da1df925ab2c46b7eada7be93a8e15a9fd', '{\"total_athletes\":3,\"filters_applied\":\"Categoría: Infantil\",\"generated_at\":\"25/06/2026 00:20:36\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Lista_Atletas.pdf\",\"report_type\":\"athlete_list\"}', '2026-06-25 00:20:36', 0, NULL),
(2, 'befe9657e316db2d68f40ce0f597e01f849beb5092b352c9b2d24c14a40e5771', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', 'a45e3533ae7b8fbca906a264ff53f1709efd8db95546243408d1970098b23a5a', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:10:40\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:10:40', 0, NULL),
(3, 'da3bf1f71028a6418099022f03bec0ebaf787253c43eca15af34e1782aca7369', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', '6bb53df643e2f20680d4fd623887526ee9bfa3cec888e400a95427085a9f0514', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:14:49\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:14:49', 0, NULL),
(4, 'c47898273c265b9313b3c0939137516520497f8915eb239e183a315744153bc6', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', 'b244f810696701ed859fedea2c519ba72aa883b58bcf816f162505905737260e', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:17:59\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:17:59', 0, NULL),
(5, '80350b14ce8c690a5a0cab6c3f3594d367f9b5f242e7767a09c1b511469d06ca', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', 'af90e152b5c9738c0eca5983e86955808571e02ffb72058a19e0352748b4e719', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:20:17\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:20:17', 0, NULL),
(6, '90e2f02db3becf63fc71173aef86a5af801bd17d438590ef42b33eb119b42db9', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', '480a7788e17a6e0a8fd75ccbae14789ece6858c99cf50361ae164a58b2b6eebf', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:23:26\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:23:26', 0, NULL),
(7, '8d1fd443291b9aaba12fe5af997c51cc78f9bb0991aefec68cfffb020ab332b1', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', '6555ea950f6720a5e5aff98942396ab0df4319484d375fca1f234800d084f9c4', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:25:25\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:25:25', 0, NULL),
(8, 'f52b28d1f62fda7661b4fba07772046c58b88970725e715cb9d83315c3361469', 'payment_history', 'Historial_de_Pago_Ricardo_Escobar.pdf', 1, 'Administrador Principal', '332f2d58c049482cd14ae4b3dfe1bd902ce955b4e4e47e0766e2ef738bea04f8', '{\"athlete_name\":\"Ricardo Escobar\",\"athlete_id_card\":31654321,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Edita Rattia de Escobar\",\"total_payments\":1,\"total_amount\":\"10,000.00\",\"generated_at\":\"25/06/2026 01:28:27\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Historial_de_Pago_Ricardo_Escobar.pdf\",\"report_type\":\"payment_history\"}', '2026-06-25 01:28:27', 0, NULL),
(9, '28ca0ee5ed6035824dd04cada57543e5eaafd4591bf6437d1440320a89375d2e', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', '94db1698278d99a49186375725471efa2698053796f19f37d2e1a6d961091784', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:32:43\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:32:43', 0, NULL),
(10, '617d3b5b310cf008a75a071724a1d039053c367f404a0dc81d698e391c24365b', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', 'd3e0454ac4ec312b854e010cf63b8d8c9b2a49ac3a566a635154456f0cfdf347', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:37:00\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:37:00', 0, NULL),
(11, 'e6d73426dd41dc189b197eb1b29a49513e5c212fa9182a2f728a81486dbe0c1b', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', 'ec46b83df63d21e761acdc56b44a502410a3c06a8c1ddb56f1bc89abb11d7d63', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:38:19\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:38:19', 0, NULL),
(12, '45ab5a6831a09d5ff829e9435623cfc38f8a1655a34ab7c6530c4de19321ed08', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', '400754056b86ba7d1945baa9f58dc337c16e37a009d671864ec17e35f16cb491', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:42:18\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:42:18', 0, NULL),
(13, '72e70313de6fa82c9a0e6c793447e1ee2756304e096a2d65021b00820895b5f3', 'athlete_card', 'Ficha_Atleta_Leonardo_Mora.pdf', 1, 'Administrador Principal', '81eef134f57ad4b3d7f04e3a582d25da0c0ba5fbe8864ea138fcfbf0a7778f97', '{\"athlete_name\":\"Leonardo Mora\",\"athlete_id_card\":32123456,\"athlete_category\":\"Pre-juvenil\",\"athlete_dob\":\"2013-06-12\",\"parent_name\":\"Juan Mora\",\"parent_phone\":\"0412-1234567\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:42:29\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Leonardo_Mora.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:42:29', 0, NULL),
(14, '0f8694fd38aee190b51f2ba96035b71a12de0d891067c1cddac509ad32ace232', 'athlete_list', 'Lista_Atletas.pdf', 1, 'Administrador Principal', '99b11dac961866316f3d60989afb1058054db6c31320ef48b5895493a36e4690', '{\"total_athletes\":3,\"filters_applied\":\"Categoría: Infantil\",\"generated_at\":\"25/06/2026 01:43:54\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Lista_Atletas.pdf\",\"report_type\":\"athlete_list\"}', '2026-06-25 01:43:54', 0, NULL),
(15, '34c2204159300ffd5a7c02df80874d6d1b6214124e6a6f209821a083ed39e4dc', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '88e3de803b8a0448d8b969d545f4ed2b7c72c11c1c30f9120a8c3121d0546e38', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"25/06/2026 01:48:37\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-25 01:48:37', 0, NULL),
(16, '76fde143a7711a2f8c9b6cbe3f4c29d8b0719471fd2bd25f2f80693fbfaebbc6', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '8b2f52bea15525c1bce9ec892d2b95a623d410fbd026192791c84e2b3106b746', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"29/06/2026 21:38:46\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-29 21:38:46', 0, NULL),
(17, 'fdd8b04c1f019658658537d92cb03854d617dea19eefba61eb956f81ff697f05', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '4a85e189c4f2d15ea7a97a6f2d04eecbf2c959cf0580d64dee68e9f349c8a8b4', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"29/06/2026 21:51:51\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-29 21:51:51', 0, NULL),
(18, '0bcf9b2beed9630315a9e59097b8c5f2f9b40b49e1fd14e7b27b8e7f00833b2c', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '2fe49599ea6e4935198cee12580ea9febb11db148f6a38a30967d8f6f0ad2119', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"29/06/2026 22:36:22\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-06-29 22:36:22', 0, NULL),
(19, '2640920141ad8beb92622424ddc2babcb0974391cfebdf37ff6cde802b6975b8', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', 'b97eddafad439cb198bd23b39459182cc8faef3edeee1b572cae7eabb0ff1a6e', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"29/07/2026 19:40:03\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-07-29 19:40:03', 0, NULL),
(20, 'bc6dcf7f7b1075b8f52d40f72c8e4ec866fd0ccec2032ca3804a9dc334a34e29', 'athlete_list', 'Lista_Atletas.pdf', 1, 'Administrador Principal', '1542938056625d37bd671230130549872a2994ad4e8104a2bfa763be38de947d', '{\"total_athletes\":3,\"filters_applied\":\"Categoría: Infantil\",\"generated_at\":\"29/07/2026 19:40:39\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Lista_Atletas.pdf\",\"report_type\":\"athlete_list\"}', '2026-07-29 19:40:39', 0, NULL),
(21, '4b4d2eab1aed6c10496a40598a021ef5a4d66f897580286da8d6c9d619c72a0a', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '15363446de2923eba2694d487e8dd9e7da2e89d220b8dbd3f80cc0f2a9e01181', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"29/07/2026 19:41:43\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-07-29 19:41:43', 0, NULL),
(24, '28874ad9f2f120dfb6f411ac831cac248ba44939e2ba0f5cbfbe9dc8fc0a7ef9', 'payment_history', 'Historial_de_Pago_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '6989085d963d1dd7b67cdeb430ff38eddbc8ed2df1897d5860e1e4235702de85', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"parent_name\":\"Mario González\",\"total_payments\":1,\"total_amount\":0,\"generated_at\":\"04/10/2026 22:59:40\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Historial_de_Pago_Jes_s_Gonz_lez.pdf\",\"report_type\":\"payment_history\"}', '2026-10-04 22:59:40', 0, NULL),
(25, '90f973a3c3d33dc97d18f0cd58e7fc214c1a6c872465f7df344d6b742058f83f', 'payment_history', 'Historial_de_Pago_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '0a4bb2eef393b648a5eb4688958b12ca6cb293c61eb7c1c9a242ad545e394bcd', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"parent_name\":\"Mario González\",\"total_payments\":1,\"total_amount\":0,\"generated_at\":\"04/10/2026 23:03:53\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Historial_de_Pago_Jes_s_Gonz_lez.pdf\",\"report_type\":\"payment_history\"}', '2026-10-04 23:03:53', 3, '2026-10-13 23:31:48'),
(26, '4c352082a134077fe3d64d986f166fc5be676b6d47ba0127fda4441fafc53920', 'athlete_list', 'Lista_Atletas.pdf', 1, 'Administrador Principal', '001fde26ff9cd0b02dcae025bf5f3bae22b4c07548573ff5c20468ecdfc29ebf', '{\"total_athletes\":7,\"filters_applied\":\"Ninguno\",\"generated_at\":\"04/10/2026 23:04:17\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Lista_Atletas.pdf\",\"report_type\":\"athlete_list\"}', '2026-10-04 23:04:17', 3, '2026-10-13 23:31:48'),
(27, 'bf3dc8e4683eab0a65982ecdac8ccb3318a8eca4b78892c9f08248be745841bf', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '1e0e909e8bcba8d7b9006ccf8fa1cf1426142be8fc1ce07e0eaf2c90527f4b66', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"04/10/2026 23:35:52\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-10-04 23:35:52', 0, NULL),
(28, '801f887b375ef59049eb12aee731653ec6744488e474c9bff011eea0d08e390d', 'athlete_card', 'Ficha_Atleta_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', '6b06eebbbe6c5ed1e2f48be97b14f861c2394b5aa2955d2303be91315bfefbc9', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"athlete_dob\":\"2016-08-15\",\"parent_name\":\"Mario González\",\"parent_phone\":\"0416-6549874\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"04/10/2026 23:54:50\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Jes_s_Gonz_lez.pdf\",\"report_type\":\"athlete_card\"}', '2026-10-04 23:54:50', 4, '2026-10-13 23:31:48'),
(39, '8c56a2673ffa016f472988b72c30ca153de706c16a5de913fd5a8a162e81f222', 'enrollment_form', 'Planilla_Inscripcion_Jes_s_Gonz_lez_13.pdf', 1, 'Administrador Principal', 'c7861a7a73fa5460351615644017125bb707b3134517017bf03b080bd2999ad6', '{\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"parent_name\":\"Mario González\",\"registration_date\":\"2025-11-16\",\"generated_at\":\"05/10/2026 00:06:56\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Planilla_Inscripcion_Jes_s_Gonz_lez_13.pdf\",\"report_type\":\"enrollment_form\"}', '2026-10-05 00:06:56', 4, '2026-10-13 23:31:48'),
(70, 'd9708a2d5c1eefe263e65ad58979e8ac15d334fde81b532587546e5fb90ea940', 'payment_receipt', 'Recibo_Pago_Andr__s_Ramirez_5.pdf', 1, 'Administrador Principal', 'd169104dcf7dd96ce8ca748abf0d63808d9d944104b1369bb1348a88c115a3bd', '{\"payment_id\":5,\"athlete_name\":\"Andrés Ramirez\",\"parent_name\":\"Gonzalo Ramírez\",\"amount\":\"10,000.00\",\"month\":\"Diciembre\",\"year\":2025,\"payment_date\":\"2025-11-16\",\"generated_at\":\"11/10/2026 09:46:51\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Recibo_Pago_Andr__s_Ramirez_5.pdf\",\"report_type\":\"payment_receipt\"}', '2026-10-11 09:46:51', 4, '2026-10-13 23:31:48'),
(145, 'eb4a5bbb5a06cad2339f674a6ee5f68afe1fcc28e5a49091bc533858c01e5a7e', 'membership_certificate', 'Constancia_CON-2026-0001_Jes_s_Gonz_lez.pdf', 1, 'Administrador Principal', 'b0d6502e0e3757f287cfe66c388595f8ea1a0d0ca876253a41dd7d1848db38c0', '{\"certificate_number\":\"CON-2026-0001\",\"athlete_id\":13,\"athlete_name\":\"Jesús González\",\"athlete_id_card\":34123456,\"athlete_category\":\"Infantil\",\"registration_date\":\"2025-11-16\",\"parent_name\":\"Mario González\",\"issued_at\":\"2026-10-13 23:40:07\",\"valid_until\":\"2026-11-12\",\"issued_by\":\"Administrador Principal\",\"generated_at\":\"13/10/2026 23:40:08\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Constancia_CON-2026-0001_Jes_s_Gonz_lez.pdf\",\"report_type\":\"membership_certificate\"}', '2026-10-13 23:40:08', 3, '2026-10-29 21:18:30'),
(260, 'c005ee7bde8b22026d96685891a0d3cfcaf23cc2dbb49525d12ebfa41ad9f78e', 'payment_history', 'Historial_de_Pago_Ricardo_Escobar.pdf', 1, 'Administrador Principal', '5934fbc01d60d70518e02777874570ebdc76e07d81cdc8a991565a6f7cfe3469', '{\"athlete_name\":\"Ricardo Escobar\",\"athlete_id_card\":31654321,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Edita Rattia de Escobar\",\"total_payments\":2,\"total_amount\":\"10,000.00\",\"generated_at\":\"09/11/2026 07:28:12\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Historial_de_Pago_Ricardo_Escobar.pdf\",\"report_type\":\"payment_history\"}', '2026-11-09 07:28:12', 0, NULL),
(261, 'bb590a2934465afaa98647b3f989e7a8589c3697bedf89792111ad44441ba335', 'payment_history', 'Historial_de_Pago_Ricardo_Escobar.pdf', 1, 'Administrador Principal', '241f171af0fede3ccf67e143f7c9dfcca9427b430dc58e63291229c72226b02e', '{\"athlete_name\":\"Ricardo Escobar\",\"athlete_id_card\":31654321,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Edita Rattia de Escobar\",\"total_payments\":2,\"total_amount\":\"10,000.00\",\"generated_at\":\"09/11/2026 07:28:48\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Historial_de_Pago_Ricardo_Escobar.pdf\",\"report_type\":\"payment_history\"}', '2026-11-09 07:28:48', 0, NULL),
(262, '223c43753a42f68d03ef9a0433302820a87f68c7a961cba08521bf55520dd073', 'payment_history', 'Historial_de_Pago_Ricardo_Escobar.pdf', 1, 'Administrador Principal', '1b975015255f946bb3a03a3d9fa1f438e061fe080bb6471b1aa18349218ecd65', '{\"athlete_name\":\"Ricardo Escobar\",\"athlete_id_card\":31654321,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Edita Rattia de Escobar\",\"total_payments\":2,\"total_amount\":\"10,000.00\",\"generated_at\":\"09/11/2026 07:35:31\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Historial_de_Pago_Ricardo_Escobar.pdf\",\"report_type\":\"payment_history\"}', '2026-11-09 07:35:31', 0, NULL),
(263, 'c9436b0b9c94a5e249f4b2f8cc731cd801d2475339782492b97065eb434d54d3', 'payment_history', 'Historial_de_Pago_Ricardo_Escobar.pdf', 148, 'Silvanny Serrano', '23aaac60136a76d0f33a208b39b59d4f9edb99892a70f5e0a4cbd8bb66a7066e', '{\"athlete_name\":\"Ricardo Escobar\",\"athlete_id_card\":31654321,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Edita Rattia de Escobar\",\"total_payments\":2,\"total_amount\":\"10,000.00\",\"generated_at\":\"09/11/2026 07:42:08\",\"generated_by\":\"Silvanny Serrano\",\"filename\":\"Historial_de_Pago_Ricardo_Escobar.pdf\",\"report_type\":\"payment_history\"}', '2026-11-09 07:42:08', 0, NULL),
(264, '730cf10bb5962f32d237909c5484b828682231807777ae98ec4b59d4db368671', 'enrollment_form', 'Planilla_Inscripcion_Dylan_Josue_Pic_n_Rodriguez_87.pdf', 1, 'Administrador Principal', '293b4656df31b9f64161edf1e32980e3a6bde9e261e905675530d75291393edb', '{\"athlete_name\":\"Dylan Josue Picón Rodriguez\",\"athlete_id_card\":33465242,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Rolando Picón\",\"registration_date\":\"2026-11-09\",\"generated_at\":\"09/11/2026 13:43:41\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Planilla_Inscripcion_Dylan_Josue_Pic_n_Rodriguez_87.pdf\",\"report_type\":\"enrollment_form\"}', '2026-11-09 13:43:41', 0, NULL),
(265, '70f2e54cd02fbd818a7cbf769a83a039b3dc4ba886aebdc19be0b8a12363b06d', 'enrollment_form', 'Planilla_Inscripcion_Jesus_Mauel_Pe_a_Escalona_88.pdf', 1, 'Administrador Principal', '481b45268e73583e8050499d5bb07294b901a4bbd3d0fa6d0c01048581dfef5c', '{\"athlete_name\":\"Jesus Mauel Peña Escalona\",\"athlete_id_card\":33504840,\"athlete_category\":\"Pre-juvenil\",\"parent_name\":\"Rodolfo Peña\",\"registration_date\":\"2026-11-09\",\"generated_at\":\"09/11/2026 16:44:06\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Planilla_Inscripcion_Jesus_Mauel_Pe_a_Escalona_88.pdf\",\"report_type\":\"enrollment_form\"}', '2026-11-09 16:44:06', 0, NULL),
(266, '42aca202216ef82208f0893d09fc53ccacad67ebe86e8e8a7fac03322fb630e8', 'payment_receipt', 'Recibo_Pago_Jesus_Mauel_Pe__a_Escalona_219.pdf', 1, 'Administrador Principal', '92e0334d9464865ea30f01e7e80e31cfacbe1c0bae2078563600a8e6a412eb32', '{\"payment_id\":219,\"athlete_name\":\"Jesus Mauel Peña Escalona\",\"parent_name\":\"Rodolfo Peña\",\"amount\":\"30,000.00\",\"month\":\"Noviembre\",\"year\":2026,\"payment_date\":\"2026-11-09\",\"generated_at\":\"09/11/2026 16:54:14\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Recibo_Pago_Jesus_Mauel_Pe__a_Escalona_219.pdf\",\"report_type\":\"payment_receipt\"}', '2026-11-09 16:54:14', 0, NULL),
(267, '61327397acd0e36363db83732fc20a926a61a09e24e995d1908ae9a819331d69', 'payment_receipt', 'Recibo_Pago_Jesus_Mauel_Pe__a_Escalona_219.pdf', 1, 'Administrador Principal', '6d2457547f7a4dc7d1f603606873255b8206b58e114c523b378c704351d2cc98', '{\"payment_id\":219,\"athlete_name\":\"Jesus Mauel Peña Escalona\",\"parent_name\":\"Rodolfo Peña\",\"amount\":\"30,000.00\",\"month\":\"Noviembre\",\"year\":2026,\"payment_date\":\"2026-11-09\",\"generated_at\":\"09/11/2026 16:54:17\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Recibo_Pago_Jesus_Mauel_Pe__a_Escalona_219.pdf\",\"report_type\":\"payment_receipt\"}', '2026-11-09 16:54:17', 0, NULL),
(268, 'dc788193fcf1a256d6191ca9158e8647c79f49c77b00a02abe10589b24f1b45d', 'membership_certificate', 'Constancia_CON-2026-0002_Jesus_Mauel_Pe_a_Escalona.pdf', 1, 'Administrador Principal', '91937fbab8b91db4c4df3e72efca4e8c2c57fd8d8b66aa1ee1bdfb014856ee5e', '{\"certificate_number\":\"CON-2026-0002\",\"athlete_id\":88,\"athlete_name\":\"Jesus Mauel Peña Escalona\",\"athlete_id_card\":33504840,\"athlete_category\":\"Pre-juvenil\",\"registration_date\":\"2026-11-09\",\"parent_name\":\"Rodolfo Peña\",\"issued_at\":\"2026-11-09 16:56:48\",\"valid_until\":\"2026-12-09\",\"issued_by\":\"Administrador Principal\",\"generated_at\":\"09/11/2026 16:56:48\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Constancia_CON-2026-0002_Jesus_Mauel_Pe_a_Escalona.pdf\",\"report_type\":\"membership_certificate\"}', '2026-11-09 16:56:48', 0, NULL),
(269, 'a34c84de683afdb15d7764d6bdb58359cf72fadb3e5990b64e2f4af2df77c81f', 'membership_certificate', 'Constancia_CON-2026-0002_Jesus_Mauel_Pe_a_Escalona.pdf', 1, 'Administrador Principal', 'ea1e78a7afc21b6ccfc3895c3e0f87bb792155b83fa733ac3053db0fc4e1cb21', '{\"certificate_number\":\"CON-2026-0002\",\"athlete_id\":88,\"athlete_name\":\"Jesus Mauel Peña Escalona\",\"athlete_id_card\":33504840,\"athlete_category\":\"Pre-juvenil\",\"registration_date\":\"2026-11-09\",\"parent_name\":\"Rodolfo Peña\",\"issued_at\":\"2026-11-09 16:56:48\",\"valid_until\":\"2026-12-09\",\"issued_by\":\"Administrador Principal\",\"generated_at\":\"09/11/2026 16:57:49\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Constancia_CON-2026-0002_Jesus_Mauel_Pe_a_Escalona.pdf\",\"report_type\":\"membership_certificate\"}', '2026-11-09 16:57:49', 0, NULL),
(270, 'd369f4f0f8ded0d9a547621627b1a8900374c3113ea7e43e64ba704eb7801ce9', 'enrollment_form', 'Planilla_Inscripcion_Jesus_Mauel_Pe_a_Escalona_88.pdf', NULL, 'Sistema', '41fc66908d3259b665860de6b8785ec752b1e7f5fb44a9c07f9d7ce29b233872', '{\"athlete_name\":\"Jesus Mauel Peña Escalona\",\"athlete_id_card\":33504840,\"athlete_category\":\"Pre-juvenil\",\"parent_name\":\"Rodolfo Peña\",\"registration_date\":\"2026-11-09\",\"generated_at\":\"13/11/2026 00:59:24\",\"generated_by\":\"Sistema\",\"filename\":\"Planilla_Inscripcion_Jesus_Mauel_Pe_a_Escalona_88.pdf\",\"report_type\":\"enrollment_form\"}', '2026-11-13 00:59:24', 0, NULL),
(271, '837111bf9cfecc69cd6e85f6f3959040dae3fb0fe96e5336ad4bf4949ec19e40', 'enrollment_form', 'Planilla_Inscripcion_Integral_Test_92.pdf', NULL, 'Sistema', '079206dc75330e8c6d3c3641c33e27eccef35c976cd47d63afc28a57be5fe00b', '{\"athlete_name\":\"Integral Test\",\"athlete_id_card\":32554433,\"athlete_category\":\"Juvenil\",\"parent_name\":\"Verificacion Integral\",\"registration_date\":\"2026-11-13\",\"generated_at\":\"13/11/2026 02:40:47\",\"generated_by\":\"Sistema\",\"filename\":\"Planilla_Inscripcion_Integral_Test_92.pdf\",\"report_type\":\"enrollment_form\"}', '2026-11-13 02:40:47', 0, NULL),
(272, '7fc749f2932a3cf62894e42c54c6fb55a4073e27612a4dd31b82d361c281981a', 'athlete_card', 'Ficha_Atleta_Dylan_Josue_Pic_n_Rodriguez.pdf', 148, 'Silvanny Serrano', '7adce9a1ef58b2edf7643c7567989d8427f5a2af753f6590e1af782ab1020992', '{\"athlete_name\":\"Dylan Josue Picón Rodriguez\",\"athlete_id_card\":33465242,\"athlete_category\":\"Juvenil\",\"athlete_dob\":\"2013-06-06\",\"parent_name\":\"Rolando Picón\",\"parent_phone\":\"0414-7199654\",\"registration_date\":\"2026-11-09\",\"generated_at\":\"13/11/2026 02:42:12\",\"generated_by\":\"Silvanny Serrano\",\"filename\":\"Ficha_Atleta_Dylan_Josue_Pic_n_Rodriguez.pdf\",\"report_type\":\"athlete_card\"}', '2026-11-13 02:42:12', 0, NULL),
(273, '8df6148960ed7d8ce5ae4a218c5df05472a89f7df12f2e8a97359a65aac13b6f', 'athlete_card', 'Ficha_Atleta_Dylan_Josue_Pic_n_Rodriguez.pdf', 148, 'Silvanny Serrano', '6afd3f0be5c002b4741fae8b672c0b369f0564048564e2bc7ab0d4b2eae2444f', '{\"athlete_name\":\"Dylan Josue Picón Rodriguez\",\"athlete_id_card\":33465242,\"athlete_category\":\"Juvenil\",\"athlete_dob\":\"2013-06-06\",\"parent_name\":\"Rolando Picón\",\"parent_phone\":\"0414-7199654\",\"registration_date\":\"2026-11-09\",\"generated_at\":\"13/11/2026 02:43:11\",\"generated_by\":\"Silvanny Serrano\",\"filename\":\"Ficha_Atleta_Dylan_Josue_Pic_n_Rodriguez.pdf\",\"report_type\":\"athlete_card\"}', '2026-11-13 02:43:11', 0, NULL),
(274, '9a85c72e23a817260394d9971f93f5f43cdd4503fdecf333339323e4c8eed768', 'athlete_card', 'Ficha_Atleta_Dylan_Josue_Pic_n_Rodriguez.pdf', 1, 'Administrador Principal', '12f4da5bc4f180aac87e8bbb2b6cb1172bb53a0be0cf7d0de0e16e6b7b5581ed', '{\"athlete_name\":\"Dylan Josue Picón Rodriguez\",\"athlete_id_card\":33465242,\"athlete_category\":\"Juvenil\",\"athlete_dob\":\"2013-06-06\",\"parent_name\":\"Rolando Picón\",\"parent_phone\":\"0414-7199654\",\"registration_date\":\"2026-11-09\",\"generated_at\":\"13/11/2026 02:50:28\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Ficha_Atleta_Dylan_Josue_Pic_n_Rodriguez.pdf\",\"report_type\":\"athlete_card\"}', '2026-11-13 02:50:28', 0, NULL),
(275, '6ee79f296ce32d1bd91a39f762f2d6e29fe6f67270e23e59fbe8768cfe4686be', 'payment_history_test', 'payment_history_test.pdf', 1, 'Admin de Prueba', '10507ba3febfa2dc635503fca75c1b6ff8ca7f3efb99d6131ae522cc9d1f4621', '{\"generated_at\":\"13/11/2026 02:52:09\",\"generated_by\":\"Admin de Prueba\",\"filename\":\"payment_history_test.pdf\",\"report_type\":\"payment_history_test\"}', '2026-11-13 02:52:09', 0, NULL),
(276, 'd70b322d8071c8a36d5bbed1e9ea40fbb7995156b487e29e0ffbcbafe5ba80f7', 'enrollment_form', 'Planilla_Inscripcion_David_Escobar_97.pdf', 1, 'Administrador Principal', '54f58fe3db34ab422be218c57955c9d0264a5ff29741dd4f60c99709d59094f8', '{\"athlete_name\":\"David Escobar\",\"athlete_id_card\":\"N/A\",\"athlete_category\":\"Infantil\",\"parent_name\":\"Edita Rattia de Escobar\",\"registration_date\":\"2026-11-13\",\"generated_at\":\"13/11/2026 06:10:36\",\"generated_by\":\"Administrador Principal\",\"filename\":\"Planilla_Inscripcion_David_Escobar_97.pdf\",\"report_type\":\"enrollment_form\"}', '2026-11-13 06:10:36', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `school_bank_account`
--

CREATE TABLE `school_bank_account` (
  `id` tinyint(1) NOT NULL DEFAULT 1,
  `holder_name` varchar(120) DEFAULT NULL COMMENT 'Titular de la cuenta',
  `holder_id` varchar(20) DEFAULT NULL COMMENT 'Cedula o RIF del titular (V-12345678, J-12345678-9)',
  `bank_name` varchar(80) DEFAULT NULL COMMENT 'Banco de la cuenta para transferencias',
  `account_type` varchar(10) DEFAULT NULL COMMENT 'corriente | ahorro',
  `account_number` varchar(20) DEFAULT NULL COMMENT 'Numero de cuenta, 20 digitos',
  `mobile_bank` varchar(80) DEFAULT NULL COMMENT 'Banco del pago movil',
  `mobile_phone` varchar(12) DEFAULT NULL COMMENT 'Telefono del pago movil (0414-1234567)',
  `mobile_id` varchar(20) DEFAULT NULL COMMENT 'Cedula o RIF asociado al pago movil',
  `updated_by` int(11) DEFAULT NULL,
  `updated_by_name` varchar(100) DEFAULT NULL COMMENT 'Copia del nombre: sigue legible si la cuenta cambia',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `school_bank_account`
--

INSERT INTO `school_bank_account` (`id`, `holder_name`, `holder_id`, `bank_name`, `account_type`, `account_number`, `mobile_bank`, `mobile_phone`, `mobile_id`, `updated_by`, `updated_by_name`, `updated_at`) VALUES
(1, 'Henry Daniel Velazquez urbina', 'V-24527809', 'Banco de Venezuela', 'corriente', '01020560310000413066', 'Banco de Venezuela', '0426-2747945', 'V-24527809', 1, 'Administrador Principal', '2026-11-08 16:59:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `school_bank_account_history`
--

CREATE TABLE `school_bank_account_history` (
  `id` int(11) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_by_name` varchar(100) NOT NULL,
  `changed_by_role` varchar(20) NOT NULL,
  `changed_fields` varchar(255) NOT NULL COMMENT 'Campos que cambiaron (sin los valores sensibles)',
  `old_values` text DEFAULT NULL COMMENT 'JSON con los valores anteriores: permite revertir un cambio indebido',
  `new_values` text DEFAULT NULL COMMENT 'JSON con los valores nuevos',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `school_bank_account_history`
--

INSERT INTO `school_bank_account_history` (`id`, `changed_by`, `changed_by_name`, `changed_by_role`, `changed_fields`, `old_values`, `new_values`, `created_at`) VALUES
(12, 1, 'Administrador Principal', 'administrador', 'Titular, Cédula/RIF del titular, Banco, Tipo de cuenta, Número de cuenta, Banco del pago móvil, Teléfono del pago móvil, Cédula/RIF del pago móvil', '{\"holder_name\":\"\",\"holder_id\":\"\",\"bank_name\":\"\",\"account_type\":\"\",\"account_number\":\"\",\"mobile_bank\":\"\",\"mobile_phone\":\"\",\"mobile_id\":\"\"}', '{\"holder_name\":\"Henry Daniel Velazquez urbina\",\"holder_id\":\"V-24527809\",\"bank_name\":\"Banco de Venezuela\",\"account_type\":\"corriente\",\"account_number\":\"01020560310000413066\",\"mobile_bank\":\"Banco de Venezuela\",\"mobile_phone\":\"0426-2747945\",\"mobile_id\":\"V-24527809\"}', '2026-11-08 16:59:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `send_counters`
--

CREATE TABLE `send_counters` (
  `channel` varchar(10) NOT NULL COMMENT 'email | whatsapp',
  `day` date NOT NULL,
  `used` int(11) NOT NULL DEFAULT 0 COMMENT 'Cupos consumidos hoy (reservados, enviados o fallidos). Se incrementa de forma atomica.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `send_counters`
--

INSERT INTO `send_counters` (`channel`, `day`, `used`) VALUES
('email', '2026-10-29', 1),
('email', '2026-11-09', 10),
('email', '2026-11-13', 1),
('whatsapp', '2026-10-29', 1),
('whatsapp', '2026-11-09', 3),
('whatsapp', '2026-11-13', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `send_log`
--

CREATE TABLE `send_log` (
  `id` int(11) NOT NULL,
  `channel` varchar(10) NOT NULL COMMENT 'email | whatsapp',
  `purpose` varchar(40) NOT NULL COMMENT 'receipt, enrollment, certificate, notification, password_reset...',
  `status` varchar(10) NOT NULL COMMENT 'reserved | sent | failed | blocked',
  `recipient_hash` char(16) DEFAULT NULL COMMENT 'Huella parcial del destinatario: permite detectar repeticiones sin guardar su correo ni telefono',
  `detail` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `send_log`
--

INSERT INTO `send_log` (`id`, `channel`, `purpose`, `status`, `recipient_hash`, `detail`, `created_at`) VALUES
(110, 'email', 'document', 'sent', 'f440db56877d3e7f', NULL, '2026-10-29 23:09:59'),
(111, 'whatsapp', 'whatsapp', 'sent', 'e95acccaa3b2652c', NULL, '2026-10-29 23:10:03'),
(112, 'email', 'notification', 'sent', 'f440db56877d3e7f', NULL, '2026-11-09 07:18:18'),
(113, 'email', 'notification', 'sent', '6d30eac4f1a9f576', NULL, '2026-11-09 07:18:20'),
(114, 'email', 'document', 'sent', '7b872d0d86d5601a', NULL, '2026-11-09 13:43:42'),
(115, 'whatsapp', 'whatsapp', 'sent', '85f40408018ad566', NULL, '2026-11-09 13:43:47'),
(116, 'email', 'document', 'sent', 'ce9a5629b0a619fe', NULL, '2026-11-09 16:44:11'),
(117, 'whatsapp', 'whatsapp', 'sent', '1f8ae67d7700b1ee', NULL, '2026-11-09 16:44:17'),
(118, 'email', 'notification', 'sent', 'f440db56877d3e7f', NULL, '2026-11-09 16:51:54'),
(119, 'email', 'notification', 'sent', '6d30eac4f1a9f576', NULL, '2026-11-09 16:51:57'),
(120, 'email', 'notification', 'sent', '70f8db6652a81da8', NULL, '2026-11-09 16:52:00'),
(121, 'whatsapp', 'whatsapp', 'sent', '1f8ae67d7700b1ee', NULL, '2026-11-09 16:54:16'),
(122, 'email', 'document', 'sent', 'ce9a5629b0a619fe', NULL, '2026-11-09 16:54:17'),
(123, 'email', 'document', 'failed', 'ce9a5629b0a619fe', NULL, '2026-11-09 16:56:49'),
(124, 'email', 'document', 'sent', 'ce9a5629b0a619fe', NULL, '2026-11-09 16:57:49'),
(125, 'email', 'document', 'sent', 'e4be6263f8721d3b', NULL, '2026-11-13 06:10:40'),
(126, 'whatsapp', 'whatsapp', 'sent', '9bd63bdec7bc24af', NULL, '2026-11-13 06:10:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `location` varchar(150) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('programado','en_curso','finalizado','cancelado') DEFAULT 'programado',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tournament_participants`
--

CREATE TABLE `tournament_participants` (
  `id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `athlete_id` int(11) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `result` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `training_schedules`
--

CREATE TABLE `training_schedules` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `coach_id` int(11) NOT NULL,
  `coach_name` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `objective` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('activo','pausado','finalizado') DEFAULT 'activo',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `training_schedules`
--

INSERT INTO `training_schedules` (`id`, `title`, `category`, `coach_id`, `coach_name`, `location`, `objective`, `notes`, `status`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 'Plan de Entrenamiento Infantil Temporada 2026', 'Infantil', 156, 'Ernesto Guerrero', 'Campo Principal Maporal', 'Inicio de Entrenamientos para reforzar las diferentes técnicas del juego', 'Los Entrenamiento están sujeto a cambios por las diferentes condiciones climaticas', 'activo', '2026-11-16', '2026-12-16', '2026-11-13 05:03:09', '2026-11-13 05:10:57'),
(2, 'Plan de Entrenamiento Pre-juvenil Temporada 2026', 'Pre-juvenil', 156, 'Ernesto Guerrero', 'Campo Principal Maporal', 'Reforzar las capacidades de los atletas', 'Se recomienda traer agua y ropa ligera', 'activo', '2026-11-16', '2026-12-16', '2026-11-13 05:06:44', '2026-11-13 05:24:18'),
(3, 'Plan de Entrenamiento Juvenil Temporada 2026', 'Juvenil', 156, 'Ernesto Guerrero', 'Campo Principal Maporal', 'Se evaluará las capacidades de los atletas para toma de desiciones en los futuros torneos', 'Habrá premios sorpresas para los que destaquen en los entrenamientos', 'activo', '2026-11-16', '2026-12-16', '2026-11-13 05:09:27', '2026-11-13 05:26:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `training_sessions`
--

CREATE TABLE `training_sessions` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `day_of_week` enum('lunes','martes','miercoles','jueves','viernes','sabado','domingo') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `activity` varchar(200) NOT NULL,
  `intensity` enum('baja','media','alta') DEFAULT 'media',
  `materials` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `training_sessions`
--

INSERT INTO `training_sessions` (`id`, `schedule_id`, `day_of_week`, `start_time`, `end_time`, `activity`, `intensity`, `materials`, `created_at`) VALUES
(10, 1, 'lunes', '15:00:00', '17:00:00', 'Bateo', 'baja', 'Bates, Pelotas, Guantes, Conos', '2026-11-13 05:10:57'),
(11, 1, 'martes', '15:00:00', '17:00:00', 'Picheo', 'media', 'Bates, Pelotas, Guantes, Conos', '2026-11-13 05:10:57'),
(13, 2, 'miercoles', '15:00:00', '17:00:00', 'Bateo', 'media', 'Bates, Pelotas, Guantes, Conos', '2026-11-13 05:24:18'),
(14, 2, 'jueves', '15:00:00', '17:00:00', 'Picheo', 'media', 'Bates, Pelotas, Guantes, Conos', '2026-11-13 05:24:18'),
(17, 3, 'viernes', '17:00:00', '19:00:00', 'Bateo', 'media', 'Bates, Pelotas, Guantes, Conos', '2026-11-13 05:26:05'),
(18, 3, 'sabado', '17:00:00', '19:00:00', 'Picheo', 'alta', 'Bates, Pelotas, Guantes, Conos', '2026-11-13 05:26:05');

-- --------------------------------------------------------

--
-- Estructura para la vista `dashboard_stats`
--
DROP TABLE IF EXISTS `dashboard_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dashboard_stats`  AS SELECT (select count(0) from `athletes`) AS `total_athletes`, (select count(0) from `athletes` where `athletes`.`status` = 'active') AS `active_athletes`, (select count(0) from `payments` where `payments`.`status` = 'approved') AS `total_payments`, (select coalesce(sum(`payments`.`amount`),0) from `payments` where `payments`.`status` = 'approved') AS `total_revenue` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `payment_reports`
--
DROP TABLE IF EXISTS `payment_reports`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `payment_reports`  AS SELECT `p`.`id` AS `id`, `p`.`athlete_id` AS `athlete_id`, `a`.`first_name` AS `first_name`, `a`.`last_name` AS `last_name`, concat(`a`.`first_name`,' ',`a`.`last_name`) AS `full_name`, `a`.`category` AS `category`, `p`.`amount` AS `amount`, `p`.`due_date` AS `due_date`, `p`.`payment_date` AS `payment_date`, `p`.`status` AS `status`, `p`.`month` AS `month`, `p`.`year` AS `year`, `p`.`notes` AS `notes` FROM (`payments` `p` join `athletes` `a` on(`p`.`athlete_id` = `a`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admins_username_status` (`username`,`status`);

--
-- Indices de la tabla `athletes`
--
ALTER TABLE `athletes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_athletes_clearance_number` (`clearance_number`),
  ADD KEY `idx_name` (`first_name`,`last_name`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_registration_date` (`registration_date`),
  ADD KEY `idx_students_full_name` (`first_name`,`last_name`),
  ADD KEY `fk_athletes_status_changed_by` (`status_changed_by`);

--
-- Indices de la tabla `certificate_counters`
--
ALTER TABLE `certificate_counters`
  ADD PRIMARY KEY (`year`);

--
-- Indices de la tabla `clearance_counters`
--
ALTER TABLE `clearance_counters`
  ADD PRIMARY KEY (`year`);

--
-- Indices de la tabla `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_type` (`type`);

--
-- Indices de la tabla `membership_certificates`
--
ALTER TABLE `membership_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_certificate_number` (`certificate_number`),
  ADD KEY `idx_certificate_athlete` (`athlete_id`,`valid_until`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`athlete_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_month_year` (`month`,`year`),
  ADD KEY `idx_payments_student_month_year` (`athlete_id`,`month`,`year`),
  ADD KEY `fk_payments_concept` (`concept_id`),
  ADD KEY `fk_payments_reported_by` (`reported_by`),
  ADD KEY `fk_payments_reviewed_by` (`reviewed_by`),
  ADD KEY `idx_payments_status_reported` (`status`,`reported_at`),
  ADD KEY `idx_payments_reference` (`reference_number`);

--
-- Indices de la tabla `payment_concepts`
--
ALTER TABLE `payment_concepts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_concept_name` (`name`);

--
-- Indices de la tabla `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_history_payment` (`payment_id`,`id`);

--
-- Indices de la tabla `pdf_verifications`
--
ALTER TABLE `pdf_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_report_type` (`report_type`);

--
-- Indices de la tabla `school_bank_account`
--
ALTER TABLE `school_bank_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_school_bank_updated_by` (`updated_by`);

--
-- Indices de la tabla `school_bank_account_history`
--
ALTER TABLE `school_bank_account_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bank_history_created` (`created_at`);

--
-- Indices de la tabla `send_counters`
--
ALTER TABLE `send_counters`
  ADD PRIMARY KEY (`channel`,`day`);

--
-- Indices de la tabla `send_log`
--
ALTER TABLE `send_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_send_log_day` (`channel`,`created_at`);

--
-- Indices de la tabla `tournaments`
--
ALTER TABLE `tournaments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_start_date` (`start_date`),
  ADD KEY `fk_tournament_creator` (`created_by`);

--
-- Indices de la tabla `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tournament_athlete` (`tournament_id`,`athlete_id`),
  ADD KEY `idx_tournament_id` (`tournament_id`),
  ADD KEY `idx_athlete_id` (`athlete_id`);

--
-- Indices de la tabla `training_schedules`
--
ALTER TABLE `training_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coach_id` (`coach_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_id` (`schedule_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT de la tabla `athletes`
--
ALTER TABLE `athletes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT de la tabla `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `membership_certificates`
--
ALTER TABLE `membership_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT de la tabla `payment_concepts`
--
ALTER TABLE `payment_concepts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT de la tabla `pdf_verifications`
--
ALTER TABLE `pdf_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=277;

--
-- AUTO_INCREMENT de la tabla `school_bank_account_history`
--
ALTER TABLE `school_bank_account_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `send_log`
--
ALTER TABLE `send_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT de la tabla `tournaments`
--
ALTER TABLE `tournaments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tournament_participants`
--
ALTER TABLE `tournament_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `training_schedules`
--
ALTER TABLE `training_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `athletes`
--
ALTER TABLE `athletes`
  ADD CONSTRAINT `fk_athletes_status_changed_by` FOREIGN KEY (`status_changed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inv_mov_item_fk` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `membership_certificates`
--
ALTER TABLE `membership_certificates`
  ADD CONSTRAINT `fk_certificate_athlete` FOREIGN KEY (`athlete_id`) REFERENCES `athletes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_concept` FOREIGN KEY (`concept_id`) REFERENCES `payment_concepts` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_reported_by` FOREIGN KEY (`reported_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payments_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`athlete_id`) REFERENCES `athletes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `fk_history_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `school_bank_account`
--
ALTER TABLE `school_bank_account`
  ADD CONSTRAINT `fk_school_bank_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `tournaments`
--
ALTER TABLE `tournaments`
  ADD CONSTRAINT `fk_tournament_creator` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD CONSTRAINT `fk_tp_athlete` FOREIGN KEY (`athlete_id`) REFERENCES `athletes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tp_tournament` FOREIGN KEY (`tournament_id`) REFERENCES `tournaments` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `training_schedules`
--
ALTER TABLE `training_schedules`
  ADD CONSTRAINT `ts_coach_fk` FOREIGN KEY (`coach_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `tss_schedule_fk` FOREIGN KEY (`schedule_id`) REFERENCES `training_schedules` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
