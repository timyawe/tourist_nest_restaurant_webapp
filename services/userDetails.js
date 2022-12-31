//Create Angular custom service for user details
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