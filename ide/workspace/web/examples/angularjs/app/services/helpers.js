app.service('H', function($location, md5, S, M, R) {
	return {
		S: S,
		SETTINGS: S,
		M: M,
		MESSAGES: M,
		R: R,
		RESOURCES: R,
		getCookieKey: function() {
			var absUrl = $location.absUrl();
			var startIndex = absUrl.indexOf("//") + 2;
			var endIndex = absUrl.indexOf("#");
			var base = absUrl.substring(startIndex, endIndex);
			var pattern = /[\s:/!@#\$%\^\&*\)\(+=.-]/g;
			var key = base.replace(pattern, "_");
			return key;
		},
		getAbsolutePath: function(href) {
		    var link = document.createElement("a");
		    link.href = href;
		    return link.href;
		},
		getHash: function(str){
           return md5.createHash(str);
		},
		getRandomNumber: function(min, max) {
			return Math.floor(Math.random() * (max - min + 1)) + min;
		}

	}
});
