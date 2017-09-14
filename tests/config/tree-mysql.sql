DROP TABLE IF EXISTS menu;

CREATE TABLE `menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parent_id` int unsigned NULL,
  `left` int unsigned NOT NULL,
  `right` int unsigned NOT NULL,
  `deep` smallint unsigned NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE='MyISAM' COLLATE 'utf8_czech_ci';