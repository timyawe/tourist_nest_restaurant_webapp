theApp.controller("dashboardCtlr", function($scope, $http, userDetails){
	//let userID = userDetails.getUserID();
	if(userDetails.getUserID() !== undefined){
		$scope.pagetitle = "Home";
		$http.get("../crud/read/getDashboardContent.php", {params: {station: userDetails.getStation()}}).then(function(response){
			$scope.allords = response.data.allorders;
			$scope.delvords = response.data.deliveredorders;
			$scope.pendords = response.data.pendingorders;
			console.log(response.data.allorders, userDetails.getUserID(), response.data);
		}, function(response){
			console.log(response.data);
		});
	}else{
		console.log("Not logged in");
		//location.replace("/");
	}
});