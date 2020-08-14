<?php


//If getallheaders doesn't exist
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

//Custom Functions
function url_get($url, $params = null, $headers = null, $unsafe = false){
    if(function_exists('curl_init')){
    	$ch = curl_init();
    
    	curl_setopt($ch, CURLOPT_URL,$url);
    	if($params != null) curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($params));
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	if($headers != null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($unsafe){
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	}

    	$server_output = curl_exec ($ch);

    	if(empty($server_output)){
    	    
            $server_error = curl_error($ch);
            $server_errno = curl_errno($ch);
        
            $server_output = ApiResponse::errorResponse($server_errno, $server_error); 
    	}    	
    
    	curl_close ($ch);
    }

	return $server_output;
}

function url_post($url, $payload = null, $headers = null, $unsafe = false){
    if(function_exists('curl_init')){

    	$ch = curl_init();

    	curl_setopt($ch, CURLOPT_URL,$url);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	if($payload != null) curl_setopt($ch, CURLOPT_POSTFIELDS,$payload);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	if($headers != null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if($unsafe){
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	}

    	$server_output = curl_exec ($ch);

    	if(empty($server_output)){
    	    
            $server_error = curl_error($ch);
            $server_errno = curl_errno($ch);
        
            $server_output = ApiResponse::errorResponse($server_errno, $server_error); 
    	}


    	curl_close ($ch);
    }

	return $server_output;

}

function send_email_smtp($from, $to, $subject, $body, $smtp, $debug=false, $cc = array(), $bcc = array(), $from_name = "", $to_names = array(), $reply_to = "", $reply_to_name = ""){
    try{
        //PHPMailer Object
        $mail = GetPHPMailer();
    
        //Enable SMTP debugging. 
        if($debug) $mail->SMTPDebug = 3;                               
        //Set PHPMailer to use SMTP.
        $mail->isSMTP();            
        //Set SMTP host name                          
        $mail->Host = $smtp["host"];
        
        if(!(empty($smtp["username"]) || empty($smtp["username"]))){
            //Set this to true if SMTP host requires authentication to send email
            $mail->SMTPAuth = true;                          
            //Provide username and password     
            $mail->Username = $smtp["username"];
            $mail->Password = $smtp["password"];
            
        }
    
        if(!empty($smtp["proto"])){
            //If SMTP requires TLS encryption then set it  - tls/ssl
            $mail->SMTPSecure = $smtp["proto"];                           
        }
    
        if(!empty($smtp["port"])){
            //Set TCP port to connect to  - 587 / 25 / 465
            $mail->Port = $smtp["port"];              
        }
        
        
        //From email address and name
        $mail->From = $from;
        $mail->FromName = empty($from_name) ? $from : $from_name;
        
        //To address and name
        $to_names = empty($to_names) ? $to : $to_names;
        
        for ($i = 0; $i < count($to); $i++) {
            $mail->addAddress($to[$i], empty($to_names[$i]) ? $to[$i] : $to_names[$i]);     
        }
        
        //Address to which recipient will reply
        if(!empty($reply_to))
        $mail->addReplyTo($reply_to, empty($reply_to_name) ? $reply_to : $reply_to_name);
        
        //CC and BCC
        for ($i = 0; $i < count($cc); $i++) {
            $mail->addCC($cc[$i]);     
        }
    
        for ($i = 0; $i < count($bcc); $i++) {
            $mail->addBCC($bcc[$i]);     
        }

        //Send HTML or Plain Text email
        $mail->isHTML(true);
        
        $mail->Subject = $subject;
        $mail->Body = $body;
        //$mail->AltBody = "This is the plain text version of the email content";
        
        if(!$mail->send()) 
        {
            $error = ApiResponse::errorResponse(500, $mail->ErrorInfo); 
            return $error;
        } 
        else 
        {
            return "OK";
        }
    } catch (Exception $e){
            $error = ApiResponse::errorResponse(500, $mail->ErrorInfo); 
            return $error;
    }

}

//Usage
//$from = "youremail@yourdomain.com";
//$to = ["recepientsemail@theirdomain.com"];
//$subject = "SUBJECT HERE";
//$body = "TEXT HERE";
//$smtp = array(
//          "host" => "smtp.theirdomain.com",
//          "username" => "username",
//          "password" => "password",
//          "proto" => "tls",
//          "port" => "587"
//);
//echo send_email_smtp($from, $to, $subject, $body, $smtp);



function send_email_sparkpost($from, $to, $subject, $body, $api_key){
	$url = "https://api.sparkpost.com/api/v1/transmissions";
	$recipients = array();
	for ($i=0; $i < count($to); $i++) { 
		array_push($recipients, array("address" => $to[$i]));
	}
	$payload =json_encode(array("content" => array("from"=>$from,"subject"=>$subject, "html"=>$body),"recipients"=>$recipients));
	$headers = [
		//'Content-Type: application/json',
		'Authorization: ' . $api_key
	];

	return url_post($url, $payload, $headers);
}


//Usage
//$from = "youremail@yourdomain.com";
//$to = ["recepientsemail@theirdomain.com"];
//$api_key = "YOUR_SPARKPOST_API_KEY";
//$subject = "SUBJECT HERE";
//$body = "TEXT HERE";
//echo send_email_sparkpost($from, $to, $subject, $body, $api_key);

function uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}


function string_endswith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

function string_startswith($haystack, $needle){
    return (strncmp($haystack, $needle, strlen($needle)) === 0);
}

function string_intersect($string_1, $string_2)
{
    $string_1_length = strlen($string_1);
    $string_2_length = strlen($string_2);
    $return          = "";

    if ($string_1_length === 0 || $string_2_length === 0) {
        // No similarities
        return $return;
    }

    $longest_common_subsequence = array();

    // Initialize the CSL array to assume there are no similarities
    for ($i = 0; $i < $string_1_length; $i++) {
        $longest_common_subsequence[$i] = array();
        for ($j = 0; $j < $string_2_length; $j++) {
            $longest_common_subsequence[$i][$j] = 0;
        }
    }

    $largest_size = 0;

    for ($i = 0; $i < $string_1_length; $i++) {
        for ($j = 0; $j < $string_2_length; $j++) {
            // Check every combination of characters
            if ($string_1[$i] === $string_2[$j]) {
                // These are the same in both strings
                if ($i === 0 || $j === 0) {
                    // It's the first character, so it's clearly only 1 character long
                    $longest_common_subsequence[$i][$j] = 1;
                } else {
                    // It's one character longer than the string from the previous character
                    $longest_common_subsequence[$i][$j] = $longest_common_subsequence[$i - 1][$j - 1] + 1;
                }

                if ($longest_common_subsequence[$i][$j] > $largest_size) {
                    // Remember this as the largest
                    $largest_size = $longest_common_subsequence[$i][$j];
                    // Wipe any previous results
                    $return       = "";
                    // And then fall through to remember this new value
                }

                if ($longest_common_subsequence[$i][$j] === $largest_size) {
                    // Remember the largest string(s)
                    $return = substr($string_1, $i - $largest_size + 1, $largest_size);
                }
            }
            // Else, $CSL should be set to 0, which it was already initialized to
        }
    }

    // Return the list of matches
    return $return;
}


function array_search_where($array, $column_name, $where, $single=true, $return_only_key = false) {
   $results = array();
   foreach ($array as $key => $val) {
       if ($val[$column_name] === $where) {
	       
       		if($return_only_key)
		{
			$ret = $key;
		} else {
			$ret = $val;
		}
       		if($single) {
			return $ret;
		} else {
			array_push($results, $ret);
		}
       }
   }
   return $results;
}

function request_is_mobile(){
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	$is_mobile = (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
	return $is_mobile;
}

function prepare_email_body($template, $data){
    $result = $template;
    foreach ($data as $k => $v) {
        $result = str_replace("{{" . $k . "}}", $v, $result);    
    }
    return $result;
}

function encrypt($text, $key){
    $keyObj = load_encryption_key($key);
    return Defuse\Crypto\Crypto::encrypt($text, $keyObj);
}

function decrypt($text, $key){
    $keyObj = load_encryption_key($key);
    return Defuse\Crypto\Crypto::decrypt($text, $keyObj);
}

function generate_encryption_key(){
    $key = Defuse\Crypto\Key::createNewRandomKey();
    return $key->saveToAsciiSafeString();
}

function load_encryption_key($key){
    return Defuse\Crypto\Key::loadFromAsciiSafeString($key);
}

function recursive_array_diff($a1, $a2) { 
    $r = array(); 
    foreach ($a1 as $k => $v) {
        if (array_key_exists($k, $a2)) { 
            if (is_array($v)) { 
                $rad = recursive_array_diff($v, $a2[$k]); 
                if (count($rad)) { $r[$k] = $rad; } 
            } else { 
                if ($v != $a2[$k]) { 
                    $r[$k] = $v; 
                }
            }
        } else { 
            $r[$k] = $v; 
        } 
    } 
    return $r; 
}

function get_diff($obj1, $obj2){
    if(is_array($obj1) && is_array($obj2)){
        return recursive_array_diff((array) $obj1, (array) $obj2);
    }
    return recursive_array_diff($obj1, $obj2);
}

function print_var_name($var) {
    foreach($GLOBALS as $var_name => $value) {
        if ($value === $var) {
            return $var_name;
        }
    }

    return false;
}

function get_diff_both($obj1, $obj2){
    return array(get_diff($obj1, $obj2),
                get_diff($obj2, $obj1)
                );
    
}

function now(){
	return date("Y-m-d H:i:s");
}	

function today(){
	return date("Y-m-d");
}	

function toDate($datetime){
	return date("Y-m-d", strtotime($datetime));
}

function dump($content, $filename = 'dump.txt', $overwrite = false){
	if($overwrite){
		file_put_contents(__DIR__ . '/' . $filename, $content);
	} else {
		$newContent = "[" . now() . "]:";
		$newContent .= "\n";
		$newContent .= $content;
		$newContent .= "\n--------------------------------------------------------------------------------\n";
		file_put_contents(__DIR__ . '/' . $filename, $newContent, FILE_APPEND);
	}	
}

?>
