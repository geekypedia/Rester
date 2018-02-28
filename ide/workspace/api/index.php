<?php
	$helloWorldApi = new RouteCommand("GET", "hello", "world", function($params=null){
		$api = new ResterController();
		$value = $api->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
		$api->showResult($value);
	}, array(), "Hello World Api");
	$resterController->addRouteCommand($helloWorldApi);
?>
