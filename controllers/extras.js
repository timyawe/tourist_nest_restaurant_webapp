theApp.controller("manage_extrasCtlr", function($scope, $http, $routeParams){
	if($routeParams.category === "Offers"){
		$scope.category = "Offers";
		$scope.show = true;
	}else{
		$scope.category = "Items Spoilt";
	}
	
	
	$http.get(getUrl()).then(function(response){
		$scope.extras = response.data;
		console.log(response.data);
	},function(response){
		console.log(response);
	});
	
	function getUrl(){
		if($routeParams.category === "Offers"){
			return "../crud/read/getOffers.php";
		}else{
			return "../crud/read/getSpoiltItems.php";
		}
	}
});

theApp.controller("create_extraCtlr", function($scope, $http, $routeParams, userDetails, lineDetails, httpResponse){
	$scope.station = userDetails.getStation();
	
	if($routeParams.ID === undefined){
		if($routeParams.category === "Offers"){
			$scope.category = "Offers";
			$scope.show = true;
		}else{
			$scope.category = "Items Spoilt";
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
			if(row.qty <= 0){
				alert("Quantity should be atleast 1");
				row.qty = null;
			}else{
				if(row.qty > row.item.finish){
					alert("Cannot update Qty, quantity entered is more than quantity available for sale");
					row.qty = null;
				}else{
					details[index] = {pdtNo: row.item.value, qty: row.qty};
				}
			}
			console.log(details, $scope.to);
		}
		
		$scope.validate = function(){
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
			}else{
				return "../crud/create/addSpoilt.php";
			}
		}
		
		function postData(){
			if($routeParams.category === "Offers"){
				return {station: $scope.station, RecepientCategory: $scope.to, UserID: userDetails.getUserID(), details:details};
			}else{
				return {station: $scope.station, UserID: userDetails.getUserID(), details:details};
			}
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
		}else{
			$scope.category = "Items Spoilt";
		}
		
		$http.get(getEditUrl()).then(function(response){
			$scope.station = response.data.extra[0].Station;
			$scope.to = response.data.extra[0].RecipientCategory;
			$scope.extras = response.data.extra_details;
			console.log(response.data);
		},function(response){
			console.log(response);
		});
		
		function getEditUrl(){
			if($routeParams.category === "Offers"){
				return "../crud/read/getOffer.php/?offerID="+ $routeParams.ID;
			}else{
				return "../crud/read/getSpoilt.php/?spoiltID="+ $routeParams.ID;
			}
		}
	}
});