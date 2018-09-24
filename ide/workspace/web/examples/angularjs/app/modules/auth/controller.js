app.controller('authController', function($scope, $rootScope, $http, $location, $cookies, H, M, S) {
	if($rootScope.currentUser){
		$location.path('/');
	}
	
	$scope.forms = {}
	
	$scope.H = H;
	$scope.M = M;
	$scope.S = S;
	
	$scope.data = {};

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
		var route = 'users';
		var data = {username: $scope.data.username, email: $scope.data.email, password: $scope.data.password};
		if(S.enableSaaS) {
			route = 'organizations'; 
			data = {organization: $scope.data.organization, email: $scope.data.email};
		}else{
			if($scope.data.password != $scope.data.confirmPassword){
				$scope.error = "Password and Confirm Password should match!";
				return;
			}
		}
		
		$http.post(H.SETTINGS.baseUrl + '/' + route +'/register', data)
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

app.controller('profileController', function($scope, $rootScope, H){
	$scope.H = H;
	$scope.M = H.M;
});