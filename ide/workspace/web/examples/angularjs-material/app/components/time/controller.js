/*global app*/
app.component('time', {
	templateUrl: 'app/components/time/template.html',
	controller: 'timeController',
	bindings: {
		value: '<',
		label: '<'
	}
});

app.controller('timeController', function($scope){
	// $scope.options = {
	// 	title: 'options.title',
	// 	value: 'options.value',
	// 	icon: 'options.icon',
	// 	background: 'bgblueish',
	// 	color: 'white-text',
	// 	action: 'options.action'
	// };

	var self = this;
	self.$onInit = function(){
		if(self.value){
			$scope.value = self.value;
			$scope.hh = $scope.value.substring(1, 3);
			$scope.mm = $scope.value.substring(4);
		}
		else{
			$scope.hh = "00";
			$scope.mm = "00";
		}
		if(self.label){
			$scope.label = self.label;
		}
	};
	
});