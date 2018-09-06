app.service('M', function($http) {
	return {
		"E404": "The resource you are trying to access does not exist! Please check you have a relevant API endpoint and/or database table available.",
		"E400": "Bad request!",
		"E500": "An error accured."
	}
});
