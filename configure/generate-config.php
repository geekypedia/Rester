<?php
$configFile = "../rester.config";
$config = json_encode($_POST);
$config_encoded = base64_encode($config);
file_put_contents($configFile,$config_encoded);

header("Location: ../");
?>
