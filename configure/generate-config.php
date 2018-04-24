<?php

require_once('lib.php');

$configFile = "../rester.config";
$config = json_encode($_POST);
$config_encoded = $encode_decode_simple->encode($config);
file_put_contents($configFile,$config_encoded);

header("Location: ../");
?>
