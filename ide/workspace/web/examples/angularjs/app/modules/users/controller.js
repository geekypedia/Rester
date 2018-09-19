app.controller('usersControllerExtension', function($scope, $controller, H) {
    
    $('select').formSelect();
    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.data.single.password = H.getHash('admin');
    }
});