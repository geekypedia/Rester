<?php

function get_current_api_path(){
	//if(!$resterController) $resterController = new ResterController();
	global $resterController;
	$currentMethod = $_SERVER['REQUEST_METHOD'];
	$currentRoute = $resterController->getCurrentRoute();
	$currentPath = $resterController->getCurrentPath()[0];
	$currentApi = $currentMethod . ' ' . $currentRoute;
	if($currentPath) $currentApi = $currentApi . '/' . $currentPath;
	return $currentApi;
}

function api_get_current_route(){
	return get_current_api_path();
}

//$exclude = array("GET hello/world", "POST users/login");
function check_simple_auth($exclude)
{
		if($exclude){
			if(in_array(get_current_api_path(), $exclude)){
				return true;
			}
		}
		//if(!$resterController) $resterController = new ResterController();
		global $resterController;
		$headers = getallheaders();
		//$allowed_auth_headers = array("api_key", "API_KEY", "Api_Key", "Api_key", "api-key", "API-KEY", "Api-Key", "Api-key");
		$allowed_auth_headers = array("api_key", "api-key");
		$auth_header = $headers['api_key'];
		if(!$auth_header) $auth_header = $_REQUEST['api_key'];
		if(!$auth_header){
			foreach($headers as $key=>$val){
				if(in_array(strtolower($key), $allowed_auth_headers)) $auth_header = $val;
			}
		}
		if($auth_header){
			$value = $resterController->query("select * from users where token='$auth_header' and datediff(now(), lease) = 0");
			if($value){
				return $value;
			}
			else
			{
				$resterController->showErrorWithMessage(401,"Unauthorized");
			}
		}
		else{
			$resterController->showErrorWithMessage(401,"Unauthorized");
		}
}


//$exclude = array("GET ", GET hello/world", "POST users/login");
function check_simple_saas($exclude, $check_request_authenticity = false)
{
	global $resterController;
	
	if((isset($exclude) && in_array(get_current_api_path(), $exclude)) || strpos(get_current_api_path(), "api-doc") > -1){
		return true;
	}
	else{
		if(strpos(get_current_api_path(), "GET") > -1){
			if(!isset($_REQUEST['secret'])){
				$resterController->showErrorWithMessage(403, 'Forbidden. Your secret is safe!');
			}
		}
		if(strpos(get_current_api_path(), "POST") > -1){
			if(!empty($_REQUEST['secret'])){
				$resterController->showErrorWithMessage(403, 'Forbidden. Your secret is safe!');
			}
		}
		if($check_request_authenticity) check_request_authenticity();
	}

}


function check_request_authenticity(){
	$api = new ResterController();

	$headers = getallheaders();
	$api_key = '';
	foreach($headers as $k => $v){
		if(in_array(strtolower($k), array('api-key','api_key'))){
			$api_key = $v;
		}
	}
	
	$request_body = $api->getRequestBody();
	$secret = $request_body['secret'];
	
	if(!empty($secret) && !empty($api_key))
	{
		$val = $api->query("select count(*) as records from users where token='$api_key' and secret='$secret'");
		if (!((count($val) > 0) && $val[0]["records"] > 0)){
			$api->showErrorWithMessage(403, 'Forbidden');
		}
	}
}



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

	//Check if the users table exists
	try{
		$tableExists = $api->query('select 1 from users');
	}
	catch(Exception $e){
		$api->showErrorWithMessage(503, "Can't find table named 'users'. Please check the documentation for more info.");
	}		
	
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
			password (string): password field string,
			token (string): token field string,
			lease (string): lease field string(timestamp)
		}
		where email and username should be marked as UNIQUE index and id as PRIMARY index.
		
		DROP TABLE IF EXISTS `users`;
		CREATE TABLE `users` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `email` varchar(100) NOT NULL,
		  `username` varchar(50) NOT NULL,
		  `password` varchar(100) NOT NULL,
		  `token` varchar(50) NOT NULL,
		  `lease` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		);

	*/
	$result = $api->getObjectsFromRouteName("users", $filter);
	
	
	if($result == null){
		$errorResult = array('error' => array('code' => 401, 'status' => 'Unauthorized'));
		$api->showResult($errorResult);
	}
	else{
		$new_token = uuid();
		$update_id = $result[0]['id'];
		$update_query = "update users set token = '$new_token', lease=now() where id = '$update_id' and ifnull(datediff(now(), lease), 1) > 0";
		$updated = $api->query($update_query);
		
		$result = $api->getObjectsFromRouteName("users", $filter);
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
	check_simple_auth(array("POST users/login", "GET hello/world"));
}


/**
* Sample organization activate command
*/
$approveFunction = function($params = NULL) {
	
	$api = new ResterController();

	//Check if the users table exists
	try{
		$tableExists = $api->query('select 1 from organizations');
	}
	catch(Exception $e){
		$api->showErrorWithMessage(503, "Can't find table named 'organizations'. Please check the documentation for more info.");
	}		
	
	
	$id = $params["id"];
	$secret = $params["secret"];

	$filter['id'] = $id;
	$filter['secret'] = $secret;
	// //Need to pass username/email and password.
	// if($email == null && $username == null)
	// {
	// 	$errorResult = array('error' => array('code' => 422, 'status' => 'Required - username/email'));
	// 	$api->showResult($errorResult);
	// }
	// if($password == null)
	// {
	// 	$errorResult = array('error' => array('code' => 422, 'status' => 'Required - password'));
	// 	$api->showResult($errorResult);
	// }
		
	// //Prefer login through e-mail. Alternately accept username.
	// if($email != null) {
	// 	$filter["email"]=$email;
	// }
	// else {
	// 	$filter["username"]=$username;
	// }
	// $filter["password"]=md5($password);
	
	/*Match details with database. There needs to be a table with the following fields
		users {
			id (integer): id field integer,
			email (string): email field string,
			username (string): username field string,
			password (string): password field string (md5 encrypted),
			token (string): token field string,
			lease (datetime): lease field datetime,
			role (string, optional): role field string ('user', 'admin'),
			secret (string): secret field string
		}
		where email and username should be marked as UNIQUE index and id as PRIMARY index.
		
		organizations {
			id (integer): id field integer,
			name (string): name field string,
			org_secret (string): org_secret field string,
			secret (string, optional): secret field string,
			is_active (integer): is_active field integer
		}
		
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
		  `is_active` tinyint(1) NOT NULL DEFAULT '1',  		  
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		);

		-- SQL Script for creating organizations table that can be used to associate secret key with each unique organization
		DROP TABLE IF EXISTS `organizations`;
		CREATE TABLE `organizations` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `org_secret` varchar(50) NOT NULL,
		  `secret` varchar(50) NOT NULL DEFAULT '206b2dbe-ecc9-490b-b81b-83767288bc5e',
		  `is_active` tinyint(1) NOT NULL DEFAULT '0',  
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `org_secret` (`org_secret`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;

	*/
	$result = $api->getObjectsFromRouteName("organizations", $filter);
	
	
	if($result == null){
		$errorResult = array('error' => array('code' => 404, 'status' => 'Not found'));
		$api->showResult($errorResult);
	}
	else{
		$update_id = $result[0]['id'];
		$update_query = "update organizations set is_active = '1' where id = '$update_id'";
		$updated = $api->query($update_query);

		// $select_query = "select org_secret from organizations where id = '$update_id'";
		// $seleted = $api->query($select_query);
		$org_secret = $result[0]['org_secret'];
		$email = $result[0]['email'];

		$select_query = "select * from users where secret = '$org_secret' and email = '$email'";
		$seleted = $api->query($select_query);
		$user_id = $seleted[0]['id'];
		
		
		if($user_id){
			$activation_query = "update users set is_active = '1', role = 'admin' where id = '$user_id'";
			$activated = $api->query($activation_query);
		} else {
			$activation_query = "INSERT INTO `users` (`email`, `username`, `password`, `token`, `lease`, `role`, `secret`, `is_active`) VALUES ('$email',	'$email',	'21232f297a57a5a743894a0e4a801fc3',	'1',	'0000-00-00 00:00:00',	'admin', '$org_secret', 1)";
			$user_id = $api->query($activation_query);
		}
		
		$resultFilter = array("id" => $user_id);
		$result = $api->getObjectsFromRouteName("users", $resultFilter);
		foreach ($result as &$r) {
			$r['password'] = 'Not visible for security reasons';
		}
		
		$organization = $api->getObjectsFromRouteName("organizations", $filter);
		
		try{
			if(function_exists('on_organization_activated')){
				on_organization_activated($organization, $result);
			}
		} catch (Exception $ex){
			
		}

		$api->showResult($result);
	}

};

$approveCommand = new RouteCommand("POST", "organizations", "activate", $approveFunction, array("org_secret"), "Method to activate an organization.");

if(DEFAULT_SAAS_MODE == true){
	$resterController->addRouteCommand($approveCommand);
	check_simple_saas(array("GET ", "POST users/login", "GET hello/world"));
}


function enable_simple_auth($exclude){
	if(!DEFAULT_LOGIN_API){
		global $resterController, $loginCommand;
		$resterController->addRouteCommand($loginCommand);
		check_simple_auth(array_merge(array("POST users/login", "GET hello/world"), $exclude));
	}
}

function enable_simple_saas($exclude, $check_request_authenticity  = false){
	if(!DEFAULT_SAAS_MODE){
		global $resterController, $approveCommand;
		$resterController->addRouteCommand($approveCommand);
		check_simple_saas(array_merge(array("GET ", "POST users/login", "GET hello/world"), $exclude), $check_request_authenticity);
	}
}


//Test Login using GET
//$loginGetCommand = new RouteCommand("GET", "users", "login", $loginFunction, array("email", "password"), "Method to login users");
//$resterController->addRouteCommand($loginGetCommand);

//Disable oauth authentication for certain routes
$resterController->addPublicMethod("POST", "users/login");

//Add file processor. parameter db_name, db_field. will update the db field based on relative path
/*
	DROP TABLE IF EXISTS `files`;
	CREATE TABLE `files` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `file` varchar(512) DEFAULT NULL,
	  PRIMARY KEY (`id`)
	);
*/
//$resterController->addFileProcessor("files", "file");
if(DEFAULT_FILE_API == true){
	$resterController->addFileProcessor("files", "file");
}

function enable_files_api(){
	if(!DEFAULT_FILE_API){
		global $resterController;
		$resterController->addFileProcessor("files", "file");
	}
}

//Custom API
//$helloWorldApi = new RouteCommand("GET", "hello", "world", function($params=null){
//	$api = new ResterController();
//	$value = $api->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
//	$api->showResult($value);
//}, array(), "Hello World Api");
//$resterController->addRouteCommand($helloWorldApi);

//Include APIs created using IDE
//if(file_exists(__DIR__."/../ide/workspace/api/index.php")){
//        include(__DIR__."/../ide/workspace/api/index.php");
//}

//Include All APIs created using IDE (Including those in sub-folders)
function getAllSubDirectories( $directory, $directory_seperator )
{
	$dirs = array_map( function($item)use($directory_seperator){ return $item . $directory_seperator;}, array_filter( glob( $directory . '*' ), 'is_dir') );

	foreach( $dirs AS $dir )
	{
		$dirs = array_merge( $dirs, getAllSubDirectories( $dir, $directory_seperator ) );
	}

	return $dirs;
}

$apiDirectory = __DIR__.'/../../ide/workspace/api/';

$subDirectories = getAllSubDirectories($apiDirectory,'/');

array_push($subDirectories, $apiDirectory);

foreach($subDirectories as &$subDir){
	$path = $subDir;
	
	$files = array_diff(scandir($path), array('.', '..'));
	foreach ($files as &$file) {
		$filePath = $path.$file;
		if(substr($filePath, -4) == ".php"){
			if(file_exists($filePath)){
			        include($filePath);
			}
		}
	}
}

?>
