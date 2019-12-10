/*global app*/
app.controller('settingsController', function($scope, $rootScope, $http, $cookies, Alert, H, M, S){
	
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
	    single: JSON.parse(JSON.stringify($rootScope.currentUser.organization))
	};
	
	$scope.reloadData = function(){
		$scope.data.single = JSON.parse(JSON.stringify($rootScope.currentUser.organization));
	}
	
	$scope.save = function(){
		if($rootScope.currentUser.organization.id){
			$http.put(S.baseUrl + '/organizations', $rootScope.currentUser.organization).then(function(r){
				Alert.success("Successfully updated the record!")	
				$rootScope.currentUser.organization = r.data;
				$scope.lock();
			}, function(e){
				Alert.danger("Error updating the record!")	
				$scope.reloadData();
				$scope.lock();
			});
			
		} else {
			Alert.warning("You are not authorized for updating this information!");
			$scope.reloadData();
			$scope.lock();
		}

	}
	
    GLOBALS.methods.autoFocus();

});
