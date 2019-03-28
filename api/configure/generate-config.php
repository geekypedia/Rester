<?php

require_once('lib.php');
require_once(__DIR__.'/../vendor/phar/defuse-crypto-2.1.0.phar');

$configFile = "../prestige.config";
$keyFile = "../prestige.key";

$keyObj = Defuse\Crypto\Key::createNewRandomKey();
$key = $keyObj->saveToAsciiSafeString();

$data["host"] = $_POST["host"];
$data["user"] = $_POST["user"];
$data["password"] = $_POST["password"];
$data["database"] = $_POST["database"];
$data["legacy_mode"] = $_POST["legacy_mode"];
$data["file_mode"] = $_POST["file_mode"];
$data["auth_mode"] = $_POST["auth_mode"];
$data["saas_mode"] = $_POST["saas_mode"];
$data["open_registrations"] = $_POST["open_registrations"];


$excluded_routes_raw = $_POST["excluded_routes"];
$excluded_routes = array();
if(!empty($excluded_routes_raw))  {
    $excluded_routes_raw = str_replace("\r\n", ",", $excluded_routes_raw);
    $excluded_routes_raw = str_replace("\n\r", ",", $excluded_routes_raw);
    $excluded_routes_raw = str_replace("\n", ",", $excluded_routes_raw);
    $excluded_routes_raw = array_map('trim', explode(",", $excluded_routes_raw));

    $excluded_routes = array_values(array_filter($excluded_routes_raw));
}

$data["excluded_routes"] = $excluded_routes;

if(empty($data["host"])){
    $data["host"] = "localhost";
}

if(empty($data["password"])){
    $data["password"] = "";
}

$config = json_encode($data);
$config_encoded = $encode_decode_simple->encode($config);
$config_encrypted = Defuse\Crypto\Crypto::encrypt($config_encoded, $keyObj);
file_put_contents($configFile,$config_encrypted);
file_put_contents($keyFile,$key);

//For debugging purpose
//file_put_contents($configFile . ".json",$config);

header("Location: ../");
?>
