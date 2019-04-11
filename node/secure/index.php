<?php
//require_once('lib.php');
// require_once(__DIR__.'/../api/vendor/phar/defuse-crypto-2.1.0.phar');

include('../index-auth.php');
$auth = authenticate('../');
if(!$auth){
  header('Location: ..?auth=false');
  exit();
}


//Initialize Configuration
// $configPath = __DIR__.'/../prestige.config';
// $keyPath = __DIR__.'/../prestige.key';

// if(file_exists($configPath)){
//     if(file_exists($keyPath)){
//         $key = file_get_contents($keyPath);
//         $keyObj = Defuse\Crypto\Key::loadFromAsciiSafeString($key);
//     }
//     $configContents = file_get_contents($configPath);
//     $configDecrypted = Defuse\Crypto\Crypto::decrypt($configContents, $keyObj);
//     $configDecoded = $encode_decode_simple->decode($configDecrypted);
//     $configJson = ($configDecoded);
//     $config = json_decode($configJson);
// }

?>
<head>
  <title>Node.JS Administration</title>
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
            
            .round-btn-med{
            	width: 200px;
            }
            
            .round-btn{
            	border-radius: 0%
            }
            
            .row-margin{
            	margin-left: 0px;
            	margin-right: 0px;
            }
            
            .dot {
			  height: 25px;
			  width: 25px;
			  background-color: #bbb; 
			  border-radius: 50%;
			  display: inline-block;
			}
			
			.bg-red{
				background-color: red; 
			}

			.bg-green{
				background-color: green; 
			}
			
			


.spinner {
  border: 16px solid #f3f3f3; /* Light grey */
  border-top: 16px solid #3498db; /* Blue */
  border-radius: 50%;
  width: 25px;
  height: 25px;
  animation: spin 2s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
			
            
		</style>

  
	<script type="text/javascript">
		$(function(){
			$('#path').focus();
			 
			 $('.round-btn').each(function(index){
			 	var that = $(this);
			 	//that.css('height', that.css('width'));
			 });

		});
		
		function act(command, cb){
			var username = "<?php echo $auth['username'] ?>";
			var password = "<?php echo $auth['password'] ?>";
			var payload = {
				username: username,
				password: password
			}
			payload[command] = '.';
			if(command == 'start') payload[command] = $('#prefix').val();
			if(command == 'npm') {
				payload[command] = $('#npm').val();
				payload['prefix'] = $('#prefix').val();
			}
			if(command == 'npmstart') {
				payload['npm'] = 'start';
				payload['prefix'] = $('#prefix').val();
			}
			
			$('.spinner').show();
			$.post('../api/', payload, function(r){
				if(command != 'status') refreshStatus();
				$('.spinner').hide();
				if(cb) cb(r);
			}).fail(function(e){
				if(command != 'status') refreshStatus();
				$('.spinner').hide();
				var err = e.responseJSON;
				if(cb) cb(err);
			});
		}
		
		function refreshStatus(){
			act('status', function(r){
				console.log(r);
				if(r.installed){
					$('#installationStatus').removeClass("bg-red");
					$('#installationStatus').addClass("bg-green");
				} else {
					$('#installationStatus').removeClass("bg-green");
					$('#installationStatus').addClass("bg-red");
				}
				if(r.running){
					$('#processStatus').removeClass("bg-red");
					$('#processStatus').addClass("bg-green");
				} else {
					$('#processStatus').removeClass("bg-green");
					$('#processStatus').addClass("bg-red");
				}
			});
		}
		
		refreshStatus();
			
		
	</script>  
</head>
<body>
<div class="panel panel-primary main-container">
  <div class="panel-heading center-text">Node.JS Administration</div>
  <div class="panel-body">
  		
		<form id='configForm' action="generate-config.php" method="post">
			<div class="form-group col-md-12">
				<label class="head-label">CONFIGURATIONS</label>
			</div>
		    <div class="form-group col-md-6">
		      <label for="host">Host:</label>
		      <input type="text" class="form-control" id="host" placeholder="Enter hostname" name="host" value="localhost">
		    </div>
		    <div class="form-group col-md-3">
		      <label for="port">Port:</label>
		      <input type="text" class="form-control" id="port" placeholder="Enter port" name="port" required value="49999">
		    </div>
		    <div class="form-group col-md-3">
		      <label for="version">NodeJS Version:</label>
		      <input type="text" class="form-control" id="version" placeholder="Enter nodejs version" name="version" required value="v10.15.3">
		    </div>
		    <div class="form-group col-md-6">
		      <label for="prefix">Project Path:</label>
		      <input type="text" class="form-control" id="prefix" placeholder="Enter project path" name="prefix" required value="node/index.js">
		    </div>
		    <div class="form-group col-md-12">
		      <label for="npm">NPM Command:</label>
		      <input type="text" class="form-control" id="npm" placeholder="Enter npm command like install express" name="npm">
		    </div>
			<div class="form-group col-md-12">
				<label class="head-label">STATUS</label>
			</div>
		    <div class="form-group col-md-2">
		    	<label class="">Node.JS: </label>
		    </div>
		    <div class="form-group col-md-1">
		    	<span class="dot" id="installationStatus"></span>
		    </div>
		    <div class="form-group col-md-3">
		    	<label class="">Application Process: </label>
		    </div>
		    <div class="form-group col-md-1">
		    	<span class="dot" id="processStatus"></span>
		    </div>
		    <div class="form-group col-md-3">
		    </div>
		    <div class="form-group col-md-1">
		    	<div class="spinner"></div>
		    </div>
		    
			<div class="form-group col-md-12">
				<label class="head-label">ACTIONS</label>
			</div>
			<div class="row row-margin">
			    <div class="form-group col-md-3">
			    	<button type="button" class="btn btn-success round-btn-med round-btn" onclick="act('install')">INSTALL</button>	
			    </div>
			    <div class="form-group col-md-1">
			    </div>
			    <div class="form-group col-md-3">
			    	<button type="button" class="btn btn-danger round-btn-med round-btn" onclick="act('uninstall')">UNINSTALL</button>	
			    </div>
			    <div class="form-group col-md-1">
			    </div>
			    <div class="form-group col-md-3">
			    	<button type="button" class="btn btn-info round-btn-med round-btn" onclick="act('npm')">EXECUTE NPM COMMAND</button>	
			    </div>
			    <div class="form-group col-md-1">
			    </div>
			</div>
			<div class="row row-margin">
			    <div class="form-group col-md-3">
			    	<button type="button" class="btn btn-success round-btn-med round-btn" onclick="act('start')">START</button>	
			    </div>
			    <div class="form-group col-md-1">
			    </div>
			    <div class="form-group col-md-3">
			    	<button type="button" class="btn btn-danger round-btn-med round-btn" onclick="act('stop')">STOP</button>	
			    </div>
			    <div class="form-group col-md-1">
			    </div>
			    <div class="form-group col-md-3">
			    	<a href="../api" target="_blank" class="btn btn-info round-btn-med round-btn">LAUNCH</a>	
			    </div>
			    <!--<div class="form-group col-md-3">-->
			    <!--	<button type="button" class="btn btn-info round-btn-med round-btn" onclick="act('npmstart')">NPM START</button>	-->
			    <!--</div>-->
			    <div class="form-group col-md-1">
			    </div>
			</div>
		  </form>
		  
		 
		    
  </div>
</div>
</body>
</html>
