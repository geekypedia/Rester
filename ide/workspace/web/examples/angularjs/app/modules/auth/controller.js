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
});