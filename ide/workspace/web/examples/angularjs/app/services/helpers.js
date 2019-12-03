/*global app, RegisterRoutes*/
app.service('H', function($location, md5, S, M, R) {
	return {
		S: S,
		SETTINGS: S,
		M: M,
		MESSAGES: M,
		R: R,
		RESOURCES: R,
		getCookieKey: function(){
			var absUrl = $location.absUrl();
			Helper.getCookieKey(absUrl);
		},
		getHash: function(str){
    		return md5.createHash(str);
		},
		getAbsolutePath: Helper.getAbsolutePath,
		getRandomNumber: Helper.getRandomNumber,
		getUUID: Helper.getUUID,
		toDateTime: Helper.toDateTime,
		toMySQLDateTime: Helper.toMySQLDateTime,
		toMySQLDate: Helper.toMySQLDate,
		checkLicenseValidity: Helper.checkLicenseValidity,
		getOpenRoutes: function(){
			var openRoutes = RegisterRoutes().customRoutes.filter(function(p){ return p.auth === false});
			var openRouteNames = [];
			openRoutes.forEach(p => openRouteNames.push("/" + p.route));
			return openRouteNames;
		},
		toTitleCase: Helper.toTitleCase,
		replaceAll: Helper.replaceAll,
		deepCopy: Helper.deepCopy
	};
});

class Helper {

	constructor() {
	}

	static getCookieKey(absUrl) {
		var startIndex = absUrl.indexOf("//") + 2;
		var endIndex = absUrl.indexOf("#");
		var base = absUrl.substring(startIndex, endIndex);
		var pattern = /[\s:/!@#\$%\^\&*\)\(+=.-]/g;
		var key = base.replace(pattern, "_");
		return key;
	}
	
	static getAbsolutePath(href) {
		if(href == null) return "";
	    var link = document.createElement("a");
	    link.href = href;
	    return link.href;
	}

	static getRandomNumber(min, max) {
    	return Math.floor(Math.random() * (max - min + 1)) + min;
	}

	static getUUID() {
	      var id = '', i;
	
	      for(i = 0; i < 36; i++)
	      {
	        if (i === 14) {
	          id += '4';
	        }
	        else if (i === 19) {
	          id += '89ab'.charAt(this.getRandomNumber(0,3));
	        }
	        else if(i === 8 || i === 13 || i === 18 || i === 23) {
	          id += '-';
	        }
	        else
	        {
	          id += '0123456789abcdef'.charAt(this.getRandomNumber(0, 15));
	        }
	      }
	      return id;
	}
	
	static toDateTime(str){
		// Split timestamp into [ Y, M, D, h, m, s ]
		var t = str.split(/[- :]/);
		
		// Apply each element to the Date function
		var d = new Date(Date.UTC(t[0], t[1]-1, t[2], t[3], t[4], t[5]));
		
		return d;
	}
	
	static toMySQLDateTime(dt){
		return dt.getUTCFullYear() + "-" + Helper.twoDigits(1 + dt.getUTCMonth()) + "-" + Helper.twoDigits(dt.getUTCDate()) + " " + Helper.twoDigits(dt.getUTCHours()) + ":" + Helper.twoDigits(dt.getUTCMinutes()) + ":" + Helper.twoDigits(dt.getUTCSeconds());
	}


	static toMySQLDate(dt, offset){
		if(offset){
			
			var offsetNum = (0 - (new Date()).getTimezoneOffset()) * 2;
			var offsetH = Math.floor(offsetNum / 60);
			var offsetMN = offsetNum % 60;
			var offsetD = 0;
			var offsetM = 0;
			var offsetY = 0;
			var currentH = dt.getHours();
			var currentMN = dt.getMinutes();
			var currentD = dt.getDate();
			var currentM = dt.getMonth();
			var currentY = dt.getFullYear();
			
			var maxDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
			if(currentY % 4 == 0) maxDays[1] = 29;
			
			var projMN = currentMN + (offsetMN);
			if (projMN >= 60) {
				projMN -= 60;
				offsetH += 1;
			}
			var projH = currentH + (offsetH);
			if(projH >= 24){
				projH -= 24;
				offsetD += 1;
			}
			
			var projD = currentD + offsetD;
			if(projD > maxDays[currentM]){
				projD -= maxDays[currentM];
				offsetM += 1;
			}
			
			var projM = currentM + offsetM;
			if(projM > 12){
				projM -= 12;
				offsetY += 1;
			}
			
			var projY = currentY + offsetY;
			
			dt = new Date(projY, projM, projD, projH, projMN, 0);
			//console.log(dt);
			
			
			//dt = new Date(dt + (new Date()).getTimezoneOffset());
		}
		return dt.getUTCFullYear() + "-" + Helper.twoDigits(1 + dt.getUTCMonth()) + "-" + Helper.twoDigits(dt.getUTCDate());
	}

	
	static twoDigits(d) {
	    if(0 <= d && d < 10) return "0" + d.toString();
	    if(-10 < d && d < 0) return "-0" + (-1*d).toString();
	    return d.toString();
	}
	
	static checkLicenseValidity(organization){
		return ((new Date() > Helper.toDateTime(organization.validity) && !(['basic', 'super'].indexOf(organization.license) > -1))  || !organization.is_active ) ? 'expired' : 'valid';
	}
	
	static toTitleCase(input){
		input = input || '';
		return input.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	}
	
	static replaceAll(input, search, replacement){
		input = input || '';
		return input.replace(new RegExp(search, 'g'), replacement);
	}
	
	static deepCopy(input){
		return JSON.parse(JSON.stringify(input));
	}

}
