CREATE DATABASE salamium_test;

USE salamium_test;

CREATE TABLE `countries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB COLLATE utf8_general_ci;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `countries_id` int(10) unsigned NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB COLLATE utf8_general_ci;

CREATE TABLE `users_x_countries` (
  `users_id` int unsigned NOT NULL,
  `countries_id` int unsigned NOT NULL
) ENGINE=InnoDB DEFAULT COLLATE=utf8_czech_ci;

ALTER TABLE `users_x_countries` ADD UNIQUE `users_id_countries_id` (`users_id`, `countries_id`);