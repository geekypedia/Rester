<head>
  <title>pRESTige Setup</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  		<!--Append / at the end of URL to load everything properly -->
		<script>
		window.onload = function(){
			var location = "" + window.location;
			if(location.charAt(location.length-1) !== '/'){
			  if(!(location.indexOf('?') > -1)){
  				var newLocation = location + "/";
  				window.location = newLocation;
			  }
			}
			if(("" + window.location).indexOf('configure/') > -1){
			  	var configForm = document.getElementById('configForm');
        			configForm.action = configForm.action.replace("configure/","");
			}
			
			var urlParams = new URLSearchParams(location.search);
			var auth = urlParams.get('auth');
			if(location.search("auth=false") > -1){
			  $('#error').text("Invalid Credentials!");
			}

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
			$('#username').focus();
		})
	</script>  
</head>
<body>
<div class="panel panel-primary main-container">
  <div class="panel-heading center-text">pRESTige Configuration - Authenticate yourself!</div>
  <div class="panel-body">
      <form id='configForm' action="configure/secure.php" method="post">
        <div class="form-group">
          <label for="username">Username:</label>
          <input type="text" class="form-control" id="username" placeholder="Enter username" name="username" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-default">Submit</button>
        </div>
        <div class="form-group">
            <p id="error" style="color:red; font-weight: small"></p>
        </div>
      </form>
  </div>
</div>
</body>
</html>


