<?php

require_once('lib.php');

$configFile = "../prestige.config";

$data["host"] = $_POST["host"];
$data["user"] = $_POST["user"];
$data["password"] = $_POST["password"];
$data["database"] = $_POST["database"];

if(empty($data["host"])){
    $data["host"] = "localhost";
}

$config = json_encode($data);
$config_encoded = $encode_decode_simple->encode($config);
file_put_contents($configFile,$config_encoded);

header("Location: ../");
?>
