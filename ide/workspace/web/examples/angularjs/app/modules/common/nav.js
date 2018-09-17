app.controller('navController', function($scope) {
    var menuItems = RegisterMenuItems();
    var data = {
        userMenu: menuItems[0],
        adminMenu: menuItems[1],
        superAdminMenu: menuItems[2]
    }
    for(var k in data){
        if(data.hasOwnProperty(k)){
            for (var i = 0; i < data[k].length; i++) {
                data[k][i].action = '#!' + data[k][i].action;
            }
        }
    }
    $scope.data = data;
});
