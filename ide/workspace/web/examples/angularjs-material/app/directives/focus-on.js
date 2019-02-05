/*global app*/
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
]);