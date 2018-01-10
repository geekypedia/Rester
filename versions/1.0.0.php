<?php

/**
* Sample custom login command
*/
//Create the command
$loginCommand = new RouteCommand("POST", "users", "login", function($params = NULL) {
	
	global $resterController;
	
	$filter["login"]=$params["login"];
	$filter["password"]=md5($params["password"]);
	
	$result = $resterController->getObjectsFromRouteName("usuarios", $filter);

	$resterController->showResult($result);
	
}, array("login", "password"), "Method to login users");

//Add the command to controller
$resterController->addRouteCommand($loginCommand);

//Disable oauth authentication for certain routes
$resterController->addPublicMethod("POST", "users/login");

//Add file processor. parameter db_name, db_field. will update the db field based on relative path
//$resterController->addFileProcessor("files", "file");
if(DEFAULT_FILE_API == true){
	$resterController->addFileProcessor("files", "file");
}


//Custom API
$helloWorldApi = new RouteCommand("GET", "hello", "world", function($params=null){
	$api = new ResterController();
	$value = $api->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
	$api->showResult($value);
}, array(), "Hello World Api");
$resterController->addRouteCommand($helloWorldApi);

?>
