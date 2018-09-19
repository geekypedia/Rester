//An example of Angular $resource. Any Controller that calls ControllerFactory with the name of the API will get default CRUD operations.
app.controller('blogControllerBase', ControllerFactory('posts'));

//Controller inheritance for any additional operation you might want apart from the deafult CRUD
app.controller('blogController', function($scope, $controller) {
	//Copy all scope variables from Base Controller
	$controller('blogControllerBase', {
		$scope: $scope
	});

	//Load all posts on initialization
	$scope.query({}, function(r) {});

	$scope.edit = function(obj) {
		$scope.mode = $scope.MODES.edit;
		$scope.editing = obj.id;
	};

	$scope.saveSingle = function() {
		$scope.save(null, function() {
			$scope.mode = $scope.MODES.view;
			$scope.editing = 0;
			$scope.initSingle();
			$scope.query();
		});
	};
	
	$scope.saveObject = function(obj){
		$scope.save	(obj, function(){
			$scope.mode = $scope.MODES.view;
			$scope.editing = 0;
			$scope.query();
		});
	};
	
	$scope.cancel = function(obj){
		$scope.mode = $scope.MODES.view;
		$scope.editing = 0;
		$scope.initSingle();
	};
	
	$scope.deleteObject = function(obj) {
		$scope.delete(obj, function() {
			$scope.query();
		});
	};

});
