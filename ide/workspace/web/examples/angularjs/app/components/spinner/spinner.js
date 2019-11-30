/*global app*/
app.directive('spinner', function($rootScope) {
  return {
    scope: {
      size: '@'
    },
    restrict: 'EA',
    replace: true,
    template: '<img src="images/spinner.gif" ng-if="$root.loading" style="width:{{size || 13}}px;height:{{size || 13}}px; vertical-align: baseline;"></img>'
  };
});