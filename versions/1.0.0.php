<?php

/**
* Sample custom login command
*/
//Create the command
// $loginCommand = new RouteCommand("POST", "users", "login", function($params = NULL) {
// 	global $resterController;
// 	$filter["login"]=$params["login"];
// 	$filter["password"]=md5($params["password"]);
// 	$result = $resterController->getObjectsFromRouteName("users", $filter);
// 	$resterController->showResult($result);
// }, array("login", "password"), "Method to login users");

$loginFunction = function($params = NULL) {
	
	$api = new ResterController();

	$email = $params["email"];
	$username = $params["username"];
	$password = $params["password"];
	
	
	//Need to pass username/email and password.
	if($email == null && $username == null)
	{
		$errorResult = array('error' => array('code' => 422, 'status' => 'Required - username/email'));
		$api->showResult($errorResult);
	}
	if($password == null)
	{
		$errorResult = array('error' => array('code' => 422, 'status' => 'Required - password'));
		$api->showResult($errorResult);
	}
		
	//Prefer login through e-mail. Alternately accept username.
	if($email != null) {
		$filter["email"]=$email;
	}
	else {
		$filter["username"]=$username;
	}
	$filter["password"]=md5($password);
	
	/*Match details with database. There needs to be a table with the following fields
		users {
			id (integer): id field integer,
			email (string): email field string,
			username (string): username field string,
			password (string): password field string
		}
		where email and username should be marked as UNIQUE index and id as PRIMARY index
	*/
	$result = $api->getObjectsFromRouteName("users", $filter);
	
	
	if($result == null){
		$errorResult = array('error' => array('code' => 401, 'status' => 'Unauthorized'));
		$api->showResult($errorResult);
	}
	else{
		foreach ($result as &$r) {
			$r['password'] = 'Not visible for security reasons';
		}
		
		$api->showResult($result);
	}

};

$loginCommand = new RouteCommand("POST", "users", "login", $loginFunction, array("email", "password"), "Method to login users");

//Add the command to controller
//$resterController->addRouteCommand($loginCommand);
if(DEFAULT_LOGIN_API == true){
	$resterController->addRouteCommand($loginCommand);
}


//Test Login using GET
//$loginGetCommand = new RouteCommand("GET", "users", "login", $loginFunction, array("email", "password"), "Method to login users");
//$resterController->addRouteCommand($loginGetCommand);

//Disable oauth authentication for certain routes
$resterController->addPublicMethod("POST", "users/login");

//Add file processor. parameter db_name, db_field. will update the db field based on relative path
//$resterController->addFileProcessor("files", "file");
if(DEFAULT_FILE_API == true){
	$resterController->addFileProcessor("files", "file");
}


//Custom API
//$helloWorldApi = new RouteCommand("GET", "hello", "world", function($params=null){
//	$api = new ResterController();
//	$value = $api->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
//	$api->showResult($value);
//}, array(), "Hello World Api");
//$resterController->addRouteCommand($helloWorldApi);

//Include APIs created using IDE
if(file_exists(__DIR__."/../ide/workspace/api/index.php")){
        include(__DIR__."/../ide/workspace/api/index.php");
}
?>
