/*global app*/
//The name of the controller should be plural that matches with your API, ending with ControllerExtension. 
//Example: your API is http://localhost:8080/api/categories then the name of the controller is categoriesControllerExtension.
//To register this controller, just go to app/config/routes.js and add 'categories' in 'easyRoutes' array.
app.controller('categoriesControllerExtension', function($scope, $controller, $rootScope, $http, $location, Popup, Alert, H, M) {
    
    
    //This function is called when you need to make changes to the new single object.
    $scope.onInit = function(obj){
        //$scope.data.single is available here. 'obj' refers to the same. It is the new instance of your 'categories' resource that matches the structure of your 'categories' API.
        obj.is_active = 1;
    };
    
    //This function is called when you are in edit mode. i.e. after a call has returned from one of your API that returns a single object. e.g http://localhost:8080/api/categories/1
    $scope.onLoad = function(obj){
        //$scope.data.single is available here. 'obj' refers to the same. It represents the object you are trying to edit.
    };
    
    //This function is called when you are in list mode. i.e. before a call has been placed to one of your API that returns a the paginated list of all objects matching your API.
    $scope.beforeLoadAll = function(query){
        //This is where you can modify your query parameters.    
        //query.is_active = 1;
        //return query;
    };


    //This function is called when you are in list mode. i.e. after a call has returned from one of your API that returns a the paginated list of all objects matching your API.
    $scope.onLoadAll = function(obj){
        //$scope.data.list is available here. 'obj' refers to the same. It represents the object you are trying to edit.
        
        //You can call $scope.setListHeaders(['column1','column2',...]) in case the auto generated column names are not what you wish to display.
        //or You can call $scope.changeListHeaders('current column name', 'new column name') to change the display text of the headers;
        $scope.changeListHeaders('Category', 'Parent Category');
        
        // console.log(parseInt(($scope.data.records - 1)/ $scope.data.limit));
    };
    
    //This function is called before the create (POST) request goes to API
    $scope.beforeSave = function(obj, next){
        //You can choose not to call next(), thus rejecting the save request. This can be used for extra validations.
        next();
    };

    //This function is called after the create (POST) request is returned from API
    $scope.onSave = function (obj, next){
        //You can choose not to call next(), thus preventing the page to display the popup that confirms the object has been created.
        next();
    };
    
    //This function is called before the update (PUT) request goes to API
    $scope.beforeUpdate = function(obj, next){
        if(obj.id == obj.category_id){
        	Alert.warning("Can not select self as parent");
        	return;
        }
        delete obj.category;
        //You can choose not to call next(), thus rejecting the update request. This can be used for extra validations.
        next();
    };

    //This function is called after the update (PUT) request is returned from API
    $scope.onUpdate = function (obj, next){
        //You can choose not to call next(), thus preventing the page to display the popup that confirms the object has been updated.
        next();
    };
    
    //This function will be called whenever there is an error during save/update operations.
    $scope.onError = function (obj, next){
        //You can choose not to call next(), thus preventing the page to display the popup that confirms there has been an error.
        next();
    };
    
    $scope.getSingularTitle = function(){
        return M.FIELD_CATEGORY.toUpperCase();
    };
    
    H.R.get('categories').query({}, function(r){
        $scope.data.categories = r;
    });
    
    

});