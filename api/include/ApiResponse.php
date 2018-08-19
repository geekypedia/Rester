<?php

class ApiResponse {


	public static function errorResponseWithMessage($errorCode, $message) {
		$result = array
		(
			'error' => array
				(
					'code' => $errorCode,
					'status' => $message,
				),
		);

		return $result;
	}
	
	public static function successResponse() {
		$result = array
		(
			'success' => array
				(
					'code' => 200,
					'status' => 'OK',
				),
		);

		return $result;
	}

	
	public static function errorResponse($errorCode) {
		
		$status = "Bad Request";
		
		switch($errorCode) {
			case 204:
			$status = "No Content";
			break;
			case 400:
			$status = "Bad Request";
			break;
			case 403:
			$status = "Forbidden";
			break;
			case 404:
			$status = "Not found";
			break;
			case 405:
			$status = "Method not allowed";
			break;
			case 409:
			$status = "Conflict";
			break;
			case 503:
			$status = "Service Unavailable";
			break;
		
		}
		
		return ApiResponse::errorResponseWithMessage($errorCode, $status);
		
	}
	
}

?>