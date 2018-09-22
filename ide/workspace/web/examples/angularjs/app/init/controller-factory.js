//ControllerFactory helps wrap basic CRUD operations for any API resource
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
		}
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
				promise.then(function() {
					if (callback) {
						callback();
					}
				}, function(e){
					errorHandler(e);
					if (callback) {
						callback(e);
					}
				});
			} else if ($scope.data.single) {
				var promise = $scope.data.single.$save();
				promise.then(function() {
					if (callback) {
						callback();
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
		}	
	
		//Load all entries on initialization
		$scope.listAll = function(currentPage){
			$scope.loading = true;
			$scope.count(function(){
				$scope.data.pagesCount = parseInt($scope.data.records / $scope.data.limit) + 1;
				$scope.data.pages = [];
				for (var i = 0; i < $scope.data.pagesCount; i++) {
					$scope.data.pages.push(i + 1);
				}
				if(!currentPage){
					if(!($scope.data.pages.indexOf($rootScope.currentPage) > -1)){
						$rootScope.currentPage = $scope.data.pages[$scope.data.pagesCount - 1];
					}
				} else {
					$rootScope.currentPage = currentPage;
				}
			    $scope.query({limit: $scope.data.limit, offset: ($rootScope.currentPage - 1) * $scope.data.limit}, function(r) {
			    	$scope.loading = false;
			    });
				
			});
		}
		
		//Load entry on initialization
		$scope.loadSingle = function(callback){
			$scope.loading = true;
		    $scope.get($routeParams.id, function(r) {
		    	if(callback) callback(r);
		    	$scope.loading = false;
		    });
		}
		
		
		//Toggle Visibility
		$scope.toggleVisibility = function(item){
		    item.visible = !item.visible;
		}
	
		//Toggle lock
	    $scope.toggleLock = function(){
	        $scope.locked = !$scope.locked;
	    }
	    
	    //Update a single record
	    $scope.updateSingle = function(callback){
			$scope.loading = true;
	    	var update = true;
	    	if($scope.beforeUpdate) update = $scope.beforeUpdate();
	    	if(update){
		        $scope.update($scope.data.single, function(r){
		            $scope.locked = true;
		            if($scope.onUpdate) $scope.onUpdate();
		            if(callback) callback(r);
					$scope.loading = false;
		        });
	    	}
	    }
	    
	    //Initialize a single record
	    $scope.newSingle = function(callback){
	    	$scope.locked = false;
	    	$scope.initSingle();
	    	if(callback) callback();
	    }
	    
	    //Save a new single record
	    $scope.saveSingle = function(callback){
	    	$scope.loading = true;
	    	var save = true;
	    	if($scope.beforeSave) save = $scope.beforeSave();
	    	if(save){
		        $scope.save($scope.data.single, function(r){
		            $scope.locked = true;
		            if($scope.onSave) $scope.onSave();
		            if(callback) callback(r);
		    		$scope.loading = false;
		        });
	    	}
	    }
	    
	    //Change a property in single
	    $scope.changeSingle = function(property, value){
	    	this.data.single[property] = value;
	    }
		

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
		}
		
		$scope.initTextResources = function(listTitle, singleTitle, listTemplate, listItemTemplate, newTemplate, editTemplate){
			$scope.textResources.title.list = listTitle;
			$scope.textResources.title.single = singleTitle;
			$scope.textResources.templates.list = listTemplate;
			$scope.textResources.templates.listItem = listItemTemplate;
			$scope.textResources.templates.create = newTemplate;
			$scope.textResources.templates.edit = editTemplate;
		}		
		
		$scope.initTextResourcesEasy = function(route, singular){
			if(!route || route == '') {
				var route = $scope.currentRoute;
			}
			var plural = route.toUpperCase();
			if(!singular || singular == '') var singular = plural.substring(0, plural.length - 1);
			var listTemplate = 'app/modules/' + route + '/list.html';
			var listItemTemplate = 'app/modules/' + route + '/list-item.html';
			var singleTemplate = 'app/modules/' + route + '/single.html'
		
			$scope.initTextResources(plural, singular, listTemplate, listItemTemplate, singleTemplate, singleTemplate);
		}

		$scope.getTitle = function(t){
			switch (t) {
				case 'single':
					return $scope.textResources.title.single;
					break;
				case 'list':
					return $scope.textResources.title.list;
					break;
				default:
					return $scope.textResources.title.list;
			}
		}
		
		$scope.getTemplate = function(t){
			switch (t) {
				case 'edit':
					return $scope.textResources.templates.edit;	
					break;
				case 'new':
					return $scope.textResources.templates.create;	
					break;
				case 'list':
					return $scope.textResources.templates.list;	
					break;
				case 'list-item':
					return $scope.textResources.templates.listItem;	
					break;
				default:
					return $scope.textResources.templates.edit;	
			}
			
		}
		
		$scope.getTableHeaders = function(){
			var headers = [];
			if($scope.data.list && $scope.data.list.length > 0 && $scope.data.list[0]){
				headers = Object.getOwnPropertyNames($scope.data.list[0]);
			}
			return headers;
		}
		
		$scope.setListHeaders = function(headers){
			$scope.data.listHeaders = headers;
		}
		
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

	    $scope.onSave = $scope.onUpdate = function(){
	        $scope.showDialog(null, M.SAVED_TITLE, M.SAVED_MESSAGE,M.SAVED_OK, M.SAVED_CANCEL, function(){}, function(){$location.path($scope.currentRoute)});
	    }
	    
	    $scope.beforeSave = $scope.beforeUpdate = function(){
	        return (!Object.keys($scope.forms[$scope.currentRoute + "Form"].$error).length);
	    }
	};
}
