app.service('Popup', function($uibModal) {
	return {
		simple: function(title, message){
			this.show({ title: title, body: message});
		},
		show: function(options){
			
			// options = {
			// 	title: "Sample Popup",
			// 	buttons: [
			// 		{
			// 			text: "OK",
			// 			theme: "primary",  //(optional)All bootstrap types - default, primary, danger, warning, success, info, lnk etc.
			//			class: "", //(optional)If you want to apply any custom class
			// 			click: function(callback, btn, data){ 
			// 				//do something
			// 				//call callback(btn) ifyou want to get your cleanup function to be called
			// 			},
			//			cleanup: function(data){ (optional)
			// 				//do something
			// 			},
			//			submits: "formName" //(optional) Name of the form this button submits. Converts it into a submit button.
			// 		}
			// 	],
			//  buttonPosition: "" // (optional) default, top,
			//	spinner: false //(optional) true - If you want to show progress while making http calls
			// 	body: "<p>Something</p>",
			// 	data: $scope.your_data (optional),
			//  watch: [{target:'data.variable', callback: function(newObj, oldObj){}}] (optional)
			// 	scope: $scope //(optional),
			//  close: function(data){}
			// }
			
		var templatePre=`
<div class="">
		<div class="modal-header">
            <h4 class="modal-title">
                {{options.title}}
            </h4>
        	<div class="controls">
            	<span ng-if="options.spinner"><spinner></spinner>&nbsp;</span>
            	<button ng-if='options.buttonPosition == "top"' ng-repeat="i in options.buttons" class="btn btn-{{i.theme}} {{i.class}}" type="button" ng-click="i.click(callback, i, data)">{{i.text}}</button>
            	<button ng-if='options.buttonPosition == "top"' class="btn btn-dark" type="{{ i.submits ? 'submit' : 'button'}}" ng-click="cancel()">Close</button>
        	</div>
        </div>
        <div class="modal-body">
        	<div class="custom-modal-content">
		`;
		var templatePost=`        		
        	</div>
		</div>	
		<div class="modal-footer" ng-if='!options.buttonPosition || options.buttonPosition == "" || options.buttonPosition == "default"' >
		    <button ng-repeat="i in options.buttons" class="btn btn-{{i.theme}} {{i.class}}" type="{{ i.submits ? 'submit' : 'button'}}" ng-click="i.click(callback, i, data)" form="{{ i.submits ? i.submits : ''}}">{{i.text}}</button>
		    <button class="btn btn-dark" type="button" ng-click="cancel()">Close</button>
		</div>		
</div>
		`;
		
		var template = templatePre + options.body + templatePost;
		
		var instance = $uibModal.open({
			template: template,
			//templateUrl: "app/services/popup.html",
			controller: function ($scope, $uibModalInstance, H, M, S, $rootScope) {
	      		$scope.options = options;
	        	$scope.data = options.data || {}
	        	
	        	$scope.close = function () {
	        		$uibModalInstance.dismiss($scope.data);
	        	};

	        	$scope.cancel = function () {
	        		$uibModalInstance.dismiss($scope.data);
	        	};
	        	
	        	$scope.callback = function(btn) {
	        		btn.data = $scope.data;
	        		$uibModalInstance.close(btn);
	        	}
	        	
	        	
	        	if(options.watch){
	        		for(i in options.watch){
	        			var w = options.watch[i];
	        			$scope.$watch(w.target, w.callback);	
	        		}
	        	}

			},
			scope : options.scope
    		});	
    	 
		instance.result.then(function(btn){
			//Get triggers when modal is closed
			if(btn && btn.cleanup) btn.cleanup(btn.data);
		}, function(data){
			//gets triggers when modal is dismissed.
			if(options && options.close) options.close(data);
		 });

		}
	};
});
