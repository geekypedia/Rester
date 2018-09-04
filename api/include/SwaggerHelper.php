<?php

require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/DBController.php');

class SwaggerHelper {
	
	
	public static function getDocFromRoute($route, $allRoutes, $custom = false) {
	
		if($custom == false){
			//Without parameter
			$apiCREATE["path"]="/".$route->routeName;
			$apiCREATE["operations"][]=SwaggerHelper::createOperation("GET", $route, SwaggerHelper::getParametersFromRoute($route, "GET", NULL, true), $route->routeName);
			$apiCREATE["operations"][]=SwaggerHelper::createOperation("POST", $route, SwaggerHelper::getParametersFromRoute($route, "POST"), $route->routeName);
			$apiCREATE["operations"][]=SwaggerHelper::createOperation("PUT", $route, SwaggerHelper::getParametersFromRoute($route, "PUT"), $route->routeName);
			
			$apiID["path"] = "/".$route->routeName."/{".$route->routeName."Id}";
			$apiID["operations"][]=SwaggerHelper::createOperation("GET", $route, SwaggerHelper::getParametersFromRoute($route, "GET", "id"), $route->routeName);
			$apiID["operations"][]=SwaggerHelper::createOperation("PUT", $route, SwaggerHelper::getParametersFromRoute($route, "PUT", "id"), "void");	
			$apiID["operations"][]=SwaggerHelper::createOperation("DELETE", $route, SwaggerHelper::getParametersFromRoute($route, "DELETE", "id"), "void");
					
			/*$apiLIST["path"] = "/".$route->routeName."/list";
			$apiLIST["operations"][]=SwaggerHelper::createOperation("GET", $route, SwaggerHelper::getParametersFromRoute($route, "GET", "list"), "array[".$route->routeName."]");*/
			
			$apis = array($apiCREATE, $apiID);
		}
		else{
			$apis = array();
		}

		foreach($route->routeCommands as $command) {
			$apiCommand = array();
			$apiCommand["path"] = "/".$route->routeName."/".$command->routeCommand;
			$apiCommand["operations"][]=SwaggerHelper::createOperation($command->method, $route, SwaggerHelper::getParametersFromCommand($command), $route->routeName, $custom, $command->description);
			$apis[] = $apiCommand;
		}
		
		$result = array
		(
			'apiVersion' => API_VERSION,
			'apis' => $apis,
			'resourcePath' => "/".$route->routeName,
			//'basePath' => 'http://'.$_SERVER['HTTP_HOST'].DBController::getRoot()
			'swaggerVersion' => "1.2",
			'produces' => array('application/json')
		);
		
		if(isset($allRoutes)) {
			$result['models']=SwaggerHelper::getModelsFromRoutes($allRoutes);
		}
		
		return $result;

	}
	
	public static function getPathFromOperation($path, $operations, $returnType) {
		$api["path"] = $path;
		$api["operations"] = $operations;
	}
	
	public static function getParametersFromCommand($command, $queryString = false) {


		if(isset($command->parameters) && count($command->parameters) > 0) {
			if($command->method == "GET") $queryString = true;
			foreach($command->parameters as $p) {
				$parameters[] = array('name' => $p,
									'type' => 'string',
									'paramType' => ($queryString) ? 'query' : 'form',
									//'required' => ($noRequired) ? false : $field->isRequired,
									'description' => $p." parameter");
			}
		
			return $parameters;
		}
		return NULL;
	}
	
	public static function getParametersFromRoute($route, $routeMethod, $routeAction = NULL, $queryString = false) {
	
		//Disable of show required fields
		$noRequired = true;
		//Get parameters from model
		$parametersFromModel = false;
		//Set the ID as parameters
		$idAsParameter = false;
		
		$parameters = array();
		
		//Logic
		switch($routeMethod) {
			case "GET":
				if($routeAction == "id") {
					$parameters[] = SwaggerHelper::getIdParameter($route, true);
				} else {
					$parameters[] = SwaggerHelper::getIdParameter($route, false, false, true);
					$parameters = array_merge($parameters, SwaggerHelper::getParametersFromModel($route, true, true));
					//var_dump($parameters);
				}
			break;
			case "PUT":
			
				if($routeAction == "id") {
					$parameters[] = SwaggerHelper::getIdParameter($route, true, false);
					$parameters = array_merge($parameters, SwaggerHelper::getParametersFromModel($route, false));
				} else {
					//$parameters[] = SwaggerHelper::getIdParameter($route, true, true);
					$parameters[] = SwaggerHelper::getBodyParameterFromModel($route);
				}
				
				//

				
			break;
			case "POST":
				//$parameters[] = SwaggerHelper::getBodyParameterFromModel($route);
				$parameters = array_merge($parameters, SwaggerHelper::getParametersFromModel($route, false));
			break;
			case "DELETE":
				if($routeAction == "id") {
					$parameters[] = SwaggerHelper::getIdParameter($route, true);
				}
			break;
		}
		
		return $parameters;
	}
	
	public static function getIdParameter($route, $required, $asForm = false, $queryString = false) {
		return array('name' => ($asForm === true) ? $route->primaryKey->fieldName : ($queryString ? $route->primaryKey->fieldName : $route->routeName."Id"), 
						'paramType' => ($asForm === true) ? 'form' : (($queryString == true) ? 'query' : 'path'),
						'type' => 'string',
						'required' => $required,
						"description" => "ID of ".$route->routeName);
	}
	
	public static function getParametersFromModel($route, $noRequired = false, $queryString = false) {

		foreach($route->routeFields as $field) {
			
	
			if($field->fieldName != "id") {
			
				$p = array('name' => (!$field->isRelation) ? $field->fieldName : $field->relation->field,
									//'type' => ($field->fieldType) ? $field->fieldType : 'void',
									//'type' => 'string',
									'type' => ($field->fieldType) ? $field->fieldType : 'string',
									'paramType' => ($queryString) ? 'query' : 'form',
									'required' => ($noRequired) ? false : $field->isRequired,
									'description' => $field->description);
			
				if($field->isFile) {
					$p["paramType"] = "body";
					unset($p["type"]);
					$p["dataType"] = "file";
					$p["consumes"]="multipart/form-data";
				}
			
				$parameters[] = $p;
			}
			
		}
				
		return $parameters;
	}
	
	public static function getBodyParameterFromModel($route) {
		return array('name' => "body",
					'paramType' => 'body',
					'required' => true,
					'type' => $route->routeName,
					'description' => $route->routeName." json representation. It can be an array to update multiple objects.");
	}
	
	public static function createOperation($method, $route, $parameters = null, $operationType, $custom = false, $customNotes = "") {
		switch($method) {
			case "GET":
				$notes = "Retrieve ".$route->routeName." objects<br /><br />";
				
				$notes.="Get all records<br/>" . '/' . $route->routeName ."<br /><br />" ;
				$notes.="Get a single record matching id XXXXX<br/>" . '/' . $route->routeName ."/XXXXX<br /><br />" ;
				$notes.="Get only XXXXX records<br/>" . '/' . $route->routeName ."/?limit=XXXXX<br /><br />" ;
				$notes.="Skip YYYYY records and get next XXXXX records<br/>" . '/' . $route->routeName ."/?limit=XXXXX&offset=YYYYY<br /><br />" ;
				$notes.="Get XXXXX records and sort by YYYYY field in descending order<br/>" . '/' . $route->routeName ."/?limit=XXXXX&order=YYYYY&orderType=desc<br /><br />" ;
				$notes.="Get all records count<br/>" . '/' . $route->routeName ."/?count=true<br /><br />" ;
				
				$notes.="<b>List of filters:</b><br /><br />";
				foreach($parameters as $p) {
				
					$notes.='/' . $route->routeName."/?".$p["name"]."[in]=XXXXX => Search XXXXX in field ".$p["name"]."<br />";
					$notes.='/' . $route->routeName."/?".$p["name"]."[gt]=XXXXX => Compare if ".$p["name"]." is greater than XXXXX<br />";
					$notes.='/' . $route->routeName."/?".$p["name"]."[ge]=XXXXX => Compare if ".$p["name"]." is greater or equal than XXXXX<br />";
					$notes.='/' . $route->routeName."/?".$p["name"]."[lt]=XXXXX => Compare if ".$p["name"]." is less than XXXXX<br />";
					$notes.='/' . $route->routeName."/?".$p["name"]."[le]=XXXXX => Compare if ".$p["name"]." is less or equal than XXXXX<br />";	
					$notes.="<br />";
					
				}
				break;
			case "PUT":
				if($operationType == "void") {
					$notes = "Update ".$route->routeName." object";
				} else {
					$notes = "Create or update ".$route->routeName." object";
				}
			break;
			case "POST":
				$notes = "Create ".$route->routeName." object";
			break;
			case "DELETE":
				$notes = "Deletes ".$route->routeName." object";
			break;
			default:
				$notes = $route->routeName." ".$method." operation";
			break;
		}
		
		if($custom == true) $notes= $customNotes;
	
		$operation["method"]=$method;
		$operation["nickname"]=strtolower($method).strtolower($route->routeName);
		if(isset($parameters))
			$operation["parameters"]=$parameters;

		$operation['produces'] = array('application/json');
		$operation['notes'] = $notes;
		$operation['authorizations']=array();
		$operation['type']=$operationType;
		if($operationType == "array") {
			$operation["items"]["\$ref"]=$route;
		}
		return $operation;
	}
	
	
	public static function getModelsFromRoutes($routes) {
		
		$models = array();
		
		foreach($routes as $route) {
			$models[$route->routeName]["id"]=$route->routeName;
			
			$properties = array();

			foreach($route->routeFields as $f) {
				
				if($f->isRelation) {
					//Replace _id for relation fields and display proper name
  					$fieldName = str_replace("_id","",$f->relation->field);
					$relationFieldName = str_replace("id","",$fieldName);

					$properties[$fieldName]["description"]=$fieldName." field ".$f->fieldType;
				
					$properties[$fieldName]["type"]=$f->fieldType;
					
					if($f->isRequired) {					
						$models[$route->routeName]["required"][]=$fieldName;
					}

				} else {
				
					$properties[$f->fieldName]["description"]=$f->fieldName." field ".$f->fieldType;
			
					$properties[$f->fieldName]["type"]=$f->fieldType;
				
					if($f->isRequired) {
						$models[$route->routeName]["required"][]=$f->fieldName;
					}
				}
			}
			
			$models[$route->routeName]["properties"]=$properties;
			
		}
		
		
		return $models;
	}
	
	public static function routeResume($routes, $custom_routes = null) {
	
		foreach($routes as $routeName => $routeObject) {

			$operation["description"]="Operations about ".$routeName;
			$operation["path"]="/api-doc/".$routeName;
		
			$r[] = $operation;
		}
		
		if($custom_routes != null){
			
			foreach($custom_routes as $routeName => $routeObject) {

				$operation["description"]="Operations about ".$routeName;
				$operation["path"]="/api-doc-custom/".$routeName;
				$r[] = $operation;
			}

			
		}
		
		$result = array
		(
			'apiVersion' => API_VERSION,
			'apis' => $r
		);
		
		return $result;
	}
	
	
	
}

?>
