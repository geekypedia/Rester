/*global app*/
class Settings{
	static get(){
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
		"menu": "expand", //expand or overlap,
		"autoMasters": false,
		"showMastersMenu": true
		}
	}
}
app.service('S', function() {
	return Settings.get();
});
