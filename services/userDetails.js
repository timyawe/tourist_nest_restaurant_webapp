//Create Angular custom service for user details
//The user variable is set in the home script when the app first loads
theApp.service("userDetails", function(){
	this.getUserID = function(){
		return user.ID;
	};
	
	this.getUserType = function(){
		return user.UserType;
	};
	
	this.getUserLevel = function(){
		return user.AccessLevel;
	};
	
	//if(user.Station !== undefined){
		this.getStation = function(){
			return user.Station;
		};
	//}

});