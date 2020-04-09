/*global app, ControllerFactory, RegisterRoutes, RegisterData*/
function RegisterEasyController(route, headers, controller, auto = false, alias) {
	app.controller((alias ? alias : route) + 'ControllerBase', ControllerFactory(route));

	app.controller((alias ? alias : route) + 'Controller', function($scope, $controller, H) {
		//Copy all scope variables from Base Controller
		$controller((alias ? alias : route) + 'ControllerBase', {
			$scope: $scope
		});
		try {
			$controller((alias ? alias : route) + 'ControllerExtension', {
				$scope: $scope
			});
		} catch (ex) {
			console.log(ex);
		}


		if (auto) {
			$scope.initTextResourcesAuto();
		} else {
			$scope.initTextResourcesEasy();
		}


		//$scope.setListHeaders(headers);

	});
}

//Register Easy Routes
(function() {
	var easyRoutes = RegisterRoutes().easyRoutes || [];
	//var data = RegisterData();

	for (var i = 0; i < easyRoutes.length; i++) {
		RegisterEasyController(easyRoutes[i] /*, data[easyRoutes[i]].headers*/ );
	}
})();

//Register Auto Routes
(function() {
	var autoRoutes = RegisterRoutes().autoRoutes || [];
	//var data = RegisterData();

	for (var i = 0; i < autoRoutes.length; i++) {
		RegisterEasyController(autoRoutes[i], null, null, true /*, data[easyRoutes[i]].headers*/ );
	}
})();


//Register Aliases
(function() {
    var aliases = RegisterRoutes().aliases || [];
    //var data = RegisterData();
    for (var i in aliases) {
        alias = i;
        route = aliases[i];
        //console.log('trying to register ' + alias + ' for route ' + route);
        RegisterEasyController(route, null, null, true, alias /*, data[easyRoutes[i]].headers*/ );
    }
})();
