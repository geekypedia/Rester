/*global angular, app*/
app.controller('organizationsControllerExtension', function($scope, $controller, $rootScope, $http, $location, $timeout, Popup, H, M) {

    if(!(['superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }
    
    $scope.checkLicenceValidity = function(item){return H.checkLicenseValidity(item) == 'valid' ? true : false };

    $scope.onInit = function(){
        //$scope.newSingle(function(){
            $scope.data.single.org_secret = H.getUUID();  
            $scope.data.single.license = 'basic';
            $scope.data.single.validity = '0000-01-01 00:00:00';
        //})
    };
    
    $scope.onLoadAll = function(){
        $scope.setListHeaders(['Organization', 'Email', 'License', 'Validity', 'Client Secret']);
    }
    
    $scope.data.currentOrganization = {};
    $scope.data.newOrganizationValues = {};
    $scope.data.newUserValues = {};
    $scope.data.plans = [{id: 'basic', title: 'Basic'}, {id: 'standard', title: 'Standard'}, {id: 'pro', title: 'Professional'}];

    

    $scope.showSetPasswordDialog = function(ev, item) {
        $scope.data.currentOrganization = item;

        var body=`
<div id="set-password-form-container">
				    <form id="set-password-form">
					    <div flex="100" class="flex flex-col">
						    <div class="form-group row">
						      <label for="admin_password">{{M.FIELD_SUPERADMIN_PASSWORD}}</label>
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
			    $scope.data.currentOrganization = {};
			    $scope.data.newOrganizationValues = {};
			    $scope.data.newUserValues = {};
			},
    		// body: $('#set-password-form-container').html(),
    		body: body,
    		spinner: true,
    		scope: $scope
    	});
    	

    };    
    
    $scope.setPassword = async function(callback, btn, data) {
		var item = $scope.data.currentOrganization;
		var newItem = data.newUserValues;
		
		if(newItem && newItem.success)	delete newItem.success;
		
			if(!newItem){
	                newItem = { error: "Please fill in the required fields!" };
	                return;
			}
		
        if($rootScope.currentUser.role == 'superadmin'){
            if(newItem.admin_password == null || newItem.admin_password == ""){
                newItem.error = "Super Admin Password is required!";
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
                    // $scope.currentOrganization = {};
                    // $scope.newUserValues = {};
                    //$mdDialog.cancel();   
                    //$scope.loading = false;
                    delete newItem.error;
                    newItem.success = "Password changed successfully!";

                },function(e){
                	delete newItem.success;
                    if(e && e.data && e.data.error && e.data.error.status){
                        newItem.error = e.data.error.message ? e.data.error.message : e.data.error.status;    
                    }
                    //$scope.loading = false;
                    //$scope.currentOrganization = {};
                    //$scope.newUserValues = {};
                    //$mdDialog.cancel();   
                });
        }
        
        //if(callback) callback(btn);
    }; 
    

    
    $scope.showActivationDialog = function(ev, item) {
        $scope.data.currentOrganization = item;

        var body=`
<div id="activation-form-container">
				    <form id="activation-form">
					    <div flex="100" class="flex flex-col">
						    <div class="form-group row">
						    	<label for="license" class="active">{{M.FIELD_LICENSE}}:</label>
								<div class="dropdown">
								  <button class="btn btn-default dropdown-toggle" type="button" id="license" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								    {{data.newOrganizationValues.license}}
								  </button>
								  <div class="dropdown-menu" aria-labelledby="license">
								    <a class="dropdown-item" ng-repeat="u in data.plans"  href="" ng-click="data.newOrganizationValues.license = u.id;">{{u.title}}</a>
								  </div>
								</div>
						    </div>
						    <div class="form-group row">
								<label for="validity">{{M.FIELD_VALIDITY}}:</label>
								<datepicker date-format="yyyy-MM-dd">
									 <input type="text" 
											name="validity" 
								        	class="form-control" 
								        	ng-model="data.newOrganizationValues.validity">
								</datepicker>							        
							</div>
						    <div class="form-group row">
								<textarea ng-model="data.newOrganizationValues.error" type="text" disabled rows="1" style="color:red; background: white; font-size: small; border: 0; width: 100%; resize:none"></textarea>
								<textarea ng-model="data.newOrganizationValues.success" type="text" disabled rows="1" style="color:green; background: white; font-size: small; border: 0; width: 100%; resize:none"></textarea>
						    </div>
					    </div>
				    </form>   
</div>        
        `;
        
        var data = { newOrganizationValues: H.deepCopy(item), plans: $scope.data.plans }
        data.newOrganizationValues.validity = H.toMySQLDate(new Date(Date.parse(data.newOrganizationValues.validity)), true);

    	Popup.show({
    		title: M.BTN_ACTIVATE,
    		buttons: [{
    			text: M.BTN_SAVE,
    			theme: "success",
    			submits: "activation-form",
    			click: $scope.activate
    		}],
			close: function(data){
			    $scope.data.currentOrganization = {};
			    $scope.data.newOrganizationValues = {};
			    $scope.data.newUserValues = {};
			},
    		// body: $('#set-password-form-container').html(),
    		body: body,
    		data: data,
    		spinner: true,
    		scope: $scope
    	});
    	        
        
    };
    
    $scope.activate = function(callback, btn, data) {
		var item = $scope.data.currentOrganization;
		var newItem = data.newOrganizationValues;
    	
        if($rootScope.currentUser.role == 'superadmin'){
            //$scope.loading = true;
            var url = H.SETTINGS.baseUrl + '/organizations/activate';
            item.validity = (newItem.validity) ? H.toMySQLDateTime(new Date(newItem.validity)) : item.validity;
            item.license = (newItem.license) ? newItem.license : item.license;
            $http.post(url, item)
                .then(function(r){
                    $scope.refreshData();
                    $scope.data.newOrganizationValues = {};
                    $scope.data.currentOrganization = {};
                    delete newItem.error;
                    newItem.success = "License updated successfully!";
                    //$mdDialog.cancel();   
                    //$scope.loading = false;
                },function(e){
                	delete newItem.success;
                    if(e && e.data && e.data.error && e.data.error.message){
                        if(e.data.error.code == 404){
                            newItem.error =  M.SAAS_API_UNAVAILABLE;
                        } else {
                            newItem.error = e.data.error.message;
                        }
                    }
                    //$scope.newOrganizationValues = {};
                    //$scope.currentOrganization = {};
                    //$mdDialog.cancel();   
                    //$scope.loading = false;
                });
        }
    };
        

    //$scope.data.roles = [{id: 'user', title: 'User'}, {id: 'admin', title: 'Administrator'}];
    
    GLOBALS.methods.autoFocus();
    
});
