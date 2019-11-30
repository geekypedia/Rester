app.service("loginEventService",function($rootScope) {
    this.broadcast = function() {$rootScope.$broadcast("logged-in")};
    this.listen = function(callback) {$rootScope.$on("logged-in",callback)};
});