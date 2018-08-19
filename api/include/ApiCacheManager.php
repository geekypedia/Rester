<?php

//Constants

define('ROUTE_CACHE_KEY', 'routesKey');


class ApiCacheManager {
		
	static function getValueFromCache($valueName) {
	
		if(!defined('CACHE_ENABLED') || !CACHE_ENABLED)
			return NULL;
		
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			$data = apc_fetch($valueName, $success);
			if(!$success)
				return NULL;
				
			return $data;
		} else {
			return NULL;
		}
	}
	
	static function saveValueToCache($valueKey, $valueData) {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			return apc_add($valueKey, $valueData);
		} else {
			return false;
		}

	}
	
	static function clear() {
		if(extension_loaded('apc') && ini_get('apc.enabled')) {
			apc_clear_cache();
			apc_clear_cache('user');
			ResterUtils::Log("Cleared cache");
		}
	}
	
}

?>