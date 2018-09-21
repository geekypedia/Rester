<?php

	//Sample Custom API
	$prestige->register("GET", "hello", "world", function($params=null){
		global $prestige;
		$value = $prestige->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
		$prestige->showResult($value);
	}, array(), "Hello World Api");


	//List of excluded APIs for Simple Authentication and Saas Mode
	//Examples
	//$excluded = array("POST users/forgot-password", "POST users/reset-password");
	$excluded = array();
	
	//Enable Simple Auth (Make sure to run the scripts below before uncommenting)
	//enable_simple_auth($excluded);

	//Check request authenticity in SaaS mode.
	//$check_request_authenticity=true;

	//Enable Simple SaaS Mode (Make sure to run the scripts below before uncommenting)
	//enable_simple_saas($excluded, $check_request_authenticity);
	
	//Enable Files API (Make sure to run the scripts below before uncommenting)
	//enable_files_api();
	
	/*
		-- SQL Script for creating users table. role and secret are only useful if you enable SaaS mode, but won't harm if you keep them even if SaaS mode is disabled.
		
		DROP TABLE IF EXISTS `users`;
		CREATE TABLE `users` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `email` varchar(100) NOT NULL,
		  `username` varchar(50) NOT NULL,
		  `password` varchar(100) NOT NULL,
		  `token` varchar(50) NOT NULL,
		  `lease` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  `role` varchar(50) DEFAULT 'user',
		  `is_active` tinyint(1) NOT NULL DEFAULT '1',  		  
		  `secret` varchar(50) NOT NULL DEFAULT '206b2dbe-ecc9-490b-b81b-83767288bc5e',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		);
		
		-- Insert 3 default users
		
		INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `secret`, `is_active`) VALUES
		(1,	'superadmin@example.com',	'superadmin',	'17c4520f6cfd1ab53d8745e84681eb49',	'1',	'0000-00-00 00:00:00',	'superadmin', '206b2dbe-ecc9-490b-b81b-83767288bc5e', 1);
		
		INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `secret`, `is_active`) VALUES
		(2,	'admin@example.com',	'admin',	'21232f297a57a5a743894a0e4a801fc3',	'1',	'0000-00-00 00:00:00',	'admin', '206b2dbe-ecc9-490b-b81b-83767288bc5e', 1);
		
		INSERT INTO `users` (`id`, `email`, `username`, `password`, `token`, `lease`, `role`, `secret`, `is_active`) VALUES
		(3,	'user@example.com',	'user',	'ee11cbb19052e40b07aac0ca060c23ee',	'1',	'0000-00-00 00:00:00',	'user', '206b2dbe-ecc9-490b-b81b-83767288bc5e', 1);


		-- SQL Script for creating organizations table that can be used to associate secret key with each unique organization
		DROP TABLE IF EXISTS `organizations`;
		CREATE TABLE `organizations` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `email` varchar(100) NOT NULL,
		  `license` varchar(15) NOT NULL DEFAULT 'basic',
		  `validity` datetime NOT NULL,  
		  `is_active` tinyint(1) NOT NULL DEFAULT '0',  
		  `org_secret` varchar(50) NOT NULL,
		  `secret` varchar(50) NOT NULL DEFAULT '206b2dbe-ecc9-490b-b81b-83767288bc5e',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `org_secret` (`org_secret`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;

		
		-- Insert a default organization
		
		INSERT INTO `organizations` (`id`, `name`, `email`, `license`, `validity`, `org_secret`, `secret`, `is_active`) VALUES
		(1,	'Default Organization',	'superadmin@example.com', 'super',	'0000-00-00 00:00:00', '206b2dbe-ecc9-490b-b81b-83767288bc5e',	'206b2dbe-ecc9-490b-b81b-83767288bc5e', 1);

	
		-- SQL Script for creating files table
	
		DROP TABLE IF EXISTS `files`;
		CREATE TABLE `files` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `file` varchar(512) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		);
	*/

?>
