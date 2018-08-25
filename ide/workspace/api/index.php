<?php

    //Sample Hello World API. 
	$helloWorldApi = new RouteCommand("GET", "hello", "world", function($params=null){
		$api = new ResterController();
		$value = $api->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
		$api->showResult($value);
	}, array(), "Hello World Api");
	$resterController->addRouteCommand($helloWorldApi);

	//Enable Simple Auth (Make sure to run the scripts below before uncommenting)
	//enable_simple_auth(array());
	
	//Enable Files API (Make sure to run the scripts below before uncommenting)
	//enable_files_api();
	
	/*
		//SQL Script for creating users table
		
		DROP TABLE IF EXISTS `users`;
		CREATE TABLE `users` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `email` varchar(100) NOT NULL,
		  `username` varchar(50) NOT NULL,
		  `password` varchar(100) NOT NULL,
		  `token` varchar(50) NOT NULL,
		  `lease` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		);
		
		//SQL Script for creating files table
	
		DROP TABLE IF EXISTS `files`;
		CREATE TABLE `files` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `file` varchar(512) DEFAULT NULL,
		  PRIMARY KEY (`id`)
		);
	*/

?>
