<?php

class ApiResponse {


	public static function errorResponseWithMessage($errorCode, $status, $message) {
		if(empty($message)) $message = $status;


		$result = array
		(
			'status' => $errorCode,
			'error' => array
				(
					'code' => $errorCode,
					'status' => $status,
					'message' => $message,
				),
		);

		return $result;
	}
	
	public static function successResponse($data = null, $statusCode = 200) {
		switch($statusCode) {
			case 201:
			$status = "Created";
			break;
			case 200:
			default:
			$status = "OK";
			break;
		}
		$result = array
		(
			'status' => $statusCode,
			'success' => array
				(
					'code' => $statusCode,
					'status' => $status,
				),
		);
		
		if($data) $result["success"]["data"] = $data;

		return $result;
	}

	
	public static function errorResponse($errorCode, $message = null) {
		
		$status = "Bad Request";
		
		switch($errorCode) {
			case 204:
			$status = "No Content";
			break;
			case 400:
			$status = "Bad Request";
			break;
			case 401:
			$status = "Unauthorized";
			break;
			case 403:
			$status = "Forbidden";
			break;
			case 404:
			$status = "Not Found";
			break;
			case 405:
			$status = "Method Not Allowed";
			break;
			case 409:
			$status = "Conflict";
			break;
			case 500:
			$status = "Internal Server Error";
			break;
			case 503:
			$status = "Service Unavailable";
			break;
		
		}
		
		return ApiResponse::errorResponseWithMessage($errorCode, $status, $message);
		
	}
	
}

?>