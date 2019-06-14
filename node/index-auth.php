<?php

//GET PASSWORD FROM Codiad Settings

define("REL_PATH_THIS", ".."); //prod
//define("REL_PATH_THIS", "../../.."); //dev


function authenticate($relative_path = "./"){

	$users_file = $relative_path . REL_PATH_THIS . '/ide/data/users.php';

    function read_passwords($users_file){
        $auth_pwds = array();
    	$data = file_get_contents($users_file);
    	$startpos = strpos($data, "[");
    	$endpos = strrpos($data, "]");
    	$len = $endpos - $startpos + 1;
    	$realdata = substr($data, $startpos, $len);
    	$obj = (array) json_decode($realdata, true);
    	for($i = 0; $i < count($obj) ; $i++){
    	    $user = $obj[$i]['username'];   
    	    $password = $obj[$i]['password'];
    	    $auth_pwds[$user] = $password;
    	}
    	return $auth_pwds;	
    }
    $reference_obj = read_passwords($users_file);

	$ref_users = array_keys($reference_obj);
	
	$SUPPLIED_PASSWORD = !empty($_POST["password"]) ? $_POST["password"] : $_REQUEST["password"];
	$SUPPLIED_PASSWORD_ENC = sha1(md5($SUPPLIED_PASSWORD));
	$SUPPLIED_USERNAME = !empty($_POST["username"]) ? $_POST["username"] : $_REQUEST["username"];
	
	if(in_array($SUPPLIED_USERNAME, $ref_users) && $SUPPLIED_PASSWORD_ENC == $reference_obj[$SUPPLIED_USERNAME]){
		return array("username" => $SUPPLIED_USERNAME, "password" => $SUPPLIED_PASSWORD);
	} else {
		return false;
	  //header('Location: .?auth=false');
	  //exit();
	}
	
}


?>
