/*global app*/
app.directive('spinner', function($rootScope) {
  return {
    scope: {
      size: '='
    },
    restrict: 'E',
    replace: true,
    template: '<img src="images/spinner.gif" ng-if="$root.loading" style="width:13px;height:13px"></img>'
  };
});