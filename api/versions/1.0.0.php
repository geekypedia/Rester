<?php

$M = array(
	"USER_404" => "User does not exist!",
	"USERS_TABLE_404" => "Can't find table named 'users'. Please check the documentation for more info.",
	"ORGANIZATION_404" => "Organization does not exist!",
	"ORGANIZATIONS_TABLE_404" => "Can't find table named 'organizations'. Please check the documentation for more info.",
	"DUPLICATE_ORG_EMAIL" => "This email is already registered with an organization. Please use a different one!",
	"DUPLICATE_USER_EMAIL" => "This email is already registered with a user. Please use a different one!",
	"SECRET_FORBIDDEN" => "Forbidden. Your secret is safe!",
	"USER_ORGANIZATION_INACTIVE" => "User belongs to an inactive organization!",
	"ORGANIZATION_LICENSE_EXPIRED" => "Organization license is expired! Please renew to continue using the system.",
	"USERNAME_EMAIL_REQUIRED" => "Required - username/email",
	"PASSWORD_REQUIRED" => "Required - password",
	"USERNAME_EMAIL_INVALID" => "Invalid email/username or password!",
	"USER_INACTIVE" => "Inactive user!",
	"PASSWORD_MASK" => "Not visible for security reasons",
	"INVALID_OPERATION" => "The operation you just tried to perform is not valid!",
	"UNAUTHORIZED_ACTION" => "You are not authorized to perform this action!",
	);

function M($key, $message=null){
	global $M;
	if(is_array($key) && empty($message)){
		foreach ($key as $k => $v) {
			$M[$k] = $v;
		}
		return;
	}
	if(empty($key) && empty($message)){
		return $M;
	}
	if(empty($message)){
		return $M[$key];
	} else {
		$M[$key] = $message;
	}
}

function M_load($route, $key = 'key', $message = 'message'){
	global $resterController;
	$resterController->find($route);
	foreach ($route as $r) {
		if(!empty($r[$key]) && !empty($r[$message])){
			M($r[$key], $r[$message]);	
		}
	}
}

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

function excluded_routes(){
	$excluded_routes = array(
				"POST users/login", 
				"POST users/forgot-password", 
				"POST users/set-password", 
				"POST users/change-password", 
				"POST users/register", 
				"POST organizations/register", 
				"GET hello/world"
				);
	
	
	if(!(EXCLUDED_ROUTES)){
	}else{
		$decoded_excluded_routes = json_decode(EXCLUDED_ROUTES);
		if(is_array($decoded_excluded_routes)){
			$excluded_routes = array_merge($excluded_routes, $decoded_excluded_routes);
		}
	}
	
	
	return $excluded_routes;
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
				$resterController->showError(401);
			}
		}
		else{
			$resterController->showError(401);
		}
}


//$exclude = array("GET ", GET hello/world", "POST users/login");
function check_simple_saas($exclude, $check_request_authenticity = false)
{
	global $resterController;
	
	if((isset($exclude) && in_array(get_current_api_path(), $exclude)) || strpos(get_current_api_path(), "api-doc") > -1 || strpos(get_current_api_path(), "files") > -1){
		return true;
	}
	else{
		if(strpos(get_current_api_path(), "GET") > -1){
			if(!isset($_REQUEST['secret'])){
				$resterController->showError(403, M('SECRET_FORBIDDEN'));
			}
		}
		if(strpos(get_current_api_path(), "POST") > -1){
		    $body = $resterController->getPostData();
			if(empty($body['secret'])){
				
				$headers = getallheaders();
				$secret = '';
				foreach($headers as $k => $v){
					if(in_array(strtolower($k), array('secret'))){
						$secret = $v;
					}
				}
				$secret = empty($secret) ? $_REQUEST['$secret'] : $secret;
				
				if(empty($secret)){
					$resterController->showError(403, M('SECRET_FORBIDDEN'));	
				}
				
			}
		}
		if($check_request_authenticity) check_request_authenticity();
		
	}

}


function check_request_authenticity(){
	global $resterController;
	$api = $resterController;

	$headers = getallheaders();
	$api_key = '';
	foreach($headers as $k => $v){
		if(in_array(strtolower($k), array('api-key','api_key'))){
			$api_key = $v;
		}
	}
	
	$api_key = empty($api_key) ? (empty($_REQUEST['api-key']) ? $_REQUEST['api_key'] : $_REQUEST['api-key']) : $api_key;
		
	$request_body = $api->getRequestBody();
	$secret = empty($request_body['secret']) ? $_REQUEST['secret'] : $request_body['secret'];

	
	if(!empty($secret) && !empty($api_key))
	{
		$val = $api->query("select count(*) as records from users where token='$api_key' and secret='$secret'");
		if (!((count($val) > 0) && $val[0]["records"] > 0)){
			$api->showError(403, M('UNAUTHORIZED_ACTION'));
		}
	}
}

function check_response_authenticity($result){
	global $resterController;
	$api = $resterController;

	$request_body = $api->getRequestBody();
	$secret = empty($request_body['secret']) ? $_REQUEST['secret'] : $request_body['secret'];


	if(!empty($secret) && !empty($result))
	{
		if (!($secret == $result['secret'])){
			$api->showError(403, M('UNAUTHORIZED_ACTION'));
		}
	}
}

function check_organization_is_active($secret){
	global $resterController;

	try {
		if(!empty($secret)){
			$val = $resterController->query("select count(*) as records from organizations where org_secret='$secret' and is_active=1");
			if (!((count($val) > 0) && $val[0]["records"] > 0)){
				$resterController->showError(401, M('USER_ORGANIZATION_INACTIVE'));
			}
			$val = $resterController->query("select count(*) as records from (select *, if(curdate() > validity and license not in ('basic', 'super'), 'expired', 'valid') as license_status from organizations where org_secret='$secret') as o where license_status = 'valid'");
			if (!((count($val) > 0) && $val[0]["records"] > 0)){
				$resterController->showError(401, M('ORGANIZATION_LICENSE_EXPIRED'));
			}
		}
	} catch (Exception $ex){
		
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
	
	global $resterController;
	$api = $resterController;

	//Check if the users table exists
	try{
		$tableExists = $api->query('select 1 from users');
	}
	catch(Exception $e){
		$api->showError(503, M('USERS_TABLE_404'));
	}		
	
	$email = $params["email"];
	$username = $params["username"];
	$password = $params["password"];
	
	
	//Need to pass username/email and password.
	if($email == null && $username == null)
	{
		$errorResult = $api->errorResponse(422, M('USERNAME_EMAIL_REQUIRED'));
		$api->showResult($errorResult);
	}
	if($password == null)
	{
		$errorResult = $api->errorResponse(422, M('PASSWORD_REQUIRED'));
		$api->showResult($errorResult);
	}
		
	//Prefer login through e-mail. Alternately accept username.
	if($email != null) {
		$filter["email"]=$email;
	}
	else {
		$filter["username"]=$username;
	}
	
	$user_exists = $api->getObjectsFromRouteName("users", $filter);
	
	$filter["password"]=md5($password);
	
	/*Match details with database. There needs to be a table with the following fields
		users {
			id (integer): id field integer,
			email (string): email field string,
			username (string): username field string,
			password (string): password field string,
			token (string): token field string,
			lease (string): lease field string(timestamp),
			is_active (integer): is_active field integer
		}
		where email and username should be marked as UNIQUE index and id as PRIMARY index.
		
		DROP TABLE IF EXISTS `users`;
		CREATE TABLE `users` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `email` varchar(100) NOT NULL,
		  `username` varchar(50) NOT NULL,
		  `password` varchar(100) NOT NULL,
		  `token` varchar(50) NOT NULL DEFAULT '00000000-00000-0000-0000-000000000000',
		  `lease` datetime NOT NULL DEFAULT '0000-01-01 00:00:00',
		  `is_active` tinyint(1) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `email` (`email`)
		);

	*/
	$result = $api->getObjectsFromRouteName("users", $filter);
	
	
	if($result == null){
		if(!$user_exists){
			$api->showError(404, M("USER_404"));
		}
		$api->showError(401, M('USERNAME_EMAIL_INVALID'));
	}
	else{
		$is_active = $result[0]['is_active'];
		if(!$is_active){
			$api->showError(401, M('USER_INACTIVE'));
		}
		$new_token = uuid();
		$update_id = $result[0]['id'];
		$update_query = "update users set token = '$new_token', lease=now() where id = '$update_id' and ifnull(datediff(now(), lease), 1) > 0";
		$updated = $api->query($update_query);
		
		$result = $api->getObjectsFromRouteName("users", $filter);
		foreach ($result as &$r) {
			$r['password'] = M('PASSWORD_MASK');
		}

		$user = $result[0];
		
		//Applicable in SaaS mode
		check_organization_is_active($user["secret"]);

		//Applicable in SaaS mode
		try{
			$org_filter["org_secret"] = $user["secret"];
			$organizations = $api->getObjectsFromRouteName("organizations", $org_filter);
			if(!empty($organizations)) {
				if(in_array($user['role'], array("superadmin"))){
					$user[organization] = $organizations[0];
				} else if(in_array($user['role'], array("admin"))){
					$user[organization] = array(
												"name" => $organizations[0]["name"],
												"email" => $organizations[0]["email"],
												"secret" => $organizations[0]["org_secret"],
												"license" => $organizations[0]["license"],
												"validity" => $organizations[0]["validity"]
											);
				} else {
					$user[organization] = array("name" => $organizations[0]["name"]);
				}
			}
		} catch (Exception $ex){
			
		}
		
		if(function_exists('on_login')){
			on_login($user);
		}

		$api->showResult($user);
	}

};

$loginCommand = new RouteCommand("POST", "users", "login", $loginFunction, array("email", "password"), "Method to login users");


$setPasswordFunction = function($params = NULL) {
	global $resterController;
	$api = $resterController;
	
	$filter['email'] = $params['admin_email'];
	$filter['password'] = md5($params['admin_password']);
	
	$result = $api->getObjectsFromRouteName("users", $filter);

	if(count($result) > 0){

		if(!(in_array(($result[0]["role"]), array("admin", "superadmin")))){
			$api->showError(403, M('UNAUTHORIZED_ACTION'));
		}
		
		$user_filter['email'] = $params['email'];
		
		$user = $api->getObjectsFromRouteName("users", $user_filter);

		$user[0]['password'] = md5($params['password']);

		$result = $api->updateObjectFromRoute("users", $user[0]['id'], $user[0]);

		if(!empty($result)){ 
		
			foreach ($result as &$r) {
				$r['password'] = M('PASSWORD_MASK');
			}
			
			if(function_exists('on_set_password')){
				on_set_password($result[0]['email'], $params['password']);
			}

	
			$api->showResult($result);
		} else {
			$api->showError(403);
		}
		
	} else{
		$api->showError(403, M('USERNAME_EMAIL_INVALID'));
	}
	
};

$setPasswordCommand = new RouteCommand("POST", "users", "set-password", $setPasswordFunction, array("email", "password", "admin_email", "admin_password"), "Method to set user password by admin");


$changePasswordFunction = function($params = NULL) {
	global $resterController;
	$api = $resterController;
	
	$filter['email'] = $params['email'];
	$filter['password'] = md5($params['password']);
	
	$result = $api->getObjectsFromRouteName("users", $filter);

	if(count($result) > 0){

		$result[0]['password'] = md5($params['new_password']);
		$result = $api->updateObjectFromRoute("users", $result[0]['id'], $result[0]);

		if(!empty($result)){ 
		
			foreach ($result as &$r) {
				$r['password'] = M('PASSWORD_MASK');
			}
			
			if(function_exists('on_change_password')){
				on_change_password($result[0]['email'], $params['new_password']);
			}
			
	
			$api->showResult($result);
		} else {
			$api->showError(403);
		}
		
	} else{
		$api->showError(403, M('USERNAME_EMAIL_INVALID'));
	}
	
};

$changePasswordCommand = new RouteCommand("POST", "users", "change-password", $changePasswordFunction, array("email", "password", "new_password"), "Method to change password");

$forgotPasswordFunction = function($params = NULL) {
	global $resterController;
	$api = $resterController;
	
	$filter['email'] = $params['email'];

	$result = $api->getObjectsFromRouteName("users", $filter);

	if(count($result) > 0){

		if(!$result[0]['is_active']){
			$api->showError(405, M('USER_INACTIVE'));
		}
		
		$new_password = "pRESTige";

		if(function_exists('on_forgot_password')){
			$new_password = $new_password = substr(uuid(), 0, 8);
		}

		$result[0]['password'] = md5($new_password);
		
		$result = $api->updateObjectFromRoute("users", $result[0]['id'], $result[0]);
		
		if(!empty($result)){ 
		
			foreach ($result as &$r) {
				$r['password'] = M('PASSWORD_MASK');
			}
			
			if(function_exists('on_forgot_password')){
				on_forgot_password($result[0]['email'], $new_password);
			}
			
	
			$api->showResult($result);
		} else {
			$api->showError(405, M('INVALID_OPERATION'));
		}
		

	} else{
		$api->showError(404, M("USER_404"));
	}
	
};

$forgotPasswordCommand = new RouteCommand("POST", "users", "forgot-password", $forgotPasswordFunction, array("email"), "Method to recover forgot password");


//Add the command to controller
//$resterController->addRouteCommand($loginCommand);
if(DEFAULT_LOGIN_API == true){
	$resterController->addRouteCommand($loginCommand);
	$resterController->addRouteCommand($setPasswordCommand);
	$resterController->addRouteCommand($changePasswordCommand);
	$resterController->addRouteCommand($forgotPasswordCommand);
	check_simple_auth(excluded_routes());
}


/**
* Sample organization activate command
*/
$activateFunction = function($params = NULL) {
	
	global $resterController;
	$api = $resterController;

	//Check if the organizations table exists
	try{
		$tableExists = $api->query('select 1 from organizations');
	}
	catch(Exception $e){
		$api->showError(503, "Can't find table named 'organizations'. Please check the documentation for more info.");
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
			is_active (integer): is_active field integer,
			secret (string): secret field string
		}
		where email and username should be marked as UNIQUE index and id as PRIMARY index.
		
		organizations {
			id (integer): id field integer,
			name (string): name field string,
			email (string): email field string,
			license (string): license field string,
			validity (datetime): validity field datetime,
			is_active (integer): is_active field integer,
			org_secret (string): org_secret field string,
			secret (string, optional): secret field string
		}
		
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
		);

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

	*/
	$result = $api->getObjectsFromRouteName("organizations", $filter);
	
	
	if($result == null){
		$api->showError(404, M('ORGANIZATION_404'));
	}
	else{
		$update_id = $result[0]['id'];
		$license = empty($params['license']) ? $result[0]['license'] : $params['license'];
		$validity = empty($params['validity']) ? $result[0]['validity'] : $params['validity'];
		$update_query = "update organizations set is_active = '1', license = '$license', validity = '$validity' where id = '$update_id'";
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
			$new_password = "pRESTige";
			if(function_exists('on_organization_activated')){
				$new_password = substr(uuid(), 0, 8);	
			}
			$new_password_md5 = md5($new_password); // md5 of 'admin' is 21232f297a57a5a743894a0e4a801fc3
			$activation_query = "INSERT INTO `users` (`email`, `username`, `password`, `token`, `lease`, `role`, `secret`, `is_active`) VALUES ('$email',	'$email', '$new_password_md5',	'1',	'0000-00-00 00:00:00',	'admin', '$org_secret', 1)";
			$user_id = $api->query($activation_query);
		}
		
		$resultFilter = array("id" => $user_id);
		$result = $api->getObjectsFromRouteName("users", $resultFilter);
		foreach ($result as &$r) {
			$r['password'] = M('PASSWORD_MASK');
		}
		
		$organization = $api->getObjectsFromRouteName("organizations", $filter);
		
		try{
			if(function_exists('on_organization_activated')){
				$user = $result[0];
				
				if(!empty($new_password)) {
					$user['password'] = $new_password;
				}
				
				on_organization_activated($organization[0], $user);
			}
		} catch (Exception $ex){
			
		}

		$api->showResult($result);
	}

};

$activateCommand = new RouteCommand("POST", "organizations", "activate", $activateFunction, array("org_secret"), "Method to activate an organization.");


/**
* Sample organization register command
*/
$registerOrganizationFunction = function($params = NULL) {
	
	global $resterController;
	$api = $resterController;

	//Check if the organizations table exists
	try{
		$tableExists = $api->query('select 1 from organizations');
	}
	catch(Exception $e){
		$api->showError(503, M('ORGANIZATIONS_TABLE_404'));
	}		
	
	$organization = $params["organization"];
	$email = $params["email"];

	$filter['email'] = $email;
	$result = $api->getObjectsFromRouteName("organizations", $filter);
	
	if(!empty($result)){
		$api->showError(422, M('DUPLICATE_ORG_EMAIL'));
	}
	else{
		$org_secret = uuid();
		$query = "insert into organizations (`name`, `email`, `license`, `validity`, `is_active`, `org_secret`, `secret`) values ('$organization', '$email', 'basic', '0000-00-00 00:00:00', 0, '$org_secret', '206b2dbe-ecc9-490b-b81b-83767288bc5e')";
		$updated = $api->query($query);


		// $select_query = "select `id`, `name`, `email`, `license`, `validity`, `is_active`, `org_secret` from organizations where secret = '$org_secret' and email = '$email' and name ='$organization'";
		// $result = $api->query($select_query); 

		// foreach ($result as &$r) {
		// 	$r['password'] = 'Not visible for security reasons';
		// }
		$filter_id['id'] = $updated;
		$organization = $api->getObjectsFromRouteName("organizations", $filter_id);
		
		try{
			if(function_exists('on_organization_registered')){
				on_organization_registered($organization[0]);
			}
		} catch (Exception $ex){
			
		}

		$api->showResult($organization);
	}

};

/**
* Sample user register command
*/
$registerUserFunction = function($params = NULL) {
	
	global $resterController;
	$api = $resterController;

	//Check if the users table exists
	try{
		$tableExists = $api->query('select 1 from users');
	}
	catch(Exception $e){
		$api->showError(503, M('USERS_TABLE_404'));
	}		
	
	$username = $params["username"];
	$email = $params["email"];
	$username = $params["email"];
	$orig_password = $params["password"];
	$password = md5($orig_password);

	$filter['email'] = $email;
	$result = $api->getObjectsFromRouteName("users", $filter);
	
	if(!empty($result)){
		$api->showError(422, M('DUPLICATE_USER_EMAIL'));
	}
	else{
		$token = uuid();
		$query = "insert into users (`username`, `email`, `password`, `token`, `lease`, `is_active`) values ('$username', '$email', '$password', '$token', '0000-00-00 00:00:00', 1)";
		$updated = $api->query($query);


		// $select_query = "select `id`, `name`, `email`, `license`, `validity`, `is_active`, `org_secret` from organizations where secret = '$org_secret' and email = '$email' and name ='$organization'";
		// $result = $api->query($select_query); 

		$filter_id['id'] = $updated;
		$result = $api->getObjectsFromRouteName("users", $filter_id);

		foreach ($result as &$r) {
			$r['password'] = M('PASSWORD_MASK');
		}
		
		try{
			if(function_exists('on_user_registered')){
				$user = $result[0];
				$user['password'] = $orig_password;
				on_user_registered($user);
			}
		} catch (Exception $ex){
			
		}

		$api->showResult($result);
	}

};


$registerOrganizationCommand = new RouteCommand("POST", "organizations", "register", $registerOrganizationFunction, array("organization", "email"), "Method to register an organization.");
$registerUserCommand = new RouteCommand("POST", "users", "register", $registerUserFunction, array("email", "password"), "Method to register a user.");

if(DEFAULT_SAAS_MODE == true){
	$resterController->addRouteCommand($activateCommand);
	check_simple_saas(array_merge(array("GET "), excluded_routes()));
	if(ENABLE_OPEN_REGISTRATIONS == true){
		$resterController->addRouteCommand($registerOrganizationCommand);
	}
}
else{
	if(ENABLE_OPEN_REGISTRATIONS == true){
		$resterController->addRouteCommand($registerUserCommand);
	}
}



function enable_simple_auth($exclude, $enable_open_registrations=false){
	if(!DEFAULT_LOGIN_API){
		global $resterController, $loginCommand, $setPasswordCommand, $changePasswordCommand, $forgotPasswordCommand;
		$resterController->addRouteCommand($loginCommand);
		$resterController->addRouteCommand($setPasswordCommand);
		$resterController->addRouteCommand($changePasswordCommand);
		$resterController->addRouteCommand($forgotPasswordCommand);
		if(!ENABLE_OPEN_REGISTRATIONS){
			if($enable_open_registrations){
				global $registerUserCommand;
				$resterController->addRouteCommand($registerUserCommand);
			}
		}
		check_simple_auth(array_merge(excluded_routes(), $exclude));
	}
}

function enable_simple_saas($exclude, $check_request_authenticity  = false, $enable_open_registrations = false){
	if(!DEFAULT_SAAS_MODE){
		global $resterController, $activateCommand;
		$resterController->addRouteCommand($activateCommand);
		if(!ENABLE_OPEN_REGISTRATIONS){
			if($enable_open_registrations){
				global $registerOrganizationCommand;
				$resterController->addRouteCommand($registerOrganizationCommand);
			}
		}
		check_simple_saas(array_merge(array("GET "),excluded_routes(), $exclude), $check_request_authenticity);
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


function load_views(){
	global $prestige;
	try{
		$views=$prestige->query("show full tables where Table_Type = 'VIEW'");
		
		$vProperty = "Tables_in_";
		$current_db_name_result = $prestige->query("SELECT DATABASE() FROM DUAL;");
		$current_db_name = "";
		if(!empty($current_db_name_result) && count($current_db_name_result) > 0){
			$current_db_name = $current_db_name_result[0]['DATABASE()'];
		}
		$vProperty = $vProperty . $current_db_name;
		
	}
	catch(Exception$ex){
	}
	for($i=0;
	$i<count($views);
	$i++){
		$v=$views[$i];
		$vName=$v[$vProperty];
		$vFinal=array("secret");
		$method="GET";
		$route="views";
		$prestige->addRouteCommand(new RouteCommand($method,$route,$vName,function ($params=null){
			global $prestige;
			$vName=$prestige->getCurrentPath()[0];
			$vList="";
			$query="select * from $vName";
			if(isset($params['secret']))$query.=" where secret='".$params['secret']."'";
			try{
				$value=$prestige->query($query);
			}
			catch(Exception$ex){
				if($ex->errorInfo[0]=="42S22"){
					$query="select * from $vName";
					try{
						$value=$prestige->query($query);
					}
					catch(Exception $exi){
						$prestige->showError(500,$exi);
					}
				} else {
					$prestige->showError(500,$ex);
				}
			}
			$prestige->showResult($value);
		}
		,$vFinal,"Get data from $vName"));
	}
}

load_views();



function load_stored_procedures(){
	global $prestige;
	
	try{
		$q = "select name, param_list from mysql.proc where db = '" . DBNAME . "'";
		$procedures = $prestige->query($q);
	} catch (Exception $ex){
		try{
			$procedureInfos = $prestige->query("select SPECIFIC_NAME as 'name' from information_schema.routines where ROUTINE_TYPE = 'PROCEDURE'");
			foreach($procedureInfos as $p){
				$pi = array();
				$pi["name"] = $proc_name = $p["name"];
				$query = "select concat(PARAMETER_MODE, ' `', PARAMETER_NAME, '` ',DATA_TYPE, '(', CHARACTER_MAXIMUM_LENGTH, ')') as parameter from information_schema.parameters where SPECIFIC_NAME = '$proc_name' and ROUTINE_TYPE = 'PROCEDURE'";
				$proc_params = $prestige->query($query);
				$proc_params_list = array();
				foreach($proc_params as $pp){
					$proc_params_list[] = $pp["parameter"];
				}
				$pi["param_list"] = $proc_params_imploded = implode(",", $proc_params_list);
				$procedures[] = $pi;
			}
		} catch(Exception $ex){
		}
	}
	for ($i = 0; $i < count($procedures); $i++) {
		$p = $procedures[$i];
		$pName = $p['name'];
		$pListSupplied = $p['param_list'];
		$pFinal = array();
		if(!empty($pListSupplied)){
			$pListSuppliedArray = explode(",", $pListSupplied);
			for ($pi = 0; $pi < count($pListSuppliedArray); $pi++) {
				 $pSupplied = $pListSuppliedArray[$pi];
				 $pSuppliedArray = explode("`", $pSupplied);
				 if(count($pSuppliedArray) > 1){
				 	$pFinal[$pSuppliedArray[1]] = true;
				 }
			}
		}
		
		$method = "POST";
		$route = "procedures";
		
		$pFinal['secret'] = false;		
		
		$prestige->addRouteCommand(new RouteCommand($method, $route, $pName, function($params=null){
			global $prestige;
			
			$pName = $prestige->getCurrentPath()[0];
			$pList = "";
			
			
			if(!empty($params)){
				$props = array_keys($params);
				$vals = array();
				for ($j = 0; $j < count($props); $j++) {
					 $k = $props[$j];
					 if(!in_array(strtolower($k),array("api-key", "api_key"))){
					 	$vals[] = "'" . $params[$k] . "'";
					 }
				}
				if(!empty($props)){
					$pList = implode(",",$vals);
				}
			}
			
			$query = "call $pName($pList)";
			
			try{
				$value = $prestige->query($query);
			} catch (Exception $ex){
				$prestige->showError(500, $ex);
			}

			$prestige->showResult($value);
		}, $pFinal, "Call $pName"));
			

	}
}
load_stored_procedures();

//Custom API
//$helloWorldApi = new RouteCommand("GET", "hello", "world", function($params=null){
//	$api = new ResterController();
//	$value = $api->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
//	$api->showResult($value);
//}, array(), "Hello World Api");
//$resterController->addRouteCommand($helloWorldApi);

//Sample Custom API. 
// $prestige->addRouteCommand(new RouteCommand("GET", "hello", "world", function($params=null){
// 	global $prestige;
// 	$value = $prestige->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
// 	$prestige->showResult($value);
// }, array(), "Hello World Api"));



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
