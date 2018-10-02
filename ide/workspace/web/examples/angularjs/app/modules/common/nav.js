/*global app, RegisterMenuItems*/
app.controller('navController', function($scope) {
    var data = RegisterMenuItems();
    for(var k in data){
        if(data.hasOwnProperty(k) && data[k].items && data[k].items.length > 0){
            for (var i = 0; i < data[k].items.length; i++) {
                data[k].items[i].action = '#!' + data[k].items[i].action;
                if(data[k].items[i].items && data[k].items[i].items.length > 0){
                    for (var j = 0; j < data[k].items[i].items.length; j++) {
                        data[k].items[i].items[j].action = '#!' + data[k].items[i].items[j].action;
                    }
                }
            }
        }
    }
    $scope.data = data;
});