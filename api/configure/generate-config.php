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

header("Location: ../");
?>
