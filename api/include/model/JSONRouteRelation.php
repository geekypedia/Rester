<?php

/**
 * This relation is stored as JSON in the database. This class does all the work for serializing & deserializing the objects
 * @author jcornado
 *
 */

define('JSON_RELATIONS_FILE','relations.json');

class JSONRouteRelation extends RouteRelation {
	
	static function getJSONRelations() {
		$relations = array();
		
		if(file_exists(JSON_RELATIONS_FILE)) {
			ResterUtils::Log(">> Processing JSON Relations file");
			
			$json_relations = file_get_contents(JSON_RELATIONS_FILE);
			
			$parsed_relations = json_decode($json_relations);
			
			foreach($parsed_relations as $r) {
				
				$relation = new JSONRouteRelation();
				$relation->relationName = $r->relationName;
				$relation->route = $r->route;
				$relation->field = $r->field;
				$relation->destinationRoute = $r->destinationRoute;
				$relation->destinationField = $r->destinationField;
				$relation->inverse = $r->inverse;
				
				$relations[$relation->route][]=$relation;
				if($r->inverse) {
					$relations[$relation->destinationRoute][]=$relation;
				}
			}
		}
		
		return $relations;
	}
	
}

?>