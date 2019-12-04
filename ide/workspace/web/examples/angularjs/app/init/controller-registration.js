/*global app, ControllerFactory, RegisterRoutes, RegisterData*/
function RegisterEasyController(route, headers, controller, auto = false) {
	app.controller(route + 'ControllerBase', ControllerFactory(route));

	app.controller(route + 'Controller', function($scope, $controller, H) {
		//Copy all scope variables from Base Controller
		$controller(route + 'ControllerBase', {
			$scope: $scope
		});
		try {
			$controller(route + 'ControllerExtension', {
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
