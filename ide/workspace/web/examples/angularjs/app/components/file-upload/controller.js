/*global app*/
app.directive('fileUpload', function (S, upload) {
    return {
        restrict: 'EA',
        scope: {
            id: '@',
            css: '@',
            label: '@',
            callback: '&',
            url: '@'
        },
        template: `
			<div class="{{containerClass}}">
			    <input type="file" class="{{containerClass}}-input" id="{{id}}_input" name="{{id}}_input">
			    <label class="{{containerClass}}-label" for="{{id}}_input">{{label || 'Choose a file'}}</label>
			</div>
        `,
        link: function (scope, element, attr) {
        	scope.id = scope.id || "input_" +  Math.floor(Math.random() * 1000);
        	scope.containerClass = scope.css || 'custom-file';
            element.bind('change', function () {
                var formData = new FormData();
                formData.append('file', element[0].children[0].children[0].files[0]);
                upload(scope.url || S.baseUrl + '/files', formData, function(r){
                	if(scope.callback) scope.callback()(r);
                });
            });

        }
    };
});
app.factory('upload', function($http) {
  return function(url, data, callback) {
    $http({
      url: url,
      method: "POST",
      data: data,
      headers: {
        'Content-Type': undefined
      }
    }).then(function(response) {
      if(callback) callback(response);
    }, function(e){
    	if(callback)  callback(e);
    });
  };
});
/*
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
}]);
*/
