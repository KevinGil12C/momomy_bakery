-- Momomy Bakery - Database Backup
-- Generated: 2026-02-24 16:19:11
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('1', 'Pedro Picapiedra', 'pedro@roca.com', '¿Tienen pasteles de piedra? Es broma, busco algo de mármol.', '0', '2026-02-24 10:00:00');
INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `is_read`, `created_at`) VALUES ('2', 'Ana Karenina', 'ana@literatura.com', 'Me urge un pastel para hoy mismo de chocolate.', '0', '2026-02-24 12:30:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('1', '1', '3', '1', '320.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('2', '2', '4', '3', '120.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('3', '3', '3', '2', '320.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('4', '4', '2', '1', '180.00');
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES ('5', '5', '1', '2', '450.00');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES ('1', NULL, 'Juan', 'Pérez', 'juan@gmail.com', '5551234567', '450.00', 'pending', 'unpaid', 'Entregar después de las 4pm', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES ('2', NULL, 'Elena', 'Rodríguez', 'elena.r@hotmail.com', '5559876543', '1150.00', 'completed', 'unpaid', 'Para fiesta de cumpleaños', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES ('3', NULL, 'Roberto', 'Sánchez', 'roberto123@yahoo.com', '5552223344', '320.00', 'pending', 'unpaid', '', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES ('4', NULL, 'Laura', 'Torres', 'laura.torres@outlook.com', '5556667788', '780.00', 'pending', 'unpaid', 'Sin nueces por favor', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `orders` (`id`, `user_id`, `customer_first_name`, `customer_last_name`, `customer_email`, `customer_phone`, `total_amount`, `status`, `payment_status`, `notes`, `created_at`, `updated_at`) VALUES ('5', NULL, 'Miguel', 'Ángel', 'miguel.a@empresa.com', '5554441122', '120.00', 'completed', 'unpaid', 'Facturar por favor', '2026-02-24 15:39:12', '2026-02-24 15:39:12');

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
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES ('1', '1', 'Pastel de Chocolate Premium', 'Pastel húmedo con cobertura de ganache', '450.00', '10', 'pastel-chocolate.jpg', '1', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES ('2', '2', 'Cupcakes de Vainilla (6 pzas)', 'Suaves panquecitos decorados', '180.00', '25', 'cupcakes-vainilla.jpg', '1', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES ('3', '3', 'Tarta de Frutos Rojos', 'Tarta artesanal con fruta fresca', '320.00', '5', 'tarta-frutos.jpg', '1', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES ('4', '4', 'Galletas de Chispas (Docena)', 'Receta tradicional crujiente', '120.00', '50', 'galletas-chispas.jpg', '1', '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `stock`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES ('5', '1', 'Cheesecake de New York', 'Clásico horneado con base de galleta', '380.00', '8', 'cheesecake.jpg', '1', '2026-02-24 15:39:12', '2026-02-24 15:39:12');

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

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('1', 'Admin', 'Momomy', 'admin@momomy.com', '/momomy_bakery/public/uploads/users/1771971289_avatar_2_698a27c4d8996.png', '1', '$2y$10$SwzHXApHaBjW5r6C.q0qs.kZLh/chMayMsWtKjnrEJ01/ubcorYMu', NULL, NULL, '2026-02-24 13:28:47', '2026-02-24 16:14:49');
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('2', 'Mariana', 'García', 'mariana@momomy.com', NULL, '1', '$2y$10$NGCGibKaThTGJV9bK8hfLeetMQ4g7xakVSmK/RnDUlLT4EtJBPDc.', NULL, NULL, '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('3', 'Carlos', 'Ramírez', 'carlos@cliente.com', NULL, '2', '$2y$10$rcC7TRz.tLLNLrgiD/V5S.7tcjGoWejGv1HXuPt3wnZiO3o.MvAw.', NULL, NULL, '2026-02-24 15:39:12', '2026-02-24 15:39:12');
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `avatar_url`, `role_id`, `password`, `two_factor_secret`, `email_verified_at`, `created_at`, `updated_at`) VALUES ('4', 'Sofía', 'López', 'sofia.reposteria@gmail.com', NULL, '1', '$2y$10$oKnA4yZOMzKjVXOFpnFTXeCp6uSAqhl6z7taYZDEMN3jYrhz3FgR2', NULL, NULL, '2026-02-24 15:39:12', '2026-02-24 15:39:12');

SET FOREIGN_KEY_CHECKS=1;