<?php

//The api version, must have a php file on versions folder to include
define('API_VERSION', "1.0.0");

//Database credentials
define('DBHOST', 'localhost');
define('DBNAME', 'mydb');
define('DBUSER', 'dbuser');
define('DBPASSWORD', 'dbpassword');

//If enabled, verbose log written on error.log
//define('LOG_VERBOSE', true);

//The path where the uploads are saved. Must be writtable by the webserver
define('FILE_UPLOAD_PATH', 'uploads');

//Enables API Cache. For now only APC is implemented
define('CACHE_ENABLED', true);

//Enable OAuth 1.0 Authentication
define('ENABLE_OAUTH', true);

?>