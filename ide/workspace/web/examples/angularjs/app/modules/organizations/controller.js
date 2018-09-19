app.controller('organizationsControllerExtension', function($scope, $controller, $rootScope, $http, $location, H) {

    if($rootScope.currentUser.role !== 'superadmin'){
        $location.path('unauthorized');
    }

    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.initSingle();
        $scope.data.single.org_secret = H.getUUID();  
    }
    
    $scope.activate = function(item) {
        if($rootScope.currentUser.role == 'superadmin'){
            var url = H.SETTINGS.baseUrl + '/organizations/activate';
            $http.post(url, item)
                .then(function(r){
                    $scope.refreshData();
                },function(r){
                    
                });
        }
    }
    
});