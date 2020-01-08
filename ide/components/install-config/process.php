<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////////
// Paths
//////////////////////////////////////////////////////////////////////

    $path = $_POST['path'];

    $rel = str_replace('/components/install-config/process.php', '', $_SERVER['REQUEST_URI']);

    $workspace = $path . "/workspace";
    $users = $path . "/data/users.php";
    $projects = $path . "/data/projects.php";
    $active = $path . "/data/active.php";
    $config = $path . "/config.php";

//////////////////////////////////////////////////////////////////////
// Functions
//////////////////////////////////////////////////////////////////////

function saveFile($file, $data)
{
    $write = fopen($file, 'w') or die("can't open file");
    fwrite($write, $data);
    fclose($write);
}

function saveJSON($file, $data)
{
    $data = "<?php/*|\r\n" . json_encode($data) . "\r\n|*/?>";
    saveFile($file, $data);
}

function encryptPassword($p)
{
    return sha1(md5($p));
}

function cleanUsername($username)
{
    return preg_replace('#[^A-Za-z0-9'.preg_quote('-_@. ').']#', '', $username);
}

function isAbsPath($path)
{
    return $path[0] === '/';
}

function cleanPath($path)
{

    // prevent Poison Null Byte injections
    $path = str_replace(chr(0), '', $path);

    // prevent go out of the workspace
    while (strpos($path, '../') !== false) {
        $path = str_replace('../', '', $path);
    }

    return $path;
}

//////////////////////////////////////////////////////////////////////
// Verify no overwrites
//////////////////////////////////////////////////////////////////////

if (!file_exists($config)){
    //////////////////////////////////////////////////////////////////
    // Get POST responses
    //////////////////////////////////////////////////////////////////

    //$username = cleanUsername($_POST['username']);
    //$password = encryptPassword($_POST['password']);
    $timezone = $_POST['timezone'];

    //////////////////////////////////////////////////////////////////
    // Create Config
    //////////////////////////////////////////////////////////////////


    $config_data = '<?php

/*
*  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
*  as-is and without warranty under the MIT License. See
*  [root]/license.txt for more. This information must remain intact.
*/

//////////////////////////////////////////////////////////////////
// CONFIG
//////////////////////////////////////////////////////////////////

// PATH TO CODIAD
define("BASE_PATH", "' . $path . '");

// BASE URL TO CODIAD (without trailing slash)
define("BASE_URL", "' . $_SERVER["HTTP_HOST"] . $rel . '");

// THEME : default, modern or clear (look at /themes)
define("THEME", "default");

//Parent path of '/ide'
$additional_paths = "," . preg_replace("/\/ide[\/]?$/", "", BASE_PATH);
//Parent path of /anything
//$additional_paths = preg_replace("/\/(?:.(?!\/))+$/", "", $additional_paths);

// ABSOLUTE PATH
define("WHITEPATHS", BASE_PATH . ",/home" . $additional_paths);

// SESSIONS (e.g. 7200)
$cookie_lifetime = "0";

// TIMEZONE
date_default_timezone_set("' . $_POST['timezone'] . '");

// External Authentification
//define("AUTH_PATH", "/path/to/customauth.php");

//////////////////////////////////////////////////////////////////
// ** DO NOT EDIT CONFIG BELOW **
//////////////////////////////////////////////////////////////////

// PATHS
define("COMPONENTS", BASE_PATH . "/components");
define("PLUGINS", BASE_PATH . "/plugins");
define("THEMES", BASE_PATH . "/themes");
define("DATA", BASE_PATH . "/data");
define("WORKSPACE", BASE_PATH . "/workspace");

// URLS
define("WSURL", BASE_URL . "/workspace");

// Marketplace
//define("MARKETURL", "http://market.codiad.com/json");

// Update Check
//define("UPDATEURL", "http://update.codiad.com/?v={VER}&o={OS}&p={PHP}&w={WEB}&a={ACT}");
//define("ARCHIVEURL", "https://github.com/Codiad/Codiad/archive/master.zip");
//define("COMMITURL", "https://api.github.com/repos/Codiad/Codiad/commits");
';

    saveFile($config, $config_data);

    echo("success");
}
