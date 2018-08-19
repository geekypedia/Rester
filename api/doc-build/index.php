<?php

require_once('swagger-php/library/Swagger/Swagger.php');
require_once('swagger-php/library/Swagger/Logger.php');
require_once('swagger-php/library/Swagger/Parser.php');

use Swagger\Swagger;
$swagger = new Swagger('/Users/jcornado/Documents/devel/rutas-backend/');
header("Content-Type: application/json");
echo $swagger->getResource('/pet', array('output' => 'json'));
?>