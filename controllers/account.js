theApp.controller("accountCtlr", function($scope, userDetails){
	if(userDetails.getUserType() !== "end"){
		$scope.acc_banner = "Admin";
		$scope.isAdmin = true;
	}else{
		$scope.acc_banner = "User";
	}
});