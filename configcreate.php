<?php
$configFile = "config.json";
$config = json_encode($_POST);
file_put_contents($configFile,$config);

header("Location: /");
?>
