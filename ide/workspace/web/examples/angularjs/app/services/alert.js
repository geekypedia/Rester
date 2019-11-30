app.service('Alert', function() {
	return {
		success: function(message){
			this.show({ message: message, type: "success"});
		},
		danger: function(message){
			this.show({ message: message, type: "danger"});
		},
		warning: function(message){
			this.show({ message: message, type: "warning"});
		},
		info: function(message){
			this.show({ message: message, type: "info"});
		},
		primary: function(message){
			this.show({ message: message, type: "primary"});
		},
		secondary: function(message){
			this.show({ message: message, type: "secondary"});
		},
		show: function(options){
			
			// options = {
			// 	message: "Sample Popup",
			//  type: "primary",  //(optional)All bootstrap types - default, primary, secondary, danger, warning, success, info etc.
			//  el: "alerts-container"
			// }
			

		
		$(function(){
			
		var template=`
<div class="alert alert-` + options.type + ` alert-dismissible fade show" role="alert">
  ` + options.message + `
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
		`;
		
		var selector = "." + (options.el ? options.el : "alerts-container");
		
		$('' + selector).html(template);

		});

		}
	};
});