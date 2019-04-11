<?php

//GET PASSWORD FROM Codiad Settings

define("REL_PATH_THIS", ".."); //prod
//define("REL_PATH_THIS", "../../.."); //dev


function authenticate($relative_path = "./"){

	$users_file = $relative_path . REL_PATH_THIS . '/ide/data/users.php';
	$reference_obj = null;


	function fill_reference_obj($users_file){
	  global $reference_obj;
		$data = file_get_contents($users_file);
		$startpos = strpos($data, "[");
		$endpos = strrpos($data, "]");
		$len = $endpos - $startpos + 1;
		$realdata = substr($data, $startpos, $len);
		$obj = (array) json_decode($realdata, true);
		$reference_obj = $obj[0];
	}
	
	fill_reference_obj($users_file);
	
	function get_password(){
	  global $reference_obj;
		return $reference_obj['password'];	
	}
	
	function get_username(){
	  global $reference_obj;
		return $reference_obj['username'];	
	}
	
	
	$PASSWORD = get_password();
	$USERNAME = get_username();
	$SUPPLIED_PASSWORD = !empty($_POST["password"]) ? $_POST["password"] : $_REQUEST["password"];
	$SUPPLIED_PASSWORD_ENC = sha1(md5($SUPPLIED_PASSWORD));
	$SUPPLIED_USERNAME = !empty($_POST["username"]) ? $_POST["username"] : $_REQUEST["username"];
	
	if($SUPPLIED_PASSWORD_ENC == $PASSWORD && $SUPPLIED_USERNAME == $USERNAME){
		return array("username" => $SUPPLIED_USERNAME, "password" => $SUPPLIED_PASSWORD);
	} else {
		return false;
	  //header('Location: .?auth=false');
	  //exit();
	}	
}



?>