<?php

/**
 * (c) 2019 Om Talsania
 * MIT License
 */

//error_reporting(E_ALL);
error_reporting(0);

set_time_limit(0);

function download_zip($url, $filename){
    $echolog[] = "";

    $os =  substr(strtoupper(PHP_OS), 0, 3);
    $slash = $os == "WIN" ? "\\" : "/";

    $output_file_path = __DIR__ . $slash . $filename;
    $output_dir = __DIR__;
    $fp = fopen ($output_file_path, 'w+');//This is the file where we save the zip file
    
    $echolog[] = "Initializing settings ...";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $echolog[] = "Starting download ...";
    curl_exec($ch); // get curl response
    curl_close($ch);
    fclose($fp);
    $echolog[] = "Completed request ...";

    return $echolog;
}


function download_zip_extract($url, $filename){
    $echolog[] = "";

    $os =  substr(strtoupper(PHP_OS), 0, 3);
    $slash = $os == "WIN" ? "\\" : "/";

    $output_file_path = __DIR__ . $slash . $filename;
    $output_dir = __DIR__;
    $fp = fopen ($output_file_path, 'w+');//This is the file where we save the zip file
    
    $echolog[] = "Initializing settings ...";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $echolog[] = "Starting download ...";
    curl_exec($ch); // get curl response
    curl_close($ch);
    fclose($fp);
    
    $echolog[] =  "Starting to extract ...";
    if (file_exists($output_file_path)){
        $zip = new ZipArchive;
        $res = $zip->open($output_file_path);
        if ($res === TRUE)
        {
            $zip->extractTo($output_dir);
            $zip->close();
            $echolog[] =   'Completed extracting ...';
        }
        else
        {
            $echolog[] =  'There was a problem opening the zip file: '.$res;
        }
    }
    else{
        $echolog[] = "There was an error downloading, writing or accessing the zip file.";
    }

    array_shift($echolog);

    return $echolog;
}



$echolog[] = "";

define("REL_PATH", "../.."); //prod
//define("REL_PATH", "../../../.."); //dev

include('../index-auth.php');
$auth_val = authenticate('../');

$auth = !($auth_val) ? false : true;
define("ADMIN_MODE", $auth); //set to true to allow unsafe operations, set back to false when finished

define("NODE_OUT", "logs");
define("NODE_PID", "node.pid.config");

$node_ver = !empty($_POST["version"]) ? $_POST["version"] : ( !empty($_REQUEST["version"]) ? $_REQUEST["version"] : "v10.15.3" );

define("NODE_VER", $node_ver);

define("NODE_ARCH", "x" . substr(php_uname("m"), -2)); //x86 or x64

$node_url_prefix = "http://nodejs.org/dist/";
$node_os = "linux";
$node_arch = "x64";
$node_os_arch_separator = "-";
$node_file_ext = ".tar.gz";
$node_exe = "node";
$npm_exe = "npm";
$bin = "bin";
$binroot = "bin";

$slash = "/";

switch (PHP_OS) {
	case 'Win':
	case 'WinNT':
	case 'WIN':
	case 'WINNT':		
		$node_os = "win";
		$binroot = ".";
		$bin = ".";
		$slash = "\\";
		$node_file_ext = ".zip";
		$node_exe = $node_exe . ".exe";
		$npm_exe = $npm_exe . ".cmd";	
		break;
	case 'Darwin':
		$node_os = "darwin";
		break;
	case 'Linux':
	default:
		$node_os = "linux";
		break;
}

define("SLASH", $slash);
define("BINROOT", $binroot);
define("BIN", $bin);
define("NODE", $node_exe);
define("NPM", $npm_exe);
define("NODE_OS", $node_os);
define("NODE_URL_PREFIX", $node_url_prefix);

$node_file = "node-" . NODE_VER . $node_os_arch_separator . NODE_OS . $node_os_arch_separator . NODE_ARCH;

define("NODE_FILE_WOEXT", $node_file);
define("NODE_FILE", $node_file . $node_file_ext);

define("NODE_URL", NODE_URL_PREFIX . NODE_VER . "/" . NODE_FILE);

define("NODE_DIR", __DIR__. SLASH . ".." . SLASH . "node");

$node_host = !empty($_POST["host"]) ? $_POST["host"] : ( !empty($_REQUEST["host"]) ? $_REQUEST["host"] :  "localhost");
$node_port = (int)(!empty($_POST["port"]) ? $_POST["port"] : ( !empty($_REQUEST["port"]) ? $_REQUEST["port"] :  "49999"));

define("NODE_HOST", $node_host);
define("NODE_PORT", $node_port);



function node_install() {
	global $echolog;
	if(file_exists(NODE_DIR)) {
		node_error(405);
		$echolog[] = "Node.js is already installed.";
		return;
	}

	if(!file_exists(NODE_FILE)) {
		$echolog[] = "Downloading Node.js from " . NODE_URL . ":";

		/*
		$fp = fopen(NODE_FILE, "w+");
		flock($fp, LOCK_EX);
		$curl = curl_init(NODE_URL);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_FILE, $fp);
		$resp = curl_exec($curl);
		curl_close($curl);
		flock($fp, LOCK_UN);
		fclose($fp);
		$echolog[] = $resp === true ? "Done." : "Failed. Error: curl_error($curl)";
		*/

		$echolog[] = download_zip(NODE_URL, NODE_FILE);
	}
	$echolog[] = "Installing Node.js:";

	$unzipCommand = NODE_OS == "win" ? __DIR__ . SLASH .  "unzip.bat" : "tar -xzf";
	$moveCommand = NODE_OS == "win" ? "move" : "mv";
	$touchCommand = NODE_OS == "win" ? "type nul >" : "touch";
	$linuxPostfix = NODE_OS == "win" ? "" : " -C node 2>&1";


	if(!file_exists(__DIR__ . SLASH . "node")){
		exec("mkdir " . __DIR__ . SLASH . "node", $out, $ret);
		$echolog[] = $out;
	}

	$cmd1 = $unzipCommand . " " . __DIR__ . SLASH . NODE_FILE . $linuxPostfix;
	$cmd2 = $moveCommand . " " . __DIR__ . SLASH . "node" . SLASH . NODE_FILE_WOEXT . " " . NODE_DIR;
	$cmd3 = $touchCommand . " " . NODE_PID;
	exec($cmd1, $out1, $ret1);
	$echolog[] = $out1;
	$echolog[] = $ret1 === 0 ? "Extraction Done." : "Extraction Failed. Error: $ret1";
	exec($cmd2, $out2, $ret2);
	$echolog[] = $out2;
	$echolog[] = $ret2 === 0 ? "Moved folder to desired location." : "Could not move folder to desired location. Error: $ret1";
	exec($cmd3, $out3, $ret3);
	$echolog[] = $out3;
	$echolog[] = $ret3 === 0 ? "Done." : "Failed. Error: $ret. Try putting node folder via (S)FTP or File Manager, so that " . __DIR__ . SLASH . "node" . SLASH . BIN . SLASH . NODE . " exists.";
}

function node_uninstall() {
	global $echolog;	
	if(!file_exists(NODE_DIR)) {
		node_error(503);
		$echolog[] = "Node.js is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	$echolog[] = "Uninstalling Node.js:";

	if((substr(strtoupper(PHP_OS), 0, 3) == "WIN")){
		$echolog[] = "del /s /q " . NODE_DIR . "";
		exec("del /s /q " . NODE_DIR . "", $out, $ret);
		$echolog[] = $out;
		exec("rd /s /q " . NODE_DIR . "", $out, $ret);
		$echolog[] = $out;
		exec("del /s /q " . __DIR__ . SLASH . NODE_PID . "", $out, $ret);	
		$echolog[] = $out;
		exec("del /s /q " .  __DIR__ . SLASH . NODE_OUT . "", $out, $ret);	
		$echolog[] = $out;
		exec("del /s /q " . __DIR__ . SLASH . ".npm", $out, $ret);
		$echolog[] = $out;
		exec("rd /s /q " . __DIR__ . SLASH . ".npm", $out, $ret);
		$echolog[] = $out;

	} else {
		exec("rm -rfv " . NODE_DIR . "", $out, $ret);
		$echolog[] = $out;
		exec("rm -rfv " . __DIR__ . SLASH . NODE_PID . "", $out, $ret);	
		$echolog[] = $out;
		exec("rm -rfv " .  __DIR__ . SLASH . NODE_OUT . "", $out, $ret);	
		$echolog[] = $out;
		exec("rm -rfv " . __DIR__ . SLASH . ".npm", $out, $ret);
		$echolog[] = $out;
		exec("rm -rfv node_modules", $out, $ret);
		$echolog[] = $out;
	}
	$echolog[] = $ret === 0 ? "Done." : "Failed. Error: $ret";
}

function node_start($file) {
	global $echolog;	
	if(!file_exists(NODE_DIR)) {
		node_error(503);
		$echolog[] = "Node.js is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	$node_pid = intval(file_get_contents(NODE_PID));
	if($node_pid > 0) {
		node_error(405);
		$echolog[] = "Node.js is already running";
		return;
	}
	$file = escapeshellarg($file);
	$start = '/workspace';
	$startlen = strlen($start);
	$pos = strpos($file, '/workspace');
	$sub = substr($file, $pos + $startlen);
	$displayFile = "{{WORKSPACE}}" . $sub;

	$echolog[] = "Starting: node $displayFile";

	//$node_pid = exec("PORT=" . NODE_PORT . " " . NODE_DIR . "/bin/node $file >" . NODE_OUT . " 2>&1 & echo $!");
	$SETVAR = (NODE_OS == 'win') ? "set " : "";
	$SETSEP = (NODE_OS == 'win') ? "&& " : " ";
	$LINTRAIL = (NODE_OS == 'win') ? "" : " 2>&1 & echo $!";
	$file = str_replace("/", SLASH, $file);
	$startcmd = $SETVAR ."PORT=" . NODE_PORT . $SETSEP . NODE_DIR . SLASH . BINROOT . SLASH . NODE . " $file > " . NODE_OUT . $LINTRAIL;
	$node_pid = exec($startcmd);


	if($node_pid > 0){ 
		$echolog[] = "Done. PID=$node_pid"; 
	}
	else {
		node_error();
		$echolog[] = "Failed.";
	}
	file_put_contents(NODE_PID, $node_pid, LOCK_EX);
	sleep(1); //Wait for node to spin up
	$echolog[] = file_get_contents(NODE_OUT);
}

function node_stop() {
	global $echolog;	
	if(!file_exists(NODE_DIR)) {
		node_error(503);
		$echolog[] = "Node.js is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	$node_pid = intval(file_get_contents(NODE_PID));
	if($node_pid === 0) {
		node_error(503);
		$echolog[] = "Node.js is not yet running. Please go to Administration panel to start it.";
		return;
	}
	$echolog[] = "Stopping Node.js with PID=$node_pid";
	$ret = -1;
	passthru("kill $node_pid", $ret);
	if($ret === 0){
		$echolog[] = "Done";
	} else {
		node_error();
		$echolog[] = "Failed. Error: $ret";
	}
	file_put_contents(NODE_PID, '', LOCK_EX);
}

function node_npm($cmd, $prefix) {
	global $echolog;	
	if(!file_exists(NODE_DIR)) {
		node_error(403);
		$echolog[] = "Node.js is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	
	$prefixbase = " --prefix " . __DIR__ . SLASH . REL_PATH . SLASH . "ide" . SLASH . "workspace" . SLASH;
	
	if($prefix) {
		$prefixpassed = $prefix;
		if(endsWith($prefix, ".js")){
			$exp = explode("/", $prefix);
			array_pop($exp);
			$stripped = implode(SLASH, $exp);
			$prefixpassed = $stripped;
		}
		$prefixcmd = $prefixbase . $prefixpassed;	
	} else {
		$prefixcmd = $prefixbase . "node";
	}
	
	$npm_cmd = (substr(strtoupper(PHP_OS), 0, 3) == "WIN") ? NODE_DIR . SLASH . BIN . SLASH . NPM : NODE_DIR . SLASH . BIN . SLASH . NODE . " " . NODE_DIR . SLASH . BIN . SLASH . NPM;
	$cmd = escapeshellcmd($npm_cmd . " --cache ." .SLASH. ".npm ". $prefixcmd  ." $cmd");
	
	$echolog[] = "Running: $cmd";
	$ret = -1;
	exec($cmd, $out, $ret);
	$echolog[] = $out;
	if($ret === 0){
		$echolog[] = "Done";
	} else {
		node_error();
		$echolog[] = "Failed";
		//$echolog[] = "Failed. Error: $ret. See <a href=\"npm-debug.log\">npm-debug.log</a>";
	}

}

function node_serve($path = "") {
	
	global $echolog;	
	if(!file_exists(NODE_DIR)) {
		//node_head();
		node_error(503);
		$echolog[] = "Node.js is not yet installed. Please go to Administration panel to install it.";
		//node_foot();
		return;
	}
	$node_pid = intval(file_get_contents(NODE_PID));
	if($node_pid === 0) {
		//node_head();
		node_error(405);
		$echolog[] = "Node.js is not yet running. Please go to Administration panel to start it.";
		//node_foot();
		return;
	}
		
	$curl = curl_init("http://" . NODE_HOST . ":" . NODE_PORT . "/$path");
	curl_setopt($curl, CURLOPT_HEADER, 1);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $headers = array();
        foreach(getallheaders() as $key => $value) {
                $headers[] = $key . ": " . $value;
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_SERVER["REQUEST_METHOD"]);
        if($_SERVER["REQUEST_METHOD"] === "POST") {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($_POST));
        }
        if($_SERVER["REQUEST_METHOD"] === "PUT") {
		$putData = @file_get_contents('php://input');
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($putData));
	}
	
 	$resp = curl_exec($curl);

	if($resp === false) {
		//node_head();
		node_error();
		$echolog[] = "Error requesting $path: " . curl_error($curl);
		return;
		//node_foot();
	} else {
		list($head, $body) = explode("\r\n\r\n", $resp, 2);

		$headarr = explode("\n", $head);
		foreach($headarr as $headval) {
			if($headval == "Transfer-Encoding: chunked") continue;
			header($headval);
		}
		echo $body;
	}
	 	
	curl_close($curl);
	
	
	exit();
}


function node_status() {
	global $echolog;	
	$result = array();
	if(!file_exists(NODE_DIR)) {
		$result["installed"] = false;
		$result["installationStatus"] = "Not Installed";
	} else {
		$result["installed"] = true;
		$result["installationStatus"] = "Installed";
	}
	$node_pid = intval(file_get_contents(NODE_PID));
	if($node_pid > 0) {
		$result["running"] = true;
		$result["processStatus"] = "Running";
	} else {
		$result["running"] = false;
		$result["processStatus"] = "Stopped";
	}
	echo json_encode($result);
	exit();
}


function node_head() {
	$echolog[] = '<!DOCTYPE html><html><head><title>Node.php</title><meta charset="utf-8"><body style="font-family:Helvetica,sans-serif;"><h1>Node.php</h1><pre>';
}

function node_foot() {
	$echolog[] = '</pre><p><a href="https://github.com/niutech/node.php" target="_blank">Powered by node.php</a></p></body></html>';
}

function node_api_head(){
	header('Content-Type: application/json');
}

function node_error($code){
	if (empty($code)) $code = 500;
	http_response_code($code);
}

function node_success($code){
	if (empty($code)) $code = 200;
	http_response_code($code);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}


function node_dispatch() {
	global $echolog;	
	if(ADMIN_MODE) {
		//node_head();
		node_api_head();


		
		if($install = isset($_GET['install']) ? ($_GET['install']) : (isset($_POST['install']) ? ($_POST['install']) :  false)) {
			node_install();
		} elseif($uninstall = isset($_GET['uninstall']) ? ($_GET['uninstall']) : (isset($_POST['uninstall']) ? ($_POST['uninstall']) :  false)) {
			node_uninstall();
		} elseif($start = isset($_GET['start']) ? ($_GET['start']) : (isset($_POST['start']) ? ($_POST['start']) :  false)) {
			$serve_path = __DIR__ . '/' . REL_PATH . '/ide/workspace/' . $start;
			node_start($serve_path);
		} elseif($stop = isset($_GET['stop']) ? ($_GET['stop']) : (isset($_POST['stop']) ? ($_POST['stop']) :  false)) {
			node_stop();
		} elseif($npm = isset($_GET['npm']) ? ($_GET['npm']) : (isset($_POST['npm']) ? ($_POST['npm']) :  false)) {
			$prefix = isset($_GET['prefix']) ? ($_GET['prefix']) : (isset($_POST['prefix']) ? ($_POST['prefix']) :  false);
			node_npm($npm, $prefix);
		} elseif($nodestatus = isset($_GET['status']) ? ($_GET['status']) : (isset($_POST['status']) ? ($_POST['status']) :  false)) {
			node_status();
		} else {
		 	$echolog[] = "You are in Admin Mode. Switch back to normal mode to serve your node app.";
		}		
		//node_foot();
	} else {
		node_api_head();

		if($path = isset($_GET['path']) ? ($_GET['path']) : (isset($_POST['path']) ? ($_POST['path']) :  false)) {
			node_serve($path);
		} else {
			node_serve();
		}
		
	}
	array_shift($echolog);
	echo json_encode($echolog);
}

node_dispatch();
