<?php

//If getallheaders doesn't exist
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

//Custom Functions
function url_get($url, $params = null, $headers = null){
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	if($params != null) curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($headers != null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	return $server_output;
}


function url_post($url, $payload = null, $headers = null){

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	if($payload != null) curl_setopt($ch, CURLOPT_POSTFIELDS,$payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($headers != null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$server_output = curl_exec ($ch);

	curl_close ($ch);

	return $server_output;

}

function send_email_sparkpost($from, $to, $subject, $body, $api_key){
	$url = "https://api.sparkpost.com/api/v1/transmissions";
	$recipients = array();
	for ($i=0; $i < count($to); $i++) { 
		array_push($recipients, array("address" => $to[$i]));
	}
	$payload =json_encode(array("content" => array("from"=>$from,"subject"=>$subject, "html"=>$body),"recipients"=>$recipients));
	$headers = [
		//'Content-Type: application/json',
		'Authorization: ' . $api_key
	];

	return url_post($url, $payload, $headers);
}


//Usage
//$from = "youremail@yourdomain.com";
//$to = ["recepientsemail@theirdomain.com"];
//$api_key = "YOUR_SPARKPOST_API_KEY";
//$subject = "SUBJECT HERE";
//$body = "TEXT HERE";
//echo send_email_sparkpost($from, $to, $subject, $body, $api_key);

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


function string_intersect($string_1, $string_2)
{
    $string_1_length = strlen($string_1);
    $string_2_length = strlen($string_2);
    $return          = "";

    if ($string_1_length === 0 || $string_2_length === 0) {
        // No similarities
        return $return;
    }

    $longest_common_subsequence = array();

    // Initialize the CSL array to assume there are no similarities
    for ($i = 0; $i < $string_1_length; $i++) {
        $longest_common_subsequence[$i] = array();
        for ($j = 0; $j < $string_2_length; $j++) {
            $longest_common_subsequence[$i][$j] = 0;
        }
    }

    $largest_size = 0;

    for ($i = 0; $i < $string_1_length; $i++) {
        for ($j = 0; $j < $string_2_length; $j++) {
            // Check every combination of characters
            if ($string_1[$i] === $string_2[$j]) {
                // These are the same in both strings
                if ($i === 0 || $j === 0) {
                    // It's the first character, so it's clearly only 1 character long
                    $longest_common_subsequence[$i][$j] = 1;
                } else {
                    // It's one character longer than the string from the previous character
                    $longest_common_subsequence[$i][$j] = $longest_common_subsequence[$i - 1][$j - 1] + 1;
                }

                if ($longest_common_subsequence[$i][$j] > $largest_size) {
                    // Remember this as the largest
                    $largest_size = $longest_common_subsequence[$i][$j];
                    // Wipe any previous results
                    $return       = "";
                    // And then fall through to remember this new value
                }

                if ($longest_common_subsequence[$i][$j] === $largest_size) {
                    // Remember the largest string(s)
                    $return = substr($string_1, $i - $largest_size + 1, $largest_size);
                }
            }
            // Else, $CSL should be set to 0, which it was already initialized to
        }
    }

    // Return the list of matches
    return $return;
}


function array_search_where($array, $column_name, $where, $single=true, $return_only_key = false) {
   $results = array();
   foreach ($array as $key => $val) {
       if ($val[$column_name] === $where) {
	       
       		if($return_only_key)
		{
			$ret = $key;
		} else {
			$ret = $val;
		}
       		if($single) {
			return $ret;
		} else {
			array_push($results, $ret);
		}
       }
   }
   return $results;
}



function request_is_mobile(){
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	$is_mobile = (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
	return $is_mobile;
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

//$exclude = array("GET hello/world", "POST hello/world");
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
		$auth_header = $headers['api_key'];
		if(!$auth_header) $auth_header = $_REQUEST['api_key'];
		if(!$auth_header){
			foreach($headers as $key=>$val){
				if($key == 'api_key') $auth_header = $val;
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

function enable_simple_auth($exclude){
	if(!DEFAULT_LOGIN_API){
		global $resterController, $loginCommand;
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
