<?php
require_once('lib.php');
require_once(__DIR__.'/../vendor/phar/defuse-crypto-2.1.0.phar');

//GET PASSWORD FROM Codiad Settings

	$users_file = __DIR__ . '/../..' . '/ide/data/users.php';

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
		
	} else {
		//return false;
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
		
  <!-- <link rel="shortcut icon" href="../../ide/workspace/web/examples/angularjs/images/favicon.ico" />     -->
  <link rel="shortcut icon" href="./favicon.ico" />    
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tour/0.11.0/js/bootstrap-tour-standalone.js" 
            integrity="sha256-1oQqWoYRKhIK87UIfRmlDObEHrO1rj2E3lv7cCSV4y0=" 
            crossorigin="anonymous"
            ></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tour/0.11.0/css/bootstrap-tour-standalone.css" 
            integrity="sha256-OdRIC3/VFxsszf6c8mx8eFrhtR3Ct8sxVXxZwonSUdg=" 
            crossorigin="anonymous"
            />
		
		
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

			.floating-button{
            width:80px;
            height: 80px;
            font-size: x-small;
            font-weight: bold;
            background: #337ab7;
            color:#FFF;
            padding:5px;
            margin: 50px;
            text-align:center;            
            right:0;
            bottom:0;
            position:fixed; 
            float:right;
            border-radius: 50%;
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
				 		
				 		$.get('seed.sql.txt', null, function(r){
				 			$('#sql_query').val(r);
				 		});

						 var current_user = '<?php echo ($SUPPLIED_USERNAME) ?>';
						 if(current_user == "user"){
						 	$('input').attr('disabled', 'disabled');
						 	$('button').attr('disabled', 'disabled');
						 	$('textarea').attr('disabled', 'disabled');
						 	$('#tokenuser').val('user@example.com');
						 	$('#tokenpassword').val('user');
						 	if(config.auth_mode){
						 		$('.user-mode > div > input').attr('disabled',false);
							 	$('.user-mode > div > button').attr('disabled',false);
						 	}
						 }						
				 		
				 	}
			 	} catch (e){
			 		
			 	}
			 }
			 
			 var tourKey = window.location.href.replace("/configure/secure.php", "").replace("https://", "").replace("http://", "").replace(/\//g,"_").replace(/\./g,"_").replace(/:/g, "_");

			 if(localStorage.getItem(tourKey + '_end') == "yes"){
                    $('#relaunch-tour').show();
                }

			tour_api_config = new Tour({
				name: tourKey,
                steps: [
                {
                    element: "#mysql-config",
                    title: "Step #2.1: Provide connection to MySQL Database",
                    content: "The most important step for pRESTige! <b>Without this step, there is no magic!</b> <br/><br/>Make sure you have access to a MySQL server/database and you have credentials for accessing the DB. Provide these credentials and the name of your database here."
                },
                {
                    element: "#api-config",
                    title: "Step #2.2: API Configuration",
                    content: "pRESTige provides a lot of default APIs, such as the APIs for managing users, organizations. APIs for authentication (login), APIs for uploading file, etc. You can control these options from here."
                },
                {
                    element: "#seed-config",
                    title: "Before Step #2.2: API Configuration Prerequisites",
                    content: "If you are enabling any modes in Step #2.2, do not forget to execute the following script"
                },
                {
                    element: "#token-config",
                    title: "After Step #2.2: Quick way to generate tokens/api_keys",
                    content: "If you have enabled Auth/SaaS mode, you won't be able to explore APIs openly. You have to provide the api_key while even browsing the API documents, because with Auth mode, the APIs are secure. You can use the token generated below to use as the api_key."
                }

                ],
                onEnd: function(){
                    $('#relaunch-tour').show();
                }});

                // Initialize the tour
                tour_api_config.init();

                // Start the tour
                tour_api_config.start();

                relaunchTour = function(){
                    $('#relaunch-tour').hide();
                    tour_api_config.restart();
                };
			 

		});
			 function executeSQL(){
			 		var cfg = '<?php echo json_encode($config) ?>';
				 	var config = JSON.parse(cfg);
				 	if(config){
				 		$.post('execute.php', config, function(r){
				 				//$('#executionStatus').val(r.status);
				 				$('#executionStatus').html("The script has been executed successfully! You will be able to use Auth Mode, SaaS mode and File APIs");
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
			<div class="form-group col-md-12" id="mysql-config">
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
		    	<button type="submit" class="btn btn-primary">Submit</button>	
		    </div>
			<hr/>		    
			<div class="form-group col-md-12" id="api-config">
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
		    <div class="form-group col-md-12"  id="seed-config">
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
		    <div class="user-mode">
		    	<div class="form-group col-md-12"  id="token-config">
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
	<div>
  		<button type="button" id="relaunch-tour" style="display: none; z-index: 10000;" class="floating-button" onclick="relaunchTour()">RELAUNCH TOUR</button>
	</div>
</div>
</body>
</html>



  


