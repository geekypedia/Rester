//Initialize app
var app = angular.module('app', [
							'ngRoute', 
							'ngResource', 
							'ngCookies', 
							'ui.grid', 
							'ui.grid.resizeColumns', 
							'ui.grid.moveColumns', 
							'ui.grid.selection', 
							'ui.grid.exporter', 
							'ui.grid.autoResize', 
							'ngMaterial', 
							'ngMessages',
							'angular-md5'
							]);


//JQuery
$(function() {
	$('.sidenav').sidenav({
		closeOnClick: true
	});
});





  app.factory('httpRequestInterceptor', function ($rootScope) {
    return {
        request: function (config) {
            if ($rootScope.currentUser) {
                config.headers['api_key'] = $rootScope.currentUser.token;
            }
            return config;
        }
    };
});

function CustomRoutes(){
    this.routes = RegisterRoutes();
}

app.provider('customRoutes', function() {
    Object.assign(this, new CustomRoutes());

    this.$get = function() {
        return new CustomRoutes();
    };
});

app.config(function ($routeProvider, $resourceProvider, $httpProvider, customRoutesProvider) {
    var routes = customRoutesProvider.routes;
    for (var i = 0; i < routes.length; i++) {
        var r = routes[i];
        $routeProvider.when('/' + r.route, { templateUrl: 'app/modules/' + r.template + '.html', controller: r.controller + 'Controller'});
    }

    $httpProvider.interceptors.push('httpRequestInterceptor');
});

app.run(function ($rootScope, $location, $cookies, H) {
    $rootScope.SETTINGS = H.SETTINGS;

    $rootScope.fieldTypes = H.SETTINGS.fieldTypes;

    $rootScope.$on("$locationChangeStart", function (event, next, current) {
        if (!$rootScope.currentUser) {
            var cookie = $cookies.get(H.getCookieKey());
            if (!cookie) {
                $location.path('/sign-in');
            }
            else {
                var cu = JSON.parse(cookie);
                $rootScope.currentUser = typeof cu==='string'? JSON.parse(cu):cu;
            }
        }
    });
});

//ControllerFactory helps wrap basic CRUD operations for any API resource
function ControllerFactory(resourceName, options, extras) {
	return function($scope, H) {
		//Get resource by name. Usually it would be you API i.e. generated magically from your database table.
		var Resource = H.R.get(resourceName);

		//Scope variables
		$scope.data = {};
		$scope.data.single = new Resource();
		$scope.data.list = [];
		$scope.errors = [];
		$scope.MODES = {
			'view': 'view',
			'edit': 'edit',
			'add': 'add'
		}
		$scope.mode = $scope.MODES.view;

		//Default error handler
		var errorHandler = function(error) {
			if (error && error.status) {
				switch (error.status) {
					case 404:
						$scope.errors.push({
							message: H.MESSAGES.E404
						});
						break;
					case 400:
						$scope.errors.push({
							message: H.MESSAGES.E400
						});
						break;
					case 500:
						$scope.errors.push({
							message: H.MESSAGES.E500
						});
						break;
					default:
						$scope.errors.push({
							message: H.MESSAGES.E500
						});
						break;
				}
			}
		};

		//Initializa new single objetc
		$scope.initSingle = function() {
			$scope.data.single = new Resource();
		};

		//Get all rows from your API/table. Provide a query filter in case you want reduced dataset.
		$scope.query = function(q, callback) {
			if (!q) {
				q = {};
			}
			Resource.query(q, function(result) {
				if (result) {
					$scope.data.list = result;
				}
				if (callback) {
					callback(result);
				}
			}, errorHandler);
		};

		//Get specific record
		$scope.get = function(id, callback) {
			Resource.get({
				id: id
			}, function(result) {
				$scope.data.single = result;
				if (callback) {
					callback(result);
				}
			}, errorHandler);
		};

		//Delete specific record
		$scope.delete = function(obj, callback) {
			if (obj && obj.$delete) {
				obj.$delete(function(r) {
					if (callback) {
						callback(r);
					}
				}, function(e) {
					if (callback) {
						callback(e);
					}
				});

			} else if (!isNaN(obj)) {
				$scope.get(obj, function(result) {
					if (result && result.$delete) {
						result.$delete();
						if (callback) {
							callback();
						}
					}
				});
			}
		};

		//Save a record
		$scope.save = function(obj, callback) {
			
			
			if (obj && obj.$save) {
				var promise = obj.$save();
				promise.then(function() {
					if (callback) {
						callback();
					}
				});
			} else if ($scope.data.single) {
				var promise = $scope.data.single.$save();
				promise.then(function() {
					if (callback) {
						callback();
					}
				});
			}
		};

		//Clear errors
		$scope.clearErrors = function() {
			$scope.errors = [];
		};

		//Refresh data
		$scope.refreshData = function() {
			$scope.query();
		};

		/*Define options
			init:true -> Load all records when the controller loads
		*/
		if (options) {
			$scope.options = options;
			if ($scope.options.init) {
				$scope.query();
			}
		}

		//Any extra stuff you might want to merge into the data object
		if (extras) {
			for (var e in extras) {
				$scope.data[e] = extras[e];
			}
		}

	};
}function RegisterMenuItems(){
    return {
        menu: [
    	    {action: '', icon: 'home',text: 'Home'},
    	    {action: 'tasks', icon: 'assignment_turned_in',text: 'Tasks'},
    	    {action: 'search', icon: 'search',text: 'Search'},
    	    {action: 'reports', icon: 'pie_chart',text: 'Reports'},
    	    {action: 'alerts', icon: 'alarm',text: 'Alerts'}
	    ],
	    adminMenu: [
    	    {action: 'settings', icon: 'settings',text: 'Settings'},
    	    {action: 'categories', icon: 'list',text: 'Categories'},
    	    {action: 'users', icon: 'people',text: 'Users'},
    	    {action: 'groups', icon: 'group_add',text: 'Groups'}
    	]
    }
}

app.service('M', function($http) {
	return {
		"E404": "The resource you are trying to access does not exist! Please check you have a relevant API endpoint and/or database table available.",
		"E400": "Bad request!",
		"E500": "An error accured."
	}
});
function RegisterRoutes() {
    return [
        {route: '', template: 'home/template', controller: 'home'},
        {route: 'sign-in', template: 'auth/sign-in', controller: 'auth'},
        // {route: 'forgot-password', template: 'auth/forgot-password', controller: 'auth'},
    ];
}

app.service('S', function($http) {
	return {
		"baseUrl": "../../../../../../api",
		"productName": "pRESTige",
		"supportEmail": "support@geekypedia.net"
	}
});app.service('H', function($location, md5, S, M, R) {
	return {
		S: S,
		SETTINGS: S,
		M: M,
		MESSAGES: M,
		R: R,
		RESOURCES: R,
		getCookieKey: function() {
			var absUrl = $location.absUrl();
			var startIndex = absUrl.indexOf("//") + 2;
			var endIndex = absUrl.indexOf("#");
			var base = absUrl.substring(startIndex, endIndex);
			var pattern = /[\s:/!@#\$%\^\&*\)\(+=.-]/g;
			var key = base.replace(pattern, "_");
			return key;
		},
		getAbsolutePath: function(href) {
		    var link = document.createElement("a");
		    link.href = href;
		    return link.href;
		},
		getHash: function(str){
           return md5.createHash(str);
		},
		getRandomNumber: function(min, max) {
			return Math.floor(Math.random() * (max - min + 1)) + min;
		}

	}
});
//Service for quickly getting the API Resource Object
app.service('R', function($resource, $http, S) {
	return {
		get: function(resourceName) {
			return $resource(S.baseUrl + '/' + resourceName + '/:id', {
				id: '@id'
			});
		},
		count: function(resourceName, cb) {
			$http.get(S.baseUrl + '/' + resourceName + '/?count=true')
				.then(function(results) {
					if (results && results.data && results.data.length > 0)
						if (cb) cb(results.data[0].count);
				}, function(e) {});
		}
	};
});
app.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;

            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);app.component('infoBox', {
	templateUrl: 'app/components/infobox/template.html',
	controller: 'infoboxController',
	bindings: {
		options: '<',
	}
});

app.controller('infoboxController', function($scope){
	// $scope.options = {
	// 	title: 'options.title',
	// 	value: 'options.value',
	// 	icon: 'options.icon',
	// 	background: 'bgblueish',
	// 	color: 'white-text',
	// 	action: 'options.action'
	// };

	var self = this;
	self.$onInit = function(){
		if(self.options){
			$scope.options = self.options;
		}
	}
	
});

app.component('modal', {
	templateUrl: 'app/components/modal/template.html',
	controller: 'modalController',
	bindings: {
		options: '=',
	}
});

app.controller('modalController', function($scope){
	var self = this;
	self.$onInit = function(){
		if(self.options){
			$scope.options = self.options;
			$scope.options.open = openModal;
		}
		else{
			$scope.options = {};
			$scope.options.open = openModal;
		}
	}
	
	$(function(){
		self.modal = M.Modal.init(document.querySelector('#mdmodal'));
	})

	function openModal(options){
		if(options){
			$scope.options = options;
			$scope.options.open = openModal;
		}
		
		self.modal.open();
	}

	
});

app.component('time', {
	templateUrl: 'app/components/time/template.html',
	controller: 'timeController',
	bindings: {
		value: '<',
		label: '<'
	}
});

app.controller('timeController', function($scope){
	// $scope.options = {
	// 	title: 'options.title',
	// 	value: 'options.value',
	// 	icon: 'options.icon',
	// 	background: 'bgblueish',
	// 	color: 'white-text',
	// 	action: 'options.action'
	// };

	var self = this;
	self.$onInit = function(){
		if(self.value){
			$scope.value = self.value;
			$scope.hh = $scope.value.substring(1, 3);
			$scope.mm = $scope.value.substring(4);
		}
		else{
			$scope.hh = "00";
			$scope.mm = "00";
		}
		if(self.label){
			$scope.label = self.label;
		}
	}
	
});

app.controller('authController', function($scope, $rootScope, $http, $location, $cookies, H) {
	if($rootScope.currentUser){
		$location.path('/');
	}
	
	$scope.login = function(){
		
		$http.post(H.SETTINGS.baseUrl + '/users/login', {email: $scope.email, password: $scope.password})
			.then(function(r){
				$scope.error = "";
				$rootScope.currentUser = r.data;
				$cookies.putObject(H.getCookieKey(), JSON.stringify(r.data));
				$location.path('/');
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					$scope.error = e.data.error.status;
				}
			});
	}
	
	$scope.logout = function(){
		$cookies.remove(H.getCookieKey());
		delete $rootScope.currentUser;
		$location.path('/sign-in');
	}
});app.controller('navController', function($scope) {
    var data = RegisterMenuItems();
    for(var k in data){
        if(data.hasOwnProperty(k)){
            for (var i = 0; i < data[k].length; i++) {
                data[k][i].action = '#!' + data[k][i].action;
            }
        }
    }
    $scope.data = data;
});
app.controller('titleController', function($scope, S){
   $scope.title =  S.productName;
});app.controller('homeController', function ($scope, $rootScope, H) {

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