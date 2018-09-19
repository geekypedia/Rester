app.controller('organizationsControllerExtension', function($scope, $controller, $rootScope, $http, $location, $mdDialog, H) {

    if($rootScope.currentUser.role !== 'superadmin'){
        $location.path('unauthorized');
    }
    
    $scope.checkLicenceValidity = function(item){return H.checkLicenseValidity(item) == 'valid' ? true : false };

    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.initSingle();
        $scope.data.single.org_secret = H.getUUID();  
    }
    
    $scope.currentOrganization = {};
    $scope.newOrganizationValues = {};
    
    $scope.activate = function(item, newItem) {
        if($rootScope.currentUser.role == 'superadmin'){
            var url = H.SETTINGS.baseUrl + '/organizations/activate';
            item.validity = (newItem.validity) ? H.toMySQLDateTime(newItem.validity) : item.validity;
            item.license = (newItem.license) ? newItem.license : item.license;
            $http.post(url, item)
                .then(function(r){
                    $scope.refreshData();
                    $scope.newOrganizationValues = {};
                    $scope.currentOrganization = {};
                    $mdDialog.cancel();   
                },function(r){
                    $scope.newOrganizationValues = {};
                    $scope.currentOrganization = {};
                    $mdDialog.cancel();   
                });
        }
    }
    
    $scope.showActivationDialog = function(ev, item) {
        $scope.currentOrganization = item;
        $mdDialog.show({
          contentElement: '#activationDialog',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose: false
        });
    };
    
    $scope.hideActivationDialog = function(){
        $scope.newOrganizationValues = {};
        $scope.currentOrganization = {};
        
        $mdDialog.cancel();            
    }


    
});