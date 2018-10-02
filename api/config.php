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
define('DEFAULT_FILE_API', false);

//Enables API Cache. For now only APC is implemented
define('CACHE_ENABLED', true);

//Enable OAuth 1.0 Authentication
define('ENABLE_OAUTH', false);

//Enable simple login API
define('DEFAULT_LOGIN_API', false);

//Enable simple SaaS Mode
define('DEFAULT_SAAS_MODE', false);

//Enable open registrations
define('ENABLE_OPEN_REGISTRATIONS', false);

//Enable deep nested queries
define('ENABLE_DEEP_QUERY', true);
define('MAX_NESTING_LEVEL', 10);

define('LEGACY_MODE', empty($config->legacy_mode) ? false : true);

//Disable PHP Errors
error_reporting(0);

?>
