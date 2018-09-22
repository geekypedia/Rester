app.controller('usersControllerExtension', function($scope, $controller, $rootScope, $location, H) {
    
    
    if(!(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }

    
    $('select').formSelect();
    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.data.single.password = H.getHash('admin');
    }
    
});