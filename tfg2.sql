-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-02-2025 a las 14:00:23
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tfg2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`ID`, `nombre`, `correo`, `contraseña`) VALUES
(1, 'administrador1', 'administrador1@gmail.com', '$2y$10$WraEsTApcp4sTorF3ypBI.2/ymf50NrdXOzS3TTLM26.OEOqDgJkK'),
(2, 'admin', 'admin@gmail.com', '$2y$10$WraEsTApcp4sTorF3ypBI.2/ymf50NrdXOzS3TTLM26.OEOqDgJkK');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquiler`
--

CREATE TABLE `alquiler` (
  `ID` int(30) NOT NULL,
  `estado` tinyint(4) NOT NULL,
  `fecha_Inicio` date NOT NULL,
  `fecha_Final` date DEFAULT NULL,
  `dias_Alquiler` int(11) NOT NULL,
  `precio_Final` decimal(10,2) DEFAULT NULL,
  `cliente_ID` int(11) DEFAULT NULL,
  `punto_Recogida` int(30) DEFAULT NULL,
  `referencia_Recogida` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquiler`
--

INSERT INTO `alquiler` (`ID`, `estado`, `fecha_Inicio`, `fecha_Final`, `dias_Alquiler`, `precio_Final`, `cliente_ID`, `punto_Recogida`, `referencia_Recogida`) VALUES
(167, 0, '2025-02-11', '2025-02-14', 1, 14.54, 52, 7, '04BCDC'),
(168, 0, '2025-02-13', '2025-02-15', 1, 8.75, 52, 5, '993286'),
(169, 0, '2025-02-14', '2025-02-15', 1, 7.00, 52, 5, '690EE4'),
(170, 0, '2025-02-14', '2025-02-15', 1, 7.00, 52, 5, '308D0F');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquiler_videojuegos_plataforma`
--

CREATE TABLE `alquiler_videojuegos_plataforma` (
  `alquiler_ID` int(30) NOT NULL,
  `videojuego_plataforma_ID` int(30) NOT NULL,
  `unidades` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquiler_videojuegos_plataforma`
--

INSERT INTO `alquiler_videojuegos_plataforma` (`alquiler_ID`, `videojuego_plataforma_ID`, `unidades`) VALUES
(167, 307, 1),
(167, 314, 1),
(168, 307, 1),
(169, 307, 1),
(170, 307, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `ID` int(30) NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`ID`, `nombre`) VALUES
(1, 'Acción'),
(2, 'Aventura'),
(3, 'Estrategia'),
(5, 'Arcade'),
(6, 'RPG'),
(7, 'Disparos'),
(8, 'ROL');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `ID` int(11) NOT NULL,
  `DNI` varchar(9) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `monedero` decimal(10,2) NOT NULL,
  `correo` varchar(30) NOT NULL,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `expiracion_token` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`ID`, `DNI`, `nombre`, `contraseña`, `monedero`, `correo`, `token_recuperacion`, `expiracion_token`) VALUES
(28, '45287456B', 'EGFEGE', '$2y$10$9u3cZKQhYHfl6M9JzCAtMOhjNh7s0awoxl84UA6G4sgJyV2qJ6Zwi', 10.00, 'REGER@GMAIL.COM', NULL, NULL),
(37, '12345678C', 'ATLAS', '$2y$10$uLEAK0NzPHc1OPFMNfY/j.rmXnUf7LYqBC2WMQItlKhHwF22G4s/a', 20.00, 'atlas@gmail.com', '6effc70872941c8fe7fc5cd6c43e5be6711d2e976aeadbf97a85f4e9c753290f1774c88f7afd5cec8ff59549add3e707409d', '2025-01-14 00:22:29'),
(52, '12345677p', 'Alejandro', '$2y$10$1ZzhtVd3193mMlf3o1r08ugKphLYJi/NSu82BbbEwforJy3P7sjKa', 88.25, 'alexdoma2001@gmail.com', 'c6305d838876af061e024518cedc58a1c064b09bc79262345ff4c90fe178d9aec2dee6c933875a08c599c4ca473c6ac9d506', '2025-02-14 16:11:12'),
(53, '12345672S', 'dcd', '$2y$10$M/XK3ji8ANdVcL4N/U1WfO0x2vNhVbYPardzsgN/xKdo4Vdm7U3eO', 0.00, 'alexdonmach@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `multa`
--

CREATE TABLE `multa` (
  `ID` int(30) NOT NULL,
  `cliente_ID` int(11) NOT NULL,
  `alquiler_ID` int(30) NOT NULL,
  `valor_Multa` decimal(30,2) NOT NULL,
  `dias_Debidos` int(30) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `multa`
--

INSERT INTO `multa` (`ID`, `cliente_ID`, `alquiler_ID`, `valor_Multa`, `dias_Debidos`, `fecha`) VALUES
(23, 52, 167, 4.85, 2, '2025-02-14'),
(24, 52, 168, 1.75, 1, '2025-02-15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plataforma`
--

CREATE TABLE `plataforma` (
  `ID` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plataforma`
--

INSERT INTO `plataforma` (`ID`, `nombre`) VALUES
(1, 'Playstation 5'),
(2, 'Playstation 4'),
(3, 'Playstation 3'),
(4, 'Playstation 2'),
(6, 'Nintendo Switch'),
(7, 'Nintendo 3ds'),
(8, 'Nintendo DS'),
(9, 'PSP'),
(10, 'XBOX ONE'),
(11, 'XBOX SERIES X'),
(12, 'XBOX 360'),
(14, 'WII'),
(15, 'WII U'),
(50, 'ejemplo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `punto_recogida`
--

CREATE TABLE `punto_recogida` (
  `ID` int(30) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `direccion` varchar(30) NOT NULL,
  `ciudad` varchar(30) NOT NULL,
  `codigo_Postal` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `punto_recogida`
--

INSERT INTO `punto_recogida` (`ID`, `nombre`, `direccion`, `ciudad`, `codigo_Postal`) VALUES
(1, 'Cheap Games Getafe', 'avda Getafe 45', 'Madrid', 28301),
(2, 'Cheap Games Barcelona', 'avda barcelonesa 34', 'Barcelona', 8001),
(3, 'Cheap Games Valencia', 'avda paella ole ole 69', 'Valencia', 46000),
(4, 'Cheap Games Cordoba', 'calle flamenquito 11', 'Cordoba', 41001),
(5, 'Cheap Games Zaragoza', 'calle monasterio 12', 'Zaragoza', 50210),
(6, 'Cheap Games Ourense', 'Av. de Ourense 22', 'Ourense', 29001),
(7, 'Cheap Games Murcia', 'Plaza Circular 4', 'Murcia', 30001),
(8, 'Cheap Games Palma', 'Calle Aragón 56', 'Palma', 7001),
(9, 'Cheap Games Guadalajara', 'Av. Guadalajara 10', 'Guadalajara', 35001),
(10, 'Cheap Games Alicante', 'Av. Alfonso X el Sabio 22', 'Alicante', 3001),
(11, 'Cheap Games Bilbao', 'Gran Vía de Don Diego López de', 'Bilbao', 48001),
(12, 'Cheap Games Córdoba', 'Calle Cruz Conde 12', 'Córdoba', 14001),
(13, 'Cheap Games Valladolid', 'Calle Santiago 5', 'Valladolid', 47001),
(14, 'Cheap Games Vigo', 'Rúa Príncipe 18', 'Vigo', 36201),
(15, 'Cheap Games León', 'Avenida Leonesa', 'Leon', 8901),
(16, 'Cheap Games Gijón', 'Calle Corredero 7', 'Gijón', 33201),
(17, 'Cheap Games Toledo', 'Calle Toledo 11', 'Toledo', 45000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videojuegos`
--

CREATE TABLE `videojuegos` (
  `ID` int(30) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `descripcion` varchar(800) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `videojuegos`
--

INSERT INTO `videojuegos` (`ID`, `nombre`, `descripcion`) VALUES
(41, 'Final Fantasy X', 'La historia de Final Fantasy X sigue a Tidus, una estrella joven del deporte acuático ficticio llamado blitzball, que es transportado a un mundo extraño llamado Spira después de que su ciudad natal sea destruida por una entidad monstruosa conocida como Sin. En Spira, Tidus se une a la invocadora Yuna en su peregrinaje para derrotar a Sin y descubre cómo este mundo y sus conflictos están interconectados con su propio pasado.'),
(43, 'God of War 3', 'Toda la ira de Kratos desatada contra los dioses que lo traicionaron a él y al mundo de la Grecia Antigua. Armado con sus letales Espadas del caos, Kratos debe enfrentarse a las criaturas mitológicas más lúgubres y resolver intrincados acertijos en su despiadada misión para destruir el Olimpo y al mismísimo Zeus.'),
(67, 'Final Fantasy XIII', 'La búsqueda del fal\'Cie de Paals, con intención de salvar a su hermana Serah lleva a Lightning, Sazh y otros personajes que encontraremos en la aventura, como Snow, Hope y Vanille, a una de las zonas más peligrosas de la ciudad. Serah es el motor inicial de esta aventura, y a partir del encuentro de estos personajes, la historia da un giro que no sólo nos trasladará a lugares futuristas, los más tecnológicos de cuantas hemos conocido en la saga, también a otros entornos más abiertos y naturales, en busca de su misión'),
(68, 'Final Fantasy XV', 'Durante la firma de un tratado de paz, Niflheim aprovecha la oportunidad para atacar y tomar el control de Insomnia. Se cree que el rey y el príncipe han muerto, pero en realidad escapan y emprenden un viaje hacia Altissa para reunirse con una superviviente del ataque, Lunafreya. Noctis, el príncipe, junto con sus leales compañeros Ignis, Gladiolus y Prompto, busca venganza contra Niflheim y proteger el cristal de su reino.'),
(69, 'Final Fantasy XVI', 'Esta es la historia de Clive Rosfield, un guerrero al que se le ha concedido el título de \"Primer Escudo de Rosaria\" y que ha jurado proteger a su hermano menor Joshua, el dominador del Fénix. En poco tiempo, Clive se verá envuelto en una gran tragedia y jurará vengarse del Eikon Oscuro Ifrit, una misteriosa entidad que trae la calamidad a su paso'),
(70, 'God of War', 'Atenas ha sido arrasada por la guerra. Tu eres Kratos, un feroz guerrero Espartano ahora desterrado, al que solo le mueven la ira y su sed de venganza. Tu destino ahora es la lucha en búsqueda de la única arma capaz de destruir a Ares, Dios de la Guerra: la Caja de Pandora.'),
(74, 'Call of Duty Finest Hour', 'Desde la perspectiva de los soldados de los tres principales países aliados: la Unión Soviética, Estados Unidos y el Reino Unido. La historia, con tintes de superproducción, nos cuenta no solo el transcurrir de la guerra sino también la experiencia personal de cada uno de los soldados.'),
(75, 'Call of Duty 2', 'En Call of Duty 2 volveremos a disfrutar de un juego de acción en primera persona basado en la Segunda Guerra Mundial en el que tendremos que hacer frente a innumerables desafíos a lo largo de tres campañas diferentes que viviremos encarnando a un soldado soviético, a otro británico, y a uno estadounidense, en diferentes frentes de combate que harán que la experiencia de juego sea de lo más variada.'),
(76, 'Call of Duty 3', 'La tercera entrega de la popular saga de acción, nuevamente ambientada en la Segunda Guerra Mundial, en concreto en la brutal y decisiva batalla de Normandía, con una espectacular campaña para un jugador y un ambicioso multijugador online hasta para 24 jugadores.'),
(77, 'Call of Duty 4 Modern Warfare', 'La campaña para un jugador se sitúa en un mundo ficticio donde un golpe de estado en un país del Medio Oriente y el ascenso al poder de un ultranacionalista ruso desencadenan una serie de eventos que amenazan la estabilidad global.\r\nEl modo multijugador también fue revolucionario, introduciendo un sistema de progresión de personajes que permitía a los jugadores desbloquear armas, accesorios y ventajas a medida que ganaban experiencia.'),
(78, 'God of War 2', 'Nos volvemos a poner en la piel de Kratos, ya convertido en el nuevo Dios de la Guerra, quien tras asesinar a Ares es traicionado por Zeus, despojado de su divinidad y asesinado. Salvado de la muerte por la Titán Gaia, Kratos es enviado en una misión para encontrar a las Hermanas del Destino, con la esperanza de cambiar su pasado y vengarse de Zeus'),
(79, 'Call of Duty World at War', 'Ambientado en la Segunda Guerra Mundial, con una aventura de acción en primera persona que cuenta con una campaña protagonizada por dos personajes: un marine en la Guerra del Pacífico, y un soldado soviético en el Frente oriental, saltando entre ambos escenarios a lo largo de múltiples misiones, lugares que no había explorado anteriormente la saga.'),
(80, 'Call of Duty Modern Warfare 2', 'Después de cambiar para siempre los juegos de acción con el primer Modern Warfare, su secuela volvía a elevar el listón del género con una campaña más espectacular si cabe y sobre todo, un modo multijugador competitivo imbatible, que se convirtió en el estándar del género y la experiencia a imitar durante más de una década');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videojuegos_categoria`
--

CREATE TABLE `videojuegos_categoria` (
  `id` int(11) NOT NULL,
  `videojuego_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `videojuegos_categoria`
--

INSERT INTO `videojuegos_categoria` (`id`, `videojuego_id`, `categoria_id`) VALUES
(282, 41, 1),
(283, 41, 2),
(284, 41, 6),
(287, 43, 1),
(288, 43, 2),
(294, 67, 1),
(295, 67, 2),
(296, 67, 6),
(248, 68, 1),
(249, 68, 2),
(161, 69, 1),
(162, 69, 2),
(305, 70, 1),
(306, 70, 2),
(212, 74, 1),
(213, 74, 7),
(229, 75, 1),
(230, 75, 7),
(231, 76, 1),
(232, 76, 7),
(233, 77, 1),
(234, 77, 7),
(235, 78, 1),
(236, 78, 2),
(237, 79, 1),
(238, 79, 7),
(256, 80, 1),
(257, 80, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `videojuegos_plataforma`
--

CREATE TABLE `videojuegos_plataforma` (
  `ID` int(30) NOT NULL,
  `videojuego_id` int(30) NOT NULL,
  `plataforma_id` int(11) NOT NULL,
  `unidades` int(11) NOT NULL DEFAULT 0,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `videojuegos_plataforma`
--

INSERT INTO `videojuegos_plataforma` (`ID`, `videojuego_id`, `plataforma_id`, `unidades`, `precio`) VALUES
(212, 69, 1, 9, 5.00),
(244, 74, 4, 4, 2.00),
(259, 75, 12, 0, 2.00),
(260, 76, 3, 0, 2.00),
(261, 76, 4, 0, 2.00),
(262, 76, 12, 0, 1.00),
(263, 76, 14, 0, 1.00),
(264, 77, 3, 0, 3.00),
(265, 77, 8, 0, 3.00),
(266, 77, 12, 0, 3.00),
(267, 77, 14, 0, 0.00),
(268, 78, 4, 0, 3.00),
(269, 79, 3, 0, 2.00),
(270, 79, 12, 0, 2.00),
(271, 79, 14, 0, 3.00),
(284, 68, 2, 0, 4.00),
(301, 80, 3, 0, 3.00),
(302, 80, 12, 0, 3.00),
(307, 41, 2, 10, 7.00),
(308, 41, 3, 0, 5.00),
(309, 41, 4, 25, 4.00),
(310, 41, 6, 0, 5.00),
(314, 43, 2, 12, 2.69),
(315, 43, 3, 0, 4.00),
(320, 67, 3, 0, 2.00),
(321, 67, 12, 0, 2.00),
(326, 70, 4, 0, 1.00),
(329, 75, 50, 1, 11.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `alquiler`
--
ALTER TABLE `alquiler`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `cliente_ID` (`cliente_ID`),
  ADD KEY `punto_Recogida` (`punto_Recogida`);

--
-- Indices de la tabla `alquiler_videojuegos_plataforma`
--
ALTER TABLE `alquiler_videojuegos_plataforma`
  ADD PRIMARY KEY (`alquiler_ID`,`videojuego_plataforma_ID`),
  ADD KEY `videojuego_plataforma_ID` (`videojuego_plataforma_ID`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `DNI` (`DNI`);

--
-- Indices de la tabla `multa`
--
ALTER TABLE `multa`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_alquiler` (`alquiler_ID`),
  ADD KEY `cliente_ID` (`cliente_ID`);

--
-- Indices de la tabla `plataforma`
--
ALTER TABLE `plataforma`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `punto_recogida`
--
ALTER TABLE `punto_recogida`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `videojuegos`
--
ALTER TABLE `videojuegos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `videojuegos_categoria`
--
ALTER TABLE `videojuegos_categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `videojuego_id` (`videojuego_id`,`categoria_id`),
  ADD KEY `fk_videojuegos_categoria_categoria` (`categoria_id`);

--
-- Indices de la tabla `videojuegos_plataforma`
--
ALTER TABLE `videojuegos_plataforma`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_videojuego_plataforma` (`videojuego_id`,`plataforma_id`),
  ADD KEY `plataforma_id` (`plataforma_id`),
  ADD KEY `videojuego_id` (`videojuego_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `alquiler`
--
ALTER TABLE `alquiler`
  MODIFY `ID` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `ID` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `multa`
--
ALTER TABLE `multa`
  MODIFY `ID` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `plataforma`
--
ALTER TABLE `plataforma`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `punto_recogida`
--
ALTER TABLE `punto_recogida`
  MODIFY `ID` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `videojuegos`
--
ALTER TABLE `videojuegos`
  MODIFY `ID` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de la tabla `videojuegos_categoria`
--
ALTER TABLE `videojuegos_categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310;

--
-- AUTO_INCREMENT de la tabla `videojuegos_plataforma`
--
ALTER TABLE `videojuegos_plataforma`
  MODIFY `ID` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alquiler`
--
ALTER TABLE `alquiler`
  ADD CONSTRAINT `alquiler_ibfk_1` FOREIGN KEY (`cliente_ID`) REFERENCES `cliente` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `alquiler_ibfk_2` FOREIGN KEY (`punto_Recogida`) REFERENCES `punto_recogida` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alquiler_cliente` FOREIGN KEY (`cliente_ID`) REFERENCES `cliente` (`ID`),
  ADD CONSTRAINT `fk_alquiler_punto_recogida` FOREIGN KEY (`punto_Recogida`) REFERENCES `punto_recogida` (`ID`);

--
-- Filtros para la tabla `alquiler_videojuegos_plataforma`
--
ALTER TABLE `alquiler_videojuegos_plataforma`
  ADD CONSTRAINT `alquiler_videojuegos_plataforma_ibfk_1` FOREIGN KEY (`alquiler_ID`) REFERENCES `alquiler` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `alquiler_videojuegos_plataforma_ibfk_2` FOREIGN KEY (`videojuego_plataforma_ID`) REFERENCES `videojuegos_plataforma` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `multa`
--
ALTER TABLE `multa`
  ADD CONSTRAINT `fk_multa_alquiler` FOREIGN KEY (`alquiler_ID`) REFERENCES `alquiler` (`ID`),
  ADD CONSTRAINT `multa_ibfk_1` FOREIGN KEY (`alquiler_ID`) REFERENCES `alquiler` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `videojuegos_categoria`
--
ALTER TABLE `videojuegos_categoria`
  ADD CONSTRAINT `fk_videojuegos_categoria_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`ID`),
  ADD CONSTRAINT `fk_videojuegos_categoria_videojuegos` FOREIGN KEY (`videojuego_id`) REFERENCES `videojuegos` (`ID`),
  ADD CONSTRAINT `videojuegos_categoria_ibfk_1` FOREIGN KEY (`videojuego_id`) REFERENCES `videojuegos` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `videojuegos_categoria_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`ID`) ON DELETE CASCADE;

--
-- Filtros para la tabla `videojuegos_plataforma`
--
ALTER TABLE `videojuegos_plataforma`
  ADD CONSTRAINT `videojuegos_plataforma_ibfk_1` FOREIGN KEY (`videojuego_id`) REFERENCES `videojuegos` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `videojuegos_plataforma_ibfk_2` FOREIGN KEY (`plataforma_id`) REFERENCES `plataforma` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
