/*global app*/
//Service for quickly getting the API Resource Object
app.service('R', function($resource, $http, S) {
	return {
		get: function(resourceName) {
			return $resource(S.baseUrl + '/' + resourceName + '/:id', {
				id: '@id'
			});
		},
		count: async function(resourceName, cb) {
			$http.get(S.baseUrl + '/' + resourceName + '/?count=true')
				.then(function(results) {
					if (results && results.data && results.data.length > 0)
						if (cb) cb(results.data[0].count);
				}, function(e) {});
		},
		query: async function(resourceName, q, cb){
			await $http.get(S.baseUrl + '/' + resourceName,  {params: q})
				.then(function(results) {
					if (results && results.data)
						if (cb) cb(results.data);
						return results.data;
				}, function(e) {
						if (cb) cb(e);
						return e;
            });			
		},
		submit: async function(resourceName, payload, cb){
			await $http.post(S.baseUrl + '/' + resourceName, payload)
				.then(function(results) {
					if (results && results.data)
						if (cb) cb(results.data);
						return results.data;
				}, function(e) {
						if (cb) cb(e);
						return e;
            });			
		},
		update: async function(resourceName, payload, cb){
			await $http.put(S.baseUrl + '/' + resourceName, payload)
				.then(function(results) {
					if (results && results.data)
						if (cb) cb(results.data);
						return results.data;
				}, function(e) {
						if (cb) cb(e);
						return e;
            });			
		},      
		remove: async function(resourceName, id, cb){
			await $http.delete(S.baseUrl + '/' + resourceName + '/' + id)
				.then(function(results) {
					if (results && results.data)
						if (cb) cb(results.data);
						return results.data;
				}, function(e) {
						if (cb) cb(e);
						return e;
            });			
		}
	};
});
