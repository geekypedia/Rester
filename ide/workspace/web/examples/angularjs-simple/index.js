//Initialize app
var app = angular.module('app', ['ngRoute', 'ngResource']);

//Defining constants
app.constant('SETTINGS', {
	baseUrl: '../../../../../api'
});

app.constant('MESSAGES', {
	'E404': 'The resource you are trying to access does not exist! Please check you have a relevant API endpoint and/or database table available.',
	'E400': 'Bad request!',
	'E500': 'An error accured.'
});

//Configuring Routes and Resources
app.config(function($routeProvider, $resourceProvider) {
	$routeProvider
		.when('/', {
			templateUrl: 'templates/home.html',
			controller: 'homeController'
		})
		.when('/blog', {
			templateUrl: 'templates/blog.html',
			controller: 'blogController'
		})
		.when('/about', {
			templateUrl: 'templates/about.html',
			controller: 'aboutController'
		});
});

//Service for quickly getting the API Resource Object
app.service('R', function($resource, SETTINGS) {
	return {
		get: function(resourceName) {
			return $resource(SETTINGS.baseUrl + '/' + resourceName + '/:id', {
				id: '@id'
			});
		}
	};
});

//ControllerFactory helps wrap basic CRUD operations for any API resource
function ControllerFactory(resourceName, options, extras) {
	return function($scope, R, MESSAGES) {
		//Get resource by name. Usually it would be you API i.e. generated magically from your database table.
		var Resource = R.get(resourceName);

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
							message: MESSAGES.E404
						});
						break;
					case 400:
						$scope.errors.push({
							message: MESSAGES.E400
						});
						break;
					case 500:
						$scope.errors.push({
							message: MESSAGES.E500
						});
						break;
					default:
						$scope.errors.push({
							message: MESSAGES.E500
						});
						break;
				}
			}
		};
		
		//Initializa new single objetc
		$scope.initSingle = function(){
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
				obj.$delete();
				if (callback) {
					callback();
				}
			} else if (!isNaN(obj)) {
				$scope.get(obj, function(result){
					if(result && result.$delete){
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
				promise.then(function(){
					if (callback) {
						callback();
					}
				});
			} else if ($scope.data.single) {
				var promise = $scope.data.single.$save();
				promise.then(function(){
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
		$scope.refreshData = function(){
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
}







