<?php
require_once('lib.php');
require_once(__DIR__.'/../vendor/phar/defuse-crypto-2.1.0.phar');

//GET PASSWORD FROM Codiad Settings
$users_file = '../../ide/data/users.php';
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
$SUPPLIED_PASSWORD = $_POST["password"];
$SUPPLIED_PASSWORD_ENC = sha1(md5($SUPPLIED_PASSWORD));
$SUPPLIED_USERNAME = $_POST["username"];

if($SUPPLIED_PASSWORD_ENC == $PASSWORD && $SUPPLIED_USERNAME == $USERNAME){
  //include("configure.php");
  //exit();
} else {
  header('Location: .?auth=false');
  exit();
}


//Initialize Configuration
$configPath = __DIR__.'/../prestige.config';
$keyPath = __DIR__.'/../prestige.key';

if(file_exists($configPath)){
    if(file_exists($keyPath)){
        $key = file_get_contents($keyPath);
        $keyObj = Defuse\Crypto\Key::loadFromAsciiSafeString($key);
    }
    $configContents = file_get_contents($configPath);
    $configDecrypted = Defuse\Crypto\Crypto::decrypt($configContents, $keyObj);
    $configDecoded = $encode_decode_simple->decode($configDecrypted);
    $configJson = ($configDecoded);
    $config = json_decode($configJson);
}

?>
<head>
  <title>pRESTige Setup</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  		<!--Append / at the end of URL to load everything properly -->
		<script>
		window.onload = function(){
			// var location = "" + window.location;
			// if(location.charAt(location.length-1) !== '/'){
			//   if(!(location.indexOf('?') > -1)){
  	// 			var newLocation = location + "/";
  	// 			window.location = newLocation;
			//   }
			// }
			// if(("" + window.location).indexOf('configure/') > -1){
			//   	var configForm = document.getElementById('configForm');
   //     			configForm.action = configForm.action.replace("configure/","");
			// }
			
			// var urlParams = new URLSearchParams(location.search);
			// var auth = urlParams.get('auth');
			// if(location.search("auth=false") > -1){
			//   $('#error').text("Invalid Credentials!");
			// }

		}
		
		</script>
		
		<style type="text/css">
		    .main-container {
                margin: auto;
                width: 40%;
                margin-top: 100px;
            }
            .center-text{
                text-align: center;
            }
		</style>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  
	<script type="text/javascript">
		$(function(){
			$('#host').focus();
			
			 var cfg = '<?php echo json_encode($config) ?>';
			 if(cfg){
			 	try{
			 		console.log(cfg);
				 	var config = JSON.parse(cfg);
				 	if(config){
				 		if(config.host) $('#host').val(config.host);
				 		if(config.user) $('#user').val(config.user);
				 		if(config.password) $('#pwd').val(config.password);
				 		if(config.database) $('#database').val(config.database);
				 		if(config.legacy_mode) $('#legacy_mode').attr("checked", "checked");
				 	}
			 	} catch (e){
			 		
			 	}
			 }
			
		});
	</script>  
</head>
<body>
<div class="panel panel-primary main-container">
  <div class="panel-heading center-text">pRESTige Configuration - Provide MySQL Connection</div>
  <div class="panel-body">
		<form id='configForm' action="generate-config.php" method="post">
		    <div class="form-group">
		      <label for="host">Host:</label>
		      <input type="text" class="form-control" id="host" placeholder="Enter hostname" name="host">
		    </div>
		    <div class="form-group">
		      <label for="user">User:</label>
		      <input type="text" class="form-control" id="user" placeholder="Enter username" name="user" required>
		    </div>
		    <div class="form-group">
		      <label for="pwd">Password:</label>
		      <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="password">
		    </div>
		    <div class="form-group">
		      <label for="database">Database:</label>
		      <input type="text" class="form-control" id="database" placeholder="Enter database name" name="database" required>
		    </div>
		    <div class="form-group">
		      <label for="legacy_mode">Legacy mode:</label>
		      <input type="checkbox" class="form-control" id="legacy_mode" placeholder="Legacy mode" name="legacy_mode">
		    </div>
		    <button type="submit" class="btn btn-default">Submit</button>
		  </form>
  </div>
</div>
</body>
</html>



  


