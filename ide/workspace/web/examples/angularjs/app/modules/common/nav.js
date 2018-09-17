app.controller('navController', function($scope) {
    var data = RegisterMenuItems();
    for(var k in data){
        if(data.hasOwnProperty(k)){
            for (var i = 0; i < data[k].length; i++) {
                data[k][i].action = '#!' + data[k][i].action;
            }
        }
    }
    $scope.data = data;
});
