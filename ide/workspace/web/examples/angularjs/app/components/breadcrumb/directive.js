app.directive('breadCrumb', function(){
	return {
	    scope: {
    	  args: '@',
    	  active: '@'
	    },

		restrict: 'EA',
    	templateUrl: 'app/components/bread-crumb/template.html'
	};
});

app.controller('breadCrumbController', function($scope) {
	
	var isString = function  (value) {
		return typeof value === 'string' || value instanceof String;
	}
	


	var args = $scope.args;
	var active = $scope.active;
	
	var isJSON = false;
	try{
		if(isString(args)){
			var argsJSON  = JSON.parse(args);
			args = argsJSON;
			isJSON = true;
		}
	} catch (ex){
		//console.log(ex);
	}

	if (isString(args) && !isJSON && args.indexOf(',') > -1){
		args = args.split(',');
	}


	var data = [];
	if(isString(args)){
		var text = args;
		// var activeClass = (active === true || active === "true" || active == args || active =="") ? "active" : "";
		var activeClass = (active === false || active ==="false") ? "" : "active";
		data = [{ text: text, active: activeClass }];
	} else if(Array.isArray(args)){

		if(args.length == 1){
			var text = (args[0].text) ? args[0].text : args[0]
			// var activeClass = (active === false || active === "false" || (active != "" && active != text) ) ? "" : "active";
			var activeClass = (active === false || active ==="false") ? "" : "active";
			data = [{ text: text, active: activeClass }];
		} else if (args.length > 1){
				var activeInArray = false;
				for(count = 0; count < args.length ; count++){
				
				var i = args[count];
				var text = "";
				var activeClass = "";
				var isLink = false;
				var link = "";
				
				if(isString(i)){
					text = i;
					if(active === true || active === "true"){
						if(i == args.length - 1){
							activeInArray = true;
							activeClass = "active";	
						}
					} else if(active === text) {
						activeInArray = true;
						activeClass = "active";	
					}
				} else {
					text = i.text;
					if(i.active){
						activeInArray = true;
						activeClass = "active";
					}
					if(i.link){
						isLink = true;
						link = i.link
					}
				}
				
				var item = {text: text, active: activeClass};
				if(isLink) item.link = link;
				
				data.push(item);
			}
			if(!activeInArray && !(active === false || active === "false")) { 
				data[data.length - 1].active = "active" 
			}
		} 
		
	} else {
		
		var activeClass = (active === false || active ==="false" || args.active === false || args.active === "false") ? "" : "active";
		args.active = activeClass;
		data = [args];
	} 
	
	$scope.data = data;
	
});
