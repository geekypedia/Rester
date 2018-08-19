<?php

class MySQLRouteRelation extends RouteRelation {
	
	static function parseRelationsFromMySQL($relationsArray) {
		
		ResterUtils::Log(">>> Processing Relations from MySQL");
		
		$relations = array();
	
		foreach($relationsArray as $r) {
			$relation = new MySQLRouteRelation();
			$relation->relationName = $r["CONSTRAINT_NAME"];
			$relation->route = $r["TABLE_NAME"];
			$relation->field = $r["COLUMN_NAME"];
			$relation->destinationRoute = $r["REFERENCED_TABLE_NAME"];
			$relation->destinationField = $r["REFERENCED_COLUMN_NAME"];
			$relations[$relation->route][]=$relation;
		}
		
		ResterUtils::Dump($relations);
	
		return $relations;
	}
	
}

?>