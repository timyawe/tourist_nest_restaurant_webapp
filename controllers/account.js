theApp.controller("accountCtlr", function($scope, userDetails){
	if(userDetails.getUserType() === "Admin"){
		$scope.acc_banner = "Admin";
		$scope.isAdim = true;
	}else{
		$scope.acc_banner = "User";
	}
});