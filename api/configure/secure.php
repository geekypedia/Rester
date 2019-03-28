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
		
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		
		
		<style type="text/css">
		    .main-container {
                margin: auto;
                width: 60%;
                margin-top: 10px;
            }
		    .main-sub-container {
                margin: auto;
                width: 100%;
                margin-top: 10px;
            }
            
            .center-text{
                text-align: center;
            }
            .left-text{
                text-align: left;
            }
            
            hr{
            	border: 1px solid #eee;
            }
            
            .head-label{
            	text-decoration: underline;
            }
            
		</style>

  
	<script type="text/javascript">
		$(function(){
			$('#host').focus();
			
			 var cfg = '<?php echo json_encode($config) ?>';
			 if(cfg){
			 	try{
				 	var config = JSON.parse(cfg);
				 	if(config){
				 		if(config.host) $('#host').val(config.host);
				 		if(config.user) $('#user').val(config.user);
				 		if(config.password) $('#pwd').val(config.password);
				 		if(config.database) $('#database').val(config.database);
				 		if(config.legacy_mode) $('#legacy_mode').attr("checked", "checked");
				 		if(config.file_mode) $('#file_mode').attr("checked", "checked");
				 		if(config.auth_mode) $('#auth_mode').attr("checked", "checked");
				 		if(config.saas_mode) $('#saas_mode').attr("checked", "checked");
				 		if(config.open_registrations) $('#open_registrations').attr("checked", "checked");
				 		
				 		if(config.excluded_routes) {
				 			var excluded_routes_raw = config.excluded_routes.join(", \n");
				 			$('#excluded_routes').val(excluded_routes_raw);
				 		} 
				 		
				 		$.get('seed.sql', null, function(r){
				 			$('#sql_query').val(r);
				 		});
				 		
				 	}
			 	} catch (e){
			 		
			 	}
			 }
			 
			 

		});
			 function executeSQL(){
			 		var cfg = '<?php echo json_encode($config) ?>';
				 	var config = JSON.parse(cfg);
				 	if(config){
				 		$.post('execute.php', config, function(r){
				 				//$('#executionStatus').val(r.status);
				 				$('#executionStatus').val("The script has been executed successfully! You will be able to use Auth Mode, SaaS mode and File APIs");
				 				$('#executionStatus').css("color", "green");
				 		}).fail(function(r){
				 			//$('#executionStatus').html(r.responseJSON.status);
				 			$('#executionStatus').html("There was an error executing the SQL. Please copy and paste it into the Database Administration Tool and execute it manually.");
				 			$('#executionStatus').css("color", "red");
				 		});
				 		
				 		
				 	}
			 	
			 }
			 
			 function getToken(){
			 	var payload = {
			 		email: $('#tokenuser').val(),
			 		password: $('#tokenpassword').val()
			 	}
			 	$.post('../users/login', payload, function(r){
			 		$('#token').val(r.token);
			 	}).fail(function(r){
			 		$('#token').val("Error generating token ...");
			 	});
			 }
		
	</script>  
</head>
<body>
<div class="panel panel-primary main-container">
  <div class="panel-heading center-text">pRESTige Configuration</div>
  <div class="panel-body">
  		
		<form id='configForm' action="generate-config.php" method="post">
			<div class="form-group col-md-12">
				<label class="head-label">MySQL CONFIGURATION</label>
			</div>
		    <div class="form-group col-md-6">
		      <label for="host">Host:</label>
		      <input type="text" class="form-control" id="host" placeholder="Enter hostname" name="host">
		    </div>
		    <div class="form-group col-md-6">
		      <label for="user">User:</label>
		      <input type="text" class="form-control" id="user" placeholder="Enter username" name="user" required>
		    </div>
		    <div class="form-group col-md-6">
		      <label for="pwd">Password:</label>
		      <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="password">
		    </div>
		    <div class="form-group col-md-6">
		      <label for="database">Database:</label>
		      <input type="text" class="form-control" id="database" placeholder="Enter database name" name="database" required>
		    </div>
		    
			<div class="form-group col-md-12">
				<label class="head-label">API CONFIGURATION</label>
			</div>
		    <div class="form-group col-md-6">
		      <label for="legacy_mode">Enable Legacy mode:</label>
		      <input type="checkbox" class="form-control" id="legacy_mode" placeholder="Legacy mode" name="legacy_mode">
		    </div>
		    <div class="form-group col-md-6">
		      <label for="file_mode">Default File API:</label>
		      <input type="checkbox" class="form-control" id="file_mode" placeholder="Default File API" name="file_mode">
		      <!--<button type="button" class="btn btn-success" id="file_mode_auto">Automatically generate required database changes</button>-->
		    </div>
		    <div class="form-group col-md-4">
		      <label for="auth_mode">Enable Authentication:</label>
		      <input type="checkbox" class="form-control" id="auth_mode" placeholder="Simple auth mode" name="auth_mode">
		      <!--<button type="button" class="btn btn-success" id="auth_mode_auto">Automatically generate required database changes</button>-->
		    </div>
		    <div class="form-group col-md-4">
		      <label for="legacy_mode">Enable SaaS Mode:</label>
		      <input type="checkbox" class="form-control" id="saas_mode" placeholder="Simple SaaS mode" name="saas_mode">
		      <!--<button type="button" class="btn btn-success" id="saas_mode_auto">Automatically generate required database changes</button>-->
		    </div>
		    <div class="form-group col-md-4">
		      <label for="open_registrations">Open Registrations:</label>
		      <input type="checkbox" class="form-control" id="open_registrations" placeholder="Open Registrations" name="open_registrations">
		    </div>
		    <div class="form-group col-md-12">
		      <label for="excluded_routes">APIs Excluded from Auth:</label>
		      <p>Comma separated list of APIs that you need to exclude from authentication. </p>
		      <textarea type="text" rows=10 class="form-control" id="excluded_routes" placeholder="Example: GET hello/world, POST hello/again" name="excluded_routes">
		      </textarea>
		    </div>
		    <div class="form-group col-md-12">
		    	<button type="submit" class="btn btn-primary">Submit</button>	
		    </div>
		    
		  </form>
		  
		  <hr/>
		    <div class="form-group col-md-12">
		      <label for="sql_query">Database changes needed to run Auth and SaaS mode, and  File APIs:</label>
		      <p>Make sure you run this script in your database.</p>
		      <textarea type="text" columns=80 rows=10 class="form-control" id="sql_query" placeholder="" name="sql_query" disabled=disabled>
		      </textarea>
		    </div>
		    <div class="form-group col-md-12">
			    <button type="button" class="btn btn-default" onclick="executeSQL()">Execute</button>
			    <div id="executionStatus" name="executionStatus" style="font-size: smaller; padding: 0px; margin: 5px;"></div>
		    </div>
		  <hr/>
		    <div>
		    	<div class="form-group col-md-12">
		    		<label>Get latest token needed to run APIs in Auth Mode:</label>
		    	</div>
		    	<div class="form-group col-md-6">
			      <label for="tokenuser">Application Username:</label>
			      <input type="text" class="form-control" id="tokenuser" value="admin@example.com" name="tokenuser" required>
		    	</div>
		    	<div class="form-group col-md-6">
			      <label for="tokenpassword">Appication Password:</label>
			      <input type="password" class="form-control" id="tokenpassword" value="admin" name="tokenpassword" required>
		    	</div>
		    	<div class="form-group col-md-6">
		    		<label for="token">Token:</label>
			      <textarea type="text" rows=1 class="form-control" id="token" placeholder="Your token will appear here ..." name="token" disabled=disabled>
			      </textarea>
		    	</div>
		    	<div class="form-group col-md-12">
		    		<button type="button" class="btn btn-default" onclick="getToken()">Get Token</button>	
		    	</div>
		    </div>
		    
  </div>
</div>
</body>
</html>



  


