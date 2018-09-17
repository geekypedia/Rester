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
  `secret` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `secret` (`secret`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `organizations` (`id`, `name`, `secret`) VALUES
(1,	'Default Organization',	'206b2dbe-ecc9-490b-b81b-83767288bc5e');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `token` varchar(50) NOT NULL,
  `lease` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `role` varchar(50) DEFAULT 'user',
  `secret` varchar(50) NOT NULL DEFAULT '206b2dbe-ecc9-490b-b81b-83767288bc5e',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `secret`) VALUES
(1,	'superadmin@example.com',	'superadmin',	'17c4520f6cfd1ab53d8745e84681eb49',	'1',	'0000-00-00 00:00:00',	'superadmin', '206b2dbe-ecc9-490b-b81b-83767288bc5e');

INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `secret`) VALUES
(2,	'admin@example.com',	'admin',	'21232f297a57a5a743894a0e4a801fc3',	'1',	'0000-00-00 00:00:00',	'admin', '206b2dbe-ecc9-490b-b81b-83767288bc5e');

INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `secret`) VALUES
(3,	'user@example.com',	'user',	'ee11cbb19052e40b07aac0ca060c23ee',	'1',	'0000-00-00 00:00:00',	'user', '206b2dbe-ecc9-490b-b81b-83767288bc5e');

