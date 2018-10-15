/*global angular, app, $*/
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
        $scope.data.single.is_active = 1;
        $scope.loadUsers();
    };
    
    $scope.onLoadSingle = function(result){
        $scope.loadUsers();
        $scope.UserGroups.query({group_id: result.id}, function(r){
            $scope.data.groupUsers = r;
        });
    };
    
    $scope.removeGroupUser = function(item){
        $scope.loading = true;
        $scope.delete(item, function(r){
            $scope.loading = false;
            // $scope.UserGroups.query({group_id: $scope.data.single.id}, function(r){
            //     $scope.data.groupUsers = r;
            // });
        });
    };
    
    $scope.addGroupUser = function(user){
        if($scope.data.single.id){
            var ug = new $scope.UserGroups();
            ug.user_id = user.id;
            ug.group_id = $scope.data.single.id;
            // if(!$scope.groupUserElements) $scope.groupUserElements = [];
            // $scope.groupUserElements.push(ug);
            $scope.save(ug, function(r){
                console.log(r);
                if(!((r && r.data && r.data.error) || (r && r.error))){
                    $scope.data.groupUsers.push(r);    
                }
                
            });
        }
    };

    $scope.onSave = function(result, next){
        
        if(result && result.id){
            // var UserGroups = H.R.get('user_groups');
            // for (var i = 0; i < $scope.data.groupUsers.length; i++) {
            //     var ug = new $scope.UserGroups();
            //     ug.user_id = $scope.data.groupUsers[i].id;
            //     ug.group_id = result.id;
            //     $scope.save(ug);
                
            // } 
        } else {
        }
        
            
        next();
    };
    

    
});