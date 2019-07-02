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


define("PYTHON_OUT", "logs");
define("PYTHON_PID", "python.pid.config");

$binroot = "bin";
$bin = "bin";
$pypy_exe = "pypy3";
$pip_exe = "pip3";

$python_ver = !empty($_POST["version"]) ? $_POST["version"] : ( !empty($_REQUEST["version"]) ? $_REQUEST["version"] : "3.6" );
if($python_ver == "2.7") {
	$python_ver = "";
	$pypy_exe = "pypy";
	$pip_exe = "pip";
}
$pypy_ver = !empty($_POST["pypy_version"]) ? $_POST["pypy_version"] : ( !empty($_REQUEST["pypy_version"]) ? $_REQUEST["pypy_version"] : "7.1.1" );

$python_url_prefix = "https://bitbucket.org/squeaky/portable-pypy/downloads/";
$python_os = "linux";
$python_arch = "x86_64";
$python_os_arch_separator = "_";
$python_file_ext = "-portable.tar.bz2";

$slash = "/";

switch (PHP_OS) {
	case 'Win':
	case 'WinNT':
	case 'WIN':
	case 'WINNT':		
		$python_url_prefix = "https://bitbucket.org/pypy/pypy/downloads/";				
		$python_os = "win";
		$python_arch = "32";
		$python_os_arch_separator = "";
		$python_file_ext = ".zip";
		$pypy_ver = "v" . $pypy_ver;
		$slash = "\\";
		$binroot = ".";
		$bin = "bin";
		$pypy_exe = $pypy_exe . ".exe";
		$pip_exe = $pip_exe . ".exe";	
		break;
	case 'Darwin':
		$python_url_prefix = "https://bitbucket.org/pypy/pypy/downloads/";		
		$python_os = "osx";
		$python_arch = "64";
		$python_os_arch_separator = "";
		$python_file_ext = ".tar.bz2";
		$pypy_ver = "v" . $pypy_ver;		
		break;
	case 'Linux':
	default:
		$python_url_prefix = "https://bitbucket.org/squeaky/portable-pypy/downloads/";
		$python_os = "linux";
		$python_arch = "x86_64";
		$python_os_arch_separator = "_";
		$python_file_ext = "-portable.tar.bz2";
		$pypy_ver = $pypy_ver . "-beta";				
		break;
}

define("SLASH", $slash);
define("BINROOT", $binroot);
define("BIN", $bin);
define("PYPY", $pypy_exe);
define("PIP", $pip_exe);

define("PYTHON_VER", $python_ver . "-" . $pypy_ver);

define("PYTHON_URL_PREFIX", $python_url_prefix);
define("PYTHON_OS", $python_os);



//define("PYTHON_ARCH", "x" . substr(php_uname("m"), -2)); //x86 or x64
define("PYTHON_ARCH", $python_arch); //x86 or x64

define("PYTHON_OS_ARCH_SEPARATOR", $python_os_arch_separator);
define("PYTHON_FILE_EXT", $python_file_ext);

$python_file = "pypy" . PYTHON_VER . "-" . PYTHON_OS . PYTHON_OS_ARCH_SEPARATOR . PYTHON_ARCH . PYTHON_FILE_EXT;
define("PYTHON_FILE", $python_file);

//$url = 'https://bitbucket.org/squeaky/portable-pypy/downloads/pypy-7.1.1-linux_x86_64-portable.tar.bz2';
//$url = 'https://bitbucket.org/pypy/pypy/downloads/pypy3.6-v7.1.1-osx64.tar.bz2';
define("PYTHON_URL", PYTHON_URL_PREFIX . PYTHON_FILE);

define("PYTHON_DIR", __DIR__. SLASH . ".." . SLASH . "python");

$python_host = !empty($_POST["host"]) ? $_POST["host"] : ( !empty($_REQUEST["host"]) ? $_REQUEST["host"] :  "localhost");
$python_port = (int)(!empty($_POST["port"]) ? $_POST["port"] : ( !empty($_REQUEST["port"]) ? $_REQUEST["port"] :  "49999"));

define("PYTHON_HOST", $python_host);
define("PYTHON_PORT", $python_port);



function python_install() {
	global $echolog;
	if(file_exists(PYTHON_DIR)) {
		python_error(405);
		$echolog[] = "Python is already installed.";
		return;
	}
	
	/*
	if(PYTHON_OS == 'win'){
		$zf = __DIR__.'/unzip.exe';
		$zurl = 'http://stahlworks.com/dev/unzip.exe';
		$zfp = fopen($zf, "w");
		flock($zfp, LOCK_EX);
		$zcurl = curl_init($zurl);
		curl_setopt($zcurl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($zcurl, CURLOPT_HEADER, true);
		curl_setopt($zcurl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($zcurl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($zcurl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($zcurl, CURLOPT_SSL_VERIFYPEER, 0);		
		curl_setopt($zcurl, CURLOPT_FILE, $zfp);

		$zresp = curl_exec($zcurl);
		curl_close($zcurl);
		flock($zfp, LOCK_UN);
		fclose($zfp);	
		$echolog[] = $zresp === true ? "Downloaded unzip utility for windows" : "Failed. Error: curl_error($curl)";		
		
	}
	*/

	if(!file_exists(__DIR__. SLASH . PYTHON_FILE)) {		
		$echolog[] = "Downloading Python from " . PYTHON_URL . ":";
		
		$echolog[] = download_zip(PYTHON_URL, PYTHON_FILE);

		//CURL
		/*
		$fp = fopen(PYTHON_FILE, "w+");
		flock($fp, LOCK_EX);
		$curl = curl_init(PYTHON_URL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);		
		curl_setopt($curl, CURLOPT_FILE, $fp);

		$resp = curl_exec($curl);
		curl_close($curl);
		flock($fp, LOCK_UN);
		fclose($fp);
		$echolog[] = $resp === true ? "Done." : "Failed. Error: curl_error($curl)";
		*/

		/*
		passthru("curl -O -L " . PYTHON_URL,$resp);
		*/

		if($resp === 0){
		} else {
			if(file_exists(__DIR__. SLASH . PYTHON_FILE)){
				//unlink(__DIR__.'/'.PYTHON_FILE);
			}
		}
		
		
	
	}

	$echolog[] = "Installing Python:";
	
	if(file_exists(__DIR__ . SLASH . "python")){
	} else {
		exec("mkdir python", $out0, $ret0);
	}

	//if(file_exists(__DIR__.'/'."pypy" . PYTHON_VER . "-linux-" . PYTHON_ARCH . "-portable"))
	
	/*
	    if (file_exists(__DIR__ . SLASH . PYTHON_FILE)){
		$zip = new ZipArchive;
		$res = $zip->open(__DIR__ . SLASH . PYTHON_FILE);
		if ($res === TRUE)
		{
		    $zip->extractTo(__DIR__ . SLASH . "python");
		    $zip->close();
		    $echolog[] =   'Completed extracting ...';
		}
		else
		{
		    $echolog[] =  'There was a problem opening the zip file: '.$res;
		}
	    }
	*/	

	$cmd1 = "tar -xjvf " . PYTHON_FILE . " -C python 2>&1";
	if(PYTHON_OS == 'win'){
		$cmd1 = __DIR__. "\\unzip.bat " . __DIR__. "\\" . PYTHON_FILE . "";
	}
	
	exec($cmd1, $out1,$ret1);
	$echolog[] = $out1;	
	if($ret1 === 0){
		$echolog[] = $out1;
	} else {
		$echolog[] = "Could not complete extracting the bundle.";
	}
	//$extracted_dir = "/pypy" . PYTHON_VER . "-linux-" . PYTHON_ARCH . "-portable";
	$extracted_dir = SLASH . "python" . SLASH . "py*";
	
	$moveCommand = "mv";
	$touchCommand = "touch";
	if(PYTHON_OS == 'win'){
		$moveCommand = "move";
		$touchCommand = "type nul >";
	}
	$cmd2 = $moveCommand .  " " . __DIR__ . $extracted_dir  . " " . PYTHON_DIR;	
	
	exec($cmd2, $out2, $ret2);
	if($ret2 === 0){
		$echolog[] = "Moved the bundle to desired location."; 
		$echolog[] = $out2;
		exec($touchCommand . " "  . PYTHON_PID, $out3, $ret3);		
		$echolog[] = $ret3 === 0 ? $out3 : "Failed. Error: $ret3. Try putting python folder via (S)FTP, so that " . __DIR__ . "/python/bin/pypy exists.";
	} else {
		$echolog[] = "Could not move the bundle to desired location." . "Failed. Error: $ret. Try putting python folder via (S)FTP, so that " . __DIR__ . "/python/bin/pypy exists.";
	}

	$cmd4 = PYTHON_DIR . SLASH . BINROOT . SLASH . PYPY . " -m ensurepip";
	
	exec($cmd4, $out4, $ret4);
	$echolog[] = $out4;
	if($ret4 === 0){
	} else {
		$echolog[] = "Could not install pip. Please use the web terminal and execute python/bin/pypy -m ensurepip";
	}
	
	//passthru("rm -f " . PYTHON_FILE, $ret);
	

}

function python_uninstall() {
	global $echolog;	
	if(!file_exists(PYTHON_DIR)) {
		python_error(503);
		$echolog[] = "Python is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	$echolog[] = "Unnstalling Python:";
	exec("rm -rfv " . PYTHON_DIR . " " . PYTHON_PID . "", $out1, $ret);
	$echolog[] = $out1;
	exec("rm -rfv python_modules", $out2, $ret);
	$echolog[] = $out2;	
	exec("rm -rfv .pip", $out3, $ret);
	$echolog[] = $out3;	
	exec("rm -rfv ". PYTHON_OUT ."", $out4, $ret);
	$echolog[] = $out4;	
	$echolog[] = $ret === 0 ? "Done." : "Failed. Error: $ret";
}

function python_start($file) {
	global $echolog;	
	if(!file_exists(PYTHON_DIR)) {
		python_error(503);
		$echolog[] = "Python is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	$python_pid = intval(file_get_contents(PYTHON_PID));
	if($python_pid > 0) {
		python_error(405);
		$echolog[] = "Python is already running";
		return;
	}
	$file = escapeshellarg($file);
	$start = '/workspace';
	$startlen = strlen($start);
	$pos = strpos($file, '/workspace');
	$sub = substr($file, $pos + $startlen);
	$displayFile = "{{WORKSPACE}}" . $sub;
	$echolog[] = "Starting: python $displayFile";

	//$python_pid = exec("PORT=" . PYTHON_PORT . " " . PYTHON_DIR . SLASH . BINROOT . SLASH . PYPY . " $file >" . PYTHON_OUT . " 2>&1 & echo $!");
	$SETVAR = (PYTHON_OS == 'win') ? "set " : "";
	$SETSEP = (PYTHON_OS == 'win') ? "&& " : " ";
	$LINTRAIL = (PYTHON_OS == 'win') ? "" : " 2>&1 & echo $!";
	$file = str_replace("/", SLASH, $file);
	$startcmd = $SETVAR ."PORT=" . PYTHON_PORT . $SETSEP . PYTHON_DIR . SLASH . BINROOT . SLASH . PYPY . " $file > " . PYTHON_OUT . $LINTRAIL;
	$python_pid = exec($startcmd);
	
	if($python_pid > 0){ 
		$echolog[] = "Done. PID=$python_pid"; 
	}
	else {
		python_error();
		$echolog[] = "Failed.";
	}
	file_put_contents(PYTHON_PID, $python_pid, LOCK_EX);
	sleep(1); //Wait for python to spin up
	$echolog[] = file_get_contents(PYTHON_OUT);
}

function python_stop() {
	global $echolog;	
	if(!file_exists(PYTHON_DIR)) {
		python_error(503);
		$echolog[] = "Python is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	$python_pid = intval(file_get_contents(PYTHON_PID));
	if($python_pid === 0) {
		python_error(503);
		$echolog[] = "Python is not yet running. Please go to Administration panel to start it.";
		return;
	}
	$echolog[] = "Stopping Python with PID=$python_pid";
	$ret = -1;
	exec("kill $python_pid", $out, $ret);
	if($ret === 0){
		$echolog[] = $out;
	} else {
		python_error();
		//$echolog[] = "Failed. Error: $ret";
	}
	file_put_contents(PYTHON_PID, '', LOCK_EX);
}

function python_pip($cmd, $prefix) {
	global $echolog;	
	if(!file_exists(PYTHON_DIR)) {
		python_error(403);
		$echolog[] = "Python is not yet installed. Please go to Administration panel to install it.";
		return;
	}
	
	$prefixbase = " --prefix " . __DIR__ . SLASH . REL_PATH . SLASH . "ide" . SLASH . "workspace" . SLASH;
	
	if($prefix) {
		$prefixpassed = $prefix;
		if(endsWith($prefix, ".py")){
			$exp = explode("/", $prefix);
			array_pop($exp);
			$stripped = implode(SLASH, $exp);
			$prefixpassed = $stripped;
		}
		$prefixcmd = $prefixbase . $prefixpassed;	
	} else {
		$prefixcmd = $prefixbase . "python";
	}
	
	$cmd = escapeshellcmd(PYTHON_DIR . SLASH . BIN . SLASH . PIP . " " /* . $prefixcmd */  . " -- $cmd");
	
	$echolog[] = "Running: $cmd";
	$ret = -1;
	exec($cmd, $out, $ret);
	if($ret === 0){
		$echolog[] = $out;
		$echolog[] = "Done";
	} else {
		python_error();
		$echolog[] = "Failed. Error: $ret. See <a href=\"pip-debug.log\">pip-debug.log</a>";
	}
	return;

}

function python_serve($path = "") {
	
	global $echolog;	
	if(!file_exists(PYTHON_DIR)) {
		//python_head();
		python_error(503);
		$echolog[] = "Python is not yet installed. Please go to Administration panel to install it.";
		//python_foot();
		return;
	}
	$python_pid = intval(file_get_contents(PYTHON_PID));
	if($python_pid === 0) {
		//python_head();
		python_error(405);
		$echolog[] = "Python is not yet running. Please go to Administration panel to start it.";
		//python_foot();
		return;
	}
		
	$curl = curl_init("http://" . PYTHON_HOST . ":" . PYTHON_PORT . "/$path");
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
		//python_head();
		python_error();
		$echolog[] = "Error requesting $path: " . curl_error($curl);
		return;
		//python_foot();
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


function python_status() {
	global $echolog;	
	$result = array();
	if(!file_exists(PYTHON_DIR)) {
		$result["installed"] = false;
		$result["installationStatus"] = "Not Installed";
	} else {
		$result["installed"] = true;
		$result["installationStatus"] = "Installed";
	}
	$python_pid = intval(file_get_contents(PYTHON_PID));
	if($python_pid > 0) {
		$result["running"] = true;
		$result["processStatus"] = "Running";
	} else {
		$result["running"] = false;
		$result["processStatus"] = "Stopped";
	}
	echo json_encode($result);
	exit();
}


function python_head() {
	$echolog[] = '<!DOCTYPE html><html><head><title>Python.php</title><meta charset="utf-8"><body style="font-family:Helvetica,sans-serif;"><h1>Python.php</h1><pre>';
}

function python_foot() {
	$echolog[] = '</pre><p><a href="https://github.com/niutech/python.php" target="_blank">Powered by python.php</a></p></body></html>';
}

function python_api_head(){
	header('Content-Type: application/json');
}

function python_error($code){
	if (empty($code)) $code = 500;
	http_response_code($code);
}

function python_success($code){
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


function python_dispatch() {
	global $echolog;	
	if(ADMIN_MODE) {
		
			
		
		
		
		

		//python_head();
		python_api_head();


		if($_FILES['file-0']){
			//print_r($_FILES['file-0']);
			file_put_contents($_FILES['file-0']['name'], $_FILES['file-0']);
			$echolog[] = "Successfully uploaded " . $_FILES['file-0']['name'];
			array_shift($echolog);
			echo json_encode($echolog);
			exit();
		};
			

		
		if($install = isset($_GET['install']) ? ($_GET['install']) : (isset($_POST['install']) ? ($_POST['install']) :  false)) {
			python_install();
		} elseif($uninstall = isset($_GET['uninstall']) ? ($_GET['uninstall']) : (isset($_POST['uninstall']) ? ($_POST['uninstall']) :  false)) {
			python_uninstall();
		} elseif($start = isset($_GET['start']) ? ($_GET['start']) : (isset($_POST['start']) ? ($_POST['start']) :  false)) {
			$serve_path = __DIR__ . '/' . REL_PATH . '/ide/workspace/' . $start;
			python_start($serve_path);
		} elseif($stop = isset($_GET['stop']) ? ($_GET['stop']) : (isset($_POST['stop']) ? ($_POST['stop']) :  false)) {
			python_stop();
		} elseif($pip = isset($_GET['pip']) ? ($_GET['pip']) : (isset($_POST['pip']) ? ($_POST['pip']) :  false)) {
			$prefix = isset($_GET['prefix']) ? ($_GET['prefix']) : (isset($_POST['prefix']) ? ($_POST['prefix']) :  false);
			python_pip($pip, $prefix);
		} elseif($pythonstatus = isset($_GET['status']) ? ($_GET['status']) : (isset($_POST['status']) ? ($_POST['status']) :  false)) {
			python_status();
		} else {
		 	$echolog[] = "You are in Admin Mode. Switch back to normal mode to serve your python app.";
		}		
		//python_foot();
	} else {
		python_api_head();

		if($path = isset($_GET['path']) ? ($_GET['path']) : (isset($_POST['path']) ? ($_POST['path']) :  false)) {
			python_serve($path);
		} else {
			python_serve();
		}
		
	}
	array_shift($echolog);
	echo json_encode($echolog);
}

python_dispatch();
