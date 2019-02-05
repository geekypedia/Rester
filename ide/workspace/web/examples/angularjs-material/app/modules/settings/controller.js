/*global app*/
app.controller('settingsController', function($scope, $rootScope, $http, $cookies, H, M){
	$scope.H = H;
	$scope.M = H.M;
	
	$scope.locked = true;
	
	$scope.unlock = function(){
	    $scope.locked = false;
	};

	$scope.lock = function(){
	    $scope.locked = true;
	};

	$scope.forms = {};
	
	$scope.data = {
	    single: $rootScope.currentUser.organization
	};

});