<?php


$mysql_host = $_POST["host"];
$mysql_database = $_POST["database"];
$mysql_user = $_POST["user"];
$mysql_password = $_POST["password"];

$success = array("status" => "success");
$failure = array("status" => "failed");

try{
    $db = new PDO("mysql:host=$mysql_host;dbname=$mysql_database", $mysql_user, $mysql_password);
    
    $query = file_get_contents("seed.sql.txt");
    
    $stmt = $db->prepare($query);
    
    
    if ($stmt->execute()){
        $data = $success;
    }
    else {
        $data = $failure;
        $data["reason"] = $stmt->errorInfo();
        $data["data"] = $query;
        http_response_code(500);
    }
}catch(Exception $ex){
    $data = $failure;
    $data["reason"] = $ex->getMessage();
    http_response_code(500);
}
    
header('Content-Type: application/json');
echo json_encode($data);

?>
