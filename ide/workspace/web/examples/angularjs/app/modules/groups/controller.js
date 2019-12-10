/*global angular, app, $*/
app.controller('groupsControllerExtension', function($scope, $controller, $rootScope, $http, $q, $location, Popup, H, M) {
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
            $("#query").typeahead({ source:$scope.data.usersList });
        });
    };
    $scope.loadUserGroups = function(groupId, callback){
        $scope.UserGroups.query({group_id: groupId}, function(r){
            $scope.data.groupUsers = r;
            if(callback) callback();
        });
    };
    
    $scope.getUsers = function(searchText){
        return $http.get(H.S.baseUrl + '/users?username[in]=' + searchText)
            .then(function(r){
                return r.data;
            });
        //return $scope.data.users.filter(p => p.username.includes(searchText));
    };

    $scope.onInit = function(obj){
        obj.is_active = 1;
        $scope.loadUsers();
    };
    
    $scope.onLoad = function(obj){
        $scope.loadUsers();
        $scope.loadUserGroups(obj.id);
        
    };
    
    if(!$scope.data.groupUsersAdd) $scope.data.groupUsersAdd = [];
    if(!$scope.data.groupUsersRemove) $scope.data.groupUsersRemove = [];
    
    $scope.removeGroupUser = function(user){
        
        var justAdded = $scope.data.groupUsersAdd.filter(function(p){return p.user_id == user.user_id && p.group_id == user.group_id });
        if(justAdded.length == 1){
            var justAddedIndex = $scope.data.groupUsersAdd.indexOf(justAdded[0]);
            if(justAddedIndex > -1){
                $scope.data.groupUsersAdd.splice(justAddedIndex, 1);    
            }
        } else {
            $scope.data.groupUsersRemove.push(user);
        }
        var userIndex = $scope.data.groupUsers.indexOf(user);
        if(userIndex > -1){
            $scope.data.groupUsers.splice(userIndex, 1);    
        }
        // $scope.loading = true;
        // $scope.delete(item, function(r){
        //     $scope.loading = false;
        //     // $scope.UserGroups.query({group_id: $scope.data.single.id}, function(r){
        //     //     $scope.data.groupUsers = r;
        //     // });
        // });
    };
    
    $scope.addGroupUser = function(user){
        if($scope.data.single.id && user){
            var ug = new $scope.UserGroups();
            ug.user_id = user.id;
            ug.group_id = $scope.data.single.id;
            var ugl = new $scope.UserGroups();
            ugl.user_id = user.id;
            ugl.group_id = $scope.data.single.id;
            ugl.user = user;
            
            var notExistsInAdd = $scope.data.groupUsersAdd.filter(function(p) { return p.user_id == ug.user_id && p.group_id == ug.group_id}).length == 0;
            var notExistsInMain = $scope.data.groupUsers.filter(function(p) { return p.user_id == ug.user_id && p.group_id == ug.group_id }).length == 0;
            var removeMembers = $scope.data.groupUsersRemove.filter(function(p) { return p.user_id == ug.user_id && p.group_id == ug.group_id });
            var existsInRemove = removeMembers.length == 1;
            var notExistsInRemove = removeMembers.length == 0;
            
            if(notExistsInAdd && notExistsInMain && notExistsInRemove){
                $scope.data.groupUsersAdd.push(ug);
                $scope.data.groupUsers.push(ugl);
            } else {
                if(existsInRemove){
                    var removeIndex = $scope.data.groupUsersRemove.indexOf(removeMembers[0]);
                    if(removeIndex > -1){
                        $scope.data.groupUsersRemove.splice(removeIndex, 1);
                        $scope.data.groupUsers.push(ugl);
                    }
                }
            }
            // $scope.save(ug, function(r){
            //     if(!((r && r.data && r.data.error) || (r && r.error))){
            //         $scope.data.groupUsers.push(r);    
            //     }
            // });
        }
    };
    
    $scope.onUpdate = function(obj, next){
        var promises = [];
        if($scope.data.groupUsersAdd.length > 0){
            promises.push($scope.post('user_groups',$scope.data.groupUsersAdd));
        }
        if($scope.data.groupUsersRemove.length > 0){
            var ids = $scope.data.groupUsersRemove.map(function(p){ return p.id });
            promises.push($scope.deleteMany('user_groups', ids));
        }
        if(promises.length > 0){
            $q.all(promises).then(function(r){
                if(r.error){
                    $scope.onErrorBase(r.error);
                } else {
                    $scope.loadUserGroups(obj.id, function(){
                        $scope.data.groupUsersAdd = [];
                        $scope.data.groupUsersRemove = [];
                        next();    
                    });
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
                next();
        } else {
            next();
        }
        
    };
    
    GLOBALS.methods.autoFocus();
    
});
