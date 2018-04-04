<?php

//Custom Functions
function uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function get_current_api_path(){
	if(!$resterController) $resterController = new ResterController();
	$currentMethod = $_SERVER['REQUEST_METHOD'];
	$currentRoute = $resterController->getCurrentRoute();
	$currentPath = $resterController->getCurrentPath()[0];
	$currentApi = $currentMethod . ' ' . $currentRoute;
	if($currentPath) $currentApi = $currentApi . '/' . $currentPath;
	return $currentApi;
}

//$exclude = array("GET hello/world", "POST hello/world");
function check_simple_auth($exclude)
{
		if($exclude){
			if(in_array(get_current_api_path(), $exclude)){
				return true;
			}
		}
		if(!$resterController) $resterController = new ResterController();
		$headers = getallheaders();
		$auth_header = $headers['api_token'];
		if($auth_header){
			$value = $resterController->query("select * from users where token='$auth_header'");
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
			password (string): password field string,
			token (string): token field string
		}
		where email and username should be marked as UNIQUE index and id as PRIMARY index
	*/
	$result = $api->getObjectsFromRouteName("users", $filter);
	
	
	if($result == null){
		$errorResult = array('error' => array('code' => 401, 'status' => 'Unauthorized'));
		$api->showResult($errorResult);
	}
	else{
		$new_token = uuid();
		$update_id = $result[0]['id'];
		$update_query = "update users set token = '$new_token' where id = '$update_id'";
		$updated = $api->query($update_query);
		if($updated){
			$result = $api->getObjectsFromRouteName("users", $filter);
			foreach ($result as &$r) {
				$r['password'] = 'Not visible for security reasons';
			}
			
			$api->showResult($result);
		}
	}

};

$loginCommand = new RouteCommand("POST", "users", "login", $loginFunction, array("email", "password"), "Method to login users");

//Add the command to controller
//$resterController->addRouteCommand($loginCommand);
if(DEFAULT_LOGIN_API == true){
	$resterController->addRouteCommand($loginCommand);
	check_simple_auth(array("POST users/login", "GET hello/world"));
}

function enable_simple_auth($exclude){
	if(!DEFAULT_LOGIN_API){
		global $resterController;
		$resterController->addRouteCommand($loginCommand);
		check_simple_auth(array_merge(array("POST users/login", "GET hello/world"), $exclude));
	}
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

$apiDirectory = __DIR__.'/../ide/workspace/api/';

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
