/*global app*/
app.controller('homeController', function ($scope, $rootScope, H, R) {

	// $controller('homeControllerBase', {
	// 	$rootScope:$rootScope
	// });

    // $('.collapsible').collapsible();
    

	$scope.H = H;
	$scope.M = H.M;

	$scope.data = {
		counters: {
			tasksCounter: {
				title: 'Tasks',
				value: '...',
				icon: 'assignment_turned_in',
				background: 'green',
				color: 'white',
				action: 'tasks',
				allowedRoles: ['user', 'admin']
			},
			usersCounter: {
				title: 'Users',
				value: '...',
				icon: 'person',
				background: 'purple',
				color: 'white',
				action: 'users',
				allowedRoles: ['admin']
			},
			groupsCounter: {
				title: 'Groups',
				value: '...',
				icon: 'group',
				background: 'pink',
				color: 'white',
				action: 'groups',
				allowedRoles: ['admin']
			},
			organizationsCounter: {
				title: 'Organizations',
				value: '...',
				icon: 'people_outline',
				background: 'green',
				color: 'white',
				action: 'organizations',
				allowedRoles: ['superadmin']
			}
		},
		bgColors: [
			"blue",
			"red",
			"teal",
			"orange",
			"cyan",
			"brown",
			"pink",
			"purple",
			"green"
			// "light-blue",
			// "amber",
			// "lime",
			// "yellow",
			// "indigo",
			// "grey",
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
		R.count(resourceName, function (result) {
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
		var resources = [];
		for (var k in $scope.data.counters) {
			var v = $scope.data.counters[k];
			if(v.allowedRoles.indexOf($rootScope.currentUser.role) > -1){
				resources.push(v.action);
			}
		}
		setCounts(resources);
	}
	
	$rootScope.currentPage = 1;
	
	
	//Random colors for each tile
	//randomizeTileColors();
	
	//Set counts for each tile
	//setCounts(["tasks", "users"]);
	
	//Set counts for each tile automatically, considering the name of the action and the path of the API is same
	setCountsDefault();


});
