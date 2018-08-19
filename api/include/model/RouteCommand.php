<?php 

class RouteCommand {
	var $method;
	var $routeName;
	var $routeCommand;
	var $callback;
	var $parameters;
	var $description;
	
	function RouteCommand($method, $routeName, $routeCommand, $callback, $parameters = NULL, $description = NULL) {
		$this->method = $method;
		$this->routeName = $routeName;
		$this->routeCommand = $routeCommand;
		$this->callback = $callback;
		if(isset($parameters))
			$this->parameters = $parameters;
			
		if(isset($description)) 
			$this->description = $description;
		
	}
	

}

?>