

<head>
  <title>Geekypedia RESTer Setup</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  		<!--Append / at the end of URL to load everything properly -->
		<script>
		window.onload = function(){
			var location = "" + window.location;
			if(location.charAt(location.length-1) !== '/'){
				var newLocation = location + "/";
				window.location = newLocation;
			}
		}
		</script>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
  <h2>MySQL Database Setup</h2>
  <form action="generate-config.php" method="post">
    <div class="form-group">
      <label for="host">Host:</label>
      <input type="text" class="form-control" id="host" placeholder="Enter hostname" name="host">
    </div>
    <div class="form-group">
      <label for="user">User:</label>
      <input type="text" class="form-control" id="user" placeholder="Enter username" name="user">
    </div>
    <div class="form-group">
      <label for="pwd">Password:</label>
      <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="password">
    </div>
    <div class="form-group">
      <label for="database">Database:</label>
      <input type="text" class="form-control" id="database" placeholder="Enter database name" name="database">
    </div>
    <button type="submit" class="btn btn-default">Submit</button>
  </form>
</div>

</body>
</html>


