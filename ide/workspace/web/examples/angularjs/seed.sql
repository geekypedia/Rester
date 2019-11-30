SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `organizations`;
CREATE TABLE `organizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `license` varchar(15) NOT NULL DEFAULT 'basic',
  `validity` datetime NOT NULL,  
  `org_secret` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',  
  `secret` varchar(50) NOT NULL DEFAULT '206b2dbe-ecc9-490b-b81b-83767288bc5e',
  PRIMARY KEY (`id`),
  UNIQUE KEY `org_secret` (`org_secret`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `organizations` (`id`, `name`, `email`, `license`, `validity`, `is_active`, `org_secret`, `secret`) VALUES
(1,	'Default Organization',	'superadmin@example.com', 'super',	'0000-00-00 00:00:00', 1, '206b2dbe-ecc9-490b-b81b-83767288bc5e',	'206b2dbe-ecc9-490b-b81b-83767288bc5e');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `token` varchar(50) NOT NULL DEFAULT '00000000-00000-0000-0000-000000000000',
  `lease` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `role` varchar(50) DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',  
  `secret` varchar(50) NOT NULL DEFAULT '206b2dbe-ecc9-490b-b81b-83767288bc5e',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `is_active`, `secret`) VALUES
(1,	'superadmin@example.com',	'superadmin',	'17c4520f6cfd1ab53d8745e84681eb49',	'1',	'0000-00-00 00:00:00',	'superadmin', 1, '206b2dbe-ecc9-490b-b81b-83767288bc5e');

INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `is_active`, `secret`) VALUES
(2,	'admin@example.com',	'admin',	'21232f297a57a5a743894a0e4a801fc3',	'1',	'0000-00-00 00:00:00',	'admin', 1, '206b2dbe-ecc9-490b-b81b-83767288bc5e');

INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `is_active`, `secret`) VALUES
(3,	'user@example.com',	'user',	'ee11cbb19052e40b07aac0ca060c23ee',	'1',	'0000-00-00 00:00:00',	'user', 1, '206b2dbe-ecc9-490b-b81b-83767288bc5e');

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `secret` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `user_groups`;
CREATE TABLE `user_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `secret` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`),
  UNIQUE KEY `user_id_group_id_secret` (`user_id`,`group_id`,`secret`),  
  CONSTRAINT `user_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `user_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `secret` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `secret` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


