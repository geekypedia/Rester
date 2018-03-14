<?php

require_once('config.php');
require_once(__DIR__.'/ResterUtils.php');
require_once(__DIR__.'/UUID.php');
require_once(__DIR__.'/model/RouteFileProcessor.php');
require_once('OAuthServer.php');
require_once('OAuthStore.php');


class ResterController {

	var $routes = array();
	
	var $customRoutes = array();
	
	var $dbController;
	
	var $requestProcessors = array();
	
	/**
	* Indexed array containing the methods don't checked by OAuth
	*/
	var $publicMethods;
	
	function ResterController() {
		$this->dbController = new DBController();
		$this->checkConnectionStatus();
		
		//Internal processors
		
		$this->addRequestProcessor("OPTIONS", function($routeName = NULL, $routePath = NULL, $parameters = NULL) {
			if(isset($routeName) && isset($routePath)) {
				$this->doResponse(SwaggerHelper::getDocFromRoute($this->getAvailableRoutes()[$routePath[0]], $this->getAvailableRoutes()));
			}
			$this->doResponse(NULL);
		});
		
		$this->addRequestProcessor("HEAD", function($routeName = NULL, $routePath = NULL, $parameters = NULL) {
			$this->showResult("");
		});
		
		/**
		* This is the main GET processor
		* If the request does not have a route, shows the doc for swagger
		* If we have a route and no operation or parameters, show the doc for swagger (route specific)
		* Else we process the parameters, checks if we have a command or an ID to return the results 
		*/
		$this->addRequestProcessor("GET", function($routeName = NULL, $routePath = NULL, $parameters = NULL) {
			//If we ask for the root, give the docs
			if($routeName == NULL) {
				$this->showRoutes();
			}
			
			if(isset($routeName) && $routeName == "api-doc" && isset($routePath)) {
				ResterUtils::Log("Returning apidoc");
				$this->doResponse(SwaggerHelper::getDocFromRoute($this->getAvailableRoutes()[$routePath[0]], $this->getAvailableRoutes()));
			}
			
			$this->checkRouteExists($routeName);
			
		
			if(count($routePath) >= 1) {
				$command = $routePath[0];
				
				if(isset($this->customRoutes["GET"][$routeName][$command])) {
					$callback = $this->customRoutes["GET"][$routeName][$command];
					call_user_func($callback, $parameters);
				} else {
					$result = array_shift($this->getObjectByID($routeName, $command));
					$this->showResult($result);
				}								
			} else {
				if(isset($parameters)){
					$result = $this->getObjectsFromRoute($this->routes[$routeName], $parameters);
				} else
					$result = $this->getObjectsFromRoute($this->routes[$routeName]);
				//show result forcing array result
				$this->showResult($result, true);
			}
		});
		
		$this->addRequestProcessor("POST", function($routeName = NULL, $routePath = NULL, $parameters = NULL) {
			
			if(!isset($routeName)) {
				$this->showError(400);
			}
			
			$body = $this->getPostData();

			//Check for command
			if(count($routePath) == 1) {
				$command = $routePath[0];
				if(isset($this->customRoutes["POST"][$routeName][$command])) {
					ResterUtils::Log(">> Executing custom command <".$command.">");
					$callback = $this->customRoutes["POST"][$routeName][$command];
					call_user_func($callback, $body);
					return;
				} else { //tenemos un id; hacemos un update
					ResterUtils::Log(">> Update object from ID");
					$this->processFiles($this->getAvailableRoutes()[$routeName], $routePath[0]);
					$result = $this->updateObjectFromRoute($routeName, $routePath[0], $_POST);
					$this->showResult($result);
				}
			}
			
			if($body == NULL) {
				//not postbody and no post data... we create something...
				ResterUtils::Log(">> CREATING BAREBONE 8======8");
				$barebone = array();
				$result = $this->insertObject($routeName, $barebone); //give all the post data	
			} else {
				//Create object from postbody
				ResterUtils::Log(">> CREATING OBJECT FROM POSTBODY: *CREATE* - ".$routeName);
				//ResterUtils::Dump($body);
					
				$route = $this->getAvailableRoutes()[$routeName];
					
				$existing = $this->getObjectByID($routeName, $body[$route->primaryKey->fieldName]);
					
				if($existing) { //we got an id, let's update the values
					$result = $this->updateObjectFromRoute($routeName, $body[$route->primaryKey->fieldName], $body);
				} else {
					$result = $this->insertObject($routeName, $body);
				}
			}
				
			$this->showResult($result, 201);
			
		});
		
		$this->addRequestProcessor("DELETE", function($routeName, $routePath) {
		
			if(!isset($routeName)) {
				$this->showError(400);
			}
			
			if(!isset($routePath) || count($routePath) < 1) {
				$this->showError(404);
			}
			
			$result = $this->deleteObjectFromRoute($routeName, $routePath[0]);
			
			if($result > 0) {
				$this->showResult(ApiResponse::successResponse());
			} else {
				$this->showResult($result);
			}
		
		});
		
		$this->addRequestProcessor("PUT", function($routeName, $routePath) {
		
			ResterUtils::Log("PROCESSING PUT");
			if(!isset($routeName)) {
				$this->showError(400);
			}
			
			$input = file_get_contents('php://input');
			
			if(empty($input)) {
				ResterUtils::Log("Empty PUT request");
				$this->showError(400);
			}
			
			if(!isset($routePath) || count($routePath) < 1) { //no id in URL, we expect json body
			
				$putData = json_decode($input, true);
				$route = $this->getAvailableRoutes()[$routeName];
				if(is_array($putData) && ResterUtils::isIndexed($putData) && count($putData) > 0) { //iterate on elements and try to update
					ResterUtils::Log("UPDATING MULTIPLE OBJECTS");
					foreach($putData as $updateObject) {
						ResterUtils::Log("UPDATING OBJECT ".$routeName." ID: ".$updateObject["id"]);
						if(isset($updateObject[$route->primaryKey->fieldName])) {
							$result = $this->updateObjectFromRoute($routeName, $updateObject[$route->primaryKey->fieldName], $updateObject);
						}
					}
					ResterUtils::Log("SUCCESS");
					$this->doResponse(ApiResponse::successResponse());
				} else {
					ResterUtils::Log("UPDATING SINGLE OBJECT");
					if(!isset($putData[$route->primaryKey->fieldName])) {
						ResterUtils::Log("No PRIMARY KEY FIELD ".$input);
						echo $route->primaryKey->fieldName;
						$this->showError(400);
					}	 
					$result = $this->updateObjectFromRoute($routeName, $putData[$route->primaryKey->fieldName], $putData);
					$this->showResult($result);
				}
			} else { //id from URL
		
				//parse_str($input, $putData);
				//$putData = json_decode($input, true);
				
				parse_str($input, $putData);
			
			
					ResterUtils::Log("IS INDEXED");
					$result = $this->updateObjectFromRoute($routeName, $routePath[0], $putData);
					$this->showResult($result);
		
			}
					
			if($result > 0) {
				$this->doResponse(ApiResponse::successResponse());
			} else {
				$this->showResult($result);
			}
		
		});
	}
	
	/**
	 * Tries to get the post data of a request. Null if no post data is given
	 */
	function getPostData() {
		
		$body = $this->getRequestBody();
		if($body != NULL && is_array($body)) {
		  return $body;	
		} if (empty($_POST) === true) { //if empty, create a barebone object
			return NULL;
		} else if (is_array($_POST) === true) { 
			return $_POST;
		}
		
		return NULL;
	}

	/**
	 * This function checks if the request is CORS valid, if not checks for an authentication and setup the auth routes
	 */
	function checkOAuth() {
		
		global $validOrigins;
		
		if(isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $validOrigins)) {
			return;
		}

		//Command to generate the Request Tokens
		$this->addRouteCommand(new RouteCommand("POST", "auth", "requestToken", function($params = NULL) {
		
			if(empty($_POST["userId"])) {
				$this->showError(400);
			}
			
			$store = OAuthStore::instance('PDO', array('conn' => DBController::$db));
		
			$key = $store->updateConsumer($_POST, $_POST["userId"], true);
			$c = $store->getConsumer($key, $_POST["userId"]);
			
			$result["key"]=$c["consumer_key"];
			$result["secret"]=$c["consumer_secret"];
			
			$this->showResult($result);
			
		}, array("userId"), "Request a new token"));
		
		
		// Create a new instance of OAuthStore and OAuthServer
		$store = OAuthStore::instance('PDO', array('conn' => DBController::$db));
		$server = new OAuthServer();
		
		ResterUtils::Log(">> CHECKING OAUTH ".$_SERVER['REQUEST_METHOD']);
		
		if (OAuthRequestVerifier::requestIsSigned()) {
		
			//If the request is signed, allow from any source
			header('Access-Control-Allow-Origin: *');
		
			try {
				$req = new OAuthRequestVerifier();
				$id = $req->verify(false);
				ResterUtils::Log("*** API USER ".$id." ***");
			}  catch (OAuthException2 $e)  {
				// The request was signed, but failed verification
				header('HTTP/1.1 401 Unauthorized');
				header('WWW-Authenticate: OAuth realm=""');
				header('Content-Type: text/plain; charset=utf8');
				ResterUtils::Log(">> OAUTH ERROR >> ".$e->getMessage());
				
				$this->showErrorWithMessage(401, 'Unauthorized. Signed but Failed Verification');
				exit();
			}	
		} else {
				
				ResterUtils::Log(">> OAUTH: Unsigned request");
				if(isset($validOrigins)) {
					foreach($validOrigins as $origin) {
						ResterUtils::Log(">> ADD ORIGIN: ".$origin);
						header('Access-Control-Allow-Origin: '.$origin);
					}
				} else {
					//TODO; CHECK ORIGIN
					header('HTTP/1.1 401 Unauthorized');
					header('WWW-Authenticate: OAuth realm=""');
					header('Content-Type: text/plain; charset=utf8');
					//echo "Authentication error";

					ResterUtils::Log(">> OAUTH ERROR >> Request not signed");
					ResterUtils::Log("*** AUTH ERROR *** ===>");
					
					$this->showErrorWithMessage(401, 'Unauthorized. Request not signed. Please provide oauth_signature');					
					exit();
				}
			//$this->showError(401);
		}
    }
	
    /**
     * Parses the request body and decodes the json
     * @return object json parsed object
     */
	function getRequestBody() {
		$requestBody = @file_get_contents('php://input');
			
		if(empty($requestBody)) {
			ResterUtils::Dump("ERR: Empty request body");
			return NULL;
		} 
		
	
		ResterUtils::Dump($requestBody, "*** REQUEST BODY ***");
		
		return json_decode($requestBody, true);
	}
	
	/**
	 * Check if route is parsed
	 * @param string $routeName
	 * @return boolean true if route exists
	 */
	function checkRouteExists($routeName) {
		if(!isset($this->getAvailableRoutes()[$routeName])) {
				//Check for Custom Routes
				if($this->customRoutes["GET"][$routeName] || $this->customRoutes["POST"][$routeName]){
					return true;
				}

				$this->showError(404);
				return false;
		}
		return true;
	}
	
	function addFileProcessor($routeName, $fieldName, $acceptedTypes = NULL) {
		if(isset($this->getAvailableRoutes()[$routeName])) {
			$this->getAvailableRoutes()[$routeName]->addFileProcessor($fieldName);	
		} else
			die("Can't add file processor ".$fieldName." to route ".$routeName);
	}
	
	function addPublicMethod($requestMethod, $routeName) {
		$this->publicMethods[$requestMethod][]=$routeName;
	}
	
	function addRouteCommand($routeCommand) {
		
		ResterUtils::Log("+++ ADDING ROUTE COMMAND: ".$routeCommand->method." - ".$routeCommand->routeName." - ".$routeCommand->routeCommand);
		
		if($routeCommand->method == "DELETE" || $routeCommand->method == "PUT") {
			exit($routeCommand->method." is not supported on custom commands. Use GET or POST instead");
		}
	
		$routes = $this->getAvailableRoutes();
		//if(isset($routes[$routeCommand->routeName])) {
			$this->customRoutes[$routeCommand->method][$routeCommand->routeName][$routeCommand->routeCommand]=$routeCommand->callback;
			
			if(!isset($routes[$routeCommand->routeName]))
				$routes[$routeCommand->routeName]=NULL;
			
			$route = $routes[$routeCommand->routeName];
			$route->routeCommands[$routeCommand->routeCommand]=$routeCommand;
		//}
		
		
	}
	
	function checkClientRestriction() {
		if ((empty($clients) !== true) && (in_array($_SERVER['REMOTE_ADDR'], (array) $clients) !== true))
		{
			exit($this->dbController->Reply(ApiResponse::errorResponse(403)));
		}
	}
	
	function addRequestProcessor($requestMethod, $callback) {
		$this->requestProcessors[$requestMethod][]=$callback;
	}
		
	/**
	* Add CORS headers or check for OAuth authentication
	*/
	function checkAuthentication() {
		global $validOrigins;
		
		if(isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $validOrigins)) {
			ResterUtils::Log(">> ADD ORIGIN: ".$_SERVER['HTTP_ORIGIN']);
			header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
		}
	
		if(!defined('ENABLE_OAUTH') || !ENABLE_OAUTH)
			return;
	
		if(!isset($this->publicMethods[$_SERVER['REQUEST_METHOD']])) {
			if($requestMethod !== "OPTIONS")
				$this->checkOAuth();
		} else {
			$publicRoutes = $this->publicMethods[$_SERVER['REQUEST_METHOD']];
			if(!in_array($this->getRoutePath(), $publicRoutes) && !in_array($this->getCurrentRoute(), $publicRoutes))
				$this->checkOAuth();
			else
				ResterUtils::Log("*** PUBLIC ROUTE ==> ".$this->getRoutePath());
		}
	}
		
	function processRequest($requestMethod) {
	
		ResterUtils::Log("*** BEGIN PROCESSING REQUEST ".$requestMethod." *** ==> ".$this->getRoutePath());
		
		$this->checkAuthentication();
		
		if(isset($this->requestProcessors[$requestMethod])) {
			foreach($this->requestProcessors[$requestMethod] as $callback) {
			
				ResterUtils::Log(">> Found processor callback");
			
				$callbackParameters = array();
				
				if($this->getCurrentRoute() && $this->getCurrentRoute() != "/") {
					$callbackParameters[0] = $this->getCurrentRoute();
					ResterUtils::Log(">> Processing route /".$this->getCurrentRoute());
					if(count($this->getCurrentPath()) > 0) {
						$callbackParameters[1]=$this->getCurrentPath();
						ResterUtils::Log(">> Processing command ".implode("/",$this->getCurrentPath()));
					} else {
						$callbackParameters[1] = NULL;
					}
					
					if($requestMethod == "GET")
						parse_str($_SERVER['QUERY_STRING'], $requestParameters);
					else if($requestMethod == "POST")
						$requestParameters = $_POST;
						
					if(isset($requestParameters)) {
						$callbackParameters[2] = $requestParameters;
						ResterUtils::Log(">> PARAMETERS: ".http_build_query($requestParameters));
						ResterUtils::Dump($requestParameters);
					}
				}
								
				try {
					if(isset($callbackParameters) && count($callbackParameters) > 0) {
						call_user_func_array($callback, $callbackParameters);
					} else {
						call_user_func($callback);
					}
				} catch(Exception $e) {
					return false;
				}
			}
		} else {
			ResterUtils::Log("*** ERROR *** Request processor not set ".$requestMethod);
			$this->showError(405);
		}
	}
	
	//TODO
	function checkConnectionStatus() {
		if(is_null(DBController::$db))
                {
                        $this->showErrorWithMessage(503, "Could not connect to the database. Please check your configurations!");
                }
		/*if ($this->dbController->Query(DSN) === false) {
			exit($this->dbController->Reply(ApiResponse::errorResponseWithMessage(503, "Error connecting to SQL")));	
		}*/
	}
	
	/*************************************/
	/* OBJECT MANAGEMENT METHODS
	/*************************************/	
	function insertObject($routeName, $objectData) {

		$route = $this->getAvailableRoutes()[$routeName];
	
		$routeFieldNames = $route->getFieldNames(TRUE);
		
		if(in_array("uuid", $routeFieldNames)) {
			$objectData["uuid"] = UUID::v4();
		} 
			
		if(in_array("lastmoddate", $routeFieldNames)) {
			$objectData["lastmoddate"] = time() * 1000;
		}
			
		if(in_array("createddate", $routeFieldNames)) {
			$objectData["createddate"] = time() * 1000;
		}
			
		//Set the object ID
		$insertID = $route->getInsertIDFromObject($objectData);
		$objectData[$route->primaryKey->fieldName] = $insertID;
	
		//Insert the object into database
		$result = $this->dbController->insertObjectToDB($route, $objectData);

		if($result == 0) { //No insert id
			$result = $insertID;
		}
		
		ResterUtils::Log("RESULT: **** ".$result);
	
		//If we have files on the object, process them
		$this->processFilesWithID($route, $result);
		
		$this->processInsertRelations($route, $objectData);
		
		//Get the object model by ID
		$object = $this->getObjectByID($routeName,$result);
		
		if(is_array($object) && ResterUtils::isIndexed($object)) {
			$object=$object[0];
		}
		
		
		
		return $object;
	}
	
	private function processInsertRelations($route, $objectData) {

		ResterUtils::Log("--- ROUTE FIELDS ---");
		//ResterUtils::Dump($route->routeFields);
		
		foreach ($objectData as $key => $value) {
			//Check for relations on insert
			if($route->routeFields[$key]->isRelation) {
				
				if($route->routeFields[$key]->fieldType == "json") {
					$relation = $route->routeFields[$key]->relation;
					
					$destinationRoute = $this->getRoute($relation->destinationRoute);
				
					$objectID = $value[$destinationRoute->primaryKey->fieldName];
				
					$value[$relation->relationName]=json_encode($value[$relation->relationName]);
				
					$this->updateObjectFromRoute($destinationRoute->routeName, $objectID, $value);
					
					$this->updateObjectFromRoute($route->routeName, $objectData[$route->primaryKey->fieldName], array($relation->field => $objectID));
				}
			} 
		}
	}
	
	/**
	 * This method looks for uploaded files. If we have, get the ID of the object, and update the database according the upload field
	 * @param Route $route
	 * @param object $objectID
	 */
	private function processFilesWithID($route, $objectID) {
		//Process files
		if(count($_FILES) > 0) { //we got files... process them
			foreach($_FILES as $fileField => $f) {
				if($route->getFileProcessor($fileField) != NULL) { //We have to process
					$processor = $route->getFileProcessor($fileField);
					$upload = $processor->saveUploadFile($objectID, $route->routeName, $f);
					$newData = array ($route->primaryKey->fieldName => $objectID, $fileField => $upload["destination"]);
					$this->updateObjectFromRoute($route->routeName, $objectID, $newData);
				}
			}
		}
	}
	
	function getObjectsFromRouteName($routeName, $filters = NULL, $orFilter = false) {
		return $this->getObjectsFromRoute($this->getAvailableRoutes()[$routeName], $filters, $orFilter);
	}
	
	function getObjectsFromRoute($route, $filters = NULL, $orFilter = false) {
	
		
		
		$result = $this->dbController->getObjectsFromDB($route, $filters, $this->getAvailableRoutes(), $orFilter);
		
		
		
		/*if(count($route->getRelationFields(TRUE)) > 0) {
		
			foreach($route->getRelationFields() as $rf) {
				
				$relationClass = get_class($rf);
				
			
						$destinationRoute = $this->getAvailableRoutes()[$rf->relation->destinationRoute];
						
						foreach($destinationRoute->getRelationFieldNames($rf->relation) as $fieldKey => $rName) {
							$relationFieldNames[] = $rf->relation->relationName.".".$fieldKey." as ".$rName;
						}
				
			}
		}
		
		$query[] = "SELECT ";
		
		$query[] = implode(",", $route->getFieldNames(FALSE, TRUE));
		
		if(isset($relationFieldNames)) {
			$query[] = ",";
			$query[] = implode(",",$relationFieldNames);
		}
		
		$query[] = " FROM `".$route->routeName."` as ".$route->routeName;
		
		//$query = array(sprintf('SELECT * FROM "%s"', $route->routeName));
		
		if(count($route->getRelationFields(TRUE)) > 0) {
			foreach($route->getRelationFields() as $relationField) {
				$query[] = ",".$relationField->relation->destinationRoute." as ".$relationField->relation->relationName;
			}
		}

		$i = 0;
		
		if(isset($fieldFilters)) {
			
			$closeBracket = false;
			
			foreach($fieldFilters as $filterField => $filterValue) {
			
				if($i == 0) {
					$q = "WHERE ("; 
					$closeBracket = true;
				} else {
					if($orFilter) {
						$q = "OR";
					} else {
						$q = "AND";
					}
				}
				
				$q .= " (".$route->routeName.".".$filterField." ";
						
				if(is_array($filterValue)) {
					$queryType = array_keys($filterValue)[0];
					$queryValue =$filterValue[$queryType];	
					
					$val = explode(",", $queryValue);
							
					switch($queryType) {
						case "in":
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
					
				} else {
	
					$val = explode(",", $filterValue);
	
					//search mode
					$q.="= '".$val[0]."'";
					
					for($i = 1; $i<count($val); $i++) {
						$q.=" OR ".$route->routeName.".".$filterField." = '".$val[$i]."'";	
					}
				}
				
				$q.= ")";				
				
				$query[] = $q;
				
				$i++;
			}
		}
		
		if($closeBracket)
			$query[] = ")";
		
		//JOINS
		if(count($route->getRelationFields(TRUE)) > 0) { //get relation fields, skipping non joinable
			if(!isset($fieldFilters) || count($fieldFilters) == 0) {
				$query[] = " WHERE ";
			} else {
				$query[] = " AND ";
			}
			$query [] = "(";
			$i = 0;
			foreach($route->getRelationFields() as $relationField) {
					if($i > 0)
						$query[] = "AND";
					$query[] = " ".$route->routeName.".".$relationField->relation->field." = ".$relationField->relation->relationName.".".$relationField->relation->destinationField." ";
					$i++;
				
			}
			$query [] = ")";
		}
		
		if (isset($order['by']) === true)
		{
			if (isset($order['order']) !== true)
			{
				$order['order'] = 'ASC';
			}

			$query[] = sprintf('ORDER BY "%s" %s', $order['by'], $order['order']);
		}

		if (isset($filters['limit']) === true)
		{
			$query[] = sprintf('LIMIT %u', $filters['limit']);

			if (isset($filters['offset']) === true)
			{
				$query[] = sprintf('OFFSET %u', $filters['offset']);
			}
		} else { //Default limit
			$query[] = "LIMIT 1000";
		}
		
		$query = sprintf('%s;', implode(' ', $query));
		
		$result = $this->dbController->Query($query);*/
		
		$response = $this->processRawObjects($route, $result);
		
		if(!isset($response)) {
			return NULL;
		}
		return $response;
	}
	
	function processRawObjects($route, $rawObjects) {
		
		foreach($rawObjects as $row) { //Iterate over rows of results
			//ResterUtils::Dump($row);
			//Clean array values
			$mainObject = ResterUtils::cleanArray($row, $route->getFieldNames(FALSE, FALSE));
		
			//Map objects to it's correct types
			$mainObject = $route->mapObjectTypes($mainObject);
						
			if(count($route->getRelationFields()) > 0) {
				foreach($route->getRelationFields() as $rf) {				
					if(($rf->relation->inverse && $route->routeName == $rf->relation->destinationRoute) 
					 || ($rf->fieldType == "json" && !$rf->relation->inverse)) {
						
						$jsonObject = json_decode($row[$rf->relation->relationName]);
						
						if(!$jsonObject)
							$jsonObject=array();
						
						$mainObject[$rf->relation->relationName]=$jsonObject;
						
					} else {
						$destinationRoute = $this->getAvailableRoutes()[$rf->relation->destinationRoute];
			
						$relationObject = array();
					
						foreach($destinationRoute->getRelationFieldNames($rf->relation) as $fieldKey => $rName) {
							$relationObject[$fieldKey]=$row[$rName];
						}
						
						$relationObject = $destinationRoute->mapObjectTypes($relationObject);

						$relationFieldName = str_replace("_id","",$rf->relation->field);
						$relationFieldName = str_replace("id","",$relationFieldName);
						$mainObject[$relationFieldName]=$relationObject;
						
						//$mainObject[$rf->relation->destinationRoute]=$relationObject;

					}
				}
			}
				
			$response[]=$mainObject;
		}
		if(isset($response))
			return $response;
		
		return NULL;
	}
	
	function query($query) {
		return $this->dbController->Query($query);
	}
		
	function getObjectByID($routeName, $ID) {

		$route = $this->getAvailableRoutes()[$routeName];
		
		if(is_array($ID)) {
			$ID=implode(",", $ID);
		}
		
		$filter = array($route->primaryKey->fieldName => $ID);
			
		$result = $this->getObjectsFromRoute($route, $filter);
			
		return $result;	
	}
	
	function deleteObjectFromRoute($routeName, $ID) {
		$query = array(
			sprintf('DELETE FROM "%s" WHERE "%s" = ?', $routeName, 'id')
		);

		$query = sprintf('%s;', implode(' ', $query));
	
		$result = $this->dbController->Query($query, $ID);

		return $result;
	}
	
	function updateObjectFromRoute($routeName, $objectID, $newData) {
		ResterUtils::Log("UPDATING OBJECT");
		if (empty($newData) === true) {
			$this->showError(204);
		}
		
		$currentRoute = $this->getAvailableRoutes()[$routeName];
		
		if(is_array($newData) === true) {
			$this->dbController->updateObjectOnDB($currentRoute, $objectID, $newData);
			return $this->getObjectByID($routeName, $objectID);
		} else {
			$this->showError(500);
		}
	}
	
	function processFiles($route, $objectID) {
			//Process files
		if(count($_FILES) > 0) { //we got files... process them
			foreach($_FILES as $fileField => $f) {
				if($route->getFileProcessor($fileField) != NULL) { //We have to process
					$processor = $route->getFileProcessor($fileField);
					$upload = $processor->saveUploadFile($objectID, $route->routeName, $f);
					$newData = array ($route->primaryKey->fieldName => $objectID, $fileField => $upload["destination"]);
					$this->updateObjectFromRoute($route->routeName, $objectID, $newData);
				}
			}
		}
	}
	
	/*************************************/
	/* RETURN DATA FUNCTIONS
	/*************************************/
	
	function showError($errorNumber) {
		$result = ApiResponse::errorResponse($errorNumber);
		exit($this->doResponse($result));
	}
	
	function showErrorWithMessage($errorNumber, $message) {
		$result = ApiResponse::errorResponseWithMessage($errorNumber, $message);
		exit($this->doResponse($result));
	}

	
	function showResult($result, $forceArray = false) {
	
		ResterUtils::Log("*** DISPLAY RESULT TO API ***");
	
		if ($result === false || count($result) == 0) {
			$this->showError(404);
		} else if (empty($result) === true) {
			$this->showError(204);
		} else if($result === true || (is_int($result) && $result >= 1) ) {
			$this->doResponse(ApiResponse::successResponse());
		} else {
			if(is_array($result) && count($result) == 1 && !$forceArray && ResterUtils::isIndexed($result)) {
				$this->doResponse($result[0]);
			} else
				$this->doResponse($result);
		}
	}
	
	private function doResponse($data, $responseCode = 200) {
	
		if(isset($data["error"])) {
			ResterUtils::Log(">> Error Response: ".$data["error"]["code"]." ".$data["error"]["status"]." - ".$this->getCurrentRoute());
			header("HTTP/1.1 ".$data["error"]["code"]." ".$data["error"]["status"], true, $data["error"]["code"]);
		}
	
		if($responseCode != 200) {
			switch($responseCode) {
				case 201:
					header("HTTP/1.1 ".$responseCode." Created");
				break;
			}
		}
	
		$bitmask = 0;
		$options = array('UNESCAPED_SLASHES', 'UNESCAPED_UNICODE');

		if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) === true)
		{
			$options[] = 'PRETTY_PRINT';
		}

		foreach ($options as $option)
		{
			$bitmask |= (defined('JSON_' . $option) === true) ? constant('JSON_' . $option) : 0;
		}

		if (($result = json_encode($data, $bitmask)) !== false)
		{
			$callback = null;

			if (array_key_exists('callback', $_GET) === true)
			{
				$callback = trim(preg_replace('~[^[:alnum:]\[\]_.]~', '', $_GET['callback']));

				if (empty($callback) !== true)
				{
					$result = sprintf('%s(%s);', $callback, $result);
				}
			}

			if (headers_sent() !== true)
			{
				header(sprintf('Content-Type: application/%s; charset=utf-8', (empty($callback) === true) ? 'json' : 'javascript'));
			}
		}

		//ResterUtils::Dump($result);
		
		exit($result);
	}
	
	function showRoutes() {
		$routes = $this->getAvailableRoutes();
		$result = SwaggerHelper::routeResume($routes);
		$this->doResponse($result);
	}
	
	/*************************************/
	/* ROUTE PARSING METHODS
	/*************************************/
	
	public function getRoot() {
		$root = preg_replace('~/++~', '/', substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME'])) . '/');
		return $root;
	}
	
	public function getCurrentRoute() {
		$routePath = array_filter(explode("/", $this->getRoot()));
		if(count($routePath) > 0)
			return array_values($routePath)[0];
		else
			return false;
	}
	
	public function getCurrentPath() {
		return array_values(array_filter(array_slice(explode("/", $this->getRoot()), 2)));
	}
	
	public function getRoutePath() {
		if(count($this->getCurrentPath()) > 0)
			return $this->getCurrentRoute()."/".implode("/",$this->getCurrentPath());
		else
			return $this->getCurrentRoute();
	}
	
	/**
	* Search the tables of the DB and configures the routes
	*/
	function getAvailableRoutes() {
		if($this->routes == NULL) {
			$this->routes = $this->dbController->getRoutes();
		}
		return $this->routes;
	}
	
	function getRoute($routeName) {
		$routes = $this->getAvailableRoutes();
		if(isset($routes[$routeName]))
			return $routes[$routeName];
			
		return NULL;
	}
	
	function getColumnsFromTable($table) {
		$result = $this->dbController->Query("DESCRIBE ".$table);
		return $result;
	}
	
	
	
	/**
 * Parse raw HTTP request data
 *
 * Pass in $a_data as an array. This is done by reference to avoid copying
 * the data around too much.
 *
 * Any files found in the request will be added by their field name to the
 * $data['files'] array.
 *
 * @param   array  Empty array to fill with data
 * @return  array  Associative array of request data
 */
	function parse_raw_http_request($input, array &$a_data)
{
   
  // grab multipart boundary from content type header
  preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
   
  // content type is probably regular form-encoded
  if (!count($matches))
  {
    // we expect regular puts to containt a query string containing data
    parse_str(urldecode($input), $a_data);
    return $a_data;
  }
   
  $boundary = $matches[1];
 
  // split content by boundary and get rid of last -- element
  $a_blocks = preg_split("/-+$boundary/", $input);
  array_pop($a_blocks);
  
  // loop data blocks
  foreach ($a_blocks as $id => $block)
  {
    if (empty($block))
      continue;
     
    // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
     
    // parse uploaded files
    if (strpos($block, 'application/octet-stream') !== FALSE)
    {
      // match "name", then everything after "stream" (optional) except for prepending newlines
      preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
      $a_data['files'][$matches[1]] = $matches[2];
    }
    // parse all other fields
    else
    {
      // match "name" and optional value in between newline sequences
      preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
      $a_data[$matches[1]] = $matches[2];
    }
  }
}

	
} //END

?>
