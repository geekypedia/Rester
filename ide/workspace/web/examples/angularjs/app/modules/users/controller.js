/*global angular, app*/
app.controller('usersControllerExtension', function($scope, $controller, $rootScope, $http, $location, $mdDialog, H) {
    
    
    if(!(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }

    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.data.single.password = H.getHash('admin');
    };
    
    $scope.setPassword = function(item, newItem) {
        if(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1){
            if(newItem.admin_password == null || newItem.admin_password == ""){
                newItem.error = "Admin Password is required!";
                return;
            }
            if(newItem.password == null || newItem.password == ""){
                newItem.error = "Password is required!";
                return;
            }
            if(newItem.password != newItem.confirm_password){
                newItem.error = "Password and Confirm Password should match!";
                return;
            }
            var url = H.SETTINGS.baseUrl + '/users/set-password';
            newItem.admin_email = $rootScope.currentUser.email;
            newItem.secret = item.secret;
            newItem.email = item.email;
            $scope.loading = true;
            $http.post(url, newItem)
                .then(function(r){
                    $scope.clickedUser = {};
                    $scope.newUserValues = {};
                    $mdDialog.cancel();   
                    $scope.loading = false;
                },function(e){
                    if(e && e.data && e.data.error && e.data.error.status){
                        newItem.error = e.data.error.message ? e.data.error.message : e.data.error.status;    
                    }
                    $scope.loading = false;
                });
        }
    };

    $scope.showSetPasswordDialog = function(ev, item) {
        $scope.clickedUser = item;
        $scope.newUserValues = {};        
        $mdDialog.show({
          contentElement: '#setPasswordDialog',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose: false
        });
    };
    
    $scope.hideSetPasswordDialog = function(){
        $scope.clickedUser = {};
        $scope.newUserValues = {};
        $mdDialog.cancel();            
    }   ; 
    
});