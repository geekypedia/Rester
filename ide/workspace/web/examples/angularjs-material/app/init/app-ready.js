/*global $*/
//JQuery
$(function() {
	$('.sidenav').sidenav({
		closeOnClick: true
	});
	
	$(document).ready(function(){
    	$('.collapsible').collapsible();
	});	
	
	$('select').formSelect();
});
