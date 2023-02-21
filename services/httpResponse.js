//Create Angular custom service for user displaying http responses wether success or error
theApp.service("httpResponse", function($timeout){
	this.success = function(status, message){
		toggleLoader("block");
		
		$timeout(function(){
			if(status === 1){
				displayResponseBox(1, message);
			}else if(status === 2){
				displayResponseBox(2, message);
			}else{
				displayResponseBox(0, message);
			}
			//Fadeout response_box after 4sec
			$timeout(fadeout, 4000);
		}, 2000);
	
	};
	
	this.error = function(status, message){
		toggleLoader("block");
	
		$timeout(function(){
			displayResponseBox(0, message);
			//Fadeout response_box after 4sec
			$timeout(fadeout, 4000);
		}, 2000);
	}
	
});