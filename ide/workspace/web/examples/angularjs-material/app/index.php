<?php

error_reporting(E_ERROR | E_PARSE);

header('Content-Type: application/javascript');

function getAllSubDirectories( $directory, $directory_seperator)
{
	$dirs = array_map( function($item)use($directory_seperator){ return $item . $directory_seperator;}, array_filter( glob( $directory . '*' ), 'is_dir') );
	foreach( $dirs AS $dir )
	{
		$dirs = array_merge( $dirs, getAllSubDirectories( $dir, $directory_seperator ) );
	}
	return $dirs;
}

function getAllFiles($directory, $excludedFiles = array()){
	$finalFiles = array();
	$subDirectories = getAllSubDirectories($directory,'/');
	array_push($subDirectories, $directory);

	foreach($subDirectories as &$subDir){
		$path = $subDir;
		
		$files = array_diff(scandir($path), array('.', '..'));
		
		
		foreach ($files as &$file) {
			$filePath = $path.$file;
			
			if(substr($filePath, -3) == ".js"){
				if(file_exists($filePath)){
					if(!in_array($file, $excludedFiles)){
					        array_push($finalFiles, $filePath);
					}
				}
			}
		}
	}
	
	return $finalFiles;
}


$scriptFile = 'index.js';

$app = array(__DIR__.'/app.js');
$init = getAllFiles(__DIR__.'/init/');
$conf = getAllFiles(__DIR__.'/config/');
$services = getAllFiles(__DIR__.'/services/');
$directives = getAllFiles(__DIR__.'/directives/');
$filters = getAllFiles(__DIR__.'/filters/');
$components = getAllFiles(__DIR__.'/components/');
$modules = getAllFiles(__DIR__.'/modules/');

$files = array_merge($app, $init, $conf, $services, $directives, $filters, $components, $modules);

$fp = fopen($scriptFile, 'w'); 

foreach($files as $file){
	$contents = file_get_contents($file);
	fwrite($fp, $contents);
}

fclose($fp);
echo file_get_contents($scriptFile);

?>
