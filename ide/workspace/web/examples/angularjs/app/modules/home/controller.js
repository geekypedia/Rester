app.controller('homeController', function ($scope, $rootScope, H) {

	// $controller('homeControllerBase', {
	// 	$rootScope:$rootScope
	// });

	$scope.data = {
		counters: {
			tasksCounter: {
				title: 'Tasks',
				value: '0',
				icon: 'assignment_turned_in',
				background: 'bg-green',
				color: 'white-text',
				action: 'tasks'
			},
			usersCounter: {
				title: 'Users',
				value: '0',
				icon: 'person',
				background: 'bg-purple',
				color: 'white-text',
				action: 'users',
				availableTo: ['admin']
			}
		},
		bgColors: [
			"bg-blue",
			"bg-red",
			"bg-teal",
			"bg-orange",
			"bg-cyan",
			"bg-brown",
			"bg-pink",
			"bg-purple",
			"bg-green"
			// "bg-light-blue",
			// "bg-amber",
			// "bg-lime",
			// "bg-yellow",
			// "bg-indigo",
			// "bg-grey",
		]

	};
	
	function getNextNumber(n) {
		var m = n % $scope.data.bgColors.length;
		return m;
	}
	
	function randomizeTileColors() {
		var count = 0;
		for(var key in $scope.data){
			if($scope.data.hasOwnProperty(key)){
				var val = $scope.data[key];
				if(val.hasOwnProperty('background')){
					val.background = $scope.data.bgColors[getNextNumber(count)];
				}
				count++;
			}
		}
	}
	
	function setCount(resourceName, counterName) {
		H.R.count(resourceName, function (result) {
			$scope.data.counters[counterName].value = result;
		});
	}
	
	function setCounts(resources) {
		for (var i = 0; i < resources.length; i++) {
			var resourceName = resources[i];
			var counterName = resourceName + 'Counter';
			setCount(resourceName, counterName);
		}
	}
	
	function setCountsDefault(){
		resources = [];
		for (var k in $scope.data.counters) {
			var v = $scope.data.counters[k];
			resources.push(v.action);
		}
		setCounts(resources);
	}
	
	
	//Random colors for each tile
	//randomizeTileColors();
	
	//Set counts for each tile
	//setCounts(["tasks", "users"]);
	
	//Set counts for each tile automatically, considering the name of the action and the path of the API is same
	setCountsDefault();


});