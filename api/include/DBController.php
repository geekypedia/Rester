<?php

include_once("config.php");
require_once(__DIR__.'/model/Route.php');
require_once(__DIR__.'/model/RouteField.php');
require_once(__DIR__.'/model/RouteRelation.php');
require_once(__DIR__.'/model/MySQLRouteRelation.php');
require_once(__DIR__.'/model/JSONRouteRelation.php');
require_once(__DIR__.'/ResterUtils.php');
require_once(__DIR__.'/ApiDBDriver.php');
require_once(__DIR__.'/ApiCacheManager.php');

class DBController
{
	
	static $db = null;
	
	function DBController() {
	
		$options = array
				(
					\PDO::ATTR_CASE => \PDO::CASE_NATURAL,
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
					//\PDO::ATTR_EMULATE_PREPARES => false,
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
					\PDO::ATTR_STRINGIFY_FETCHES => false,
					\PDO::ATTR_PERSISTENT => true
				);
	
		$options += array
						(
							\PDO::ATTR_AUTOCOMMIT => true,
							\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "utf8" COLLATE "utf8_general_ci", time_zone = "+00:00";',
							\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
						);
		try{
			$this::$db = new \PDO(sprintf('%s:host=%s;port=%s;dbname=%s', "mysql", DBHOST, 3306, DBNAME), DBUSER, DBPASSWORD, $options);
		}
		catch(Exception $e)
		{
		}
	}

	public function Query($query = null)
	{
		static $result = array();

		try
		{
			if (isset($this::$db, $query) === true)
			{
			
				ResterUtils::Log("*** QUERY: ".$query." ***");
			
				if (strncasecmp($this::$db->getAttribute(\PDO::ATTR_DRIVER_NAME), 'mysql', 5) === 0)
				{
					$query = strtr($query, '"', '`');
				}

				if (empty($result[$hash = crc32($query)]) === true)
				{
					$result[$hash] = $this::$db->prepare($query);
				}
				
				$data = array_slice(func_get_args(), 1);
				
				if (count($data, COUNT_RECURSIVE) > count($data))
				{
					$data = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data)), false);
				}
								
				if ($result[$hash]->execute($data) === true)
				{
					/*$sequence = null;

					if ((strncmp($this::$db->getAttribute(\PDO::ATTR_DRIVER_NAME), 'pgsql', 5) === 0) && (sscanf($query, 'INSERT INTO %s', $sequence) > 0))
					{
						$sequence = sprintf('%s_id_seq', trim($sequence, '"'));
					}*/
					
					switch (strtoupper(strstr($query, ' ', true)))
					{
						case 'INSERT':
						case 'REPLACE':
							return $this::$db->lastInsertId();

						case 'UPDATE':
						case 'DELETE':
							return $result[$hash]->rowCount();
						case 'SELECT':
						case 'EXPLAIN':
						case 'PRAGMA':
						case 'SHOW':
						case 'DESCRIBE':
							return $result[$hash]->fetchAll();
						case 'CALL':
						    $r = array();
						    
						    $r[] = $result[$hash]->fetchAll();
						    
						    try {
							    while ($result[$hash]->nextRowset()) {
								    $r[] = $result[$hash]->fetchAll();
								}
						    } catch (Exception $ex){
						    	
						    }
						    
						    return $r;							
						
					}
					return true;
				}

				return false;
			}
		}

		catch (Exception $e)
		{
			error_log($e->getMessage());
			throw $e;
			return false;
		}

		return (isset($this::$db) === true) ? $this::$db : false;
	}

	public static function Serve($on = null, $callback = null)
	{


		/*if (isset($_SERVER['REQUEST_METHOD']) !== true)
		{
			$_SERVER['REQUEST_METHOD'] = 'CLI';
		}*/

		parse_str($_SERVER['QUERY_STRING'], $parameters);

		$root = preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
		
		$requestQuery = explode("?", $root);
		
		//error_log($_SERVER["PHP_SELF"]." - ".$_SERVER['SCRIPT_NAME']." - ".$_SERVER['QUERY_STRING']);
		
		if($route === "/" && $root === "/") {
			error_log("PROCESSING ROOT");
			return exit(call_user_func($callback));
		} else if($route === "/") {
			return false;
		} else {
			error_log("PROCESSING ".$route." - ".$root);
		}
		
		$replace = str_replace(array('#any', '#num'), array('[^/]++', '[0-9]++'), $route) . '~i';
	
		//echo '~^' . str_replace(array('#any', '#num'), array('[^/]++', '[0-9]++'), $route) . '~i';
			
		if ($num_match = preg_match('~^' . str_replace(array('#any', '#num'), array('[^/]++', '[0-9]++'), $route) . '~i', $root, $parts) > 0) {	
			ResterUtils::Log("PROCESSING CALLBACK ".$root." - ".$route);
			return (empty($callback) === true) ? true : exit(call_user_func_array($callback, array_slice($parts, 1)));
		} else {
			ResterUtils::Log("NO MATCH ".$root." - ".$route);
		}
		
		return false;
	}
	
	public function getRoot() {
		$root = preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
		return $root;
	}
	
	function getRelations() {
		$relations = $this->Query("select * from information_schema.key_column_usage where table_schema = '".DBNAME."' and referenced_table_name is not null");
		return MySQLRouteRelation::parseRelationsFromMySQL($relations);
	}
	
	function getRouteFields($route, $currentRelations = null) {
		$result = DBController::Query("DESCRIBE ".$route->routeName);
		$routeFields = array();
		
		foreach($result as $f) {
			$routeField = new RouteField();
				
			$routeField->fieldName = $f["Field"];
			$routeField->fieldType = RouteField::getTypeFromMySQL($f["Type"]);
			$routeField->isRequired = ($f["Null"] == "NO") ? true : false;
			$routeField->defaultValue = $f["Default"];
			$routeField->description = $routeField->fieldName." field ".$routeField->fieldType;
			if($f["Extra"] == "auto_increment") {
				$routeField->isAutoIncrement = TRUE;
			}
			
			if(isset($currentRelations)) {
				foreach($currentRelations as $r) {
					if($r->field == $routeField->fieldName && $r->route == $route->routeName) {
						$routeField->setRelation($r);
						ResterUtils::Log("+++ ADD RELATION: ".$route->routeName." >> ".$routeField->fieldType);
					}
					//Inverses
					if($r->destinationRoute == $route->routeName && $r->relationName == $routeField->fieldName && $r->inverse) {
						$routeField->setRelation($r);
						ResterUtils::Log("+++ ADD INVERSE: ".$route->routeName." >> ".$routeField->fieldName);
					}
				}
			}
			
			if($f["Key"] == "PRI") {
				$routeField->isKey = true;
				$route->primaryKey = $routeField;
			}
		
			$routeFields[$routeField->fieldName]=$routeField;
		}
			
		return $routeFields;
	}
	
 	function insertObjectToDB($route, $object) {
		
 		//ResterUtils::Dump($route);
 		
		foreach ($object as $key => $value) {
			//Check for relations on insert
			if(!$route->routeFields[$key]->isRelation) {
				$data[sprintf('%s', $key)] = '?';
				$values[$key] = $value;
			}
		}
		
		$query = sprintf('INSERT INTO "%s" (%s) VALUES (%s)', $route->routeName, implode(', ', array_keys($data)), implode(', ', $data));
		
		return $this->Query($query, $values);
	}
	
	function updateObjectOnDB($route, $objectID, $objectData) {
		$data = array();
		
		foreach ($objectData as $key => $value) {
			$data[$key] = sprintf('`%s` = ?', $key);
		}
		
		$query = array
		(
				sprintf('UPDATE `%s` SET %s WHERE `%s` = \''.$objectID.'\'', $route->routeName, implode(',', $data), $route->primaryKey->fieldName),
		);
		
		$query = sprintf('%s;', implode(' ', $query));
		
		$result = $this->Query($query, $objectData);
			
		return $result;
	}
	
	function getObjectsFromDB($route, $filters, $availableRoutes, $orFilter = false) {
		
		
		$selectFields = array();
		$selectTables = array();
		$joins = array();
		$queryFields = array();
		
		//Order Stuff
		if(isset($filters['count'])) {
			$countOnly=$filters['count'];
			unset($filters['count']);
		}
		
		
		//Order Stuff
		if(isset($filters['order'])) {
			$order['by']=$filters['order'];
			unset($filters['order']);
		}
		if(isset($filters['orderType'])) {
			$order['order']=$filters['orderType'];
			unset($filters['orderType']);
		}
		
		//or Filter
		if(isset($filters['orFilter'])) {
			$orFilter=$filters['orFilter'];
			unset($filters['orFilter']);
		}

		//Return if $route is null, to prevent 500 error
		if($route == null) return $route;		
		
		//Add the main route fields
		$selectFields = array_merge($selectFields, $route->getFieldNames(FALSE, TRUE));
		

		//Process relations
		if(count($route->getRelationFields()) > 0) {
			foreach($route->getRelationFields() as $rf) {
				
				if($rf->relation->inverse && $rf->relation->route != $route->routeName)
					continue;
				
				if($rf->fieldType == "json" && !$rf->relation->inverse) {
					$selectFields[] = $rf->relation->field;
					continue;
				}
					
				$destinationRoute = $availableRoutes[$rf->relation->destinationRoute];
				

				foreach($destinationRoute->getRelationFieldNames($rf->relation) as $fieldKey => $rName) {
						//if(!$rf->relation->inverse || $rf->relation->)
						//Commenting the below if for cases where the foreign key and the reference have same name.
						//if($fieldKey != $rf->relation->field)
							$selectFields[] = $rf->relation->relationName.".".$fieldKey." as ".$rName;
				}
					
				//$selectTables[$rf->relation->destinationRoute] = $rf->relation->relationName;
				//$joins[] = $route->routeName.".".$rf->relation->field." = ".$rf->relation->relationName.".".$rf->relation->destinationField;
				
				$joins[]=$rf;
					
			}
			
		}
		
		
		//Main Route Table
		$selectTables[$route->routeName]=$route->routeName;
		
		
		$fieldFilters = $filters;
		$fieldFilters = $this->cleanReservedFields($fieldFilters);

		//Filters
		//filterfields
		if(count($fieldFilters) > 0) {
			foreach($fieldFilters as $filterField => $filterValue) {
				if(is_array($filterValue)) {
					foreach(array_keys($filterValue) as $queryType) {

					$q = " (".$route->routeName.".".$filterField." ";
					//$queryType = array_keys($filterValue)[0];
					$queryValue = $filterValue[$queryType];
		
					$val = explode(",", $queryValue);
		
					switch($queryType) {
						case "in":
							if(is_array($val) && count($val) > 1) {
								//search mode
								$q.="= '".$val[0]."'";
		
								for($i = 1; $i<count($val); $i++) {
									$q.=" OR ".$route->routeName.".".$filterField." = '".$val[$i]."'";
								}
							} else
								$q.="LIKE '%".$queryValue."%'";
							break;
						case "gt":
							$q.="> ".$queryValue;
							break;
						case "lt":
							$q.="< ".$queryValue;
							break;
						case "ge":
							$q.=">= ".$queryValue;
							break;
						case "le":
							$q.="<= ".$queryValue;
							break;
						default:
							$q.="= '".$queryValue."'";
							break;
					}
					
					$q.= ")";
						
					array_push($queryFields, $q);
					
					}
		
				} else {
					$q = " (".$route->routeName.".".$filterField." ";
		
					$val = explode(",", $filterValue);
		
					//search mode
					$q.="= '".$val[0]."'";
		
					for($i = 1; $i<count($val); $i++) {
						$q.=" OR ".$route->routeName.".".$filterField." = '".$val[$i]."'";
					}
					
					$q.= ")";

					$queryFields[] = $q;
				}
			    
			}
		}
		
		// Construct the query
		
		$query = "SELECT ";
		
		//COUNT ONLY
		if($countOnly){
			$query .= "COUNT(*) as count";
		}
		else{
			$query .= implode(",", $selectFields);
		}
		
		//OLDER WAY WITHOUT COUNT
		//$query .= implode(",", $selectFields);
		
		$query .= " FROM ";
		foreach($selectTables as $t => $ta) {
			$tablesFormatted[] = $t." as ".$ta;
		}
		$query .= implode(",", $tablesFormatted);
		
		
		//Process joins
		if(count($joins) > 0) {
			foreach($joins as $join) {
				$query .= " LEFT JOIN ".$join->relation->destinationRoute." as ".$join->relation->relationName." ON (".$join->relation->route.".".$join->relation->field." = ".$join->relation->relationName.".".$join->relation->destinationField.")";
			}
			//$query.=" ( ".implode(" AND ", $joins)." ) ";
		}
		
		
		if(count($queryFields) > 0) {
			$query.= " WHERE ";
		}
		
		
		
		if(count($queryFields) > 0) {
			if($orFilter){
				$query.= implode(" OR ", $queryFields);
			}
			else{
				$query.= implode(" AND ", $queryFields);
			}
		}
		
		if (isset($order['by']) === true)
		{
			if (isset($order['order']) !== true)
			{
				$order['order'] = 'ASC';
			}
		
			$query .= sprintf(' ORDER BY "%s" %s', $order['by'], $order['order']);
		}
		
		if (isset($filters['limit']) === true)
		{
			$query .= sprintf(' LIMIT %u', $filters['limit']);
		
			if (isset($filters['offset']) === true)
			{
				$query .= sprintf(' OFFSET %u', $filters['offset']);
			}
		} else { //Default limit
			if(!$countOnly){
				$query .= " LIMIT 1000";
			}
		}
		
		
		return $this->Query($query);
	}
	
	function getRoutes() {
		$routes = ApiCacheManager::getValueFromCache(ROUTE_CACHE_KEY);
		if($routes == NULL)
			$routes = $this->getRoutesFromDB();
			
		//Add the auth routes and remove the not desired ones	
		unset($routes["oauth_consumer_registry"]);
		unset($routes["oauth_consumer_token"]);
		unset($routes["oauth_log"]);
		unset($routes["oauth_server_nonce"]);
		unset($routes["oauth_server_registry"]);
		unset($routes["oauth_server_token"]);
		
		//We create a virtual route called auth to add the auth methods
		if(ENABLE_OAUTH == true){
			$authRoute = new Route();
			$authRoute->routeName = "auth";
			$routes["auth"]=$authRoute;
		}
		
		return $routes;
	}
	
	function getRoutesFromDB() {
		$relations = array();
		
		$relations = $this->getRelations();
		
		$json_relations = JSONRouteRelation::getJSONRelations();
		
		if(count($json_relations) > 0) {
		 	foreach($json_relations as $route => $relation) {
		 		if(isset($relations[$route]))
		 			$relations[$route]=array_merge($relations[$route], $relation);
		 		else 
		 			$relations[$route]=$relation;
		 	}
		}
		
		$result = DBController::Query("SHOW TABLES");
	
		if ($result === false) {
			exit(ApiResponse::errorResponse(404));
		} else if (empty($result) === true) {
			return array();
			//exit(ApiResponse::errorResponse(204));
		} else {
			foreach($result as $k => $v) {
				$route = reset($v);
				
				$route = new Route();
				$route->routeName = reset($v);
				if(isset($relations[$route->routeName]))
					$route->routeFields = $this->getRouteFields($route, $relations[$route->routeName]);
				else
					$route->routeFields = $this->getRouteFields($route);
				
				ResterUtils::Log("*** PRIMARY KEY: ".$route->routeName." => ".$route->primaryKey->fieldName);
				
				$routes[$route->routeName]=$route;
			}
		}
		
		ApiCacheManager::saveValueToCache(ROUTE_CACHE_KEY, $routes);
		
		return $routes;
	}
	
	function getReservedFields() {
		return array("order", "limit", "orderType", "offset", "orFilter");
	}
	
	function cleanReservedFields($fieldsMap) {
	
		foreach($this->getReservedFields() as $reserved) {
			if(array_key_exists($reserved, $fieldsMap)) {
				unset($fieldsMap[$reserved]);
			}
		}
	
		return $fieldsMap;
	}
	
}

?>
