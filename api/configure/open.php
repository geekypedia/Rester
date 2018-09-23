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
			alert(location.search("auth1"));
			if(auth === false){
			  $('#error').text("Invalid Credentials!");
			}

		}
		</script>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2>pRESTige Configuration</h2>
  <form id='configForm' action="configure/secure.php" method="post">
    <div class="form-group">
      <label for="username">Username:</label>
      <input type="text" class="form-control" id="username" placeholder="Enter username" name="username">
    </div>
    <div class="form-group">
      <label for="pwd">Password:</label>
      <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="password">
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
    <p id="error" style="color:red; font-weight: small"></p>
  </form>
</div>

</body>
</html>


