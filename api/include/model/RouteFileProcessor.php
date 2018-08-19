<?php

class RouteFileProcessor {
	
	var $field;
	var $acceptedTypes = array();

	function RouteFileProcessor($field, $acceptedTypes = array("image/png", "image/jpg")) {
		$this->field = $field;
		$this->acceptedTypes = $acceptedTypes;
	}
	
	function saveUploadFile($idDoc, $routeName, $file) {
		
		
		$uploadDir = FILE_UPLOAD_PATH."/".$routeName;
		
		error_log("Saving uploaded file to ".$uploadDir);
		
		if(!file_exists($uploadDir)) {
			mkdir($uploadDir, 0777, true);
		}
		
		$upload["destination"] = $uploadDir."/". $idDoc."-".$file["name"];
		
		if (file_exists($upload["destination"])) {
			 error_log("File already exists");
			 return $upload;
		} else {
			move_uploaded_file($file["tmp_name"],  $upload["destination"]);
			return $upload;
		}
	}
}

?>