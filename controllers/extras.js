theApp.controller("extrasCtlr", function($scope, userDetails){
	if(userDetails.getUserType() !== "user"){
		//$scope.acc_banner = "Admin";
		$scope.isAdmin = true;console.log("nice");
	}/*else{
		$scope.acc_banner = "User";
	}*/

});

theApp.controller("manage_extrasCtlr", function($scope, $http, $routeParams,userDetails){
	if($routeParams.category === "Offers"){
		$scope.category = "Offers";
		$scope.show = true;
	}else if($routeParams.category === "spoilt"){
		$scope.category = "Items Spoilt";
	}else{
		$scope.category = "Missing Items";
	}
	
	
	$http.get(getUrl()).then(function(response){
		$scope.extras = response.data.message;
		console.log(response.data);
	},function(response){
		console.log(response);
	});
	
	function getUrl(){
		if($routeParams.category === "Offers"){
			return "../crud/read/getOffers.php?station=" + userDetails.getStation();
		}else if($routeParams.category === "spoilt"){
			return "../crud/read/getSpoiltItems.php?station=" + userDetails.getStation();
		}else{
			return "../crud/read/getMissingItems.php";
		}
	}
	
});

theApp.controller("create_extraCtlr", function($scope, $http, $routeParams, userDetails, lineDetails, httpResponse){
	$scope.station = userDetails.getStation();
	
	if($routeParams.ID === undefined){
		if($routeParams.category === "Offers"){
			$scope.category = "Offers";
			$scope.isMissingItems = false;
			$scope.show = true;
		}else if($routeParams.category === "Items Spoilt"){
			$scope.category = "Items Spoilt";
			$scope.isMissingItems = false;
		}else{
			$scope.category = "Missing Items";
			$scope.isMissingItems = true;
		}
			
		let details = [1];//to contain details for form submission
		
		/* Generating the options of the Item select tag */
		lineDetails.getItems(getItemsUrl(), {params: {station: userDetails.getStation()}}).then(res => $scope.items =res);
		
		/* Array to hold the number of rows of the orders details */
		$scope.rows = [{ID:1, item: undefined, qty:null, itemSelected: false}];
		
		/* Function to add a row to the details */
		$scope.addRow = () => lineDetails.addRow_create($scope.rows, details);
		
		/* Function to reomve a row from the details */
		$scope.removeRow = (index) => lineDetails.removeRow_create($scope.rows, index, "Atleast one item is required", details);
		
		$scope.changeItem = function(row, index){
			row.qty = null;
			if(row.item === undefined){
				row.itemSelected = false;
			}else{
				row.itemSelected = true;
			}console.log(row, index);
		}
		
		$scope.updateRow = function(row, index){
			if($scope.category != "Missing Items"){
				if(row.qty <= 0){
					alert("Quantity should be atleast 1");
					row.qty = null;
				}else{
					if(row.qty > row.item.finish  && row.item.onlySold == 0){
						alert("Cannot update Qty, quantity entered is more than quantity available for sale");
						row.qty = null;
					}else{
						details[index] = {pdtNo: row.item.value, qty: row.qty};
					}
				}
			}else{
				details[index] = {pdtNo: row.item.value, qty: row.qty};
			}
			console.log(row.qty , row.item.finish  ,row.item.onlySold);
		}
		
		$scope.validate = function(){
			document.getElementsByClassName("save_btn")[0].setAttribute("disabled", true);
			document.getElementsByClassName("save_btn")[0].style.cursor = "not-allowed";
	 
			$scope.rows = [{ID:1, item: undefined, qty:null, itemSelected: false}];
			//console.log(postData(),postUrl());
			$http.post(postUrl(), postData()).then(function(response){
				httpResponse.success(1, response.data.message);
				console.log(response.data);
				exitEditMode("extras_btn");
			},function(response){
				console.log(response);
			});
			console.log(postUrl(), postData());
		}
		
		function postUrl(){
			if($routeParams.category === "Offers"){
				return "../crud/create/addOffer.php";
			}else if($routeParams.category === "Items Spoilt"){
				return "../crud/create/addSpoilt.php";
			}else{
				return "../crud/create/addMissing.php";
			}
		}
		
		function postData(){
			let post_data;
			if($routeParams.category === "Offers"){
				post_data = {station: $scope.station, RecepientCategory: $scope.to, UserID: userDetails.getUserID(), details:details};
			}else{
				post_data = {station: $scope.station, UserID: userDetails.getUserID(), details:details};
			}
			$scope.category = undefined; 
			$scope.to = undefined; 
			
			return post_data;
		}
		
		function getItemsUrl(){
			if($routeParams.category === "Offers"){
				return "../crud/read/getOrdItemsList.php";
			}else{
				return "../crud/read/getReqItemsList.php";
			}
		}
	}else{
		if($routeParams.category === "Offers"){
			$scope.category = "Offers";
			$scope.show = true;
		}else if($routeParams.category === "Items Spoilt"){
			$scope.category = "Items Spoilt";
		}else{
			$scope.category = "Missing Items";
		}
		console.log(getEditUrl());
		$http.get(getEditUrl()).then(function(response){console.log(response.data);
			$scope.station = response.data.extra[0].Station;
			$scope.to = response.data.extra[0].RecipientCategory;
			$scope.extras = response.data.extra_details;
			
		},function(response){
			console.log(response);
		});
		
		function getEditUrl(){
			if($routeParams.category === "Offers"){
				return "../crud/read/getOffer.php/?offerID="+ $routeParams.ID;
			}else if($routeParams.category === "Items Spoilt"){
				return "../crud/read/getSpoilt.php/?spoiltID="+ $routeParams.ID;
			}else{
				return "../crud/read/getMissing.php/?missingID="+ $routeParams.ID;
			}
		}
	}
});
