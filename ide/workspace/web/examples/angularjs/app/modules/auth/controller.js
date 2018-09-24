/*global app*/
app.controller('authController', function($scope, $rootScope, $http, $location, $cookies, H, M, S) {
	if($rootScope.currentUser){
		$location.path('/');
	}
	
	$scope.forms = {};
	
	$scope.H = H;
	$scope.M = M;
	$scope.S = S;
	
	$scope.data = {};

	$scope.login = function(){
		$http.post(H.SETTINGS.baseUrl + '/users/login', {email: $scope.email, password: $scope.password})
			.then(function(r){
				$scope.error = "";
				if(!r.data.token){
					$scope.error = M.E500;
					return;
				}
				$rootScope.currentUser = r.data;
				$cookies.putObject(H.getCookieKey(), JSON.stringify(r.data));
				$location.path('/');
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					$scope.error = e.data.error.message ? e.data.error.message : e.data.error.status;
				}
			});
	};

	$scope.forgotPassword = function(){
		$http.post(H.SETTINGS.baseUrl + '/users/forgot-password', {email: $scope.email})
			.then(function(r){
				$scope.error = M.RECOVERY_EMAIL_SENT;
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					$scope.error = e.data.error.message ? e.data.error.message : e.data.error.status;
				}
			});
	};

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
					$scope.error = e.data.error.message ? e.data.error.message : e.data.error.status;
				}
			});
	};
	
	$scope.logout = function(){
		$cookies.remove(H.getCookieKey());
		delete $rootScope.currentUser;
		$location.path('/sign-in');
	};
});

app.controller('unauthorizedController', function($scope, H){
	$scope.H = H;
	$scope.M = H.M;
});

app.controller('profileController', function($scope, $rootScope, $http, $cookies, H, M){
	$scope.H = H;
	$scope.M = H.M;
	$scope.locked = true;
	$scope.lockedClass = "hidden";
	$scope.editingClass = "";
	$scope.forms = {};
	$scope.data = {};
	$scope.pass = {};

	$scope.disableEdit = function(){
		$scope.locked = true;
		$scope.lockedClass = "hidden";
		$scope.editingClass = "";
	}
	
	$scope.edit = function(){
		$scope.locked = false;
		$scope.editingClass = "float-left";
		$scope.lockedClass = "visible float-right formClass";
		$scope.data.username = $rootScope.currentUser.username;
		$scope.data.email = $rootScope.currentUser.email;
		$scope.data.role = $rootScope.currentUser.role;
	};
	
	$scope.save = function(){
		$scope.error = "";
		$scope.message = "";
		$http.get(H.S.baseUrl + '/users/' + $rootScope.currentUser.id).then(function(res){
			var r = res.data;
			r.username = $scope.data.username;
			r.email = $scope.data.email;
			r.role = $scope.data.role;
			$http.put(H.S.baseUrl + '/users', r).then(function(r){
				$scope.message = H.M.PROFILE_SAVED;
				var user = r.data;
				user.password = $rootScope.currentUser.password;
				user.organization = $rootScope.currentUser.organization;
				$rootScope.currentUser = user;
				$cookies.putObject(H.getCookieKey(), JSON.stringify($rootScope.currentUser));
			}, function(e){
				$scope.error = H.M.PROFILE_SAVE_ERROR;
			});
		},function(e){
			$scope.error = H.M.PROFILE_SAVE_ERROR;
		});
	};
	
	$scope.changePassword = function(){
		$scope.pass.error = "";
		$scope.pass.message = "";
		if($scope.pass.newPassword != $scope.pass.confirmPassword){
			$scope.pass.error = M.PASSWORD_NOT_MATCHING;
			return;
		}
		var data = {
			email: $rootScope.currentUser.email,
			password: $scope.pass.password,
			new_password: $scope.pass.newPassword
		};
		$http.post(H.S.baseUrl + '/users/change-password', data).then(function(res){
			$scope.pass.message = H.M.PASSWORD_CHANGED;
		},function(e){
			$scope.pass.error = H.M.PASSWORD_CHANGE_ERROR + " " + e.data.error.message;
		});
	};	
});