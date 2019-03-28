<?php

require_once('configure/lib.php');

//The api version, must have a php file on versions folder to include
define('API_VERSION', "1.0.0");

//Initialize Configuration
$configPath = __DIR__.'/prestige.config';
$keyPath = __DIR__.'/prestige.key';


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
else{
    include('configure/index.php');
    exit();
}

//Database credentials
define('DBHOST', $config->host);
define('DBNAME', $config->database);
define('DBUSER', $config->user);
define('DBPASSWORD', $config->password);

//If enabled, verbose log written on error.log
//define('LOG_VERBOSE', true);

//The path where the uploads are saved. Must be writtable by the webserver
define('FILE_UPLOAD_PATH', 'uploads');
define('DEFAULT_FILE_API', empty($config->file_mode) ? false : true);

//Enables API Cache. For now only APC is implemented
define('CACHE_ENABLED', true);

//Enable OAuth 1.0 Authentication
define('ENABLE_OAUTH', false);

//Enable simple login API
define('DEFAULT_LOGIN_API', empty($config->auth_mode) ? false : true);

//Enable simple SaaS Mode
define('DEFAULT_SAAS_MODE', empty($config->saas_mode) ? false : true);

//Enable open registrations
define('ENABLE_OPEN_REGISTRATIONS', empty($config->open_registrations) ? false : true);


//Excluded Routes
define('EXCLUDED_ROUTES', empty($config->excluded_routes) ? json_encode(array()) : json_encode($config->excluded_routes));

//Enable deep nested queries
define('ENABLE_DEEP_QUERY', true);
define('MAX_NESTING_LEVEL', 10);

//Return exceptions in API response
define('API_EXCEPTIONS_IN_RESPONSE', true);

//Legacy mode
define('LEGACY_MODE', empty($config->legacy_mode) ? false : true);

//Disable PHP Errors
error_reporting(0);

?>
