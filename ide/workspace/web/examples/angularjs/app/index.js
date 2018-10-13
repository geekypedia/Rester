/*global angular*/
//Initialize app
var app = angular.module('app', [
							'ngRoute', 
							'ngResource', 
							'ngCookies', 
							'ui.grid', 
							'ui.grid.resizeColumns', 
							'ui.grid.moveColumns', 
							'ui.grid.selection', 
							'ui.grid.exporter', 
							'ui.grid.autoResize', 
							'ngMaterial', 
							'ngMessages',
							'angular-md5'
							]);


/*global $*/
//JQuery
$(function() {
	$('.sidenav').sidenav({
		closeOnClick: true
	});
	
	$(document).ready(function(){
    	$('.collapsible').collapsible();
	});	
	
	$('select').formSelect();
});
/*global app, RegisterRoutes*/
app.factory('httpRequestInterceptor', function ($rootScope, $q) {
    return {
        request: function (config) {
            $rootScope.loading = true;
            if ($rootScope.currentUser) {
                config.headers['api-key'] = $rootScope.currentUser.token;
                
                if($rootScope.SETTINGS.enableSaaS){
                    if(config.method == "GET" || config.method == "DELETE" || config.method == "PUT"){
                    	var m = config.url.match(/\.[0-9a-z]+$/i);
                        if(m && m.length > 0){
                        }else{
                        	var idx = config.url.lastIndexOf("/");
                        	var idt  = config.url.substr(idx);
	                        if(config.method == "PUT" && isNaN(idt)){
	                        	config.data.secret = $rootScope.currentUser.secret;
	                        }else{
	                            var secret = '/?secret=';
	                            if(config.url.endsWith('/')) secret = '?secret=';
	                            if(config.url.indexOf('?') > -1) secret = '&secret=';
	                            config.url = config.url + secret + $rootScope.currentUser.secret;
	                        }
                        }
                    }
                    else{
                        config.data.secret = $rootScope.currentUser.secret;
                    }
                }
            }
            return config;
        },
        response: function(response){
            //if(response.headers()['content-type'] === "application/json; charset=utf-8"){
                $rootScope.loading = false;
            //}
            return response;            
        },
        responseError: function(response){
            $rootScope.loading = false;
            if(response.status === 401){
            	$rootScope.$emit('loginRequired');
            }
            return $q.reject(response);
        }
    };
});

function CustomRoutes(){
    this.routes = RegisterRoutes();
}

app.provider('customRoutes', function() {
    Object.assign(this, new CustomRoutes());

    this.$get = function() {
        return new CustomRoutes();
    };
});

app.config(function ($routeProvider, $resourceProvider, $httpProvider, customRoutesProvider) {
    var routes = customRoutesProvider.routes.customRoutes;

    var easyRoutes = customRoutesProvider.routes.easyRoutes;
    for (var i = 0; i < easyRoutes.length; i++) {
        var r = easyRoutes[i];
        routes.push({route: r, template: 'common/templates/list', controller: r});
        routes.push({route: r + '/new', template: 'common/templates/new', controller: r});
        routes.push({route: r + '/:id', template: 'common/templates/edit', controller: r});
    }

    for (var i = 0; i < routes.length; i++) {
        var r = routes[i];
        $routeProvider.when('/' + r.route, { templateUrl: 'app/modules/' + r.template + '.html', controller: r.controller + 'Controller'});
    }
    

    $httpProvider.interceptors.push('httpRequestInterceptor');
});

app.run(function ($rootScope, $location, $cookies, H) {
    $rootScope.SETTINGS = H.SETTINGS;

    $rootScope.fieldTypes = H.SETTINGS.fieldTypes;
    
    $rootScope.openRoutes = H.getOpenRoutes();

    $rootScope.$on("$locationChangeStart", function (event, next, current) {
        if (!$rootScope.currentUser) {
            
            var cookie = $cookies.get(H.getCookieKey());
            if (!cookie) {
                if($rootScope.openRoutes.indexOf($location.path()) > -1){} else {
                    $location.path('/sign-in');
                }
            }
            else {
                var cu = JSON.parse(cookie);
                $rootScope.currentUser = typeof cu==='string'? JSON.parse(cu):cu;
            }
        }
    });
    
    $rootScope.$on("loginRequired", function (event, next, current) {
    	$cookies.remove(H.getCookieKey());
		delete $rootScope.currentUser;
		$location.path('/sign-in');
    });
    
});//ControllerFactory helps wrap basic CRUD operations for any API resource
function ControllerFactory(resourceName, options, extras) {
	return function($scope, $rootScope, $http, $routeParams, $location, $mdDialog, H, M, S, R) {
		//Get resource by name. Usually it would be you API i.e. generated magically from your database table.
		var Resource = H.R.get(resourceName);

		//Scope variables
		$scope.data = {};
		$scope.data.single = new Resource();
		$scope.data.list = [];
		$scope.data.limit = 10;
		$scope.data.currentPage = 1;
		$scope.data.pages = [];
		$scope.errors = [];
		$scope.MODES = {
			'view': 'view',
			'edit': 'edit',
			'add': 'add'
		};
		$scope.mode = $scope.MODES.view;
		$scope.locked = true;
		$scope.forms = {};
		$scope.H = H;
		$scope.M = M;

		//Set currentRoute
		$scope.currentRoute = (function(){
			var route = $location.path().substring(1);
			var slash = route.indexOf('/');
			if(slash > -1){
				route = route.substring(0, slash);
			}
			return route;
		})();
		
		$scope.currentRouteHref = "#!" + $scope.currentRoute;
		$scope.newRouteHref = "#!" + $scope.currentRoute + "/new";
		$scope.editRouteHref = "#!" + $scope.currentRoute + "/:id";


		//Default error handler
		var errorHandler = function(error) {
			if (error && error.status) {
				switch (error.status) {
					case 404:
						$scope.errors.push({
							message: H.MESSAGES.E404
						});
						break;
					case 422:
						$scope.errors.push({
							message: H.MESSAGES.E422
						});
						break;
					case 405:
						$scope.errors.push({
							message: H.MESSAGES.E405
						});
						break;
					case 400:
						$scope.errors.push({
							message: H.MESSAGES.E400
						});
						break;
					case 500:
						$scope.errors.push({
							message: H.MESSAGES.E500
						});
						break;
					case 401:
						$rootScope.$emit('loginRequired');
					case 403:
						$location.path('unauthorized');
					default:
						$scope.errors.push({
							message: H.MESSAGES.E500
						});
						break;
				}
			}
		};

		//Initializa new single objetc
		$scope.initSingle = function() {
			$scope.data.single = new Resource();
		};

		//Get all rows from your API/table. Provide a query filter in case you want reduced dataset.
		$scope.query = function(q, callback) {
			if (!q) {
				q = {};
			}
			Resource.query(q, function(result) {
				if (result) {
					$scope.data.list = result;
				}
				if (callback) {
					callback(result);
				}
			}, function(error) {
			    errorHandler(error);
				if (callback) {
					callback(error);
				}
			});
		};
		
		//Get specific record
		$scope.count = function(callback) {
			Resource.query({
				count: true
			}, function(result) {
				$scope.data.records = result[0].count;
				if (callback) {
					callback(result);
				}
			}, function(error) {
			    errorHandler(error);
				if (callback) {
					callback(error);
				}
			});
		};
		

		//Get specific record
		$scope.get = function(id, callback) {
			Resource.get({
				id: id
			}, function(result) {
				$scope.data.single = result;
				if (callback) {
					callback(result);
				}
			}, function(error) {
			    errorHandler(error);
				if (callback) {
					callback(error);
				}
			});
		};

		//Delete specific record
		$scope.delete = function(obj, callback) {
			if (obj && obj.$delete) {
				if(S.legacyMode){
					$http.post(S.baseUrl + "/" + resourceName + "/delete/", obj).then(function(r){
						if (callback && r.data) {
							callback(r.data);
						}
					}, function(e){
						errorHandler(e);
						if (callback) {
							callback(e);
						}
					});
				} else {
					obj.$delete(function(r) {
						if (callback) {
							callback(r);
						}
					}, function(e) {
						errorHandler(e);
						if (callback) {
							callback(e);
						}
					});					
				}

			} else if (!isNaN(obj)) {
				$scope.get(obj, function(result) {
					if (result && result.$delete) {
						result.$delete();
						if (callback) {
							callback();
						}
					}
				});
			}
		};

		//Save a record
		$scope.save = function(obj, callback) {
			if (obj && obj.$save) {
				var promise = obj.$save();
				promise.then(function(r) {
					if (callback) {
						callback(r);
					}
				}, function(e){
					errorHandler(e);
					if (callback) {
						callback(e);
					}
				});
			} else if ($scope.data.single) {
				var promise = $scope.data.single.$save();
				promise.then(function(r) {
					if (callback) {
						callback(r);
					}
				}, function(e){
					errorHandler(e);
					if (callback) {
						callback(e);
					}
				});
			}
		};
		
		$scope.update = function(obj, callback) {
			var url = H.SETTINGS.baseUrl + "/" + resourceName;
			
			if(H.S.legacyMode){
				$http.post(url + "/update", obj)
				.then((function (data, status, headers, config) {
					if (callback) {
						callback(data);
					}
	            }), (function (e) {
	            	errorHandler(e);
					if (callback) {
						callback(e);
					}
				}));
			} else {
				$http.put(url, obj)
				.then((function (data, status, headers, config) {
					if (callback) {
						callback(data);
					}
	            }), (function (e) {
	            	errorHandler(e);
					if (callback) {
						callback(e);
					}
				}));
				
			}
			
		};

		//Clear errors
		$scope.clearErrors = function() {
			$scope.errors = [];
		};

		//Refresh data
		$scope.refreshData = function() {
			$scope.listAll();
		};
		
		$scope.setActive = function(i){
			return ($rootScope.currentPage == i) ? 'active' : 'waves-effect';
		};
	
		//Load all entries on initialization
		$scope.listAll = function(currentPage){
			//$scope.loading = true;
			$scope.count(function(){
				$scope.loading = true;
				$scope.data.pagesCount = parseInt(($scope.data.records - 1)/ $scope.data.limit) + 1;
				$scope.data.pages = [];
				for (var i = 0; i < $scope.data.pagesCount; i++) {
					$scope.data.pages.push(i + 1);
				}
				if(!currentPage){
					if(!($scope.data.pages.indexOf($rootScope.currentPage) > -1)){
						if($rootScope.currentPage > 0){
							$rootScope.currentPage = $scope.data.pages[$scope.data.pagesCount - 1];
						} else {
							$rootScope.currentPage = 1;
						}
					}
				} else {
					$rootScope.currentPage = currentPage;
				}
			    $scope.query({limit: $scope.data.limit, offset: ($rootScope.currentPage - 1) * $scope.data.limit}, function(r) {
			    	$scope.loading = false;
			    	if($scope.onLoad) $scope.onLoad(r);
			    });
				
			});
		};
		
		//Load entry on initialization
		$scope.loadSingle = function(callback){
			//$scope.loading = true;
		    $scope.get($routeParams.id, function(r) {
		    	if($scope.onLoadSingle) $scope.onLoadSingle(r);
		    	if(callback) callback(r);
		    	//$scope.loading = false;
		    });
		};
		
		
		//Toggle Visibility
		$scope.toggleVisibility = function(item){
		    item.visible = !item.visible;
		};
	
		//Toggle lock
	    $scope.toggleLock = function(){
	        $scope.locked = !$scope.locked;
	    };
	    
	    //Update a single record
	    $scope.updateSingle = function(callback){
			//$scope.loading = true;
	    	if($scope.beforeUpdate) {
	    		$scope.beforeUpdate(function(r){
		    	var update = true;
		    	if($scope.beforeUpdateBase) update = $scope.beforeUpdateBase();
			    	if(update){
				        $scope.update($scope.data.single, function(r){
				            $scope.locked = true;
				            if($scope.onUpdate) {
				            	$scope.onUpdate(r, function(r){
				            		if($scope.onUpdateBase) $scope.onUpdateBase();		
				            	});
				            } else {
				            	if($scope.onUpdateBase) $scope.onUpdateBase();		
				            }
		                    
				            if(callback) callback(r);
							//$scope.loading = false;
				        });
			    	}
	    			
	    		});            
	    	} else {
		    	var update = true;
		    	if($scope.beforeUpdateBase) update = $scope.beforeUpdateBase();
		    	if(update){
				        $scope.update($scope.data.single, function(r){
				            $scope.locked = true;
				            if($scope.onUpdate) {
				            	$scope.onUpdate(r, function(r){
				            		if($scope.onUpdateBase) $scope.onUpdateBase();		
				            	});
				            } else {
				            	if($scope.onUpdateBase) $scope.onUpdateBase();		
				            }
		                    
				            if(callback) callback(r);
							//$scope.loading = false;
				        });
		    	}
	    	}
	    };	    
	    //Initialize a single record
	    $scope.newSingle = function(callback){
	    	$scope.locked = false;
	    	$scope.initSingle();
	    	if(callback) callback();
	    };
	    
	    //Save a new single record
	    $scope.saveSingle = function(callback){
	    	//$scope.loading = true;
	    	
	    	if($scope.beforeSave) {
	    		$scope.beforeSave(function(r){
	    			var save = true;
	    			if($scope.beforeSaveBase) save = $scope.beforeSaveBase();
	    			if(save){
				        $scope.save($scope.data.single, function(r){
				            $scope.locked = true;
				            if($scope.onSave) {
				            	$scope.onSave(r, function(r){
				            		if($scope.onSaveBase) $scope.onSaveBase();
				            	});
				            } else {
				            	if($scope.onSaveBase) $scope.onSaveBase();	
				            }
				            
				            if(callback) callback(r);
				    		//$scope.loading = false;
				        });
	    			}
	    		});	
	    	} else {
		    	var save = true;
		    	if($scope.beforeSaveBase) save = $scope.beforeSaveBase();
		    	if(save){
			        $scope.save($scope.data.single, function(r){
			            $scope.locked = true;
			            if($scope.onSave) {
			            	$scope.onSave(r, function(r){
			            		if($scope.onSaveBase) $scope.onSaveBase();
			            	});
			            } else {
			            	if($scope.onSaveBase) $scope.onSaveBase();	
			            }
			            
			            if(callback) callback(r);
			    		//$scope.loading = false;
			        });
		    	}    		
	    	}

	    };
	    
	    //Change a property in single
	    $scope.changeSingle = function(property, value){
	    	this.data.single[property] = value;
	    };
		

		/*Define options
			init:true -> Load all records when the controller loads
		*/
		if (options) {
			$scope.options = options;
			if ($scope.options.init) {
				$scope.query();
			}
		}

		//Any extra stuff you might want to merge into the data object
		if (extras) {
			for (var e in extras) {
				$scope.data[e] = extras[e];
			}
		}
		
		
		//Localized resources
		$scope.textResources = {
			title: {
				single: '',
				list: ''
			},
			templates: {
				edit: '',
				create: '',
				list: ''
			}
		};
		
		$scope.initTextResources = function(listTitle, singleTitle, listTemplate, listItemTemplate, newTemplate, editTemplate){
			$scope.textResources.title.list = listTitle;
			$scope.textResources.title.single = singleTitle;
			$scope.textResources.templates.list = listTemplate;
			$scope.textResources.templates.listItem = listItemTemplate;
			$scope.textResources.templates.create = newTemplate;
			$scope.textResources.templates.edit = editTemplate;
		};		
		
		$scope.initTextResourcesEasy = function(route, singular){
			if(!route || route == '') {
				route = $scope.currentRoute;
			}
			var plural = route.toUpperCase();
			if(!singular || singular == '') singular = plural.substring(0, plural.length - 1);
			var listTemplate = 'app/modules/' + route + '/list.html';
			var listItemTemplate = 'app/modules/' + route + '/list-item.html';
			var singleTemplate = 'app/modules/' + route + '/single.html';
		
			$scope.initTextResources(plural, singular, listTemplate, listItemTemplate, singleTemplate, singleTemplate);
		};

		$scope.getTitle = function(t){
			switch (t) {
				case 'single':
					return $scope.textResources.title.single;
				case 'list':
					return $scope.textResources.title.list;
				default:
					return $scope.textResources.title.list;
			}
		}
		
		$scope.getTemplate = function(t){
			switch (t) {
				case 'edit':
					return $scope.textResources.templates.edit;	
				case 'new':
					return $scope.textResources.templates.create;	
				case 'list':
					return $scope.textResources.templates.list;	
				case 'list-item':
					return $scope.textResources.templates.listItem;	
				default:
					return $scope.textResources.templates.edit;	
			}
			
		};
		
		$scope.getTableHeaders = function(){
			var headers = [];
			if($scope.data.list && $scope.data.list.length > 0 && $scope.data.list[0]){
				headers = Object.getOwnPropertyNames($scope.data.list[0]);
			}
			return headers;
		};
		
		$scope.setListHeaders = function(headers){
			$scope.data.listHeaders = headers;
		};
		
		 $scope.showDialog = function(ev, title, content, okText = "OK", cancelText = "Cancel", okHandler, cancelHandler) {
		    var confirm = $mdDialog.confirm()
		          .title(title)
		          .textContent(content)
		          .ariaLabel('')
		          .targetEvent(ev)
		          .ok(okText)
		          .cancel(cancelText);
		
		    $mdDialog.show(confirm).then(function() {
		      if(okHandler) okHandler();
		    }, function() {
		      if(cancelHandler) cancelHandler();
		    });
		  };

	    $scope.onSaveBase = $scope.onUpdateBase = function(){
	        $scope.showDialog(null, M.SAVED_TITLE, M.SAVED_MESSAGE,M.SAVED_OK, M.SAVED_CANCEL, function(){}, function(){$location.path($scope.currentRoute)});
	    };
	    
	    $scope.beforeSaveBase = $scope.beforeUpdateBase = function(){
	        return (!Object.keys($scope.forms[$scope.currentRoute + "Form"].$error).length);
	    };
	};
}
/*global app, ControllerFactory, RegisterRoutes, RegisterData*/
function RegisterEasyController(route, headers, controller){
	app.controller(route + 'ControllerBase', ControllerFactory(route));
	
	app.controller(route + 'Controller', function($scope, $controller, H) {
		//Copy all scope variables from Base Controller
		$controller(route + 'ControllerBase', {
			$scope: $scope
		});
		try{
			$controller(route + 'ControllerExtension', {
				$scope: $scope
			});
		} catch (ex){
			
		}
		
		$scope.initTextResourcesEasy();
		
		$scope.setListHeaders(headers);
		
	});
}

//Register Easy Routes
(function(){
    var easyRoutes = RegisterRoutes().easyRoutes;
    var data = RegisterData();
    
    for (var i = 0; i < easyRoutes.length; i++) {
        RegisterEasyController(easyRoutes[i], data[easyRoutes[i]].headers);
    }
})();function RegisterData(){
    return{
        organizations: {
            headers: ['Organization', 'Email', 'License', 'Validity', 'Client Secret', 'Actions']
        },
        users: {
            headers: ['Username', 'Email', 'Last Lease', 'Role', 'Actions']
        },
        groups: {
            headers: ['Title']
        }
    };
}function RegisterMenuItems(){
    return [
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: '', icon: 'home',text: 'Home'}
	        ],
	        allowedRoles: ['user', 'admin', 'superadmin']
        },
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'tasks', icon: 'assignment_turned_in',text: 'Tasks'},
        	    {action: 'search', icon: 'search',text: 'Search'},
        	    {action: 'reports', icon: 'pie_chart',text: 'Reports'},
        	    {action: 'alerts', icon: 'alarm',text: 'Alerts'}
	        ],
	        allowedRoles: ['user', 'admin']
        },
        {
            header: 'Administration',
            showHeader: true,
            showSeparator: true,
            items: [
        	    {action: 'settings', icon: 'settings',text: 'Settings'},
        	    {action: 'categories', icon: 'list',text: 'Categories'},
        	    {action: 'users', icon: 'person',text: 'Users'},
        	    {action: 'groups', icon: 'group',text: 'Groups'}
	        ],
	        allowedRoles: ['admin']
        },
        {
            header: 'Customer Management',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'organizations', icon: 'people_outline',text: 'Organizations'}
	        ],
	        allowedRoles: ['superadmin']
        }
    ];
}/*global app*/
app.service('M', function($http) {
	return {
		"E404": "The resource you are trying to access does not exist!",
		"E422": "Invalid parameters!",
		"E405": "Invalid operation!",
		"E400": "Bad request!",
		"E500": "An error accured!",
		"LOGIN_API_UNAVAILABLE": "Please contact the administrator. It seems that the login services are not enabled!",
		"REGISTER_API_UNAVAILABLE": "Please contact the administrator. It seems that the registration services are not enabled!",
		"SAAS_API_UNAVAILABLE": "Please contact the administrator. It seems that the SaaS services are not enabled!",
		"REQUIRED": "This field is required!",
		"INVALID_EMAIL": "Invalid email!",
		"UNAUTHORIZED_AREA": "You are not authorized to access this area!",
		"NA": "N/A",
		"SAVED_TITLE": "Item Saved!",
		"SAVED_MESSAGE": "You have successfully saved this record!",
		"SAVED_OK": "Stay Here",
		"SAVED_CANCEL": "Go Back To Listing",
		"RECOVERY_EMAIL_SENT": "We have sent instructions to your registered e-mail address. Please check your spam folder.",
		"REGISTRATION_EMAIL_SENT": "We have sent your request for approval. This usually takes upto 72 hours, but usually our approval panel is very quick to respond. You will soon get an activation email. Please keep checking your spam folder.",
		"PROFILE_SAVED": "Profile information updated successfully!",
		"PROFILE_SAVE_ERROR": "Could not save profile!",
		"PASSWORD_CHANGED": "Changed password successfully!",
		"PASSWORD_CHANGE_ERROR": "Could not change password!",
		"ADMIN_PASSWORD_REQUIRED": "Admin Password is required!",
		"PASSWORD_REQUIRED": "Password is required!",
		"PASSWORD_NOT_MATCHING": "Password and Confirm Password should match!",
		"TITLE_ADD_PREFIX": "ADD",
		"TITLE_EDIT_PREFIX": "EDIT",
		"TITLE_DASHBOARD": "DASHBOARD",
		"TITLE_LICENSE": "License",
		"BTN_SAVE": "Save",
		"BTN_UPDATE": "Update",
		"BTN_EDIT": "Edit",
		"BTN_SUBMIT": "Submit",
		"BTN_OK": "OK",
		"BTN_CANCEL": "Cancel",
		"BTN_LOGIN": "Login",
		"BTN_RECOVER": "Recover",
		"BTN_REGISTER": "Register",
		"BTN_SHOW": "Show",
		"BTN_CHANGE_LICENSE": "Change License",
		"BTN_SET_PASSWORD": "Set Password",
		"BTN_ACTIVATE": "Activate",
		"HEADING_LOGIN": "Please, sign into your account",
		"HEADING_FORGOT_PASSWORD": "Forgot your password?",
		"HEADING_REGISTER": "Register now!",
		"LNK_REGISTER": "Register",
		"LNK_FORGOT_PASSWORD": "Forgot password?",
		"LNK_BACK_TO_LOGIN": "Back to Sign-in",
		"FIELD_EMAIL_ENTER": "Enter your email",
		"FIELD_PASSWORD_ENTER": "Enter your password",
		"FIELD_ORGANIZATION": "Organization",
		"FIELD_ROLE": "Role",
		"FIELD_EMAIL": "Email",
		"FIELD_USERNAME": "Username",
		"FIELD_PASSWORD": "Password",
		"FIELD_NEW_PASSWORD": "New Password",
		"FIELD_CONFIRM_PASSWORD": "Confirm Password",
		"FIELD_ADMIN_PASSWORD": "Admin Password",
		"FIELD_SUPERADMIN_PASSWORD": "Super Admin Password",
		"FIELD_CLIENT_SECRET": "Client Secret",
		"FIELD_VALIDITY": "Validity",
		"FIELD_LICENSE": "License",
		"FIELD_GROUPNAME": "Group Title",
		"FIELD_ACTIVE": "Active"
	};
});function RegisterRoutes() {
    return {
        customRoutes: [
            {route: '', template: 'home/template', controller: 'home'},
            {route: 'sign-in', template: 'auth/sign-in', controller: 'auth', auth: false},
            {route: 'forgot-password', template: 'auth/forgot-password', controller: 'auth', auth: false},
            {route: 'register', template: 'auth/register', controller: 'auth', auth: false},
            {route: 'profile', template: 'auth/profile', controller: 'profile'},
            {route: 'unauthorized', template: 'auth/unauthorized', controller: 'unauthorized'}
        ],
        easyRoutes: ['organizations', 'users', 'groups']
    };
}/*global app*/
app.service('S', function($http) {
	return {
		"baseUrl": "../../../../../api",
		"productName": "pRESTige",
		"supportEmail": "support@prestigeframework.com",
		"enableSaaS": true,
		"openRegistration": true,
		"legacyMode": false
	}
});/*global app, RegisterRoutes*/
app.service('H', function($location, md5, S, M, R) {
	return {
		S: S,
		SETTINGS: S,
		M: M,
		MESSAGES: M,
		R: R,
		RESOURCES: R,
		getCookieKey: function(){
			var absUrl = $location.absUrl();
			Helper.getCookieKey(absUrl);
		},
		getHash: function(str){
    		return md5.createHash(str);
		},
		getAbsolutePath: Helper.getAbsolutePath,
		getRandomNumber: Helper.getRandomNumber,
		getUUID: Helper.getUUID,
		toDateTime: Helper.toDateTime,
		toMySQLDateTime: Helper.toMySQLDateTime,
		checkLicenseValidity: Helper.checkLicenseValidity,
		getOpenRoutes: function(){
			var openRoutes = RegisterRoutes().customRoutes.filter(p => p.auth === false);
			var openRouteNames = [];
			openRoutes.forEach(p => openRouteNames.push("/" + p.route));
			return openRouteNames;
		}
	};
});

class Helper {

	constructor() {
	}

	static getCookieKey(absUrl) {
		var startIndex = absUrl.indexOf("//") + 2;
		var endIndex = absUrl.indexOf("#");
		var base = absUrl.substring(startIndex, endIndex);
		var pattern = /[\s:/!@#\$%\^\&*\)\(+=.-]/g;
		var key = base.replace(pattern, "_");
		return key;
	}
	
	static getAbsolutePath(href) {
	    var link = document.createElement("a");
	    link.href = href;
	    return link.href;
	}

	static getRandomNumber(min, max) {
    	return Math.floor(Math.random() * (max - min + 1)) + min;
	}

	static getUUID() {
	      var id = '', i;
	
	      for(i = 0; i < 36; i++)
	      {
	        if (i === 14) {
	          id += '4';
	        }
	        else if (i === 19) {
	          id += '89ab'.charAt(this.getRandomNumber(0,3));
	        }
	        else if(i === 8 || i === 13 || i === 18 || i === 23) {
	          id += '-';
	        }
	        else
	        {
	          id += '0123456789abcdef'.charAt(this.getRandomNumber(0, 15));
	        }
	      }
	      return id;
	}
	
	static toDateTime(str){
		// Split timestamp into [ Y, M, D, h, m, s ]
		var t = str.split(/[- :]/);
		
		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		
		return d;
	}
	
	static toMySQLDateTime(dt){
		return dt.getUTCFullYear() + "-" + Helper.twoDigits(1 + dt.getUTCMonth()) + "-" + Helper.twoDigits(dt.getUTCDate()) + " " + Helper.twoDigits(dt.getUTCHours()) + ":" + Helper.twoDigits(dt.getUTCMinutes()) + ":" + Helper.twoDigits(dt.getUTCSeconds());
	}
	
	static twoDigits(d) {
	    if(0 <= d && d < 10) return "0" + d.toString();
	    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
	    return d.toString();
	}
	
	static checkLicenseValidity(organization){
		return ((new Date() > Helper.toDateTime(organization.validity) && !(['basic', 'super'].indexOf(organization.license) > -1))  || !organization.is_active ) ? 'expired' : 'valid';
	}

}/*global app*/
//Service for quickly getting the API Resource Object
app.service('R', function($resource, $http, S) {
	return {
		get: function(resourceName) {
			return $resource(S.baseUrl + '/' + resourceName + '/:id', {
				id: '@id'
			});
		},
		count: function(resourceName, cb) {
			$http.get(S.baseUrl + '/' + resourceName + '/?count=true')
				.then(function(results) {
					if (results && results.data && results.data.length > 0)
						if (cb) cb(results.data[0].count);
				}, function(e) {});
		}
	};
});/*global app*/
app.directive('focusOn', ['$timeout',
    function ($timeout) {
        var checkDirectivePrerequisites = function (attrs) {
          if (!attrs.focusOn && attrs.focusOn != "") {
                throw "FocusOn missing attribute to evaluate";
          }
        };

        return {            
            restrict: "A",
            link: function (scope, element, attrs, ctrls) {
                checkDirectivePrerequisites(attrs);

                scope.$watch(attrs.focusOn, function (currentValue, lastValue) {
                    if(currentValue == true) {
                        $timeout(function () {    
                            element.focus();
                        });
                    }
                });
            }
        };
    }
]);/*global app*/
app.directive('spinner', function($rootScope) {
  return {
    scope: {
      size: '='
    },
    restrict: 'E',
    replace: true,
    template: '<img src="images/spinner.gif" ng-if="$root.loading" style="width:13px;height:13px"></img>'
  };
});/*global app, Helper*/
app.filter('checkLicenseValidity', function() {
    return function(organization) {
        return Helper.checkLicenseValidity(organization);
        //return new Date();
    };
});/*global app*/
app.filter('lowerCase', function() {
    return function(input) {
      input = input || '';
      return input.replace(/\w\S*/g, function(txt){return txt.toLowerCase();});
    };
});  /*global app*/
app.filter('titleCase', function() {
    return function(input) {
      input = input || '';
      return input.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    };
});/*global app, Helper*/
app.filter('toDateTime', function() {
    return function(str) {
        return Helper.toDateTime(str);
    };
});/*global app*/
app.filter('upperCase', function() {
    return function(input) {
      input = input || '';
      return input.replace(/\w\S*/g, function(txt){return txt.toUpperCase();});
    };
});/*global app*/
app.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;

            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);/*global app*/
app.component('infoBox', {
	templateUrl: 'app/components/infobox/template.html',
	controller: 'infoboxController',
	bindings: {
		options: '<',
	}
});

app.controller('infoboxController', function($scope){
	// $scope.options = {
	// 	title: 'options.title',
	// 	value: 'options.value',
	// 	icon: 'options.icon',
	// 	background: 'bgblueish',
	// 	color: 'white-text',
	// 	action: 'options.action'
	// };

	var self = this;
	self.$onInit = function(){
		if(self.options){
			$scope.options = self.options;
		}
	};
	
});/*global app, $, M*/
app.component('modal', {
	templateUrl: 'app/components/modal/template.html',
	controller: 'modalController',
	bindings: {
		options: '=',
	}
});

app.controller('modalController', function($scope){
	var self = this;
	self.$onInit = function(){
		if(self.options){
			$scope.options = self.options;
			$scope.options.open = openModal;
		}
		else{
			$scope.options = {};
			$scope.options.open = openModal;
		}
	};
	
	$(function(){
		self.modal = M.Modal.init(document.querySelector('#mdmodal'));
	});

	function openModal(options){
		if(options){
			$scope.options = options;
			$scope.options.open = openModal;
		}
		
		self.modal.open();
	}

});/*global app*/
app.component('time', {
	templateUrl: 'app/components/time/template.html',
	controller: 'timeController',
	bindings: {
		value: '<',
		label: '<'
	}
});

app.controller('timeController', function($scope){
	// $scope.options = {
	// 	title: 'options.title',
	// 	value: 'options.value',
	// 	icon: 'options.icon',
	// 	background: 'bgblueish',
	// 	color: 'white-text',
	// 	action: 'options.action'
	// };

	var self = this;
	self.$onInit = function(){
		if(self.value){
			$scope.value = self.value;
			$scope.hh = $scope.value.substring(1, 3);
			$scope.mm = $scope.value.substring(4);
		}
		else{
			$scope.hh = "00";
			$scope.mm = "00";
		}
		if(self.label){
			$scope.label = self.label;
		}
	};
	
});/*global app*/
app.controller('authController', function($scope, $rootScope, $http, $location, $cookies, H, M, S) {
	if($rootScope.currentUser){
		$location.path('/');
	}
	
	$scope.forms = {};
	
	$scope.H = H;
	$scope.M = M;
	$scope.S = S;
	
	$scope.data = {};
	
	//$scope.loading = false;

	$scope.login = function(){
		//$scope.loading = true;
		$http.post(H.SETTINGS.baseUrl + '/users/login', {email: $scope.email, password: $scope.password})
			.then(function(r){
				$scope.error = "";
				if(!r.data.token){
					$scope.error = M.E500;
					//$scope.loading = false;
					return;
				}
				$rootScope.currentUser = r.data;
				$cookies.putObject(H.getCookieKey(), JSON.stringify(r.data));
				$location.path('/');
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					if(e.data.error.code == 404 && e.data.error.message == "Not Found"){
						$scope.error = M.LOGIN_API_UNAVAILABLE;
					} else {
						$scope.error = e.data.error.message ? e.data.error.message : e.data.error.status;	
					}
					
				}
				//$scope.loading = false;
			});
	};

	$scope.forgotPassword = function(){
		//$scope.loading = true;
		$http.post(H.SETTINGS.baseUrl + '/users/forgot-password', {email: $scope.email})
			.then(function(r){
				$scope.error = M.RECOVERY_EMAIL_SENT;
				//$scope.loading = false;
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					if(e.data.error.code == 404 && e.data.error.message == "Not Found"){
						$scope.error = M.LOGIN_API_UNAVAILABLE;
					} else {
						$scope.error = e.data.error.message ? e.data.error.message : e.data.error.status;
					}
				}
				//$scope.loading = false;
			});
	};

	$scope.register = function(){
		var route = 'users';
		var data = {username: $scope.data.username, email: $scope.data.email, password: $scope.data.password};
		if(S.enableSaaS) {
			route = 'organizations'; 
			data = {organization: $scope.data.organization, email: $scope.data.email};
		}else{
			if($scope.data.password != $scope.data.confirmPassword){
				$scope.error = "Password and Confirm Password should match!";
				return;
			}
		}
		
		$http.post(H.SETTINGS.baseUrl + '/' + route +'/register', data)
			.then(function(r){
				$scope.error = M.REGISTRATION_EMAIL_SENT;
			}, function(e){
				if(e && e.data && e.data.error && e.data.error.status){
					if(e.data.error.code == 404 && e.data.error.message == "Not Found"){
						$scope.error = M.REGISTER_API_UNAVAILABLE;
					} else {
						$scope.error = e.data.error.message ? e.data.error.message : e.data.error.status;
					}
				}
			});
	};
	
	$scope.logout = function(){
		$cookies.remove(H.getCookieKey());
		delete $rootScope.currentUser;
		$location.path('/sign-in');
	};
});


/*global app*/
app.controller('profileController', function($scope, $rootScope, $http, $cookies, H, M){
	$scope.H = H;
	$scope.M = H.M;
	
	$scope.locked = true;
	$scope.lockedClass = "hidden";
	$scope.editingClass = "";
	
	$scope.forms = {};
	$scope.userData = {};
	$scope.passwordData = {};

	$scope.disableEdit = function(){
		$scope.locked = true;
		$scope.lockedClass = "hidden";
		$scope.editingClass = "";
	}
	
	$scope.edit = function(){
		$scope.locked = false;
		$scope.editingClass = "float-left";
		$scope.lockedClass = "visible float-right formClass";
	
		$scope.userData.username = $rootScope.currentUser.username;
		$scope.userData.email = $rootScope.currentUser.email;
		$scope.userData.role = $rootScope.currentUser.role;
	};
	
	$scope.updateHandler = function(r){
				$scope.userData.message = H.M.PROFILE_SAVED;
				var user = r.data;
				user.password = $rootScope.currentUser.password;
				user.organization = $rootScope.currentUser.organization;
				$rootScope.currentUser = user;
				$cookies.putObject(H.getCookieKey(), JSON.stringify($rootScope.currentUser));
	}
	
	$scope.save = function(){
		$scope.userData.error = "";
		$scope.userData.message = "";
		$http.get(H.S.baseUrl + '/users/' + $rootScope.currentUser.id).then(function(res){
			var r = res.data;
			r.username = $scope.userData.username;
			r.email = $scope.userData.email;
			r.role = $scope.userData.role;
			
			if(H.S.legacyMode){
				$http.post(H.S.baseUrl + '/users/update', r).then(function(r){
					$scope.updateHandler(r);
				}, function(e){
					$scope.userData.error = H.M.PROFILE_SAVE_ERROR;
				});
			} else {
				$http.put(H.S.baseUrl + '/users', r).then(function(r){
					$scope.updateHandler(r);
				}, function(e){
					$scope.userData.error = H.M.PROFILE_SAVE_ERROR;
				});
			}
		},function(e){
			$scope.userData.error = H.M.PROFILE_SAVE_ERROR;
		});
	};
	
	$scope.changePassword = function(){
		$scope.passwordData.error = "";
		$scope.passwordData.message = "";
		if($scope.passwordData.newPassword != $scope.passwordData.confirmPassword){
			$scope.passwordData.error = M.PASSWORD_NOT_MATCHING;
			return;
		}
		var data = {
			email: $rootScope.currentUser.email,
			password: $scope.passwordData.password,
			new_password: $scope.passwordData.newPassword
		};
		$http.post(H.S.baseUrl + '/users/change-password', data).then(function(res){
			$scope.passwordData.message = H.M.PASSWORD_CHANGED;
		},function(e){
			$scope.passwordData.error = H.M.PASSWORD_CHANGE_ERROR + " " + e.data.error.message;
		});
	};	
});/*global app*/
app.controller('unauthorizedController', function($scope, H){
	$scope.H = H;
	$scope.M = H.M;
});
/*global app, RegisterMenuItems*/
app.controller('navController', function($scope) {
    var data = RegisterMenuItems();
    for(var k in data){
        if(data.hasOwnProperty(k) && data[k].items && data[k].items.length > 0){
            for (var i = 0; i < data[k].items.length; i++) {
                data[k].items[i].action = '#!' + data[k].items[i].action;
                if(data[k].items[i].items && data[k].items[i].items.length > 0){
                    data[k].items[i].action = '';
                    for (var j = 0; j < data[k].items[i].items.length; j++) {
                        data[k].items[i].items[j].action = '#!' + data[k].items[i].items[j].action;
                    }
                }
            }
        }
    }
    $scope.data = data;
});/*global app*/
app.controller('titleController', function($scope, S){
   $scope.title =  S.productName;
});/*global angular, app, $*/
app.controller('groupsControllerExtension', function($scope, $controller, $rootScope, $http, $location, $mdDialog, H, M) {
    if(!(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }

    $scope.UserGroups = H.R.get('user_groups');
    $scope.Users = H.R.get('users');
    $scope.loadUsers = function(){
        $scope.Users.query({}, function(r){
            $scope.users = r;    
            var usersList = {};
            $scope.users.map(function(p){
                usersList[p.username] = "images/user.png";
            });
            $scope.data.usersList = usersList;
        });
    }

    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.data.single.is_active = true;
        $scope.data.groupUsers = [{id: 3}];
        $scope.loadUsers();
    };
    
    $scope.onLoadSingle = function(result){
        $scope.UserGroups.query({group_id: result.id}, function(r){
            $scope.loadUsers();
            $scope.data.groupUsers = r;
        });
    };
    
    $scope.removeGroupUser = function(item){
        $scope.delete(item, function(r){
            $scope.onLoadSingle($scope.data.single);
        });
    };
    
    $scope.addGroupUser = function(user){
        if($scope.data.single.id){
            var ug = new $scope.UserGroups();
            ug.user_id = user.id;
            ug.group_id = $scope.data.single.id;
            if(!$scope.groupUserElements) $scope.groupUserElements = [];
            $scope.groupUserElements.push(ug);
            $scope.save(ug, function(r){
                $scope.data.groupUsers.push(r);
            });
        }
    };

    $scope.onSave = function(result, next){
        
        if(result && result.id){
            var UserGroups = H.R.get('user_groups');
            for (var i = 0; i < $scope.data.groupUsers.length; i++) {
                var ug = new $scope.UserGroups();
                ug.user_id = $scope.data.groupUsers[i].id;
                ug.group_id = result.id;
                       
                $scope.save(ug, function(){
                    $scope.locked = false;
                });
            } 
        } else {
        }
        
            
        next();
    };
    

    
});/*global app*/
app.controller('homeController', function ($scope, $rootScope, H, R) {

	// $controller('homeControllerBase', {
	// 	$rootScope:$rootScope
	// });
	
	$scope.H = H;
	$scope.M = H.M;

	$scope.data = {
		counters: {
			tasksCounter: {
				title: 'Tasks',
				value: '...',
				icon: 'assignment_turned_in',
				background: 'bg-green',
				color: 'white-text',
				action: 'tasks',
				allowedRoles: ['user', 'admin']
			},
			usersCounter: {
				title: 'Users',
				value: '...',
				icon: 'person',
				background: 'bg-purple',
				color: 'white-text',
				action: 'users',
				allowedRoles: ['admin']
			},
			groupsCounter: {
				title: 'Groups',
				value: '...',
				icon: 'group',
				background: 'bg-pink',
				color: 'white-text',
				action: 'groups',
				allowedRoles: ['admin']
			},
			organizationsCounter: {
				title: 'Organizations',
				value: '...',
				icon: 'people_outline',
				background: 'bg-green',
				color: 'white-text',
				action: 'organizations',
				allowedRoles: ['superadmin']
			}
		},
		bgColors: [
			"bg-blue",
			"bg-red",
			"bg-teal",
			"bg-orange",
			"bg-cyan",
			"bg-brown",
			"bg-pink",
			"bg-purple",
			"bg-green"
			// "bg-light-blue",
			// "bg-amber",
			// "bg-lime",
			// "bg-yellow",
			// "bg-indigo",
			// "bg-grey",
		]

	};
	
	function getNextNumber(n) {
		var m = n % $scope.data.bgColors.length;
		return m;
	}
	
	function randomizeTileColors() {
		var count = 0;
		for(var key in $scope.data){
			if($scope.data.hasOwnProperty(key)){
				var val = $scope.data[key];
				if(val.hasOwnProperty('background')){
					val.background = $scope.data.bgColors[getNextNumber(count)];
				}
				count++;
			}
		}
	}
	
	function setCount(resourceName, counterName) {
		R.count(resourceName, function (result) {
			$scope.data.counters[counterName].value = result;
		});
	}
	
	function setCounts(resources) {
		for (var i = 0; i < resources.length; i++) {
			var resourceName = resources[i];
			var counterName = resourceName + 'Counter';
			setCount(resourceName, counterName);
		}
	}
	
	function setCountsDefault(){
		var resources = [];
		for (var k in $scope.data.counters) {
			var v = $scope.data.counters[k];
			if(v.allowedRoles.indexOf($rootScope.currentUser.role) > -1){
				resources.push(v.action);
			}
		}
		setCounts(resources);
	}
	
	$rootScope.currentPage = 1;
	
	
	//Random colors for each tile
	//randomizeTileColors();
	
	//Set counts for each tile
	//setCounts(["tasks", "users"]);
	
	//Set counts for each tile automatically, considering the name of the action and the path of the API is same
	setCountsDefault();


});
/*global angular, app*/
app.controller('organizationsControllerExtension', function($scope, $controller, $rootScope, $http, $location, $timeout, $mdDialog, H, M) {

    if(!(['superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }
    
    $scope.checkLicenceValidity = function(item){return H.checkLicenseValidity(item) == 'valid' ? true : false };

    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.initSingle();
        $scope.data.single.org_secret = H.getUUID();  
        $scope.data.single.license = 'basic';
        $scope.data.single.validity = '0000-00-00 00:00:00';
    };
    
    $scope.currentOrganization = {};
    $scope.newOrganizationValues = {};
    $scope.newUserValues = {};
    
    $scope.activate = function(item, newItem) {
        if($rootScope.currentUser.role == 'superadmin'){
            //$scope.loading = true;
            var url = H.SETTINGS.baseUrl + '/organizations/activate';
            item.validity = (newItem.validity) ? H.toMySQLDateTime(newItem.validity) : item.validity;
            item.license = (newItem.license) ? newItem.license : item.license;
            $http.post(url, item)
                .then(function(r){
                    $scope.refreshData();
                    $scope.newOrganizationValues = {};
                    $scope.currentOrganization = {};
                    $mdDialog.cancel();   
                    //$scope.loading = false;
                },function(e){
                    if(e && e.data && e.data.error && e.data.error.message){
                        if(e.data.error.code == 404){
                            $scope.newOrganizationValues.error =  M.SAAS_API_UNAVAILABLE;
                        } else {
                            $scope.newOrganizationValues.error = e.data.error.message;
                        }
                    }
                    //$scope.newOrganizationValues = {};
                    //$scope.currentOrganization = {};
                    //$mdDialog.cancel();   
                    //$scope.loading = false;
                });
        }
    };
    
    $scope.setPassword = function(item, newItem) {
        if($rootScope.currentUser.role == 'superadmin'){
            if(newItem.admin_password == null || newItem.admin_password == ""){
                newItem.error = "Super Admin Password is required!";
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
            //$scope.loading = true;
            $http.post(url, newItem)
                .then(function(r){
                    $scope.currentOrganization = {};
                    $scope.newUserValues = {};
                    $mdDialog.cancel();   
                    //$scope.loading = false;
                },function(e){
                    if(e && e.data && e.data.error && e.data.error.status){
                        newItem.error = e.data.error.message ? e.data.error.message : e.data.error.status;    
                    }
                    //$scope.loading = false;
                    //$scope.currentOrganization = {};
                    //$scope.newUserValues = {};
                    //$mdDialog.cancel();   
                });
        }
    };  
    
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
    };

    $scope.showSetPasswordDialog = function(ev, item) {
        $scope.currentOrganization = item;
        $mdDialog.show({
          contentElement: '#setPasswordDialog',
          parent: angular.element(document.body),
          targetEvent: ev,
          clickOutsideToClose: false
        });
    };
    
    $scope.hideSetPasswordDialog = function(){
        $scope.currentOrganization = {};
        $scope.newUserValues = {};
        
        $mdDialog.cancel();            
    };
});/*global angular, app*/
app.controller('usersControllerExtension', function($scope, $controller, $rootScope, $http, $location, $mdDialog, H, M) {
    
    
    if(!(['admin', 'superadmin'].indexOf($rootScope.currentUser.role) > -1)){
        $location.path('unauthorized');
    }

    $scope.newSingle = function(){
        $scope.locked = false;
        $scope.data.single.password = H.getHash('pRESTige');
    };
    
    $scope.setPassword = function(item, newItem) {
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
            //$scope.loading = true;
            $http.post(url, newItem)
                .then(function(r){
                    $scope.clickedUser = {};
                    $scope.newUserValues = {};
                    $mdDialog.cancel();   
                    //$scope.loading = false;
                },function(e){
                    if(e && e.data && e.data.error && e.data.error.status){
                        newItem.error = e.data.error.message ? e.data.error.message : e.data.error.status;    
                    }
                    //$scope.loading = false;
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