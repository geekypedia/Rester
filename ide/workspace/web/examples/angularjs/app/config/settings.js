/*global app*/
app.service('S', function($http) {
	return {
		"baseUrl": "../../../../../api",
		"productName": "pRESTige",
		"supportEmail": "support@prestigeframework.com",
		"enableSaaS": true,
		"openRegistration": true,
		"legacyMode": false,
		"theme": {
			background: "primary",
			color: "white"
		},
		"menu": "expand" //expand or overlap
	}
});