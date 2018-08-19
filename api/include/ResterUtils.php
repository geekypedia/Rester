<?php

class ResterUtils {
	
	/**
	* Function to check if array is number indexed 0,1,2,3...
	*/
	static function isIndexed($arr) {
		return array_values($arr) === $arr;
	}
	
	static function cleanArray($arr, $keys) {
		$ret = array();
		foreach($keys as $k) {
			if(isset($arr[$k]) && $arr[$k] !== "") //remove empty elements
				$ret[$k]=$arr[$k];
		}
		
		return $ret;
	}

	static function Log($message) {
		if(defined('LOG_VERBOSE')) {
			error_log($message);
		}
	}
	
	static function Dump($x, $message = NULL) {
		if(defined('LOG_VERBOSE')) {
			
			if($message != NULL)
				Log($message);
			
			// Dump x
			ob_start();
			var_dump($x);
			$contents = ob_get_contents();
			ob_end_clean();
			error_log($contents);
			
			if($message != NULL)
				Log("**************************************");
		}
	}
}

?>