/*global app*/
// app.component('sideNav', {
// 	templateUrl: 'app/components/nav/side-nav.html',
// 	controller: 'navController',
// 	bindings: {
// 		options: '<',
// 	}
// });


app.directive('headerNav', function(){
	return {
		restrict: 'EA',
    	templateUrl: 'app/components/nav/header-nav.html'
	};
});


app.directive('sideNav', function(){
	return {
		restrict: 'EA',
    	templateUrl: 'app/components/nav/side-nav.html'
	};
});

app.directive('topNav', function(){
	return {
		restrict: 'EA',
    	templateUrl: 'app/components/nav/top-nav.html'
	};
});

app.directive('rightNav', function(){
	return {
    	templateUrl: 'app/components/nav/right-nav.html'
	};
});



