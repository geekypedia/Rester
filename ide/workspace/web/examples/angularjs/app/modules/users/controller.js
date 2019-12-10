/*global angular, app*/
app.controller('usersControllerExtension', function($scope, $controller, $rootScope, $http, $location, $uibModal, H, M, Popup) {

    if(!(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }

    $scope.onInit = function(){
        //$scope.newSingle(function(){
        $scope.data.single.password = H.getHash('pRESTige');    
        //});
    };
    
    $scope.onLoadAll = function(){
        $scope.setListHeaders(['Username', 'Email', 'Last Lease', 'Role']);
    }
    
    $scope.onLoad = function(){
		$scope.roleLocked = $rootScope.currentUser.organization.email == $scope.data.single.email;
    }
    
    
    $scope.setPassword = async function(callback, btn, data){

			var item = $scope.data.clickedUser;
			var newItem = data.newUserValues;
			if(newItem && newItem.success)	delete newItem.success;
			
			if(!newItem){
	                newItem = { error: "Please fill in the required fields!" };
	                return;
			}

			
	        if(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1){
	        	
	            if(newItem.admin_password == null || newItem.admin_password == ""){
	                newItem.error = M.ADMIN_PASSWORD_REQUIRED;
	                return;
	            }

	            if(newItem.password == null || newItem.password == ""){
	                newItem.error = M.PASSWORD_REQUIRED;
	                return;
	            }

	            if(newItem.password != newItem.confirm_password){
	                newItem.error = M.PASSWORD_NOT_MATCHING;
	                return;
	            }

	            var url = H.SETTINGS.baseUrl + '/users/set-password';
	            newItem.admin_email = $rootScope.currentUser.email;
	            newItem.secret = item.secret;
	            newItem.email = item.email;
	            
	            await $http.post(url, newItem)
	                .then(function(r){
	                    //$scope.data.clickedUser = {};
	                    //$scope.data.newUserValues = {};
	                    
	                    delete newItem.error;
	                    newItem.success = "Password changed successfully!";
	                    
	                    //$uibModalInstance.close('save');
	                    //$scope.loading = false;
	                },function(e){
	                	delete newItem.success;
	                    if(e && e.data && e.data.error && e.data.error.status){
	                        newItem.error = e.data.error.message ? e.data.error.message : e.data.error.status;    
	                    }
	                    //$scope.loading = false;
	                });
	                
			}
			
			//if(callback) callback(btn);
			
			

	}
    
    $scope.showSetPasswordDialog = async function(ev, item) {
    	

    	$scope.data.clickedUser = item;
        $scope.data.newUserValues = {};
        
        var body=`
<div id="set-password-form-container">
				    <form id="set-password-form">
					    <div flex="100" class="flex flex-col">
						    <div class="form-group row">
						      <label for="admin_password">{{M.FIELD_ADMIN_PASSWORD}}</label>
						      <input class="form-control"  name="admin_password" ng-model="data.newUserValues.admin_password" type="password" required>
						    </div>
						    <div class="form-group row">
						      <label for="password">{{M.FIELD_PASSWORD}}</label>
						      <input class="form-control"  name="password" ng-model="data.newUserValues.password" type="password" required>
						    </div>
						    <div class="form-group row">
						      <label for="confirm_password">{{M.FIELD_CONFIRM_PASSWORD}}</label>
						      <input class="form-control"  name="confirm_password" ng-model="data.newUserValues.confirm_password" type="password" required>
						    </div>
						    <div class="form-group row">
								<textarea ng-model="data.newUserValues.error" type="text" disabled rows="1" style="color:red; background: white; font-size: small; border: 0; width: 100%; resize:none"></textarea>
								<textarea ng-model="data.newUserValues.success" type="text" disabled rows="1" style="color:green; background: white; font-size: small; border: 0; width: 100%; resize:none"></textarea>
						    </div>
					    </div>
				    </form>   
</div>        
        `;

    	
    	Popup.show({
    		title: M.BTN_SET_PASSWORD,
    		buttons: [{
    			text: M.BTN_SAVE,
    			theme: "success",
    			submits: "set-password-form",
    			click: $scope.setPassword
    		}],
			close: function(data){
		        	$scope.data.clickedUser = {};
		        	$scope.data.newUserValues = {};
			},
    		// body: $('#set-password-form-container').html(),
    		body: body,
    		spinner: true,
    		scope: $scope
    	});
    	

        
    };
    
    $scope.data.roles = [{id: 'user', title: 'User'}, {id: 'admin', title: 'Administrator'}];

});
