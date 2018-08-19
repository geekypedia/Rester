<?php

class RouteField {
	
	var $fieldName;
	var $fieldType;
	var $isRequired;
	var $isKey;
	var $defaultValue;
	var $description;
	var $isRelation = false;
	var $relation;
	var $isFile = false;
	var $isAutoIncrement = false;
	
	static function getTypeFromMySQL($mysqlType) {
		$type = "string";
		if(strpos($mysqlType, "int") !== false) {
			$type = "integer";
		}
		
		if(strpos($mysqlType, "float") !== false || strpos($mysqlType, "double") !== false) {
			$type = "float";
		}

		if(strtolower($mysqlType) == "enum('false','true')" 
				|| strtolower($mysqlType) == "enum('true','false')" 
				|| strpos($mysqlType, "bool") !== false
				|| strpos($mysqlType, "bit") !== false) {
			$type = "boolean";
		}
		
		return $type;
	}
		
	function setRelation($routeRelation) {
		$this->isRelation = true;
		
		if(!is_a($routeRelation, "JSONRouteRelation")) {
			$this->fieldName = $routeRelation->relationName;
			$this->fieldType = $routeRelation->destinationRoute;
		} else {
			$this->fieldType = "json";
		}
		$this->relation = $routeRelation;
		$this->isRequired = false;
		//error_log("SET RELATION ".$this->fieldName." ".$routeRelation->relationName." - type: ".$routeRelation->route);
	}
	
	function getRelationFieldName() {
		if($this->isRelation)
			return $this->relation->relationName."_".$this->relation->field;
		return NULL;
	}
}

?>
