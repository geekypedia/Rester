//An example of Angular $http
app.controller('aboutController', function($scope, $http) {
	var url = 'https://api.github.com/users/omgtalsania';
	$http
		.get(url)
		.then(function(resource) {
			$scope.data = resource.data;
		});
});