/*global app, RegisterMenuItems*/
app.controller('navController', async function($scope, $http, S, H) {

	var masterMenu =   {
            header: 'Masters',
            showHeader: true,
            showSeparator: true,
            items: GLOBALS.menu.masterItems,
	        allowedRoles: ['admin']
    };
    
    var data = RegisterMenuItems();
    if(S.showMastersMenu && masterMenu) {
    	data.push(masterMenu);
    }
    
    
    for(var k in data){
        if(data.hasOwnProperty(k) && data[k].items && data[k].items.length > 0){
            for (var i = 0; i < data[k].items.length; i++) {
                if(!data[k].items[i].action.startsWith('#!')) data[k].items[i].action =  '#!' + data[k].items[i].action;
                if(data[k].items[i].color && !data[k].items[i].color.startsWith("col-")) data[k].items[i].color = "col-" + data[k].items[i].color;
                if(data[k].items[i].items && data[k].items[i].items.length > 0){
                    data[k].items[i].action = '';
                    for (var j = 0; j < data[k].items[i].items.length; j++) {
                        if(!data[k].items[i].items[j].action.startsWith('#!')) data[k].items[i].items[j].action = '#!' + data[k].items[i].items[j].action;
                        if(data[k].items[i].items[j].color  && !data[k].items[i].items[j].color.startsWith("col-")) data[k].items[i].items[j].color = "col-" + data[k].items[i].items[j].color;
                    }
                }
            }
        }
    }
    
    setTimeout(function(){
	    for(var k in data){
	        if(data.hasOwnProperty(k) && data[k].items && data[k].items.length > 0){
	            for (var i = 0; i < data[k].items.length; i++) {
	                if(!data[k].items[i].action.startsWith('#!')) data[k].items[i].action =  '#!' + data[k].items[i].action;
	                if(data[k].items[i].color && !data[k].items[i].color.startsWith("col-")) data[k].items[i].color = "col-" + data[k].items[i].color;
	                if(data[k].items[i].items && data[k].items[i].items.length > 0){
	                    data[k].items[i].action = '';
	                    for (var j = 0; j < data[k].items[i].items.length; j++) {
	                        if(!data[k].items[i].items[j].action.startsWith('#!')) data[k].items[i].items[j].action = '#!' + data[k].items[i].items[j].action;
	                        if(data[k].items[i].items[j].color  && !data[k].items[i].items[j].color.startsWith("col-")) data[k].items[i].items[j].color = "col-" + data[k].items[i].items[j].color;
	                    }
	                }
	            }
	        }
	    }    	
    }, 1000);
    
    
    $scope.data = data;
});
