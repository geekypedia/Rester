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
                    	var bypassedKeywords = ['ui-grid'];
                    	var bypassedKeywordsMatches = bypassedKeywords.filter(p => config.url.indexOf(p) > -1);
                        if((m && m.length > 0) || bypassedKeywordsMatches.length > 0){
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
                        config.headers['secret'] = $rootScope.currentUser.secret;
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
            if(response.status === 503){
            	$rootScope.$emit('outOfService');
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

    $rootScope.$on("outOfService", function (event, next, current) {
    	$cookies.remove(H.getCookieKey());
		delete $rootScope.currentUser;
		$location.path('/out-of-service');
    });
    
});
