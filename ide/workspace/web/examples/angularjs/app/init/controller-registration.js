function RegisterEasyController(route, headers, controller){
	app.controller(route + 'ControllerBase', ControllerFactory(route));
	
	app.controller(route + 'Controller', function($scope, $controller, H) {
		//Copy all scope variables from Base Controller
		$controller(route + 'ControllerBase', {
			$scope: $scope
		});
		try{
			$controller(route + 'ControllerExtension', {
				$scope: $scope
			});
		} catch (ex){
			
		}
		
		$scope.initTextResourcesEasy();
		
		$scope.setListHeaders(headers);
		
	});
}

//Register Easy Routes
(function(){
    var easyRoutes = RegisterRoutes().easyRoutes;
    var data = RegisterData();
    
    for (var i = 0; i < easyRoutes.length; i++) {
        RegisterEasyController(easyRoutes[i], data[easyRoutes[i]].headers);
    }
})();
