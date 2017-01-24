CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `countries_id` int(10) unsigned NULL,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB COLLATE utf8_general_ci;

CREATE TABLE `countries` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB COLLATE utf8_general_ci;

CREATE TABLE `users_x_books` (
  `user_id` tinyint(3) unsigned NOT NULL,
  `book_id` tinyint(3) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT COLLATE=utf8_czech_ci;

ALTER TABLE `users_x_books` ADD UNIQUE `user_id_book_id` (`user_id`, `book_id`);