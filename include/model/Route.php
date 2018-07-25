<?php

class Route {
	/** The main route name */
	var $routeName;
	/** All the fields contained in this route */
	var $routeFields = array();
	/** Route command processors */
	var $routeCommands = array();
	/** Route primary key field */
	var $primaryKey = NULL;
	/** All the file processors of this route */
	var $fileProcessors = array();
	
	/**
	 * Returns the relation fields with this route
	 * @return array of RouteField
	 */
	function getRelationFields($skipNonJoining = FALSE) {
		$r = array();
		foreach($this->routeFields as $rf) {
			
			if($rf->isRelation) {
				if($skipNonJoining) {
					if($rf->fieldType != "json")
						$r[]=$rf;
				} else
					$r[]=$rf;
			}
		}
		return $r;
	}
	
	/**
	 * Gets all the field names
	 * @param boolean $includeRelations all the relations are included with sql like reference
	 * @param boolean $routePrefix append route prefix
	 * @return array(string) array containing route names as string
	 */
	function getFieldNames($includeRelations = TRUE, $routePrefix = FALSE) {
		$fieldNames = array();
		foreach($this->routeFields as $rf) {
			if($includeRelations || !$rf->isRelation) {
				if($routePrefix)
					$fieldNames[] = $this->routeName.".".$rf->fieldName;
				else
					$fieldNames[] = $rf->fieldName;
			}
		}
		return $fieldNames;
	}

	function getRelationFieldNames($relation) {
		foreach($this->getFieldNames(FALSE) as $fn) {
			$relationFieldNames[$fn] = $relation->relationName."_".$fn;
		}
		foreach($this->getRelationFields() as $rf) {
			$relationFieldNames[$rf->relation->field] = $rf->relation->relationName;
		}
		return $relationFieldNames;
	}
	
	function addFileProcessor($fieldName) {
		foreach($this->routeFields as $field) {
			if($field->fieldName == $fieldName) {
				ResterUtils::Log($this->routeName." > ".$field->fieldName." IS FILE TYPE");
				$field->isFile = true;
				$this->fileProcessors[] = new RouteFileProcessor($field);
			}
		}
	}
	
	function getFileProcessor($name) {
		foreach($this->fileProcessors as $processor) {
			if($name == $processor->field->fieldName)
				return $processor;
		}
		return NULL;
	}
	
	/**
	 * PDO does not treat types of objects by default, this method casts the object values to appropiate type
	 * @param object $object the source object
	 * @return object the result object
	 */
	function mapObjectTypes($object) {
		foreach($this->routeFields as $rf) {
			if($rf->fieldType == "integer") {
				if(isset($object[$rf->fieldName]))
					$object[$rf->fieldName]=intval($object[$rf->fieldName]);
			}
			
			if($rf->fieldType == "float") {
				if(isset($object[$rf->fieldName]))
					$object[$rf->fieldName]=floatval($object[$rf->fieldName]);
			}

			if($rf->fieldType == "boolean") {
				$jsonValue = json_encode($object[$rf->fieldName]);
				$bitZero = "\"\\u0000\"";
				$bitOne = "\"\\u0001\"";
				if($jsonValue == $bitOne){
					$object[$rf->fieldName] = true;
				} else if ($jsonValue == $bitZero){
					$object[$rf->fieldName] = false;
				} else {
					$object[$rf->fieldName]= (bool) $object[$rf->fieldName];
				}
			}
			
			if($rf->fieldType == "json") {
				$objec[$rf->fieldName]="JSON";
			}
		}
		return $object;
	}

	/**
	 * Looks for id into object, if not found, we can generate one
	 * @param object $objectData source object
	 * @return string|NULL id of the object
	 */
	function getInsertIDFromObject($objectData) {
		if($this->primaryKey != NULL) {
			//if(!isset($data[$route->primaryKey->fieldName])) { //we have not passed an id by parameters
			if(!array_key_exists($this->primaryKey->fieldName, $objectData)) {
				ResterUtils::Log(">> NO KEY SET ON CREATE *".$this->primaryKey->fieldName."*");
				if($this->primaryKey->isAutoIncrement) { //put a dummy value to auto_increment do the job
					$objectData[$this->primaryKey->fieldName] = '0';
				} else {
					$insertID = UUID::v4();
					ResterUtils::Log(">> GENERATING NEW UUID ".$insertID);;
					$objectData[$this->primaryKey->fieldName] = $insertID; //generate a UUID
				}
			} else {
				$insertID = $objectData[$this->primaryKey->fieldName];
			}
			return $insertID;
		}
		return NULL;
	}
	
}

?>
