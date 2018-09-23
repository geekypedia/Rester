app.controller('authController', function($scope, $rootScope, $http, $location, $cookies, H, M, S) {
	if($rootScope.currentUser){
		$location.path('/');
	}
	
	$scope.forms = {}
	
	$scope.H = H;
	$scope.M = M;
	$scope.S = S;

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

	$scope.forgotPassword = function(){
		$http.post(H.SETTINGS.baseUrl + '/users/forgot-password', {email: $scope.email})
			.then(function(r){
				$scope.error = M.RECOVERY_EMAIL_SENT;
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					$scope.error = e.data.error.status;
				}
			});
	}

	$scope.register = function(){
		$http.post(H.SETTINGS.baseUrl + '/organizations', {name: $scope.organization, email: $scope.email})
			.then(function(r){
				$scope.error = M.REGISTRATION_EMAIL_SENT;
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

app.controller('unauthorizedController', function($scope, H){
	$scope.H = H;
	$scope.M = H.M;
});