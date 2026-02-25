-- Momomy Bakery - Database Backup
-- Generated: 2026-02-25 16:57:20
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `business_settings`;
CREATE TABLE `business_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `business_settings` (`id`, `business_name`, `address`, `email`, `phone`, `tax_id`, `logo_url`, `updated_at`) VALUES ('1', 'Momomy Bakery', 'Av. Dulce 123, Ciudad de México, CP 01000', 'kebo.jcg77@gmail.com', '5512345678', NULL, NULL, '2026-02-24 15:29:44');

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `created_at`) VALUES ('1', 'Pasteles', NULL, 'pasteles', '2026-02-24 13:28:46');
INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `created_at`) VALUES ('2', 'Panes', NULL, 'panes', '2026-02-24 13:28:46');
INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `created_at`) VALUES ('3', 'Galletas', NULL, 'galletas', '2026-02-24 13:28:46');
INSERT INTO `categories` (`id`, `name`, `description`, `slug`, `created_at`) VALUES ('4', 'Donas', NULL, 'donas', '2026-02-24 13:28:46');

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('1', 'Pedro Picapiedra', 'pedro@roca.com', '¿Tienen pasteles de piedra? Es broma, busco algo de mármol.', '0', '2026-02-24 10:00:00');
INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('2', 'Ana Karenina', 'ana@literatura.com', 'Me urge un pastel para hoy mismo de chocolate.', '0', '2026-02-24 12:30:00');
INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('3', 'Roberto Gómez', 'roberto@test.com', '¿Hacen pasteles sin gluten? Mi esposa es celíaca y me encantaría sorprenderla.', '0', '2026-02-25 16:56:06');
INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('4', 'Diana Flores', 'diana.f@mail.com', 'Quisiera cotizar una mesa de postres para 80 personas, es para una boda en junio.', '0', '2026-02-25 16:56:06');
INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('5', 'Arturo Vidal', 'arturo@empresa.com', '¿Tienen servicio de entrega a domicilio los domingos? Necesito para un evento familiar.', '0', '2026-02-25 16:56:06');

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES ('1', 'María', 'González', 'maria@prueba.com', '$2y$10$f8uwxskp1KjAZjwpzIlhq.U528SKOXV3byvap61nsoxpJ3UYC3hn2', '5551001001', '2026-02-25 16:56:06');
INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES ('2', 'Carlos', 'Hernández', 'carlos@prueba.com', '$2y$10$f8uwxskp1KjAZjwpzIlhq.U528SKOXV3byvap61nsoxpJ3UYC3hn2', '5551002002', '2026-02-25 16:56:06');
INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES ('3', 'Lucía', 'Martínez', 'lucia@prueba.com', '$2y$10$f8uwxskp1KjAZjwpzIlhq.U528SKOXV3byvap61nsoxpJ3UYC3hn2', '5551003003', '2026-02-25 16:56:06');
INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES ('4', 'Fernando', 'Díaz', 'fernando@prueba.com', '$2y$10$f8uwxskp1KjAZjwpzIlhq.U528SKOXV3byvap61nsoxpJ3UYC3hn2', '5551004004', '2026-02-25 16:56:06');
INSERT INTO `customers` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `created_at`) VALUES ('5', 'Valentina', 'Rojas', 'valentina@prueba.com', '$2y$10$f8uwxskp1KjAZjwpzIlhq.U528SKOXV3byvap61nsoxpJ3UYC3hn2', '5551005005', '2026-02-25 16:56:06');

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news` (`id`, `title`, `content`, `image_url`, `is_published`, `created_at`) VALUES ('1', '🎂 ¡Nuevo Sabor! Pastel Red Velvet ya disponible', 'Hemos agregado a nuestro menú el clásico Red Velvet con cream cheese frosting. Un sabor aterciopelado que combina lo mejor del cacao con un toque único. Disponible en porciones individuales y pastel completo. ¡Ven a probarlo antes de que se agoten!', NULL, '1', '2026-02-25 16:56:06');
INSERT INTO `news` (`id`, `title`, `content`, `image_url`, `is_published`, `created_at`) VALUES ('2', '🍪 Taller de Galletas Decoradas - Sábado 8 de Marzo', 'Te invitamos a nuestro taller especial donde aprenderás a decorar galletas con royal icing. Incluye todos los materiales, 12 galletas para llevar y un delicioso café artesanal. Cupo limitado a 15 personas. Reserva tu lugar llamando al 5512345678.', NULL, '1', '2026-02-25 16:56:06');
INSERT INTO `news` (`id`, `title`, `content`, `image_url`, `is_published`, `created_at`) VALUES ('3', '💕 Promo San Valentín Extendida', 'Por demanda popular, extendemos nuestra promoción de San Valentín todo el mes de marzo: 2x1 en cupcakes de vainilla y 20% de descuento en pasteles de chocolate para parejas. ¡El amor sabe mejor con Momomy!', NULL, '1', '2026-02-25 16:56:06');

DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('1', 'maria@prueba.com', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('2', 'carlos@prueba.com', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('3', 'lucia@prueba.com', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('4', 'ana.belen@correo.com', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('5', 'diego.flores@empresa.mx', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('6', 'sofia.martinez@gmail.com', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('7', 'pedro.navaja@hotmail.com', '2026-02-25 16:56:06');
INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES ('8', 'camila.reyes@outlook.com', '2026-02-25 16:56:06');

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('1', '1', '3', '1', '320.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('2', '2', '4', '3', '120.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('3', '3', '3', '2', '320.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('4', '4', '2', '1', '180.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('5', '5', '1', '2', '450.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('6', '6', '1', '2', '450.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('7', '7', '3', '1', '320.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('8', '7', '2', '1', '180.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('9', '8', '5', '1', '380.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('10', '9', '4', '2', '120.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('11', '10', '1', '3', '450.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('12', '11', '3', '2', '320.00');

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `customer_first_name` varchar(100) DEFAULT NULL,
  `customer_last_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `notes` text DEFAULT NULL,
  `tracking_token` varchar(64) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_token` (`tracking_token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('1', NULL, 'Juan', 'Pérez', 'juan@gmail.com', '5551234567', '450.00', 'pending', 'unpaid', 'Entregar después de las 4pm', 'd2fd4bfd8384e369585ebe4e3625c914', NULL, '2026-02-24 15:39:12', '2026-02-25 15:11:43');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('2', NULL, 'Elena', 'Rodríguez', 'elena.r@hotmail.com', '5559876543', '1150.00', 'completed', 'unpaid', 'Para fiesta de cumpleaños', 'baba0687d255118c5e7208b1d60322a4', NULL, '2026-02-24 15:39:12', '2026-02-25 15:11:43');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('3', NULL, 'Roberto', 'Sánchez', 'roberto123@yahoo.com', '5552223344', '320.00', 'pending', 'unpaid', '', '9ad8e9485b9cea436fbf5c93e00f9869', NULL, '2026-02-24 15:39:12', '2026-02-25 15:11:43');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('4', NULL, 'Laura', 'Torres', 'laura.torres@outlook.com', '5556667788', '780.00', 'pending', 'unpaid', 'Sin nueces por favor', 'a6d1a3e20641021d37c4167a75654a2f', NULL, '2026-02-24 15:39:12', '2026-02-25 15:11:43');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('5', NULL, 'Miguel', 'Ángel', 'miguel.a@empresa.com', '5554441122', '120.00', 'completed', 'unpaid', 'Facturar por favor', 'd4d7c8cbbe3bb79db010d2284283b777', NULL, '2026-02-24 15:39:12', '2026-02-25 15:11:43');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('6', NULL, 'María', 'González', 'maria@prueba.com', '5551001001', '900.00', 'completed', 'paid', 'Entregar en la puerta del edificio B', 'dff7e0981b34093b7523878ca473b476', '1', '2026-02-25 16:56:06', '2026-02-25 16:56:06');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('7', NULL, 'Carlos', 'Hernández', 'carlos@prueba.com', '5551002002', '500.00', 'processing', 'paid', 'Para fiesta de oficina', '43e47834c5db09ee58480cc95fb4dfb8', '2', '2026-02-25 16:56:06', '2026-02-25 16:56:06');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('8', NULL, 'Lucía', 'Martínez', 'lucia@prueba.com', '5551003003', '380.00', 'pending', 'unpaid', '', '67c0cf785d5ab6eed41a117c5e6191c6', '3', '2026-02-25 16:56:06', '2026-02-25 16:56:06');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('9', NULL, 'Fernando', 'Díaz', 'fernando@prueba.com', '5551004004', '240.00', 'completed', 'paid', 'Sin azúcar extra por favor', '74d6ad703221ce0836ccc8fadde8a2ed', '4', '2026-02-25 16:56:06', '2026-02-25 16:56:06');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('10', NULL, 'Valentina', 'Rojas', 'valentina@prueba.com', '5551005005', '1350.00', 'processing', 'paid', 'Evento corporativo, entregar antes de las 10AM', '75c2494394e34944864e6e65e2fd4876', '5', '2026-02-25 16:56:06', '2026-02-25 16:56:06');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `tracking_token`, `customer_id`, `created_at`, `updated_at`) VALUES ('11', NULL, 'María', 'González', 'maria@prueba.com', '5551001001', '640.00', 'pending', 'unpaid', 'Segunda compra, ahora quiero de frutos rojos', '761fe42e297c7a863258c0817e5430b8', '1', '2026-02-25 16:56:06', '2026-02-25 16:56:06');

DROP TABLE IF EXISTS `product_comments`;
CREATE TABLE `product_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `rating` int(11) DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('1', '1', '1', '¡Increíblemente delicioso! El mejor pastel de chocolate que he probado en mi vida. La cobertura de ganache es perfección pura.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('2', '1', '2', 'Muy bueno, aunque me hubiera gustado un poco más de relleno. La textura es suave y húmeda.', '4', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('3', '1', '3', 'Lo pedí para el cumpleaños de mi hija y todos quedaron encantados. Definitivamente lo vuelvo a pedir.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('4', '1', '4', 'Buen sabor pero la porción me pareció un poco pequeña para el precio. Aun así, calidad premium.', '3', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('5', '2', '1', 'Están monísimos y saben delicioso. Perfectos para llevar a la oficina.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('6', '2', '5', 'Ricos pero demasiado dulces para mi gusto. La decoración es muy linda.', '3', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('7', '2', '3', 'Mis hijos los amaron, la vainilla se siente natural y no artificial. ¡Excelente!', '4', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('8', '3', '2', 'Una explosión de sabores frescos. La combinación de fresas y frambuesas es genial.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('9', '3', '4', 'Hermosa presentación y sabor balance ado entre dulce y ácido. Me encantó.', '4', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('10', '3', '5', 'La mejor tarta que he probado en esta zona. La recomiendo al 100%.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('11', '4', '1', 'Crujientes por fuera, suaves por dentro. ¡Justo como me gustan! Adictivas.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('12', '4', '3', 'Buenas pero no superan a las de mi abuela jaja. Aun así muy ricas.', '4', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('13', '4', '2', 'Compré dos veces en la misma semana, eso dice todo sobre estas galletas.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('14', '5', '4', 'Cremoso, suave y con una base de galleta perfecta. Auténtico estilo New York.', '5', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('15', '5', '5', 'Demasiado denso para mi gusto, pero el sabor es muy bueno. Se nota la calidad.', '3', '2026-02-25 16:56:06');
INSERT INTO `product_comments` (`id`, `product_id`, `user_id`, `comment`, `rating`, `created_at`) VALUES ('16', '5', '1', '¡Lo AMO! Es mi postre favorito de toda la tienda. Nunca me canso de pedirlo.', '5', '2026-02-25 16:56:06');

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_specialty` tinyint(1) DEFAULT 0,
  `is_special_of_week` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`, `is_specialty`, `is_special_of_week`) VALUES ('1', '1', 'Pastel de Chocolate Premium', 'Pastel húmedo con cobertura de ganache', '450.00', '10', '/momomy_bakery/public/uploads/products/1772055905_avatar_1_697a40ea4302e.png', '1', '2026-02-24 15:39:12', '2026-02-25 16:27:24', '1', '1');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`, `is_specialty`, `is_special_of_week`) VALUES ('2', '2', 'Cupcakes de Vainilla (6 pzas)', 'Suaves panquecitos decorados', '180.00', '25', 'cupcakes-vainilla.jpg', '1', '2026-02-24 15:39:12', '2026-02-25 16:29:37', '0', '0');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`, `is_specialty`, `is_special_of_week`) VALUES ('3', '3', 'Tarta de Frutos Rojos', 'Tarta artesanal con fruta fresca', '320.00', '5', 'tarta-frutos.jpg', '1', '2026-02-24 15:39:12', '2026-02-25 15:29:50', '0', '0');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`, `is_specialty`, `is_special_of_week`) VALUES ('4', '4', 'Galletas de Chispas (Docena)', 'Receta tradicional crujiente', '120.00', '50', 'galletas-chispas.jpg', '1', '2026-02-24 15:39:12', '2026-02-25 16:27:52', '0', '0');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`, `is_specialty`, `is_special_of_week`) VALUES ('5', '1', 'Cheesecake de New York', 'Clásico horneado con base de galleta', '380.00', '8', 'cheesecake.jpg', '1', '2026-02-24 15:39:12', '2026-02-25 16:45:50', '1', '0');

DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_first_name` varchar(100) DEFAULT NULL,
  `customer_last_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(150) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','sent','accepted','rejected') DEFAULT 'sent',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quotations` (`id`, `customer_first_name`, `customer_last_name`, `customer_email`, `subject`, `content`, `pdf_path`, `sent_at`, `status`) VALUES ('1', 'Beatriz', 'Luna', 'beatriz@boda.com', 'Presupuesto para Mesa de Postres - Boda', 'Hola Beatriz, el costo por 50 personas con mini tartas y cupcakes es de $3,500.', NULL, '2026-02-24 15:39:12', 'sent');
INSERT INTO `quotations` (`id`, `customer_first_name`, `customer_last_name`, `customer_email`, `subject`, `content`, `pdf_path`, `sent_at`, `status`) VALUES ('2', 'Gimnasio', 'Fitness', 'contacto@gym.com', 'Cotización Galletas Saludables', 'Cotizamos la caja de galletas de avena por mayoreo (100 pzas) en $800.', NULL, '0000-00-00 00:00:00', 'draft');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES ('1', 'admin', 'Administrador con acceso total', '2026-02-24 15:31:05');
INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES ('2', 'customer', 'Cliente con acceso limitado', '2026-02-24 15:31:05');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `is_2fa_enabled` tinyint(1) DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` varchar(100) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_user_role` (`role_id`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `is_2fa_enabled`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('1', 'Admin', 'Momomy', 'kekg150@gmail.com', '/momomy_bakery/public/uploads/users/1771971289_avatar_2_698a27c4d8996.png', '1', '0', '$2y$10$SwzHXApHaBjW5r6C.q0qs.kZLh/chMayMsWtKjnrEJ01/ubcorYMu', NULL, NULL, '2026-02-24 13:28:47', '2026-02-25 15:12:49');
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `is_2fa_enabled`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('2', 'Mariana', 'García', 'mariana@momomy.com', NULL, '1', '0', '$2y$10$NGCGibKaThTGJV9bK8hfLeetMQ4g7xakVSmK/RnDUlLT4EtJBPDc.', NULL, NULL, '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `is_2fa_enabled`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('3', 'Carlos', 'Ramírez', 'carlos@cliente.com', NULL, '2', '0', '$2y$10$rcC7TRz.tLLNLrgiD/V5S.7tcjGoWejGv1HXuPt3wnZiO3o.MvAw.', NULL, NULL, '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `is_2fa_enabled`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('4', 'Sofía', 'López', 'sofia.reposteria@gmail.com', NULL, '1', '0', '$2y$10$oKnA4yZOMzKjVXOFpnFTXeCp6uSAqhl6z7taYZDEMN3jYrhz3FgR2', NULL, NULL, '2026-02-24 15:39:12', '2026-02-24 15:39:12');

SET FOREIGN_KEY_CHECKS=1;